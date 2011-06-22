describe 'the Leveling Up article', ->

  it 'should have the proper content', ->
    html = $('#main_content').html()
    phrases = [
      'Pillar 1'
      'Pillar 2'
      'Pillar 3'
      'Operating System'
      'interacting'
      'thousands'
      ]
    for phrase in phrases
      expect(html).toContain phrase, "HTML should have contained #{phrase}"