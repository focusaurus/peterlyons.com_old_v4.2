@title = "BigClock: a full screen desktop clock in java | Peter Lyons"
p ->
  '''BigClock is a simple desktop clock utility that fills up the entire
window with a clock, so you can get a clock of any size. It allows you
to choose your own colors and time format.
You can launch it through Java Web Start with the
link below, or '''
  a href: "./dist/bigclock.jar", ->
  '''download the jar file directly and run it with the command 'java -jar bigclock.jar'
using JRE 1.3 or newer.'''
p ->
  a href: "./dist/bigclock.jnlp", ->
    'Start BigClock!'
text webstart
