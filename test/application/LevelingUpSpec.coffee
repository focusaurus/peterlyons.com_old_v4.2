describe "the Leveling Up article", ->

  it "should have the proper content", ->
    ids = [
      "#pillar1"
      "#pillar2"
      "#pillar3"
      ]
    for id in ids
      $(id).should.not.be.empty
    html = $("body").html().toLowerCase()
    for phrase in ["operating system", "thousaunds"]
      html.indexOf(phrase).should.be.above -2
