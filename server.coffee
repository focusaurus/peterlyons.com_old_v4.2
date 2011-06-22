async = require 'async'
_ = require './public/js/underscore'
express = require 'express'
child_process = require 'child_process'
fs = require 'fs'
markdown = require 'markdown-js'
jade = require 'jade'

config = require './server_config'
gallery = require './app/models/gallery'

app = express.createServer()
app.use express.bodyParser()
#TODO just add a renderDefaults method to the IncomingMessage prototype
app.use (req, res, next) ->
  res.renderDefaults = (URI, newLocals) ->
    locals = _.defaults(newLocals, defaultLocals)
    if req.param 'test'
      locals.specURIs = [
        '/lib/jasmine/jasmine.js'
        '/lib/jasmine/jasmine-html.js'
        '/application/LayoutSpec.js'
      ]
      locals.testCSS = ['/lib/jasmine/jasmine.css']
    if req.param 'start'
      locals.specURIs.start = true
    this.render URI, {locals: locals}
  next()
app.use app.router
#app.use(require('stylus').middleware({src: config.staticDir}))
app.use express.compiler(src: __dirname + '/public', enable: ['coffeescript'])
app.use express.static(config.staticDir)
if config.env.testing or config.env.development
  #Note to self. Make sure compiler comes BEFORE static
  app.use express.compiler(src: __dirname + '/spec/js', enable: ['coffeescript'])
  app.use express.static(__dirname + '/spec/js')

app.set 'view engine', 'jade'
app.set 'views', __dirname + '/app/templates'


partials = {}
#This pre-loads all included partials
fs.readdir app.set('views'), (error, names) ->
  if error
    throw error
  for name in names
    if name.match /.partial$/
      key = name.split('.')[0]
      fs.readFile app.set('views') + '/' + name, (error, data) ->
        if error
          throw error
        partials[key] = data.toString()
        console.log "Stored data in key #{key}: #{partials[key].slice(0, 20)}..."

defaultLocals =
  config: config
  title: ''
  partials: partials
  wordpress: false
  specURIs: []
  testCSS: []

defaultLocals.specURIs.start = false


['galleries', 'photos'].map (controllerName) ->
  controller = require './app/controllers/' + controllerName
  controller.setup app

pages = []
page = (URI, title, spec=null) ->
  pages.push {URI, title, spec}
page '', 'Peter Lyons: Web Development, Startups, Music'
page 'home', 'Peter Lyons: Web Development, Startups, Music'
page 'bands', 'My Bands'
page 'bigclock', 'BigClock: a full screen desktop clock in java'
page 'career', 'My Career'
page 'hackstars', 'TechStars, pick me!'
page 'linkzie', 'Linkzie: A Simple Bookmark Manager'
page 'smartears', 'SmartEars: Ear Training Software'
page 'oberlin', 'Music from Oberlin'
page 'code_conventions', 'Code Conventions'
page 'favorites', 'Favorite Musicians'
page 'error404', 'Not Found'
page 'error502', 'Oops'

route = (page) ->
  app.get '/' + page.URI, (req, res) ->
    res.renderDefaults page.URI or 'home',
      title: page.title
      wordpress: req.param 'wordpress'

route page for page in pages

app.get '/leveling_up', (req, res) ->
  fs.readFile __dirname + '/app/templates/leveling_up.md', 'utf8', (error, md) ->
    if error
      res.render 'error502'
      return
    body = markdown.makeHtml md
    locals =
      title: 'Leveling Up: Career Advancement for Software Developers'
      body: body
    options =
      locals: _.defaults locals, defaultLocals
    template = __dirname + '/app/templates/layout.jade'
    jade.renderFile template, options, (error, html) ->
      if error
        res.render 'error502'
        return
      res.send html

console.log "#{config.site} server starting on http://localhost:#{config.port}"
if process.env.NODE_ENV in ['production', 'staging']
  app.listen config.port, '127.0.0.1'
else
  app.use express.logger {format: ':method :url'}
  #Listen on all IPs in dev/test (for testing from other machines)
  app.listen config.port
