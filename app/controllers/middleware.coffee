config = require "../../config"
fs = require "fs"
jsdom = require "jsdom"
jade = require "jade"
markdown = require("markdown-js").makeHtml
path = require "path"

jqueryPath = path.join __dirname, "..", "..", "public", "js", "jquery.js"
flickrshowTemplate = """<object width="500" height="375">
  <param name="flashvars" value="offsite=true&lang=en-us&{URLs}&jump_to="></param> <param name="movie" value="http://www.flickr.com/apps/slideshow/show.swf?v=109615"></param> <param name="allowFullScreen" value="true"></param><embed type="application/x-shockwave-flash" src="http://www.flickr.com/apps/slideshow/show.swf?v=109615" allowFullScreen="true" flashvars="offsite=true&lang=en-us&{URLs}&jump_to=" width="500" height="375"></embed></object>"""

exports.html = (req, res, next) ->
  fs.readFile res.viewPath, "utf8", (error, htmlText) ->
    res.html = htmlText
    next error

exports.markdownToHTML = (req, res, next) ->
  fs.readFile res.viewPath, "utf8", (error, markdownText) ->
    res.html = markdown markdownText
    next error

exports.layout = (req, res, next) ->
  layoutPath = path.join __dirname, "..", "templates", "layout.jade"
  fs.readFile layoutPath, "utf8", (error, jadeText) ->
    layoutFunc = jade.compile jadeText, {filename: layoutPath}
    locals =
      config: config
      post: false
      title: ""
      body: res.html or ""
    res.html = layoutFunc locals
    next error

exports.domify = (req, res, next) ->
  jsdom.env res.html, [jqueryPath], (error, dom) ->
    res.dom = dom
    next error

exports.flickr = (req, res, next) ->
  $ = res.dom.window.$
  $("flickrshow").each (index, elem) ->
    $elem = $(elem)
    URLs = $elem.attr "href"
    $elem.replaceWith(flickrshowTemplate.replace /\{URLs\}/g, URLs)
  next()

exports.undomify = (req, res, next) ->
  res.html = res.dom.window.document.innerHTML
  next()

exports.send = (req, res) ->
  res.send res.html
