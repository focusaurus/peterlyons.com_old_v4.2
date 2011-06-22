########## Global Setup Stuff ##########
baseURL = 'http://localhost:9400'
homePage = baseURL + '/?test=1'
verbose = phantom.args[0] in ["--verbose", "-v"]

########## Shared Helper Functions ##########
out = (message) ->
  if verbose
    console.log '>...' + message

status = (result, description, indent='') ->
  marker = ''
  if result.skipped
    status = 'SKIPPED'
  else if result.failedCount == 0
    status = 'passed'
  else
    status = 'FAILED' 
    marker = '*'
  return "#{marker}#{indent}#{description}: #{status} " + \
    "(#{result.passedCount} pass/#{result.failedCount} fail)\n"

runJasmine = (callback) ->
  if not jasmine?
    console.log 'The node.js app server looks to NOT BE RUNNING. START IT.'
    phantom.exit 15
  jasmine.getEnv().currentRunner().finishCallback = () ->
    runner = jasmine.getEnv().currentRunner()
    results = runner.results()
    output = ['\n']
    if verbose
      for suite in runner.suites()
        output.push status(suite.results(), suite.description)
        for spec in suite.specs()
          output.push status(spec.results(), spec.description, '  ')
        output.push '\n'
              
    countFailure results.failedCount
    output.push status(results, 'All Jasmine Tests')
    console.log output.join ''
    callback()
  jasmine.getEnv().execute()

#This is a callback the tests invoke when they finish
runNextTest = ->
  queue = getQueue()
  if queue.length == 0
    #We're done
    out 'DONE'
    phantom.exit getFailureCount()
  else
    openNextURL()

openNextURL = () ->
  testName = getQueue()[0]
  phantom.open testFunctions[testName].URL or homePage

########## State Management Functions ##########
_getState = ->
  if phantom.state
    return JSON.parse phantom.state
  else
    return {failCount: 0, queue: []}

_setState = (state) ->
  out "Saving queue: #{state.queue} with failCount #{state.failCount}"
  phantom.state = JSON.stringify state

getQueue = ->
  return _getState().queue

setQueue = (queue) ->
  state = _getState()
  state.queue = queue
  _setState state
  return queue

countFailure = (count=1) ->
  state = _getState()
  state.failCount += count
  _setState state

getFailureCount = ->
  state = _getState()
  return state.failCount

########## Test Functions ##########
testFunctions = {}

testFunctions.home = (callback) ->
  out 'running home tests'
  runJasmine callback

testFunctions.career = (callback) ->
  runJasmine callback
testFunctions.career.URL = baseURL + '/career'

out('phantom.state is: ' + phantom.state)
switch phantom.state
  when ''
    #populate the initial test queue
    setQueue [
      'home'
      'career'
    ]
    #This kicks off the test cycle
    openNextURL()
  else
    #parse the queue JSON
    queue = getQueue()
    test = queue.shift()
    setQueue queue
    testFunc = testFunctions[test]
    URL = testFunc.URL or homePage
    out "Running test function #{test} for URL #{URL} with args #{testFunc.args}"
    args = [runNextTest]
    args.concat testFunc.arguments
    #This actually runs the test
    testFunctions[test].apply window, args
