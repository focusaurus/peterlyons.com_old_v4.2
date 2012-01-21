blog = process.argv[2]
asyncjs = require "asyncjs"
fs = require "fs"
mysql = require "mysql"
path = require "path"
util = require "util"
Post = require("../app/models/post").Post

buildPost = (result, next) ->
  post = new Post
  post.base = path.join __dirname, "../app/posts"
  post.blog = client.database
  post.content = result.post_content
  post.publish_date = result.post_date_gmt
  post.format = if result.post_content.match /<(a|p|b|li)/i then "html" else "md"
  post.name = result.post_name
  post.title = result.post_title
  console.log "----------"
  console.log util.inspect(post.metadata())
  next null, post

savePost = (post, next) ->
  console.log "Converting", post.contentPath()
  dirPath = path.join post.base, post.URI()
  asyncjs.makePath dirPath, (error) ->
    filePath = path.join post.base, post.contentPath()
    fs.writeFile filePath, post.content, "utf8", (error) ->
      console.log "Saved post at", filePath
      next error, post

saveMetadata = (post, next) ->
  filePath = path.join post.base, post.metadataPath()
  fs.writeFile filePath, JSON.stringify(post.metadata()) + "\n", "utf8", (error) ->
    console.log "Saved metadata at", filePath
    next error, post

client = mysql.createClient
  user: blog
  password: "Strap...it3"
  port: 23306
  database: blog
postQuery = "select * from wp_posts where post_status = 'publish' and post_type = 'post' order by post_date_gmt;"
client.query postQuery, (error, results, fields) ->
  asyncjs.list(results).map(buildPost).each(savePost).each(saveMetadata).end (error) ->
    if error
      console.log error
      process.exit 42
    process.exit 0
