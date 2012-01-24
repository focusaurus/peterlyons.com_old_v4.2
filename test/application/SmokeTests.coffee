config = require "../../config"
request = require "superagent"
expect = require("chai").expect

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
    #BUGBUG debug this#["/app/photos?gallery=jacksonville_2008&photo=012_matthew_the_kitten", /Matthew/]
    ["/problog/2009/03/announcing-petes-points", /professional/]
    ["/persblog/2007/10/petes-travel-adventure-2007-begins-friday-october-5th", /Alitalia/]
  ]

  for test in testConfigs
    makeTest = (test) ->
      it "#{test[0]} should match #{test[1]}", (done) ->
        request.get config.baseURL + test[0], (res) ->
          expect(res.status).to.equal 200
          expect(res.text).match test[1]
          done()
    makeTest test