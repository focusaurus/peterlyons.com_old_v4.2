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
page "home", "Peter Lyons: node.js coder for hire"
page "bands", "My Bands"
page "bigclock", "BigClock: a full screen desktop clock in java"
page "career", "My Career"
page "code_conventions", "Code Conventions"
page "error404", "Not Found"
page "error502", "Oops"
page "favorites", "Favorite Musicians"
page "hackstars", "TechStars, pick me!"
page "leveling_up.md", "Leveling Up: Career Advancement for Software Developers"
page "linkzie", "Linkzie: A Simple Bookmark Manager"
page "oberlin", "Music from Oberlin"
page "practices.md", "Practices and Values"
page "smartears", "SmartEars: Ear Training Software"
page "stacks.md", "Technology Stacks"
page "web_prog.md", "Web Programming Concepts for Non-Programmers"
homePage = new Page "home", {title: pages[0].locals.title}
homePage.URI = ""
pages.push homePage

route = (app, page) ->
  app.get "/" + page.URI, (req) ->
    page.render req

setup = (app) ->
  #Route all the simple static pages
  route app, page for page in pages

module.exports = {setup, Page}
