/**
 * Module dependencies.
 */
var connect = require('connect');

/**
 * noop middleware.
 */

function noop(req, res, next) {
  next();
}

/**
 * Urlencoded:
 *
 *  Handle text/* mime-type requests,
 *  providing the full request body as a String at `req.body`.
 *
 * Options:
 *
 *    - `limit`  byte limit disabled by default
 *
 * @param {Object} options
 * @return {Function}
 * @api public
 */

exports = module.exports = function(options){
  options = options || {};

  var limit = options.limit
    ? connect.limit(options.limit)
    : noop;

  return function plain(req, res, next) {
    if (req._body) return next();
    req.body = req.body || '';

    if (!connect.utils.hasBody(req)) return next();

    // check Content-Type
    if (connect.utils.mime(req).indexOf('text/') !== 0) return next();

    // flag as parsed
    req._body = true;

    // parse
    limit(req, res, function(err){
      if (err) return next(err);
      var buf = '';
      req.setEncoding('utf8');
      req.on('data', function(chunk){ buf += chunk });
      req.on('end', function(){
        req.body = buf.length ? buf : '';
        next();
      });
    });
  };
};
