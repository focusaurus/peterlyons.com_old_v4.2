path = require "path"

class Page
  constructor: (@view, @locals={}) ->
    if @view.indexOf(".") >= 0
      @URI = @view.split(".")[0]
    else
      @URI = @view
      @view = "#{@view}.jade"

  render: (req) =>
    if @locals.title? and @locals.title.indexOf("Peter Lyons") < 0
      @locals.title = @locals.title + " | Peter Lyons"
    req.res.render @view, {locals: @locals}

pages = []
page = (URI, title) ->
  pages.push new Page(URI, {title})
page 'home', 'Peter Lyons: node.js coder for hire'
page 'bands', 'My Bands'
page 'bigclock', 'BigClock: a full screen desktop clock in java'
page 'career', 'My Career'
page 'hackstars', 'TechStars, pick me!'
page 'linkzie', 'Linkzie: A Simple Bookmark Manager'
page 'smartears', 'SmartEars: Ear Training Software'
page 'oberlin', 'Music from Oberlin'
page 'code_conventions', 'Code Conventions'
page 'favorites', 'Favorite Musicians'
page 'error404', 'Not Found'
page 'error502', 'Oops'
page "leveling_up.md", "Leveling Up: Career Advancement for Software Developers"
page "web_prog.md", "Web Programming Concepts for Non-Programmers"
page "practices.md", "Practices and Values"
page "stacks.md", "Technology Stacks"
homePage = new Page 'home', {title: pages[0].locals.title}
homePage.URI = ''
pages.push homePage

route = (app, page) ->
  app.get "/" + page.URI, (req) ->
    page.render req

setup = (app) ->
  #Route all the simple static pages
  route app, page for page in pages

module.exports = {setup, Page}
