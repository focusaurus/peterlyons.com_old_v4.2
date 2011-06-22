_ = require 'underscore'
config = require '../../config'
fs = require 'fs'
jade = require 'jade'
markdown = require 'markdown-js'

class Page
  constructor: (@URI, @title='', @locals={}, @specs=[]) ->
    @locals.title = @title
    @view = @URI

Page.render = (req, res) ->
  locals = _.defaults(@locals, defaultLocals)
  locals.wordpress = req.param 'wordpress', false
  if req.param 'test'
    locals.specURIs = [
      '/lib/jasmine/jasmine.js'
      '/lib/jasmine/jasmine-html.js'
      '/application/LayoutSpec.js'
    ].concat @specs
    locals.testCSS = ['/lib/jasmine/jasmine.css']
  if req.param 'start'
    locals.specURIs.start = true
  res.render @view, {locals: locals}

pages = []
page = (URI, title, specs) ->
  pages.push new Page(URI, title, {}, specs)
page 'home', 'Peter Lyons: Web Development, Startups, Music', ['/application/HomePageSpec.js']
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
homePage = new Page 'home', pages[0].title, {}, pages[0].specs
homePage.URI = ''
pages.push homePage

partials = {}

defaultLocals =
  config: config
  title: ''
  partials: partials
  wordpress: false
  specURIs: []
  testCSS: []

defaultLocals.specURIs.start = false

route = (app, page) ->
  app.get '/' + page.URI, (req, res) ->
    Page.render.apply page, [req, res]

exports.setup = (app) ->
  #This pre-loads all included partials
  fs.readdir app.set('views'), (error, names) ->
    if error
      throw error
    for name in names
      if name.match /.partial$/
        key = name.split('.')[0]
        fs.readFile app.set('views') + '/' + name, 'utf8', (error, data) ->
          if error
            throw error
          partials[key] = data
          excerpt = data.slice(0, 20).replace '\n', '\\n'
          console.log "Stored data in key #{key}: #{excerpt}..."
  
  #Route all the simple static pages
  route app, page for page in pages

  app.get '/leveling_up', (req, res) ->
    fs.readFile __dirname + '../templates/leveling_up.md', 'utf8', (error, md) ->
      if error
        res.render 'error502'
        return
      body = markdown.makeHtml md
      locals =
        title: 'Leveling Up: Career Advancement for Software Developers'
        body: body
      options =
        locals: _.defaults locals, defaultLocals
      template = __dirname + '/../templates/layout.jade'
      jade.renderFile template, options, (error, html) ->
        if error
          res.render 'error502'
          return
        res.send html

exports.Page = Page