_ = require './public/js/underscore.js'
express = require 'express'
child_process = require 'child_process'
fs = require 'fs'

config = require './server_config'

app = express.createServer()
app.use express.methodOverride()
app.use express.bodyParser()
app.use app.router
app.use(require('stylus').middleware({src: __dirname + '/public'}))
app.use express.static(__dirname + '/public')
app.use express.static(__dirname + '/overlay/var/www/' + config.site)
app.set 'view engine', 'jade'
app.set 'views', __dirname + '/app/templates'

locals =
  config: config
  navLinks: []
  title: ""

locals.navLinks.push({uri: "/home.html", label: "Home"})
locals.navLinks.push({uri: "/problog", label: "Blog (Technology)"})
locals.navLinks.push({uri: "/persblog", label: "Blog (Personal)"})
locals.navLinks.push({uri: "#{config.photos.galleryURI}", label: "Photo Gallery"})
locals.navLinks.push({uri: "/oberlin.html", label: "Sounds from Oberlin"})
locals.navLinks.push({uri: "/code_conventions.html", label: "Code Conventions"})
locals.navLinks.push({uri: "/smartears.html", label: "SmartEars"})
locals.navLinks.push({uri: "/bigclock.html", label: "BigClock"})
locals.wordpress = false

#This pre-loads all included partials
fs.readdir app.set('views'), (err, names) ->
  if err
    throw err
  for name in names
    if name.match /.partial$/
      key = name.split(".")[0]
      fs.readFile app.set('views') + "/" + name, (err, data) ->
        if err
          throw err
        locals[key] = data.toString()
        console.log "Stored data in key #{key}: #{locals[key].slice(0, 20)}..."
pages = []
page = (URI, title)->
  pages.push {URI: URI, title: title, staticURI: URI + ".html"}
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

route = (page) ->
  app.get '/' + page.URI, (req, res)->
    locals.title = page.title
    res.render page.URI, {locals: locals}

route page for page in pages

app.get '/app/photos', (req, res)->
  locals.title = "Photo Gallery"
  conf = config.photos
  fs.readdir conf.galleryDir, (err, names)->
    throw err if err
    #Stupid Mac OS X polluting the user space filesystem
    locals.galleries = _.without(names, '.DS_Store')
    locals.gallery = conf.defaultGallery
    galleryParam = req.param 'gallery'
    if _.contains locals.galleries, galleryParam
      locals.gallery = galleryParam
    #Now we run iptc_caption.py to generate a list of photos with captions
    #from the filesystem
    command = ["python ./bin/iptc_caption.py --dir ",
          "'#{conf.galleryDir}/#{locals.gallery}'"].join ''
    child_process.exec command, (error, photoJSON, stderr)->
      if error
        console.log error
        locals.photos = []
        return
      locals.photos = JSON.parse(photoJSON)
      for photo in locals.photos
        photo.fullSizeURI ="#{conf.photoURI}#{locals.gallery}/#{photo.name}#{conf.extension}"
        photo.pageURI = "#{conf.galleryURI}?gallery=#{locals.gallery}&photo=#{photo.name}"

      #Figure out which photo to display full size.
      photoParam = req.param 'photo'
      index = _.pluck(locals.photos, 'name').indexOf(photoParam)
      #If it's a bogus photo name, default to the first photo
      index = 0 if index < 0
      locals.photo = locals.photos[index]
      locals.photo.next = locals.photos[index + 1] or locals.photos[0]
      locals.photo.prev = locals.photos[index - 1] or _.last(locals.photos)
      res.render 'photos', {locals: locals}

console.log "#{config.site} server starting on port #{config.port}"
app.configure 'production', ()->
  app.listen config.port, '127.0.0.1'
app.configure 'test', ()->
  app.listen config.port
