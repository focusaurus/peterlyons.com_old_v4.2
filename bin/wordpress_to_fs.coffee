asyncjs = require "asyncjs"
fs = require "fs"
mysql = require "mysql"
path = require "path"
util = require "util"

leadZero = (value) -> if value > 9 then "#{value}" else "0#{value}"

guessFormat = (content) ->
  if content.match /<(a|p|b|li)/i then "html" else "md"

makeDirs = (base, dirs..., callback) ->
  fullDirs = [base]
  for dir in dirs
    dirPath = path.join fullDirs.slice(-1)[0], dir
    fullDirs.push dirPath
  asyncjs.files(fullDirs)
  .print()
  .filter (file, next) ->
    path.exists file.path, (exists) -> next null, !exists
  .print()
  .mkdir("755")
  .each (file, next) ->
    console.log "path:", file.path, "should now exist"
    next(null, file)
  .end (error, result) ->
    callback error, result

buildPost = (result, next) ->
  post =
    blog: client.database
    content: result.post_content.slice 0, 50 #Snippet until we log it
    date: result.post_date_gmt
    format: guessFormat result.post_content
    name: result.post_name
    title: result.post_title

  console.log "----------"
  console.log util.inspect(post)
  #Now store the full content
  post.content = result.post_content
  next null, post

savePost = (post, next) ->
  base = path.resolve __dirname + "/../app/posts"
  year = post.date.getFullYear().toString()
  month = leadZero(post.date.getMonth() + 1)
  day = leadZero(post.date.getDay())
  file = "#{post.name}.#{post.format}"
  post.path = path.join base, post.blog, year, month, file
  console.log "Converting", post.path
  makeDirs base, post.blog, year, month, (error) ->
    return next(error) if error
    fs.writeFile post.path, post.content, "utf8", (error) ->
      return next(error) if error
      console.log "Post", post.title, "saved"
      next(null, post)

client = mysql.createClient
  user: "problog"
  password: "Strap...it3"
  port: 23306
  database: "problog"
postQuery = "select * from wp_posts where post_status = 'publish' and post_type = 'post' order by post_date_gmt limit 25;"
client.query postQuery, (error, results, fields) ->
  asyncjs.list(results).map(buildPost).each(savePost).end()
