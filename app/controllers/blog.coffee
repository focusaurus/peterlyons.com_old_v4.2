_ = require "underscore"
fs = require "fs"
glob = require "glob"
pages = require "./pages"
path = require "path"

class Post extends pages.Page
  constructor: (@view, @title='', @locals={}, @specs=[]) ->
    match = @view.match /(\w+)\/(\d{4,})\/(\d{2})\/([\w-_]+)/
    @URI = "/" + match[0]
    @metadataPath = @view.replace ".md", ".json"

  loadMetadata: (callback) =>
    self = this
    path.exists @metadataPath, (exists) ->
      return if not exists
      fs.readFile self.metadataPath, "utf8", (error, jsonString) ->
        return callback(error) if error
        metadata = JSON.parse jsonString
        self.publish_date = new Date(metadata.publish_date)
        callback()

setup = (app) ->
  postGlob = path.normalize(__dirname + "/../posts/problog/**/*.md")
  glob.glob postGlob, (error, paths) ->
    throw error if error
    for path in paths
      post = new Post path
      post.loadMetadata (error) ->
        throw error if error
        console.log post.view, post.URI, post.publish_date
        app.get post.URI, (req, res) ->
          post.render req, res

module.exports = {setup}
