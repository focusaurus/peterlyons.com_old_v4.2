describe 'Pages using the main layout', ->

  it "should have the basic layout HTML", ->
    expect($('title').text()).toContain 'Peter Lyons'
    expect($('#header')).toBeDefined()
    expect($('#header img')).toBeDefined()
    expect($('#site_nav a')).toBeDefined()
    expect($('#main_content')).toBeDefined()
