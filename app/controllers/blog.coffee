_ = require "underscore"
asyncjs = require "asyncjs"
fs = require "fs"
markdown = require("markdown-js").makeHtml
middleware = require "./middleware"
pages = require "./pages"
path = require "path"

{Post, leadZero} = require("../models/post")

########## middleware ##########
loadPost = (req, res, next) ->
  blog = req.params[0]
  post = new Post
  post.base = path.join(__dirname, "..", "posts")
  post.load path.join(post.base, req.path + ".json"), blog, (error) ->
    return next(error) if error
    res.post = post
    res.viewPath = post.viewPath()
    next()

html = (req, res, next) ->
  return next() if not /\.html$/.test res.viewPath
  fs.readFile res.viewPath, "utf8", (error, htmlText) ->
    res.html = htmlText
    next error

markdownToHTML = (req, res, next) ->
  return next() if not /\.md$/.test res.viewPath
  fs.readFile res.viewPath, "utf8", (error, markdownText) ->
    res.html = markdown markdownText
    next error

postTitle = (req, res, next) ->
  $ = res.dom.window.$
  $("title").text(res.post.title + " | Peter Lyons")
  $("header").after("<h1>#{res.post.title}</h1>")
  next()

postMiddleware = [
  loadPost
  html
  markdownToHTML
  middleware.layout
  middleware.domify
  postTitle
  middleware.flickr
  middleware.undomify
  middleware.send
]

class BlogIndex extends pages.Page
  constructor: (@view, title='', @locals={}) ->
    @URI = @view
    @locals.title = title
    @locals.URI = @URI

  route: (app) =>
    self = this
    app.get "/#{@URI}", (req) ->
      self.render req

    app.get "/#{@URI}/feed", (req, res) ->
      options =
        layout: false
        pretty: true
        locals: self.locals
      options.locals.posts = self.posts
      res.header "Content-Type", "text/xml"
      res.render "feed", options

    app.get new RegExp("/(#{@URI})/\\d{4}/\\d{2}/\\w+"), postMiddleware

presentPost = (post) ->
  date = leadZero(post.publish_date.getMonth() + 1)
  date = date + "/" + leadZero(post.publish_date.getDay() + 1)
  date = date + "/" + post.publish_date.getFullYear()
  presented = {}
  presented = _.extend presented, post
  presented.title = presented.title.trim()
  presented.date = post.publish_date.toString "MMM dd, yyyy"
  presented

loadBlog = (app, URI, callback) ->
  posts = []
  asyncjs.walkfiles(path.normalize(__dirname + "/../posts/" + URI), null, asyncjs.PREORDER)
  .stat()
  .each (file, next) ->
    return next() if file.stat.isDirectory()
    return next() if not /\.(md|html)$/.test file.name
    post = new Post
    posts.push post
    post.base = path.resolve(path.join(__dirname, "../posts"))
    #TODO fix up
    noExt = file.path.substr 0, file.path.lastIndexOf('.')
    post.load "#{noExt}.json", URI, (error) ->
      return next(error) if error
      post.presented = presentPost(post)
      next()
  .end (error) ->
    posts = _.sortBy posts, (post) ->
      post.publish_date
    .reverse()
    callback error, posts

setup = (app) ->
  problog = new BlogIndex("problog", "Pete's Points")
  persblog = new BlogIndex("persblog", "The Stretch of Vitality")
  asyncjs.list([problog, persblog]).each (blog, next) ->
    loadBlog app, blog.URI, (error,  posts) ->
      blog.posts = blog.locals.posts = posts
      next error
  .each (blog, next) ->
    blog.route app
    next()
  .end (error) ->
    #no-op
module.exports = {setup}
