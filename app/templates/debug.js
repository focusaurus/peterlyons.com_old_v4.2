var repl = require("repl");
var fs = require('fs');
var coffeekup = require('coffeekup');
var template = fs.readFileSync("template.coffee").toString();
var server = repl.start();
server.context.ck = coffeekup;
server.context.template = template;
server.context.fs = fs
server.context.r = function() {
  return fs.readFileSync("template.coffee").toString();
};
server.context.cap = function(value) {
  return value.toUpperCase();
};
