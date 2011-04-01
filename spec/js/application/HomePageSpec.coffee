utils = require './TestUtils'

describe 'the home page', ->

  it 'should have the intro material', ->
    utils.testPage '/home', this, (err, browser, status)->
      if err
        self.fail(err.message)
        asyncSpecDone()
        return
      expect(browser.text('title')).toEqual 'Peter Lyons: Web Development, Startups, Music | Peter Lyons'
      expect(browser.querySelector('#header')).toBeDefined()
      expect(browser.text('#header img')).toBeDefined()
      expect(browser.querySelector('#site_nav a')).toBeDefined()
      expect(browser.querySelector('#main_content')).toBeDefined()
      asyncSpecDone()
    asyncSpecWait()
