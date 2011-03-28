exports.site = 'peterlyons.com'
exports.port = 9400
exports.appURI = '/app'
exports.staticDir = __dirname + '/overlay/var/www/' + exports.site
exports.photos = 
  photoURI: '/photos/'
  galleryURI: exports.appURI + '/photos'
  galleryDir:  exports.staticDir + '/photos'
  defaultGallery: 'winter_2010'
  thumbExtension: '-TN.jpg'
  extension: '.jpg'

