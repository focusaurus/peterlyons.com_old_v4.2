_ = require "underscore"
config = require "../../config"
date = require "../../date"
fs = require "fs"
asyncjs = require "asyncjs"
pages = require "./pages"
path = require "path"
util = require "util"

{Post, leadZero} = require("../models/post")
cache = {}

class PostPage extends pages.Page
  constructor: (@post, @locals={}) ->
    @view = @post.viewPath()
    @locals.title = @post.title
    @locals.post = @post

class BlogIndex extends pages.Page
  constructor: (@view, title='', @locals={}, @specs=[]) ->
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
    for post in @posts
      router = (post) ->
        app.get "/" + post.URI(), (req) ->
          new PostPage(post.presented).render req
      router post

presentPost = (post) ->
  date = leadZero(post.publish_date.getMonth() + 1)
  date = date + "/" + leadZero(post.publish_date.getDay() + 1)
  date = date + "/" + post.publish_date.getFullYear()
  presented = {}
  presented = _.extend presented, post
  presented.title = presented.title.trim()
  presented.name = presented.name.trim()
  presented.date = post.publish_date.toString "MMM dd, yyyy"
  presented

loadBlog = (app, URI, callback) ->
  cache[URI] = []
  asyncjs.walkfiles(path.normalize(__dirname + "/../posts/" + URI), null, asyncjs.PREORDER)
  .stat()
  .each (file, next) ->
    return next() if file.stat.isDirectory()
    return next() if not /\.(md|html)$/.test file.name
    post = new Post
    cache[URI].push post
    post.base = path.resolve(path.join(__dirname, "../posts"))
    #TODO fix up
    noExt = file.path.substr 0, file.path.lastIndexOf('.')
    post.load "#{noExt}.json", URI, (error) ->
      return next(error) if error
      post.presented = presentPost(post)
      next()
  .end (error) ->
    cache[URI] = _.sortBy cache[URI], (post) ->
      post.publish_date
    .reverse()
    callback error, cache[URI]

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
