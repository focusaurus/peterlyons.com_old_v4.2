staticbase = "http://peterlyons.com"
wkbase = "/app"
navLinks = []
navLinks.push({uri: "/home.html", label: "Home"})
navLinks.push({uri: "/problog", label: "Blog (Technology)"})
navLinks.push({uri: "/persblog", label: "Blog (Personal)"})
navLinks.push({uri: "#{wkbase}/photos", label: "Photo Gallery"})
navLinks.push({uri: "/oberlin.html", label: "Sounds from Oberlin"})
navLinks.push({uri: "/code_conventions.html", label: "Code Conventions"})
navLinks.push({uri: "/smartears.html", label: "SmartEars"})
navLinks.push({uri: "/bigclock.html", label: "BigClock"})

doctype 5
html lang: "en", ->
  head ->
    meta charset: "utf-8"
    meta "http-equiv": "Content-Type", content: "text/html;charset=utf-8"
    meta name: "keywords", content: "peter lyons, pete lyons, web development, startups, music, sax, saxophone, saxophonist, sunny daze, confunktion junction, oberlin, smartears, smart ears"
    meta name:"author", content: "Peter Lyons"
    meta name: "description", content: "The web site for Peter Lyons, Web Developer and Musician"
    meta name: "copyright", content: "2001, Peter Lyons"
    link rel: "stylesheet", href: "#{staticbase}/peterlyons.css", type:"text/css"
    link rel: "openid.server", href: "http://www.livejournal.com/openid/server.bml"
    link rel: "openid.delegate", href: "http://focusaurus.livejournal.com/"
    title "#{@title} | Peter Lyons" if @title?
  body ->
    nav ->
      for l in navLinks
        a href: l.uri, -> l.label
    div class: "body", ->
      @body
