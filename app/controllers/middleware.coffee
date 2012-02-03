config = require "../../config"
fs = require "fs"
jsdom = require "jsdom"
jade = require "jade"
path = require "path"

jqueryPath = path.join __dirname, "..", "..", "public", "js", "jquery.js"

flickrshowTemplate = """<object width="500" height="375">
  <param name="flashvars" value="offsite=true&lang=en-us&{URLs}&jump_to="></param> <param name="movie" value="http://www.flickr.com/apps/slideshow/show.swf?v=109615"></param> <param name="allowFullScreen" value="true"></param><embed type="application/x-shockwave-flash" src="http://www.flickr.com/apps/slideshow/show.swf?v=109615" allowFullScreen="true" flashvars="offsite=true&lang=en-us&{URLs}&jump_to=" width="500" height="375"></embed></object>"""

youtubeTemplate = "<iframe width='420' height='315' src='{URL}' frameborder='0' allowfullscreen></iframe>"

exports.layout = (req, res, next) ->
  layoutPath = path.join __dirname, "..", "templates", "layout.jade"
  fs.readFile layoutPath, "utf8", (error, jadeText) ->
    layoutFunc = jade.compile jadeText, {filename: layoutPath}
    locals =
      config: config
      title: ""
      body: res.html or ""
    res.html = layoutFunc locals
    next error

exports.domify = (req, res, next) ->
  jsdom.env res.html, [jqueryPath], (error, dom) ->
    return next error if error
    res.dom = dom
    dom.toMarkup = ->
      #Remove the local jquery script reference added by jsdom
      #Once this pull request is merged and released we can do this:
      #https://github.com/tmpvar/jsdom/pull/392#issuecomment-3747364
      #@window.$("script.jsdom").remove()
      #But for now we need to do this, which is brittle
      @window.$("script").last().remove()
      @window.document.doctype + @window.document.innerHTML
    next error

exports.undomify = (req, res, next) ->
  res.html = res.dom.toMarkup()
  next()

exports.send = (req, res) ->
  res.send res.html

exports.flickr = (req, res, next) ->
  $ = res.dom.window.$
  $("flickrshow").each (index, elem) ->
    $elem = $(elem)
    URLs = $elem.attr "href"
    $elem.replaceWith(flickrshowTemplate.replace /\{URLs\}/g, URLs)
  next()

exports.youtube = (req, res, next) ->
  $ = res.dom.window.$
  $("youtube").each (index, elem) ->
    $elem = $(elem)
    URL = $elem.attr "href"
    $elem.replaceWith(youtubeTemplate.replace /\{URL\}/, URL)
  next()
