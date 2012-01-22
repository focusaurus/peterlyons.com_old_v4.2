#!/usr/bin/env coffee
config = require "../config"
request = require "superagent"
jsdom = require "jsdom"

jqURL = "http://localhost:#{config.port}/js/jquery.js"
jqURL = "http://code.jquery.com/jquery-1.7.min.js"
request.get "http://localhost:#{config.port}", (res) ->
  console.log res.status
  #console.log res.text.slice(0,20)
  jsdom.env res.text, [jqURL], (errors, window) ->
    #console.log errors
    #console.log window.$
