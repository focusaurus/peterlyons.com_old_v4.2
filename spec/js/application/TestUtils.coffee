config = require '../../../server_config'
zombie = require 'zombie'

exports.testPage = (URI, spec, callback)->
  browser = new zombie.Browser()
  browser.visit 'http://localhost:' + config.port + URI, (err, browser, status)->
    if err and err.message.toLowerCase().indexOf('connection refused') >= 0
      spec.fail(config.appName + " is not running. Please start the server.")
    callback(err, browser, status)
