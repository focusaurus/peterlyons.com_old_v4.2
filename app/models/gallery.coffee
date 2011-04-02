config = require '../../server_config'
dateREs =
  YMD: /20\d{6}/
  MDY: /(\d{1,2})\/(\d{1,2})\/((\d{2}|\d{4}))/
  MDYY: /(\d{1,2})\/(\d{1,2})\/((\d{2}|\d{4}))/
  timestamp: /\d{13}/
monthNames = ['January', 'February', 'March', 'April', 'May', 'June', 'July', \
 'August', 'September', 'October', 'November', 'December']

exports.Gallery = class Gallery
  constructor: (@dirName, @displayName, @startDate) ->
    #avoid errors if these are undefined
    @dirName = @dirName or ''
    @displayName = @displayName or @dirName

    #First, check the dirname for an embedded date like 20110402
    match = @dirName.match dateREs.YMD
    if match
      #Store this in case there is no supplied startDate
      startDateFromDirName = @parseDate match[0]
      formattedDate =  monthNames[startDateFromDirName.getMonth()] + ' ' + \
        startDateFromDirName.getDate() + ', ' + \
        startDateFromDirName.getFullYear()
      #If we found a date in YYYYMMDD format, change it to January 1, 1970
      @displayName = @displayName.replace dateREs.YMD, formattedDate

    #Do some basic prettification of the display name
    @displayName = @displayName.replace /_/g, ' '
    @displayName = @displayName.slice(0, 1).toUpperCase() + \
      @displayName.slice(1)

    if @startDate
      #OK, let's make sure the data is kosher
      #Simplest to convert to a string initially
      dateString = '' + @startDate
      @startDate = @parseDate @startDate
      if @startDate
        @startDate = @startDate.getTime()
    else
      if startDateFromDirName
        @startDate = startDateFromDirName.getTime()

  parseDate: (dateString) ->
    if dateREs.YMD.test dateString
      return @parseYMD dateString
    else if dateREs.MDY.test dateString
      return @parseDMY dateString
    else if dateREs.timestamp.test dateString
      return new Date(new Number(dateString))
    else
      return null

  parseYMD: (ymd) ->
    year = new Number(ymd.slice(0, 4))
    month = new Number(ymd.slice(4, 6))
    day = new Number(ymd.slice(6, 8))
    return new Date(year, month - 1, day)

  parseDMY: (dmy) ->
    [month, day, year] = dmy.split('/')
    if year.length == 2
      year = new Date().getFullYear().toString().slice(0, 2) + year
    return new Date(new Number(year), new Number(month) - 1, new Number(day))
