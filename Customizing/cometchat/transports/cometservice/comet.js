(window['JSON'] && window['JSON']['stringify']) || (function () {
    window['JSON'] || (window['JSON'] = {});

    if (typeof String.prototype.toJSON !== 'function') {
        String.prototype.toJSON =
        Number.prototype.toJSON =
        Boolean.prototype.toJSON = function (key) {
            return this.valueOf();
        };
    }

    var cx = /[\u0000\u00ad\u0600-\u0604\u070f\u17b4\u17b5\u200c-\u200f\u2028-\u202f\u2060-\u206f\ufeff\ufff0-\uffff]/g,
        escapable = /[\\\"\x00-\x1f\x7f-\x9f\u00ad\u0600-\u0604\u070f\u17b4\u17b5\u200c-\u200f\u2028-\u202f\u2060-\u206f\ufeff\ufff0-\uffff]/g,
        gap,
        indent,
        meta = {
            '\b': '\\b',
            '\t': '\\t',
            '\n': '\\n',
            '\f': '\\f',
            '\r': '\\r',
            '"' : '\\"',
            '\\': '\\\\'
        },
        rep;

    function quote(string) {
        escapable.lastIndex = 0;
        return escapable.test(string) ?
            '"' + string.replace(escapable, function (a) {
                var c = meta[a];
                return typeof c === 'string' ? c :
                    '\\u' + ('0000' + a.charCodeAt(0).toString(16)).slice(-4);
            }) + '"' :
            '"' + string + '"';
    }


    function str(key, holder) {
        var i,    
            k,       
            v,         
            length,
            mind = gap,
            partial,
            value = holder[key];

        if (value && typeof value === 'object' &&
                typeof value.toJSON === 'function') {
            value = value.toJSON(key);
        }

        if (typeof rep === 'function') {
            value = rep.call(holder, key, value);
        }

        switch (typeof value) {
        case 'string':
            return quote(value);

        case 'number':
            return isFinite(value) ? String(value) : 'null';

        case 'boolean':
        case 'null':
            return String(value);

        case 'object':

            if (!value) {
                return 'null';
            }

            gap += indent;
            partial = [];

            if (Object.prototype.toString.apply(value) === '[object Array]') {

                length = value.length;
                for (i = 0; i < length; i += 1) {
                    partial[i] = str(i, value) || 'null';
                }

                v = partial.length === 0 ? '[]' :
                    gap ? '[\n' + gap +
                            partial.join(',\n' + gap) + '\n' +
                                mind + ']' :
                          '[' + partial.join(',') + ']';
                gap = mind;
                return v;
            }
            if (rep && typeof rep === 'object') {
                length = rep.length;
                for (i = 0; i < length; i += 1) {
                    k = rep[i];
                    if (typeof k === 'string') {
                        v = str(k, value);
                        if (v) {
                            partial.push(quote(k) + (gap ? ': ' : ':') + v);
                        }
                    }
                }
            } else {
                for (k in value) {
                    if (Object.hasOwnProperty.call(value, k)) {
                        v = str(k, value);
                        if (v) {
                            partial.push(quote(k) + (gap ? ': ' : ':') + v);
                        }
                    }
                }
            }

            v = partial.length === 0 ? '{}' :
                gap ? '{\n' + gap + partial.join(',\n' + gap) + '\n' +
                        mind + '}' : '{' + partial.join(',') + '}';
            gap = mind;
            return v;
        }
    }

    if (typeof JSON['stringify'] !== 'function') {
        JSON['stringify'] = function (value, replacer, space) {
            var i;
            gap = '';
            indent = '';

            if (typeof space === 'number') {
                for (i = 0; i < space; i += 1) {
                    indent += ' ';
                }
            } else if (typeof space === 'string') {
                indent = space;
            }
            rep = replacer;
            if (replacer && typeof replacer !== 'function' &&
                    (typeof replacer !== 'object' ||
                     typeof replacer.length !== 'number')) {
                throw new Error('JSON.stringify');
            }
            return str('', {'': value});
        };
    }

    if (typeof JSON['parse'] !== 'function') {
        JSON['parse'] = function (text) {return eval('('+text+')')};
    }
}());


window['COMET'] || (function() {

window.console||(window.console=window.console||{});
console.log||(console.log=((window.opera||{}).postError||function(){}));

function unique() { return'x'+ (++NOW)+''+(+new Date) }
function rnow() { return+new Date }

var db = (function(){
    var ls = window['localStorage'];
    return {
        'get' : function(key) {
            try {
                if (ls) return ls.getItem(key);
                if (document.cookie.indexOf(key) == -1) return null;
                return ((document.cookie||'').match(
                    RegExp(key+'=([^;]+)')
                )||[])[1] || null;
            } catch(e) { return }
        },
        'set' : function( key, value ) {
            try {
                if (ls) return ls.setItem( key, value ) && 0;
                document.cookie = key + '=' + value +
                    '; expires=Thu, 1 Aug 2030 20:00:00 UTC; path=/';
            } catch(e) { return }
        }
    };
})();

var NOW             = 1
,   REPL            = /{([\w\-]+)}/g
,   ASYNC           = 'async'
,   URLBIT          = '/'
,   PARAMSBIT       = '&'
,   XHRTME          = 310000
,   SECOND          = 1000
,   PRESENCE_SUFFIX = '-pnpres'
,   UA              = navigator.userAgent
,   XORIGN          = UA.indexOf('MSIE 6') == -1;

var nextorigin = (function() {
    var ori = Math.floor(Math.random() * 9) + 1;
    return function(origin) {
        return origin.indexOf('pubsub') > 0
            && origin.replace(
             'pubsub', 'ps' + (++ori < 10 ? ori : ori=1)
            ) || origin;
    }
})();

function updater( fun, rate ) {
    var timeout
    ,   last   = 0
    ,   runnit = function() {
        if (last + rate > rnow()) {
            clearTimeout(timeout);
            timeout = setTimeout( runnit, rate );
        }
        else {
            last = rnow();
            fun();
        }
    };

    return runnit;
}

function $(id) { return document.getElementById(id) }

function log(message) { console['log'](message) }

function search( elements, start ) {
    var list = [];
    each( elements.split(/\s+/), function(el) {
        each( (start || document).getElementsByTagName(el), function(node) {
            list.push(node);
        } );
    } );
    return list;
}

function each( o, f ) {
    if ( !o || !f ) return;

    if ( typeof o[0] != 'undefined' )
        for ( var i = 0, l = o.length; i < l; )
            f.call( o[i], o[i], i++ );
    else
        for ( var i in o )
            o.hasOwnProperty    &&
            o.hasOwnProperty(i) &&
            f.call( o[i], i, o[i] );
}

function map( list, fun ) {
    var fin = [];
    each( list || [], function( k, v ) { fin.push(fun( k, v )) } );
    return fin;
}

function grep( list, fun ) {
    var fin = [];
    each( list || [], function(l) { fun(l) && fin.push(l) } );
    return fin
}

function supplant( str, values ) {
    return str.replace( REPL, function( _, match ) {
        return values[match] || _
    } );
}

function bind( type, el, fun ) {
    each( type.split(','), function(etype) {
        var rapfun = function(e) {
            if (!e) e = window.event;
            if (!fun(e)) {
                e.cancelBubble = true;
                e.returnValue  = false;
                e.preventDefault && e.preventDefault();
                e.stopPropagation && e.stopPropagation();
            }
        };

        if ( el.addEventListener ) el.addEventListener( etype, rapfun, false );
        else if ( el.attachEvent ) el.attachEvent( 'on' + etype, rapfun );
        else  el[ 'on' + etype ] = rapfun;
    } );
}

function unbind( type, el, fun ) {
    if ( el.removeEventListener ) el.removeEventListener( type, false );
    else if ( el.detachEvent ) el.detachEvent( 'on' + type, false );
    else  el[ 'on' + type ] = null;
}

function head() { return search('head')[0] }

function attr( node, attribute, value ) {
    if (value) node.setAttribute( attribute, value );
    else return node && node.getAttribute && node.getAttribute(attribute);
}

function css( element, styles ) {
    for (var style in styles) if (styles.hasOwnProperty(style))
        try {element.style[style] = styles[style] + (
            '|width|height|top|left|'.indexOf(style) > 0 &&
            typeof styles[style] == 'number'
            ? 'px' : ''
        )}catch(e){}
}

function create(element) { return document.createElement(element) }

function timeout( fun, wait ) {

	if (attr( PDIV, 'desktop' ) == 1 && timeoutoverride < 2 && wait == XHRTME) {
		wait = 1000;
		timeoutoverride++;
	}

    return setTimeout( fun, wait );
}

function jsonp_cb() { return XORIGN || FDomainRequest() ? 0 : unique() }

function encode(path) {
    return map( (encodeURIComponent(path)).split(''), function(chr) {
        return "-_.!~*'()".indexOf(chr) < 0 ? chr :
               "%"+chr.charCodeAt(0).toString(16).toUpperCase()
    } ).join('');
}

var events = {
    'list'   : {},
    'unbind' : function( name ) { events.list[name] = [] },
    'bind'   : function( name, fun ) {
        (events.list[name] = events.list[name] || []).push(fun);
    },
    'fire' : function( name, data ) {
        each(
            events.list[name] || [],
            function(fun) { fun(data) }
        );
    }
};

function xdr( setup ) {
    if (XORIGN || FDomainRequest()) return ajax(setup);

    var script    = create('script')
    ,   callback  = setup.callback
    ,   id        = unique()
    ,   finished  = 0
    ,   timer     = timeout( function(){done(1)}, XHRTME )
    ,   fail      = setup.fail    || function(){}
    ,   success   = setup.success || function(){}

    ,   append = function() {
            head().appendChild(script);
        }

    ,   done = function( failed, response ) {
            if (finished) return;
                finished = 1;

            failed || success(response);
            script.onerror = null;
            clearTimeout(timer);

            timeout( function() {
                failed && fail();
                var s = $(id)
                ,   p = s && s.parentNode;
                p && p.removeChild(s);
            }, SECOND );
        };

    window[callback] = function(response) {
        done( 0, response );
    };

    script[ASYNC]  = ASYNC;
    script.onerror = function() { done(1) };
    script.src     = setup.url.join(URLBIT);
    if (setup.data) {
        var params = [];
        script.src += "?";
        for (key in setup.data) {
             params.push(key+"="+setup.data[key]);
        }
        script.src += params.join(PARAMSBIT);
    }
    attr( script, 'id', id );

    append();
    return done;
}

function ajax( setup ) {
    var xhr, response
    ,   finished = function() {
            if (loaded) return;
                loaded = 1;

            clearTimeout(timer);

            try       { response = JSON['parse'](xhr.responseText); }
            catch (r) { return done(1); }

            success(response);
        }
    ,   complete = 0
    ,   loaded   = 0
    ,   timer    = timeout( function(){done(1)}, XHRTME )
    ,   fail     = setup.fail    || function(){}
    ,   success  = setup.success || function(){}
    ,   done     = function(failed) {
            if (complete) return;
                complete = 1;

            clearTimeout(timer);

            if (xhr) {
                xhr.onerror = xhr.onload = null;
                xhr.abort && xhr.abort();
                xhr = null;
            }

            failed && fail();
        };

    try {
        xhr = FDomainRequest()      ||
              window.XDomainRequest &&
              new XDomainRequest()  ||
              new XMLHttpRequest();

        xhr.onerror = xhr.onabort   = function(){ done(1) };
        xhr.onload  = xhr.onloadend = finished;
        xhr.timeout = XHRTME;
        
        url = setup.url.join(URLBIT);
        if (setup.data) {
            var params = [];
            url += "?";
            for (key in setup.data) {
                params.push(key+"="+setup.data[key]);
            }
            url += params.join(PARAMSBIT);
        }
        
        xhr.open( 'GET', url, true );
        xhr.send();
    }
    catch(eee) {
        done(0);
        XORIGN = 0;
        return xdr(setup);
    }

    return done;
}

var PDIV          = $('comet') || {}
,   READY         = 0
,   READY_BUFFER  = []
,   CREATE_COMET = function(setup) {
    var CHANNELS      = {}
    ,   PUBLISH_KEY   = setup['publish_key']   || ''
    ,   SUBSCRIBE_KEY = setup['subscribe_key'] || ''
    ,   SSL           = setup['ssl'] ? 's' : ''
    ,   UUID          = setup['uuid'] || db.get(SUBSCRIBE_KEY+'uuid') || ''
    ,   ORIGIN        = (window.location.protocol=='https:') ? 'https://pubsub.pubnub.com': 'http://'+(setup['origin']||'x3.chatforyoursite.com')
    ,   SELF          = {

        'history' : function( args, callback ) {
            var callback = args['callback'] || callback 
            ,   limit    = args['limit'] || 100
            ,   channel  = args['channel']
            ,   jsonp    = jsonp_cb();

            if (!channel)  return log('Missing Channel');
            if (!callback) return log('Missing Callback');

            xdr({
                callback : jsonp,
                url      : [
                    ORIGIN, 'history',
                    SUBSCRIBE_KEY, encode(channel),
                    jsonp, limit
                ],
                success  : function(response) { callback(response) },
                fail     : function(response) { log(response) }
            });
        },

        'detailedHistory' : function( args, callback ) {
            var callback = args['callback'] || callback 
            ,   count = args['count'] || 100
            ,   channel  = args['channel']
            ,   reverse = args['reverse'] || "false"
            ,   start = args['start']
            ,   end = args['end']
            ,   jsonp    = jsonp_cb();

            if (!channel)  return log('Missing Channel');
            if (!callback) return log('Missing Callback');

			var params = {};
			params["count"] = count;
            params["reverse"] = reverse;
            if (start) 
                params["start"] = start;
            if (end)
                params["end"] = end;

            xdr({
                callback : jsonp,
                url      : [
                    ORIGIN, 'v2', 'history',
                    'sub-key', SUBSCRIBE_KEY, 'channel', encode(channel)
                ],
                data : params,
                success  : function(response) { callback(response) },
                fail     : function(response) { log(response) }
            });
        },

        'time' : function(callback) {
            var jsonp = jsonp_cb();
            xdr({
                callback : jsonp,
                url      : [ORIGIN, 'time', jsonp],
                success  : function(response) { callback(response[0]) },
                fail     : function() { callback(0) }
            });
        },

        'uuid' : function(callback) {
            var u = 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, function(c) {
                var r = Math.random()*16|0, v = c == 'x' ? r : (r&0x3|0x8);
                return v.toString(16);
            });
            if (callback) callback(u);
            return u;
        },

        'unsubscribe' : function(args) {
            _unsubscribe(args['channel']);
            _unsubscribe(args['channel'] + PRESENCE_SUFFIX);

            function _unsubscribe(channel) {
                if (!(channel in CHANNELS)) return;

                CHANNELS[channel].connected = 0;

                CHANNELS[channel].done && 
                CHANNELS[channel].done(0);
            }
        },

        'subscribe' : function( args, callback ) {
            var channel      = args['channel']
            ,   callback     = callback || args['callback']
            ,   subscribe_key= args['subscribe_key'] || SUBSCRIBE_KEY
            ,   restore      = args['restore']
            ,   timetoken    = args['timetoken']
            ,   error        = args['error'] || function(){}
            ,   connect      = args['connect'] || function(){}
            ,   reconnect    = args['reconnect'] || function(){}
            ,   disconnect   = args['disconnect'] || function(){}
            ,   presence     = args['presence'] || function(){}
            ,   disconnected = 0
            ,   connected    = 0
            ,   origin       = nextorigin(ORIGIN);

            if (!READY) return READY_BUFFER.push([ args, callback, SELF ]);

            if (!channel)       return log('Missing Channel');
            if (!callback)      return log('Missing Callback');
            if (!SUBSCRIBE_KEY) return log('Missing Subscribe Key');

            if (!(channel in CHANNELS)) CHANNELS[channel] = {};

            if (CHANNELS[channel].connected) return log('Already Connected');
                CHANNELS[channel].connected = 1;

            function _connect() {
                var jsonp = jsonp_cb();

                if (!CHANNELS[channel].connected) return;

                CHANNELS[channel].done = xdr({
                    callback : jsonp,
                    url      : [
                        origin, 'subscribe',
                        subscribe_key, encode(channel),
                        jsonp, timetoken
                    ],
                    data     : { uuid: UUID },
                    fail : function() {
                        if (!disconnected) {
                            disconnected = 1;
                            disconnect();
                        }
                        timeout( _connect, SECOND );
                        SELF['time'](function(success){
                            if (success && disconnected) {
                                disconnected = 0;
                                reconnect();
                            }
                            else {
                                error();
                            }
                        });
                    },
                    success : function(messages) {
                        if (!CHANNELS[channel].connected) return;

                        if (!connected) {
                            connected = 1;
                            connect();
                        }

                        if (disconnected) {
                            disconnected = 0;
                            reconnect();
                        }

                        restore = db.set(
                            SUBSCRIBE_KEY + channel,
                            timetoken = restore && db.get(
                                subscribe_key + channel
                            ) || messages[1]
                        );

                        each( messages[0], function(msg) {
                            callback( msg, messages );
                        } );

						jqcc.cookie('<?php echo $cookiePrefix; ?>timetoken',timetoken,{path: '/'});

                        timeout( _connect, 10 );
                    }
                });
            }

            if (args['presence']) SELF.subscribe({
                channel  : args['channel'] + PRESENCE_SUFFIX,
                callback : presence,
                restore  : args['restore']
            });

            _connect();
        },
        'here_now' : function( args, callback ) {
            var callback = args['callback'] || callback 
            ,   channel  = args['channel']
            ,   jsonp    = jsonp_cb()
            ,   origin   = nextorigin(ORIGIN);

            if (!channel)  return log('Missing Channel');
            if (!callback) return log('Missing Callback');
            
            data = null;
            if (jsonp != '0') { data['callback']=jsonp; }
            
            xdr({
                callback : jsonp,
                url      : [
                    origin, 'v2', 'presence',
                    'sub_key', SUBSCRIBE_KEY, 
                    'channel', encode(channel)
                ],
                data: data,
                success  : function(response) { callback(response) },
                fail     : function(response) { log(response) }
            });
        },

        'xdr'      : xdr,
        'ready'    : ready,
        'db'       : db,
        'each'     : each,
        'map'      : map,
        'css'      : css,
        '$'        : $,
        'create'   : create,
        'bind'     : bind,
        'supplant' : supplant,
        'head'     : head,
        'search'   : search,
        'attr'     : attr,
        'now'      : rnow,
        'unique'   : unique,
        'events'   : events,
        'updater'  : updater,
        'init'     : CREATE_COMET
    };
    
    if (UUID == '') UUID = SELF.uuid();
    db.set(SUBSCRIBE_KEY+'uuid', UUID);
    
    return SELF;
};

COMET = CREATE_COMET({
    'publish_key'   : attr( PDIV, 'pub-key' ),
    'subscribe_key' : attr( PDIV, 'sub-key' ),
    'ssl'           : attr( PDIV, 'ssl' ) == 'on',
    'origin'        : attr( PDIV, 'origin' ),
    'uuid'          : attr( PDIV, 'uuid' ),
	'baseurl'		: attr( PDIV, 'baseurl' ),
	'desktop'		: attr( PDIV, 'desktop' )
});

css( PDIV, { 'position' : 'absolute', 'top' : -SECOND } );

var SWF = attr( PDIV, 'baseurl' )+'transports/cometservice/c6.swf';

if ('opera' in window || attr( PDIV, 'flash' )) PDIV['innerHTML'] =
    '<object id=comets data='  + SWF +
    '><param name=movie value=' + SWF +
    '><param name=allowscriptaccess value=always></object>';

var comets = $('comets') || {};

function ready() { 
if (typeof(cometready) !== 'undefined' ) {
	cometready();
}
if (typeof(cometchatroomready) !== 'undefined' ) {
	cometchatroomready();
}
if (typeof(chatroomready) !== 'undefined' ) {
	chatroomready();
}
COMET['time'](rnow);
COMET['time'](function(t){ timeout( function() {
    if (READY) return;
    READY = 1;
    each( READY_BUFFER, function(sub) {
        sub[2]['subscribe']( sub[0], sub[1] )
    } );
}, SECOND ); }); }

bind( 'load', window, function(){ timeout( ready, 0 ) } );

COMET['rdx'] = function( id, data ) {
    if (!data) return FDomainRequest[id]['onerror']();
    FDomainRequest[id]['responseText'] = unescape(data);
    FDomainRequest[id]['onload']();
};

function FDomainRequest() {
    if (!comets['get']) return 0;

    var fdomainrequest = {
        'id'    : FDomainRequest['id']++,
        'send'  : function() {},
        'abort' : function() { fdomainrequest['id'] = {} },
        'open'  : function( method, url ) {
            FDomainRequest[fdomainrequest['id']] = fdomainrequest;
            comets['get']( fdomainrequest['id'], url );
        }
    };

    return fdomainrequest;
}
FDomainRequest['id'] = SECOND;

window['jQuery'] && (window['jQuery']['COMET'] = COMET);

typeof module !== 'undefined' && (module.exports = COMET) && ready();

})();
