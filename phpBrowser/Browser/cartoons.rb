#!/usr/bin/ruby
require 'open-uri'
require 'mysql'
require 'timeout'
require 'rubygems'
require 'nokogiri' 
require 'cgi'
require 'md5'
require 'time'

mysql = Mysql.init()
mysql.connect('cerberus','db','db')
mysql.select_db('viper7')

if $*.count != 0
	if $*[0].chomp == 'flush'
		mysql.query("TRUNCATE TABLE toons") 
		mysql.query("TRUNCATE TABLE toontags") 
		puts '*** Flushed Database ***'
	end
end

def addSeries(name, parent, mysql)
	folder = mysql.escape_string(CGI.escape(parent + '/' + name))
	return if mysql.query("SELECT count(*) FROM toons WHERE folder=\"#{folder}\"").fetch_row[0].to_i > 0
	
	text = open("http://www.thetvdb.com/api/GetSeries.php?seriesname=#{CGI.escape(name)}").read
	doc = Nokogiri::XML(text)
	series = doc.xpath('//Data').at('Series')
	tags = []
		
	if series != nil
		id = series.at('seriesid').text
		name = mysql.escape_string(series.at('SeriesName').text)
		banner = mysql.escape_string('http://www.thetvdb.com/banners/' + series.at('banner').text) if series.at('banner') != nil
		summary = mysql.escape_string(series.at('Overview').text) if series.at('Overview') != nil
		if series.at('IMDB_ID') != nil
			imdburl = mysql.escape_string('http://www.imdb.com/title/' + series.at('IMDB_ID').text) 
			3.times do
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
		
		if banner != nil
			if banner.match(/http\:\/\//i)
				outName = 'cartoons/' + MD5.md5(Time.now.to_f.to_s).to_s + '.jpg'
				outPath = "/opt/mediacache/#{outName}"
				outFile = open(outPath,'w')
				inFile = open(banner, 'r')
				while !inFile.eof?
					outFile.write(inFile.read)
				end
				outFile.close
				inFile.close
				banner = outName
			end

			mysql.query("INSERT INTO toons SET thetvdbid=#{id}, name='#{name}', folder=\"#{folder}\", summary=\"#{summary}\", imdburl='#{imdburl}', imageurl='#{banner}'")
			seriesid = mysql.insert_id
			mysql.query("DELETE FROM toontags WHERE toonsid=#{seriesid}")
			tags.each {|x|
				x=CGI.unescapeHTML(x.gsub(/&#160;/,'-'))
				mysql.query("INSERT INTO toontags SET toonsid=#{seriesid}, tag='#{x}'")
			}
			banner =~ /^.*\/(.*?)$/
			imagefile = ''
			imagefile = '- ' + $1 if $1 != nil
			puts "Added #{name} #{imagefile} - #{tags.count} tags"
		else
			puts "No image for #{name}"
		end
	else
		puts "Failed finding details for #{name}"
	end
end

parent = Dir.new('/opt/filestore/Cartoons')
parent.rewind
parent.each{|filename|
	next if filename == '.' or filename == '..' or !File.directory? parent.path + '/' + filename
	addSeries filename, 'Cartoons', mysql
}
