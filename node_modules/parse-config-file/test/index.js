'use strict'

require('should')

var path = require('path')
var parse = require('..')
var fs = require('fs')

var PATH = {
  json: fs.readFileSync(path.resolve('test/fixtures/json'), 'utf8'),
  cson: fs.readFileSync(path.resolve('test/fixtures/cson'), 'utf8'),
  yaml: fs.readFileSync(path.resolve('test/fixtures/yaml'), 'utf8')
}

function expected (data) {
  Object.keys(data).length.should.be.equal(2)
}

describe('load-conf-file', function () {
  Object.keys(PATH).forEach(function (filetype) {
    it(filetype, function () {
      var data = parse(PATH[filetype])
      expected(data)
    })
  })
})
