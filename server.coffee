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

app.get '/leveling_up', (req, res) ->
  fs.readFile __dirname + '/app/templates/leveling_up.md', 'utf8', (error, md) ->
    if error
      res.render 'error502'
      return
    body = markdown.makeHtml md
    locals =
      title: 'Leveling Up: Career Advancement for Software Developers | Peter Lyons'
      body: body
    options =
      locals: _.defaults locals, defaultLocals
    template = __dirname + '/app/templates/layout.jade'
    jade.renderFile template, options, (error, html) ->
      if error
        res.render 'error502'
        return
      res.send html

getGalleries = (callback) ->
  fs.readFile config.photos.galleryDataPath, (error, data) ->
    if error
      return callback(error)
    galleries = (new gallery.Gallery(jg.dirName, jg.displayName, jg.startDate) \
      for jg in JSON.parse(data))
    return callback(null, galleries)

#Load photo metadata from a photos.json file in the gallery directory
getPhotoJSON = (locals, callback) ->
  fs.readFile locals.gallery.dirPath + "/" + "photos.json", (error, photoJSON) ->
    if error
      return callback()
    p = locals.photos
    #This extends locals.photos with all the new photos
    p.push.apply(p, photoJSONToObject locals.gallery, photoJSON)
    callback()

#Try the earlier approach where captions were embedded in IPTC info in
#the photo .jpg files directly
getPhotoIPTC = (locals, callback) ->
  if locals.photos
    #photoList has already been loaded from flat .json file
    #Don't bother trying IPTC subprocess
    return callback()

  #Now we run iptc_caption.py to generate a list of photos with captions
  #from the filesystem. This program writes JSON to stdout
  command = ['python ./bin/iptc_caption.py --dir ',
              "'#{config.photos.galleryDir}/#{gallery.dirName}'"].join ''
  child_process.exec command, (error, photoJSON, stderr) ->
    if error
      console.log error
      return callback()
    #This extends locals.photos with all the new photos
    p = locals.photos
    p.push.apply(p, photoJSONToObject locals.gallery, photoJSON)
    callback()

photoJSONToObject = (gallery, photoJSON) ->
  photos = JSON.parse(photoJSON)
  for photo in photos
    photo.fullSizeURI ="#{config.photos.photoURI}#{gallery.dirName}/#{photo.name}#{config.photos.extension}"
    photo.pageURI = "#{config.photos.galleryURI}?gallery=#{gallery.dirName}&photo=#{photo.name}"
  return photos

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
    locals.photos = []
    #First, try to load a photos.json metadata file
    #2nd choice, try iptc_caption.py to build the json
    async.series [
      (callback) ->
        getPhotoJSON locals, callback
      ,
      (callback) ->
        getPhotoIPTC locals, callback
      ],
      (error, dontcare) ->
        #Figure out which photo to display full size.
        photoParam = req.param 'photo'
        index = _.pluck(locals.photos, 'name').indexOf(photoParam)
        #If it's a bogus photo name, default to the first photo
        index = 0 if index < 0
        locals.photo = locals.photos[index]
        locals.photo.next = locals.photos[index + 1] or locals.photos[0]
        locals.photo.prev = locals.photos[index - 1] or _.last(locals.photos)
        locals.title = "#{locals.gallery.displayName} Photo Gallery"
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
      locals.formatDate = (date) ->
        if not date
          return ''
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

  galleries = _.sortBy galleries, (gallery) ->
    gallery.startDate
  galleries.reverse()
  fs.writeFile './app/data/galleries.json', JSON.stringify(galleries), (error) ->
    if error
      res.send error, 503
    else
      res.redirect '/admin/galleries'

app.get '/photos', renderPhotos

console.log "#{config.site} server starting on http://localhost:#{config.port}"
if process.env.NODE_ENV in ['production', 'staging']
  app.listen config.port, '127.0.0.1'
else
  #No nginx rewrites in the dev environment, so make this URI also work
  app.get '/app/photos', renderPhotos
  app.get '/admin/galleries', adminGalleries
  app.post '/admin/galleries', updateGalleries
  app.use express.logger {format: ':method :url'}
  #Listen on all IPs in dev/test (for testing from other machines)
  app.listen config.port
