config = require "../config"
express = require "express"
markdown = require("markdown-js").makeHtml

app = express.createServer()
app.use express.bodyParser()
app.use express.logger {format: ":method :url"}
app.use app.router
app.use express.compiler(src: __dirname + "/../public", enable: ["coffeescript"])
app.use express.static(config.staticDir)
if config.tests
  #Note to self. Make sure compiler comes BEFORE static
  app.use express.compiler(src: __dirname + "/../spec/js", enable: ["coffeescript"])
  app.use express.static(__dirname + "/../spec/js")

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

#Load in the controllers
["pages", "galleries", "photos", "blog"].map (controllerName) ->
  controller = require "./controllers/" + controllerName
  controller.setup app

ip = if config.loopback then "127.0.0.1" else "0.0.0.0"
console.log "Express serving on http://#{ip}:#{config.port} ( #{config.baseURL} )"
app.listen config.port, ip
