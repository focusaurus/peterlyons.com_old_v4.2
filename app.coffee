express = require 'express'
util = require 'util'
coffeekup = require 'coffeekup'
app = express.createServer()
app.configure ->
  app.use express.methodOverride()
  app.use express.bodyDecoder()
  app.use app.router
  app.use express.staticProvider(__dirname + '/public')
  app.set 'views', __dirname + '/templates'

app.register '.coffee', require 'coffeekup'
app.set 'view engine', 'coffee'

pages = ['home', 'bands', 'bigclock', 'template']
route = (pageURI) ->
  app.get '/' + pageURI, (req, res) ->
    res.render pageURI

route page for page in pages
app.get '/', (req, res) ->
    res.render pages[0]
   
app.get '/bigclock2', (req, res) ->
  include = (template) ->
    coffeekup.render(template)
  options =
    context:
      varc: "varc_value_context"
      include: (template) ->
        coffeekup.render(template)
    locals:
      varl: "varl_value"
      include: (template) ->
        coffeekup.render(template)
  util.log options
  res.render "bigclock", options
app.listen 9400
