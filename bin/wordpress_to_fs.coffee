asyncjs = require "asyncjs"
fs = require "fs"
mysql = require "mysql"
path = require "path"
util = require "util"

class Post

  metadata: =>
    date: @date
    name: @name
    title: @title
    format: @format

  URI: =>
    year = @date.getFullYear().toString()
    month = leadZero(@date.getMonth() + 1)
    day = leadZero(@date.getDay())
    path.join @blog, year, month, @name

  contentPath: =>
    "#{@URI()}.#{@format}"

  metadataPath: =>
    "#{@URI()}.json"

leadZero = (value) -> if value > 9 then "#{value}" else "0#{value}"

makeDirs = (base, dirs..., callback) ->
  fullDirs = [base]
  for dir in dirs
    dirPath = path.join fullDirs.slice(-1)[0], dir
    fullDirs.push dirPath
  asyncjs.files(fullDirs)
  .filter (file, next) ->
    path.exists file.path, (exists) -> next null, !exists
  .mkdir("755")
  .each (file, next) ->
    console.log "path:", file.path, "should now exist"
    next(null, file)
  .end (error, result) ->
    callback error, result

buildPost = (result, next) ->
  post = new Post
  post.base = path.join __dirname, "../app/posts"
  post.blog = client.database
  post.content = result.post_content
  post.date = result.post_date_gmt
  post.format = if result.post_content.match /<(a|p|b|li)/i then "html" else "md"
  post.name = result.post_name
  post.title = result.post_title
  console.log "----------"
  console.log util.inspect(post.metadata())
  next null, post

savePost = (post, next) ->
  console.log "Converting", post.contentPath()
  fragment = path.dirname post.URI()
  makeDirs post.base, fragment.split("/")..., (error) ->
    return next(error) if error
    filePath = path.join post.base, post.contentPath()
    fs.writeFile filePath, post.content, "utf8", (error) ->
      return next(error) if error
      console.log "Post", post.title, "saved"
      next null, post

saveMetadata = (post, next) ->
  filePath = path.join post.base, post.metadataPath()
  fs.writeFile filePath, JSON.stringify(post.metadata()), "utf8", (error) ->
    return next(error) if error
    console.log "Saved metadata at", filePath
    next null, post

client = mysql.createClient
  user: "problog"
  password: "Strap...it3"
  port: 23306
  database: "problog"
postQuery = "select * from wp_posts where post_status = 'publish' and post_type = 'post' order by post_date_gmt limit 25;"
client.query postQuery, (error, results, fields) ->
  asyncjs.list(results).map(buildPost).each(savePost).each(saveMetadata).end (error) ->
    if error
      console.log error
      process.exit 42
    process.exit 0
