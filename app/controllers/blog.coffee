_ = require "underscore"
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
  constructor: (@post, @locals={}, @specs=[]) ->
    @title = @post.title

  render: (res) ->
    console.log "rendering post", util.inspect(@post)
    options = @makeOptions res.req
    res.render @post.viewPath(), options

class BlogIndex extends pages.Page
  constructor: (@posts, @title='', @locals={}, @specs=[]) ->

  render: (res) ->
    options = @makeOptions res.req
    options.locals.posts = @posts
    res.render "blog_index", options

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
      console.log post.view, post.URI(), post.publish_date
      app.get "/" + post.URI(), (req, res) ->
        new PostPage(post.presented).render res
      next()
      post.presented = presentPost(post)
  .end ->
    cache.posts = _.sortBy cache.posts, (post) ->
      post.publish_date
    .reverse()
    console.log "real asyncjs walkfiles"

  app.get "/problog", (req, res) ->
    new BlogIndex(cache.posts, "Blog Index").render res

  app.get "/problog/feed", (req, res) ->
    options =
      layout: false
      pretty: true
      locals:
        posts: cache.posts
        site: "http://peterlyons.com" #TODO soft code this
    res.header "Content-Type", "text/xml"
    res.render "feed", options
module.exports = {setup}
