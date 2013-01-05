config = require "../../config"
assert = require("chai").assert

{loadPage} = require "../TestUtils"
request = require "superagent"

describe "a blog post page", ->
  $ = null

  before (done) ->
    URL = config.baseURL + "/persblog/2012/01/san-francisco-walkabout"
    loadPage URL, (dom)->
      $ = dom
      done()

  it "should have the post title", ->
    assert.match $("title").text(), /walkabout/i

  it "should process a flickr tag", ->
    assert.lengthOf $("flickr"), 0
    assert $("object").length > 0

  it "should process a youtube tag", ->
    assert.lengthOf $("youtube"), 0
    assert $("object").length > 0

  it "should have disqus comments", ->
    assert.lengthOf $("#disqus_thread"), 1

describe "a blog index page", ->
  $ = null

  before (done) ->
    loadPage config.baseURL + "/problog", (dom)->
      $ = dom
      done()

  it "should have nicely formatted dates", ->
    assert $("li.post span.date").length > 0
    date = $("li.post span.date").last().html()
    assert.match date, /Mar 14, 2009/

describe "a blog post preview", ->

  it "should convert markdown to HTML", (done) ->
    done()
