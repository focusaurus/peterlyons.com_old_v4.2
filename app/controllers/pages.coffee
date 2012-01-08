_ = require 'underscore'
config = require '../../config'
fs = require 'fs'

defaultLocals =
  config: config
  title: ''
  wordpress: false
  specURIs: []
  testCSS: []
defaultLocals.specURIs.start = false

class Page
  constructor: (@view, @title='', @locals={}, @specs=[]) ->
    if @view.indexOf(".") >= 0
      @URI = @view.split(".")[0]
    else
      @URI = @view
      @view = "#{@view}.jade"
    @locals.title = @title

  makeOptions: (req) =>
    options =
      locals: _.defaults @locals, defaultLocals
      layout: "layout.jade"
    addTests req, options.locals, @specs
    options

  render: (req, res) =>
    res.render @view, @makeOptions(req)

addTests = (req, locals, specs=[]) ->
  locals.wordpress = req.param 'wordpress', false
  if req.param 'test'
    locals.specURIs = [
      '/lib/jasmine/jasmine.js'
      '/lib/jasmine/jasmine-html.js'
      '/application/LayoutSpec.js'
    ]
    locals.specURIs.push.apply locals.specURIs, specs
    locals.testCSS = ['/lib/jasmine/jasmine.css']
  if req.param 'start'
    locals.specURIs.start = true

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
page "leveling_up.md", "Leveling Up: Career Advancement for Software Developers", ['/application/LevelingUpSpec.js']
page "web_prog.md", "Web Programming Concepts for Non-Programmers"
page "practices.md", "Practices and Values"
page "stacks.md", "Technology Stacks"
homePage = new Page 'home', pages[0].title, {}, pages[0].specs
homePage.URI = ''
pages.push homePage

route = (app, page) ->
  app.get '/' + page.URI, (req, res) ->
    page.render req, res

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

exports.Page = Page
