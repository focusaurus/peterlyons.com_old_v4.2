########## Global Setup Stuff ##########
baseURL = 'http://localhost:9400'
verbose = phantom.args[0] in ["--verbose", "-v"]
phantom.injectJs 'interact.coffee'
phantom.injectJs 'waitfor.coffee'

########## Shared Helper Functions ##########
out = (message) ->
  if verbose
    console.log '>...' + message
failureCount = 0
countFailure = (count) ->
  failureCount += count

runJasmine = ->
  window.result = done: false, output: ['\n']
  if not jasmine?
    console.log 'The app server looks to NOT BE RUNNING. START IT.'
    window.result.done = true
    return

  status = (result, description, indent='') ->
    marker = ''
    if result.skipped
      label = 'SKIPPED'
    else if result.failedCount == 0
      label = 'passed'
    else
      label = 'FAILED' 
      marker = '*'
    return "#{marker}#{indent}#{description}: #{label} " + \
      "(#{result.passedCount} pass/#{result.failedCount} fail)\n"

  output = window.result.output
  jasmine.getEnv().currentRunner().finishCallback = ->
    runner = jasmine.getEnv().currentRunner()
    for suite in runner.suites()
      output.push status(suite.results(), suite.description)
      for spec in suite.specs()
        output.push status(spec.results(), spec.description, '  ')
      output.push '\n'

    results = runner.results()
    output.push status(results, 'All Jasmine Tests')
    window.result.failedCount = results.failedCount
    window.result.done = true
  jasmine.getEnv().execute()

jasmineWrapper = (page, next) ->
  page.evaluate runJasmine
  waitFor ->
    page.evaluate ->
      window.result?.done or false
  , ->
    result = page.evaluate ->
      window.result
    out result.output.join('')
    countFailure result.failedCount
    if result.failedCount == 0
      console.log '.'
    else
      console.log 'F'
    next()
  , 5 * 1000

page = new WebPage()
page.settings.loadImages = false
page.settings.loadPlugins = false
page.onConsoleMessage = (message) ->
  if message.indexOf("Unsafe JavaScript") == 0
    return
  out message
class Test
  constructor: (@URI, @toRun) ->
########## Custom Logic ##########
pagesToTest = [
  '/'
  '/home'
  '/bands'
  '/bigclock'
  '/career'
  '/hackstars'
  '/linkzie'
  '/smartears'
  '/oberlin'
  '/code_conventions'
  '/favorites'
  '/error404'
  '/error502'
  '/app/photos'
  '/leveling_up'
]
actions = []
pagesToTest.map (URI) ->
  actions.push new Test(baseURL + URI + '?test=1', jasmineWrapper)

window.interact page, actions, verbose, ->
  if failureCount == 0
    console.log "all tests passed"
  else
    console.log "*** #{failureCount} TESTS FAILED ***"
  phantom.exit failureCount
