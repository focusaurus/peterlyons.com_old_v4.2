describe "the photos page", ->

  it "should have the photo surrounding structure", ->
    $("title").text().indexOf("Photo Gallery | Peter Lyons").should.be.above -1
    $("body script").text().indexOf("data-fullSizeURI").should.be.above -1
    for selector in ["h1.galleryName", "figure", "figcaption", "#nextPrev", "a.thumbnail"]
      $(selector).should.not.be.empty
