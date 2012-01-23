config = require "../../config"
expect = require("chai").expect
jsdom = require "jsdom"

describe "the home page", ->
  $ = null

  before (done) ->
    jsdom.env config.baseURL, [config.jqueryURL], (error, jsWindow) ->
      $ = jsWindow.$
      done()

  it 'should have the intro material', ->
    for selector in ["section#intro", "section#chops", "section#writing"]
      expect($(selector)).not.to.be.empty
