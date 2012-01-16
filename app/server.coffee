config = require "../config"
express = require "express"
markdown = require("markdown-js").makeHtml

app = express.createServer()
app.use express.bodyParser()
app.use app.router
app.use express.compiler(src: __dirname + "/../public", enable: ["coffeescript"])
app.use express.static(config.staticDir)
if config.env.testing or config.env.development
  #Note to self. Make sure compiler comes BEFORE static
  app.use express.compiler(src: __dirname + "/../spec/js", enable: ["coffeescript"])
  app.use express.static(__dirname + "/../spec/js")

app.register ".md",
  compile: (md, options) ->
    html = markdown md
    (locals) -> html
app.set "view engine", "jade"
app.set "view options",
 layout: "layout.jade"
 locals:
   config: config
   specURIs: []
   testCSS: []
   title: ''
   wordpress: false
app.set "views", __dirname + "/templates"

#Load in the controllers
["pages", "galleries", "photos", "blog"].map (controllerName) ->
  controller = require "./controllers/" + controllerName
  controller.setup app

console.log "#{config.site} server starting on http://localhost:#{config.port}"
if process.env.NODE_ENV in ["production", "staging"]
  app.listen config.port, "127.0.0.1"
else
  app.use express.logger {format: ":method :url"}
  #Listen on all IPs in dev/test (for testing from other machines)
  app.listen config.port
