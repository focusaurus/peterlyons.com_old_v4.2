@title = 'Peter Lyons: Web Development, Startups, Music'
p '''Hi, I'm Pete Lyons.
I'm a web developer and startup enthusiast.
I play the saxophone. I climb rocks.'''
p 'My current areas of interest in the technology/startup arena are as follows.'
ul ->
  li ->
    a href: 'http://c2.com/cgi/wiki?SovereignComputing', ->
      'Sovereign Computing'
  li 'Tools for clear thinking'
  li 'Startups that enable consumers to bypass dated institutions'

img src: '/photos/summer_2009/013_climbing_lake_mary.jpg', title: 'Climbing at Lake Mary in Estes Park',
  alt: 'Climbing at Lake Mary in Estes Park'
p ->
  a href: 'http://validator.w3.org/check?uri=referer', ->
    img src: 'http://www.w3.org/Icons/valid-xhtml10-blue',
      alt: 'Valid XHTML 1.0 Transitional', height: '31', width: '88'
  a href: 'http://jigsaw.w3.org/css-validator/', ->
    img style: 'border:0;width:88px;height:31px',
      src: 'http://jigsaw.w3.org/css-validator/images/vcss-blue',
      alt: 'Valid CSS!'
