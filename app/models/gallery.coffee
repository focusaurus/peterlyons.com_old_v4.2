config = require '../../server_config'
dateRE = /20\d{6}/
monthNames = ['January', 'February', 'March', 'April', 'May', 'June', 'July', \
 'August', 'September', 'October', 'November', 'December']

exports.Gallery = class Gallery
  constructor: (@dirName, @displayName, @startDate) ->
    if not @displayName
      @displayName = (@dirName or '').replace /_/g, ' '
      @displayName = @displayName.slice(0, 1).toUpperCase() + \
        @displayName.slice(1)
      match = @displayName.match dateRE
      if match
        dateString = match[0]
        year = dateString.slice(0, 4)
        monthIndex = new Number(dateString.slice(4, 6)) - 1
        day = new Number(dateString.slice(6, 8))
        formattedDate =  monthNames[monthIndex] + ' ' + day + ', ' + year
        @displayName = @displayName.replace dateRE, formattedDate
        
          
  
  
