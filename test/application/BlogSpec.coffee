config = require "../../config"
expect = require("chai").expect
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
    expect($("title").text()).to.match /walkabout/i

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
    expect($("li.post span.date")).not.to.be.empty
    date = $("li.post span.date").last().html()
    expect(date).to.match /Mar 14, 2009/

describe "a blog post preview", ->

  it "should convert markdown to HTML", (done) ->
    done()
