describe 'Pages using the main layout', ->

  it "should have the basic layout HTML", ->
    $('header').should.not.be.empty
    $('title').text().indexOf('Peter Lyons').should.be.above -1
    $('header img').should.not.be.empty
    $('header nav a').should.not.be.empty
    $('body .content').should.not.be.empty
