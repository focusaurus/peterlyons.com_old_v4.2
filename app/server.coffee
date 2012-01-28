#!/usr/bin/env coffee
config = require "../config"
errors = require "./errors"
express = require "express"
markdown = require("markdown-js").makeHtml
path = require "path"

app = express.createServer()
app.use express.bodyParser()
app.use express.logger {format: ":method :url"}
app.use app.router
app.use express.compiler(src: __dirname + "/../public", enable: ["coffeescript"])
app.use express.static(config.staticDir)
if config.tests
  #Note to self. Make sure compiler comes BEFORE static
  app.use express.compiler(src: __dirname + "/../test", enable: ["coffeescript"])
  app.use express.static(__dirname + "/../test")


app.register ".md",
  compile: (md, options) ->
    html = markdown md
    (locals) -> html
app.register ".html",
  compile: (html, options) ->
    (locals) -> html
app.set "view engine", "jade"
app.set "view options",
  layout: "layout.jade"
app.set "views", __dirname + "/templates"

specURIs = []
specURIs.start = false
app.helpers
  config: config
  post: false
  specURIs: specURIs
  testCSS: []
  title: ''
  wordpress: false

app.error (error, req, res, next) ->
  console.log error
  if error instanceof errors.NotFound
    res.render path.join(__dirname, "..", config.staticDir, "error404.html"), {layout: false}
  else
    next error

#Last in the chain means 404 for you
app.use (req, res, next) ->
  next new errors.NotFound

#Load in the controllers
["pages", "galleries", "photos", "blog"].map (controllerName) ->
  controller = require "./controllers/" + controllerName
  controller.setup app

ip = if config.loopback then "127.0.0.1" else "0.0.0.0"
console.log 2, process.env.PATH
console.log process.env.NODE_ENV
console.log "Express serving on http://#{ip}:#{config.port} baseURRL: #{config.baseURL}, env: #{process.env.NODE_ENV}"
app.listen config.port, ip
