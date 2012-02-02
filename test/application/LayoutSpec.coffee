config = require "../../config"
expect = require("chai").expect
jsdom = require "jsdom"
_ = require "underscore"

describe "the main layout", ->
  $ = null

  before (done) ->
    jsdom.env config.baseURL, [config.jqueryURL], (error, jsWindow) ->
      $ = jsWindow.$
      done()

  it "should have the google fonts",->
    hrefs = $("link[rel=stylesheet]").map (index, elem) -> $(elem).attr("href")
    found = _.some hrefs, (href) ->
      href.indexOf("fonts.googleapis.com") >= 0
    expect(found)

  it "should have the key structural elements", ->
    for selector in ["header nav a", "header img", "body .content", "footer"]
      expect($(selector)).not.to.be.empty

  it "should have the normal title", ->
    expect($("title").text()).to.eql "Peter Lyons: node.js coder for hire"
