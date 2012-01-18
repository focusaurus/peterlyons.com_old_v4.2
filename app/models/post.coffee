_ = require "underscore"
fs = require "fs"
path = require "path"

leadZero = (value) -> if value > 9 then "#{value}" else "0#{value}"

class Post
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

module.exports = {Post, leadZero}
