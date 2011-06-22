window.reversePhases = ->
  phases = []
  parent = $('div.phase').parent()
  $('div.phase').each (index, div)->
    $(div).remove()
    phases.unshift $(div)
  parent.append phase for phase in phases