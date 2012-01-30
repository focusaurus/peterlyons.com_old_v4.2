config = require "../../config"
expect = require("chai").expect
jsdom = require "jsdom"

describe "a blog post page", ->
  $ = null

  before (done) ->
    jsdom.env config.baseURL + "/persblog/2012/01/san-francisco-walkabout", [config.jqueryURL], (error, jsWindow) ->
      $ = jsWindow.$
      done()

  it "should have the post title", ->
    expect($("title").text()).to.match /walkabout/i

  it "should process a flickr tag", ->
    expect($("flickr")).to.be.empty
    expect($("object")).not.to.be.empty

  it "should have disqus comments", ->
    expect($("#disqus_thread")).not.to.be.empty
