config = require '../../../server_config'
http = require 'http'
utils = require './TestUtils'

describe 'Pages using the main layout', ->
  URIs = ['/', '/home', '/home?wordpress=1', '/bands', '/bigclock', '/career', \
    '/hackstars', '/linkzie', '/smartears', '/oberlin', '/code_conventions', \
    '/favorites', '/error404', '/error502']
    
  for URI in URIs
    it "'#{URI} should have the basic layout HTML", ->
      self = this
      utils.testPage URI, this, (err, browser, status)->
        if err
          self.fail(err.message)
          asyncSpecDone()
          return
        expect(browser.text('title')).toContain 'Peter Lyons'
        expect(browser.querySelector('#header')).toBeDefined()
        expect(browser.text('#header img')).toBeDefined()
        expect(browser.querySelector('#site_nav a')).toBeDefined()
        expect(browser.querySelector('#main_content')).toBeDefined()
        asyncSpecDone()
      asyncSpecWait()
      
describe 'The wordpress header_boilerplate.php output', ->
  #zombie can't deal with embedded PHP due to the '?' chars,
  #So we need to do a lower level HTTP get and test the HTML more simply
  it 'should include embedded PHP markup', ->
    self = this
    options =
      host: 'localhost',
      port: config.port,
      path: '/home?wordpress=1'
    
    trans = http.get options, (res)->
      expect(res.statusCode).toEqual 200
      chunks = []
      res.on 'data', (chunk)->
        chunks.push chunk
      res.on 'end', ->
        html = chunks.join ''
        expect(html).toContain '<?php bloginfo'
        expect(html).toContain '<?php get_sidebar'
        expect(html).toContain 'WORDPRESS HEADER BOILERPLATE'
        asyncSpecDone()
    trans.on 'error', (error)->
      self.fail(error)
    asyncSpecWait()
