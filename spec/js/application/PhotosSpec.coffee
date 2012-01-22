describe 'the photos page', ->

  it 'should have the photo surrounding structure', ->
    expect($('title').text()).toContain 'Photo Gallery | Peter Lyons'
    expect($('body script').text()).toContain 'data-fullSizeURI'
    for selector in ['h1.galleryName', 'figure', "figcaption", "#nextPrev", "a.thumbnail"]
      expect($(selector)).toExist()
