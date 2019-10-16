

goog.require('ol');
goog.require('ol.net');

describe('ol.net', function() {

  describe('jsonp()', function() {
    var head = document.getElementsByTagName('head')[0];
    var origAppendChild = head.appendChild;
    var origCreateElement = document.createElement;
    var origSetTimeout = setTimeout;
    var key, removeChild;

    function createCallback(url, done) {
      removeChild = sinon.spy();
      var callback = function(data) {
        expect(data).to.be(url + key);
        expect(removeChild.called).to.be(true);
        done();
      };
      key = 'olc_' + ol.getUid(callback);
      return callback;
    }

    beforeEach(function() {
      var element = {};
      document.createElement = function(arg) {
        if (arg == 'script') {
          return element;
        } else {
          return origCreateElement.apply(document, arguments);
        }
      };
      head.appendChild = function(el) {
        if (el === element) {
          element.parentNode = {
            removeChild: removeChild
          };
          origSetTimeout(function() {
            window[key](element.src);
          }, 0);
        } else {
          origAppendChild.apply(head, arguments);
        }
      };
      setTimeout = function(fn, time) {
        origSetTimeout(fn, 100);
      };
    });

    afterEach(function() {
      document.createElement = origCreateElement;
      head.appendChild = origAppendChild;
      setTimeout = origSetTimeout;
    });

    it('appends callback param to url, cleans up after call', function(done) {
      ol.net.jsonp('foo', createCallback('foo?callback=', done));
    });
    it('appends correct callback param to a url with query', function(done) {
      var callback = createCallback('http://foo/bar?baz&callback=', done);
      ol.net.jsonp('http://foo/bar?baz', callback);
    });
    it('calls errback when jsonp is not executed, cleans up', function(done) {
      head.appendChild = function(element) {
        element.parentNode = {
          removeChild: removeChild
        };
      };
      function callback() {
        expect.fail();
      }
      function errback() {
        expect(window[key]).to.be(undefined);
        expect(removeChild.called).to.be(true);
        done();
      }
      ol.net.jsonp('foo', callback, errback);
    });
    it('accepts a custom callback param', function(done) {
      var callback = createCallback('foo?mycallback=', done);
      ol.net.jsonp('foo', callback, undefined, 'mycallback');
    });

  });

});
