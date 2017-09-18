/*jslint evil: true, strict: false, regexp: false*/

/**
 * Provides dynamic loading for the YUI library.  It includes the dependency
 * info for the library, and will automatically pull in dependencies for
 * the modules requested.  It supports rollup files (such as utilities.js
 * and yahoo-dom-event.js), and will automatically use these when
 * appropriate in order to minimize the number of http connections
 * required to load all of the dependencies.
 *
 * @module yuiloader
 * @namespace YAHOO.util
 */

/**
 * YUILoader provides dynamic loading for YUI.
 * @class YAHOO.util.YUILoader
 * @todo
 *      version management, automatic sandboxing
 */
(function() {

    var Y = YAHOO,
        util = Y.util,
        lang = Y.lang,
        env = Y.env,
        PROV = "_provides",
        SUPER = "_supersedes",
        REQ = "expanded",
        AFTER = "_after",
        VERSION = "@VERSION@";

    // version hack for cdn testing
    // if (/VERSION/.test(VERSION)) {
        // VERSION = "2.8.2";
    // }

    var YUI = {

        dupsAllowed: {'yahoo': true, 'get': true},

        /*
         * The library metadata for the current release  The is the default
         * value for YAHOO.util.YUILoader.moduleInfo
         * @property YUIInfo
         * @static
         */
        info: {

    // 'root': '2.5.2/build/',
    // 'base': 'http://yui.yahooapis.com/2.5.2/build/',

    'root': VERSION + '/build/',
    'base': 'http://yui.yahooapis.com/' + VERSION + '/build/',

    'comboBase': 'http://yui.yahooapis.com/combo?',

    'skin': {
        'defaultSkin': 'sam',
        'base': 'assets/skins/',
        'path': 'skin.css',
        'after': ['reset', 'fonts', 'grids', 'base'],
        'rollup': 3
    },

    dupsAllowed: ['yahoo', 'get'],

    'moduleInfo': {

        'animation': {
            'type': 'js',
            'path': 'animation/animation-min.js',
            'requires': ['dom', 'event']
        },

        'autocomplete': {
            'type': 'js',
            'path': 'autocomplete/autocomplete-min.js',
            'requires': ['dom', 'event', 'datasource'],
            'optional': ['connection', 'animation'],
            'skinnable': true
        },

        'base': {
            'type': 'css',
            'path': 'base/base-min.css',
            'after': ['reset', 'fonts', 'grids']
        },

        'button': {
            'type': 'js',
            'path': 'button/button-min.js',
            'requires': ['element'],
            'optional': ['menu'],
            'skinnable': true
        },

        'calendar': {
            'type': 'js',
            'path': 'calendar/calendar-min.js',
            'requires': ['event', 'dom'],
            supersedes: ['datemath'],
            'skinnable': true
        },

        'carousel': {
            'type': 'js',
            'path': 'carousel/carousel-min.js',
            'requires': ['element'],
            'optional': ['animation'],
            'skinnable': true
        },

        'charts': {
            'type': 'js',
            'path': 'charts/charts-min.js',
            'requires': ['element', 'json', 'datasource', 'swf']
        },

        'colorpicker': {
            'type': 'js',
            'path': 'colorpicker/colorpicker-min.js',
            'requires': ['slider', 'element'],
            'optional': ['animation'],
            'skinnable': true
        },

        'connection': {
            'type': 'js',
            'path': 'connection/connection-min.js',
            'requires': ['event'],
            'supersedes': ['connectioncore']
        },

        'connectioncore': {
            'type': 'js',
            'path': 'connection/connection_core-min.js',
            'requires': ['event'],
            'pkg': 'connection'
        },

        'container': {
            'type': 'js',
            'path': 'container/container-min.js',
            'requires': ['dom', 'event'],
            // button is also optional, but this creates a circular
            // dependency when loadOptional is specified.  button
            // optionally includes menu, menu requires container.
            'optional': ['dragdrop', 'animation', 'connection'],
            'supersedes': ['containercore'],
            'skinnable': true
        },

        'containercore': {
            'type': 'js',
            'path': 'container/container_core-min.js',
            'requires': ['dom', 'event'],
            'pkg': 'container'
        },

        'cookie': {
            'type': 'js',
            'path': 'cookie/cookie-min.js',
            'requires': ['yahoo']
        },

        'datasource': {
            'type': 'js',
            'path': 'datasource/datasource-min.js',
            'requires': ['event'],
            'optional': ['connection']
        },

        'datatable': {
            'type': 'js',
            'path': 'datatable/datatable-min.js',
            'requires': ['element', 'datasource'],
            'optional': ['calendar', 'dragdrop', 'paginator'],
            'skinnable': true
        },

        datemath: {
            'type': 'js',
            'path': 'datemath/datemath-min.js',
            'requires': ['yahoo']
        },

        'dom': {
            'type': 'js',
            'path': 'dom/dom-min.js',
            'requires': ['yahoo']
        },

        'dragdrop': {
            'type': 'js',
            'path': 'dragdrop/dragdrop-min.js',
            'requires': ['dom', 'event']
        },

        'editor': {
            'type': 'js',
            'path': 'editor/editor-min.js',
            'requires': ['menu', 'element', 'button'],
            'optional': ['animation', 'dragdrop'],
            'supersedes': ['simpleeditor'],
            'skinnable': true
        },

        'element': {
            'type': 'js',
            'path': 'element/element-min.js',
            'requires': ['dom', 'event'],
            'optional': ['event-mouseenter', 'event-delegate']
        },

        'element-delegate': {
            'type': 'js',
            'path': 'element-delegate/element-delegate-min.js',
            'requires': ['element']
        },

        'event': {
            'type': 'js',
            'path': 'event/event-min.js',
            'requires': ['yahoo']
        },

        'event-simulate': {
            'type': 'js',
            'path': 'event-simulate/event-simulate-min.js',
            'requires': ['event']
        },

        'event-delegate': {
            'type': 'js',
            'path': 'event-delegate/event-delegate-min.js',
            'requires': ['event'],
            'optional': ['selector']
        },

        'event-mouseenter': {
            'type': 'js',
            'path': 'event-mouseenter/event-mouseenter-min.js',
            'requires': ['dom', 'event']
        },

        'fonts': {
            'type': 'css',
            'path': 'fonts/fonts-min.css'
        },

        'get': {
            'type': 'js',
            'path': 'get/get-min.js',
            'requires': ['yahoo']
        },

        'grids': {
            'type': 'css',
            'path': 'grids/grids-min.css',
            'requires': ['fonts'],
            'optional': ['reset']
        },

        'history': {
            'type': 'js',
            'path': 'history/history-min.js',
            'requires': ['event']
        },

         'imagecropper': {
             'type': 'js',
             'path': 'imagecropper/imagecropper-min.js',
             'requires': ['dragdrop', 'element', 'resize'],
             'skinnable': true
         },

         'imageloader': {
            'type': 'js',
            'path': 'imageloader/imageloader-min.js',
            'requires': ['event', 'dom']
         },

         'json': {
            'type': 'js',
            'path': 'json/json-min.js',
            'requires': ['yahoo']
         },

         'layout': {
             'type': 'js',
             'path': 'layout/layout-min.js',
             'requires': ['element'],
             'optional': ['animation', 'dragdrop', 'resize', 'selector'],
             'skinnable': true
         },

        'logger': {
            'type': 'js',
            'path': 'logger/logger-min.js',
            'requires': ['event', 'dom'],
            'optional': ['dragdrop'],
            'skinnable': true
        },

        'menu': {
            'type': 'js',
            'path': 'menu/menu-min.js',
            'requires': ['containercore'],
            'skinnable': true
        },

        'paginator': {
            'type': 'js',
            'path': 'paginator/paginator-min.js',
            'requires': ['element'],
            'skinnable': true
        },

        'profiler': {
            'type': 'js',
            'path': 'profiler/profiler-min.js',
            'requires': ['yahoo']
        },


        'profilerviewer': {
            'type': 'js',
            'path': 'profilerviewer/profilerviewer-min.js',
            'requires': ['profiler', 'yuiloader', 'element'],
            'skinnable': true
        },

        'progressbar': {
            'type': 'js',
            'path': 'progressbar/progressbar-min.js',
            'requires': ['element'],
            'optional': ['animation'],
            'skinnable': true
        },

        'reset': {
            'type': 'css',
            'path': 'reset/reset-min.css'
        },

        'reset-fonts-grids': {
            'type': 'css',
            'path': 'reset-fonts-grids/reset-fonts-grids.css',
            'supersedes': ['reset', 'fonts', 'grids', 'reset-fonts'],
            'rollup': 4
        },

        'reset-fonts': {
            'type': 'css',
            'path': 'reset-fonts/reset-fonts.css',
            'supersedes': ['reset', 'fonts'],
            'rollup': 2
        },

         'resize': {
             'type': 'js',
             'path': 'resize/resize-min.js',
             'requires': ['dragdrop', 'element'],
             'optional': ['animation'],
             'skinnable': true
         },

        'selector': {
            'type': 'js',
            'path': 'selector/selector-min.js',
            'requires': ['yahoo', 'dom']
        },

        'simpleeditor': {
            'type': 'js',
            'path': 'editor/simpleeditor-min.js',
            'requires': ['element'],
            'optional': ['containercore', 'menu', 'button', 'animation', 'dragdrop'],
            'skinnable': true,
            'pkg': 'editor'
        },

        'slider': {
            'type': 'js',
            'path': 'slider/slider-min.js',
            'requires': ['dragdrop'],
            'optional': ['animation'],
            'skinnable': true
        },

        'storage': {
            'type': 'js',
            'path': 'storage/storage-min.js',
            'requires': ['yahoo', 'event', 'cookie'],
            'optional': ['swfstore']
        },

         'stylesheet': {
            'type': 'js',
            'path': 'stylesheet/stylesheet-min.js',
            'requires': ['yahoo']
         },

        'swf': {
            'type': 'js',
            'path': 'swf/swf-min.js',
            'requires': ['element'],
            'supersedes': ['swfdetect']
        },

        'swfdetect': {
            'type': 'js',
            'path': 'swfdetect/swfdetect-min.js',
            'requires': ['yahoo']
        },

        'swfstore': {
            'type': 'js',
            'path': 'swfstore/swfstore-min.js',
            'requires': ['element', 'cookie', 'swf']
        },

        'tabview': {
            'type': 'js',
            'path': 'tabview/tabview-min.js',
            'requires': ['element'],
            'optional': ['connection'],
            'skinnable': true
        },

        'treeview': {
            'type': 'js',
            'path': 'treeview/treeview-min.js',
            'requires': ['event', 'dom'],
            'optional': ['json', 'animation', 'calendar'],
            'skinnable': true
        },

        'uploader': {
            'type': 'js',
            'path': 'uploader/uploader-min.js',
            'requires': ['element']
        },

        'utilities': {
            'type': 'js',
            'path': 'utilities/utilities.js',
            'supersedes': ['yahoo', 'event', 'dragdrop', 'animation', 'dom', 'connection', 'element', 'yahoo-dom-event', 'get', 'yuiloader', 'yuiloader-dom-event'],
            'rollup': 8
        },

        'yahoo': {
            'type': 'js',
            'path': 'yahoo/yahoo-min.js'
        },

        'yahoo-dom-event': {
            'type': 'js',
            'path': 'yahoo-dom-event/yahoo-dom-event.js',
            'supersedes': ['yahoo', 'event', 'dom'],
            'rollup': 3
        },

        'yuiloader': {
            'type': 'js',
            'path': 'yuiloader/yuiloader-min.js',
            'supersedes': ['yahoo', 'get']
        },

        'yuiloader-dom-event': {
            'type': 'js',
            'path': 'yuiloader-dom-event/yuiloader-dom-event.js',
            'supersedes': ['yahoo', 'dom', 'event', 'get', 'yuiloader', 'yahoo-dom-event'],
            'rollup': 5
        },

        'yuitest': {
            'type': 'js',
            'path': 'yuitest/yuitest-min.js',
            'requires': ['logger'],
            'optional': ['event-simulate'],
            'skinnable': true
        }
    }
},
        ObjectUtil: {
            appendArray: function(o, a) {
                if (a) {
                    for (var i=0; i<a.length; i=i+1) {
                        o[a[i]] = true;
                    }
                }
            },

            keys: function(o, ordered) {
                var a=[], i;
                for (i in o) {
                    if (lang.hasOwnProperty(o, i)) {
                        a.push(i);
                    }
                }

                return a;
            }
        },

        ArrayUtil: {

            appendArray: function(a1, a2) {
                Array.prototype.push.apply(a1, a2);
                /*
                for (var i=0; i<a2.length; i=i+1) {
                    a1.push(a2[i]);
                }
                */
            },

            indexOf: function(a, val) {
                for (var i=0; i<a.length; i=i+1) {
                    if (a[i] === val) {
                        return i;
                    }
                }

                return -1;
            },

            toObject: function(a) {
                var o = {};
                for (var i=0; i<a.length; i=i+1) {
                    o[a[i]] = true;
                }

                return o;
            },

            /*
             * Returns a unique array.  Does not maintain order, which is fine
             * for this application, and performs better than it would if it
             * did.
             */
            uniq: function(a) {
                return YUI.ObjectUtil.keys(YUI.ArrayUtil.toObject(a));
            }
        }
    };

    YAHOO.util.YUILoader = function(o) {

        /**
         * Internal callback to handle multiple internal insert() calls
         * so that css is inserted prior to js
         * @property _internalCallback
         * @private
         */
        this._internalCallback = null;

        /**
         * Use the YAHOO environment listener to detect script load.  This
         * is only switched on for Safari 2.x and below.
         * @property _useYahooListener
         * @private
         */
        this._useYahooListener = false;

        /**
         * Callback that will be executed when the loader is finished
         * with an insert
         * @method onSuccess
         * @type function
         */
        this.onSuccess = null;

        /**
         * Callback that will be executed if there is a failure
         * @method onFailure
         * @type function
         */
        this.onFailure = Y.log;

        /**
         * Callback that will be executed each time a new module is loaded
         * @method onProgress
         * @type function
         */
        this.onProgress = null;

        /**
         * Callback that will be executed if a timeout occurs
         * @method onTimeout
         * @type function
         */
        this.onTimeout = null;

        /**
         * The execution scope for all callbacks
         * @property scope
         * @default this
         */
        this.scope = this;

        /**
         * Data that is passed to all callbacks
         * @property data
         */
        this.data = null;

        /**
         * Node reference or id where new nodes should be inserted before
         * @property insertBefore
         * @type string|HTMLElement
         */
        this.insertBefore = null;

        /**
         * The charset attribute for inserted nodes
         * @property charset
         * @type string
         * @default utf-8
         */
        this.charset = null;

        /**
         * The name of the variable in a sandbox or script node
         * (for external script support in Safari 2.x and earlier)
         * to reference when the load is complete.  If this variable
         * is not available in the specified scripts, the operation will
         * fail.
         * @property varName
         * @type string
         */
        this.varName = null;

        /**
         * The base directory.
         * @property base
         * @type string
         * @default http://yui.yahooapis.com/[YUI VERSION]/build/
         */
        this.base = YUI.info.base;

        /**
         * Base path for the combo service
         * @property comboBase
         * @type string
         * @default http://yui.yahooapis.com/combo?
         */
        this.comboBase = YUI.info.comboBase;

        /**
         * If configured, YUI will use the the combo handler on the
         * Yahoo! CDN to pontentially reduce the number of http requests
         * required.
         * @property combine
         * @type boolean
         * @default false
         */
        // this.combine = (o && !('base' in o));
        this.combine = false;


        /**
         * Root path to prepend to module path for the combo
         * service
         * @property root
         * @type string
         * @default [YUI VERSION]/build/
         */
        this.root = YUI.info.root;

        /**
         * Timeout value in milliseconds.  If set, this value will be used by
         * the get utility.  the timeout event will fire if
         * a timeout occurs.
         * @property timeout
         * @type int
         */
        this.timeout = 0;

        /**
         * A list of modules that should not be loaded, even if
         * they turn up in the dependency tree
         * @property ignore
         * @type string[]
         */
        this.ignore = null;

        /**
         * A list of modules that should always be loaded, even
         * if they have already been inserted into the page.
         * @property force
         * @type string[]
         */
        this.force = null;

        /**
         * Should we allow rollups
         * @property allowRollup
         * @type boolean
         * @default true
         */
        this.allowRollup = true;

        /**
         * A filter to apply to result urls.  This filter will modify the default
         * path for all modules.  The default path for the YUI library is the
         * minified version of the files (e.g., event-min.js).  The filter property
         * can be a predefined filter or a custom filter.  The valid predefined
         * filters are:
         * <dl>
         *  <dt>DEBUG</dt>
         *  <dd>Selects the debug versions of the library (e.g., event-debug.js).
         *      This option will automatically include the logger widget</dd>
         *  <dt>RAW</dt>
         *  <dd>Selects the non-minified version of the library (e.g., event.js).
         * </dl>
         * You can also define a custom filter, which must be an object literal
         * containing a search expression and a replace string:
         * <pre>
         *  myFilter: &#123;
         *      'searchExp': "-min\\.js",
         *      'replaceStr': "-debug.js"
         *  &#125;
         * </pre>
         * @property filter
         * @type string|{searchExp: string, replaceStr: string}
         */
        this.filter = null;

        /**
         * The list of requested modules
         * @property required
         * @type {string: boolean}
         */
        this.required = {};

        /**
         * The library metadata
         * @property moduleInfo
         */
        this.moduleInfo = lang.merge(YUI.info.moduleInfo);

        /**
         * List of rollup files found in the library metadata
         * @property rollups
         */
        this.rollups = null;

        /**
         * Whether or not to load optional dependencies for
         * the requested modules
         * @property loadOptional
         * @type boolean
         * @default false
         */
        this.loadOptional = false;

        /**
         * All of the derived dependencies in sorted order, which
         * will be populated when either calculate() or insert()
         * is called
         * @property sorted
         * @type string[]
         */
        this.sorted = [];

        /**
         * Set when beginning to compute the dependency tree.
         * Composed of what YAHOO reports to be loaded combined
         * with what has been loaded by the tool
         * @propery loaded
         * @type {string: boolean}
         */
        this.loaded = {};

        /**
         * Flag to indicate the dependency tree needs to be recomputed
         * if insert is called again.
         * @property dirty
         * @type boolean
         * @default true
         */
        this.dirty = true;

        /**
         * List of modules inserted by the utility
         * @property inserted
         * @type {string: boolean}
         */
        this.inserted = {};

        /**
         * Provides the information used to skin the skinnable components.
         * The following skin definition would result in 'skin1' and 'skin2'
         * being loaded for calendar (if calendar was requested), and
         * 'sam' for all other skinnable components:
         *
         *   <code>
         *   skin: {
         *
         *      // The default skin, which is automatically applied if not
         *      // overriden by a component-specific skin definition.
         *      // Change this in to apply a different skin globally
         *      defaultSkin: 'sam',
         *
         *      // This is combined with the loader base property to get
         *      // the default root directory for a skin. ex:
         *      // http://yui.yahooapis.com/2.3.0/build/assets/skins/sam/
         *      base: 'assets/skins/',
         *
         *      // The name of the rollup css file for the skin
         *      path: 'skin.css',
         *
         *      // The number of skinnable components requested that are
         *      // required before using the rollup file rather than the
         *      // individual component css files
         *      rollup: 3,
         *
         *      // Any component-specific overrides can be specified here,
         *      // making it possible to load different skins for different
         *      // components.  It is possible to load more than one skin
         *      // for a given component as well.
         *      overrides: {
         *          calendar: ['skin1', 'skin2']
         *      }
         *   }
         *   </code>
         *   @property skin
         */

        var self = this;

        env.listeners.push(function(m) {
            if (self._useYahooListener) {
                //Y.log("YAHOO listener: " + m.name);
                self.loadNext(m.name);
            }
        });

        this.skin = lang.merge(YUI.info.skin);

        this._config(o);

    };

    Y.util.YUILoader.prototype = {

        FILTERS: {
            RAW: {
                'searchExp': "-min\\.js",
                'replaceStr': ".js"
            },
            DEBUG: {
                'searchExp': "-min\\.js",
                'replaceStr': "-debug.js"
            }
        },

        SKIN_PREFIX: "skin-",

        _config: function(o) {

            // apply config values
            if (o) {
                for (var i in o) {
                    if (lang.hasOwnProperty(o, i)) {
                        if (i == "require") {
                            this.require(o[i]);
                        } else {
                            this[i] = o[i];
                        }
                    }
                }
            }

            // fix filter
            var f = this.filter;

            if (lang.isString(f)) {
                f = f.toUpperCase();

                // the logger must be available in order to use the debug
                // versions of the library
                if (f === "DEBUG") {
                    this.require("logger");
                }

                // hack to handle a a bug where LogWriter is being instantiated
                // at load time, and the loader has no way to sort above it
                // at the moment.
                if (!Y.widget.LogWriter) {
                    Y.widget.LogWriter = function() {
                        return Y;
                    };
                }

                this.filter = this.FILTERS[f];
            }

        },

        /** Add a new module to the component metadata.
         * <dl>
         *     <dt>name:</dt>       <dd>required, the component name</dd>
         *     <dt>type:</dt>       <dd>required, the component type (js or css)</dd>
         *     <dt>path:</dt>       <dd>required, the path to the script from "base"</dd>
         *     <dt>requires:</dt>   <dd>array of modules required by this component</dd>
         *     <dt>optional:</dt>   <dd>array of optional modules for this component</dd>
         *     <dt>supersedes:</dt> <dd>array of the modules this component replaces</dd>
         *     <dt>after:</dt>      <dd>array of modules the components which, if present, should be sorted above this one</dd>
         *     <dt>rollup:</dt>     <dd>the number of superseded modules required for automatic rollup</dd>
         *     <dt>fullpath:</dt>   <dd>If fullpath is specified, this is used instead of the configured base + path</dd>
         *     <dt>skinnable:</dt>  <dd>flag to determine if skin assets should automatically be pulled in</dd>
         * </dl>
         * @method addModule
         * @param o An object containing the module data
         * @return {boolean} true if the module was added, false if
         * the object passed in did not provide all required attributes
         */
        addModule: function(o) {

            if (!o || !o.name || !o.type || (!o.path && !o.fullpath)) {
                return false;
            }

            o.ext = ('ext' in o) ? o.ext : true;
            o.requires = o.requires || [];

            this.moduleInfo[o.name] = o;
            this.dirty = true;

            return true;
        },

        /**
         * Add a requirement for one or more module
         * @method require
         * @param what {string[] | string*} the modules to load
         */
        require: function(what) {
            var a = (typeof what === "string") ? arguments : what;
            this.dirty = true;
            YUI.ObjectUtil.appendArray(this.required, a);
        },

        /**
         * Adds the skin def to the module info
         * @method _addSkin
         * @param skin {string} the name of the skin
         * @param mod {string} the name of the module
         * @return {string} the module name for the skin
         * @private
         */
        _addSkin: function(skin, mod) {

            // Add a module definition for the skin rollup css
            var name = this.formatSkin(skin), info = this.moduleInfo,
                sinf = this.skin, ext = info[mod] && info[mod].ext;

            // Y.log('ext? ' + mod + ": " + ext);
            if (!info[name]) {
                // Y.log('adding skin ' + name);
                this.addModule({
                    'name': name,
                    'type': 'css',
                    'path': sinf.base + skin + '/' + sinf.path,
                    //'supersedes': '*',
                    'after': sinf.after,
                    'rollup': sinf.rollup,
                    'ext': ext
                });
            }

            // Add a module definition for the module-specific skin css
            if (mod) {
                name = this.formatSkin(skin, mod);
                if (!info[name]) {
                    var mdef = info[mod], pkg = mdef.pkg || mod;
                    // Y.log('adding skin ' + name);
                    this.addModule({
                        'name': name,
                        'type': 'css',
                        'after': sinf.after,
                        'path': pkg + '/' + sinf.base + skin + '/' + mod + '.css',
                        'ext': ext
                    });
                }
            }

            return name;
        },

        /**
         * Returns an object containing properties for all modules required
         * in order to load the requested module
         * @method getRequires
         * @param mod The module definition from moduleInfo
         */
        getRequires: function(mod) {
            if (!mod) {
                return [];
            }

            if (!this.dirty && mod.expanded) {
                return mod.expanded;
            }

            mod.requires=mod.requires || [];
            var i, d=[], r=mod.requires, o=mod.optional, info=this.moduleInfo, m;
            for (i=0; i<r.length; i=i+1) {
                d.push(r[i]);
                m = info[r[i]];
                YUI.ArrayUtil.appendArray(d, this.getRequires(m));
            }

            if (o && this.loadOptional) {
                for (i=0; i<o.length; i=i+1) {
                    d.push(o[i]);
                    YUI.ArrayUtil.appendArray(d, this.getRequires(info[o[i]]));
                }
            }

            mod.expanded = YUI.ArrayUtil.uniq(d);

            return mod.expanded;
        },


        /**
         * Returns an object literal of the modules the supplied module satisfies
         * @method getProvides
         * @param name{string} The name of the module
         * @param notMe {string} don't add this module name, only include superseded modules
         * @return what this module provides
         */
        getProvides: function(name, notMe) {
            var addMe = !(notMe), ckey = (addMe) ? PROV : SUPER,
                m = this.moduleInfo[name], o = {};

            if (!m) {
                return o;
            }

            if (m[ckey]) {
// Y.log('cached: ' + name + ' ' + ckey + ' ' + lang.dump(this.moduleInfo[name][ckey], 0));
                return m[ckey];
            }

            var s = m.supersedes, done={}, me = this;

            // use worker to break cycles
            var add = function(mm) {
                if (!done[mm]) {
                    // Y.log(name + ' provides worker trying: ' + mm);
                    done[mm] = true;
                    // we always want the return value normal behavior
                    // (provides) for superseded modules.
                    lang.augmentObject(o, me.getProvides(mm));
                }

                // else {
                // Y.log(name + ' provides worker skipping done: ' + mm);
                // }
            };

            // calculate superseded modules
            if (s) {
                for (var i=0; i<s.length; i=i+1) {
                    add(s[i]);
                }
            }

            // supersedes cache
            m[SUPER] = o;
            // provides cache
            m[PROV] = lang.merge(o);
            m[PROV][name] = true;

// Y.log(name + " supersedes " + lang.dump(m[SUPER], 0));
// Y.log(name + " provides " + lang.dump(m[PROV], 0));

            return m[ckey];
        },


        /**
         * Calculates the dependency tree, the result is stored in the sorted
         * property
         * @method calculate
         * @param o optional options object
         */
        calculate: function(o) {
            if (o || this.dirty) {
                this._config(o);
                this._setup();
                this._explode();
                if (this.allowRollup) {
                    this._rollup();
                }
                this._reduce();
                this._sort();

                // Y.log("after calculate: " + lang.dump(this.required));

                this.dirty = false;
            }
        },

        /**
         * Investigates the current YUI configuration on the page.  By default,
         * modules already detected will not be loaded again unless a force
         * option is encountered.  Called by calculate()
         * @method _setup
         * @private
         */
        _setup: function() {

            var info = this.moduleInfo, name, i, j;

            // Create skin modules
            for (name in info) {

                if (lang.hasOwnProperty(info, name)) {
                    var m = info[name];
                    if (m && m.skinnable) {
                        // Y.log("skinning: " + name);
                        var o=this.skin.overrides, smod;
                        if (o && o[name]) {
                            for (i=0; i<o[name].length; i=i+1) {
                                smod = this._addSkin(o[name][i], name);
                            }
                        } else {
                            smod = this._addSkin(this.skin.defaultSkin, name);
                        }

                        if (YUI.ArrayUtil.indexOf(m.requires, smod) == -1) {
                            m.requires.push(smod);
                        }
                    }
                }

            }

            var l = lang.merge(this.inserted); // shallow clone

            if (!this._sandbox) {
                l = lang.merge(l, env.modules);
            }

            // Y.log("Already loaded stuff: " + lang.dump(l, 0));

            // add the ignore list to the list of loaded packages
            if (this.ignore) {
                YUI.ObjectUtil.appendArray(l, this.ignore);
            }

            // remove modules on the force list from the loaded list
            if (this.force) {
                for (i=0; i<this.force.length; i=i+1) {
                    if (this.force[i] in l) {
                        delete l[this.force[i]];
                    }
                }
            }

            // expand the list to include superseded modules
            for (j in l) {
                // Y.log("expanding: " + j);
                if (lang.hasOwnProperty(l, j)) {
                    lang.augmentObject(l, this.getProvides(j));
                }
            }

            // Y.log("loaded expanded: " + lang.dump(l, 0));

            this.loaded = l;

        },


        /**
         * Inspects the required modules list looking for additional
         * dependencies.  Expands the required list to include all
         * required modules.  Called by calculate()
         * @method _explode
         * @private
         */
        _explode: function() {

            var r=this.required, i, mod;

            for (i in r) {
                if (lang.hasOwnProperty(r, i)) {
                    mod = this.moduleInfo[i];
                    if (mod) {

                        var req = this.getRequires(mod);

                        if (req) {
                            YUI.ObjectUtil.appendArray(r, req);
                        }
                    }
                }
            }
        },

        /*
         * @method _skin
         * @private
         * @deprecated
         */
        _skin: function() {
        },

        /**
         * Returns the skin module name for the specified skin name.  If a
         * module name is supplied, the returned skin module name is
         * specific to the module passed in.
         * @method formatSkin
         * @param skin {string} the name of the skin
         * @param mod {string} optional: the name of a module to skin
         * @return {string} the full skin module name
         */
        formatSkin: function(skin, mod) {
            var s = this.SKIN_PREFIX + skin;
            if (mod) {
                s = s + "-" + mod;
            }

            return s;
        },

        /**
         * Reverses <code>formatSkin</code>, providing the skin name and
         * module name if the string matches the pattern for skins.
         * @method parseSkin
         * @param mod {string} the module name to parse
         * @return {skin: string, module: string} the parsed skin name
         * and module name, or null if the supplied string does not match
         * the skin pattern
         */
        parseSkin: function(mod) {

            if (mod.indexOf(this.SKIN_PREFIX) === 0) {
                var a = mod.split("-");
                return {skin: a[1], module: a[2]};
            }

            return null;
        },

        /**
         * Look for rollup packages to determine if all of the modules a
         * rollup supersedes are required.  If so, include the rollup to
         * help reduce the total number of connections required.  Called
         * by calculate()
         * @method _rollup
         * @private
         */
        _rollup: function() {
            var i, j, m, s, rollups={}, r=this.required, roll,
                info = this.moduleInfo;

            // find and cache rollup modules
            if (this.dirty || !this.rollups) {
                for (i in info) {
                    if (lang.hasOwnProperty(info, i)) {
                        m = info[i];
                        //if (m && m.rollup && m.supersedes) {
                        if (m && m.rollup) {
                            rollups[i] = m;
                        }
                    }
                }

                this.rollups = rollups;
            }

            // make as many passes as needed to pick up rollup rollups
            for (;;) {
                var rolled = false;

                // go through the rollup candidates
                for (i in rollups) {

                    // there can be only one
                    if (!r[i] && !this.loaded[i]) {
                        m =info[i]; s = m.supersedes; roll=false;

                        if (!m.rollup) {
                            continue;
                        }

                        var skin = (m.ext) ? false : this.parseSkin(i), c = 0;

                        // Y.log('skin? ' + i + ": " + skin);
                        if (skin) {
                            for (j in r) {
                                if (lang.hasOwnProperty(r, j)) {
                                    if (i !== j && this.parseSkin(j)) {
                                        c++;
                                        roll = (c >= m.rollup);
                                        if (roll) {
                                            // Y.log("skin rollup " + lang.dump(r));
                                            break;
                                        }
                                    }
                                }
                            }

                        } else {

                            // check the threshold
                            for (j=0;j<s.length;j=j+1) {

                                // if the superseded module is loaded, we can't load the rollup
                                if (this.loaded[s[j]] && (!YUI.dupsAllowed[s[j]])) {
                                    roll = false;
                                    break;
                                // increment the counter if this module is required.  if we are
                                // beyond the rollup threshold, we will use the rollup module
                                } else if (r[s[j]]) {
                                    c++;
                                    roll = (c >= m.rollup);
                                    if (roll) {
                                        // Y.log("over thresh " + c + ", " + lang.dump(r));
                                        break;
                                    }
                                }
                            }
                        }

                        if (roll) {
                            // Y.log("rollup: " +  i + ", " + lang.dump(this, 1));
                            // add the rollup
                            r[i] = true;
                            rolled = true;

                            // expand the rollup's dependencies
                            this.getRequires(m);
                        }
                    }
                }

                // if we made it here w/o rolling up something, we are done
                if (!rolled) {
                    break;
                }
            }
        },

        /**
         * Remove superceded modules and loaded modules.  Called by
         * calculate() after we have the mega list of all dependencies
         * @method _reduce
         * @private
         */
        _reduce: function() {

            var i, j, s, m, r=this.required;
            for (i in r) {

                // remove if already loaded
                if (i in this.loaded) {
                    delete r[i];

                // remove anything this module supersedes
                } else {

                    var skinDef = this.parseSkin(i);

                    if (skinDef) {
                        //YAHOO.log("skin found in reduce: " + skinDef.skin + ", " + skinDef.module);
                        // the skin rollup will not have a module name
                        if (!skinDef.module) {
                            var skin_pre = this.SKIN_PREFIX + skinDef.skin;
                            //YAHOO.log("skin_pre: " + skin_pre);
                            for (j in r) {

                                if (lang.hasOwnProperty(r, j)) {
                                    m = this.moduleInfo[j];
                                    var ext = m && m.ext;
                                    if (!ext && j !== i && j.indexOf(skin_pre) > -1) {
                                        // Y.log ("removing component skin: " + j);
                                        delete r[j];
                                    }
                                }
                            }
                        }
                    } else {

                         m = this.moduleInfo[i];
                         s = m && m.supersedes;
                         if (s) {
                             for (j=0; j<s.length; j=j+1) {
                                 if (s[j] in r) {
                                     delete r[s[j]];
                                 }
                             }
                         }
                    }
                }
            }
        },

        _onFailure: function(msg) {
            YAHOO.log('Failure', 'info', 'loader');

            var f = this.onFailure;
            if (f) {
                f.call(this.scope, {
                    msg: 'failure: ' + msg,
                    data: this.data,
                    success: false
                });
            }
        },

        _onTimeout: function() {
            YAHOO.log('Timeout', 'info', 'loader');
            var f = this.onTimeout;
            if (f) {
                f.call(this.scope, {
                    msg: 'timeout',
                    data: this.data,
                    success: false
                });
            }
        },

        /**
         * Sorts the dependency tree.  The last step of calculate()
         * @method _sort
         * @private
         */
        _sort: function() {
            // create an indexed list
            var s=[], info=this.moduleInfo, loaded=this.loaded,
                checkOptional=!this.loadOptional, me = this;

            // returns true if b is not loaded, and is required
            // directly or by means of modules it supersedes.
            var requires = function(aa, bb) {

                var mm=info[aa];

                if (loaded[bb] || !mm) {
                    return false;
                }

                var ii,
                    rr = mm.expanded,
                    after = mm.after,
                    other = info[bb],
                    optional = mm.optional;


                // check if this module requires the other directly
                if (rr && YUI.ArrayUtil.indexOf(rr, bb) > -1) {
                    return true;
                }

                // check if this module should be sorted after the other
                if (after && YUI.ArrayUtil.indexOf(after, bb) > -1) {
                    return true;
                }

                // if loadOptional is not specified, optional dependencies still
                // must be sorted correctly when present.
                if (checkOptional && optional && YUI.ArrayUtil.indexOf(optional, bb) > -1) {
                    return true;
                }

                // check if this module requires one the other supersedes
                var ss=info[bb] && info[bb].supersedes;
                if (ss) {
                    for (ii=0; ii<ss.length; ii=ii+1) {
                        if (requires(aa, ss[ii])) {
                            return true;
                        }
                    }
                }

                // external css files should be sorted below yui css
                if (mm.ext && mm.type == 'css' && !other.ext && other.type == 'css') {
                    return true;
                }

                return false;
            };

            // get the required items out of the obj into an array so we
            // can sort
            for (var i in this.required) {
                if (lang.hasOwnProperty(this.required, i)) {
                    s.push(i);
                }
            }

            // pointer to the first unsorted item
            var p=0;

            // keep going until we make a pass without moving anything
            for (;;) {

                var l=s.length, a, b, j, k, moved=false;

                // start the loop after items that are already sorted
                for (j=p; j<l; j=j+1) {

                    // check the next module on the list to see if its
                    // dependencies have been met
                    a = s[j];

                    // check everything below current item and move if we
                    // find a requirement for the current item
                    for (k=j+1; k<l; k=k+1) {
                        if (requires(a, s[k])) {

                            // extract the dependency so we can move it up
                            b = s.splice(k, 1);

                            // insert the dependency above the item that
                            // requires it
                            s.splice(j, 0, b[0]);

                            moved = true;
                            break;
                        }
                    }

                    // jump out of loop if we moved something
                    if (moved) {
                        break;
                    // this item is sorted, move our pointer and keep going
                    } else {
                        p = p + 1;
                    }
                }

                // when we make it here and moved is false, we are
                // finished sorting
                if (!moved) {
                    break;
                }

            }

            this.sorted = s;
        },

        toString: function() {
            var o = {
                type: "YUILoader",
                base: this.base,
                filter: this.filter,
                required: this.required,
                loaded: this.loaded,
                inserted: this.inserted
            };

            lang.dump(o, 1);
        },

        _combine: function() {

                this._combining = [];

                var self = this,
                    s=this.sorted,
                    len = s.length,
                    js = this.comboBase,
                    css = this.comboBase,
                    target,
                    startLen = js.length,
                    i, m, type = this.loadType;

                YAHOO.log('type ' + type);

                for (i=0; i<len; i=i+1) {

                    m = this.moduleInfo[s[i]];

                    if (m && !m.ext && (!type || type === m.type)) {

                        target = this.root + m.path;

                        // if (i < len-1) {
                        target += '&';
                        // }

                        if (m.type == 'js') {
                            js += target;
                        } else {
                            css += target;
                        }

                        // YAHOO.log(target);
                        this._combining.push(s[i]);
                    }
                }

                if (this._combining.length) {

YAHOO.log('Attempting to combine: ' + this._combining, "info", "loader");

                    var callback=function(o) {
                        // YAHOO.log('Combo complete: ' + o.data, "info", "loader");
                        // this._combineComplete = true;

                        var c=this._combining, len=c.length, i, m;
                        for (i=0; i<len; i=i+1) {
                            this.inserted[c[i]] = true;
                        }

                        this.loadNext(o.data);
                    },

                    loadScript = function() {
                        // YAHOO.log('combining js: ' + js);
                        if (js.length > startLen) {
                            YAHOO.util.Get.script(self._filter(js), {
                                data: self._loading,
                                onSuccess: callback,
                                onFailure: self._onFailure,
                                onTimeout: self._onTimeout,
                                insertBefore: self.insertBefore,
                                charset: self.charset,
                                timeout: self.timeout,
                                scope: self
                            });
                        } else {
                            this.loadNext();
                        }
                    };

                    // load the css first
                    // YAHOO.log('combining css: ' + css);
                    if (css.length > startLen) {
                        YAHOO.util.Get.css(this._filter(css), {
                            data: this._loading,
                            onSuccess: loadScript,
                            onFailure: this._onFailure,
                            onTimeout: this._onTimeout,
                            insertBefore: this.insertBefore,
                            charset: this.charset,
                            timeout: this.timeout,
                            scope: self
                        });
                    } else {
                        loadScript();
                    }

                    return;

                } else {
                    // this._combineComplete = true;
                    this.loadNext(this._loading);
                }
        },

        /**
         * inserts the requested modules and their dependencies.
         * <code>type</code> can be "js" or "css".  Both script and
         * css are inserted if type is not provided.
         * @method insert
         * @param o optional options object
         * @param type {string} the type of dependency to insert
         */
        insert: function(o, type) {
            // if (o) {
            //     Y.log("insert: " + lang.dump(o, 1) + ", " + type);
            // } else {
            //     Y.log("insert: " + this.toString() + ", " + type);
            // }

            // build the dependency list
            this.calculate(o);


            // set a flag to indicate the load has started
            this._loading = true;

            // flag to indicate we are done with the combo service
            // and any additional files will need to be loaded
            // individually
            // this._combineComplete = false;

            // keep the loadType (js, css or undefined) cached
            this.loadType = type;

            if (this.combine) {
                return this._combine();
            }

            if (!type) {
                // Y.log("trying to load css first");
                var self = this;
                this._internalCallback = function() {
                            self._internalCallback = null;
                            self.insert(null, "js");
                        };
                this.insert(null, "css");
                return;
            }


            // start the load
            this.loadNext();

        },

        /**
         * Interns the script for the requested modules.  The callback is
         * provided a reference to the sandboxed YAHOO object.  This only
         * applies to the script: css can not be sandboxed; css will be
         * loaded into the page normally if specified.
         * @method sandbox
         * @param callback {Function} the callback to exectued when the load is
         *        complete.
         */
        sandbox: function(o, type) {
            // if (o) {
                // YAHOO.log("sandbox: " + lang.dump(o, 1) + ", " + type);
            // } else {
                // YAHOO.log("sandbox: " + this.toString() + ", " + type);
            // }

            var self = this,
                success = function(o) {

                    var idx=o.argument[0], name=o.argument[2];

                    // store the response in the position it was requested
                    self._scriptText[idx] = o.responseText;

                    // YAHOO.log("received: " + o.responseText.substr(0, 100) + ", " + idx);

                    if (self.onProgress) {
                        self.onProgress.call(self.scope, {
                                    name: name,
                                    scriptText: o.responseText,
                                    xhrResponse: o,
                                    data: self.data
                                });
                    }

                    // only generate the sandbox once everything is loaded
                    self._loadCount++;

                    if (self._loadCount >= self._stopCount) {

                        // the variable to find
                        var v = self.varName || "YAHOO";

                        // wrap the contents of the requested modules in an anonymous function
                        var t = "(function() {\n";

                        // return the locally scoped reference.
                        var b = "\nreturn " + v + ";\n})();";

                        var ref = eval(t + self._scriptText.join("\n") + b);

                        self._pushEvents(ref);

                        if (ref) {
                            self.onSuccess.call(self.scope, {
                                    reference: ref,
                                    data: self.data
                                });
                        } else {
                            self._onFailure.call(self.varName + " reference failure");
                        }
                    }
                },

                failure = function(o) {
                    self.onFailure.call(self.scope, {
                            msg: "XHR failure",
                            xhrResponse: o,
                            data: self.data
                        });
                };

            self._config(o);

            if (!self.onSuccess) {
throw new Error("You must supply an onSuccess handler for your sandbox");
            }

            self._sandbox = true;


            // take care of any css first (this can't be sandboxed)
            if (!type || type !== "js") {
                self._internalCallback = function() {
                            self._internalCallback = null;
                            self.sandbox(null, "js");
                        };
                self.insert(null, "css");
                return;
            }

            // get the connection manager if not on the page
            if (!util.Connect) {
                // get a new loader instance to load connection.
                var ld = new YAHOO.util.YUILoader();
                ld.insert({
                    base: self.base,
                    filter: self.filter,
                    require: "connection",
                    insertBefore: self.insertBefore,
                    charset: self.charset,
                    onSuccess: function() {
                        self.sandbox(null, "js");
                    },
                    scope: self
                }, "js");
                return;
            }

            self._scriptText = [];
            self._loadCount = 0;
            self._stopCount = self.sorted.length;
            self._xhr = [];

            self.calculate();

            var s=self.sorted, l=s.length, i, m, url;

            for (i=0; i<l; i=i+1) {
                m = self.moduleInfo[s[i]];

                // undefined modules cause a failure
                if (!m) {
                    self._onFailure("undefined module " + m);
                    for (var j=0;j<self._xhr.length;j=j+1) {
                        self._xhr[j].abort();
                    }
                    return;
                }

                // css files should be done
                if (m.type !== "js") {
                    self._loadCount++;
                    continue;
                }

                url = m.fullpath;
                url = (url) ? self._filter(url) : self._url(m.path);

                // YAHOO.log("xhr request: " + url + ", " + i);

                var xhrData = {
                    success: success,
                    failure: failure,
                    scope: self,
                    // [module index, module name, sandbox name]
                    argument: [i, url, s[i]]
                };

                self._xhr.push(util.Connect.asyncRequest('GET', url, xhrData));
            }
        },

        /**
         * Executed every time a module is loaded, and if we are in a load
         * cycle, we attempt to load the next script.  Public so that it
         * is possible to call this if using a method other than
         * YAHOO.register to determine when scripts are fully loaded
         * @method loadNext
         * @param mname {string} optional the name of the module that has
         * been loaded (which is usually why it is time to load the next
         * one)
         */
        loadNext: function(mname) {

            // It is possible that this function is executed due to something
            // else one the page loading a YUI module.  Only react when we
            // are actively loading something
            if (!this._loading) {
                return;
            }

            var self = this,
                donext = function(o) {
                    self.loadNext(o.data);
                }, successfn, s = this.sorted, len=s.length, i, fn, m, url;


            if (mname) {

                // if the module that was just loaded isn't what we were expecting,
                // continue to wait
                if (mname !== this._loading) {
                    return;
                }

                // YAHOO.log("loadNext executing, just loaded " + mname);

                // The global handler that is called when each module is loaded
                // will pass that module name to this function.  Storing this
                // data to avoid loading the same module multiple times
                this.inserted[mname] = true;

                if (this.onProgress) {
                    this.onProgress.call(this.scope, {
                            name: mname,
                            data: this.data
                        });
                }
                //var o = this.getProvides(mname);
                //this.inserted = lang.merge(this.inserted, o);
            }



            for (i=0; i<len; i=i+1) {

                // This.inserted keeps track of what the loader has loaded
                if (s[i] in this.inserted) {
                    // YAHOO.log(s[i] + " alread loaded ");
                    continue;
                }

                // Because rollups will cause multiple load notifications
                // from YAHOO, loadNext may be called multiple times for
                // the same module when loading a rollup.  We can safely
                // skip the subsequent requests
                if (s[i] === this._loading) {
                    // YAHOO.log("still loading " + s[i] + ", waiting");
                    return;
                }

                // log("inserting " + s[i]);
                m = this.moduleInfo[s[i]];

                if (!m) {
                    this.onFailure.call(this.scope, {
                            msg: "undefined module " + m,
                            data: this.data
                        });
                    return;
                }

                // The load type is stored to offer the possibility to load
                // the css separately from the script.
                if (!this.loadType || this.loadType === m.type) {

                    successfn = donext;

                    this._loading = s[i];
                    //YAHOO.log("attempting to load " + s[i] + ", " + this.base);

                    fn = (m.type === "css") ? util.Get.css : util.Get.script;
                    url = m.fullpath;
                    url = (url) ? this._filter(url) : this._url(m.path);

                    // safari 2.x or lower, script, and part of YUI
                    if (env.ua.webkit && env.ua.webkit < 420 && m.type === "js" &&
                          !m.varName) {
                          //YUI.info.moduleInfo[s[i]]) {
                          //YAHOO.log("using YAHOO env " + s[i] + ", " + m.varName);
                        successfn = null;
                        this._useYahooListener = true;
                    }

                    fn(url, {
                        data: s[i],
                        onSuccess: successfn,
                        onFailure: this._onFailure,
                        onTimeout: this._onTimeout,
                        insertBefore: this.insertBefore,
                        charset: this.charset,
                        timeout: this.timeout,
                        varName: m.varName,
                        scope: self
                    });

                    return;
                }
            }

            // we are finished
            this._loading = null;

            // internal callback for loading css first
            if (this._internalCallback) {
                var f = this._internalCallback;
                this._internalCallback = null;
                f.call(this);
            } else if (this.onSuccess) {
                this._pushEvents();
                this.onSuccess.call(this.scope, {
                        data: this.data
                    });
            }

        },

        /**
         * In IE, the onAvailable/onDOMReady events need help when Event is
         * loaded dynamically
         * @method _pushEvents
         * @param {Function} optional function reference
         * @private
         */
        _pushEvents: function(ref) {
            var r = ref || YAHOO;
            if (r.util && r.util.Event) {
                r.util.Event._load();
            }
        },

        /**
         * Applies filter
         * method _filter
         * @return {string} the filtered string
         * @private
         */
        _filter: function(str) {
            var f = this.filter;
            return (f) ?  str.replace(new RegExp(f.searchExp, 'g'), f.replaceStr) : str;
        },

        /**
         * Generates the full url for a module
         * method _url
         * @param path {string} the path fragment
         * @return {string} the full url
         * @private
         */
        _url: function(path) {
            return this._filter((this.base || "") + path);
        }

    };

})();

