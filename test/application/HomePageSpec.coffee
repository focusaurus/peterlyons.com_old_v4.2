describe 'the home page', ->

  it 'should have the intro material', ->
    $('title').text().should.equal 'Peter Lyons: node.js coder for hire'
