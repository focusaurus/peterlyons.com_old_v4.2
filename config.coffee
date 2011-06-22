exports.site = 'peterlyons.com'
exports.port = 9400
exports.appURI = '/app'
exports.staticDir = './public'
exports.photos =
  photoURI: '/photos/'
  galleryURI: exports.appURI + '/photos'
  galleryDir:  exports.staticDir + '/photos'
  defaultGallery: 'spring_2011'
  thumbExtension: '-TN.jpg'
  extension: '.jpg'
  galleryDataPath: './app/data/galleries.json'
exports.env =
  production: false
  staging: false
  testing: false
  development: false
#Set the current environment to true in the env object
currentEnv = process.env.NODE_ENV or 'development'
exports.env[currentEnv] = true


