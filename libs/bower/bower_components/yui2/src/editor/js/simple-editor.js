/**
 * @module editor
 * @description <p>The Rich Text Editor is a UI control that replaces a standard HTML textarea; it allows for the rich formatting of text content, including common structural treatments like lists, formatting treatments like bold and italic text, and drag-and-drop inclusion and sizing of images. The Rich Text Editor's toolbar is extensible via a plugin architecture so that advanced implementations can achieve a high degree of customization.</p>
 * @namespace YAHOO.widget
 * @requires yahoo, dom, element, event, toolbar
 * @optional animation, container_core, resize, dragdrop
 */

(function() {
var Dom = YAHOO.util.Dom,
    Event = YAHOO.util.Event,
    Lang = YAHOO.lang,
    Toolbar = YAHOO.widget.Toolbar;

    /**
     * The Rich Text Editor is a UI control that replaces a standard HTML textarea; it allows for the rich formatting of text content, including common structural treatments like lists, formatting treatments like bold and italic text, and drag-and-drop inclusion and sizing of images. The Rich Text Editor's toolbar is extensible via a plugin architecture so that advanced implementations can achieve a high degree of customization.
     * @constructor
     * @class SimpleEditor
     * @extends YAHOO.util.Element
     * @param {String/HTMLElement} el The textarea element to turn into an editor.
     * @param {Object} attrs Object liternal containing configuration parameters.
    */
    
    YAHOO.widget.SimpleEditor = function(el, attrs) {
        YAHOO.log('SimpleEditor Initalizing', 'info', 'SimpleEditor');
        
        var o = {};
        if (Lang.isObject(el) && (!el.tagName) && !attrs) {
            Lang.augmentObject(o, el); //Break the config reference
            el = document.createElement('textarea');
            this.DOMReady = true;
            if (o.container) {
                var c = Dom.get(o.container);
                c.appendChild(el);
            } else {
                document.body.appendChild(el);
            }
        } else {
            if (attrs) {
                Lang.augmentObject(o, attrs); //Break the config reference
            }
        }

        var oConfig = {
            element: null,
            attributes: o
        }, id = null;

        if (Lang.isString(el)) {
            id = el;
        } else {
            if (oConfig.attributes.id) {
                id = oConfig.attributes.id;
            } else {
                this.DOMReady = true;
                id = Dom.generateId(el);
            }
        }
        oConfig.element = el;

        var element_cont = document.createElement('DIV');
        oConfig.attributes.element_cont = new YAHOO.util.Element(element_cont, {
            id: id + '_container'
        });
        var div = document.createElement('div');
        Dom.addClass(div, 'first-child');
        oConfig.attributes.element_cont.appendChild(div);
        
        if (!oConfig.attributes.toolbar_cont) {
            oConfig.attributes.toolbar_cont = document.createElement('DIV');
            oConfig.attributes.toolbar_cont.id = id + '_toolbar';
            div.appendChild(oConfig.attributes.toolbar_cont);
        }
        var editorWrapper = document.createElement('DIV');
        div.appendChild(editorWrapper);
        oConfig.attributes.editor_wrapper = editorWrapper;

        YAHOO.widget.SimpleEditor.superclass.constructor.call(this, oConfig.element, oConfig.attributes);
    };


    YAHOO.extend(YAHOO.widget.SimpleEditor, YAHOO.util.Element, {
        /**
        * @private
        * @property _resizeConfig
        * @description The default config for the Resize Utility
        */
        _resizeConfig: {
            handles: ['br'],
            autoRatio: true,
            status: true,
            proxy: true,
            useShim: true,
            setSize: false
        },
        /**
        * @private
        * @method _setupResize
        * @description Creates the Resize instance and binds its events.
        */
        _setupResize: function() {
            if (!YAHOO.util.DD || !YAHOO.util.Resize) { return false; }
            if (this.get('resize')) {
                var config = {};
                Lang.augmentObject(config, this._resizeConfig); //Break the config reference
                this.resize = new YAHOO.util.Resize(this.get('element_cont').get('element'), config);
                this.resize.on('resize', function(args) {
                    var anim = this.get('animate');
                    this.set('animate', false);
                    this.set('width', args.width + 'px');
                    var h = args.height,
                        th = (this.toolbar.get('element').clientHeight + 2),
                        dh = 0;
                    if (this.dompath) {
                        dh = (this.dompath.clientHeight + 1); //It has a 1px top border..
                    }
                    var newH = (h - th - dh);
                    this.set('height', newH + 'px');
                    this.get('element_cont').setStyle('height', '');
                    this.set('animate', anim);
                }, this, true);
            }
        },
        /**
        * @property resize
        * @description A reference to the Resize object
        * @type YAHOO.util.Resize
        */
        resize: null,
        /**
        * @private
        * @method _setupDD
        * @description Sets up the DD instance used from the 'drag' config option.
        */
        _setupDD: function() {
            if (!YAHOO.util.DD) { return false; }
            if (this.get('drag')) {
                YAHOO.log('Attaching DD instance to Editor', 'info', 'SimpleEditor');
                var d = this.get('drag'),
                    dd = YAHOO.util.DD;
                if (d === 'proxy') {
                    dd = YAHOO.util.DDProxy;
                }

                this.dd = new dd(this.get('element_cont').get('element'));
                this.toolbar.addClass('draggable'); 
                this.dd.setHandleElId(this.toolbar._titlebar); 
            }
        },
        /**
        * @property dd
        * @description A reference to the DragDrop object.
        * @type YAHOO.util.DD/YAHOO.util.DDProxy
        */
        dd: null,
        /**
        * @private
        * @property _lastCommand
        * @description A cache of the last execCommand (used for Undo/Redo so they don't mark an undo level)
        * @type String
        */
        _lastCommand: null,
        _undoNodeChange: function() {},
        _storeUndo: function() {},
        /**
        * @private
        * @method _checkKey
        * @description Checks a keyMap entry against a key event
        * @param {Object} k The _keyMap object
        * @param {Event} e The Mouse Event
        * @return {Boolean}
        */
        _checkKey: function(k, e) {
            var ret = false;
            if ((e.keyCode === k.key)) {
                if (k.mods && (k.mods.length > 0)) {
                    var val = 0;
                    for (var i = 0; i < k.mods.length; i++) {
                        if (this.browser.mac) {
                            if (k.mods[i] == 'ctrl') {
                                k.mods[i] = 'meta';
                            }
                        }
                        if (e[k.mods[i] + 'Key'] === true) {
                            val++;
                        }
                    }
                    if (val === k.mods.length) {
                        ret = true;
                    }
                } else {
                    ret = true;
                }
            }
            //YAHOO.log('Shortcut Key Check: (' + k.key + ') return: ' + ret, 'info', 'SimpleEditor');
            return ret;
        },
        /**
        * @private
        * @property _keyMap
        * @description Named key maps for various actions in the Editor. Example: <code>CLOSE_WINDOW: { key: 87, mods: ['shift', 'ctrl'] }</code>. 
        * This entry shows that when key 87 (W) is found with the modifiers of shift and control, the window will close. You can customize this object to tweak keyboard shortcuts.
        * @type {Object/Mixed}
        */
        _keyMap: {
            SELECT_ALL: {
                key: 65, //A key
                mods: ['ctrl']
            },
            CLOSE_WINDOW: {
                key: 87, //W key
                mods: ['shift', 'ctrl']
            },
            FOCUS_TOOLBAR: {
                key: 27,
                mods: ['shift']
            },
            FOCUS_AFTER: {
                key: 27
            },
            FONT_SIZE_UP: {
                key: 38,
                mods: ['shift', 'ctrl']
            },
            FONT_SIZE_DOWN: {
                key: 40,
                mods: ['shift', 'ctrl']
            },
            CREATE_LINK: {
                key: 76,
                mods: ['shift', 'ctrl']
            },
            BOLD: {
                key: 66,
                mods: ['shift', 'ctrl']
            },
            ITALIC: {
                key: 73,
                mods: ['shift', 'ctrl']
            },
            UNDERLINE: {
                key: 85,
                mods: ['shift', 'ctrl']
            },
            UNDO: {
                key: 90,
                mods: ['ctrl']
            },
            REDO: {
                key: 90,
                mods: ['shift', 'ctrl']
            },
            JUSTIFY_LEFT: {
                key: 219,
                mods: ['shift', 'ctrl']
            },
            JUSTIFY_CENTER: {
                key: 220,
                mods: ['shift', 'ctrl']
            },
            JUSTIFY_RIGHT: {
                key: 221,
                mods: ['shift', 'ctrl']
            }
        },
        /**
        * @private
        * @method _cleanClassName
        * @description Makes a useable classname from dynamic data, by dropping it to lowercase and replacing spaces with -'s.
        * @param {String} str The classname to clean up
        * @return {String}
        */
        _cleanClassName: function(str) {
            return str.replace(/ /g, '-').toLowerCase();
        },
        /**
        * @property _textarea
        * @description Flag to determine if we are using a textarea or an HTML Node.
        * @type Boolean
        */
        _textarea: null,
        /**
        * @property _docType
        * @description The DOCTYPE to use in the editable container.
        * @type String
        */
        _docType: '<!DOCTYPE HTML PUBLIC "-/'+'/W3C/'+'/DTD HTML 4.01/'+'/EN" "http:/'+'/www.w3.org/TR/html4/strict.dtd">',
        /**
        * @property editorDirty
        * @description This flag will be set when certain things in the Editor happen. It is to be used by the developer to check to see if content has changed.
        * @type Boolean
        */
        editorDirty: null,
        /**
        * @property _defaultCSS
        * @description The default CSS used in the config for 'css'. This way you can add to the config like this: { css: YAHOO.widget.SimpleEditor.prototype._defaultCSS + 'ADD MYY CSS HERE' }
        * @type String
        */
        _defaultCSS: 'html { height: 95%; } body { padding: 7px; background-color: #fff; font: 13px/1.22 arial,helvetica,clean,sans-serif;*font-size:small;*font:x-small; } a, a:visited, a:hover { color: blue !important; text-decoration: underline !important; cursor: text !important; } .warning-localfile { border-bottom: 1px dashed red !important; } .yui-busy { cursor: wait !important; } img.selected { border: 2px dotted #808080; } img { cursor: pointer !important; border: none; } body.ptags.webkit div.yui-wk-p { margin: 11px 0; } body.ptags.webkit div.yui-wk-div { margin: 0; }',
        /**
        * @property _defaultToolbar
        * @private
        * @description Default toolbar config.
        * @type Object
        */
        _defaultToolbar: null,
        /**
        * @property _lastButton
        * @private
        * @description The last button pressed, so we don't disable it.
        * @type Object
        */
        _lastButton: null,
        /**
        * @property _baseHREF
        * @private
        * @description The base location of the editable page (this page) so that relative paths for image work.
        * @type String
        */
        _baseHREF: function() {
            var href = document.location.href;
            if (href.indexOf('?') !== -1) { //Remove the query string
                href = href.substring(0, href.indexOf('?'));
            }
            href = href.substring(0, href.lastIndexOf('/')) + '/';
            return href;
        }(),
        /**
        * @property _lastImage
        * @private
        * @description Safari reference for the last image selected (for styling as selected).
        * @type HTMLElement
        */
        _lastImage: null,
        /**
        * @property _blankImageLoaded
        * @private
        * @description Don't load the blank image more than once..
        * @type Boolean
        */
        _blankImageLoaded: null,
        /**
        * @property _fixNodesTimer
        * @private
        * @description Holder for the fixNodes timer
        * @type Date
        */
        _fixNodesTimer: null,
        /**
        * @property _nodeChangeTimer
        * @private
        * @description Holds a reference to the nodeChange setTimeout call
        * @type Number
        */
        _nodeChangeTimer: null,
        /**
        * @property _nodeChangeDelayTimer
        * @private
        * @description Holds a reference to the nodeChangeDelay setTimeout call
        * @type Number
        */
        _nodeChangeDelayTimer: null,
        /**
        * @property _lastNodeChangeEvent
        * @private
        * @description Flag to determine the last event that fired a node change
        * @type Event
        */
        _lastNodeChangeEvent: null,
        /**
        * @property _lastNodeChange
        * @private
        * @description Flag to determine when the last node change was fired
        * @type Date
        */
        _lastNodeChange: 0,
        /**
        * @property _rendered
        * @private
        * @description Flag to determine if editor has been rendered or not
        * @type Boolean
        */
        _rendered: null,
        /**
        * @property DOMReady
        * @private
        * @description Flag to determine if DOM is ready or not
        * @type Boolean
        */
        DOMReady: null,
        /**
        * @property _selection
        * @private
        * @description Holder for caching iframe selections
        * @type Object
        */
        _selection: null,
        /**
        * @property _mask
        * @private
        * @description DOM Element holder for the editor Mask when disabled
        * @type Object
        */
        _mask: null,
        /**
        * @property _showingHiddenElements
        * @private
        * @description Status of the hidden elements button
        * @type Boolean
        */
        _showingHiddenElements: null,
        /**
        * @property currentWindow
        * @description A reference to the currently open EditorWindow
        * @type Object
        */
        currentWindow: null,
        /**
        * @property currentEvent
        * @description A reference to the current editor event
        * @type Event
        */
        currentEvent: null,
        /**
        * @property operaEvent
        * @private
        * @description setTimeout holder for Opera and Image DoubleClick event..
        * @type Object
        */
        operaEvent: null,
        /**
        * @property currentFont
        * @description A reference to the last font selected from the Toolbar
        * @type HTMLElement
        */
        currentFont: null,
        /**
        * @property currentElement
        * @description A reference to the current working element in the editor
        * @type Array
        */
        currentElement: null,
        /**
        * @property dompath
        * @description A reference to the dompath container for writing the current working dom path to.
        * @type HTMLElement
        */
        dompath: null,
        /**
        * @property beforeElement
        * @description A reference to the H2 placed before the editor for Accessibilty.
        * @type HTMLElement
        */
        beforeElement: null,
        /**
        * @property afterElement
        * @description A reference to the H2 placed after the editor for Accessibilty.
        * @type HTMLElement
        */
        afterElement: null,
        /**
        * @property invalidHTML
        * @description Contains a list of HTML elements that are invalid inside the editor. They will be removed when they are found. If you set the value of a key to "{ keepContents: true }", then the element will be replaced with a yui-non span to be filtered out when cleanHTML is called. The only tag that is ignored here is the span tag as it will force the Editor into a loop and freeze the browser. However.. all of these tags will be removed in the cleanHTML routine.
        * @type Object
        */
        invalidHTML: {
            form: true,
            input: true,
            button: true,
            select: true,
            link: true,
            html: true,
            body: true,
            iframe: true,
            script: true,
            style: true,
            textarea: true
        },
        /**
        * @property toolbar
        * @description Local property containing the <a href="YAHOO.widget.Toolbar.html">YAHOO.widget.Toolbar</a> instance
        * @type <a href="YAHOO.widget.Toolbar.html">YAHOO.widget.Toolbar</a>
        */
        toolbar: null,
        /**
        * @private
        * @property _contentTimer
        * @description setTimeout holder for documentReady check
        */
        _contentTimer: null,
        /**
        * @private
        * @property _contentTimerMax
        * @description The number of times the loaded content should be checked before giving up. Default: 500
        */
        _contentTimerMax: 500,
        /**
        * @private
        * @property _contentTimerCounter
        * @description Counter to check the number of times the body is polled for before giving up
        * @type Number
        */
        _contentTimerCounter: 0,
        /**
        * @private
        * @property _disabled
        * @description The Toolbar items that should be disabled if there is no selection present in the editor.
        * @type Array
        */
        _disabled: [ 'createlink', 'fontname', 'fontsize', 'forecolor', 'backcolor' ],
        /**
        * @private
        * @property _alwaysDisabled
        * @description The Toolbar items that should ALWAYS be disabled event if there is a selection present in the editor.
        * @type Object
        */
        _alwaysDisabled: { undo: true, redo: true },
        /**
        * @private
        * @property _alwaysEnabled
        * @description The Toolbar items that should ALWAYS be enabled event if there isn't a selection present in the editor.
        * @type Object
        */
        _alwaysEnabled: { },
        /**
        * @private
        * @property _semantic
        * @description The Toolbar commands that we should attempt to make tags out of instead of using styles.
        * @type Object
        */
        _semantic: { 'bold': true, 'italic' : true, 'underline' : true },
        /**
        * @private
        * @property _tag2cmd
        * @description A tag map of HTML tags to convert to the different types of commands so we can select the proper toolbar button.
        * @type Object
        */
        _tag2cmd: {
            'b': 'bold',
            'strong': 'bold',
            'i': 'italic',
            'em': 'italic',
            'u': 'underline',
            'sup': 'superscript',
            'sub': 'subscript',
            'img': 'insertimage',
            'a' : 'createlink',
            'ul' : 'insertunorderedlist',
            'ol' : 'insertorderedlist'
        },

        /**
        * @private _createIframe
        * @description Creates the DOM and YUI Element for the iFrame editor area.
        * @param {String} id The string ID to prefix the iframe with
        * @return {Object} iFrame object
        */
        _createIframe: function() {
            var ifrmDom = document.createElement('iframe');
            ifrmDom.id = this.get('id') + '_editor';
            var config = {
                border: '0',
                frameBorder: '0',
                marginWidth: '0',
                marginHeight: '0',
                leftMargin: '0',
                topMargin: '0',
                allowTransparency: 'true',
                width: '100%'
            };
            if (this.get('autoHeight')) {
                config.scrolling = 'no';
            }
            for (var i in config) {
                if (Lang.hasOwnProperty(config, i)) {
                    ifrmDom.setAttribute(i, config[i]);
                }
            }
            var isrc = 'javascript:;';
            if (this.browser.ie) {
                //isrc = 'about:blank';
                //TODO - Check this, I have changed it before..
                isrc = 'javascript:false;';
            }
            ifrmDom.setAttribute('src', isrc);
            var ifrm = new YAHOO.util.Element(ifrmDom);
            ifrm.setStyle('visibility', 'hidden');
            return ifrm;
        },
        /**
        * @private _isElement
        * @description Checks to see if an Element reference is a valid one and has a certain tag type
        * @param {HTMLElement} el The element to check
        * @param {String} tag The tag that the element needs to be
        * @return {Boolean}
        */
        _isElement: function(el, tag) {
            if (el && el.tagName && (el.tagName.toLowerCase() == tag)) {
                return true;
            }
            if (el && el.getAttribute && (el.getAttribute('tag') == tag)) {
                return true;
            }
            return false;
        },
        /**
        * @private _hasParent
        * @description Checks to see if an Element reference or one of it's parents is a valid one and has a certain tag type
        * @param {HTMLElement} el The element to check
        * @param {String} tag The tag that the element needs to be
        * @return HTMLElement
        */
        _hasParent: function(el, tag) {
            if (!el || !el.parentNode) {
                return false;
            }
            
            while (el.parentNode) {
                if (this._isElement(el, tag)) {
                    return el;
                }
                if (el.parentNode) {
                    el = el.parentNode;
                } else {
                    return false;
                }
            }
            return false;
        },
        /**
        * @private
        * @method _getDoc
        * @description Get the Document of the IFRAME
        * @return {Object}
        */
        _getDoc: function() {
            var value = false;
            try {
                if (this.get('iframe').get('element').contentWindow.document) {
                    value = this.get('iframe').get('element').contentWindow.document;
                    return value;
                }
            } catch (e) {
                return false;
            }
        },
        /**
        * @private
        * @method _getWindow
        * @description Get the Window of the IFRAME
        * @return {Object}
        */
        _getWindow: function() {
            return this.get('iframe').get('element').contentWindow;
        },
        /**
        * @method focus
        * @description Attempt to set the focus of the iframes window.
        */
        focus: function() {
            this._getWindow().focus();
        },
        /**
        * @private
        * @depreciated - This should not be used, moved to this.focus();
        * @method _focusWindow
        * @description Attempt to set the focus of the iframes window.
        */
        _focusWindow: function() {
            YAHOO.log('_focusWindow: depreciated in favor of this.focus()', 'warn', 'Editor');
            this.focus();
        },
        /**
        * @private
        * @method _hasSelection
        * @description Determines if there is a selection in the editor document.
        * @return {Boolean}
        */
        _hasSelection: function() {
            var sel = this._getSelection();
            var range = this._getRange();
            var hasSel = false;

            if (!sel || !range) {
                return hasSel;
            }

            //Internet Explorer
            if (this.browser.ie) {
                if (range.text) {
                    hasSel = true;
                }
                if (range.html) {
                    hasSel = true;
                }
            } else {
                if (this.browser.webkit) {
                    if (sel+'' !== '') {
                        hasSel = true;
                    }
                } else {
                    if (sel && (sel.toString() !== '') && (sel !== undefined)) {
                        hasSel = true;
                    }
                }
            }
            return hasSel;
        },
        /**
        * @private
        * @method _getSelection
        * @description Handles the different selection objects across the A-Grade list.
        * @return {Object} Selection Object
        */
        _getSelection: function() {
            var _sel = null;
            if (this._getDoc() && this._getWindow()) {
                if (this._getDoc().selection &&! this.browser.opera) {
                    _sel = this._getDoc().selection;
                } else {
                    _sel = this._getWindow().getSelection();
                }
                //Handle Safari's lack of Selection Object
                if (this.browser.webkit) {
                    if (_sel.baseNode) {
                            this._selection = {};
                            this._selection.baseNode = _sel.baseNode;
                            this._selection.baseOffset = _sel.baseOffset;
                            this._selection.extentNode = _sel.extentNode;
                            this._selection.extentOffset = _sel.extentOffset;
                    } else if (this._selection !== null) {
                        _sel = this._getWindow().getSelection();
                        _sel.setBaseAndExtent(
                            this._selection.baseNode,
                            this._selection.baseOffset,
                            this._selection.extentNode,
                            this._selection.extentOffset);
                        this._selection = null;
                    }
                }
            }
            return _sel;
        },
        /**
        * @private
        * @method _selectNode
        * @description Places the highlight around a given node
        * @param {HTMLElement} node The node to select
        */
        _selectNode: function(node, collapse) {
            if (!node) {
                return false;
            }
            var sel = this._getSelection(),
                range = null;

            if (this.browser.ie) {
                try { //IE freaks out here sometimes..
                    range = this._getDoc().body.createTextRange();
                    range.moveToElementText(node);
                    range.select();
                } catch (e) {
                    YAHOO.log('IE failed to select element: ' + node.tagName, 'warn', 'SimpleEditor');
                }
            } else if (this.browser.webkit) {
                if (collapse) {
				    sel.setBaseAndExtent(node, 1, node, node.innerText.length);
                } else {
				    sel.setBaseAndExtent(node, 0, node, node.innerText.length);
                }
            } else if (this.browser.opera) {
                sel = this._getWindow().getSelection();
                range = this._getDoc().createRange();
                range.selectNode(node);
                sel.removeAllRanges();
                sel.addRange(range);
            } else {
                range = this._getDoc().createRange();
                range.selectNodeContents(node);
                sel.removeAllRanges();
                sel.addRange(range);
            }
            //TODO - Check Performance
            this.nodeChange();
        },
        /**
        * @private
        * @method _getRange
        * @description Handles the different range objects across the A-Grade list.
        * @return {Object} Range Object
        */
        _getRange: function() {
            var sel = this._getSelection();

            if (sel === null) {
                return null;
            }

            if (this.browser.webkit && !sel.getRangeAt) {
                var _range = this._getDoc().createRange();
                try {
                    _range.setStart(sel.anchorNode, sel.anchorOffset);
                    _range.setEnd(sel.focusNode, sel.focusOffset);
                } catch (e) {
                    _range = this._getWindow().getSelection()+'';
                }
                return _range;
            }

            if (this.browser.ie) {
                try {
                    return sel.createRange();
                } catch (e2) {
                    return null;
                }
            }

            if (sel.rangeCount > 0) {
                return sel.getRangeAt(0);
            }
            return null;
        },
        /**
        * @private
        * @method _setDesignMode
        * @description Sets the designMode property of the iFrame document's body.
        * @param {String} state This should be either on or off
        */
        _setDesignMode: function(state) {
            if (this.get('setDesignMode')) {
                try {
                    this._getDoc().designMode = ((state.toLowerCase() == 'off') ? 'off' : 'on');
                } catch(e) { }
            }
        },
        /**
        * @private
        * @method _toggleDesignMode
        * @description Toggles the designMode property of the iFrame document on and off.
        * @return {String} The state that it was set to.
        */
        _toggleDesignMode: function() {
            YAHOO.log('It is not recommended to use this method and it will be depreciated.', 'warn', 'SimpleEditor');
            var _dMode = this._getDoc().designMode,
                _state = ((_dMode.toLowerCase() == 'on') ? 'off' : 'on');
            this._setDesignMode(_state);
            return _state;
        },
        /**
        * @private
        * @property _focused
        * @description Holder for trapping focus/blur state and prevent double events
        * @type Boolean
        */
        _focused: null,
        /**
        * @private
        * @method _handleFocus
        * @description Handles the focus of the iframe. Note, this is window focus event, not an Editor focus event.
        * @param {Event} e The DOM Event
        */
        _handleFocus: function(e) {
            if (!this._focused) {
                //YAHOO.log('Editor Window Focused', 'info', 'SimpleEditor');
                this._focused = true;
                this.fireEvent('editorWindowFocus', { type: 'editorWindowFocus', target: this });
            }
        },
        /**
        * @private
        * @method _handleBlur
        * @description Handles the blur of the iframe. Note, this is window blur event, not an Editor blur event.
        * @param {Event} e The DOM Event
        */
        _handleBlur: function(e) {
            if (this._focused) {
                //YAHOO.log('Editor Window Blurred', 'info', 'SimpleEditor');
                this._focused = false;
                this.fireEvent('editorWindowBlur', { type: 'editorWindowBlur', target: this });
            }
        },
        /**
        * @private
        * @method _initEditorEvents
        * @description This method sets up the listeners on the Editors document.
        */
        _initEditorEvents: function() {
            //Setup Listeners on iFrame
            var doc = this._getDoc(),
                win = this._getWindow();

            Event.on(doc, 'mouseup', this._handleMouseUp, this, true);
            Event.on(doc, 'mousedown', this._handleMouseDown, this, true);
            Event.on(doc, 'click', this._handleClick, this, true);
            Event.on(doc, 'dblclick', this._handleDoubleClick, this, true);
            Event.on(doc, 'keypress', this._handleKeyPress, this, true);
            Event.on(doc, 'keyup', this._handleKeyUp, this, true);
            Event.on(doc, 'keydown', this._handleKeyDown, this, true);
            /* TODO -- Everyone but Opera works here..
            Event.on(doc, 'paste', function() {
                YAHOO.log('PASTE', 'info', 'SimpleEditor');
            }, this, true);
            */
 
            //Focus and blur..
            Event.on(win, 'focus', this._handleFocus, this, true);
            Event.on(win, 'blur', this._handleBlur, this, true);
        },
        /**
        * @private
        * @method _removeEditorEvents
        * @description This method removes the listeners on the Editors document (for disabling).
        */
        _removeEditorEvents: function() {
            //Remove Listeners on iFrame
            var doc = this._getDoc(),
                win = this._getWindow();

            Event.removeListener(doc, 'mouseup', this._handleMouseUp, this, true);
            Event.removeListener(doc, 'mousedown', this._handleMouseDown, this, true);
            Event.removeListener(doc, 'click', this._handleClick, this, true);
            Event.removeListener(doc, 'dblclick', this._handleDoubleClick, this, true);
            Event.removeListener(doc, 'keypress', this._handleKeyPress, this, true);
            Event.removeListener(doc, 'keyup', this._handleKeyUp, this, true);
            Event.removeListener(doc, 'keydown', this._handleKeyDown, this, true);

            //Focus and blur..
            Event.removeListener(win, 'focus', this._handleFocus, this, true);
            Event.removeListener(win, 'blur', this._handleBlur, this, true);
        },
        _fixWebkitDivs: function() {
            if (this.browser.webkit) {
                var divs = this._getDoc().body.getElementsByTagName('div');
                Dom.addClass(divs, 'yui-wk-div');
            }
        },
        /**
        * @private
        * @method _initEditor
        * @param {Boolean} raw Don't add events.
        * @description This method is fired from _checkLoaded when the document is ready. It turns on designMode and set's up the listeners.
        */
        _initEditor: function(raw) {
            if (this._editorInit) {
                return;
            }
            this._editorInit = true;
            if (this.browser.ie) {
                this._getDoc().body.style.margin = '0';
            }
            if (!this.get('disabled')) {
                this._setDesignMode('on');
                this._contentTimerCounter = 0;
            }
            if (!this._getDoc().body) {
                YAHOO.log('Body is null, check again', 'error', 'SimpleEditor');
                this._contentTimerCounter = 0;
                this._editorInit = false;
                this._checkLoaded();
                return false;
            }
            
            YAHOO.log('editorLoaded', 'info', 'SimpleEditor');
            if (!raw) {
                this.toolbar.on('buttonClick', this._handleToolbarClick, this, true);
            }
            if (!this.get('disabled')) {
                this._initEditorEvents();
                this.toolbar.set('disabled', false);
            }

            if (raw) {
                this.fireEvent('editorContentReloaded', { type: 'editorreloaded', target: this });
            } else {
                this.fireEvent('editorContentLoaded', { type: 'editorLoaded', target: this });
            }
            this._fixWebkitDivs();
            if (this.get('dompath')) {
                YAHOO.log('Delayed DomPath write', 'info', 'SimpleEditor');
                var self = this;
                setTimeout(function() {
                    self._writeDomPath.call(self);
                    self._setupResize.call(self);
                }, 150);
            }
            var br = [];
            for (var i in this.browser) {
                if (this.browser[i]) {
                    br.push(i);
                }
            }
            if (this.get('ptags')) {
                br.push('ptags');
            }
            Dom.addClass(this._getDoc().body, br.join(' '));
            this.nodeChange(true);
        },
        /**
        * @private
        * @method _checkLoaded
        * @param {Boolean} raw Don't add events.
        * @description Called from a setTimeout loop to check if the iframes body.onload event has fired, then it will init the editor.
        */
        _checkLoaded: function(raw) {
            this._editorInit = false;
            this._contentTimerCounter++;
            if (this._contentTimer) {
                clearTimeout(this._contentTimer);
            }
            if (this._contentTimerCounter > this._contentTimerMax) {
                YAHOO.log('ERROR: Body Did Not load', 'error', 'SimpleEditor');
                return false;
            }
            var init = false;
            try {
                if (this._getDoc() && this._getDoc().body) {
                    if (this.browser.ie) {
                        if (this._getDoc().body.readyState == 'complete') {
                            init = true;
                        }
                    } else {
                        if (this._getDoc().body._rteLoaded === true) {
                            init = true;
                        }
                    }
                }
            } catch (e) {
                init = false;
                YAHOO.log('checking body (e)' + e, 'error', 'SimpleEditor');
            }

            if (init === true) {
                //The onload event has fired, clean up after ourselves and fire the _initEditor method
                YAHOO.log('Firing _initEditor', 'info', 'SimpleEditor');
                this._initEditor(raw);
            } else {
                var self = this;
                this._contentTimer = setTimeout(function() {
                    self._checkLoaded.call(self, raw);
                }, 20);
            }
        },
        /**
        * @private
        * @method _setInitialContent
        * @param {Boolean} raw Don't add events.
        * @description This method will open the iframes content document and write the textareas value into it, then start the body.onload checking.
        */
        _setInitialContent: function(raw) {
            YAHOO.log('Populating editor body with contents of the text area', 'info', 'SimpleEditor');

            var value = ((this._textarea) ? this.get('element').value : this.get('element').innerHTML),
                doc = null;

            if (value === '') {
                value = '<br>';
            }

            var html = Lang.substitute(this.get('html'), {
                TITLE: this.STR_TITLE,
                CONTENT: this._cleanIncomingHTML(value),
                CSS: this.get('css'),
                HIDDEN_CSS: ((this.get('hiddencss')) ? this.get('hiddencss') : '/* No Hidden CSS */'),
                EXTRA_CSS: ((this.get('extracss')) ? this.get('extracss') : '/* No Extra CSS */')
            }),
            check = true;

            html = html.replace(/RIGHT_BRACKET/gi, '{');
            html = html.replace(/LEFT_BRACKET/gi, '}');

            if (document.compatMode != 'BackCompat') {
                YAHOO.log('Adding Doctype to editable area', 'info', 'SimpleEditor');
                html = this._docType + "\n" + html;
            } else {
                YAHOO.log('DocType skipped because we are in BackCompat Mode.', 'warn', 'SimpleEditor');
            }

            if (this.browser.ie || this.browser.webkit || this.browser.opera || (navigator.userAgent.indexOf('Firefox/1.5') != -1)) {
                //Firefox 1.5 doesn't like setting designMode on an document created with a data url
                try {
                    //Adobe AIR Code
                    if (this.browser.air) {
                        doc = this._getDoc().implementation.createHTMLDocument();
                        var origDoc = this._getDoc();
                        origDoc.open();
                        origDoc.close();
                        doc.open();
                        doc.write(html);
                        doc.close();
                        var node = origDoc.importNode(doc.getElementsByTagName("html")[0], true);
                        origDoc.replaceChild(node, origDoc.getElementsByTagName("html")[0]);
                        origDoc.body._rteLoaded = true;
                    } else {
                        doc = this._getDoc();
                        doc.open();
                        doc.write(html);
                        doc.close();
                    }
                } catch (e) {
                    YAHOO.log('Setting doc failed.. (_setInitialContent)', 'error', 'SimpleEditor');
                    //Safari will only be here if we are hidden
                    check = false;
                }
            } else {
                //This keeps Firefox 2 from writing the iframe to history preserving the back buttons functionality
                this.get('iframe').get('element').src = 'data:text/html;charset=utf-8,' + encodeURIComponent(html);
            }
            this.get('iframe').setStyle('visibility', '');
            if (check) {
                this._checkLoaded(raw);
            }            
        },
        /**
        * @private
        * @method _setMarkupType
        * @param {String} action The action to take. Possible values are: css, default or semantic
        * @description This method will turn on/off the useCSS execCommand.
        */
        _setMarkupType: function(action) {
            switch (this.get('markup')) {
                case 'css':
                    this._setEditorStyle(true);
                    break;
                case 'default':
                    this._setEditorStyle(false);
                    break;
                case 'semantic':
                case 'xhtml':
                    if (this._semantic[action]) {
                        this._setEditorStyle(false);
                    } else {
                        this._setEditorStyle(true);
                    }
                    break;
            }
        },
        /**
        * Set the editor to use CSS instead of HTML
        * @param {Booleen} stat True/False
        */
        _setEditorStyle: function(stat) {
            try {
                this._getDoc().execCommand('useCSS', false, !stat);
            } catch (ex) {
            }
        },
        /**
        * @private
        * @method _getSelectedElement
        * @description This method will attempt to locate the element that was last interacted with, either via selection, location or event.
        * @return {HTMLElement} The currently selected element.
        */
        _getSelectedElement: function() {
            var doc = this._getDoc(),
                range = null,
                sel = null,
                elm = null,
                check = true;

            if (this.browser.ie) {
                this.currentEvent = this._getWindow().event; //Event utility assumes window.event, so we need to reset it to this._getWindow().event;
                range = this._getRange();
                if (range) {
                    elm = range.item ? range.item(0) : range.parentElement();
                    if (this._hasSelection()) {
                        //TODO
                        //WTF.. Why can't I get an element reference here?!??!
                    }
                    if (elm === doc.body) {
                        elm = null;
                    }
                }
                if ((this.currentEvent !== null) && (this.currentEvent.keyCode === 0)) {
                    elm = Event.getTarget(this.currentEvent);
                }
            } else {
                sel = this._getSelection();
                range = this._getRange();

                if (!sel || !range) {
                    return null;
                }
                //TODO
                if (!this._hasSelection() && this.browser.webkit3) {
                    //check = false;
                }
                if (this.browser.gecko) {
                    //Added in 2.6.0
                    if (range.startContainer) {
                        if (range.startContainer.nodeType === 3) {
                            elm = range.startContainer.parentNode;
                        } else if (range.startContainer.nodeType === 1) {
                            elm = range.startContainer;
                        }
                        //Added in 2.7.0
                        if (this.currentEvent) {
                            var tar = Event.getTarget(this.currentEvent);
                            if (!this._isElement(tar, 'html')) {
                                if (elm !== tar) {
                                    elm = tar;
                                }
                            }
                        }
                    }
                }
                
                if (check) {
                    if (sel.anchorNode && (sel.anchorNode.nodeType == 3)) {
                        if (sel.anchorNode.parentNode) { //next check parentNode
                            elm = sel.anchorNode.parentNode;
                        }
                        if (sel.anchorNode.nextSibling != sel.focusNode.nextSibling) {
                            elm = sel.anchorNode.nextSibling;
                        }
                    }
                    if (this._isElement(elm, 'br')) {
                        elm = null;
                    }
                    if (!elm) {
                        elm = range.commonAncestorContainer;
                        if (!range.collapsed) {
                            if (range.startContainer == range.endContainer) {
                                if (range.startOffset - range.endOffset < 2) {
                                    if (range.startContainer.hasChildNodes()) {
                                        elm = range.startContainer.childNodes[range.startOffset];
                                    }
                                }
                            }
                        }
                    }
               }
            }
            
            if (this.currentEvent !== null) {
                try {
                    switch (this.currentEvent.type) {
                        case 'click':
                        case 'mousedown':
                        case 'mouseup':
                            if (this.browser.webkit) {
                                elm = Event.getTarget(this.currentEvent);
                            }
                            break;
                        default:
                            //Do nothing
                            break;
                    }
                } catch (e) {
                    YAHOO.log('Firefox 1.5 errors here: ' + e, 'error', 'SimpleEditor');
                }
            } else if ((this.currentElement && this.currentElement[0]) && (!this.browser.ie)) {
                //TODO is this still needed?
                //elm = this.currentElement[0];
            }


            if (this.browser.opera || this.browser.webkit) {
                if (this.currentEvent && !elm) {
                    elm = YAHOO.util.Event.getTarget(this.currentEvent);
                }
            }
            if (!elm || !elm.tagName) {
                elm = doc.body;
            }
            if (this._isElement(elm, 'html')) {
                //Safari sometimes gives us the HTML node back..
                elm = doc.body;
            }
            if (this._isElement(elm, 'body')) {
                //make sure that body means this body not the parent..
                elm = doc.body;
            }
            if (elm && !elm.parentNode) { //Not in document
                elm = doc.body;
            }
            if (elm === undefined) {
                elm = null;
            }
            return elm;
        },
        /**
        * @private
        * @method _getDomPath
        * @description This method will attempt to build the DOM path from the currently selected element.
        * @param HTMLElement el The element to start with, if not provided _getSelectedElement is used
        * @return {Array} An array of node references that will create the DOM Path.
        */
        _getDomPath: function(el) {
            if (!el) {
			    el = this._getSelectedElement();
            }
			var domPath = [];
            while (el !== null) {
                if (el.ownerDocument != this._getDoc()) {
                    el = null;
                    break;
                }
                //Check to see if we get el.nodeName and nodeType
                if (el.nodeName && el.nodeType && (el.nodeType == 1)) {
                    domPath[domPath.length] = el;
                }

                if (this._isElement(el, 'body')) {
                    break;
                }

                el = el.parentNode;
            }
            if (domPath.length === 0) {
                if (this._getDoc() && this._getDoc().body) {
                    domPath[0] = this._getDoc().body;
                }
            }
            return domPath.reverse();
        },
        /**
        * @private
        * @method _writeDomPath
        * @description Write the current DOM path out to the dompath container below the editor.
        */
        _writeDomPath: function() { 
            var path = this._getDomPath(),
                pathArr = [],
                classPath = '',
                pathStr = '';

            for (var i = 0; i < path.length; i++) {
                var tag = path[i].tagName.toLowerCase();
                if ((tag == 'ol') && (path[i].type)) {
                    tag += ':' + path[i].type;
                }
                if (Dom.hasClass(path[i], 'yui-tag')) {
                    tag = path[i].getAttribute('tag');
                }
                if ((this.get('markup') == 'semantic') || (this.get('markup') == 'xhtml')) {
                    switch (tag) {
                        case 'b': tag = 'strong'; break;
                        case 'i': tag = 'em'; break;
                    }
                }
                if (!Dom.hasClass(path[i], 'yui-non')) {
                    if (Dom.hasClass(path[i], 'yui-tag')) {
                        pathStr = tag;
                    } else {
                        classPath = ((path[i].className !== '') ? '.' + path[i].className.replace(/ /g, '.') : '');
                        if ((classPath.indexOf('yui') != -1) || (classPath.toLowerCase().indexOf('apple-style-span') != -1)) {
                            classPath = '';
                        }
                        pathStr = tag + ((path[i].id) ? '#' + path[i].id : '') + classPath;
                    }
                    switch (tag) {
                        case 'body':
                            pathStr = 'body';
                            break;
                        case 'a':
                            if (path[i].getAttribute('href', 2)) {
                                pathStr += ':' + path[i].getAttribute('href', 2).replace('mailto:', '').replace('http:/'+'/', '').replace('https:/'+'/', ''); //May need to add others here ftp
                            }
                            break;
                        case 'img':
                            var h = path[i].height;
                            var w = path[i].width;
                            if (path[i].style.height) {
                                h = parseInt(path[i].style.height, 10);
                            }
                            if (path[i].style.width) {
                                w = parseInt(path[i].style.width, 10);
                            }
                            pathStr += '(' + w + 'x' + h + ')';
                        break;
                    }

                    if (pathStr.length > 10) {
                        pathStr = '<span title="' + pathStr + '">' + pathStr.substring(0, 10) + '...' + '</span>';
                    } else {
                        pathStr = '<span title="' + pathStr + '">' + pathStr + '</span>';
                    }
                    pathArr[pathArr.length] = pathStr;
                }
            }
            var str = pathArr.join(' ' + this.SEP_DOMPATH + ' ');
            //Prevent flickering
            if (this.dompath.innerHTML != str) {
                this.dompath.innerHTML = str;
            }
        },
        /**
        * @private
        * @method _fixNodes
        * @description Fix href and imgs as well as remove invalid HTML.
        */
        _fixNodes: function() {
            try {
                var doc = this._getDoc(),
                    els = [];

                for (var v in this.invalidHTML) {
                    if (YAHOO.lang.hasOwnProperty(this.invalidHTML, v)) {
                        if (v.toLowerCase() != 'span') {
                            var tags = doc.body.getElementsByTagName(v);
                            if (tags.length) {
                                for (var i = 0; i < tags.length; i++) {
                                    els.push(tags[i]);
                                }
                            }
                        }
                    }
                }
                for (var h = 0; h < els.length; h++) {
                    if (els[h].parentNode) {
                        if (Lang.isObject(this.invalidHTML[els[h].tagName.toLowerCase()]) && this.invalidHTML[els[h].tagName.toLowerCase()].keepContents) {
                            this._swapEl(els[h], 'span', function(el) {
                                el.className = 'yui-non';
                            });
                        } else {
                            els[h].parentNode.removeChild(els[h]);
                        }
                    }
                }
                var imgs = this._getDoc().getElementsByTagName('img');
                Dom.addClass(imgs, 'yui-img');
            } catch(e) {}
        },
        /**
        * @private
        * @method _isNonEditable
        * @param Event ev The Dom event being checked
        * @description Method is called at the beginning of all event handlers to check if this element or a parent element has the class yui-noedit (this.CLASS_NOEDIT) applied.
        * If it does, then this method will stop the event and return true. The event handlers will then return false and stop the nodeChange from occuring. This method will also
        * disable and enable the Editor's toolbar based on the noedit state.
        * @return Boolean
        */
        _isNonEditable: function(ev) {
            if (this.get('allowNoEdit')) {
                var el = Event.getTarget(ev);
                if (this._isElement(el, 'html')) {
                    el = null;
                }
                var path = this._getDomPath(el);
                for (var i = (path.length - 1); i > -1; i--) {
                    if (Dom.hasClass(path[i], this.CLASS_NOEDIT)) {
                        //if (this.toolbar.get('disabled') === false) {
                        //    this.toolbar.set('disabled', true);
                        //}
                        try {
                             this._getDoc().execCommand('enableObjectResizing', false, 'false');
                        } catch (e) {}
                        this.nodeChange();
                        Event.stopEvent(ev);
                        YAHOO.log('CLASS_NOEDIT found in DOM Path, stopping event', 'info', 'SimpleEditor');
                        return true;
                    }
                }
                //if (this.toolbar.get('disabled') === true) {
                    //Should only happen once..
                    //this.toolbar.set('disabled', false);
                    try {
                         this._getDoc().execCommand('enableObjectResizing', false, 'true');
                    } catch (e2) {}
                //}
            }
            return false;
        },
        /**
        * @private
        * @method _setCurrentEvent
        * @param {Event} ev The event to cache
        * @description Sets the current event property
        */
        _setCurrentEvent: function(ev) {
            this.currentEvent = ev;
        },
        /**
        * @private
        * @method _handleClick
        * @param {Event} ev The event we are working on.
        * @description Handles all click events inside the iFrame document.
        */
        _handleClick: function(ev) {
            var ret = this.fireEvent('beforeEditorClick', { type: 'beforeEditorClick', target: this, ev: ev });
            if (ret === false) {
                return false;
            }
            if (this._isNonEditable(ev)) {
                return false;
            }
            this._setCurrentEvent(ev);
            if (this.currentWindow) {
                this.closeWindow();
            }
            if (this.currentWindow) {
                this.closeWindow();
            }
            if (this.browser.webkit) {
                var tar =Event.getTarget(ev);
                if (this._isElement(tar, 'a') || this._isElement(tar.parentNode, 'a')) {
                    Event.stopEvent(ev);
                    this.nodeChange();
                }
            } else {
                this.nodeChange();
            }
            this.fireEvent('editorClick', { type: 'editorClick', target: this, ev: ev });
        },
        /**
        * @private
        * @method _handleMouseUp
        * @param {Event} ev The event we are working on.
        * @description Handles all mouseup events inside the iFrame document.
        */
        _handleMouseUp: function(ev) {
            var ret = this.fireEvent('beforeEditorMouseUp', { type: 'beforeEditorMouseUp', target: this, ev: ev });
            if (ret === false) {
                return false;
            }
            if (this._isNonEditable(ev)) {
                return false;
            }
            //Don't set current event for mouseup.
            //It get's fired after a menu is closed and gives up a bogus event to work with
            //this._setCurrentEvent(ev);
            var self = this;
            if (this.browser.opera) {
                /*
                * @knownissue Opera appears to stop the MouseDown, Click and DoubleClick events on an image inside of a document with designMode on..
                * @browser Opera
                * @description This work around traps the MouseUp event and sets a timer to check if another MouseUp event fires in so many seconds. If another event is fired, they we internally fire the DoubleClick event.
                */
                var sel = Event.getTarget(ev);
                if (this._isElement(sel, 'img')) {
                    this.nodeChange();
                    if (this.operaEvent) {
                        clearTimeout(this.operaEvent);
                        this.operaEvent = null;
                        this._handleDoubleClick(ev);
                    } else {
                        this.operaEvent = window.setTimeout(function() {
                            self.operaEvent = false;
                        }, 700);
                    }
                }
            }
            //This will stop Safari from selecting the entire document if you select all the text in the editor
            if (this.browser.webkit || this.browser.opera) {
                if (this.browser.webkit) {
                    Event.stopEvent(ev);
                }
            }
            this.nodeChange();
            this.fireEvent('editorMouseUp', { type: 'editorMouseUp', target: this, ev: ev });
        },
        /**
        * @private
        * @method _handleMouseDown
        * @param {Event} ev The event we are working on.
        * @description Handles all mousedown events inside the iFrame document.
        */
        _handleMouseDown: function(ev) {
            var ret = this.fireEvent('beforeEditorMouseDown', { type: 'beforeEditorMouseDown', target: this, ev: ev });
            if (ret === false) {
                return false;
            }
            if (this._isNonEditable(ev)) {
                return false;
            }
            this._setCurrentEvent(ev);
            var sel = Event.getTarget(ev);
            if (this.browser.webkit && this._hasSelection()) {
                var _sel = this._getSelection();
                if (!this.browser.webkit3) {
                    _sel.collapse(true);
                } else {
                    _sel.collapseToStart();
                }
            }
            if (this.browser.webkit && this._lastImage) {
                Dom.removeClass(this._lastImage, 'selected');
                this._lastImage = null;
            }
            if (this._isElement(sel, 'img') || this._isElement(sel, 'a')) {
                if (this.browser.webkit) {
                    Event.stopEvent(ev);
                    if (this._isElement(sel, 'img')) {
                        Dom.addClass(sel, 'selected');
                        this._lastImage = sel;
                    }
                }
                if (this.currentWindow) {
                    this.closeWindow();
                }
                this.nodeChange();
            }
            this.fireEvent('editorMouseDown', { type: 'editorMouseDown', target: this, ev: ev });
        },
        /**
        * @private
        * @method _handleDoubleClick
        * @param {Event} ev The event we are working on.
        * @description Handles all doubleclick events inside the iFrame document.
        */
        _handleDoubleClick: function(ev) {
            var ret = this.fireEvent('beforeEditorDoubleClick', { type: 'beforeEditorDoubleClick', target: this, ev: ev });
            if (ret === false) {
                return false;
            }
            if (this._isNonEditable(ev)) {
                return false;
            }
            this._setCurrentEvent(ev);
            var sel = Event.getTarget(ev);
            if (this._isElement(sel, 'img')) {
                this.currentElement[0] = sel;
                this.toolbar.fireEvent('insertimageClick', { type: 'insertimageClick', target: this.toolbar });
                this.fireEvent('afterExecCommand', { type: 'afterExecCommand', target: this });
            } else if (this._hasParent(sel, 'a')) { //Handle elements inside an a
                this.currentElement[0] = this._hasParent(sel, 'a');
                this.toolbar.fireEvent('createlinkClick', { type: 'createlinkClick', target: this.toolbar });
                this.fireEvent('afterExecCommand', { type: 'afterExecCommand', target: this });
            }
            this.nodeChange();
            this.fireEvent('editorDoubleClick', { type: 'editorDoubleClick', target: this, ev: ev });
        },
        /**
        * @private
        * @method _handleKeyUp
        * @param {Event} ev The event we are working on.
        * @description Handles all keyup events inside the iFrame document.
        */
        _handleKeyUp: function(ev) {
            var ret = this.fireEvent('beforeEditorKeyUp', { type: 'beforeEditorKeyUp', target: this, ev: ev });
            if (ret === false) {
                return false;
            }
            if (this._isNonEditable(ev)) {
                return false;
            }
            this._storeUndo();
            this._setCurrentEvent(ev);
            switch (ev.keyCode) {
                case this._keyMap.SELECT_ALL.key:
                    if (this._checkKey(this._keyMap.SELECT_ALL, ev)) {
                        this.nodeChange();
                    }
                    break;
                case 32: //Space Bar
                case 35: //End
                case 36: //Home
                case 37: //Left Arrow
                case 38: //Up Arrow
                case 39: //Right Arrow
                case 40: //Down Arrow
                case 46: //Forward Delete
                case 8: //Delete
                case this._keyMap.CLOSE_WINDOW.key: //W key if window is open
                    if ((ev.keyCode == this._keyMap.CLOSE_WINDOW.key) && this.currentWindow) {
                        if (this._checkKey(this._keyMap.CLOSE_WINDOW, ev)) {
                            this.closeWindow();
                        }
                    } else {
                        if (!this.browser.ie) {
                            if (this._nodeChangeTimer) {
                                clearTimeout(this._nodeChangeTimer);
                            }
                            var self = this;
                            this._nodeChangeTimer = setTimeout(function() {
                                self._nodeChangeTimer = null;
                                self.nodeChange.call(self);
                            }, 100);
                        } else {
                            this.nodeChange();
                        }
                        this.editorDirty = true;
                    }
                    break;
            }
            this.fireEvent('editorKeyUp', { type: 'editorKeyUp', target: this, ev: ev });
        },
        /**
        * @private
        * @method _handleKeyPress
        * @param {Event} ev The event we are working on.
        * @description Handles all keypress events inside the iFrame document.
        */
        _handleKeyPress: function(ev) {
            var ret = this.fireEvent('beforeEditorKeyPress', { type: 'beforeEditorKeyPress', target: this, ev: ev });
            if (ret === false) {
                return false;
            }

            if (this.get('allowNoEdit')) {
                //if (ev && ev.keyCode && ((ev.keyCode == 46) || ev.keyCode == 63272)) {
                if (ev && ev.keyCode && (ev.keyCode == 63272)) {
                    //Forward delete key
                    YAHOO.log('allowNoEdit is set, forward delete key has been disabled', 'warn', 'SimpleEditor');
                    Event.stopEvent(ev);
                }
            }
            if (this._isNonEditable(ev)) {
                return false;
            }
            this._setCurrentEvent(ev);
            this._storeUndo();
            if (this.browser.opera) {
                if (ev.keyCode === 13) {
                    var tar = this._getSelectedElement();
                    if (!this._isElement(tar, 'li')) {
                        this.execCommand('inserthtml', '<br>');
                        Event.stopEvent(ev);
                    }
                }
            }
            if (this.browser.webkit) {
                if (!this.browser.webkit3) {
                    if (ev.keyCode && (ev.keyCode == 122) && (ev.metaKey)) {
                        //This is CMD + z (for undo)
                        if (this._hasParent(this._getSelectedElement(), 'li')) {
                            YAHOO.log('We are in an LI and we found CMD + z, stopping the event', 'warn', 'SimpleEditor');
                            Event.stopEvent(ev);
                        }
                    }
                }
                this._listFix(ev);
            }
            this._fixListDupIds();
            this.fireEvent('editorKeyPress', { type: 'editorKeyPress', target: this, ev: ev });
        },
        /**
        * @private
        * @method _handleKeyDown
        * @param {Event} ev The event we are working on.
        * @description Handles all keydown events inside the iFrame document.
        */
        _handleKeyDown: function(ev) {
            var ret = this.fireEvent('beforeEditorKeyDown', { type: 'beforeEditorKeyDown', target: this, ev: ev });
            if (ret === false) {
                return false;
            }
            var tar = null, _range = null;
            if (this._isNonEditable(ev)) {
                return false;
            }
            this._setCurrentEvent(ev);
            if (this.currentWindow) {
                this.closeWindow();
            }
            if (this.currentWindow) {
                this.closeWindow();
            }
            var doExec = false,
                action = null,
                value = null,
                exec = false;

            //YAHOO.log('keyCode: ' + ev.keyCode, 'info', 'SimpleEditor');

            switch (ev.keyCode) {
                case this._keyMap.FOCUS_TOOLBAR.key:
                    if (this._checkKey(this._keyMap.FOCUS_TOOLBAR, ev)) {
                        var h = this.toolbar.getElementsByTagName('h2')[0];
                        if (h && h.firstChild) {
                            h.firstChild.focus();
                        }
                    } else if (this._checkKey(this._keyMap.FOCUS_AFTER, ev)) {
                        //Focus After Element - Esc
                        this.afterElement.focus();
                    }
                    Event.stopEvent(ev);
                    doExec = false;
                    break;
                //case 76: //L
                case this._keyMap.CREATE_LINK.key: //L
                    if (this._hasSelection()) {
                        if (this._checkKey(this._keyMap.CREATE_LINK, ev)) {
                            var makeLink = true;
                            if (this.get('limitCommands')) {
                                if (!this.toolbar.getButtonByValue('createlink')) {
                                    YAHOO.log('Toolbar Button for (createlink) was not found, skipping exec.', 'info', 'SimpleEditor');
                                    makeLink = false;
                                }
                            }
                            if (makeLink) {
                                this.execCommand('createlink', '');
                                this.toolbar.fireEvent('createlinkClick', { type: 'createlinkClick', target: this.toolbar });
                                this.fireEvent('afterExecCommand', { type: 'afterExecCommand', target: this });
                                doExec = false;
                            }
                        }
                    }
                    break;
                //case 90: //Z
                case this._keyMap.UNDO.key:
                case this._keyMap.REDO.key:
                    if (this._checkKey(this._keyMap.REDO, ev)) {
                        action = 'redo';
                        doExec = true;
                    } else if (this._checkKey(this._keyMap.UNDO, ev)) {
                        action = 'undo';
                        doExec = true;
                    }
                    break;
                //case 66: //B
                case this._keyMap.BOLD.key:
                    if (this._checkKey(this._keyMap.BOLD, ev)) {
                        action = 'bold';
                        doExec = true;
                    }
                    break;
                case this._keyMap.FONT_SIZE_UP.key:
                case this._keyMap.FONT_SIZE_DOWN.key:
                    var uk = false, dk = false;
                    if (this._checkKey(this._keyMap.FONT_SIZE_UP, ev)) {
                        uk = true;
                    }
                    if (this._checkKey(this._keyMap.FONT_SIZE_DOWN, ev)) {
                        dk = true;
                    }
                    if (uk || dk) {
                        var fs_button = this.toolbar.getButtonByValue('fontsize'),
                            label = parseInt(fs_button.get('label'), 10),
                            newValue = (label + 1);

                        if (dk) {
                            newValue = (label - 1);
                        }

                        action = 'fontsize';
                        value = newValue + 'px';
                        doExec = true;
                    }
                    break;
                //case 73: //I
                case this._keyMap.ITALIC.key:
                    if (this._checkKey(this._keyMap.ITALIC, ev)) {
                        action = 'italic';
                        doExec = true;
                    }
                    break;
                //case 85: //U
                case this._keyMap.UNDERLINE.key:
                    if (this._checkKey(this._keyMap.UNDERLINE, ev)) {
                        action = 'underline';
                        doExec = true;
                    }
                    break;
                case 9:
                    if (this.browser.ie) {
                        //Insert a tab in Internet Explorer
                        _range = this._getRange();
                        tar = this._getSelectedElement();
                        if (!this._isElement(tar, 'li')) {
                            if (_range) {
                                _range.pasteHTML('&nbsp;&nbsp;&nbsp;&nbsp;');
                                _range.collapse(false);
                                _range.select();
                            }
                            Event.stopEvent(ev);
                        }
                    }
                    //Firefox 3 code
                    if (this.browser.gecko > 1.8) {
                        tar = this._getSelectedElement();
                        if (this._isElement(tar, 'li')) {
                            if (ev.shiftKey) {
                                this._getDoc().execCommand('outdent', null, '');
                            } else {
                                this._getDoc().execCommand('indent', null, '');
                            }
                            
                        } else if (!this._hasSelection()) {
                            this.execCommand('inserthtml', '&nbsp;&nbsp;&nbsp;&nbsp;');
                        }
                        Event.stopEvent(ev);
                    }
                    break;
                case 13:
                    var p = null, i = 0;
                    if (this.get('ptags') && !ev.shiftKey) {
                        if (this.browser.gecko) {
                            tar = this._getSelectedElement();
                            if (!this._hasParent(tar, 'li')) {
                                if (this._hasParent(tar, 'p')) {
                                    p = this._getDoc().createElement('p');
                                    p.innerHTML = '&nbsp;';
                                    Dom.insertAfter(p, tar);
                                    this._selectNode(p.firstChild);
                                } else if (this._isElement(tar, 'body')) {
                                    this.execCommand('insertparagraph', null);
                                    var ps = this._getDoc().body.getElementsByTagName('p');
                                    for (i = 0; i < ps.length; i++) {
                                        if (ps[i].getAttribute('_moz_dirty') !== null) {
                                            p = this._getDoc().createElement('p');
                                            p.innerHTML = '&nbsp;';
                                            Dom.insertAfter(p, ps[i]);
                                            this._selectNode(p.firstChild);
                                            ps[i].removeAttribute('_moz_dirty');
                                        }
                                    }
                                } else {
                                    YAHOO.log('Something went wrong with paragraphs, please file a bug!!', 'error', 'SimpleEditor');
                                    doExec = true;
                                    action = 'insertparagraph';
                                }
                                Event.stopEvent(ev);
                            }
                        }
                        if (this.browser.webkit) {
                            tar = this._getSelectedElement();
                            if (!this._hasParent(tar, 'li')) {
                                this.execCommand('insertparagraph', null);
                                var divs = this._getDoc().body.getElementsByTagName('div');
                                for (i = 0; i < divs.length; i++) {
                                    if (!Dom.hasClass(divs[i], 'yui-wk-div')) {
                                        Dom.addClass(divs[i], 'yui-wk-p');
                                    }
                                }
                                Event.stopEvent(ev);
                            }
                        }
                    } else {
                        if (this.browser.webkit) {
                            tar = this._getSelectedElement();
                            if (!this._hasParent(tar, 'li')) {
                                if (this.browser.webkit4) {
                                    this.execCommand('insertlinebreak');
                                } else {
                                    this.execCommand('inserthtml', '<var id="yui-br"></var>');
                                    var holder = this._getDoc().getElementById('yui-br'),
                                        br = this._getDoc().createElement('br'),
                                        caret = this._getDoc().createElement('span');

                                    holder.parentNode.replaceChild(br, holder);
                                    caret.className = 'yui-non';
                                    caret.innerHTML = '&nbsp;';
                                    Dom.insertAfter(caret, br);
                                    this._selectNode(caret);
                                }
                                Event.stopEvent(ev);
                            }
                        }
                        if (this.browser.ie) {
                            YAHOO.log('Stopping P tags', 'info', 'SimpleEditor');
                            //Insert a <br> instead of a <p></p> in Internet Explorer
                            _range = this._getRange();
                            tar = this._getSelectedElement();
                            if (!this._isElement(tar, 'li')) {
                                if (_range) {
                                    _range.pasteHTML('<br>');
                                    _range.collapse(false);
                                    _range.select();
                                }
                                Event.stopEvent(ev);
                            }
                        }
                    }
                    break;
            }
            if (this.browser.ie) {
                this._listFix(ev);
            }
            if (doExec && action) {
                this.execCommand(action, value);
                Event.stopEvent(ev);
                this.nodeChange();
            }
            this._storeUndo();
            this.fireEvent('editorKeyDown', { type: 'editorKeyDown', target: this, ev: ev });
        },
        /**
        * @private
        * @property _fixListRunning
        * @type Boolean
        * @description Keeps more than one _fixListDupIds from running at the same time.
        */
        _fixListRunning: null,
        /**
        * @private
        * @method _fixListDupIds
        * @description Some browsers will duplicate the id of an LI when created in designMode.
        * This method will fix the duplicate id issue. However it will only preserve the first element 
        * in the document list with the unique id. 
        */
        _fixListDupIds: function() {
            if (this._fixListRunning) {
                return false;
            }
            if (this._getDoc()) {
                this._fixListRunning = true;
                var lis = this._getDoc().body.getElementsByTagName('li'),
                    i = 0, ids = {};
                for (i = 0; i < lis.length; i++) {
                    if (lis[i].id) {
                        if (ids[lis[i].id]) {
                            lis[i].id = '';
                        }
                        ids[lis[i].id] = true;
                    }
                }
                this._fixListRunning = false;
            }
        },
        /**
        * @private
        * @method _listFix
        * @param {Event} ev The event we are working on.
        * @description Handles the Enter key, Tab Key and Shift + Tab keys for List Items.
        */
        _listFix: function(ev) {
            //YAHOO.log('Lists Fix (' + ev.keyCode + ')', 'info', 'SimpleEditor');
            var testLi = null, par = null, preContent = false, range = null;
            //Enter Key
            if (this.browser.webkit) {
                if (ev.keyCode && (ev.keyCode == 13)) {
                    if (this._hasParent(this._getSelectedElement(), 'li')) {
                        var tar = this._hasParent(this._getSelectedElement(), 'li');
                        if (tar.previousSibling) {
                            if (tar.firstChild && (tar.firstChild.length == 1)) {
                                this._selectNode(tar);
                            }
                        }
                    }
                }
            }
            //Shift + Tab Key
            if (ev.keyCode && ((!this.browser.webkit3 && (ev.keyCode == 25)) || ((this.browser.webkit3 || !this.browser.webkit) && ((ev.keyCode == 9) && ev.shiftKey)))) {
                testLi = this._getSelectedElement();
                if (this._hasParent(testLi, 'li')) {
                    testLi = this._hasParent(testLi, 'li');
                    YAHOO.log('We have a SHIFT tab in an LI, reverse it..', 'info', 'SimpleEditor');
                    if (this._hasParent(testLi, 'ul') || this._hasParent(testLi, 'ol')) {
                        YAHOO.log('We have a double parent, move up a level', 'info', 'SimpleEditor');
                        par = this._hasParent(testLi, 'ul');
                        if (!par) {
                            par = this._hasParent(testLi, 'ol');
                        }
                        //YAHOO.log(par.previousSibling + ' :: ' + par.previousSibling.innerHTML);
                        if (this._isElement(par.previousSibling, 'li')) {
                            par.removeChild(testLi);
                            par.parentNode.insertBefore(testLi, par.nextSibling);
                            if (this.browser.ie) {
                                range = this._getDoc().body.createTextRange();
                                range.moveToElementText(testLi);
                                range.collapse(false);
                                range.select();
                            }
                            if (this.browser.webkit) {
                                this._selectNode(testLi.firstChild);
                            }
                            Event.stopEvent(ev);
                        }
                    }
                }
            }
            //Tab Key
            if (ev.keyCode && ((ev.keyCode == 9) && (!ev.shiftKey))) {
                YAHOO.log('List Fix - Tab', 'info', 'SimpleEditor');
                var preLi = this._getSelectedElement();
                if (this._hasParent(preLi, 'li')) {
                    preContent = this._hasParent(preLi, 'li').innerHTML;
                }
                //YAHOO.log('preLI: ' + preLi.tagName + ' :: ' + preLi.innerHTML);
                if (this.browser.webkit) {
                    this._getDoc().execCommand('inserttext', false, '\t');
                }
                testLi = this._getSelectedElement();
                if (this._hasParent(testLi, 'li')) {
                    YAHOO.log('We have a tab in an LI', 'info', 'SimpleEditor');
                    par = this._hasParent(testLi, 'li');
                    YAHOO.log('parLI: ' + par.tagName + ' :: ' + par.innerHTML);
                    var newUl = this._getDoc().createElement(par.parentNode.tagName.toLowerCase());
                    if (this.browser.webkit) {
                        var span = Dom.getElementsByClassName('Apple-tab-span', 'span', par);
                        //Remove the span element that Safari puts in
                        if (span[0]) {
                            par.removeChild(span[0]);
                            par.innerHTML = Lang.trim(par.innerHTML);
                            //Put the HTML from the LI into this new LI
                            if (preContent) {
                                par.innerHTML = '<span class="yui-non">' + preContent + '</span>&nbsp;';
                            } else {
                                par.innerHTML = '<span class="yui-non">&nbsp;</span>&nbsp;';
                            }
                        }
                    } else {
                        if (preContent) {
                            par.innerHTML = preContent + '&nbsp;';
                        } else {
                            par.innerHTML = '&nbsp;';
                        }
                    }

                    par.parentNode.replaceChild(newUl, par);
                    newUl.appendChild(par);
                    if (this.browser.webkit) {
                        this._getSelection().setBaseAndExtent(par.firstChild, 1, par.firstChild, par.firstChild.innerText.length);
                        if (!this.browser.webkit3) {
                            par.parentNode.parentNode.style.display = 'list-item';
                            setTimeout(function() {
                                par.parentNode.parentNode.style.display = 'block';
                            }, 1);
                        }
                    } else if (this.browser.ie) {
                        range = this._getDoc().body.createTextRange();
                        range.moveToElementText(par);
                        range.collapse(false);
                        range.select();
                    } else {
                        this._selectNode(par);
                    }
                    Event.stopEvent(ev);
                }
                if (this.browser.webkit) {
                    Event.stopEvent(ev);
                }
                this.nodeChange();
            }
        },
        /**
        * @method nodeChange
        * @param {Boolean} force Optional paramenter to skip the threshold counter
        * @description Handles setting up the toolbar buttons, getting the Dom path, fixing nodes.
        */
        nodeChange: function(force) {
            var NCself = this;
            this._storeUndo();
            if (this.get('nodeChangeDelay')) {
                this._nodeChangeDelayTimer = window.setTimeout(function() {
                    NCself._nodeChangeDelayTimer = null;
                    NCself._nodeChange.apply(NCself, arguments);
                }, 0);
            } else {
                this._nodeChange();
            }
        },
        /**
        * @private
        * @method _nodeChange
        * @param {Boolean} force Optional paramenter to skip the threshold counter
        * @description Fired from nodeChange in a setTimeout.
        */
        _nodeChange: function(force) {
            var threshold = parseInt(this.get('nodeChangeThreshold'), 10),
                thisNodeChange = Math.round(new Date().getTime() / 1000),
                self = this;

            if (force === true) {
                this._lastNodeChange = 0;
            }
            
            if ((this._lastNodeChange + threshold) < thisNodeChange) {
                if (this._fixNodesTimer === null) {
                    this._fixNodesTimer = window.setTimeout(function() {
                        self._fixNodes.call(self);
                        self._fixNodesTimer = null;
                    }, 0);
                }
            }
            this._lastNodeChange = thisNodeChange;
            if (this.currentEvent) {
                try {
                    this._lastNodeChangeEvent = this.currentEvent.type;
                } catch (e) {}
            }

            var beforeNodeChange = this.fireEvent('beforeNodeChange', { type: 'beforeNodeChange', target: this });
            if (beforeNodeChange === false) {
                return false;
            }
            if (this.get('dompath')) {
                window.setTimeout(function() {
                    self._writeDomPath.call(self);
                }, 0);
            }
            //Check to see if we are disabled before continuing
            if (!this.get('disabled')) {
                if (this.STOP_NODE_CHANGE) {
                    //Reset this var for next action
                    this.STOP_NODE_CHANGE = false;
                    return false;
                } else {
                    var sel = this._getSelection(),
                        range = this._getRange(),
                        el = this._getSelectedElement(),
                        fn_button = this.toolbar.getButtonByValue('fontname'),
                        fs_button = this.toolbar.getButtonByValue('fontsize'),
                        undo_button = this.toolbar.getButtonByValue('undo'),
                        redo_button = this.toolbar.getButtonByValue('redo');

                    //Handle updating the toolbar with active buttons
                    var _ex = {};
                    if (this._lastButton) {
                        _ex[this._lastButton.id] = true;
                        //this._lastButton = null;
                    }
                    if (!this._isElement(el, 'body')) {
                        if (fn_button) {
                            _ex[fn_button.get('id')] = true;
                        }
                        if (fs_button) {
                            _ex[fs_button.get('id')] = true;
                        }
                    }
                    if (redo_button) {
                        delete _ex[redo_button.get('id')];
                    }
                    this.toolbar.resetAllButtons(_ex);

                    //Handle disabled buttons
                    for (var d = 0; d < this._disabled.length; d++) {
                        var _button = this.toolbar.getButtonByValue(this._disabled[d]);
                        if (_button && _button.get) {
                            if (this._lastButton && (_button.get('id') === this._lastButton.id)) {
                                //Skip
                            } else {
                                if (!this._hasSelection() && !this.get('insert')) {
                                    switch (this._disabled[d]) {
                                        case 'fontname':
                                        case 'fontsize':
                                            break;
                                        default:
                                            //No Selection - disable
                                            this.toolbar.disableButton(_button);
                                    }
                                } else {
                                    if (!this._alwaysDisabled[this._disabled[d]]) {
                                        this.toolbar.enableButton(_button);
                                    }
                                }
                                if (!this._alwaysEnabled[this._disabled[d]]) {
                                    this.toolbar.deselectButton(_button);
                                }
                            }
                        }
                    }
                    var path = this._getDomPath();
                    var tag = null, cmd = null;
                    for (var i = 0; i < path.length; i++) {
                        tag = path[i].tagName.toLowerCase();
                        if (path[i].getAttribute('tag')) {
                            tag = path[i].getAttribute('tag').toLowerCase();
                        }
                        cmd = this._tag2cmd[tag];
                        if (cmd === undefined) {
                            cmd = [];
                        }
                        if (!Lang.isArray(cmd)) {
                            cmd = [cmd];
                        }

                        //Bold and Italic styles
                        if (path[i].style.fontWeight.toLowerCase() == 'bold') {
                            cmd[cmd.length] = 'bold';
                        }
                        if (path[i].style.fontStyle.toLowerCase() == 'italic') {
                            cmd[cmd.length] = 'italic';
                        }
                        if (path[i].style.textDecoration.toLowerCase() == 'underline') {
                            cmd[cmd.length] = 'underline';
                        }
                        if (path[i].style.textDecoration.toLowerCase() == 'line-through') {
                            cmd[cmd.length] = 'strikethrough';
                        }
                        if (cmd.length > 0) {
                            for (var j = 0; j < cmd.length; j++) {
                                this.toolbar.selectButton(cmd[j]);
                                this.toolbar.enableButton(cmd[j]);
                            }
                        }
                        //Handle Alignment
                        switch (path[i].style.textAlign.toLowerCase()) {
                            case 'left':
                            case 'right':
                            case 'center':
                            case 'justify':
                                var alignType = path[i].style.textAlign.toLowerCase();
                                if (path[i].style.textAlign.toLowerCase() == 'justify') {
                                    alignType = 'full';
                                }
                                this.toolbar.selectButton('justify' + alignType);
                                this.toolbar.enableButton('justify' + alignType);
                                break;
                        }
                    }
                    //After for loop

                    //Reset Font Family and Size to the inital configs
                    if (fn_button) {
                        var family = fn_button._configs.label._initialConfig.value;
                        fn_button.set('label', '<span class="yui-toolbar-fontname-' + this._cleanClassName(family) + '">' + family + '</span>');
                        this._updateMenuChecked('fontname', family);
                    }

                    if (fs_button) {
                        fs_button.set('label', fs_button._configs.label._initialConfig.value);
                    }

                    var hd_button = this.toolbar.getButtonByValue('heading');
                    if (hd_button) {
                        hd_button.set('label', hd_button._configs.label._initialConfig.value);
                        this._updateMenuChecked('heading', 'none');
                    }
                    var img_button = this.toolbar.getButtonByValue('insertimage');
                    if (img_button && this.currentWindow && (this.currentWindow.name == 'insertimage')) {
                        this.toolbar.disableButton(img_button);
                    }
                    if (this._lastButton && this._lastButton.isSelected) {
                        this.toolbar.deselectButton(this._lastButton.id);
                    }
                    this._undoNodeChange();
                }
            }

            this.fireEvent('afterNodeChange', { type: 'afterNodeChange', target: this });
        },
        /**
        * @private
        * @method _updateMenuChecked
        * @param {Object} button The command identifier of the button you want to check
        * @param {String} value The value of the menu item you want to check
        * @param {<a href="YAHOO.widget.Toolbar.html">YAHOO.widget.Toolbar</a>} The Toolbar instance the button belongs to (defaults to this.toolbar) 
        * @description Gets the menu from a button instance, if the menu is not rendered it will render it. It will then search the menu for the specified value, unchecking all other items and checking the specified on.
        */
        _updateMenuChecked: function(button, value, tbar) {
            if (!tbar) {
                tbar = this.toolbar;
            }
            var _button = tbar.getButtonByValue(button);
            _button.checkValue(value);
        },
        /**
        * @private
        * @method _handleToolbarClick
        * @param {Event} ev The event that triggered the button click
        * @description This is an event handler attached to the Toolbar's buttonClick event. It will fire execCommand with the command identifier from the Toolbar Button.
        */
        _handleToolbarClick: function(ev) {
            var value = '';
            var str = '';
            var cmd = ev.button.value;
            if (ev.button.menucmd) {
                value = cmd;
                cmd = ev.button.menucmd;
            }
            this._lastButton = ev.button;
            if (this.STOP_EXEC_COMMAND) {
                YAHOO.log('execCommand skipped because we found the STOP_EXEC_COMMAND flag set to true', 'warn', 'SimpleEditor');
                YAHOO.log('NOEXEC::execCommand::(' + cmd + '), (' + value + ')', 'warn', 'SimpleEditor');
                this.STOP_EXEC_COMMAND = false;
                return false;
            } else {
                this.execCommand(cmd, value);
                if (!this.browser.webkit) {
                     var Fself = this;
                     setTimeout(function() {
                         Fself.focus.call(Fself);
                     }, 5);
                 }
            }
            Event.stopEvent(ev);
        },
        /**
        * @private
        * @method _setupAfterElement
        * @description Creates the accessibility h2 header and places it after the iframe in the Dom for navigation.
        */
        _setupAfterElement: function() {
            if (!this.beforeElement) {
                this.beforeElement = document.createElement('h2');
                this.beforeElement.className = 'yui-editor-skipheader';
                this.beforeElement.tabIndex = '-1';
                this.beforeElement.innerHTML = this.STR_BEFORE_EDITOR;
                this.get('element_cont').get('firstChild').insertBefore(this.beforeElement, this.toolbar.get('nextSibling'));
            }
            if (!this.afterElement) {
                this.afterElement = document.createElement('h2');
                this.afterElement.className = 'yui-editor-skipheader';
                this.afterElement.tabIndex = '-1';
                this.afterElement.innerHTML = this.STR_LEAVE_EDITOR;
                this.get('element_cont').get('firstChild').appendChild(this.afterElement);
            }
        },
        /**
        * @private
        * @method _disableEditor
        * @param {Boolean} disabled Pass true to disable, false to enable
        * @description Creates a mask to place over the Editor.
        */
        _disableEditor: function(disabled) {
            var iframe, par, html, height;
            if (!this.get('disabled_iframe')) {
                iframe = this._createIframe();
                iframe.set('id', 'disabled_' + this.get('iframe').get('id'));
                iframe.setStyle('height', '100%');
                iframe.setStyle('display', 'none');
                iframe.setStyle('visibility', 'visible');
                this.set('disabled_iframe', iframe);
                par = this.get('iframe').get('parentNode');
                par.appendChild(iframe.get('element'));
            }
            if (!iframe) {
                iframe = this.get('disabled_iframe');
            }
            if (disabled) {
                this._orgIframe = this.get('iframe');

                if (this.toolbar) {
                    this.toolbar.set('disabled', true);
                }

                html = this.getEditorHTML();
                height = this.get('iframe').get('offsetHeight');
                iframe.setStyle('visibility', '');
                iframe.setStyle('position', '');
                iframe.setStyle('top', '');
                iframe.setStyle('left', '');
                this._orgIframe.setStyle('visibility', 'hidden');
                this._orgIframe.setStyle('position', 'absolute');
                this._orgIframe.setStyle('top', '-99999px');
                this._orgIframe.setStyle('left', '-99999px');
                this.set('iframe', iframe);
                this._setInitialContent(true);
                
                if (!this._mask) {
                    this._mask = document.createElement('DIV');
                    Dom.addClass(this._mask, 'yui-editor-masked');
                    if (this.browser.ie) {
                        this._mask.style.height = height + 'px';
                    }
                    this.get('iframe').get('parentNode').appendChild(this._mask);
                }
                this.on('editorContentReloaded', function() {
                    this._getDoc().body._rteLoaded = false;
                    this.setEditorHTML(html);
                    iframe.setStyle('display', 'block');
                    this.unsubscribeAll('editorContentReloaded');
                });
            } else {
                if (this._mask) {
                    this._mask.parentNode.removeChild(this._mask);
                    this._mask = null;
                    if (this.toolbar) {
                        this.toolbar.set('disabled', false);
                    }
                    iframe.setStyle('visibility', 'hidden');
                    iframe.setStyle('position', 'absolute');
                    iframe.setStyle('top', '-99999px');
                    iframe.setStyle('left', '-99999px');
                    this._orgIframe.setStyle('visibility', '');
                    this._orgIframe.setStyle('position', '');
                    this._orgIframe.setStyle('top', '');
                    this._orgIframe.setStyle('left', '');
                    this.set('iframe', this._orgIframe);

                    this.focus();
                    var self = this;
                    window.setTimeout(function() {
                        self.nodeChange.call(self);
                    }, 100);
                }
            }
        },
        /**
        * @property SEP_DOMPATH
        * @description The value to place in between the Dom path items
        * @type String
        */
        SEP_DOMPATH: '<',
        /**
        * @property STR_LEAVE_EDITOR
        * @description The accessibility string for the element after the iFrame
        * @type String
        */
        STR_LEAVE_EDITOR: 'You have left the Rich Text Editor.',
        /**
        * @property STR_BEFORE_EDITOR
        * @description The accessibility string for the element before the iFrame
        * @type String
        */
        STR_BEFORE_EDITOR: 'This text field can contain stylized text and graphics. To cycle through all formatting options, use the keyboard shortcut Shift + Escape to place focus on the toolbar and navigate between options with your arrow keys. To exit this text editor use the Escape key and continue tabbing. <h4>Common formatting keyboard shortcuts:</h4><ul><li>Control Shift B sets text to bold</li> <li>Control Shift I sets text to italic</li> <li>Control Shift U underlines text</li> <li>Control Shift L adds an HTML link</li></ul>',
        /**
        * @property STR_TITLE
        * @description The Title of the HTML document that is created in the iFrame
        * @type String
        */
        STR_TITLE: 'Rich Text Area.',
        /**
        * @property STR_IMAGE_HERE
        * @description The text to place in the URL textbox when using the blankimage.
        * @type String
        */
        STR_IMAGE_HERE: 'Image URL Here',
        /**
        * @property STR_IMAGE_URL
        * @description The label string for Image URL
        * @type String
        */
        STR_IMAGE_URL: 'Image URL',        
        /**
        * @property STR_LINK_URL
        * @description The label string for the Link URL.
        * @type String
        */
        STR_LINK_URL: 'Link URL',
        /**
        * @protected
        * @property STOP_EXEC_COMMAND
        * @description Set to true when you want the default execCommand function to not process anything
        * @type Boolean
        */
        STOP_EXEC_COMMAND: false,
        /**
        * @protected
        * @property STOP_NODE_CHANGE
        * @description Set to true when you want the default nodeChange function to not process anything
        * @type Boolean
        */
        STOP_NODE_CHANGE: false,
        /**
        * @protected
        * @property CLASS_NOEDIT
        * @description CSS class applied to elements that are not editable.
        * @type String
        */
        CLASS_NOEDIT: 'yui-noedit',
        /**
        * @protected
        * @property CLASS_CONTAINER
        * @description Default CSS class to apply to the editors container element
        * @type String
        */
        CLASS_CONTAINER: 'yui-editor-container',
        /**
        * @protected
        * @property CLASS_EDITABLE
        * @description Default CSS class to apply to the editors iframe element
        * @type String
        */
        CLASS_EDITABLE: 'yui-editor-editable',
        /**
        * @protected
        * @property CLASS_EDITABLE_CONT
        * @description Default CSS class to apply to the editors iframe's parent element
        * @type String
        */
        CLASS_EDITABLE_CONT: 'yui-editor-editable-container',
        /**
        * @protected
        * @property CLASS_PREFIX
        * @description Default prefix for dynamically created class names
        * @type String
        */
        CLASS_PREFIX: 'yui-editor',
        /** 
        * @property browser
        * @description Standard browser detection
        * @type Object
        */
        browser: function() {
            var br = YAHOO.env.ua;
            //Check for webkit3
            if (br.webkit >= 420) {
                br.webkit3 = br.webkit;
            } else {
                br.webkit3 = 0;
            }
            if (br.webkit >= 530) {
                br.webkit4 = br.webkit;
            } else {
                br.webkit4 = 0;
            }
            br.mac = false;
            //Check for Mac
            if (navigator.userAgent.indexOf('Macintosh') !== -1) {
                br.mac = true;
            }

            return br;
        }(),
        /** 
        * @method init
        * @description The Editor class' initialization method
        */
        init: function(p_oElement, p_oAttributes) {
            YAHOO.log('init', 'info', 'SimpleEditor');

            if (!this._defaultToolbar) {
                this._defaultToolbar = {
                    collapse: true,
                    titlebar: 'Text Editing Tools',
                    draggable: false,
                    buttons: [
                        { group: 'fontstyle', label: 'Font Name and Size',
                            buttons: [
                                { type: 'select', label: 'Arial', value: 'fontname', disabled: true,
                                    menu: [
                                        { text: 'Arial', checked: true },
                                        { text: 'Arial Black' },
                                        { text: 'Comic Sans MS' },
                                        { text: 'Courier New' },
                                        { text: 'Lucida Console' },
                                        { text: 'Tahoma' },
                                        { text: 'Times New Roman' },
                                        { text: 'Trebuchet MS' },
                                        { text: 'Verdana' }
                                    ]
                                },
                                { type: 'spin', label: '13', value: 'fontsize', range: [ 9, 75 ], disabled: true }
                            ]
                        },
                        { type: 'separator' },
                        { group: 'textstyle', label: 'Font Style',
                            buttons: [
                                { type: 'push', label: 'Bold CTRL + SHIFT + B', value: 'bold' },
                                { type: 'push', label: 'Italic CTRL + SHIFT + I', value: 'italic' },
                                { type: 'push', label: 'Underline CTRL + SHIFT + U', value: 'underline' },
                                { type: 'push', label: 'Strike Through', value: 'strikethrough' },
                                { type: 'separator' },
                                { type: 'color', label: 'Font Color', value: 'forecolor', disabled: true },
                                { type: 'color', label: 'Background Color', value: 'backcolor', disabled: true }
                                
                            ]
                        },
                        { type: 'separator' },
                        { group: 'indentlist', label: 'Lists',
                            buttons: [
                                { type: 'push', label: 'Create an Unordered List', value: 'insertunorderedlist' },
                                { type: 'push', label: 'Create an Ordered List', value: 'insertorderedlist' }
                            ]
                        },
                        { type: 'separator' },
                        { group: 'insertitem', label: 'Insert Item',
                            buttons: [
                                { type: 'push', label: 'HTML Link CTRL + SHIFT + L', value: 'createlink', disabled: true },
                                { type: 'push', label: 'Insert Image', value: 'insertimage' }
                            ]
                        }
                    ]
                };
            }

            YAHOO.widget.SimpleEditor.superclass.init.call(this, p_oElement, p_oAttributes);
            YAHOO.widget.EditorInfo._instances[this.get('id')] = this;


            this.currentElement = [];
            this.on('contentReady', function() {
                this.DOMReady = true;
                this.fireQueue();
            }, this, true);

        },
        /**
        * @method initAttributes
        * @description Initializes all of the configuration attributes used to create 
        * the editor.
        * @param {Object} attr Object literal specifying a set of 
        * configuration attributes used to create the editor.
        */
        initAttributes: function(attr) {
            YAHOO.widget.SimpleEditor.superclass.initAttributes.call(this, attr);
            var self = this;

            /**
            * @config setDesignMode
            * @description Should the Editor set designMode on the document. Default: true.
            * @default true
            * @type Boolean
            */
            this.setAttributeConfig('setDesignMode', {
                value: ((attr.setDesignMode === false) ? false : true)
            });
            /**
            * @config nodeChangeDelay
            * @description Do we wrap the nodeChange method in a timeout for performance. Default: true.
            * @default true
            * @type Boolean
            */
            this.setAttributeConfig('nodeChangeDelay', {
                value: ((attr.nodeChangeDelay === false) ? false : true)
            });
            /**
            * @config maxUndo
            * @description The max number of undo levels to store.
            * @default 30
            * @type Number
            */
            this.setAttributeConfig('maxUndo', {
                writeOnce: true,
                value: attr.maxUndo || 30
            });

            /**
            * @config ptags
            * @description If true, the editor uses &lt;P&gt; tags instead of &lt;br&gt; tags. (Use Shift + Enter to get a &lt;br&gt;)
            * @default false
            * @type Boolean
            */
            this.setAttributeConfig('ptags', {
                writeOnce: true,
                value: attr.ptags || false
            });
            /**
            * @config insert
            * @description If true, selection is not required for: fontname, fontsize, forecolor, backcolor.
            * @default false
            * @type Boolean
            */
            this.setAttributeConfig('insert', {
                writeOnce: true,
                value: attr.insert || false,
                method: function(insert) {
                    if (insert) {
                        var buttons = {
                            fontname: true,
                            fontsize: true,
                            forecolor: true,
                            backcolor: true
                        };
                        var tmp = this._defaultToolbar.buttons;
                        for (var i = 0; i < tmp.length; i++) {
                            if (tmp[i].buttons) {
                                for (var a = 0; a < tmp[i].buttons.length; a++) {
                                    if (tmp[i].buttons[a].value) {
                                        if (buttons[tmp[i].buttons[a].value]) {
                                            delete tmp[i].buttons[a].disabled;
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            });
            /**
            * @config container
            * @description Used when dynamically creating the Editor from Javascript with no default textarea.
            * We will create one and place it in this container. If no container is passed we will append to document.body.
            * @default false
            * @type HTMLElement
            */
            this.setAttributeConfig('container', {
                writeOnce: true,
                value: attr.container || false
            });
            /**
            * @config plainText
            * @description Process the inital textarea data as if it was plain text. Accounting for spaces, tabs and line feeds.
            * @default false
            * @type Boolean
            */
            this.setAttributeConfig('plainText', {
                writeOnce: true,
                value: attr.plainText || false
            });
            /**
            * @private
            * @config iframe
            * @description Internal config for holding the iframe element.
            * @default null
            * @type HTMLElement
            */
            this.setAttributeConfig('iframe', {
                value: null
            });
            /**
            * @private
            * @config disabled_iframe
            * @description Internal config for holding the iframe element used when disabling the Editor.
            * @default null
            * @type HTMLElement
            */
            this.setAttributeConfig('disabled_iframe', {
                value: null
            });
            /**
            * @private
            * @depreciated - No longer used, should use this.get('element')
            * @config textarea
            * @description Internal config for holding the textarea element (replaced with element).
            * @default null
            * @type HTMLElement
            */
            this.setAttributeConfig('textarea', {
                value: null,
                writeOnce: true
            });
            /**
            * @config nodeChangeThreshold
            * @description The number of seconds that need to be in between nodeChange processing
            * @default 3
            * @type Number
            */            
            this.setAttributeConfig('nodeChangeThreshold', {
                value: attr.nodeChangeThreshold || 3,
                validator: YAHOO.lang.isNumber
            });
            /**
            * @config allowNoEdit
            * @description Should the editor check for non-edit fields. It should be noted that this technique is not perfect. If the user does the right things, they will still be able to make changes.
            * Such as highlighting an element below and above the content and hitting a toolbar button or a shortcut key.
            * @default false
            * @type Boolean
            */            
            this.setAttributeConfig('allowNoEdit', {
                value: attr.allowNoEdit || false,
                validator: YAHOO.lang.isBoolean
            });
            /**
            * @config limitCommands
            * @description Should the Editor limit the allowed execCommands to the ones available in the toolbar. If true, then execCommand and keyboard shortcuts will fail if they are not defined in the toolbar.
            * @default false
            * @type Boolean
            */            
            this.setAttributeConfig('limitCommands', {
                value: attr.limitCommands || false,
                validator: YAHOO.lang.isBoolean
            });
            /**
            * @config element_cont
            * @description Internal config for the editors container
            * @default false
            * @type HTMLElement
            */
            this.setAttributeConfig('element_cont', {
                value: attr.element_cont
            });
            /**
            * @private
            * @config editor_wrapper
            * @description The outter wrapper for the entire editor.
            * @default null
            * @type HTMLElement
            */
            this.setAttributeConfig('editor_wrapper', {
                value: attr.editor_wrapper || null,
                writeOnce: true
            });
            /**
            * @attribute height
            * @description The height of the editor iframe container, not including the toolbar..
            * @default Best guessed size of the textarea, for best results use CSS to style the height of the textarea or pass it in as an argument
            * @type String
            */
            this.setAttributeConfig('height', {
                value: attr.height || Dom.getStyle(self.get('element'), 'height'),
                method: function(height) {
                    if (this._rendered) {
                        //We have been rendered, change the height
                        if (this.get('animate')) {
                            var anim = new YAHOO.util.Anim(this.get('iframe').get('parentNode'), {
                                height: {
                                    to: parseInt(height, 10)
                                }
                            }, 0.5);
                            anim.animate();
                        } else {
                            Dom.setStyle(this.get('iframe').get('parentNode'), 'height', height);
                        }
                    }
                }
            });
            /**
            * @config autoHeight
            * @description Remove the scrollbars from the edit area and resize it to fit the content. It will not go any lower than the current config height.
            * @default false
            * @type Boolean || Number
            */
            this.setAttributeConfig('autoHeight', {
                value: attr.autoHeight || false,
                method: function(a) {
                    if (a) {
                        if (this.get('iframe')) {
                            this.get('iframe').get('element').setAttribute('scrolling', 'no');
                        }
                        this.on('afterNodeChange', this._handleAutoHeight, this, true);
                        this.on('editorKeyDown', this._handleAutoHeight, this, true);
                        this.on('editorKeyPress', this._handleAutoHeight, this, true);
                    } else {
                        if (this.get('iframe')) {
                            this.get('iframe').get('element').setAttribute('scrolling', 'auto');
                        }
                        this.unsubscribe('afterNodeChange', this._handleAutoHeight);
                        this.unsubscribe('editorKeyDown', this._handleAutoHeight);
                        this.unsubscribe('editorKeyPress', this._handleAutoHeight);
                    }
                }
            });
            /**
            * @attribute width
            * @description The width of the editor container.
            * @default Best guessed size of the textarea, for best results use CSS to style the width of the textarea or pass it in as an argument
            * @type String
            */            
            this.setAttributeConfig('width', {
                value: attr.width || Dom.getStyle(this.get('element'), 'width'),
                method: function(width) {
                    if (this._rendered) {
                        //We have been rendered, change the width
                        if (this.get('animate')) {
                            var anim = new YAHOO.util.Anim(this.get('element_cont').get('element'), {
                                width: {
                                    to: parseInt(width, 10)
                                }
                            }, 0.5);
                            anim.animate();
                        } else {
                            this.get('element_cont').setStyle('width', width);
                        }
                    }
                }
            });
                        
            /**
            * @attribute blankimage
            * @description The URL for the image placeholder to put in when inserting an image.
            * @default The yahooapis.com address for the current release + 'assets/blankimage.png'
            * @type String
            */            
            this.setAttributeConfig('blankimage', {
                value: attr.blankimage || this._getBlankImage()
            });
            /**
            * @attribute css
            * @description The Base CSS used to format the content of the editor
            * @default <code><pre>html {
                height: 95%;
            }
            body {
                height: 100%;
                padding: 7px; background-color: #fff; font:13px/1.22 arial,helvetica,clean,sans-serif;*font-size:small;*font:x-small;
            }
            a {
                color: blue;
                text-decoration: underline;
                cursor: pointer;
            }
            .warning-localfile {
                border-bottom: 1px dashed red !important;
            }
            .yui-busy {
                cursor: wait !important;
            }
            img.selected { //Safari image selection
                border: 2px dotted #808080;
            }
            img {
                cursor: pointer !important;
                border: none;
            }
            </pre></code>
            * @type String
            */            
            this.setAttributeConfig('css', {
                value: attr.css || this._defaultCSS,
                writeOnce: true
            });
            /**
            * @attribute html
            * @description The default HTML to be written to the iframe document before the contents are loaded (Note that the DOCTYPE attr will be added at render item)
            * @default This HTML requires a few things if you are to override:
                <p><code>{TITLE}, {CSS}, {HIDDEN_CSS}, {EXTRA_CSS}</code> and <code>{CONTENT}</code> need to be there, they are passed to YAHOO.lang.substitute to be replace with other strings.<p>
                <p><code>onload="document.body._rteLoaded = true;"</code> : the onload statement must be there or the editor will not finish loading.</p>
                <code>
                <pre>
                &lt;html&gt;
                    &lt;head&gt;
                        &lt;title&gt;{TITLE}&lt;/title&gt;
                        &lt;meta http-equiv="Content-Type" content="text/html; charset=UTF-8" /&gt;
                        &lt;style&gt;
                        {CSS}
                        &lt;/style&gt;
                        &lt;style&gt;
                        {HIDDEN_CSS}
                        &lt;/style&gt;
                        &lt;style&gt;
                        {EXTRA_CSS}
                        &lt;/style&gt;
                    &lt;/head&gt;
                &lt;body onload="document.body._rteLoaded = true;"&gt;
                {CONTENT}
                &lt;/body&gt;
                &lt;/html&gt;
                </pre>
                </code>
            * @type String
            */            
            this.setAttributeConfig('html', {
                value: attr.html || '<html><head><title>{TITLE}</title><meta http-equiv="Content-Type" content="text/html; charset=UTF-8" /><base href="' + this._baseHREF + '"><style>{CSS}</style><style>{HIDDEN_CSS}</style><style>{EXTRA_CSS}</style></head><body onload="document.body._rteLoaded = true;">{CONTENT}</body></html>',
                writeOnce: true
            });

            /**
            * @attribute extracss
            * @description Extra user defined css to load after the default SimpleEditor CSS
            * @default ''
            * @type String
            */            
            this.setAttributeConfig('extracss', {
                value: attr.extracss || '',
                writeOnce: true
            });

            /**
            * @attribute handleSubmit
            * @description Config handles if the editor will attach itself to the textareas parent form's submit handler.
            If it is set to true, the editor will attempt to attach a submit listener to the textareas parent form.
            Then it will trigger the editors save handler and place the new content back into the text area before the form is submitted.
            * @default false
            * @type Boolean
            */            
            this.setAttributeConfig('handleSubmit', {
                value: attr.handleSubmit || false,
                method: function(exec) {
                    if (this.get('element').form) {
                        if (!this._formButtons) {
                            this._formButtons = [];
                        }
                        if (exec) {
                            Event.on(this.get('element').form, 'submit', this._handleFormSubmit, this, true);
                            var i = this.get('element').form.getElementsByTagName('input');
                            for (var s = 0; s < i.length; s++) {
                                var type = i[s].getAttribute('type');
                                if (type && (type.toLowerCase() == 'submit')) {
                                    Event.on(i[s], 'click', this._handleFormButtonClick, this, true);
                                    this._formButtons[this._formButtons.length] = i[s];
                                }
                            }
                        } else {
                            Event.removeListener(this.get('element').form, 'submit', this._handleFormSubmit);
                            if (this._formButtons) {
                                Event.removeListener(this._formButtons, 'click', this._handleFormButtonClick);
                            }
                        }
                    }
                }
            });
            /**
            * @attribute disabled
            * @description This will toggle the editor's disabled state. When the editor is disabled, designMode is turned off and a mask is placed over the iframe so no interaction can take place.
            All Toolbar buttons are also disabled so they cannot be used.
            * @default false
            * @type Boolean
            */

            this.setAttributeConfig('disabled', {
                value: false,
                method: function(disabled) {
                    if (this._rendered) {
                        this._disableEditor(disabled);
                    }
                }
            });
            /**
            * @config saveEl
            * @description When save HTML is called, this element will be updated as well as the source of data.
            * @default element
            * @type HTMLElement
            */
            this.setAttributeConfig('saveEl', {
                value: this.get('element')
            });
            /**
            * @config toolbar_cont
            * @description Internal config for the toolbars container
            * @default false
            * @type Boolean
            */
            this.setAttributeConfig('toolbar_cont', {
                value: null,
                writeOnce: true
            });
            /**
            * @attribute toolbar
            * @description The default toolbar config.
            * @type Object
            */            
            this.setAttributeConfig('toolbar', {
                value: attr.toolbar || this._defaultToolbar,
                writeOnce: true,
                method: function(toolbar) {
                    if (!toolbar.buttonType) {
                        toolbar.buttonType = this._defaultToolbar.buttonType;
                    }
                    this._defaultToolbar = toolbar;
                }
            });
            /**
            * @attribute animate
            * @description Should the editor animate window movements
            * @default false unless Animation is found, then true
            * @type Boolean
            */            
            this.setAttributeConfig('animate', {
                value: ((attr.animate) ? ((YAHOO.util.Anim) ? true : false) : false),
                validator: function(value) {
                    var ret = true;
                    if (!YAHOO.util.Anim) {
                        ret = false;
                    }
                    return ret;
                }
            });
            /**
            * @config panel
            * @description A reference to the panel we are using for windows.
            * @default false
            * @type Boolean
            */            
            this.setAttributeConfig('panel', {
                value: null,
                writeOnce: true,
                validator: function(value) {
                    var ret = true;
                    if (!YAHOO.widget.Overlay) {
                        ret = false;
                    }
                    return ret;
                }               
            });
            /**
            * @attribute focusAtStart
            * @description Should we focus the window when the content is ready?
            * @default false
            * @type Boolean
            */            
            this.setAttributeConfig('focusAtStart', {
                value: attr.focusAtStart || false,
                writeOnce: true,
                method: function(fs) {
                    if (fs) {
                        this.on('editorContentLoaded', function() {
                            var self = this;
                            setTimeout(function() {
                                self.focus.call(self);
                                self.editorDirty = false;
                            }, 400);
                        }, this, true);
                    }
                }
            });
            /**
            * @attribute dompath
            * @description Toggle the display of the current Dom path below the editor
            * @default false
            * @type Boolean
            */            
            this.setAttributeConfig('dompath', {
                value: attr.dompath || false,
                method: function(dompath) {
                    if (dompath && !this.dompath) {
                        this.dompath = document.createElement('DIV');
                        this.dompath.id = this.get('id') + '_dompath';
                        Dom.addClass(this.dompath, 'dompath');
                        this.get('element_cont').get('firstChild').appendChild(this.dompath);
                        if (this.get('iframe')) {
                            this._writeDomPath();
                        }
                    } else if (!dompath && this.dompath) {
                        this.dompath.parentNode.removeChild(this.dompath);
                        this.dompath = null;
                    }
                }
            });
            /**
            * @attribute markup
            * @description Should we try to adjust the markup for the following types: semantic, css, default or xhtml
            * @default "semantic"
            * @type String
            */            
            this.setAttributeConfig('markup', {
                value: attr.markup || 'semantic',
                validator: function(markup) {
                    switch (markup.toLowerCase()) {
                        case 'semantic':
                        case 'css':
                        case 'default':
                        case 'xhtml':
                        return true;
                    }
                    return false;
                }
            });
            /**
            * @attribute removeLineBreaks
            * @description Should we remove linebreaks and extra spaces on cleanup
            * @default false
            * @type Boolean
            */            
            this.setAttributeConfig('removeLineBreaks', {
                value: attr.removeLineBreaks || false,
                validator: YAHOO.lang.isBoolean
            });
            
            /**
            * @config drag
            * @description Set this config to make the Editor draggable, pass 'proxy' to make use YAHOO.util.DDProxy.
            * @type {Boolean/String}
            */
            this.setAttributeConfig('drag', {
                writeOnce: true,
                value: attr.drag || false
            });

            /**
            * @config resize
            * @description Set this to true to make the Editor Resizable with YAHOO.util.Resize. The default config is available: myEditor._resizeConfig
            * Animation will be ignored while performing this resize to allow for the dynamic change in size of the toolbar.
            * @type Boolean
            */
            this.setAttributeConfig('resize', {
                writeOnce: true,
                value: attr.resize || false
            });

            /**
            * @config filterWord
            * @description Attempt to filter out MS Word HTML from the Editor's output.
            * @type Boolean
            */
            this.setAttributeConfig('filterWord', {
                value: attr.filterWord || false,
                validator: YAHOO.lang.isBoolean
            });

        },
        /**
        * @private
        * @method _getBlankImage
        * @description Retrieves the full url of the image to use as the blank image.
        * @return {String} The URL to the blank image
        */
        _getBlankImage: function() {
            if (!this.DOMReady) {
                this._queue[this._queue.length] = ['_getBlankImage', arguments];
                return '';
            }
            var img = '';
            if (!this._blankImageLoaded) {
                if (YAHOO.widget.EditorInfo.blankImage) {
                    this.set('blankimage', YAHOO.widget.EditorInfo.blankImage);
                    this._blankImageLoaded = true;
                } else {
                    var div = document.createElement('div');
                    div.style.position = 'absolute';
                    div.style.top = '-9999px';
                    div.style.left = '-9999px';
                    div.className = this.CLASS_PREFIX + '-blankimage';
                    document.body.appendChild(div);
                    img = YAHOO.util.Dom.getStyle(div, 'background-image');
                    img = img.replace('url(', '').replace(')', '').replace(/"/g, '');
                    //Adobe AIR Code
                    img = img.replace('app:/', '');             
                    this.set('blankimage', img);
                    this._blankImageLoaded = true;
                    div.parentNode.removeChild(div);
                    YAHOO.widget.EditorInfo.blankImage = img;
                }
            } else {
                img = this.get('blankimage');
            }
            return img;
        },
        /**
        * @private
        * @method _handleAutoHeight
        * @description Handles resizing the editor's height based on the content
        */
        _handleAutoHeight: function() {
            var doc = this._getDoc(),
                body = doc.body,
                docEl = doc.documentElement;

            var height = parseInt(Dom.getStyle(this.get('editor_wrapper'), 'height'), 10);
            var newHeight = body.scrollHeight;
            if (this.browser.webkit) {
                newHeight = docEl.scrollHeight;
            }
            if (newHeight < parseInt(this.get('height'), 10)) {
                newHeight = parseInt(this.get('height'), 10);
            }
            if ((height != newHeight) && (newHeight >= parseInt(this.get('height'), 10))) {   
                var anim = this.get('animate');
                this.set('animate', false);
                this.set('height', newHeight + 'px');
                this.set('animate', anim);
                if (this.browser.ie) {
                    //Internet Explorer needs this
                    this.get('iframe').setStyle('height', '99%');
                    this.get('iframe').setStyle('zoom', '1');
                    var self = this;
                    window.setTimeout(function() {
                        self.get('iframe').setStyle('height', '100%');
                    }, 1);
                }
            }
        },
        /**
        * @private
        * @property _formButtons
        * @description Array of buttons that are in the Editor's parent form (for handleSubmit)
        * @type Array
        */
        _formButtons: null,
        /**
        * @private
        * @property _formButtonClicked
        * @description The form button that was clicked to submit the form.
        * @type HTMLElement
        */
        _formButtonClicked: null,
        /**
        * @private
        * @method _handleFormButtonClick
        * @description The click listener assigned to each submit button in the Editor's parent form.
        * @param {Event} ev The click event
        */
        _handleFormButtonClick: function(ev) {
            var tar = Event.getTarget(ev);
            this._formButtonClicked = tar;
        },
        /**
        * @private
        * @method _handleFormSubmit
        * @description Handles the form submission.
        * @param {Object} ev The Form Submit Event
        */
        _handleFormSubmit: function(ev) {
            this.saveHTML();

            var form = this.get('element').form,
                tar = this._formButtonClicked || false;

            Event.removeListener(form, 'submit', this._handleFormSubmit);
            if (YAHOO.env.ua.ie) {
                //form.fireEvent("onsubmit");
                if (tar && !tar.disabled) {
                    tar.click();
                }
            } else {  // Gecko, Opera, and Safari
                if (tar && !tar.disabled) {
                    tar.click();
                }
                var oEvent = document.createEvent("HTMLEvents");
                oEvent.initEvent("submit", true, true);
                form.dispatchEvent(oEvent);
                if (YAHOO.env.ua.webkit) {
                    if (YAHOO.lang.isFunction(form.submit)) {
                        form.submit();
                    }
                }
            }
            //2.6.0
            //Removed this, not need since removing Safari 2.x
            //Event.stopEvent(ev);
        },
        /**
        * @private
        * @method _handleFontSize
        * @description Handles the font size button in the toolbar.
        * @param {Object} o Object returned from Toolbar's buttonClick Event
        */
        _handleFontSize: function(o) {
            var button = this.toolbar.getButtonById(o.button.id);
            var value = button.get('label') + 'px';
            this.execCommand('fontsize', value);
            return false;
        },
        /**
        * @private
        * @description Handles the colorpicker buttons in the toolbar.
        * @param {Object} o Object returned from Toolbar's buttonClick Event
        */
        _handleColorPicker: function(o) {
            var cmd = o.button;
            var value = '#' + o.color;
            if ((cmd == 'forecolor') || (cmd == 'backcolor')) {
                this.execCommand(cmd, value);
            }
        },
        /**
        * @private
        * @method _handleAlign
        * @description Handles the alignment buttons in the toolbar.
        * @param {Object} o Object returned from Toolbar's buttonClick Event
        */
        _handleAlign: function(o) {
            var cmd = null;
            for (var i = 0; i < o.button.menu.length; i++) {
                if (o.button.menu[i].value == o.button.value) {
                    cmd = o.button.menu[i].value;
                }
            }
            var value = this._getSelection();

            this.execCommand(cmd, value);
            return false;
        },
        /**
        * @private
        * @method _handleAfterNodeChange
        * @description Fires after a nodeChange happens to setup the things that where reset on the node change (button state).
        */
        _handleAfterNodeChange: function() {
            var path = this._getDomPath(),
                elm = null,
                family = null,
                fontsize = null,
                validFont = false,
                fn_button = this.toolbar.getButtonByValue('fontname'),
                fs_button = this.toolbar.getButtonByValue('fontsize'),
                hd_button = this.toolbar.getButtonByValue('heading');

            for (var i = 0; i < path.length; i++) {
                elm = path[i];

                var tag = elm.tagName.toLowerCase();


                if (elm.getAttribute('tag')) {
                    tag = elm.getAttribute('tag');
                }

                family = elm.getAttribute('face');
                if (Dom.getStyle(elm, 'font-family')) {
                    family = Dom.getStyle(elm, 'font-family');
                    //Adobe AIR Code
                    family = family.replace(/'/g, '');                    
                }

                if (tag.substring(0, 1) == 'h') {
                    if (hd_button) {
                        for (var h = 0; h < hd_button._configs.menu.value.length; h++) {
                            if (hd_button._configs.menu.value[h].value.toLowerCase() == tag) {
                                hd_button.set('label', hd_button._configs.menu.value[h].text);
                            }
                        }
                        this._updateMenuChecked('heading', tag);
                    }
                }
            }

            if (fn_button) {
                for (var b = 0; b < fn_button._configs.menu.value.length; b++) {
                    if (family && fn_button._configs.menu.value[b].text.toLowerCase() == family.toLowerCase()) {
                        validFont = true;
                        family = fn_button._configs.menu.value[b].text; //Put the proper menu name in the button
                    }
                }
                if (!validFont) {
                    family = fn_button._configs.label._initialConfig.value;
                }
                var familyLabel = '<span class="yui-toolbar-fontname-' + this._cleanClassName(family) + '">' + family + '</span>';
                if (fn_button.get('label') != familyLabel) {
                    fn_button.set('label', familyLabel);
                    this._updateMenuChecked('fontname', family);
                }
            }

            if (fs_button) {
                fontsize = parseInt(Dom.getStyle(elm, 'fontSize'), 10);
                if ((fontsize === null) || isNaN(fontsize)) {
                    fontsize = fs_button._configs.label._initialConfig.value;
                }
                fs_button.set('label', ''+fontsize);
            }
            
            if (!this._isElement(elm, 'body') && !this._isElement(elm, 'img')) {
                this.toolbar.enableButton(fn_button);
                this.toolbar.enableButton(fs_button);
                this.toolbar.enableButton('forecolor');
                this.toolbar.enableButton('backcolor');
            }
            if (this._isElement(elm, 'img')) {
                if (YAHOO.widget.Overlay) {
                    this.toolbar.enableButton('createlink');
                }
            }
            if (this._hasParent(elm, 'blockquote')) {
                this.toolbar.selectButton('indent');
                this.toolbar.disableButton('indent');
                this.toolbar.enableButton('outdent');
            }
            if (this._hasParent(elm, 'ol') || this._hasParent(elm, 'ul')) {
                this.toolbar.disableButton('indent');
            }
            this._lastButton = null;
            
        },
        /**
        * @private
        * @method _handleInsertImageClick
        * @description Opens the Image Properties Window when the insert Image button is clicked or an Image is Double Clicked.
        */
        _handleInsertImageClick: function() {
            if (this.get('limitCommands')) {
                if (!this.toolbar.getButtonByValue('insertimage')) {
                    YAHOO.log('Toolbar Button for (insertimage) was not found, skipping exec.', 'info', 'SimpleEditor');
                    return false;
                }
            }
        
            this.toolbar.set('disabled', true); //Disable the toolbar when the prompt is showing
            var _handleAEC = function() {
                var el = this.currentElement[0],
                    src = 'http://';
                if (!el) {
                    el = this._getSelectedElement();
                }
                if (el) {
                    if (el.getAttribute('src')) {
                        src = el.getAttribute('src', 2);
                        if (src.indexOf(this.get('blankimage')) != -1) {
                            src = this.STR_IMAGE_HERE;
                        }
                    }
                }
                var str = prompt(this.STR_IMAGE_URL + ': ', src);
                if ((str !== '') && (str !== null)) {
                    el.setAttribute('src', str);
                } else if (str === '') {
                    el.parentNode.removeChild(el);
                    this.currentElement = [];
                    this.nodeChange();
                } else if ((str === null)) {
                    src = el.getAttribute('src', 2);
                    if (src.indexOf(this.get('blankimage')) != -1) {
                        el.parentNode.removeChild(el);
                        this.currentElement = [];
                        this.nodeChange();
                    }
                }
                this.closeWindow();
                this.toolbar.set('disabled', false);
                this.unsubscribe('afterExecCommand', _handleAEC, this, true);
            };
            this.on('afterExecCommand', _handleAEC, this, true);
        },
        /**
        * @private
        * @method _handleInsertImageWindowClose
        * @description Handles the closing of the Image Properties Window.
        */
        _handleInsertImageWindowClose: function() {
            this.nodeChange();
        },
        /**
        * @private
        * @method _isLocalFile
        * @param {String} url THe url/string to check
        * @description Checks to see if a string (href or img src) is possibly a local file reference..
        */
        _isLocalFile: function(url) {
            if ((url) && (url !== '') && ((url.indexOf('file:/') != -1) || (url.indexOf(':\\') != -1))) {
                return true;
            }
            return false;
        },
        /**
        * @private
        * @method _handleCreateLinkClick
        * @description Handles the opening of the Link Properties Window when the Create Link button is clicked or an href is doubleclicked.
        */
        _handleCreateLinkClick: function() {
            if (this.get('limitCommands')) {
                if (!this.toolbar.getButtonByValue('createlink')) {
                    YAHOO.log('Toolbar Button for (createlink) was not found, skipping exec.', 'info', 'SimpleEditor');
                    return false;
                }
            }
        
            this.toolbar.set('disabled', true); //Disable the toolbar when the prompt is showing

            var _handleAEC = function() {
                var el = this.currentElement[0],
                    url = '';

                if (el) {
                    if (el.getAttribute('href', 2) !== null) {
                        url = el.getAttribute('href', 2);
                    }
                }
                var str = prompt(this.STR_LINK_URL + ': ', url);
                if ((str !== '') && (str !== null)) {
                    var urlValue = str;
                    if ((urlValue.indexOf(':/'+'/') == -1) && (urlValue.substring(0,1) != '/') && (urlValue.substring(0, 6).toLowerCase() != 'mailto')) {
                        if ((urlValue.indexOf('@') != -1) && (urlValue.substring(0, 6).toLowerCase() != 'mailto')) {
                            //Found an @ sign, prefix with mailto:
                            urlValue = 'mailto:' + urlValue;
                        } else {
                            /* :// not found adding */
                            if (urlValue.substring(0, 1) != '#') {
                                //urlValue = 'http:/'+'/' + urlValue;
                            }
                        }
                    }
                    el.setAttribute('href', urlValue);
                } else if (str !== null) {
                    var _span = this._getDoc().createElement('span');
                    _span.innerHTML = el.innerHTML;
                    Dom.addClass(_span, 'yui-non');
                    el.parentNode.replaceChild(_span, el);
                }
                this.closeWindow();
                this.toolbar.set('disabled', false);
                this.unsubscribe('afterExecCommand', _handleAEC, this, true);
            };
            this.on('afterExecCommand', _handleAEC, this);

        },
        /**
        * @private
        * @method _handleCreateLinkWindowClose
        * @description Handles the closing of the Link Properties Window.
        */
        _handleCreateLinkWindowClose: function() {
            this.nodeChange();
            this.currentElement = [];
        },
        /**
        * @method render
        * @description Calls the private method _render in a setTimeout to allow for other things on the page to continue to load.
        */
        render: function() {
            if (this._rendered) {
                return false;
            }
            YAHOO.log('Render', 'info', 'SimpleEditor');
            if (!this.DOMReady) {
                YAHOO.log('!DOMReady', 'info', 'SimpleEditor');
                this._queue[this._queue.length] = ['render', arguments];
                return false;
            }
            if (this.get('element')) {
                if (this.get('element').tagName) {
                    this._textarea = true;
                    if (this.get('element').tagName.toLowerCase() !== 'textarea') {
                        this._textarea = false;
                    }
                } else {
                    YAHOO.log('No Valid Element', 'error', 'SimpleEditor');
                    return false;
                }
            } else {
                YAHOO.log('No Element', 'error', 'SimpleEditor');
                return false;
            }
            this._rendered = true;
            var self = this;
            window.setTimeout(function() {
                self._render.call(self);
            }, 4);
        },
        /**
        * @private
        * @method _render
        * @description Causes the toolbar and the editor to render and replace the textarea.
        */
        _render: function() {
            var self = this;
            this.set('textarea', this.get('element'));

            this.get('element_cont').setStyle('display', 'none');
            this.get('element_cont').addClass(this.CLASS_CONTAINER);
            
            this.set('iframe', this._createIframe());

            window.setTimeout(function() {
                self._setInitialContent.call(self);
            }, 10);

            this.get('editor_wrapper').appendChild(this.get('iframe').get('element'));

            if (this.get('disabled')) {
                this._disableEditor(true);
            }

            var tbarConf = this.get('toolbar');
            //Create Toolbar instance
            if (tbarConf instanceof Toolbar) {
                this.toolbar = tbarConf;
                //Set the toolbar to disabled until content is loaded
                this.toolbar.set('disabled', true);
            } else {
                //Set the toolbar to disabled until content is loaded
                tbarConf.disabled = true;
                this.toolbar = new Toolbar(this.get('toolbar_cont'), tbarConf);
            }

            YAHOO.log('fireEvent::toolbarLoaded', 'info', 'SimpleEditor');
            this.fireEvent('toolbarLoaded', { type: 'toolbarLoaded', target: this.toolbar });

            
            this.toolbar.on('toolbarCollapsed', function() {
                if (this.currentWindow) {
                    this.moveWindow();
                }
            }, this, true);
            this.toolbar.on('toolbarExpanded', function() {
                if (this.currentWindow) {
                    this.moveWindow();
                }
            }, this, true);
            this.toolbar.on('fontsizeClick', this._handleFontSize, this, true);
            
            this.toolbar.on('colorPickerClicked', function(o) {
                this._handleColorPicker(o);
                return false; //Stop the buttonClick event
            }, this, true);

            this.toolbar.on('alignClick', this._handleAlign, this, true);
            this.on('afterNodeChange', this._handleAfterNodeChange, this, true);
            this.toolbar.on('insertimageClick', this._handleInsertImageClick, this, true);
            this.on('windowinsertimageClose', this._handleInsertImageWindowClose, this, true);
            this.toolbar.on('createlinkClick', this._handleCreateLinkClick, this, true);
            this.on('windowcreatelinkClose', this._handleCreateLinkWindowClose, this, true);
            

            //Replace Textarea with editable area
            this.get('parentNode').replaceChild(this.get('element_cont').get('element'), this.get('element'));

            
            this.setStyle('visibility', 'hidden');
            this.setStyle('position', 'absolute');
            this.setStyle('top', '-9999px');
            this.setStyle('left', '-9999px');
            this.get('element_cont').appendChild(this.get('element'));
            this.get('element_cont').setStyle('display', 'block');


            Dom.addClass(this.get('iframe').get('parentNode'), this.CLASS_EDITABLE_CONT);
            this.get('iframe').addClass(this.CLASS_EDITABLE);

            //Set height and width of editor container
            this.get('element_cont').setStyle('width', this.get('width'));
            Dom.setStyle(this.get('iframe').get('parentNode'), 'height', this.get('height'));

            this.get('iframe').setStyle('width', '100%'); //WIDTH
            this.get('iframe').setStyle('height', '100%');

            this._setupDD();

            window.setTimeout(function() {
                self._setupAfterElement.call(self);
            }, 0);
            this.fireEvent('afterRender', { type: 'afterRender', target: this });
        },
        /**
        * @method execCommand
        * @param {String} action The "execCommand" action to try to execute (Example: bold, insertimage, inserthtml)
        * @param {String} value (optional) The value for a given action such as action: fontname value: 'Verdana'
        * @description This method attempts to try and level the differences in the various browsers and their support for execCommand actions
        */
        execCommand: function(action, value) {
            var beforeExec = this.fireEvent('beforeExecCommand', { type: 'beforeExecCommand', target: this, args: arguments });
            if ((beforeExec === false) || (this.STOP_EXEC_COMMAND)) {
                this.STOP_EXEC_COMMAND = false;
                return false;
            }
            this._lastCommand = action;
            this._setMarkupType(action);
            if (this.browser.ie) {
                this._getWindow().focus();
            }
            var exec = true;
            
            if (this.get('limitCommands')) {
                if (!this.toolbar.getButtonByValue(action)) {
                    YAHOO.log('Toolbar Button for (' + action + ') was not found, skipping exec.', 'info', 'SimpleEditor');
                    exec = false;
                }
            }

            this.editorDirty = true;
            
            if ((typeof this['cmd_' + action.toLowerCase()] == 'function') && exec) {
                YAHOO.log('Found execCommand override method: (cmd_' + action.toLowerCase() + ')', 'info', 'SimpleEditor');
                var retValue = this['cmd_' + action.toLowerCase()](value);
                exec = retValue[0];
                if (retValue[1]) {
                    action = retValue[1];
                }
                if (retValue[2]) {
                    value = retValue[2];
                }
            }
            if (exec) {
                YAHOO.log('execCommand::(' + action + '), (' + value + ')', 'info', 'SimpleEditor');
                try {
                    this._getDoc().execCommand(action, false, value);
                } catch(e) {
                    YAHOO.log('execCommand Failed', 'error', 'SimpleEditor');
                }
            } else {
                YAHOO.log('OVERRIDE::execCommand::(' + action + '),(' + value + ') skipped', 'warn', 'SimpleEditor');
            }
            this.on('afterExecCommand', function() {
                this.unsubscribeAll('afterExecCommand');
                this.nodeChange();
            }, this, true);
            this.fireEvent('afterExecCommand', { type: 'afterExecCommand', target: this });
            
        },
    /* {{{  Command Overrides */

        /**
        * @method cmd_bold
        * @param value Value passed from the execCommand method
        * @description This is an execCommand override method. It is called from execCommand when the execCommand('bold') is used.
        */
        cmd_bold: function(value) {
            if (!this.browser.webkit) {
                var el = this._getSelectedElement();
                if (el && this._isElement(el, 'span') && this._hasSelection()) {
                    if (el.style.fontWeight == 'bold') {
                        el.style.fontWeight = '';
                        var b = this._getDoc().createElement('b'),
                        par = el.parentNode;
                        par.replaceChild(b, el);
                        b.appendChild(el);
                    }
                }
            }
            return [true];
        },
        /**
        * @method cmd_italic
        * @param value Value passed from the execCommand method
        * @description This is an execCommand override method. It is called from execCommand when the execCommand('italic') is used.
        */

        cmd_italic: function(value) {
            if (!this.browser.webkit) {
                var el = this._getSelectedElement();
                if (el && this._isElement(el, 'span') && this._hasSelection()) {
                    if (el.style.fontStyle == 'italic') {
                        el.style.fontStyle = '';
                        var i = this._getDoc().createElement('i'),
                        par = el.parentNode;
                        par.replaceChild(i, el);
                        i.appendChild(el);
                    }
                }
            }
            return [true];
        },


        /**
        * @method cmd_underline
        * @param value Value passed from the execCommand method
        * @description This is an execCommand override method. It is called from execCommand when the execCommand('underline') is used.
        */
        cmd_underline: function(value) {
            if (!this.browser.webkit) {
                var el = this._getSelectedElement();
                if (el && this._isElement(el, 'span')) {
                    if (el.style.textDecoration == 'underline') {
                        el.style.textDecoration = 'none';
                    } else {
                        el.style.textDecoration = 'underline';
                    }
                    return [false];
                }
            }
            return [true];
        },
        /**
        * @method cmd_backcolor
        * @param value Value passed from the execCommand method
        * @description This is an execCommand override method. It is called from execCommand when the execCommand('backcolor') is used.
        */
        cmd_backcolor: function(value) {
            var exec = true,
                el = this._getSelectedElement(),
                action = 'backcolor';

            if (this.browser.gecko || this.browser.opera) {
                this._setEditorStyle(true);
                action = 'hilitecolor';
            }

            if (!this._isElement(el, 'body') && !this._hasSelection()) {
                el.style.backgroundColor = value;
                this._selectNode(el);
                exec = false;
            } else {
                if (this.get('insert')) {
                    el = this._createInsertElement({ backgroundColor: value });
                } else {
                    this._createCurrentElement('span', { backgroundColor: value, color: el.style.color, fontSize: el.style.fontSize, fontFamily: el.style.fontFamily });
                    this._selectNode(this.currentElement[0]);
                }
                exec = false;
            }

            return [exec, action];
        },
        /**
        * @method cmd_forecolor
        * @param value Value passed from the execCommand method
        * @description This is an execCommand override method. It is called from execCommand when the execCommand('forecolor') is used.
        */
        cmd_forecolor: function(value) {
            var exec = true,
                el = this._getSelectedElement();
                
                if (!this._isElement(el, 'body') && !this._hasSelection()) {
                    Dom.setStyle(el, 'color', value);
                    this._selectNode(el);
                    exec = false;
                } else {
                    if (this.get('insert')) {
                        el = this._createInsertElement({ color: value });
                    } else {
                        this._createCurrentElement('span', { color: value, fontSize: el.style.fontSize, fontFamily: el.style.fontFamily, backgroundColor: el.style.backgroundColor });
                        this._selectNode(this.currentElement[0]);
                    }
                    exec = false;
                }
                return [exec];
        },
        /**
        * @method cmd_unlink
        * @param value Value passed from the execCommand method
        * @description This is an execCommand override method. It is called from execCommand when the execCommand('unlink') is used.
        */
        cmd_unlink: function(value) {
            this._swapEl(this.currentElement[0], 'span', function(el) {
                el.className = 'yui-non';
            });
            return [false];
        },
        /**
        * @method cmd_createlink
        * @param value Value passed from the execCommand method
        * @description This is an execCommand override method. It is called from execCommand when the execCommand('createlink') is used.
        */
        cmd_createlink: function(value) {
            var el = this._getSelectedElement(), _a = null;
            if (this._hasParent(el, 'a')) {
                this.currentElement[0] = this._hasParent(el, 'a');
            } else if (this._isElement(el, 'li')) {
                _a = this._getDoc().createElement('a');
                _a.innerHTML = el.innerHTML;
                el.innerHTML = '';
                el.appendChild(_a);
                this.currentElement[0] = _a;
            } else if (!this._isElement(el, 'a')) {
                this._createCurrentElement('a');
                _a = this._swapEl(this.currentElement[0], 'a');
                this.currentElement[0] = _a;
            } else {
                this.currentElement[0] = el;
            }
            return [false];
        },
        /**
        * @method cmd_insertimage
        * @param value Value passed from the execCommand method
        * @description This is an execCommand override method. It is called from execCommand when the execCommand('insertimage') is used.
        */
        cmd_insertimage: function(value) {
            var exec = true, _img = null, action = 'insertimage',
                el = this._getSelectedElement();

            if (value === '') {
                value = this.get('blankimage');
            }

            /*
            * @knownissue Safari Cursor Position
            * @browser Safari 2.x
            * @description The issue here is that we have no way of knowing where the cursor position is
            * inside of the iframe, so we have to place the newly inserted data in the best place that we can.
            */
            
            YAHOO.log('InsertImage: ' + el.tagName, 'info', 'SimpleEditor');
            if (this._isElement(el, 'img')) {
                this.currentElement[0] = el;
                exec = false;
            } else {
                if (this._getDoc().queryCommandEnabled(action)) {
                    this._getDoc().execCommand(action, false, value);
                    var imgs = this._getDoc().getElementsByTagName('img');
                    for (var i = 0; i < imgs.length; i++) {
                        if (!YAHOO.util.Dom.hasClass(imgs[i], 'yui-img')) {
                            YAHOO.util.Dom.addClass(imgs[i], 'yui-img');
                            this.currentElement[0] = imgs[i];
                        }
                    }
                    exec = false;
                } else {
                    if (el == this._getDoc().body) {
                        _img = this._getDoc().createElement('img');
                        _img.setAttribute('src', value);
                        YAHOO.util.Dom.addClass(_img, 'yui-img');
                        this._getDoc().body.appendChild(_img);
                    } else {
                        this._createCurrentElement('img');
                        _img = this._getDoc().createElement('img');
                        _img.setAttribute('src', value);
                        YAHOO.util.Dom.addClass(_img, 'yui-img');
                        this.currentElement[0].parentNode.replaceChild(_img, this.currentElement[0]);
                    }
                    this.currentElement[0] = _img;
                    exec = false;
                }
            }
            return [exec];
        },
        /**
        * @method cmd_inserthtml
        * @param value Value passed from the execCommand method
        * @description This is an execCommand override method. It is called from execCommand when the execCommand('inserthtml') is used.
        */
        cmd_inserthtml: function(value) {
            var exec = true, action = 'inserthtml', _span = null, _range = null;
            /*
            * @knownissue Safari cursor position
            * @browser Safari 2.x
            * @description The issue here is that we have no way of knowing where the cursor position is
            * inside of the iframe, so we have to place the newly inserted data in the best place that we can.
            */
            if (this.browser.webkit && !this._getDoc().queryCommandEnabled(action)) {
                YAHOO.log('More Safari DOM tricks (inserthtml)', 'info', 'EditorSafari');
                this._createCurrentElement('img');
                _span = this._getDoc().createElement('span');
                _span.innerHTML = value;
                this.currentElement[0].parentNode.replaceChild(_span, this.currentElement[0]);
                exec = false;
            } else if (this.browser.ie) {
                _range = this._getRange();
                if (_range.item) {
                    _range.item(0).outerHTML = value;
                } else {
                    _range.pasteHTML(value);
                }
                exec = false;                    
            }
            return [exec];
        },
        /**
        * @method cmd_list
        * @param tag The tag of the list you want to create (eg, ul or ol)
        * @description This is a combined execCommand override method. It is called from the cmd_insertorderedlist and cmd_insertunorderedlist methods.
        */
        cmd_list: function(tag) {
            var exec = true, list = null, li = 0, el = null, str = '',
                selEl = this._getSelectedElement(), action = 'insertorderedlist';
                if (tag == 'ul') {
                    action = 'insertunorderedlist';
                }
            /*
            * @knownissue Safari 2.+ doesn't support ordered and unordered lists
            * @browser Safari 2.x
            * The issue with this workaround is that when applied to a set of text
            * that has BR's in it, Safari may or may not pick up the individual items as
            * list items. This is fixed in WebKit (Safari 3)
            * 2.6.0: Seems there are still some issues with List Creation and Safari 3, reverting to previously working Safari 2.x code
            */
            //if ((this.browser.webkit && !this._getDoc().queryCommandEnabled(action))) {
            if ((this.browser.webkit && !this.browser.webkit4) || (this.browser.opera)) {
                if (this._isElement(selEl, 'li') && this._isElement(selEl.parentNode, tag)) {
                    YAHOO.log('We already have a list, undo it', 'info', 'SimpleEditor');
                    el = selEl.parentNode;
                    list = this._getDoc().createElement('span');
                    YAHOO.util.Dom.addClass(list, 'yui-non');
                    str = '';
                    var lis = el.getElementsByTagName('li'), p_tag = ((this.browser.opera && this.get('ptags')) ? 'p' : 'div');
                    for (li = 0; li < lis.length; li++) {
                        str += '<' + p_tag + '>' + lis[li].innerHTML + '</' + p_tag + '>';
                    }
                    list.innerHTML = str;
                    this.currentElement[0] = el;
                    this.currentElement[0].parentNode.replaceChild(list, this.currentElement[0]);
                } else {
                    YAHOO.log('Create list item', 'info', 'SimpleEditor');
                    this._createCurrentElement(tag.toLowerCase());
                    list = this._getDoc().createElement(tag);
                    for (li = 0; li < this.currentElement.length; li++) {
                        var newli = this._getDoc().createElement('li');
                        newli.innerHTML = this.currentElement[li].innerHTML + '<span class="yui-non">&nbsp;</span>&nbsp;';
                        list.appendChild(newli);
                        if (li > 0) {
                            this.currentElement[li].parentNode.removeChild(this.currentElement[li]);
                        }
                    }
                    var b_tag = ((this.browser.opera) ? '<BR>' : '<br>'),
                    items = list.firstChild.innerHTML.split(b_tag), i, item;
                    if (items.length > 0) {
                        list.innerHTML = '';
                        for (i = 0; i < items.length; i++) {
                            item = this._getDoc().createElement('li');
                            item.innerHTML = items[i];
                            list.appendChild(item);
                        }
                    }

                    this.currentElement[0].parentNode.replaceChild(list, this.currentElement[0]);
                    this.currentElement[0] = list;
                    var _h = this.currentElement[0].firstChild;
                    _h = Dom.getElementsByClassName('yui-non', 'span', _h)[0];
                    if (this.browser.webkit) {
                        this._getSelection().setBaseAndExtent(_h, 1, _h, _h.innerText.length);
                    }
                }
                exec = false;
            } else {
                el = this._getSelectedElement();
                YAHOO.log(el.tagName);
                if (this._isElement(el, 'li') && this._isElement(el.parentNode, tag) || (this.browser.ie && this._isElement(this._getRange().parentElement, 'li')) || (this.browser.ie && this._isElement(el, 'ul')) || (this.browser.ie && this._isElement(el, 'ol'))) { //we are in a list..
                    YAHOO.log('We already have a list, undo it', 'info', 'SimpleEditor');
                    if (this.browser.ie) {
                        if ((this.browser.ie && this._isElement(el, 'ul')) || (this.browser.ie && this._isElement(el, 'ol'))) {
                            el = el.getElementsByTagName('li')[0];
                        }
                        YAHOO.log('Undo IE', 'info', 'SimpleEditor');
                        str = '';
                        var lis2 = el.parentNode.getElementsByTagName('li');
                        for (var j = 0; j < lis2.length; j++) {
                            str += lis2[j].innerHTML + '<br>';
                        }
                        var newEl = this._getDoc().createElement('span');
                        newEl.innerHTML = str;
                        el.parentNode.parentNode.replaceChild(newEl, el.parentNode);
                    } else {
                        this.nodeChange();
                        this._getDoc().execCommand(action, '', el.parentNode);
                        this.nodeChange();
                    }
                    exec = false;
                }
                if (this.browser.opera) {
                    var self = this;
                    window.setTimeout(function() {
                        var liso = self._getDoc().getElementsByTagName('li');
                        for (var i = 0; i < liso.length; i++) {
                            if (liso[i].innerHTML.toLowerCase() == '<br>') {
                                liso[i].parentNode.parentNode.removeChild(liso[i].parentNode);
                            }
                        }
                    },30);
                }
                if (this.browser.ie && exec) {
                    var html = '';
                    if (this._getRange().html) {
                        html = '<li>' + this._getRange().html+ '</li>';
                    } else {
                        var t = this._getRange().text.split('\n');
                        if (t.length > 1) {
                            html = '';
                            for (var ie = 0; ie < t.length; ie++) {
                                html += '<li>' + t[ie] + '</li>';
                            }
                        } else {
                            var txt = this._getRange().text;
                            if (txt === '') {
                                html = '<li id="new_list_item">' + txt + '</li>';
                            } else {
                                html = '<li>' + txt + '</li>';
                            }
                        }
                    }
                    this._getRange().pasteHTML('<' + tag + '>' + html + '</' + tag + '>');
                    var new_item = this._getDoc().getElementById('new_list_item');
                    if (new_item) {
                        var range = this._getDoc().body.createTextRange();
                        range.moveToElementText(new_item);
                        range.collapse(false);
                        range.select();                       
                        new_item.id = '';
                    }
                    exec = false;
                }
            }
            return exec;
        },
        /**
        * @method cmd_insertorderedlist
        * @param value Value passed from the execCommand method
        * @description This is an execCommand override method. It is called from execCommand when the execCommand('insertorderedlist ') is used.
        */
        cmd_insertorderedlist: function(value) {
            return [this.cmd_list('ol')];
        },
        /**
        * @method cmd_insertunorderedlist 
        * @param value Value passed from the execCommand method
        * @description This is an execCommand override method. It is called from execCommand when the execCommand('insertunorderedlist') is used.
        */
        cmd_insertunorderedlist: function(value) {
            return [this.cmd_list('ul')];
        },
        /**
        * @method cmd_fontname
        * @param value Value passed from the execCommand method
        * @description This is an execCommand override method. It is called from execCommand when the execCommand('fontname') is used.
        */
        cmd_fontname: function(value) {
            var exec = true,
                selEl = this._getSelectedElement();

            this.currentFont = value;
            if (selEl && selEl.tagName && !this._hasSelection() && !this._isElement(selEl, 'body') && !this.get('insert')) {
                YAHOO.util.Dom.setStyle(selEl, 'font-family', value);
                exec = false;
            } else if (this.get('insert') && !this._hasSelection()) {
                YAHOO.log('No selection and no selected element and we are in insert mode', 'info', 'SimpleEditor');
                var el = this._createInsertElement({ fontFamily: value });
                exec = false;
            }
            return [exec];
        },
        /**
        * @method cmd_fontsize
        * @param value Value passed from the execCommand method
        * @description This is an execCommand override method. It is called from execCommand when the execCommand('fontsize') is used.
        */
        cmd_fontsize: function(value) {
            var el = null, go = true;
            el = this._getSelectedElement();
            if (this.browser.webkit) {
                if (this.currentElement[0]) {
                    if (el == this.currentElement[0]) {
                        go = false;
                        YAHOO.util.Dom.setStyle(el, 'fontSize', value);
                        this._selectNode(el);
                        this.currentElement[0] = el;
                    }
                }
            }
            if (go) {
                if (!this._isElement(this._getSelectedElement(), 'body') && (!this._hasSelection())) {
                    el = this._getSelectedElement();
                    YAHOO.util.Dom.setStyle(el, 'fontSize', value);
                    if (this.get('insert') && this.browser.ie) {
                        var r = this._getRange();
                        r.collapse(false);
                        r.select();
                    } else {
                        this._selectNode(el);
                    }
                } else if (this.currentElement && (this.currentElement.length > 0) && (!this._hasSelection()) && (!this.get('insert'))) {
                    YAHOO.util.Dom.setStyle(this.currentElement, 'fontSize', value);
                } else {
                    if (this.get('insert') && !this._hasSelection()) {
                        el = this._createInsertElement({ fontSize: value });
                        this.currentElement[0] = el;
                        this._selectNode(this.currentElement[0]);
                    } else {
                        this._createCurrentElement('span', {'fontSize': value, fontFamily: el.style.fontFamily, color: el.style.color, backgroundColor: el.style.backgroundColor });
                        this._selectNode(this.currentElement[0]);
                    }
                }
            }
            return [false];
        },
    /* }}} */
        /**
        * @private
        * @method _swapEl
        * @param {HTMLElement} el The element to swap with
        * @param {String} tagName The tagname of the element that you wish to create
        * @param {Function} callback (optional) A function to run on the element after it is created, but before it is replaced. An element reference is passed to this function.
        * @description This function will create a new element in the DOM and populate it with the contents of another element. Then it will assume it's place.
        */
        _swapEl: function(el, tagName, callback) {
            var _el = this._getDoc().createElement(tagName);
            if (el) {
                _el.innerHTML = el.innerHTML;
            }
            if (typeof callback == 'function') {
                callback.call(this, _el);
            }
            if (el) {
                el.parentNode.replaceChild(_el, el);
            }
            return _el;
        },
        /**
        * @private
        * @method _createInsertElement
        * @description Creates a new "currentElement" then adds some text (and other things) to make it selectable and stylable. Then the user can continue typing.
        * @param {Object} css (optional) Object literal containing styles to apply to the new element.
        * @return {HTMLElement}
        */
        _createInsertElement: function(css) {
            this._createCurrentElement('span', css);
            var el = this.currentElement[0];
            if (this.browser.webkit) {
                //Little Safari Hackery here..
                el.innerHTML = '<span class="yui-non">&nbsp;</span>';
                el = el.firstChild;
                this._getSelection().setBaseAndExtent(el, 1, el, el.innerText.length);                    
            } else if (this.browser.ie || this.browser.opera) {
                el.innerHTML = '&nbsp;';
            }
            this.focus();
            this._selectNode(el, true);
            return el;
        },
        /**
        * @private
        * @method _createCurrentElement
        * @param {String} tagName (optional defaults to a) The tagname of the element that you wish to create
        * @param {Object} tagStyle (optional) Object literal containing styles to apply to the new element.
        * @description This is a work around for the various browser issues with execCommand. This method will run <code>execCommand('fontname', false, 'yui-tmp')</code> on the given selection.
        * It will then search the document for an element with the font-family set to <strong>yui-tmp</strong> and replace that with another span that has other information in it, then assign the new span to the 
        * <code>this.currentElement</code> array, so we now have element references to the elements that were just modified. At this point we can use standard DOM manipulation to change them as we see fit.
        */
        _createCurrentElement: function(tagName, tagStyle) {
            tagName = ((tagName) ? tagName : 'a');
            var tar = null,
                el = [],
                _doc = this._getDoc();
            
            if (this.currentFont) {
                if (!tagStyle) {
                    tagStyle = {};
                }
                tagStyle.fontFamily = this.currentFont;
                this.currentFont = null;
            }
            this.currentElement = [];

            var _elCreate = function(tagName, tagStyle) {
                var el = null;
                tagName = ((tagName) ? tagName : 'span');
                tagName = tagName.toLowerCase();
                switch (tagName) {
                    case 'h1':
                    case 'h2':
                    case 'h3':
                    case 'h4':
                    case 'h5':
                    case 'h6':
                        el = _doc.createElement(tagName);
                        break;
                    default:
                        el = _doc.createElement(tagName);
                        if (tagName === 'span') {
                            YAHOO.util.Dom.addClass(el, 'yui-tag-' + tagName);
                            YAHOO.util.Dom.addClass(el, 'yui-tag');
                            el.setAttribute('tag', tagName);
                        }

                        for (var k in tagStyle) {
                            if (YAHOO.lang.hasOwnProperty(tagStyle, k)) {
                                el.style[k] = tagStyle[k];
                            }
                        }
                        break;
                }
                return el;
            };

            if (!this._hasSelection()) {
                if (this._getDoc().queryCommandEnabled('insertimage')) {
                    this._getDoc().execCommand('insertimage', false, 'yui-tmp-img');
                    var imgs = this._getDoc().getElementsByTagName('img');
                    for (var j = 0; j < imgs.length; j++) {
                        if (imgs[j].getAttribute('src', 2) == 'yui-tmp-img') {
                            el = _elCreate(tagName, tagStyle);
                            imgs[j].parentNode.replaceChild(el, imgs[j]);
                            this.currentElement[this.currentElement.length] = el;
                        }
                    }
                } else {
                    if (this.currentEvent) {
                        tar = YAHOO.util.Event.getTarget(this.currentEvent);
                    } else {
                        //For Safari..
                        tar = this._getDoc().body;                        
                    }
                }
                if (tar) {
                    /*
                    * @knownissue Safari Cursor Position
                    * @browser Safari 2.x
                    * @description The issue here is that we have no way of knowing where the cursor position is
                    * inside of the iframe, so we have to place the newly inserted data in the best place that we can.
                    */
                    el = _elCreate(tagName, tagStyle);
                    if (this._isElement(tar, 'body') || this._isElement(tar, 'html')) {
                        if (this._isElement(tar, 'html')) {
                            tar = this._getDoc().body;
                        }
                        tar.appendChild(el);
                    } else if (tar.nextSibling) {
                        tar.parentNode.insertBefore(el, tar.nextSibling);
                    } else {
                        tar.parentNode.appendChild(el);
                    }
                    //this.currentElement = el;
                    this.currentElement[this.currentElement.length] = el;
                    this.currentEvent = null;
                    if (this.browser.webkit) {
                        //Force Safari to focus the new element
                        this._getSelection().setBaseAndExtent(el, 0, el, 0);
                        if (this.browser.webkit3) {
                            this._getSelection().collapseToStart();
                        } else {
                            this._getSelection().collapse(true);
                        }
                    }
                }
            } else {
                //Force CSS Styling for this action...
                this._setEditorStyle(true);
                this._getDoc().execCommand('fontname', false, 'yui-tmp');
                var _tmp = [], __tmp, __els = ['font', 'span', 'i', 'b', 'u'];

                if (!this._isElement(this._getSelectedElement(), 'body')) {
                    __els[__els.length] = this._getDoc().getElementsByTagName(this._getSelectedElement().tagName);
                    __els[__els.length] = this._getDoc().getElementsByTagName(this._getSelectedElement().parentNode.tagName);
                }
                for (var _els = 0; _els < __els.length; _els++) {
                    var _tmp1 = this._getDoc().getElementsByTagName(__els[_els]);
                    for (var e = 0; e < _tmp1.length; e++) {
                        _tmp[_tmp.length] = _tmp1[e];
                    }
                }

                
                for (var i = 0; i < _tmp.length; i++) {
                    if ((YAHOO.util.Dom.getStyle(_tmp[i], 'font-family') == 'yui-tmp') || (_tmp[i].face && (_tmp[i].face == 'yui-tmp'))) {
                        if (tagName !== 'span') {
                            el = _elCreate(tagName, tagStyle);
                        } else {
                            el = _elCreate(_tmp[i].tagName, tagStyle);
                        }
                        el.innerHTML = _tmp[i].innerHTML;
                        if (this._isElement(_tmp[i], 'ol') || (this._isElement(_tmp[i], 'ul'))) {
                            var fc = _tmp[i].getElementsByTagName('li')[0];
                            _tmp[i].style.fontFamily = 'inherit';
                            fc.style.fontFamily = 'inherit';
                            el.innerHTML = fc.innerHTML;
                            fc.innerHTML = '';
                            fc.appendChild(el);
                            this.currentElement[this.currentElement.length] = el;
                        } else if (this._isElement(_tmp[i], 'li')) {
                            _tmp[i].innerHTML = '';
                            _tmp[i].appendChild(el);
                            _tmp[i].style.fontFamily = 'inherit';
                            this.currentElement[this.currentElement.length] = el;
                        } else {
                            if (_tmp[i].parentNode) {
                                _tmp[i].parentNode.replaceChild(el, _tmp[i]);
                                this.currentElement[this.currentElement.length] = el;
                                this.currentEvent = null;
                                if (this.browser.webkit) {
                                    //Force Safari to focus the new element
                                    this._getSelection().setBaseAndExtent(el, 0, el, 0);
                                    if (this.browser.webkit3) {
                                        this._getSelection().collapseToStart();
                                    } else {
                                        this._getSelection().collapse(true);
                                    }
                                }
                                if (this.browser.ie && tagStyle && tagStyle.fontSize) {
                                    this._getSelection().empty();
                                }
                                if (this.browser.gecko) {
                                    this._getSelection().collapseToStart();
                                }
                            }
                        }
                    }
                }
                var len = this.currentElement.length;
                for (var o = 0; o < len; o++) {
                    if ((o + 1) != len) { //Skip the last one in the list
                        if (this.currentElement[o] && this.currentElement[o].nextSibling) {
                            if (this._isElement(this.currentElement[o], 'br')) {
                                this.currentElement[this.currentElement.length] = this.currentElement[o].nextSibling;
                            }
                        }
                    }
                }
            }
        },
        /**
        * @method saveHTML
        * @description Cleans the HTML with the cleanHTML method then places that string back into the textarea.
        * @return String
        */
        saveHTML: function() {
            var html = this.cleanHTML();
            if (this._textarea) {
                this.get('element').value = html;
            } else {
                this.get('element').innerHTML = html;
            }
            if (this.get('saveEl') !== this.get('element')) {
                var out = this.get('saveEl');
                if (Lang.isString(out)) {
                    out = Dom.get(out);
                }
                if (out) {
                    if (out.tagName.toLowerCase() === 'textarea') {
                        out.value = html;
                    } else {
                        out.innerHTML = html;
                    }
                }
            }
            return html;
        },
        /**
        * @method setEditorHTML
        * @param {String} incomingHTML The html content to load into the editor
        * @description Loads HTML into the editors body
        */
        setEditorHTML: function(incomingHTML) {
            var html = this._cleanIncomingHTML(incomingHTML);
            html = html.replace(/RIGHT_BRACKET/gi, '{');
            html = html.replace(/LEFT_BRACKET/gi, '}');
            this._getDoc().body.innerHTML = html;
            this.nodeChange();
        },
        /**
        * @method getEditorHTML
        * @description Gets the unprocessed/unfiltered HTML from the editor
        */
        getEditorHTML: function() {
            try {
                var b = this._getDoc().body;
                if (b === null) {
                    YAHOO.log('Body is null, returning null.', 'error', 'SimpleEditor');
                    return null;
                }
                return this._getDoc().body.innerHTML;
            } catch (e) {
                return '';
            }
        },
        /**
        * @method show
        * @description This method needs to be called if the Editor was hidden (like in a TabView or Panel). It is used to reset the editor after being in a container that was set to display none.
        */
        show: function() {
            if (this.browser.gecko) {
                this._setDesignMode('on');
                this.focus();
            }
            if (this.browser.webkit) {
                var self = this;
                window.setTimeout(function() {
                    self._setInitialContent.call(self);
                }, 10);
            }
            //Adding this will close all other Editor window's when showing this one.
            if (this.currentWindow) {
                this.closeWindow();
            }
            //Put the iframe back in place
            this.get('iframe').setStyle('position', 'static');
            this.get('iframe').setStyle('left', '');
        },
        /**
        * @method hide
        * @description This method needs to be called if the Editor is to be hidden (like in a TabView or Panel). It should be called to clear timeouts and close open editor windows.
        */
        hide: function() {
            //Adding this will close all other Editor window's.
            if (this.currentWindow) {
                this.closeWindow();
            }
            if (this._fixNodesTimer) {
                clearTimeout(this._fixNodesTimer);
                this._fixNodesTimer = null;
            }
            if (this._nodeChangeTimer) {
                clearTimeout(this._nodeChangeTimer);
                this._nodeChangeTimer = null;
            }
            this._lastNodeChange = 0;
            //Move the iframe off of the screen, so that in containers with visiblity hidden, IE will not cover other elements.
            this.get('iframe').setStyle('position', 'absolute');
            this.get('iframe').setStyle('left', '-9999px');
        },
        /**
        * @method _cleanIncomingHTML
        * @param {String} html The unfiltered HTML
        * @description Process the HTML with a few regexes to clean it up and stabilize the input
        * @return {String} The filtered HTML
        */
        _cleanIncomingHTML: function(html) {
            html = html.replace(/{/gi, 'RIGHT_BRACKET');
            html = html.replace(/}/gi, 'LEFT_BRACKET');

            html = html.replace(/<strong([^>]*)>/gi, '<b$1>');
            html = html.replace(/<\/strong>/gi, '</b>');   

            //replace embed before em check
            html = html.replace(/<embed([^>]*)>/gi, '<YUI_EMBED$1>');
            html = html.replace(/<\/embed>/gi, '</YUI_EMBED>');

            html = html.replace(/<em([^>]*)>/gi, '<i$1>');
            html = html.replace(/<\/em>/gi, '</i>');
            html = html.replace(/_moz_dirty=""/gi, '');
            
            //Put embed tags back in..
            html = html.replace(/<YUI_EMBED([^>]*)>/gi, '<embed$1>');
            html = html.replace(/<\/YUI_EMBED>/gi, '</embed>');
            if (this.get('plainText')) {
                YAHOO.log('Filtering as plain text', 'info', 'SimpleEditor');
                html = html.replace(/\n/g, '<br>').replace(/\r/g, '<br>');
                html = html.replace(/  /gi, '&nbsp;&nbsp;'); //Replace all double spaces
                html = html.replace(/\t/gi, '&nbsp;&nbsp;&nbsp;&nbsp;'); //Replace all tabs
            }
            //Removing Script Tags from the Editor
            html = html.replace(/<script([^>]*)>/gi, '<bad>');
            html = html.replace(/<\/script([^>]*)>/gi, '</bad>');
            html = html.replace(/&lt;script([^>]*)&gt;/gi, '<bad>');
            html = html.replace(/&lt;\/script([^>]*)&gt;/gi, '</bad>');
            //Replace the line feeds
            html = html.replace(/\r\n/g, '<YUI_LF>').replace(/\n/g, '<YUI_LF>').replace(/\r/g, '<YUI_LF>');
            
            //Remove Bad HTML elements (used to be script nodes)
            html = html.replace(new RegExp('<bad([^>]*)>(.*?)<\/bad>', 'gi'), '');
            //Replace the lines feeds
            html = html.replace(/<YUI_LF>/g, '\n');
            return html;
        },
        /**
        * @method cleanHTML
        * @param {String} html The unfiltered HTML
        * @description Process the HTML with a few regexes to clean it up and stabilize the output
        * @return {String} The filtered HTML
        */
        cleanHTML: function(html) {
            //Start Filtering Output
            //Begin RegExs..
            if (!html) { 
                html = this.getEditorHTML();
            }
            var markup = this.get('markup');
            //Make some backups...
            html = this.pre_filter_linebreaks(html, markup);

            //Filter MS Word
            html = this.filter_msword(html);

		    html = html.replace(/<img([^>]*)\/>/gi, '<YUI_IMG$1>');
		    html = html.replace(/<img([^>]*)>/gi, '<YUI_IMG$1>');

		    html = html.replace(/<input([^>]*)\/>/gi, '<YUI_INPUT$1>');
		    html = html.replace(/<input([^>]*)>/gi, '<YUI_INPUT$1>');

		    html = html.replace(/<ul([^>]*)>/gi, '<YUI_UL$1>');
		    html = html.replace(/<\/ul>/gi, '<\/YUI_UL>');
		    html = html.replace(/<blockquote([^>]*)>/gi, '<YUI_BQ$1>');
		    html = html.replace(/<\/blockquote>/gi, '<\/YUI_BQ>');

		    html = html.replace(/<embed([^>]*)>/gi, '<YUI_EMBED$1>');
		    html = html.replace(/<\/embed>/gi, '<\/YUI_EMBED>');

            //Convert b and i tags to strong and em tags
            if ((markup == 'semantic') || (markup == 'xhtml')) {
                //html = html.replace(/<i(\s+[^>]*)?>/gi, "<em$1>");
                html = html.replace(/<i([^>]*)>/gi, "<em$1>");
                html = html.replace(/<\/i>/gi, '</em>');
                //html = html.replace(/<b(\s+[^>]*)?>/gi, "<strong$1>");
                html = html.replace(/<b([^>]*)>/gi, "<strong$1>");
                html = html.replace(/<\/b>/gi, '</strong>');
            }

            html = html.replace(/_moz_dirty=""/gi, '');

            //normalize strikethrough
            html = html.replace(/<strike/gi, '<span style="text-decoration: line-through;"');
            html = html.replace(/\/strike>/gi, '/span>');
            
            
            //Case Changing
            if (this.browser.ie) {
                html = html.replace(/text-decoration/gi, 'text-decoration');
                html = html.replace(/font-weight/gi, 'font-weight');
                html = html.replace(/_width="([^>]*)"/gi, '');
                html = html.replace(/_height="([^>]*)"/gi, '');
                //Cleanup Image URL's
                var url = this._baseHREF.replace(/\//gi, '\\/'),
                    re = new RegExp('src="' + url, 'gi');
                html = html.replace(re, 'src="');
            }
		    html = html.replace(/<font/gi, '<font');
		    html = html.replace(/<\/font>/gi, '</font>');
		    html = html.replace(/<span/gi, '<span');
		    html = html.replace(/<\/span>/gi, '</span>');
            if ((markup == 'semantic') || (markup == 'xhtml') || (markup == 'css')) {
                html = html.replace(new RegExp('<font([^>]*)face="([^>]*)">(.*?)<\/font>', 'gi'), '<span $1 style="font-family: $2;">$3</span>');
                html = html.replace(/<u/gi, '<span style="text-decoration: underline;"');
                if (this.browser.webkit) {
                    html = html.replace(new RegExp('<span class="Apple-style-span" style="font-weight: bold;">([^>]*)<\/span>', 'gi'), '<strong>$1</strong>');
                    html = html.replace(new RegExp('<span class="Apple-style-span" style="font-style: italic;">([^>]*)<\/span>', 'gi'), '<em>$1</em>');
                }
                html = html.replace(/\/u>/gi, '/span>');
                if (markup == 'css') {
                    html = html.replace(/<em([^>]*)>/gi, '<i$1>');
                    html = html.replace(/<\/em>/gi, '</i>');
                    html = html.replace(/<strong([^>]*)>/gi, '<b$1>');
                    html = html.replace(/<\/strong>/gi, '</b>');
                    html = html.replace(/<b/gi, '<span style="font-weight: bold;"');
                    html = html.replace(/\/b>/gi, '/span>');
                    html = html.replace(/<i/gi, '<span style="font-style: italic;"');
                    html = html.replace(/\/i>/gi, '/span>');
                }
                html = html.replace(/  /gi, ' '); //Replace all double spaces and replace with a single
            } else {
		        html = html.replace(/<u/gi, '<u');
		        html = html.replace(/\/u>/gi, '/u>');
            }
		    html = html.replace(/<ol([^>]*)>/gi, '<ol$1>');
		    html = html.replace(/\/ol>/gi, '/ol>');
		    html = html.replace(/<li/gi, '<li');
		    html = html.replace(/\/li>/gi, '/li>');
            html = this.filter_safari(html);

            html = this.filter_internals(html);

            html = this.filter_all_rgb(html);

            //Replace our backups with the real thing
            html = this.post_filter_linebreaks(html, markup);

            if (markup == 'xhtml') {
		        html = html.replace(/<YUI_IMG([^>]*)>/g, '<img $1 />');
		        html = html.replace(/<YUI_INPUT([^>]*)>/g, '<input $1 />');
            } else {
		        html = html.replace(/<YUI_IMG([^>]*)>/g, '<img $1>');
		        html = html.replace(/<YUI_INPUT([^>]*)>/g, '<input $1>');
            }
		    html = html.replace(/<YUI_UL([^>]*)>/g, '<ul$1>');
		    html = html.replace(/<\/YUI_UL>/g, '<\/ul>');

            html = this.filter_invalid_lists(html);

		    html = html.replace(/<YUI_BQ([^>]*)>/g, '<blockquote$1>');
		    html = html.replace(/<\/YUI_BQ>/g, '<\/blockquote>');

		    html = html.replace(/<YUI_EMBED([^>]*)>/g, '<embed$1>');
		    html = html.replace(/<\/YUI_EMBED>/g, '<\/embed>');
            
            //This should fix &amp;'s in URL's
            html = html.replace(/ &amp; /gi, ' YUI_AMP ');
            html = html.replace(/ &amp;/gi, ' YUI_AMP_F ');
            html = html.replace(/&amp; /gi, ' YUI_AMP_R ');
            html = html.replace(/&amp;/gi, '&');
            html = html.replace(/ YUI_AMP /gi, ' &amp; ');
            html = html.replace(/ YUI_AMP_F /gi, ' &amp;');
            html = html.replace(/ YUI_AMP_R /gi, '&amp; ');

            //Trim the output, removing whitespace from the beginning and end
            html = YAHOO.lang.trim(html);

            if (this.get('removeLineBreaks')) {
                html = html.replace(/\n/g, '').replace(/\r/g, '');
                html = html.replace(/  /gi, ' '); //Replace all double spaces and replace with a single
            }
            
            for (var v in this.invalidHTML) {
                if (YAHOO.lang.hasOwnProperty(this.invalidHTML, v)) {
                    if (Lang.isObject(v) && v.keepContents) {
                        html = html.replace(new RegExp('<' + v + '([^>]*)>(.*?)<\/' + v + '>', 'gi'), '$1');
                    } else {
                        html = html.replace(new RegExp('<' + v + '([^>]*)>(.*?)<\/' + v + '>', 'gi'), '');
                    }
                }
            }

            /* LATER -- Add DOM manipulation
            console.log(html);
            var frag = document.createDocumentFragment();
            frag.innerHTML = html;

            var ps = frag.getElementsByTagName('p'),
                len = ps.length;
            for (var i = 0; i < len; i++) {
                var ps2 = ps[i].getElementsByTagName('p');
                if (ps2.length) {
                    
                }
                
            }
            html = frag.innerHTML;
            console.log(html);
            */

            this.fireEvent('cleanHTML', { type: 'cleanHTML', target: this, html: html });

            return html;
        },
        /**
        * @method filter_msword
        * @param String html The HTML string to filter
        * @description Filters out msword html attributes and other junk. Activate with filterWord: true in config
        */
        filter_msword: function(html) {
            if (!this.get('filterWord')) {
                return html;
            }
            //Remove the ms o: tags
            html = html.replace(/<o:p>\s*<\/o:p>/g, '');
            html = html.replace(/<o:p>[\s\S]*?<\/o:p>/g, '&nbsp;');

            //Remove the ms w: tags
            html = html.replace( /<w:[^>]*>[\s\S]*?<\/w:[^>]*>/gi, '');

            //Remove mso-? styles.
            html = html.replace( /\s*mso-[^:]+:[^;"]+;?/gi, '');

            //Remove more bogus MS styles.
            html = html.replace( /\s*MARGIN: 0cm 0cm 0pt\s*;/gi, '');
            html = html.replace( /\s*MARGIN: 0cm 0cm 0pt\s*"/gi, "\"");
            html = html.replace( /\s*TEXT-INDENT: 0cm\s*;/gi, '');
            html = html.replace( /\s*TEXT-INDENT: 0cm\s*"/gi, "\"");
            html = html.replace( /\s*PAGE-BREAK-BEFORE: [^\s;]+;?"/gi, "\"");
            html = html.replace( /\s*FONT-VARIANT: [^\s;]+;?"/gi, "\"" );
            html = html.replace( /\s*tab-stops:[^;"]*;?/gi, '');
            html = html.replace( /\s*tab-stops:[^"]*/gi, '');

            //Remove XML declarations
            html = html.replace(/<\\?\?xml[^>]*>/gi, '');

            //Remove lang
            html = html.replace(/<(\w[^>]*) lang=([^ |>]*)([^>]*)/gi, "<$1$3");

            //Remove language tags
            html = html.replace( /<(\w[^>]*) language=([^ |>]*)([^>]*)/gi, "<$1$3");

            //Remove onmouseover and onmouseout events (from MS Word comments effect)
            html = html.replace( /<(\w[^>]*) onmouseover="([^\"]*)"([^>]*)/gi, "<$1$3");
            html = html.replace( /<(\w[^>]*) onmouseout="([^\"]*)"([^>]*)/gi, "<$1$3");
            
            return html;
        },
        /**
        * @method filter_invalid_lists
        * @param String html The HTML string to filter
        * @description Filters invalid ol and ul list markup, converts this: <li></li><ol>..</ol> to this: <li></li><li><ol>..</ol></li>
        */
        filter_invalid_lists: function(html) {
            html = html.replace(/<\/li>\n/gi, '</li>');

            html = html.replace(/<\/li><ol>/gi, '</li><li><ol>');
            html = html.replace(/<\/ol>/gi, '</ol></li>');
            html = html.replace(/<\/ol><\/li>\n/gi, "</ol>");

            html = html.replace(/<\/li><ul>/gi, '</li><li><ul>');
            html = html.replace(/<\/ul>/gi, '</ul></li>');
            html = html.replace(/<\/ul><\/li>\n?/gi, "</ul>");

            html = html.replace(/<\/li>/gi, "</li>");
            html = html.replace(/<\/ol>/gi, "</ol>");
            html = html.replace(/<ol>/gi, "<ol>");
            html = html.replace(/<ul>/gi, "<ul>");
            return html;
        },
        /**
        * @method filter_safari
        * @param String html The HTML string to filter
        * @description Filters strings specific to Safari
        * @return String
        */
        filter_safari: function(html) {
            if (this.browser.webkit) {
                //<span class="Apple-tab-span" style="white-space:pre">	</span>
                html = html.replace(/<span class="Apple-tab-span" style="white-space:pre">([^>])<\/span>/gi, '&nbsp;&nbsp;&nbsp;&nbsp;');
                html = html.replace(/Apple-style-span/gi, '');
                html = html.replace(/style="line-height: normal;"/gi, '');
                html = html.replace(/yui-wk-div/gi, '');
                html = html.replace(/yui-wk-p/gi, '');


                //Remove bogus LI's
                html = html.replace(/<li><\/li>/gi, '');
                html = html.replace(/<li> <\/li>/gi, '');
                html = html.replace(/<li>  <\/li>/gi, '');
                //Remove bogus DIV's - updated from just removing the div's to replacing /div with a break
                if (this.get('ptags')) {
		            html = html.replace(/<div([^>]*)>/g, '<p$1>');
				    html = html.replace(/<\/div>/gi, '</p>');
                } else {
                    //html = html.replace(/<div>/gi, '<br>');
                    html = html.replace(/<div([^>]*)>([ tnr]*)<\/div>/gi, '<br>');
				    html = html.replace(/<\/div>/gi, '');
                }
            }
            return html;
        },
        /**
        * @method filter_internals
        * @param String html The HTML string to filter
        * @description Filters internal RTE strings and bogus attrs we don't want
        * @return String
        */
        filter_internals: function(html) {
		    html = html.replace(/\r/g, '');
            //Fix stuff we don't want
	        html = html.replace(/<\/?(body|head|html)[^>]*>/gi, '');
            //Fix last BR in LI
		    html = html.replace(/<YUI_BR><\/li>/gi, '</li>');

		    html = html.replace(/yui-tag-span/gi, '');
		    html = html.replace(/yui-tag/gi, '');
		    html = html.replace(/yui-non/gi, '');
		    html = html.replace(/yui-img/gi, '');
		    html = html.replace(/ tag="span"/gi, '');
		    html = html.replace(/ class=""/gi, '');
		    html = html.replace(/ style=""/gi, '');
		    html = html.replace(/ class=" "/gi, '');
		    html = html.replace(/ class="  "/gi, '');
		    html = html.replace(/ target=""/gi, '');
		    html = html.replace(/ title=""/gi, '');

            if (this.browser.ie) {
		        html = html.replace(/ class= /gi, '');
		        html = html.replace(/ class= >/gi, '');
            }
            
            return html;
        },
        /**
        * @method filter_all_rgb
        * @param String str The HTML string to filter
        * @description Converts all RGB color strings found in passed string to a hex color, example: style="color: rgb(0, 255, 0)" converts to style="color: #00ff00"
        * @return String
        */
        filter_all_rgb: function(str) {
            var exp = new RegExp("rgb\\s*?\\(\\s*?([0-9]+).*?,\\s*?([0-9]+).*?,\\s*?([0-9]+).*?\\)", "gi");
            var arr = str.match(exp);
            if (Lang.isArray(arr)) {
                for (var i = 0; i < arr.length; i++) {
                    var color = this.filter_rgb(arr[i]);
                    str = str.replace(arr[i].toString(), color);
                }
            }
            
            return str;
        },
        /**
        * @method filter_rgb
        * @param String css The CSS string containing rgb(#,#,#);
        * @description Converts an RGB color string to a hex color, example: rgb(0, 255, 0) converts to #00ff00
        * @return String
        */
        filter_rgb: function(css) {
            if (css.toLowerCase().indexOf('rgb') != -1) {
                var exp = new RegExp("(.*?)rgb\\s*?\\(\\s*?([0-9]+).*?,\\s*?([0-9]+).*?,\\s*?([0-9]+).*?\\)(.*?)", "gi");
                var rgb = css.replace(exp, "$1,$2,$3,$4,$5").split(',');
            
                if (rgb.length == 5) {
                    var r = parseInt(rgb[1], 10).toString(16);
                    var g = parseInt(rgb[2], 10).toString(16);
                    var b = parseInt(rgb[3], 10).toString(16);

                    r = r.length == 1 ? '0' + r : r;
                    g = g.length == 1 ? '0' + g : g;
                    b = b.length == 1 ? '0' + b : b;

                    css = "#" + r + g + b;
                }
            }
            return css;
        },
        /**
        * @method pre_filter_linebreaks
        * @param String html The HTML to filter
        * @param String markup The markup type to filter to
        * @description HTML Pre Filter
        * @return String
        */
        pre_filter_linebreaks: function(html, markup) {
            if (this.browser.webkit) {
		        html = html.replace(/<br class="khtml-block-placeholder">/gi, '<YUI_BR>');
		        html = html.replace(/<br class="webkit-block-placeholder">/gi, '<YUI_BR>');
            }
		    html = html.replace(/<br>/gi, '<YUI_BR>');
		    html = html.replace(/<br (.*?)>/gi, '<YUI_BR>');
		    html = html.replace(/<br\/>/gi, '<YUI_BR>');
		    html = html.replace(/<br \/>/gi, '<YUI_BR>');
		    html = html.replace(/<div><YUI_BR><\/div>/gi, '<YUI_BR>');
		    html = html.replace(/<p>(&nbsp;|&#160;)<\/p>/g, '<YUI_BR>');            
		    html = html.replace(/<p><br>&nbsp;<\/p>/gi, '<YUI_BR>');
		    html = html.replace(/<p>&nbsp;<\/p>/gi, '<YUI_BR>');
            //Fix last BR
	        html = html.replace(/<YUI_BR>$/, '');
            //Fix last BR in P
	        html = html.replace(/<YUI_BR><\/p>/g, '</p>');
            if (this.browser.ie) {
	            html = html.replace(/&nbsp;&nbsp;&nbsp;&nbsp;/g, '\t');
            }
            return html;
        },
        /**
        * @method post_filter_linebreaks
        * @param String html The HTML to filter
        * @param String markup The markup type to filter to
        * @description HTML Pre Filter
        * @return String
        */
        post_filter_linebreaks: function(html, markup) {
            if (markup == 'xhtml') {
		        html = html.replace(/<YUI_BR>/g, '<br />');
            } else {
		        html = html.replace(/<YUI_BR>/g, '<br>');
            }
            return html;
        },
        /**
        * @method clearEditorDoc
        * @description Clear the doc of the Editor
        */
        clearEditorDoc: function() {
            this._getDoc().body.innerHTML = '&nbsp;';
        },
        /**
        * @method openWindow
        * @description Override Method for Advanced Editor
        */
        openWindow: function(win) {
        },
        /**
        * @method moveWindow
        * @description Override Method for Advanced Editor
        */
        moveWindow: function() {
        },
        /**
        * @private
        * @method _closeWindow
        * @description Override Method for Advanced Editor
        */
        _closeWindow: function() {
        },
        /**
        * @method closeWindow
        * @description Override Method for Advanced Editor
        */
        closeWindow: function() {
            //this.unsubscribeAll('afterExecCommand');
            this.toolbar.resetAllButtons();
            this.focus();        
        },
        /**
        * @method destroy
        * @description Destroys the editor, all of it's elements and objects.
        * @return {Boolean}
        */
        destroy: function() {
            if (this._nodeChangeDelayTimer) {
                clearTimeout(this._nodeChangeDelayTimer);
            }
            this.hide();
        
            YAHOO.log('Destroying Editor', 'warn', 'SimpleEditor');
            if (this.resize) {
                YAHOO.log('Destroying Resize', 'warn', 'SimpleEditor');
                this.resize.destroy();
            }
            if (this.dd) {
                YAHOO.log('Unreg DragDrop Instance', 'warn', 'SimpleEditor');
                this.dd.unreg();
            }
            if (this.get('panel')) {
                YAHOO.log('Destroying Editor Panel', 'warn', 'SimpleEditor');
                this.get('panel').destroy();
            }
            this.saveHTML();
            this.toolbar.destroy();
            YAHOO.log('Restoring TextArea', 'info', 'SimpleEditor');
            this.setStyle('visibility', 'visible');
            this.setStyle('position', 'static');
            this.setStyle('top', '');
            this.setStyle('left', '');
            var textArea = this.get('element');
            this.get('element_cont').get('parentNode').replaceChild(textArea, this.get('element_cont').get('element'));
            this.get('element_cont').get('element').innerHTML = '';
            this.set('handleSubmit', false); //Remove the submit handler
            return true;
        },        
        /**
        * @method toString
        * @description Returns a string representing the editor.
        * @return {String}
        */
        toString: function() {
            var str = 'SimpleEditor';
            if (this.get && this.get('element_cont')) {
                str = 'SimpleEditor (#' + this.get('element_cont').get('id') + ')' + ((this.get('disabled') ? ' Disabled' : ''));
            }
            return str;
        }
    });

/**
* @event toolbarLoaded
* @description Event is fired during the render process directly after the Toolbar is loaded. Allowing you to attach events to the toolbar. See <a href="YAHOO.util.Element.html#addListener">Element.addListener</a> for more information on listening for this event.
* @type YAHOO.util.CustomEvent
*/
/**
* @event cleanHTML
* @description Event is fired after the cleanHTML method is called.
* @type YAHOO.util.CustomEvent
*/
/**
* @event afterRender
* @description Event is fired after the render process finishes. See <a href="YAHOO.util.Element.html#addListener">Element.addListener</a> for more information on listening for this event.
* @type YAHOO.util.CustomEvent
*/
/**
* @event editorContentLoaded
* @description Event is fired after the editor iframe's document fully loads and fires it's onload event. From here you can start injecting your own things into the document. See <a href="YAHOO.util.Element.html#addListener">Element.addListener</a> for more information on listening for this event.
* @type YAHOO.util.CustomEvent
*/
/**
* @event beforeNodeChange
* @description Event fires at the beginning of the nodeChange process. See <a href="YAHOO.util.Element.html#addListener">Element.addListener</a> for more information on listening for this event.
* @type YAHOO.util.CustomEvent
*/
/**
* @event afterNodeChange
* @description Event fires at the end of the nodeChange process. See <a href="YAHOO.util.Element.html#addListener">Element.addListener</a> for more information on listening for this event.
* @type YAHOO.util.CustomEvent
*/
/**
* @event beforeExecCommand
* @description Event fires at the beginning of the execCommand process. See <a href="YAHOO.util.Element.html#addListener">Element.addListener</a> for more information on listening for this event.
* @type YAHOO.util.CustomEvent
*/
/**
* @event afterExecCommand
* @description Event fires at the end of the execCommand process. See <a href="YAHOO.util.Element.html#addListener">Element.addListener</a> for more information on listening for this event.
* @type YAHOO.util.CustomEvent
*/
/**
* @event editorMouseUp
* @param {Event} ev The DOM Event that occured
* @description Passed through HTML Event. See <a href="YAHOO.util.Element.html#addListener">Element.addListener</a> for more information on listening for this event.
* @type YAHOO.util.CustomEvent
*/
/**
* @event editorMouseDown
* @param {Event} ev The DOM Event that occured
* @description Passed through HTML Event. See <a href="YAHOO.util.Element.html#addListener">Element.addListener</a> for more information on listening for this event.
* @type YAHOO.util.CustomEvent
*/
/**
* @event editorDoubleClick
* @param {Event} ev The DOM Event that occured
* @description Passed through HTML Event. See <a href="YAHOO.util.Element.html#addListener">Element.addListener</a> for more information on listening for this event.
* @type YAHOO.util.CustomEvent
*/
/**
* @event editorClick
* @param {Event} ev The DOM Event that occured
* @description Passed through HTML Event. See <a href="YAHOO.util.Element.html#addListener">Element.addListener</a> for more information on listening for this event.
* @type YAHOO.util.CustomEvent
*/
/**
* @event editorKeyUp
* @param {Event} ev The DOM Event that occured
* @description Passed through HTML Event. See <a href="YAHOO.util.Element.html#addListener">Element.addListener</a> for more information on listening for this event.
* @type YAHOO.util.CustomEvent
*/
/**
* @event editorKeyPress
* @param {Event} ev The DOM Event that occured
* @description Passed through HTML Event. See <a href="YAHOO.util.Element.html#addListener">Element.addListener</a> for more information on listening for this event.
* @type YAHOO.util.CustomEvent
*/
/**
* @event editorKeyDown
* @param {Event} ev The DOM Event that occured
* @description Passed through HTML Event. See <a href="YAHOO.util.Element.html#addListener">Element.addListener</a> for more information on listening for this event.
* @type YAHOO.util.CustomEvent
*/
/**
* @event beforeEditorMouseUp
* @param {Event} ev The DOM Event that occured
* @description Fires before editor event, returning false will stop the internal processing.
* @type YAHOO.util.CustomEvent
*/
/**
* @event beforeEditorMouseDown
* @param {Event} ev The DOM Event that occured
* @description Fires before editor event, returning false will stop the internal processing.
* @type YAHOO.util.CustomEvent
*/
/**
* @event beforeEditorDoubleClick
* @param {Event} ev The DOM Event that occured
* @description Fires before editor event, returning false will stop the internal processing.
* @type YAHOO.util.CustomEvent
*/
/**
* @event beforeEditorClick
* @param {Event} ev The DOM Event that occured
* @description Fires before editor event, returning false will stop the internal processing.
* @type YAHOO.util.CustomEvent
*/
/**
* @event beforeEditorKeyUp
* @param {Event} ev The DOM Event that occured
* @description Fires before editor event, returning false will stop the internal processing.
* @type YAHOO.util.CustomEvent
*/
/**
* @event beforeEditorKeyPress
* @param {Event} ev The DOM Event that occured
* @description Fires before editor event, returning false will stop the internal processing.
* @type YAHOO.util.CustomEvent
*/
/**
* @event beforeEditorKeyDown
* @param {Event} ev The DOM Event that occured
* @description Fires before editor event, returning false will stop the internal processing.
* @type YAHOO.util.CustomEvent
*/

/**
* @event editorWindowFocus
* @description Fires when the iframe is focused. Note, this is window focus event, not an Editor focus event.
* @type YAHOO.util.CustomEvent
*/
/**
* @event editorWindowBlur
* @description Fires when the iframe is blurred. Note, this is window blur event, not an Editor blur event.
* @type YAHOO.util.CustomEvent
*/


/**
 * @description Singleton object used to track the open window objects and panels across the various open editors
 * @class EditorInfo
 * @static
*/
YAHOO.widget.EditorInfo = {
    /**
    * @private
    * @property _instances
    * @description A reference to all editors on the page.
    * @type Object
    */
    _instances: {},
    /**
    * @private
    * @property blankImage
    * @description A reference to the blankImage url
    * @type String 
    */
    blankImage: '',
    /**
    * @private
    * @property window
    * @description A reference to the currently open window object in any editor on the page.
    * @type Object <a href="YAHOO.widget.EditorWindow.html">YAHOO.widget.EditorWindow</a>
    */
    window: {},
    /**
    * @private
    * @property panel
    * @description A reference to the currently open panel in any editor on the page.
    * @type Object <a href="YAHOO.widget.Overlay.html">YAHOO.widget.Overlay</a>
    */
    panel: null,
    /**
    * @method getEditorById
    * @description Returns a reference to the Editor object associated with the given textarea
    * @param {String/HTMLElement} id The id or reference of the textarea to return the Editor instance of
    * @return Object <a href="YAHOO.widget.Editor.html">YAHOO.widget.Editor</a>
    */
    getEditorById: function(id) {
        if (!YAHOO.lang.isString(id)) {
            //Not a string, assume a node Reference
            id = id.id;
        }
        if (this._instances[id]) {
            return this._instances[id];
        }
        return false;
    },
    /**
    * @method saveAll
    * @description Saves all Editor instances on the page. If a form reference is passed, only Editor's bound to this form will be saved.
    * @param {HTMLElement} form The form to check if this Editor instance belongs to
    */
    saveAll: function(form) {
        var i, e, items = YAHOO.widget.EditorInfo._instances;
        if (form) {
            for (i in items) {
                if (Lang.hasOwnProperty(items, i)) {
                    e = items[i];
                    if (e.get('element').form && (e.get('element').form == form)) {
                        e.saveHTML();
                    }
                }
            }
        } else {
            for (i in items) {
                if (Lang.hasOwnProperty(items, i)) {
                    items[i].saveHTML();
                }
            }
        }
    },
    /**
    * @method toString
    * @description Returns a string representing the EditorInfo.
    * @return {String}
    */
    toString: function() {
        var len = 0;
        for (var i in this._instances) {
            if (Lang.hasOwnProperty(this._instances, i)) {
                len++;
            }
        }
        return 'Editor Info (' + len + ' registered intance' + ((len > 1) ? 's' : '') + ')';
    }
};



    
})();
