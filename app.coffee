express = require 'express'
util = require 'util'
fs = require 'fs'
coffeekup = require 'coffeekup'
app = express.createServer()
app.configure ->
  app.use express.methodOverride()
  app.use express.bodyDecoder()
  app.use app.router
  app.use express.staticProvider(__dirname + '/public')
  app.set 'views', __dirname + '/app/templates'

app.register '.coffee', require 'coffeekup'
app.set 'view engine', 'coffee'
util.debug(app.set('views'))
#This pre-loads all included partials
locals = {}
fs.readdir app.set('views'), (err, names) ->
  if err
    throw err
  for name in names
    if name.match /.partial$/
      key = name.split(".")[0]
      util.debug "Matched #{name} as #{key}"
      fs.readFile app.set('views') + "/" + name, (err, data) ->
        if err
          throw err
        locals[key] = data.toString()
        util.debug "Stored data in key #{key}: #{locals[key]}"

pages = ['home', 'bands', 'bigclock', 'template']
route = (pageURI) ->
  app.get '/' + pageURI, (req, res) ->
    res.render pageURI, {locals: locals}

route page for page in pages
app.get '/', (req, res) ->
    res.render pages[0], {locals: locals}
   
app.listen 9400
