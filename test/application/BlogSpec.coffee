config = require "../../config"
expect = require("chai").expect
{loadPage} = require "../TestUtils"

describe "a blog post page", ->
  $ = null

  before (done) ->
    URL = config.baseURL + "/persblog/2012/01/san-francisco-walkabout"
    loadPage URL, (dom)->
      $ = dom
      done()

  it "should have the post title", ->
    expect($("title").text()).to.match /walkabout/i

  it "should process a flickr tag", ->
    expect($("flickr")).to.be.empty
    expect($("object")).not.to.be.empty

  it "should process a youtube tag", ->
    expect($("flickr")).to.be.empty
    expect($("object")).not.to.be.empty
  it "should have disqus comments", ->
    expect($("#disqus_thread")).not.to.be.empty

describe "a blog index page", ->
  $ = null

  before (done) ->
    loadPage config.baseURL + "/problog", (dom)->
      $ = dom
      done()

  it "should have nicely formatted dates", ->
    expect($("li.post span.date")).not.to.be.empty
    date = $("li.post span.date").last().html()
    expect(date).to.match /Mar 14, 2009/
