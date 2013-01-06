_ = require "underscore"
async = require "async"
fs = require "fs"
path = require "path"
asyncjs = require "asyncjs"
markdown = require("markdown-js").makeHtml
mkdirp = require "mkdirp"

leadZero = (value) -> if value > 9 then "#{value}" else "0#{value}"
slug = (phrase="") ->
  phrase.replace(/\s/g, "-").replace(/'/g, "").replace(/\./g, "-").replace(/--/g, "-").replace(/-$/, "").toLowerCase()

class Post
  constructor: (@blog, @title, @publish_date, @format) ->
    @name = slug(@title)

  metadata: =>
    publish_date: @publish_date
    name: @name
    title: @title
    format: @format

  URI: =>
    year = @publish_date.getFullYear().toString()
    month = leadZero(@publish_date.getMonth() + 1)
    day = leadZero(@publish_date.getDay())
    path.join @blog, year, month, @name

  contentPath: =>
    "#{@URI()}.#{@format}"

  metadataPath: =>
    "#{@URI()}.json"

  dirPath: =>
    path.dirname "#{@contentPath()}"

  viewPath: =>
    path.join @base, "#{@URI()}.#{@format}"

  loadMetadata: (metadataPath, blog, callback) =>
    @metadataPath = metadataPath
    @blog = blog
    self = this
    path.exists @metadataPath, (exists) ->
      return if not exists
      fs.readFile metadataPath, "utf8", (error, jsonString) ->
        return callback(error) if error
        metadata = JSON.parse jsonString
        _.extend self, metadata
        self.publish_date = new Date self.publish_date
        self.view = "#{self.URI()}.#{self.format}"
        callback()

  load: (metadataPath, blog, callback) =>
    @metadataPath = metadataPath
    @blog = blog
    self = this
    asyncjs.files([@metadataPath]).readFile("utf8").each (file, next) ->
      metadata = JSON.parse file.data
      _.extend self, metadata
      self.publish_date = new Date self.publish_date
      self.view = "#{self.URI()}.#{self.format}"
      next()
    .each (file, next) ->
      noExt = file.path.substr 0, file.path.lastIndexOf('.')
      file.path = "#{noExt}.#{self.format}"
      file.name = path.basename file.path
      next()
    .readFile("utf8").each (file, next) ->
      if self.format == "md"
        self.content = markdown file.data
      else
        self.content = file.data
      next()
    .end (error) ->
      callback error

  save: (callback) =>
    self = this
    contentPath = path.join @base, @contentPath()
    metadataPath = path.join @base, @metadataPath()
    mkdirp this.dirPath(), (error) ->
      return callback error if error
      work = [
        async.apply fs.writeFile, contentPath, self.content
        async.apply fs.writeFile, metadataPath, JSON.stringify(self.metadata())
      ]
      async.parallel work, callback

module.exports = {Post, leadZero, slug}
