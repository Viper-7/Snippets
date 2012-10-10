#!/usr/bin/ruby

require 'open-uri'
require 'cgi'
require 'uri'
require 'mysql'
require 'time'
require 'timeout'
require 'movie.rb'
require 'md5'

mysql = Mysql.init()
mysql.connect('cerberus','db','db')
mysql.select_db('viper7')

parent = Dir.new('/opt/filestore/Movies')

if $*.count != 0
	if $*[0].chomp == 'flush'
		Movie.clear
		exec("rm -rf /opt/mediacache/movies/*")
		puts '*** Flushed Database ***'
	end
end

puts "Cleaning Database"

Movie.fetch_all.each{|movie| 
	if !parent.include?(movie.filename) 
		puts "Removing deleted file #{movie.filename}"
		movie.delete
	else
		movie.cache
		if movie.boxurl.match(/http\:\/\//i)
			outName = "series/#{movie.name}.jpg".gsub(/&'\!/,'')
			outPath = "/opt/mediacache/#{outName}"
			outFile = open(outPath,'w')
			inFile = open(movie.boxurl, 'r')
			puts "Caching thumbnail for #{movie.name} to #{outPath}"
			while !inFile.eof?
				outFile.write(inFile.read)
			end
			outFile.close
			inFile.close
			movie.boxurl = outPath
			movie.save
		end
	end
}

parent.rewind
parent.each{|filename|
	next if filename == '.' or filename == '..' or filename == 'Thumbs.db' or filename == 'movieslist.txt' or filename == 'movieslist' or File.directory? parent.path + '/' + filename

	movie=filename.gsub(/\.[^\.]*$/,'')
	filename = mysql.escape_string(filename).gsub(/\&/,'\\\&').gsub(/ /,'\ ')

	qry = mysql.query("SELECT ID FROM imdbfiles WHERE filename='#{filename}'")
	next if qry.num_rows > 0
	
	part = 0
	movie =~ / - ([\d]) of [\d]/
	part = $1.to_i if $1
	movie=mysql.escape_string(movie.gsub(/ - [\d] of [\d]/,''))
	title=movie.gsub(/\&/,'\&')

	path = parent.path + '/' + filename.gsub(/\(/,'\(').gsub(/\)/,'\)')

	duration = `avidentify #{path} 2>/dev/null| grep Duration | cut -f 4 -d ' ' | cut -f 1 -d ,`.chop
	duration = 0 if duration == 'N/A' or duration == ''
	
	duration =~ /([\d]{2}):([\d]{2}):([\d]{2}).([\d])/
	duration = 0
	if $1 and $2 and $3 and $4
		duration = ($1.to_i * 3600) + ($2.to_i * 60) + $3.to_i + ($4.to_f / 10)
	end
	
	imdbid = 0
	qry = mysql.query("SELECT ID FROM imdb WHERE Name='#{title}'")
	imdbid = qry.fetch_row[0] if qry.num_rows > 0 
	if imdbid == 0
		mysql.query("INSERT INTO imdb SET Name='#{title}'")
		imdbid = mysql.insert_id
	end
	
	dbpart = 0
	qry = mysql.query("SELECT MAX(part) FROM imdbfiles WHERE imdbid='#{imdbid}' GROUP BY part")
	dbpart = qry.fetch_row[0] if qry.num_rows > 0
	
	if part > 0
		puts "Adding ID\##{imdbid}: #{movie} - Part #{part} - #{duration} secs"
		mysql.query("DELETE FROM imdbfiles WHERE imdbid='#{imdbid}' AND part=0") if dbpart == 0
	else
		puts "Adding ID\##{imdbid}: #{movie} - #{duration} secs"
		mysql.query("DELETE FROM imdbfiles WHERE imdbid='#{imdbid}'")
	end
	mysql.query("INSERT INTO imdbfiles SET imdbid='#{imdbid}', Filename='#{filename}', Duration=#{duration}, Part=#{part}")
}

puts 'Polling IMDB'

qry = mysql.query("SELECT Name, Duration FROM imdb where imdburl=''")
qry.each_hash{|row|
	movie = row['Name']
	duration = row['Duration']
	tags = []
	boxlink = ""
	title = ""
	plot = ""
	imdburl = ""
	tagline = ""
	releasedate = ""
	rating = 0
	text = ""

	dbtitle = mysql.escape_string(movie.gsub(/\&/,'\&'))
	
	print "Fetching IMDB data for #{movie} - "
	5.times do
		begin
			timeout(10) do
				text = open("http://www.google.com.au/search?btnI=1&q=" + URI.escape(movie).gsub(/ /,'+').gsub(/&/,'%26') + "+site%3Aimdb.com").read
			end
		rescue Exception
		end
		break if text.length > 0
	end
	
	text.scan(/<div id="tn15lhs">(.*?)<\/div>/m) {|x|
		x[0].scan(/src="(.*?)"/) {|line|
			outName = "movies/#{movie}.jpg".gsub(/&|'|!/,'')
			outPath = "/opt/mediacache/#{outName}"
			outFile = open(outPath,'w')
			inFile = open(line.first.to_s.chomp, 'r')
			while !inFile.eof?
				outFile.write(inFile.read)
			end
			outFile.close
			inFile.close
			boxlink = outName
		}
	}
	text.scan(/<div id="tn15title">(.*?)(?:<h5>Language:<\/h5>|<h5>Company:<\/h5>)/m) {|x|
		x[0].scan(/<h1>(.*?) <span>/) { |line|
			title = mysql.escape_string(line.to_s.chomp)
		}
		x[0].scan(/<h5>Plot:<\/h5>(.*?)<\/div>/m) { |block|
			block[0].scan(/<a class="tn15more inline" href="(.*?)\/plotsummary" onClick="\(new Image\(\)\)/) { |line|
				imdburl = mysql.escape_string('http://www.imdb.com' + line.to_s.chomp)
			}
			plot = mysql.escape_string(block[0].gsub(/\/title/,'http://www.imdb.com/title').gsub(/\/name/,'http://www.imdb.com/name').gsub(/add\ synopsis|full\ synopsis|\|/,'').chomp)
		}
		x[0].scan(/<h5>Genre:<\/h5>(.*?)<\/div>/m) { |block|
			if imdburl.length == 0
				block[0].scan(/<a class="tn15more inline" href="(.*?)\/keywords" onClick="\(new Image\(\)\)/) { |line|
					imdburl = mysql.escape_string('http://www.imdb.com' + line.to_s.chomp)
				}
			end
			block[0].scan(/<[^>]*?>(.*?)<\/a>/) {|line|
				tag = mysql.escape_string(line[0].chomp)
				tags.push tag if tag != 'more'
			}
		}
		if imdburl.length == 0
			x[0].scan(/"http:\/\/pro.imdb.com\/rg\/maindetails-title\/tconst-pro-header-link(.*?)">More at IMDb Pro/) { |line|
				imdburl = mysql.escape_string('http://www.imdb.com' + line.to_s.chomp)
			}
		end
		
		if duration.to_i == 0
			x[0].scan(/<h5>Runtime:<\/h5>(.*?)<\/div>/m) { |block|
				block[0] =~ /(\d* min)/
				duration = $1.to_i * 60
			}
		end
		
		x[0].scan(/<h5>Tagline:<\/h5>(.*?)(?: <a class="tn15more inline"|<\/div>)/m) { |block|
			tagline = mysql.escape_string(block[0].chomp.gsub(/\n/,''))
		}
		x[0].scan(/<h5>Release Date:<\/h5> (.*?)(?: <a class="tn15more inline"|<\/div>)/m) { |block|
			begin
				releasedate = Time.parse(block[0].chomp.gsub(/\n/,'').gsub(/\([^\)]*\)/,'')).to_i
			rescue Exception => ex
				releasedate = Time.parse('1 Jan ' + block[0].chomp.gsub(/\n/,'').gsub(/\([^\)]*\)/,'')).to_i if releasedate == ''
			end
		}
		releasedate = Time.parse('1 Jan 07').to_i if releasedate == '' or releasedate == 0
		
		x[0].scan(/<div class="usr rating">.*?<div class="meta">.*?<b>(.*?)<\/b>/m) { |line|
			rating = line[0].chop.to_f
		}
	}
	
	if imdburl != ''
		5.times do
			begin
				timeout(10) do
					text = open("#{imdburl}/keywords").read
				end
			rescue Exception
			end
			break if text.length > 0
		end
	
		text.scan(/<div id="tn15" class="keywords">.*?<div id="tn15content">.*<ul>(.*)<\/ul>/m) {|block|
			block[0].scan(/<a href[^>]*?>(.*?)<\/a>/) {|line|
				tag = mysql.escape_string(line[0].chomp)
				tags.push tag if tag != 'more'
			}
		}
	end

	movies = Movie.fetch_by_name("#{dbtitle}")
	if imdburl.length > 0 and movies.length > 0
		movies.each{|movie|
				fileduration = mysql.query("SELECT SUM(duration) from imdbfiles WHERE imdbid=#{movie.imdbid} GROUP BY imdbid").fetch_row[0].to_i
				duration = fileduration if fileduration > 0
				mysql.query("UPDATE imdb SET title='#{title}', plot='#{plot}', duration='#{duration}', tagline='#{tagline}', boxurl='#{boxlink}', releasedate='#{releasedate}', rating='#{rating}', imdburl='#{imdburl}' WHERE ID=#{movie.imdbid}")
				mysql.query("DELETE FROM imdbtags WHERE imdbid=#{movie.imdbid}")
				tags.each {|x|
					x=CGI.unescapeHTML(x.gsub(/&#160;/,'-'))
					mysql.query("INSERT INTO imdbtags SET imdbid=#{movie.imdbid}, tag='#{x}'")
				}
				puts "Success - #{tags.count} tags - #{releasedate}"
		}
	else
		puts "Failed - Deleting record"
		puts "http://www.google.com.au/search?btnI=1&q=" + URI.escape(movie).gsub(/ /,'+').gsub(/&/,'%26') + "+site%3Aimdb.com"
		movies.each{|x| x.delete}
	end

}

puts 'Removing duplicate tags'
2.times do
	qry = mysql.query("SELECT DISTINCT tag, imdbid, count(tag) as num FROM imdbtags GROUP BY imdbid, tag")
	count = 0
	qry.each_hash {|x|
		next if x['num'] == '1'

		imdbid = x['imdbid']
		tag = mysql.escape_string(x['tag'])
		num = x['num']

		count += 1
		mysql.query("DELETE FROM imdbtags WHERE imdbid=#{imdbid} AND tag='#{tag}' LIMIT 1")
		puts "#{count}: Found #{num} tags for #{imdbid} - Deleted 1st #{tag}"
	}
end
