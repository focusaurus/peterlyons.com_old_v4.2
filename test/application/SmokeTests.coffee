request = require "superagent"
config = require "../../config"
describe 'smoke tests for most pages on the site', ->

  testConfigs = [
    ["/", /node\.js/]
    ["/career", /Opsware/]
    ["/stacks", /CoffeeScript/]
    ["/practices", /Craftsmanship/]
    ["/bands", /Afronauts/]
    ["/bigclock", /clock/]
    ["/linkzie", /bookmark/]
    ["/code_conventions", /readability/]
    ["/error404", /404 error page/]
    ["/error502", /nap/]
    ["/leveling_up", /Pillar 1/]
    ["/web_prog", /PHP/]
    ["/hackstars", /TechStars/]
    ["/smartears", /interval/]
    ["/oberlin", /Edison/]
    ["/favorites", /Imogen/]
    ["/problog", /Pete's Points/]
    ["/persblog", /travel/]
    ["/app/photos", /Gallery/]
    ["/problog/2009/03/announcing-petes-points", /professional/]
    ["/persblog/2007/10/petes-travel-adventure-2007-begins-friday-october-5th", /Alitalia/]
  ]

  for test in testConfigs
    makeTest = (test) ->
      it "#{test[0]} should match #{test[1]}", (done) ->
        request.get config.baseURL + test[0], (res) ->
          res.status.should.equal 200
          res.text.should.match test[1]
          done()
    makeTest test

describe "in-page test integration", ->
  it "should include the test libraries and init code", (done) ->
    request.get config.baseURL + "?test=start", (res) ->
      res.status.should.equal 200
      #res.text.should.match /mocha\.js/
      #res.text.should.match /mocha\.css/
      #res.text.should.match /chai\.js/
      done()

describe "the main layout", ->
  text = null
  before (done) ->
    request.get config.baseURL, (res) ->
      text = res.text
      done()
  it "should have the google fonts", (done) ->
    text.should.match /fonts\.googleapis\.com/
    done()

  it "should have the footer", (done) ->
    text.should.match /<footer>/i
    done()
