describe 'Pages using the main layout', ->

  it "should have the basic layout HTML", ->
    expect($('title').text()).toContain 'Peter Lyons'
    expect($('#header')).toBeDefined()
    expect($('#header img')).toBeDefined()
    expect($('#site_nav a')).toBeDefined()
    expect($('#main_content')).toBeDefined()

describe 'The wordpress header_boilerplate.php output', ->
  
  it 'should include embedded PHP markup', ->
    done = done: false
    $.get '/home?wordpress=1', (html, textStatus, response) ->
      expect(response.status).toEqual 200
      expect(html).toContain '<?php bloginfo'
      expect(html).toContain '<?php get_sidebar'
      expect(html).toContain 'WORDPRESS HEADER BOILERPLATE'
      done.done = true
    waitsFor ->
      done.done