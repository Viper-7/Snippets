require 'mysql'
class Movie
	attr_accessor :imdbid, :filename, :title, :name, :plot, :imdburl, :tagline, :releasedate
	attr_accessor :rating, :boxurl, :parts, :duration
	
	class << self
		attr_accessor :mysql

		def connect
			if @mysql.nil?
				@mysql = Mysql.init()
				@mysql.connect('cerberus','db','db')
				@mysql.select_db('viper7')
			end
		end
		
		Movie.connect
	end

	def cache
		if @imdbid
			qry = Movie.mysql.query("SELECT imdb.ID, Name, Title, Plot, IMDBURL, Tagline, ReleaseDate, Rating, BoxURL, sum(imdbfiles.duration) as duration, max(part) as parts FROM imdb, imdbfiles WHERE imdb.ID=imdbfiles.imdbid AND imdb.ID=#{@imdbid} GROUP BY imdb.id")
			qry.each_hash{|row|
				@name = row['Name'] if !@name
				@title = row['Title'] if !@title
				@plot = row['Plot'] if !@plot
				@imdburl = row['IMDBURL'] if !@imdburl
				@tagline = row['Tagline'] if !@tagline
				@releasedate = row['ReleaseDate'] if !@rating
				@rating = row['Rating'] if !@name
				@boxurl = row['BoxURL'] if !@boxurl
				if row['parts'].to_i > 0 and !@parts
					@parts = row['parts'].to_i
				else
					@parts = 1
				end
				@duration = row['duration'] if !@duration
			}
		else
			puts 'Tried to cache unloaded object'
		end
	end
	
	def save
		if @imdbid
			cache
			Movie.mysql.query("UPDATE imdb SET Name='#{Mysql.escape_string(@name)}', Title='#{Mysql.escape_string(@title)}', Plot='#{Mysql.escape_string(@plot)}', IMDBURL='#{Mysql.escape_string(@imdburl)}', Tagline='#{Mysql.escape_string(@tagline)}', ReleaseDate=#{@releasedate}, Rating='#{@rating}', BoxURL='#{Mysql.escape_string(@boxurl)}' WHERE ID=#{@imdbid}")
		else
			puts 'Tried to save unloaded object'
		end
	end
	
	def self.fetch_by_id(id)
		qry = Movie.mysql.query("SELECT imdbid, filename FROM imdbfiles WHERE imdbid=#{id}")
		out = []
		qry.each{|id,filename|
			newMovie = Movie.new
			newMovie.imdbid = id
			newMovie.filename = filename
			out.push newMovie
		}
		return out
	end
	
	def self.fetch_by_name(args)
		args = Mysql.escape_string(args)
		qry = Movie.mysql.query("SELECT imdb.ID, filename FROM imdb, imdbfiles WHERE imdb.id = imdbfiles.imdbid AND imdb.Name LIKE '#{args}'")
		out = []
		qry.each{|id,filename|
			newMovie = Movie.new
			newMovie.imdbid = id
			newMovie.filename = filename
			out.push newMovie
		}
		return out
	end
	
	def self.fetch_by_filename(args)
		args = Mysql.escape_string(args)
		qry = Movie.mysql.query("SELECT DISTINCT imdbid, filename FROM imdbfiles WHERE filename LIKE '#{args}'")
		out = []
		qry.each{|id,filename|
			newMovie = Movie.new
			newMovie.imdbid = id
			newMovie.filename = filename
			out.push newMovie
		}
		return out
	end
	
	def self.fetch_by_sql(sql)
		testqry = Movie.mysql.query(sql)
		if testqry.num_fields == 1 and testqry.num_rows > 0
			if testqry.fetch_row[0].match(/\d/)
				qry = Movie.mysql.query("SELECT DISTINCT imdbid, filename FROM imdbfiles WHERE imdbid IN (#{sql})")
				out = []
				qry.each{|id,filename|
					newMovie = Movie.new
					newMovie.imdbid = id
					newMovie.filename = filename
					out.push newMovie
				}
				return out
			else
				return []
			end
		else
			return []
		end
	end
	
	def self.fetch_all
		qry = Movie.mysql.query("SELECT imdbid, filename FROM imdbfiles")
		out = []
		qry.each{|id,filename|
			newMovie = Movie.new
			newMovie.imdbid = id
			newMovie.filename = filename
			out.push newMovie
		}
		return out
	end
	
	def delete
		Movie.mysql.query("DELETE FROM imdbfiles WHERE imdbid='#{@imdbid}'")
		Movie.mysql.query("DELETE FROM imdbtags WHERE imdbid='#{@imdbid}'")
		Movie.mysql.query("DELETE FROM imdb WHERE id='#{@imdbid}'")
	end
	
	def self.clear
		Movie.mysql.query("TRUNCATE TABLE imdbfiles")
		Movie.mysql.query("TRUNCATE TABLE imdbtags")
		Movie.mysql.query("TRUNCATE TABLE imdb")
	end	
end
