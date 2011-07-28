pageNum = 1
window.interact = (page, actions, verbose=true, doneCallback) ->
  header = (status) ->
    return if not verbose
    console.log "Page #{pageNum}: #{status}. " + \
      page.evaluate ->
        document.location.href + " - " + document.title
    pageNum++

  if Array.isArray actions
    #Simple linear sequence of actions
    page.onLoadFinished = (status) ->
      header status
      #Call interact again--pass only the functions that
      #haven't been invoked yet
      actions[0].toRun page, ->
        if actions.length <= 1
          doneCallback()
        else
          interact page, actions.slice(1), verbose, doneCallback
    #Call the first callback in the list. It should result in page
    #reload so the
    #above onLoadFinished gets called
    page.open actions[0].URI
  else
    #Object style interaction. Allows loops, dynamic logic, etc
    page.onLoadFinished = (status) ->
      header status
      actions.next(page, actions)
    actions.next(page, actions)
