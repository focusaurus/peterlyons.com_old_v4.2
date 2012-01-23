config = require "../../config"
expect = require("chai").expect
jsdom = require "jsdom"

describe "the photos page", ->
  $ = null

  before (done) ->
    jsdom.env config.baseURL + "/app/photos", [config.jqueryURL], (error, jsWindow) ->
      $ = jsWindow.$
      done()

  it "should have the photo surrounding structure", ->
    for selector in ["h1.galleryName", "figure", "figcaption", "#nextPrev", "a.thumbnail"]
      expect($(selector)).not.to.be.empty

  it "should have the correct title", ->
    expect($("title").text()).to.include "Photo Gallery | Peter Lyons"

  it "should have the data attributes needed", ->
    expect($("body script").text()).to.include "data-fullSizeURI"
