asyncjs = require "asyncjs"
fs = require "fs"
mysql = require "mysql"
path = require "path"

leadZero = (value) -> if value > 9 then "#{value}" else "0#{value}"

isHTML = (content) ->
  content.match /<(a|p|b|li)>/i

makeDirs = (base, dirs..., callback) ->
  fullDirs = [base]
  for dir in dirs
    dirPath = path.join fullDirs.slice(-1)[0], dir
    fullDirs.push dirPath
  console.log fullDirs
  asyncjs.list(fullDirs).filter (dirPath, next) ->
    path.exists dirPath, (exists) ->
      if exists
        console.log "dirPath exists", dirPath
        next null
      else
        console.log "dirPath does not exist", dirPath
        next dirPath
  .end()

savePost = (post) ->
  base = path.resolve __dirname + "/../app/posts"
  date = post.post_date_gmt
  year = date.getFullYear().toString()
  month = leadZero(date.getMonth() + 1)
  day = leadZero(date.getDay())
  file = post.post_name
  if post.html
    file = file + ".html"
  else
    file = file + ".md"
  post_path = path.join base, post.blog, year, month, file
  post_dir = path.dirname post_path
  console.log post_path
  makeDirs base, post.blog, year, month, (error) ->


client = mysql.createClient
  user: "problog"
  password: "Strap...it3"
  port: 23306
  database: "problog"
postQuery = "select * from wp_posts where post_status = 'publish' order by post_date_gmt limit 1;"
client.query postQuery, (error, results, fields) ->
  for post in results
    post.html = isHTML(post.post_content)
    post.blog = client.database
    console.log "----------"
    console.log "title: #{post.post_title}"
    console.log "name: #{post.post_name}"
    console.log "blog: #{post.blog}"
    console.log "date: #{post.post_date_gmt}"
    console.log "html: " + if post.html then "yes" else "no"
    console.log "content: #{post.post_content.slice(0, 50)}"
    savePost post
