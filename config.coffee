exports.site = "localhost"
exports.port = 9400
exports.appURI = "/app"
exports.staticDir = "./public"
exports.photos =
  photoURI: "/photos/"
  galleryURI: exports.appURI + "/photos"
  galleryDir:  exports.staticDir + "/photos"
  thumbExtension: "-TN.jpg"
  extension: ".jpg"
  galleryDataPath: "./app/data/galleries.json"
exports.env =
  production: false
  staging: false
  testing: false
  development: false
#Set the current environment to true in the env object
currentEnv = process.env.NODE_ENV or "development"
exports.env[currentEnv] = true

exports.baseURL = "http://#{exports.site}:#{exports.port}"
if exports.env.production
  exports.site = "peterlyons.com"
  exports.baseURL = "http://#{exports.site}"
