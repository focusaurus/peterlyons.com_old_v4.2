describe 'the home page', ->

  it 'should have the intro material', ->
    expect($('title').text()).toEqual 'Peter Lyons: node.js coder for hire'
