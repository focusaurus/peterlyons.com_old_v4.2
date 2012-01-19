_ = require "underscore"
config = require "../../config"
date = require "../../date"
fs = require "fs"
asyncjs = require "asyncjs"
pages = require "./pages"
path = require "path"
util = require "util"

{Post, leadZero} = require("../models/post")
cache =
  posts: []

class PostPage extends pages.Page
  constructor: (@post, @locals={}) ->
    @view = @post.viewPath()
    @locals.title = @post.title
    @locals.post = @post

class BlogIndex extends pages.Page
  constructor: (@posts, title='', @locals={}, @specs=[]) ->
    @view = "problog"
    @locals.title = title
    @locals.posts = @posts

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

setup = (app) ->
  asyncjs.walkfiles(path.normalize(__dirname + "/../posts"), null, asyncjs.PREORDER)
  .stat()
  .each (file, next) ->
    return next() if file.stat.isDirectory()
    return next() if not /\.(md|html)$/.test file.name
    post = new Post
    cache.posts.push post
    post.base = path.resolve(path.join(__dirname, "../posts"))
    #TODO fix up
    noExt = file.path.substr 0, file.path.lastIndexOf('.')
    post.load "#{noExt}.json", "problog", (error) ->
      return next(error) if error
      app.get "/" + post.URI(), (req) ->
        new PostPage(post.presented).render req
      next()
      post.presented = presentPost(post)
  .end ->
    cache.posts = _.sortBy cache.posts, (post) ->
      post.publish_date
    .reverse()

  app.get "/problog", (req) ->
    new BlogIndex(cache.posts, "Pete's Points").render req

  app.get "/problog/feed", (req, res) ->
    options =
      layout: false
      pretty: true
      locals:
        posts: cache.posts
        baseURL: config.baseURL
    res.header "Content-Type", "text/xml"
    res.render "feed", options
module.exports = {setup}
