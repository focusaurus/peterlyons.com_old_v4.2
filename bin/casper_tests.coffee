casper = require("casper").create()

casper.start 'http://localhost:9400/career?test=load'
# , ->
#     @.echo(@.getTitle());
#     #title = @.evaluate ->
#     #    return $("title").text()
#     #@.echo "TITLE IS: " + title
#     @.waitForSelector "#mocha"
#     , ->
#       @.echo "casper found #mocha selector"
#       @.evaluate ->
#         mocha.run().on "end", ->
#           $("body").prepend("#mocha_end")
#       , ->
#         @.echo "ERROR"
#       , 15000
#       @.waitForSelector "#mocha_end", ->
#         @.echo "#mocha_end found"
#       , ->
#         @.echo "error waiting for #mocha_end"
#         @.echo arguments
#       , 1500
#       #@.echo result.total
#       # @.echo "Failures: " + @.evaluate ->
#       #   $("#mocha #stats .failures").text().match(/\d+/)[0]
#       # @.echo "Successes: " + @.evaluate ->
#       #   $("#mocha #stats .passes").text().match(/\d+/)[0]
#       # @.echo "Duration: " + @.evaluate ->
#       #   $("#mocha #stats .duration").text().match(/\d+/)[0]
#       @.echo result.failures
#       @.echo result.total
#     , ->
#         @.echo "timeout waiting for #mocha selector"
casper.then ->
  @.echo @.getTitle()
casper.then ->
  @.waitForSelector "header", ->
    @.echo "Casper found header"
casper.then ->
  @.evaluate ->
    $("body .content").prepend("<div id='mocha'></div>")
casper.then ->
  result = @.evaluate ->
    mocha.run().on "end", ->
      $("body").prepend("<div id='mocha_end'></div>")
  @.echo result.total
casper.then ->
  @.waitForSelector "#mocha_end", ->
    @.echo "casper found #mocha_end"
casper.then ->
  @.echo @.evaluate ->
    $("#mocha").html()
casper.run()
