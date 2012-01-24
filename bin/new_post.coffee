#!/usr/bin/env coffee
child_process = require "child_process"
commander = require "commander"
fs = require "fs"
path = require "path"
Post = require("../app/models/post").Post
commander
  .version("0.0.1")
  .option("-f, --format [format]", "Post file format [md]", "md")
  .option("-b, --blog [persblog]", "blog name [persblog]", "persblog")
  .option("-t, --title [title]", "Post title")
  .parse(process.argv);

post = new Post commander.blog, commander.title, new Date(), commander.format

post.base = path.join __dirname, "..", "app", "posts"
metadata = JSON.stringify(post.metadata()) + "\n"
fs.writeFile path.join(post.base, post.metadataPath()), metadata, "utf8", (error) ->
  throw error if error
path.exists post.viewPath(), (exists) ->
  if not exists
    fs.writeFile post.viewPath(), "\n", "utf8", (error) ->
      throw error if error
  child_process.exec "subl #{post.viewPath()}", ->
