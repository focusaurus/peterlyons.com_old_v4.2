describe 'the photos page', ->

  it 'should have the photo surrounding structure', ->
    expect($('title').text()).toContain 'Photo Gallery | Peter Lyons'
    expect($('h1.galleryName')).toBeDefined()
    expect($('img#photo')).toBeDefined()
    expect($('a#prev')).toBeDefined()
    expect($('a#next')).toBeDefined()
    expect($('h1#caption')).toBeDefined()
    expect($('body script').text()).toContain 'showPhoto'
    expect($('h2')).toBeDefined()