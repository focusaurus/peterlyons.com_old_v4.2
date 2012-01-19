exports.site = "localhost"
exports.port = 9400
exports.baseURL = "http://#{exports.site}:#{exports.port}"
exports.appURI = "/app"
#Listen on all IPs in dev/test (for testing from other machines),
#But loopback in staging/prod since nginx listens on the routed interface
exports.loopback = false
exports.staticDir = "./public"
exports.photos =
  photoURI: "/photos/"
  galleryURI: exports.appURI + "/photos"
  galleryDir:  exports.staticDir + "/photos"
  thumbExtension: "-TN.jpg"
  extension: ".jpg"
  galleryDataPath: "./app/data/galleries.json"
exports.tests = false
#exports.env =
#  production: false
#  staging: false
#  testing: false
#  development: false
#Set the current environment to true in the env object
#exports.env[currentEnv] = true

switch process.env.NODE_ENV
  when "production"
    exports.site = "peterlyons.com"
    exports.baseURL = "http://#{exports.site}"
    exports.loopback = true
  when "staging"
    exports.site = "staging.peterlyons.com"
    exports.baseURL = "http://#{exports.site}"
    exports.loopback = true
  when "development", "test"
    exports.tests = true
