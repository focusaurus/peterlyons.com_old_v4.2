_ = require "underscore"
text = require "../lib/text"
asyncjs = require "asyncjs"
date = require "../../lib/date" #Do not remove. Monkey patches Date
errors = require "../errors"
express = require "express"
fs = require "fs"
jade = require "jade"
markdown = require("markdown-js").makeHtml
middleware = require "./middleware"
pages = require "./pages"
path = require "path"
util = require "util"

{Post, leadZero} = require("../models/post")

postLinks = {}

########## middleware ##########
loadPost = (req, res, next) ->
  blog = req.params[0]
  post = new Post
  post.base = path.join(__dirname, "..", "posts")
  post.load path.join(post.base, req.path + ".json"), blog, (error) ->
    if error?.code is "ENOENT"
      return next new errors.NotFound req.path
    return next(error) if error
    res.post = post
    post.presented = presentPost post
    links = postLinks[post.URI()]
    post.previous = links.previous
    post.next = links.next
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

blogArticle = (req, res, next) ->
  post = res.post
  footerPath = path.join __dirname, "..", "templates", "blog_layout.jade"
  fs.readFile footerPath, "utf8", (error, jadeText) ->
    return next error if error
    footerFunc = jade.compile jadeText
    res.html = footerFunc {post, body: res.html}
    next()

postTitle = (req, res, next) ->
  res.$("title").text(res.post.title + " | Peter Lyons")
  next()

previewMarkdown = (req, res, next) ->
  console.log("@bug previewMarkdown", req.body)
  res.html = markdown req.body
  next()

previewMiddleware = [
  middleware.debugLog "@bug before text"
  text({limit:"5mb"})
  middleware.debugLog "@bug after text"
  previewMarkdown
  middleware.domify
  middleware.flickr
  middleware.youtube
  middleware.undomify
  middleware.send
]

postMiddleware = [
  loadPost
  html
  markdownToHTML
  blogArticle
  middleware.layout
  middleware.domify
  postTitle
  middleware.flickr
  middleware.youtube
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
      asyncjs.list(self.posts).map (post, next) ->
        fakeRes =
          post: post
          viewPath: post.viewPath()
        next null, fakeRes
      .each (fakeRes, next) ->
        html req, fakeRes, next
      .each (fakeRes, next) ->
        markdownToHTML req, fakeRes, next
      .each (fakeRes, next) ->
        middleware.domify req, fakeRes, next
      .each (fakeRes, next) ->
        middleware.flickr req, fakeRes, next
      .each (fakeRes, next) ->
        middleware.youtube req, fakeRes, next
      .each (fakeRes, next) ->
        fakeRes.post.content = fakeRes.html
        next()
      .end (error, fakeRes) ->
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

loadBlog = (URI, callback) ->
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
    for post, index in posts
      postLinks[post.URI()] =
        next: if index > 0 then posts[index - 1] else null
        previous: if index < posts.length then posts[index + 1] else null
    callback error, posts

setup = (app) ->
  problog = new BlogIndex("problog", "Pete's Points")
  persblog = new BlogIndex("persblog", "The Stretch of Vitality")
  asyncjs.list([problog, persblog]).each (blog, next) ->
    loadBlog blog.URI, (error,  posts) ->
      blog.posts = blog.locals.posts = posts
      next error
  .each (blog, next) ->
    blog.route app
    next()
  .end (error) ->
    #no-op
  app.post "/convert", previewMiddleware

module.exports = {setup}
