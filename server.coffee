_ = require './overlay/var/www/peterlyons.com/js/underscore'
express = require 'express'
child_process = require 'child_process'
fs = require 'fs'

config = require './server_config'

app = express.createServer()
app.use express.methodOverride()
app.use express.bodyParser()
app.use app.router
#app.use(require('stylus').middleware({src: config.staticDir}))
app.use express.static(config.staticDir)
app.set 'view engine', 'jade'
app.set 'views', __dirname + '/app/templates'

partials = {}
#This pre-loads all included partials
fs.readdir app.set('views'), (err, names) ->
  if err
    throw err
  for name in names
    if name.match /.partial$/
      key = name.split('.')[0]
      fs.readFile app.set('views') + '/' + name, (err, data) ->
        if err
          throw err
        partials[key] = data.toString()
        console.log "Stored data in key #{key}: #{partials[key].slice(0, 20)}..."

locals =
  config: config
  title: ''
  partials: partials
  wordpress: false

pages = []
page = (URI, title)->
  pages.push {URI: URI, title: title}
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
  app.get '/' + page.URI, (req, res)->
    locals = _.defaults({title: page.title, wordpress: req.param 'wordpress'}, locals)
    res.render page.URI or 'home', {locals: locals}

route page for page in pages

getGalleries = (callback)->
  fs.readdir config.photos.galleryDir, (err, names)->
    if err
      return callback(err)
    #Stupid Mac OS X polluting the user space filesystem
    galleries = _.without(names, '.DS_Store')
    galleries.sort()
    return callback(null, galleries)

renderPhotos = (req, res)->
  locals = _.defaults({title: 'Photo Gallery'}, locals}
  conf = config.photos
  getGalleries (err, galleries)->
    throw err if err
    locals.galleries = galleries
    locals.gallery = conf.defaultGallery
    galleryParam = req.param 'gallery'
    if _.contains locals.galleries, galleryParam
      locals.gallery = galleryParam
    #Now we run iptc_caption.py to generate a list of photos with captions
    #from the filesystem
    command = ['python ./bin/iptc_caption.py --dir ',
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
      #TODO set locals.title to something that includes the photo name
      res.render 'photos', {locals: locals}

adminPhotos = (req, res)->
  locals = _.defaults {title: 'Manage Photos'}, locals
  getGalleries (err, galleries)->
    throw err if err
    locals.galleries = galleries
    res.render 'photos_admin', {locals: locals}

app.get '/photos', renderPhotos

console.log "#{config.site} server starting on port #{config.port}"
if process.env.NODE_ENV in ['production', 'staging']
  app.listen config.port, '127.0.0.1'
else
  #No nginx rewrites in the dev environment, so make this URI also work
  app.get '/app/photos', renderPhotos
  app.get '/admin/photos', adminPhotos
  #Listen on all IPs in dev/test (for testing from other machines)
  app.listen config.port
