describe 'the home page', ->

  it 'should have the intro material', ->
    expect($('title').text()).toEqual 'Peter Lyons: Web Development, Startups, Music | Peter Lyons'