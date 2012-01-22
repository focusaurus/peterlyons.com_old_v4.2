describe 'the Leveling Up article', ->

  it 'should have the proper content', ->
    html = $('#main_content').html()
    ids = [
      '#pillar1'
      '#pillar2'
      '#pillar3'
      'Operating System'
      'interacting'
      'thousands'
      ]
    for id in ids
      expect(id).toBeDefined("HTML should have contained ##{id} element")
