'use strict'

var YAML = require('yaml-parser')
var CSON = require('cson-parser')

function parse (str, opts) {
  try {
    return CSON.parse(str, opts)
  } catch (err) {
    try {
      return YAML.safeLoad(str, opts)
    } catch (err) {
      throw err
    }
  }
}

module.exports = parse
