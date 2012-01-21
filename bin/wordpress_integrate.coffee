config = require "../config"
fs = require "fs"
jade = require "jade"

render = (templatePath, locals={}) ->
  template = fs.readFileSync templatePath, "utf8"
  renderFunc = jade.compile template, {filename: templatePath}
  renderFunc locals

templatePath = __dirname + "/../app/templates/includes/footer.jade"
locals =
  config: config
footerHTML = render templatePath, locals
html =  "\n</div><!-- main_content -->\n#{footerHTML}</body></html>\n"
outputPath = __dirname + "/../public/persblog/wp-content/themes/fluid-blue/footer.php"
fs.writeFileSync(outputPath , html, "utf8")
console.log "generated #{outputPath}"

templatePath = __dirname + "/../app/templates/layout.jade"
locals =
  body: "TEST1"
  config: config
  post: false
  specURIs: []
  testCSS: []
  wordpress: true
html = render templatePath, locals
marker = "</nav>"
deleteIndex = html.indexOf marker
html = html.slice 0, deleteIndex + marker.length
html = html + "\n"
outputPath = __dirname + "/../public/persblog/wp-content/themes/fluid-blue/header_boilerplate.php"
console.log "generated #{outputPath}"
fs.writeFileSync outputPath, html, "utf8"
