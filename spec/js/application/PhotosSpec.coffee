utils = require './TestUtils'

describe 'the photos page', ->

  it 'should have the photo surrounding structure', ->
    self = this
    utils.testPage '/photos', this, (err, browser, status)->
      if err
        self.fail(err.message)
        asyncSpecDone()
        return
      expect(browser.text('title')).toContain 'Photo Gallery | Peter Lyons'
      expect(browser.querySelector('h1.galleryName')).toBeDefined()
      expect(browser.querySelector('img#photo')).toBeDefined()
      expect(browser.querySelector('a#prev')).toBeDefined()
      expect(browser.querySelector('a#next')).toBeDefined()
      expect(browser.querySelector('h1#caption')).toBeDefined()
      expect(browser.text('body script')).toContain 'showPhoto'
      expect(browser.querySelectorAll('h2')).toBeDefined()
      asyncSpecDone()
    asyncSpecWait()
