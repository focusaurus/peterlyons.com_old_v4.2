_ = require './public/js/underscore.js'
express = require 'express'
fs = require 'fs'

config = require './server_config'

app = express.createServer()
app.use express.methodOverride()
app.use express.bodyDecoder()
app.use app.router
app.use(require('stylus').middleware({src: __dirname + '/public'}))
app.use express.staticProvider(__dirname + '/public')
app.use express.staticProvider(__dirname + '/overlay/var/www/' + config.site)
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

pages = ['home', 'bands', 'bigclock', 'template']
titles =
  home: "Home"
  bands: "Bands"
  bigclock: "Big Clock"
  
route = (pageURI) ->
  app.get '/' + pageURI, (req, res) ->
    locals.title = titles[pageURI]
    res.render pageURI, {locals: locals}

route page for page in pages

app.get '/app/photos', (req, res)->
  locals.title = "Photo Gallery"
  fs.readdir config.photos.galleryDir, (err, names)->
    throw err if err
    #Stupid Mac OS X polluting the user space filesystem
    locals.galleries = _.without(names, '.DS_Store')
    locals.gallery = config.photos.defaultGallery
    galleryParam = req.param 'gallery'
    if _.contains locals.galleries, galleryParam
      locals.gallery = galleryParam
    fs.readdir config.photos.galleryDir + '/' + locals.gallery, (err, names)->
      throw err if err
      locals.photos = _.select names, (name)->
        name.indexOf(config.photos.thumbExtension) > 0
      locals.photos = _.map locals.photos, (thumbName)->
        photoName = thumbName.slice(0, thumbName.length - config.photos.thumbExtension.length)
        return {
         name: photoName
         caption: 'TODO',
         jsCaption: 'TODO',
         fullSizeURI: "#{config.photos.photoURI}#{locals.gallery}/#{photoName}#{config.photos.extension}",
         pageURI: "#{config.photos.galleryURI}?gallery=#{locals.gallery}&photo=#{photoName}"}
         
      photoParam = req.param 'photo'
      index = _.pluck(locals.photos, 'name').indexOf(photoParam)
      index = 0 if index < 0
      locals.photo = locals.photos[index]
      locals.photo.next = locals.photos[index + 1] or locals.photos[0]
      locals.photo.prev = locals.photos[index - 1] or _.last(locals.photos)
      res.render 'photos', {locals: locals}
    
console.log "#{config.site} server starting on port #{config.port}"
app.listen config.port
