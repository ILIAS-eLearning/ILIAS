

goog.require('ol.structs.LRUCache');


describe('ol.structs.LRUCache', function() {

  var lruCache;

  function fillLRUCache(lruCache) {
    lruCache.set('a', 0);
    lruCache.set('b', 1);
    lruCache.set('c', 2);
    lruCache.set('d', 3);
  }

  beforeEach(function() {
    lruCache = new ol.structs.LRUCache();
  });

  describe('empty cache', function() {
    it('has size zero', function() {
      expect(lruCache.getCount()).to.eql(0);
    });
    it('has no keys', function() {
      expect(lruCache.getKeys()).to.eql([]);
    });
    it('has no values', function() {
      expect(lruCache.getValues()).to.eql([]);
    });
  });

  describe('populating', function() {
    it('returns the correct size', function() {
      fillLRUCache(lruCache);
      expect(lruCache.getCount()).to.eql(4);
    });
    it('contains the correct keys in the correct order', function() {
      fillLRUCache(lruCache);
      expect(lruCache.getKeys()).to.eql(['d', 'c', 'b', 'a']);
    });
    it('contains the correct values in the correct order', function() {
      fillLRUCache(lruCache);
      expect(lruCache.getValues()).to.eql([3, 2, 1, 0]);
    });
    it('reports which keys are contained', function() {
      fillLRUCache(lruCache);
      expect(lruCache.containsKey('a')).to.be.ok();
      expect(lruCache.containsKey('b')).to.be.ok();
      expect(lruCache.containsKey('c')).to.be.ok();
      expect(lruCache.containsKey('d')).to.be.ok();
      expect(lruCache.containsKey('e')).to.not.be();
    });
  });

  describe('getting the oldest key', function() {
    it('moves the key to newest position', function() {
      fillLRUCache(lruCache);
      lruCache.get('a');
      expect(lruCache.getCount()).to.eql(4);
      expect(lruCache.getKeys()).to.eql(['a', 'd', 'c', 'b']);
      expect(lruCache.getValues()).to.eql([0, 3, 2, 1]);
    });
  });

  describe('getting a key in the middle', function() {
    it('moves the key to newest position', function() {
      fillLRUCache(lruCache);
      lruCache.get('b');
      expect(lruCache.getCount()).to.eql(4);
      expect(lruCache.getKeys()).to.eql(['b', 'd', 'c', 'a']);
      expect(lruCache.getValues()).to.eql([1, 3, 2, 0]);
    });
  });

  describe('getting the newest key', function() {
    it('maintains the key to newest position', function() {
      fillLRUCache(lruCache);
      lruCache.get('d');
      expect(lruCache.getCount()).to.eql(4);
      expect(lruCache.getKeys()).to.eql(['d', 'c', 'b', 'a']);
      expect(lruCache.getValues()).to.eql([3, 2, 1, 0]);
    });
  });

  describe('replacing value of a key', function() {
    it('moves the key to newest position', function() {
      fillLRUCache(lruCache);
      lruCache.replace('b', 4);
      expect(lruCache.getCount()).to.eql(4);
      expect(lruCache.getKeys()).to.eql(['b', 'd', 'c', 'a']);
      expect(lruCache.getValues()).to.eql([4, 3, 2, 0]);
    });
  });

  describe('setting a new value', function() {
    it('adds it as the newest value', function() {
      fillLRUCache(lruCache);
      lruCache.set('e', 4);
      expect(lruCache.getKeys()).to.eql(['e', 'd', 'c', 'b', 'a']);
      expect(lruCache.getValues()).to.eql([4, 3, 2, 1, 0]);
    });
  });

  describe('setting an existing value', function() {
    it('raises an exception', function() {
      fillLRUCache(lruCache);
      expect(function() {
        lruCache.set('a', 0);
      }).to.throwException();
    });
  });

  describe('disallowed keys', function() {
    it('setting raises an exception', function() {
      expect(function() {
        lruCache.set('constructor', 0);
      }).to.throwException();
      expect(function() {
        lruCache.set('hasOwnProperty', 0);
      }).to.throwException();
      expect(function() {
        lruCache.set('isPrototypeOf', 0);
      }).to.throwException();
      expect(function() {
        lruCache.set('propertyIsEnumerable', 0);
      }).to.throwException();
      expect(function() {
        lruCache.set('toLocaleString', 0);
      }).to.throwException();
      expect(function() {
        lruCache.set('toString', 0);
      }).to.throwException();
      expect(function() {
        lruCache.set('valueOf', 0);
      }).to.throwException();
    });
    it('getting returns false', function() {
      expect(lruCache.containsKey('constructor')).to.not.be();
      expect(lruCache.containsKey('hasOwnProperty')).to.not.be();
      expect(lruCache.containsKey('isPrototypeOf')).to.not.be();
      expect(lruCache.containsKey('propertyIsEnumerable')).to.not.be();
      expect(lruCache.containsKey('toLocaleString')).to.not.be();
      expect(lruCache.containsKey('toString')).to.not.be();
      expect(lruCache.containsKey('valueOf')).to.not.be();
    });
  });

  describe('popping a value', function() {
    it('returns the least-recent-used value', function() {
      fillLRUCache(lruCache);
      expect(lruCache.pop()).to.eql(0);
      expect(lruCache.getCount()).to.eql(3);
      expect(lruCache.containsKey('a')).to.not.be();
      expect(lruCache.pop()).to.eql(1);
      expect(lruCache.getCount()).to.eql(2);
      expect(lruCache.containsKey('b')).to.not.be();
      expect(lruCache.pop()).to.eql(2);
      expect(lruCache.getCount()).to.eql(1);
      expect(lruCache.containsKey('c')).to.not.be();
      expect(lruCache.pop()).to.eql(3);
      expect(lruCache.getCount()).to.eql(0);
      expect(lruCache.containsKey('d')).to.not.be();
    });
  });

  describe('#peekFirstKey()', function() {
    it('returns the newest key in the cache', function() {
      var cache = new ol.structs.LRUCache();
      cache.set('oldest', 'oldest');
      cache.set('oldish', 'oldish');
      cache.set('newish', 'newish');
      cache.set('newest', 'newest');
      expect(cache.peekFirstKey()).to.eql('newest');
    });

    it('works if the cache has one item', function() {
      var cache = new ol.structs.LRUCache();
      cache.set('key', 'value');
      expect(cache.peekFirstKey()).to.eql('key');
    });

    it('throws if the cache is empty', function() {
      var cache = new ol.structs.LRUCache();
      expect(function() {
        cache.peekFirstKey();
      }).to.throwException();
    });
  });

  describe('peeking at the last value', function() {
    it('returns the last key', function() {
      fillLRUCache(lruCache);
      expect(lruCache.peekLast()).to.eql(0);
    });
    it('throws an exception when the cache is empty', function() {
      expect(function() {
        lruCache.peekLast();
      }).to.throwException();
    });
  });

  describe('peeking at the last key', function() {
    it('returns the last key', function() {
      fillLRUCache(lruCache);
      expect(lruCache.peekLastKey()).to.eql('a');
    });
    it('throws an exception when the cache is empty', function() {
      expect(function() {
        lruCache.peekLastKey();
      }).to.throwException();
    });
  });

  describe('#remove()', function() {
    it('removes an item from the cache', function() {
      var cache = new ol.structs.LRUCache();
      cache.set('oldest', 'oldest');
      cache.set('oldish', 'oldish');
      cache.set('newish', 'newish');
      cache.set('newest', 'newest');

      cache.remove('oldish');
      expect(cache.getCount()).to.eql(3);
      expect(cache.getValues()).to.eql(['newest', 'newish', 'oldest']);
    });

    it('works when removing the oldest item', function() {
      var cache = new ol.structs.LRUCache();
      cache.set('oldest', 'oldest');
      cache.set('oldish', 'oldish');
      cache.set('newish', 'newish');
      cache.set('newest', 'newest');

      cache.remove('oldest');
      expect(cache.getCount()).to.eql(3);
      expect(cache.peekLastKey()).to.eql('oldish');
      expect(cache.getValues()).to.eql(['newest', 'newish', 'oldish']);
    });

    it('works when removing the newest item', function() {
      var cache = new ol.structs.LRUCache();
      cache.set('oldest', 'oldest');
      cache.set('oldish', 'oldish');
      cache.set('newish', 'newish');
      cache.set('newest', 'newest');

      cache.remove('newest');
      expect(cache.getCount()).to.eql(3);
      expect(cache.peekFirstKey()).to.eql('newish');
      expect(cache.getValues()).to.eql(['newish', 'oldish', 'oldest']);
    });

    it('returns the removed item', function() {
      var cache = new ol.structs.LRUCache();
      var item = {};
      cache.set('key', item);

      var returned = cache.remove('key');
      expect(returned).to.be(item);
    });

    it('throws if the key does not exist', function() {
      var cache = new ol.structs.LRUCache();
      cache.set('foo', 'foo');
      cache.set('bar', 'bar');

      var call = function() {
        cache.remove('bam');
      };
      expect(call).to.throwException();
    });
  });

  describe('clearing the cache', function() {
    it('clears the cache', function() {
      fillLRUCache(lruCache);
      lruCache.clear();
      expect(lruCache.getCount()).to.eql(0);
      expect(lruCache.getKeys()).to.eql([]);
      expect(lruCache.getValues()).to.eql([]);
    });
  });

});
