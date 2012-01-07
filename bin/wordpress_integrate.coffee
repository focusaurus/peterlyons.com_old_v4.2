config = require "../config"
fs = require "fs"
jade = require "jade"

output = __dirname + "/../public/persblog/wp-content/themes/fluid-blue/footer.php"
footerJade = fs.readFileSync(
  __dirname + "/../app/templates/includes/footer.jade", "utf8")
func = jade.compile footerJade, {layout: false}
footerHTML = func({config})
html =  "\n</div><!-- main_content -->\n#{footerHTML}</body></html>\n"
console.log "generated #{output}"
fs.writeFileSync(output , html, "utf8")
