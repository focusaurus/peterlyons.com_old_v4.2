_ = require './overlay/var/www/peterlyons.com/js/underscore'
express = require 'express'
child_process = require 'child_process'
fs = require 'fs'

config = require './server_config'
gallery = require './app/models/gallery'

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

pages = []
page = (URI, title) ->
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

render = (res, URI, newLocals) ->
  locals = _.defaults(newLocals, defaultLocals)
  res.render URI, {locals: locals}

route = (page) ->
  app.get '/' + page.URI, (req, res) ->
    render(res, page.URI or 'home', \
      {title: page.title, wordpress: req.param 'wordpress'})

route page for page in pages

getGalleries = (callback) ->
  fs.readFile config.photos.galleryDataPath, (error, data) ->
    if error
      return callback(error)
    return callback(null, JSON.parse(data))

renderPhotos = (req, res) ->
  locals = {title: 'Photo Gallery'}
  conf = config.photos
  getGalleries (error, galleries) ->
    throw error if error
    locals.galleries = galleries
    locals.gallery = new gallery.Gallery(conf.defaultGallery)
    galleryParam = req.param 'gallery'
    galleryNames = _.pluck galleries, 'dirName'
    if _.contains galleryNames, galleryParam
      locals.gallery = galleries[galleryNames.indexOf(galleryParam)]
    #Now we run iptc_caption.py to generate a list of photos with captions
    #from the filesystem
    command = ['python ./bin/iptc_caption.py --dir ',
          "'#{conf.galleryDir}/#{locals.gallery.dirName}'"].join ''
    child_process.exec command, (error, photoJSON, stderr) ->
      if error
        console.log error
        locals.photos = []
        return
      locals.photos = JSON.parse(photoJSON)
      for photo in locals.photos
        photo.fullSizeURI ="#{conf.photoURI}#{locals.gallery.dirName}/#{photo.name}#{conf.extension}"
        photo.pageURI = "#{conf.galleryURI}?gallery=#{locals.gallery.dirName}&photo=#{photo.name}"

      #Figure out which photo to display full size.
      photoParam = req.param 'photo'
      index = _.pluck(locals.photos, 'name').indexOf(photoParam)
      #If it's a bogus photo name, default to the first photo
      index = 0 if index < 0
      locals.photo = locals.photos[index]
      locals.photo.next = locals.photos[index + 1] or locals.photos[0]
      locals.photo.prev = locals.photos[index - 1] or _.last(locals.photos)
      #TODO set locals.title to something that includes the photo name
      render res, 'photos', locals

adminGalleries = (req, res) ->
  getGalleries (error, jsonGalleries) ->
    throw error if error
    if req.param 'discover'
      jsonNames = _.pluck(jsonGalleries, 'dirName')
      fs.readdir config.photos.galleryDir, (error, names) ->
        throw error if error
        #Stupid Mac OS X polluting the user space filesystem
        galleryDirNames = _.without(names, '.DS_Store')
        #galleryDirNames.sort()
        galleryDirNames = galleryDirNames.filter (name) ->
           not (jsonNames.indexOf(name) >= 0)

        newGalleries = (new gallery.Gallery(dirName) for dirName in galleryDirNames)
        allGalleries = jsonGalleries.concat newGalleries
        locals = {title: 'Manage Photos', galleries: allGalleries}
        render res, 'admin_galleries', locals
    else
      locals = {title: 'Manage Photos', galleries: jsonGalleries}
      #BUGBUG try to re-generate the autocomputed start date
      locals.galleries = (new gallery.Gallery(g.dirName, g.displayName, g.startDate) for g in jsonGalleries)
      locals.formatDate = (timestamp) ->
        if not timestamp
          return ''
        date = new Date(timestamp)
        return "#{date.getMonth() + 1}/#{date.getDate()}/#{date.getFullYear()}"
      render res, 'admin_galleries', locals

updateGalleries = (req, res) ->
  galleries = []
  for key of req.body
    match = key.match /gallery_(.*)_displayName/
    if not match
      continue
    dirName = match[1]
    startDate = req.body['gallery_' + dirName + '_startDate']
    galleries.push(new gallery.Gallery(dirName, req.body[key], startDate))

  _.sortBy galleries, (gallery) ->
    gallery.dirName

  fs.writeFile './app/data/galleries.json', JSON.stringify(galleries), (error) ->
    if error
      res.send error, 503
    else
      res.redirect '/admin/galleries'

app.get '/photos', renderPhotos

console.log "#{config.site} server starting on port #{config.port}"
if process.env.NODE_ENV in ['production', 'staging']
  app.listen config.port, '127.0.0.1'
else
  #No nginx rewrites in the dev environment, so make this URI also work
  app.get '/app/photos', renderPhotos
  app.get '/admin/galleries', adminGalleries
  app.post '/admin/galleries', updateGalleries
  app.use express.logger()
  #Listen on all IPs in dev/test (for testing from other machines)
  app.listen config.port
