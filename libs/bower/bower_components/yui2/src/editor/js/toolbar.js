/**
 * @module editor
 * @description <p>Creates a rich Toolbar widget based on Button. Primarily used with the Rich Text Editor</p>
 * @namespace YAHOO.widget
 * @requires yahoo, dom, element, event, toolbarbutton
 * @optional container_core, dragdrop
 */
(function() {
var Dom = YAHOO.util.Dom,
    Event = YAHOO.util.Event,
    Lang = YAHOO.lang;
    
    var getButton = function(id) {
        var button = id;
        if (Lang.isString(id)) {
            button = this.getButtonById(id);
        }
        if (Lang.isNumber(id)) {
            button = this.getButtonByIndex(id);
        }
        if ((!(button instanceof YAHOO.widget.ToolbarButton)) && (!(button instanceof YAHOO.widget.ToolbarButtonAdvanced))) {
            button = this.getButtonByValue(id);
        }
        if ((button instanceof YAHOO.widget.ToolbarButton) || (button instanceof YAHOO.widget.ToolbarButtonAdvanced)) {
            return button;
        }
        return false;
    };

    /**
     * Provides a rich toolbar widget based on the button and menu widgets
     * @constructor
     * @class Toolbar
     * @extends YAHOO.util.Element
     * @param {String/HTMLElement} el The element to turn into a toolbar.
     * @param {Object} attrs Object liternal containing configuration parameters.
    */
    YAHOO.widget.Toolbar = function(el, attrs) {
        YAHOO.log('Toolbar Initalizing', 'info', 'Toolbar');
        YAHOO.log(arguments.length + ' arguments passed to constructor', 'info', 'Toolbar');
        
        if (Lang.isObject(arguments[0]) && !Dom.get(el).nodeType) {
            attrs = el;
        }
        var local_attrs = {};
        if (attrs) {
            Lang.augmentObject(local_attrs, attrs); //Break the config reference
        }
        

        var oConfig = {
            element: null,
            attributes: local_attrs
        };
        
        
        if (Lang.isString(el) && Dom.get(el)) {
            oConfig.element = Dom.get(el);
        } else if (Lang.isObject(el) && Dom.get(el) && Dom.get(el).nodeType) {  
            oConfig.element = Dom.get(el);
        }
        

        if (!oConfig.element) {
            YAHOO.log('No element defined, creating toolbar container', 'warn', 'Toolbar');
            oConfig.element = document.createElement('DIV');
            oConfig.element.id = Dom.generateId();
            
            if (local_attrs.container && Dom.get(local_attrs.container)) {
                YAHOO.log('Container found in config appending to it (' + Dom.get(local_attrs.container).id + ')', 'info', 'Toolbar');
                Dom.get(local_attrs.container).appendChild(oConfig.element);
            }
        }
        

        if (!oConfig.element.id) {
            oConfig.element.id = ((Lang.isString(el)) ? el : Dom.generateId());
            YAHOO.log('No element ID defined for toolbar container, creating..', 'warn', 'Toolbar');
        }
        YAHOO.log('Initing toolbar with id: ' + oConfig.element.id, 'info', 'Toolbar');
        
        var fs = document.createElement('fieldset');
        var lg = document.createElement('legend');
        lg.innerHTML = 'Toolbar';
        fs.appendChild(lg);
        
        var cont = document.createElement('DIV');
        oConfig.attributes.cont = cont;
        Dom.addClass(cont, 'yui-toolbar-subcont');
        fs.appendChild(cont);
        oConfig.element.appendChild(fs);

        oConfig.element.tabIndex = -1;

        
        oConfig.attributes.element = oConfig.element;
        oConfig.attributes.id = oConfig.element.id;

        this._configuredButtons = [];

        YAHOO.widget.Toolbar.superclass.constructor.call(this, oConfig.element, oConfig.attributes);
         
    };

    YAHOO.extend(YAHOO.widget.Toolbar, YAHOO.util.Element, {
        /**
        * @protected
        * @property _configuredButtons
        * @type Array
        */
        _configuredButtons: null,
        /**
        * @method _addMenuClasses
        * @private
        * @description This method is called from Menu's renderEvent to add a few more classes to the menu items
        * @param {String} ev The event that fired.
        * @param {Array} na Array of event information.
        * @param {Object} o Button config object. 
        */
        _addMenuClasses: function(ev, na, o) {
            Dom.addClass(this.element, 'yui-toolbar-' + o.get('value') + '-menu');
            if (Dom.hasClass(o._button.parentNode.parentNode, 'yui-toolbar-select')) {
                Dom.addClass(this.element, 'yui-toolbar-select-menu');
            }
            var items = this.getItems();
            for (var i = 0; i < items.length; i++) {
                Dom.addClass(items[i].element, 'yui-toolbar-' + o.get('value') + '-' + ((items[i].value) ? items[i].value.replace(/ /g, '-').toLowerCase() : items[i]._oText.nodeValue.replace(/ /g, '-').toLowerCase()));
                Dom.addClass(items[i].element, 'yui-toolbar-' + o.get('value') + '-' + ((items[i].value) ? items[i].value.replace(/ /g, '-') : items[i]._oText.nodeValue.replace(/ /g, '-')));
            }
        },
        /** 
        * @property buttonType
        * @description The default button to use
        * @type Object
        */
        buttonType: YAHOO.widget.ToolbarButton,
        /** 
        * @property dd
        * @description The DragDrop instance associated with the Toolbar
        * @type Object
        */
        dd: null,
        /** 
        * @property _colorData
        * @description Object reference containing colors hex and text values.
        * @type Object
        */
        _colorData: {
/* {{{ _colorData */
    '#111111': 'Obsidian',
    '#2D2D2D': 'Dark Gray',
    '#434343': 'Shale',
    '#5B5B5B': 'Flint',
    '#737373': 'Gray',
    '#8B8B8B': 'Concrete',
    '#A2A2A2': 'Gray',
    '#B9B9B9': 'Titanium',
    '#000000': 'Black',
    '#D0D0D0': 'Light Gray',
    '#E6E6E6': 'Silver',
    '#FFFFFF': 'White',
    '#BFBF00': 'Pumpkin',
    '#FFFF00': 'Yellow',
    '#FFFF40': 'Banana',
    '#FFFF80': 'Pale Yellow',
    '#FFFFBF': 'Butter',
    '#525330': 'Raw Siena',
    '#898A49': 'Mildew',
    '#AEA945': 'Olive',
    '#7F7F00': 'Paprika',
    '#C3BE71': 'Earth',
    '#E0DCAA': 'Khaki',
    '#FCFAE1': 'Cream',
    '#60BF00': 'Cactus',
    '#80FF00': 'Chartreuse',
    '#A0FF40': 'Green',
    '#C0FF80': 'Pale Lime',
    '#DFFFBF': 'Light Mint',
    '#3B5738': 'Green',
    '#668F5A': 'Lime Gray',
    '#7F9757': 'Yellow',
    '#407F00': 'Clover',
    '#8A9B55': 'Pistachio',
    '#B7C296': 'Light Jade',
    '#E6EBD5': 'Breakwater',
    '#00BF00': 'Spring Frost',
    '#00FF80': 'Pastel Green',
    '#40FFA0': 'Light Emerald',
    '#80FFC0': 'Sea Foam',
    '#BFFFDF': 'Sea Mist',
    '#033D21': 'Dark Forrest',
    '#438059': 'Moss',
    '#7FA37C': 'Medium Green',
    '#007F40': 'Pine',
    '#8DAE94': 'Yellow Gray Green',
    '#ACC6B5': 'Aqua Lung',
    '#DDEBE2': 'Sea Vapor',
    '#00BFBF': 'Fog',
    '#00FFFF': 'Cyan',
    '#40FFFF': 'Turquoise Blue',
    '#80FFFF': 'Light Aqua',
    '#BFFFFF': 'Pale Cyan',
    '#033D3D': 'Dark Teal',
    '#347D7E': 'Gray Turquoise',
    '#609A9F': 'Green Blue',
    '#007F7F': 'Seaweed',
    '#96BDC4': 'Green Gray',
    '#B5D1D7': 'Soapstone',
    '#E2F1F4': 'Light Turquoise',
    '#0060BF': 'Summer Sky',
    '#0080FF': 'Sky Blue',
    '#40A0FF': 'Electric Blue',
    '#80C0FF': 'Light Azure',
    '#BFDFFF': 'Ice Blue',
    '#1B2C48': 'Navy',
    '#385376': 'Biscay',
    '#57708F': 'Dusty Blue',
    '#00407F': 'Sea Blue',
    '#7792AC': 'Sky Blue Gray',
    '#A8BED1': 'Morning Sky',
    '#DEEBF6': 'Vapor',
    '#0000BF': 'Deep Blue',
    '#0000FF': 'Blue',
    '#4040FF': 'Cerulean Blue',
    '#8080FF': 'Evening Blue',
    '#BFBFFF': 'Light Blue',
    '#212143': 'Deep Indigo',
    '#373E68': 'Sea Blue',
    '#444F75': 'Night Blue',
    '#00007F': 'Indigo Blue',
    '#585E82': 'Dockside',
    '#8687A4': 'Blue Gray',
    '#D2D1E1': 'Light Blue Gray',
    '#6000BF': 'Neon Violet',
    '#8000FF': 'Blue Violet',
    '#A040FF': 'Violet Purple',
    '#C080FF': 'Violet Dusk',
    '#DFBFFF': 'Pale Lavender',
    '#302449': 'Cool Shale',
    '#54466F': 'Dark Indigo',
    '#655A7F': 'Dark Violet',
    '#40007F': 'Violet',
    '#726284': 'Smoky Violet',
    '#9E8FA9': 'TopParentItem Gray',
    '#DCD1DF': 'Violet White',
    '#BF00BF': 'Royal Violet',
    '#FF00FF': 'Fuchsia',
    '#FF40FF': 'Magenta',
    '#FF80FF': 'Orchid',
    '#FFBFFF': 'Pale Magenta',
    '#4A234A': 'Dark Purple',
    '#794A72': 'Medium Purple',
    '#936386': 'Cool Granite',
    '#7F007F': 'Purple',
    '#9D7292': 'Purple Moon',
    '#C0A0B6': 'Pale Purple',
    '#ECDAE5': 'Pink Cloud',
    '#BF005F': 'Hot Pink',
    '#FF007F': 'Deep Pink',
    '#FF409F': 'Grape',
    '#FF80BF': 'Electric Pink',
    '#FFBFDF': 'Pink',
    '#451528': 'Purple Red',
    '#823857': 'Purple Dino',
    '#A94A76': 'Purple Gray',
    '#7F003F': 'Rose',
    '#BC6F95': 'Antique Mauve',
    '#D8A5BB': 'Cool Marble',
    '#F7DDE9': 'Pink Granite',
    '#C00000': 'Apple',
    '#FF0000': 'Fire Truck',
    '#FF4040': 'Pale Red',
    '#FF8080': 'Salmon',
    '#FFC0C0': 'Warm Pink',
    '#441415': 'Sepia',
    '#82393C': 'Rust',
    '#AA4D4E': 'Brick',
    '#800000': 'Brick Red',
    '#BC6E6E': 'Mauve',
    '#D8A3A4': 'Shrimp Pink',
    '#F8DDDD': 'Shell Pink',
    '#BF5F00': 'Dark Orange',
    '#FF7F00': 'Orange',
    '#FF9F40': 'Grapefruit',
    '#FFBF80': 'Canteloupe',
    '#FFDFBF': 'Wax',
    '#482C1B': 'Dark Brick',
    '#855A40': 'Dirt',
    '#B27C51': 'Tan',
    '#7F3F00': 'Nutmeg',
    '#C49B71': 'Mustard',
    '#E1C4A8': 'Pale Tan',
    '#FDEEE0': 'Marble'
/* }}} */
        },
        /** 
        * @property _colorPicker
        * @description The HTML Element containing the colorPicker
        * @type HTMLElement
        */
        _colorPicker: null,
        /** 
        * @property STR_COLLAPSE
        * @description String for Toolbar Collapse Button
        * @type String
        */
        STR_COLLAPSE: 'Collapse Toolbar',
        /** 
        * @property STR_EXPAND
        * @description String for Toolbar Collapse Button - Expand
        * @type String
        */
        STR_EXPAND: 'Expand Toolbar',
        /** 
        * @property STR_SPIN_LABEL
        * @description String for spinbutton dynamic label. Note the {VALUE} will be replaced with YAHOO.lang.substitute
        * @type String
        */
        STR_SPIN_LABEL: 'Spin Button with value {VALUE}. Use Control Shift Up Arrow and Control Shift Down arrow keys to increase or decrease the value.',
        /** 
        * @property STR_SPIN_UP
        * @description String for spinbutton up
        * @type String
        */
        STR_SPIN_UP: 'Click to increase the value of this input',
        /** 
        * @property STR_SPIN_DOWN
        * @description String for spinbutton down
        * @type String
        */
        STR_SPIN_DOWN: 'Click to decrease the value of this input',
        /** 
        * @property _titlebar
        * @description Object reference to the titlebar
        * @type HTMLElement
        */
        _titlebar: null,
        /** 
        * @property browser
        * @description Standard browser detection
        * @type Object
        */
        browser: YAHOO.env.ua,
        /**
        * @protected
        * @property _buttonList
        * @description Internal property list of current buttons in the toolbar
        * @type Array
        */
        _buttonList: null,
        /**
        * @protected
        * @property _buttonGroupList
        * @description Internal property list of current button groups in the toolbar
        * @type Array
        */
        _buttonGroupList: null,
        /**
        * @protected
        * @property _sep
        * @description Internal reference to the separator HTML Element for cloning
        * @type HTMLElement
        */
        _sep: null,
        /**
        * @protected
        * @property _sepCount
        * @description Internal refernce for counting separators, so we can give them a useful class name for styling
        * @type Number
        */
        _sepCount: null,
        /**
        * @protected
        * @property draghandle
        * @type HTMLElement
        */
        _dragHandle: null,
        /**
        * @protected
        * @property _toolbarConfigs
        * @type Object
        */
        _toolbarConfigs: {
            renderer: true
        },
        /**
        * @protected
        * @property CLASS_CONTAINER
        * @description Default CSS class to apply to the toolbar container element
        * @type String
        */
        CLASS_CONTAINER: 'yui-toolbar-container',
        /**
        * @protected
        * @property CLASS_DRAGHANDLE
        * @description Default CSS class to apply to the toolbar's drag handle element
        * @type String
        */
        CLASS_DRAGHANDLE: 'yui-toolbar-draghandle',
        /**
        * @protected
        * @property CLASS_SEPARATOR
        * @description Default CSS class to apply to all separators in the toolbar
        * @type String
        */
        CLASS_SEPARATOR: 'yui-toolbar-separator',
        /**
        * @protected
        * @property CLASS_DISABLED
        * @description Default CSS class to apply when the toolbar is disabled
        * @type String
        */
        CLASS_DISABLED: 'yui-toolbar-disabled',
        /**
        * @protected
        * @property CLASS_PREFIX
        * @description Default prefix for dynamically created class names
        * @type String
        */
        CLASS_PREFIX: 'yui-toolbar',
        /** 
        * @method init
        * @description The Toolbar class's initialization method
        */
        init: function(p_oElement, p_oAttributes) {
            YAHOO.widget.Toolbar.superclass.init.call(this, p_oElement, p_oAttributes);
        },
        /**
        * @method initAttributes
        * @description Initializes all of the configuration attributes used to create 
        * the toolbar.
        * @param {Object} attr Object literal specifying a set of 
        * configuration attributes used to create the toolbar.
        */
        initAttributes: function(attr) {
            YAHOO.widget.Toolbar.superclass.initAttributes.call(this, attr);
            this.addClass(this.CLASS_CONTAINER);

            /**
            * @attribute buttonType
            * @description The buttonType to use (advanced or basic)
            * @type String
            */
            this.setAttributeConfig('buttonType', {
                value: attr.buttonType || 'basic',
                writeOnce: true,
                validator: function(type) {
                    switch (type) {
                        case 'advanced':
                        case 'basic':
                            return true;
                    }
                    return false;
                },
                method: function(type) {
                    if (type == 'advanced') {
                        if (YAHOO.widget.Button) {
                            this.buttonType = YAHOO.widget.ToolbarButtonAdvanced;
                        } else {
                            YAHOO.log('Can not find YAHOO.widget.Button', 'error', 'Toolbar');
                            this.buttonType = YAHOO.widget.ToolbarButton;
                        }
                    } else {
                        this.buttonType = YAHOO.widget.ToolbarButton;
                    }
                }
            });


            /**
            * @attribute buttons
            * @description Object specifying the buttons to include in the toolbar
            * Example:
            * <code><pre>
            * {
            *   { id: 'b3', type: 'button', label: 'Underline', value: 'underline' },
            *   { type: 'separator' },
            *   { id: 'b4', type: 'menu', label: 'Align', value: 'align',
            *       menu: [
            *           { text: "Left", value: 'alignleft' },
            *           { text: "Center", value: 'aligncenter' },
            *           { text: "Right", value: 'alignright' }
            *       ]
            *   }
            * }
            * </pre></code>
            * @type Array
            */
            
            this.setAttributeConfig('buttons', {
                value: [],
                writeOnce: true,
                method: function(data) {
                    var i, button, buttons, len, b;
                    for (i in data) {
                        if (Lang.hasOwnProperty(data, i)) {
                            if (data[i].type == 'separator') {
                                this.addSeparator();
                            } else if (data[i].group !== undefined) {
                                buttons = this.addButtonGroup(data[i]);
                                if (buttons) {
                                    len = buttons.length;
                                    for(b = 0; b < len; b++) {
                                        if (buttons[b]) {
                                            this._configuredButtons[this._configuredButtons.length] = buttons[b].id;
                                        }
                                    }
                                }
                                
                            } else {
                                button = this.addButton(data[i]);
                                if (button) {
                                    this._configuredButtons[this._configuredButtons.length] = button.id;
                                }
                            }
                        }
                    }
                }
            });

            /**
            * @attribute disabled
            * @description Boolean indicating if the toolbar should be disabled. It will also disable the draggable attribute if it is on.
            * @default false
            * @type Boolean
            */
            this.setAttributeConfig('disabled', {
                value: false,
                method: function(disabled) {
                    if (this.get('disabled') === disabled) {
                        return false;
                    }
                    if (disabled) {
                        this.addClass(this.CLASS_DISABLED);
                        this.set('draggable', false);
                        this.disableAllButtons();
                    } else {
                        this.removeClass(this.CLASS_DISABLED);
                        if (this._configs.draggable._initialConfig.value) {
                            //Draggable by default, set it back
                            this.set('draggable', true);
                        }
                        this.resetAllButtons();
                    }
                }
            });

            /**
            * @config cont
            * @description The container for the toolbar.
            * @type HTMLElement
            */
            this.setAttributeConfig('cont', {
                value: attr.cont,
                readOnly: true
            });


            /**
            * @attribute grouplabels
            * @description Boolean indicating if the toolbar should show the group label's text string.
            * @default true
            * @type Boolean
            */
            this.setAttributeConfig('grouplabels', {
                value: ((attr.grouplabels === false) ? false : true),
                method: function(grouplabels) {
                    if (grouplabels) {
                        Dom.removeClass(this.get('cont'), (this.CLASS_PREFIX + '-nogrouplabels'));
                    } else {
                        Dom.addClass(this.get('cont'), (this.CLASS_PREFIX + '-nogrouplabels'));
                    }
                }
            });
            /**
            * @attribute titlebar
            * @description Boolean indicating if the toolbar should have a titlebar. If
            * passed a string, it will use that as the titlebar text
            * @default false
            * @type Boolean or String
            */
            this.setAttributeConfig('titlebar', {
                value: false,
                method: function(titlebar) {
                    if (titlebar) {
                        if (this._titlebar && this._titlebar.parentNode) {
                            this._titlebar.parentNode.removeChild(this._titlebar);
                        }
                        this._titlebar = document.createElement('DIV');
                        this._titlebar.tabIndex = '-1';
                        Event.on(this._titlebar, 'focus', function() {
                            this._handleFocus();
                        }, this, true);
                        Dom.addClass(this._titlebar, this.CLASS_PREFIX + '-titlebar');
                        if (Lang.isString(titlebar)) {
                            var h2 = document.createElement('h2');
                            h2.tabIndex = '-1';
                            h2.innerHTML = '<a href="#" tabIndex="0">' + titlebar + '</a>';
                            this._titlebar.appendChild(h2);
                            Event.on(h2.firstChild, 'click', function(ev) {
                                Event.stopEvent(ev);
                            });
                            Event.on([h2, h2.firstChild], 'focus', function() {
                                this._handleFocus();
                            }, this, true);
                        }
                        if (this.get('firstChild')) {
                            this.insertBefore(this._titlebar, this.get('firstChild'));
                        } else {
                            this.appendChild(this._titlebar);
                        }
                        if (this.get('collapse')) {
                            this.set('collapse', true);
                        }
                    } else if (this._titlebar) {
                        if (this._titlebar && this._titlebar.parentNode) {
                            this._titlebar.parentNode.removeChild(this._titlebar);
                        }
                    }
                }
            });


            /**
            * @attribute collapse
            * @description Boolean indicating if the the titlebar should have a collapse button.
            * The collapse button will not remove the toolbar, it will minimize it to the titlebar
            * @default false
            * @type Boolean
            */
            this.setAttributeConfig('collapse', {
                value: false,
                method: function(collapse) {
                    if (this._titlebar) {
                        var collapseEl = null;
                        var el = Dom.getElementsByClassName('collapse', 'span', this._titlebar);
                        if (collapse) {
                            if (el.length > 0) {
                                //There is already a collapse button
                                return true;
                            }
                            collapseEl = document.createElement('SPAN');
                            collapseEl.innerHTML = 'X';
                            collapseEl.title = this.STR_COLLAPSE;

                            Dom.addClass(collapseEl, 'collapse');
                            this._titlebar.appendChild(collapseEl);
                            Event.addListener(collapseEl, 'click', function() {
                                if (Dom.hasClass(this.get('cont').parentNode, 'yui-toolbar-container-collapsed')) {
                                    this.collapse(false); //Expand Toolbar
                                } else {
                                    this.collapse(); //Collapse Toolbar
                                }
                            }, this, true);
                        } else {
                            collapseEl = Dom.getElementsByClassName('collapse', 'span', this._titlebar);
                            if (collapseEl[0]) {
                                if (Dom.hasClass(this.get('cont').parentNode, 'yui-toolbar-container-collapsed')) {
                                    //We are closed, reopen the titlebar..
                                    this.collapse(false); //Expand Toolbar
                                }
                                collapseEl[0].parentNode.removeChild(collapseEl[0]);
                            }
                        }
                    }
                }
            });

            /**
            * @attribute draggable
            * @description Boolean indicating if the toolbar should be draggable.  
            * @default false
            * @type Boolean
            */

            this.setAttributeConfig('draggable', {
                value: (attr.draggable || false),
                method: function(draggable) {
                    if (draggable && !this.get('titlebar')) {
                        YAHOO.log('Dragging enabled', 'info', 'Toolbar');
                        if (!this._dragHandle) {
                            this._dragHandle = document.createElement('SPAN');
                            this._dragHandle.innerHTML = '|';
                            this._dragHandle.setAttribute('title', 'Click to drag the toolbar');
                            this._dragHandle.id = this.get('id') + '_draghandle';
                            Dom.addClass(this._dragHandle, this.CLASS_DRAGHANDLE);
                            if (this.get('cont').hasChildNodes()) {
                                this.get('cont').insertBefore(this._dragHandle, this.get('cont').firstChild);
                            } else {
                                this.get('cont').appendChild(this._dragHandle);
                            }
                            this.dd = new YAHOO.util.DD(this.get('id'));
                            this.dd.setHandleElId(this._dragHandle.id);
                            
                        }
                    } else {
                        YAHOO.log('Dragging disabled', 'info', 'Toolbar');
                        if (this._dragHandle) {
                            this._dragHandle.parentNode.removeChild(this._dragHandle);
                            this._dragHandle = null;
                            this.dd = null;
                        }
                    }
                    if (this._titlebar) {
                        if (draggable) {
                            this.dd = new YAHOO.util.DD(this.get('id'));
                            this.dd.setHandleElId(this._titlebar);
                            Dom.addClass(this._titlebar, 'draggable');
                        } else {
                            Dom.removeClass(this._titlebar, 'draggable');
                            if (this.dd) {
                                this.dd.unreg();
                                this.dd = null;
                            }
                        }
                    }
                },
                validator: function(value) {
                    var ret = true;
                    if (!YAHOO.util.DD) {
                        ret = false;
                    }
                    return ret;
                }
            });

        },
        /**
        * @method addButtonGroup
        * @description Add a new button group to the toolbar. (uses addButton)
        * @param {Object} oGroup Object literal reference to the Groups Config (contains an array of button configs as well as the group label)
        */
        addButtonGroup: function(oGroup) {
            if (!this.get('element')) {
                this._queue[this._queue.length] = ['addButtonGroup', arguments];
                return false;
            }
            
            if (!this.hasClass(this.CLASS_PREFIX + '-grouped')) {
                this.addClass(this.CLASS_PREFIX + '-grouped');
            }
            var div = document.createElement('DIV');
            Dom.addClass(div, this.CLASS_PREFIX + '-group');
            Dom.addClass(div, this.CLASS_PREFIX + '-group-' + oGroup.group);
            if (oGroup.label) {
                var label = document.createElement('h3');
                label.innerHTML = oGroup.label;
                div.appendChild(label);
            }
            if (!this.get('grouplabels')) {
                Dom.addClass(this.get('cont'), this.CLASS_PREFIX, '-nogrouplabels');
            }

            this.get('cont').appendChild(div);

            //For accessibility, let's put all of the group buttons in an Unordered List
            var ul = document.createElement('ul');
            div.appendChild(ul);

            if (!this._buttonGroupList) {
                this._buttonGroupList = {};
            }
            
            this._buttonGroupList[oGroup.group] = ul;

            //An array of the button ids added to this group
            //This is used for destruction later...
            var addedButtons = [],
                button;
            

            for (var i = 0; i < oGroup.buttons.length; i++) {
                var li = document.createElement('li');
                li.className = this.CLASS_PREFIX + '-groupitem';
                ul.appendChild(li);
                if ((oGroup.buttons[i].type !== undefined) && oGroup.buttons[i].type == 'separator') {
                    this.addSeparator(li);
                } else {
                    oGroup.buttons[i].container = li;
                    button = this.addButton(oGroup.buttons[i]);
                    if (button) {
                        addedButtons[addedButtons.length]  = button.id;
                    }
                }
            }
            return addedButtons;
        },
        /**
        * @method addButtonToGroup
        * @description Add a new button to a toolbar group. Buttons supported:
        *   push, split, menu, select, color, spin
        * @param {Object} oButton Object literal reference to the Button's Config
        * @param {String} group The Group identifier passed into the initial config
        * @param {HTMLElement} after Optional HTML element to insert this button after in the DOM.
        */
        addButtonToGroup: function(oButton, group, after) {
            var groupCont = this._buttonGroupList[group],
                li = document.createElement('li');

            li.className = this.CLASS_PREFIX + '-groupitem';
            oButton.container = li;
            this.addButton(oButton, after);
            groupCont.appendChild(li);
        },
        /**
        * @method addButton
        * @description Add a new button to the toolbar. Buttons supported:
        *   push, split, menu, select, color, spin
        * @param {Object} oButton Object literal reference to the Button's Config
        * @param {HTMLElement} after Optional HTML element to insert this button after in the DOM.
        */
        addButton: function(oButton, after) {
            if (!this.get('element')) {
                this._queue[this._queue.length] = ['addButton', arguments];
                return false;
            }
            if (!this._buttonList) {
                this._buttonList = [];
            }
            YAHOO.log('Adding button of type: ' + oButton.type, 'info', 'Toolbar');
            if (!oButton.container) {
                oButton.container = this.get('cont');
            }

            if ((oButton.type == 'menu') || (oButton.type == 'split') || (oButton.type == 'select')) {
                if (Lang.isArray(oButton.menu)) {
                    for (var i in oButton.menu) {
                        if (Lang.hasOwnProperty(oButton.menu, i)) {
                            var funcObject = {
                                fn: function(ev, x, oMenu) {
                                    if (!oButton.menucmd) {
                                        oButton.menucmd = oButton.value;
                                    }
                                    oButton.value = ((oMenu.value) ? oMenu.value : oMenu._oText.nodeValue);
                                },
                                scope: this
                            };
                            oButton.menu[i].onclick = funcObject;
                        }
                    }
                }
            }
            var _oButton = {}, skip = false;
            for (var o in oButton) {
                if (Lang.hasOwnProperty(oButton, o)) {
                    if (!this._toolbarConfigs[o]) {
                        _oButton[o] = oButton[o];
                    }
                }
            }
            if (oButton.type == 'select') {
                _oButton.type = 'menu';
            }
            if (oButton.type == 'spin') {
                _oButton.type = 'push';
            }
            if (_oButton.type == 'color') {
                if (YAHOO.widget.Overlay) {
                    _oButton = this._makeColorButton(_oButton);
                } else {
                    skip = true;
                }
            }
            if (_oButton.menu) {
                if ((YAHOO.widget.Overlay) && (oButton.menu instanceof YAHOO.widget.Overlay)) {
                    oButton.menu.showEvent.subscribe(function() {
                        this._button = _oButton;
                    });
                } else {
                    for (var m = 0; m < _oButton.menu.length; m++) {
                        if (!_oButton.menu[m].value) {
                            _oButton.menu[m].value = _oButton.menu[m].text;
                        }
                    }
                    if (this.browser.webkit) {
                        _oButton.focusmenu = false;
                    }
                }
            }
            if (skip) {
                oButton = false;
            } else {
                //Add to .get('buttons') manually
                this._configs.buttons.value[this._configs.buttons.value.length] = oButton;
                
                var tmp = new this.buttonType(_oButton);
                tmp.get('element').tabIndex = '-1';
                tmp.get('element').setAttribute('role', 'button');
                tmp._selected = true;
                
                if (this.get('disabled')) {
                    //Toolbar is disabled, disable the new button too!
                    tmp.set('disabled', true);
                }
                if (!oButton.id) {
                    oButton.id = tmp.get('id');
                }
                YAHOO.log('Button created (' + oButton.type + ')', 'info', 'Toolbar');
                
                if (after) {
                    var el = tmp.get('element');
                    var nextSib = null;
                    if (after.get) {
                        nextSib = after.get('element').nextSibling;
                    } else if (after.nextSibling) {
                        nextSib = after.nextSibling;
                    }
                    if (nextSib) {
                        nextSib.parentNode.insertBefore(el, nextSib);
                    }
                }
                tmp.addClass(this.CLASS_PREFIX + '-' + tmp.get('value'));

                var icon = document.createElement('span');
                icon.className = this.CLASS_PREFIX + '-icon';
                tmp.get('element').insertBefore(icon, tmp.get('firstChild'));
                if (tmp._button.tagName.toLowerCase() == 'button') {
                    tmp.get('element').setAttribute('unselectable', 'on');
                    //Replace the Button HTML Element with an a href if it exists
                    var a = document.createElement('a');
                    a.innerHTML = tmp._button.innerHTML;
                    a.href = '#';
                    a.tabIndex = '-1';
                    Event.on(a, 'click', function(ev) {
                        Event.stopEvent(ev);
                    });
                    tmp._button.parentNode.replaceChild(a, tmp._button);
                    tmp._button = a;
                }

                if (oButton.type == 'select') {
                    if (tmp._button.tagName.toLowerCase() == 'select') {
                        icon.parentNode.removeChild(icon);
                        var iel = tmp._button,
                            parEl = tmp.get('element');
                        parEl.parentNode.replaceChild(iel, parEl);
                        //The 'element' value is currently the orphaned element
                        //In order for "destroy" to execute we need to get('element') to reference the correct node.
                        //I'm not sure if there is a direct approach to setting this value.
                        tmp._configs.element.value = iel;
                    } else {
                        //Don't put a class on it if it's a real select element
                        tmp.addClass(this.CLASS_PREFIX + '-select');
                    }
                }
                if (oButton.type == 'spin') {
                    if (!Lang.isArray(oButton.range)) {
                        oButton.range = [ 10, 100 ];
                    }
                    this._makeSpinButton(tmp, oButton);
                }
                tmp.get('element').setAttribute('title', tmp.get('label'));
                if (oButton.type != 'spin') {
                    if ((YAHOO.widget.Overlay) && (_oButton.menu instanceof YAHOO.widget.Overlay)) {
                        var showPicker = function(ev) {
                            var exec = true;
                            if (ev.keyCode && (ev.keyCode == 9)) {
                                exec = false;
                            }
                            if (exec) {
                                if (this._colorPicker) {
                                    this._colorPicker._button = oButton.value;
                                }
                                var menuEL = tmp.getMenu().element;
                                if (Dom.getStyle(menuEL, 'visibility') == 'hidden') {
                                    tmp.getMenu().show();
                                } else {
                                    tmp.getMenu().hide();
                                }
                            }
                            YAHOO.util.Event.stopEvent(ev);
                        };
                        tmp.on('mousedown', showPicker, oButton, this);
                        tmp.on('keydown', showPicker, oButton, this);
                        
                    } else if ((oButton.type != 'menu') && (oButton.type != 'select')) {
                        tmp.on('keypress', this._buttonClick, oButton, this);
                        tmp.on('mousedown', function(ev) {
                            YAHOO.util.Event.stopEvent(ev);
                            this._buttonClick(ev, oButton);
                        }, oButton, this);
                        tmp.on('click', function(ev) {
                            YAHOO.util.Event.stopEvent(ev);
                        });
                    } else {
                        //Stop the mousedown event so we can trap the selection in the editor!
                        tmp.on('mousedown', function(ev) {
                            //YAHOO.util.Event.stopEvent(ev);
                        });
                        tmp.on('click', function(ev) {
                            //YAHOO.util.Event.stopEvent(ev);
                        });
                        tmp.on('change', function(ev) {
                            if (!ev.target) {
                                if (!oButton.menucmd) {
                                    oButton.menucmd = oButton.value;
                                }
                                oButton.value = ev.value;
                                this._buttonClick(ev, oButton);
                            }
                        }, this, true);

                        var self = this;
                        //Hijack the mousedown event in the menu and make it fire a button click..
                        tmp.on('appendTo', function() {
                            var tmp = this;
                            if (tmp.getMenu() && tmp.getMenu().mouseDownEvent) {
                                tmp.getMenu().mouseDownEvent.subscribe(function(ev, args) {
                                    YAHOO.log('mouseDownEvent', 'warn', 'Toolbar');
                                    var oMenu = args[1];
                                    YAHOO.util.Event.stopEvent(args[0]);
                                    tmp._onMenuClick(args[0], tmp);
                                    if (!oButton.menucmd) {
                                        oButton.menucmd = oButton.value;
                                    }
                                    oButton.value = ((oMenu.value) ? oMenu.value : oMenu._oText.nodeValue);
                                    self._buttonClick.call(self, args[1], oButton);
                                    tmp._hideMenu();
                                    return false;
                                });
                                tmp.getMenu().clickEvent.subscribe(function(ev, args) {
                                    YAHOO.log('clickEvent', 'warn', 'Toolbar');
                                    YAHOO.util.Event.stopEvent(args[0]);
                                });
                                tmp.getMenu().mouseUpEvent.subscribe(function(ev, args) {
                                    YAHOO.log('mouseUpEvent', 'warn', 'Toolbar');
                                    YAHOO.util.Event.stopEvent(args[0]);
                                });
                            }
                        });
                        
                    }
                } else {
                    //Stop the mousedown event so we can trap the selection in the editor!
                    tmp.on('mousedown', function(ev) {
                        YAHOO.util.Event.stopEvent(ev);
                    });
                    tmp.on('click', function(ev) {
                        YAHOO.util.Event.stopEvent(ev);
                    });
                }
                if (this.browser.ie) {
                    /*
                    //Add a couple of new events for IE
                    tmp.DOM_EVENTS.focusin = true;
                    tmp.DOM_EVENTS.focusout = true;
                    
                    //Stop them so we don't loose focus in the Editor
                    tmp.on('focusin', function(ev) {
                        YAHOO.util.Event.stopEvent(ev);
                    }, oButton, this);
                    
                    tmp.on('focusout', function(ev) {
                        YAHOO.util.Event.stopEvent(ev);
                    }, oButton, this);
                    tmp.on('click', function(ev) {
                        YAHOO.util.Event.stopEvent(ev);
                    }, oButton, this);
                    */
                }
                if (this.browser.webkit) {
                    //This will keep the document from gaining focus and the editor from loosing it..
                    //Forcefully remove the focus calls in button!
                    tmp.hasFocus = function() {
                        return true;
                    };
                }
                this._buttonList[this._buttonList.length] = tmp;
                if ((oButton.type == 'menu') || (oButton.type == 'split') || (oButton.type == 'select')) {
                    if (Lang.isArray(oButton.menu)) {
                        YAHOO.log('Button type is (' + oButton.type + '), doing extra renderer work.', 'info', 'Toolbar');
                        var menu = tmp.getMenu();
                        if (menu && menu.renderEvent) {
                            menu.renderEvent.subscribe(this._addMenuClasses, tmp);
                            if (oButton.renderer) {
                                menu.renderEvent.subscribe(oButton.renderer, tmp);
                            }
                        }
                    }
                }
            }
            return oButton;
        },
        /**
        * @method addSeparator
        * @description Add a new button separator to the toolbar.
        * @param {HTMLElement} cont Optional HTML element to insert this button into.
        * @param {HTMLElement} after Optional HTML element to insert this button after in the DOM.
        */
        addSeparator: function(cont, after) {
            if (!this.get('element')) {
                this._queue[this._queue.length] = ['addSeparator', arguments];
                return false;
            }
            var sepCont = ((cont) ? cont : this.get('cont'));
            if (!this.get('element')) {
                this._queue[this._queue.length] = ['addSeparator', arguments];
                return false;
            }
            if (this._sepCount === null) {
                this._sepCount = 0;
            }
            if (!this._sep) {
                YAHOO.log('Separator does not yet exist, creating', 'info', 'Toolbar');
                this._sep = document.createElement('SPAN');
                Dom.addClass(this._sep, this.CLASS_SEPARATOR);
                this._sep.innerHTML = '|';
            }
            YAHOO.log('Separator does exist, cloning', 'info', 'Toolbar');
            var _sep = this._sep.cloneNode(true);
            this._sepCount++;
            Dom.addClass(_sep, this.CLASS_SEPARATOR + '-' + this._sepCount);
            if (after) {
                var nextSib = null;
                if (after.get) {
                    nextSib = after.get('element').nextSibling;
                } else if (after.nextSibling) {
                    nextSib = after.nextSibling;
                } else {
                    nextSib = after;
                }
                if (nextSib) {
                    if (nextSib == after) {
                        nextSib.parentNode.appendChild(_sep);
                    } else {
                        nextSib.parentNode.insertBefore(_sep, nextSib);
                    }
                }
            } else {
                sepCont.appendChild(_sep);
            }
            return _sep;
        },
        /**
        * @method _createColorPicker
        * @private
        * @description Creates the core DOM reference to the color picker menu item.
        * @param {String} id the id of the toolbar to prefix this DOM container with.
        */
        _createColorPicker: function(id) {
            if (Dom.get(id + '_colors')) {
               Dom.get(id + '_colors').parentNode.removeChild(Dom.get(id + '_colors'));
            }
            var picker = document.createElement('div');
            picker.className = 'yui-toolbar-colors';
            picker.id = id + '_colors';
            picker.style.display = 'none';
            Event.on(window, 'load', function() {
                document.body.appendChild(picker);
            }, this, true);

            this._colorPicker = picker;

            var html = '';
            for (var i in this._colorData) {
                if (Lang.hasOwnProperty(this._colorData, i)) {
                    html += '<a style="background-color: ' + i + '" href="#">' + i.replace('#', '') + '</a>';
                }
            }
            html += '<span><em>X</em><strong></strong></span>';
            window.setTimeout(function() {
                picker.innerHTML = html;
            }, 0);

            Event.on(picker, 'mouseover', function(ev) {
                var picker = this._colorPicker;
                var em = picker.getElementsByTagName('em')[0];
                var strong = picker.getElementsByTagName('strong')[0];
                var tar = Event.getTarget(ev);
                if (tar.tagName.toLowerCase() == 'a') {
                    em.style.backgroundColor = tar.style.backgroundColor;
                    strong.innerHTML = this._colorData['#' + tar.innerHTML] + '<br>' + tar.innerHTML;
                }
            }, this, true);
            Event.on(picker, 'focus', function(ev) {
                Event.stopEvent(ev);
            });
            Event.on(picker, 'click', function(ev) {
                Event.stopEvent(ev);
            });
            Event.on(picker, 'mousedown', function(ev) {
                Event.stopEvent(ev);
                var tar = Event.getTarget(ev);
                if (tar.tagName.toLowerCase() == 'a') {
                    var retVal = this.fireEvent('colorPickerClicked', { type: 'colorPickerClicked', target: this, button: this._colorPicker._button, color: tar.innerHTML, colorName: this._colorData['#' + tar.innerHTML] } );
                    if (retVal !== false) {
                        var info = {
                            color: tar.innerHTML,
                            colorName: this._colorData['#' + tar.innerHTML],
                            value: this._colorPicker._button 
                        };
                    
                        this.fireEvent('buttonClick', { type: 'buttonClick', target: this.get('element'), button: info });
                    }
                    this.getButtonByValue(this._colorPicker._button).getMenu().hide();
                }
            }, this, true);
        },
        /**
        * @method _resetColorPicker
        * @private
        * @description Clears the currently selected color or mouseover color in the color picker.
        */
        _resetColorPicker: function() {
            var em = this._colorPicker.getElementsByTagName('em')[0];
            var strong = this._colorPicker.getElementsByTagName('strong')[0];
            em.style.backgroundColor = 'transparent';
            strong.innerHTML = '';
        },
        /**
        * @method _makeColorButton
        * @private
        * @description Called to turn a "color" button into a menu button with an Overlay for the menu.
        * @param {Object} _oButton <a href="YAHOO.widget.ToolbarButton.html">YAHOO.widget.ToolbarButton</a> reference
        */
        _makeColorButton: function(_oButton) {
            if (!this._colorPicker) {   
                this._createColorPicker(this.get('id'));
            }
            _oButton.type = 'color';
            _oButton.menu = new YAHOO.widget.Overlay(this.get('id') + '_' + _oButton.value + '_menu', { visible: false, position: 'absolute', iframe: true });
            _oButton.menu.setBody('');
            _oButton.menu.render(this.get('cont'));
            Dom.addClass(_oButton.menu.element, 'yui-button-menu');
            Dom.addClass(_oButton.menu.element, 'yui-color-button-menu');
            _oButton.menu.beforeShowEvent.subscribe(function() {
                _oButton.menu.cfg.setProperty('zindex', 5); //Re Adjust the overlays zIndex.. not sure why.
                _oButton.menu.cfg.setProperty('context', [this.getButtonById(_oButton.id).get('element'), 'tl', 'bl']); //Re Adjust the overlay.. not sure why.
                //Move the DOM reference of the color picker to the Overlay that we are about to show.
                this._resetColorPicker();
                var _p = this._colorPicker;
                if (_p.parentNode) {
                    _p.parentNode.removeChild(_p);
                }
                _oButton.menu.setBody('');
                _oButton.menu.appendToBody(_p);
                this._colorPicker.style.display = 'block';
            }, this, true);
            return _oButton;
        },
        /**
        * @private
        * @method _makeSpinButton
        * @description Create a button similar to an OS Spin button.. It has an up/down arrow combo to scroll through a range of int values.
        * @param {Object} _button <a href="YAHOO.widget.ToolbarButton.html">YAHOO.widget.ToolbarButton</a> reference
        * @param {Object} oButton Object literal containing the buttons initial config
        */
        _makeSpinButton: function(_button, oButton) {
            _button.addClass(this.CLASS_PREFIX + '-spinbutton');
            var self = this,
                _par = _button._button.parentNode.parentNode, //parentNode of Button Element for appending child
                range = oButton.range,
                _b1 = document.createElement('a'),
                _b2 = document.createElement('a');
                _b1.href = '#';
                _b2.href = '#';
                _b1.tabIndex = '-1';
                _b2.tabIndex = '-1';
            
            //Setup the up and down arrows
            _b1.className = 'up';
            _b1.title = this.STR_SPIN_UP;
            _b1.innerHTML = this.STR_SPIN_UP;
            _b2.className = 'down';
            _b2.title = this.STR_SPIN_DOWN;
            _b2.innerHTML = this.STR_SPIN_DOWN;

            //Append them to the container
            _par.appendChild(_b1);
            _par.appendChild(_b2);
            
            var label = YAHOO.lang.substitute(this.STR_SPIN_LABEL, { VALUE: _button.get('label') });
            _button.set('title', label);

            var cleanVal = function(value) {
                value = ((value < range[0]) ? range[0] : value);
                value = ((value > range[1]) ? range[1] : value);
                return value;
            };

            var br = this.browser;
            var tbar = false;
            var strLabel = this.STR_SPIN_LABEL;
            if (this._titlebar && this._titlebar.firstChild) {
                tbar = this._titlebar.firstChild;
            }
            
            var _intUp = function(ev) {
                YAHOO.util.Event.stopEvent(ev);
                if (!_button.get('disabled') && (ev.keyCode != 9)) {
                    var value = parseInt(_button.get('label'), 10);
                    value++;
                    value = cleanVal(value);
                    _button.set('label', ''+value);
                    var label = YAHOO.lang.substitute(strLabel, { VALUE: _button.get('label') });
                    _button.set('title', label);
                    if (!br.webkit && tbar) {
                        //tbar.focus(); //We do this for accessibility, on the re-focus of the element, a screen reader will re-read the title that was just changed
                        //_button.focus();
                    }
                    self._buttonClick(ev, oButton);
                }
            };

            var _intDown = function(ev) {
                YAHOO.util.Event.stopEvent(ev);
                if (!_button.get('disabled') && (ev.keyCode != 9)) {
                    var value = parseInt(_button.get('label'), 10);
                    value--;
                    value = cleanVal(value);

                    _button.set('label', ''+value);
                    var label = YAHOO.lang.substitute(strLabel, { VALUE: _button.get('label') });
                    _button.set('title', label);
                    if (!br.webkit && tbar) {
                        //tbar.focus(); //We do this for accessibility, on the re-focus of the element, a screen reader will re-read the title that was just changed
                        //_button.focus();
                    }
                    self._buttonClick(ev, oButton);
                }
            };

            var _intKeyUp = function(ev) {
                if (ev.keyCode == 38) {
                    _intUp(ev);
                } else if (ev.keyCode == 40) {
                    _intDown(ev);
                } else if (ev.keyCode == 107 && ev.shiftKey) {  //Plus Key
                    _intUp(ev);
                } else if (ev.keyCode == 109 && ev.shiftKey) {  //Minus Key
                    _intDown(ev);
                }
            };

            //Handle arrow keys..
            _button.on('keydown', _intKeyUp, this, true);

            //Listen for the click on the up button and act on it
            //Listen for the click on the down button and act on it
            Event.on(_b1, 'mousedown',function(ev) {
                Event.stopEvent(ev);
            }, this, true);
            Event.on(_b2, 'mousedown', function(ev) {
                Event.stopEvent(ev);
            }, this, true);
            Event.on(_b1, 'click', _intUp, this, true);
            Event.on(_b2, 'click', _intDown, this, true);
        },
        /**
        * @protected
        * @method _buttonClick
        * @description Click handler for all buttons in the toolbar.
        * @param {String} ev The event that was passed in.
        * @param {Object} info Object literal of information about the button that was clicked.
        */
        _buttonClick: function(ev, info) {
            var doEvent = true;
            
            if (ev && ev.type == 'keypress') {
                if (ev.keyCode == 9) {
                    doEvent = false;
                } else if ((ev.keyCode === 13) || (ev.keyCode === 0) || (ev.keyCode === 32)) {
                } else {
                    doEvent = false;
                }
            }

            if (doEvent) {
                var fireNextEvent = true,
                    retValue = false;
                    
                info.isSelected = this.isSelected(info.id);

                if (info.value) {
                    YAHOO.log('fireEvent::' + info.value + 'Click', 'info', 'Toolbar');
                    retValue = this.fireEvent(info.value + 'Click', { type: info.value + 'Click', target: this.get('element'), button: info });
                    if (retValue === false) {
                        fireNextEvent = false;
                    }
                }
                
                if (info.menucmd && fireNextEvent) {
                    YAHOO.log('fireEvent::' + info.menucmd + 'Click', 'info', 'Toolbar');
                    retValue = this.fireEvent(info.menucmd + 'Click', { type: info.menucmd + 'Click', target: this.get('element'), button: info });
                    if (retValue === false) {
                        fireNextEvent = false;
                    }
                }
                if (fireNextEvent) {
                    YAHOO.log('fireEvent::buttonClick', 'info', 'Toolbar');
                    this.fireEvent('buttonClick', { type: 'buttonClick', target: this.get('element'), button: info });
                }

                if (info.type == 'select') {
                    var button = this.getButtonById(info.id);
                    if (button.buttonType == 'rich') {
                        var txt = info.value;
                        for (var i = 0; i < info.menu.length; i++) {
                            if (info.menu[i].value == info.value) {
                                txt = info.menu[i].text;
                                break;
                            }
                        }
                        button.set('label', '<span class="yui-toolbar-' + info.menucmd + '-' + (info.value).replace(/ /g, '-').toLowerCase() + '">' + txt + '</span>');
                        var _items = button.getMenu().getItems();
                        for (var m = 0; m < _items.length; m++) {
                            if (_items[m].value.toLowerCase() == info.value.toLowerCase()) {
                                _items[m].cfg.setProperty('checked', true);
                            } else {
                                _items[m].cfg.setProperty('checked', false);
                            }
                        }
                    }
                }
                if (ev) {
                    Event.stopEvent(ev);
                }
            }
        },
        /**
        * @private
        * @property _keyNav
        * @description Flag to determine if the arrow nav listeners have been attached
        * @type Boolean
        */
        _keyNav: null,
        /**
        * @private
        * @property _navCounter
        * @description Internal counter for walking the buttons in the toolbar with the arrow keys
        * @type Number
        */
        _navCounter: null,
        /**
        * @private
        * @method _navigateButtons
        * @description Handles the navigation/focus of toolbar buttons with the Arrow Keys
        * @param {Event} ev The Key Event
        */
        _navigateButtons: function(ev) {
            switch (ev.keyCode) {
                case 37:
                case 39:
                    if (ev.keyCode == 37) {
                        this._navCounter--;
                    } else {
                        this._navCounter++;
                    }
                    if (this._navCounter > (this._buttonList.length - 1)) {
                        this._navCounter = 0;
                    }
                    if (this._navCounter < 0) {
                        this._navCounter = (this._buttonList.length - 1);
                    }
                    if (this._buttonList[this._navCounter]) {
                        var el = this._buttonList[this._navCounter].get('element');
                        if (this.browser.ie) {
                            el = this._buttonList[this._navCounter].get('element').getElementsByTagName('a')[0];
                        }
                        if (this._buttonList[this._navCounter].get('disabled')) {
                            this._navigateButtons(ev);
                        } else {
                            el.focus();
                        }
                    }
                    break;
            }
        },
        /**
        * @private
        * @method _handleFocus
        * @description Sets up the listeners for the arrow key navigation
        */
        _handleFocus: function() {
            if (!this._keyNav) {
                var ev = 'keypress';
                if (this.browser.ie) {
                    ev = 'keydown';
                }
                Event.on(this.get('element'), ev, this._navigateButtons, this, true);
                this._keyNav = true;
                this._navCounter = -1;
            }
        },
        /**
        * @method getButtonById
        * @description Gets a button instance from the toolbar by is Dom id.
        * @param {String} id The Dom id to query for.
        * @return {<a href="YAHOO.widget.ToolbarButton.html">YAHOO.widget.ToolbarButton</a>}
        */
        getButtonById: function(id) {
            var len = this._buttonList.length;
            for (var i = 0; i < len; i++) {
                if (this._buttonList[i] && this._buttonList[i].get('id') == id) {
                    return this._buttonList[i];
                }
            }
            return false;
        },
        /**
        * @method getButtonByValue
        * @description Gets a button instance or a menuitem instance from the toolbar by it's value.
        * @param {String} value The button value to query for.
        * @return {<a href="YAHOO.widget.ToolbarButton.html">YAHOO.widget.ToolbarButton</a> or <a href="YAHOO.widget.MenuItem.html">YAHOO.widget.MenuItem</a>}
        */
        getButtonByValue: function(value) {
            var _buttons = this.get('buttons');
            if (!_buttons) {
                return false;
            }
            var len = _buttons.length;
            for (var i = 0; i < len; i++) {
                if (_buttons[i].group !== undefined) {
                    for (var m = 0; m < _buttons[i].buttons.length; m++) {
                        if ((_buttons[i].buttons[m].value == value) || (_buttons[i].buttons[m].menucmd == value)) {
                            return this.getButtonById(_buttons[i].buttons[m].id);
                        }
                        if (_buttons[i].buttons[m].menu) { //Menu Button, loop through the values
                            for (var s = 0; s < _buttons[i].buttons[m].menu.length; s++) {
                                if (_buttons[i].buttons[m].menu[s].value == value) {
                                    return this.getButtonById(_buttons[i].buttons[m].id);
                                }
                            }
                        }
                    }
                } else {
                    if ((_buttons[i].value == value) || (_buttons[i].menucmd == value)) {
                        return this.getButtonById(_buttons[i].id);
                    }
                    if (_buttons[i].menu) { //Menu Button, loop through the values
                        for (var j = 0; j < _buttons[i].menu.length; j++) {
                            if (_buttons[i].menu[j].value == value) {
                                return this.getButtonById(_buttons[i].id);
                            }
                        }
                    }
                }
            }
            return false;
        },
        /**
        * @method getButtonByIndex
        * @description Gets a button instance from the toolbar by is index in _buttonList.
        * @param {Number} index The index of the button in _buttonList.
        * @return {<a href="YAHOO.widget.ToolbarButton.html">YAHOO.widget.ToolbarButton</a>}
        */
        getButtonByIndex: function(index) {
            if (this._buttonList[index]) {
                return this._buttonList[index];
            } else {
                return false;
            }
        },
        /**
        * @method getButtons
        * @description Returns an array of buttons in the current toolbar
        * @return {Array}
        */
        getButtons: function() {
            return this._buttonList;
        },
        /**
        * @method disableButton
        * @description Disables a button in the toolbar.
        * @param {String/Number} id Disable a button by it's id, index or value.
        * @return {Boolean}
        */
        disableButton: function(id) {
            var button = getButton.call(this, id);
            if (button) {
                button.set('disabled', true);
            } else {
                return false;
            }
        },
        /**
        * @method enableButton
        * @description Enables a button in the toolbar.
        * @param {String/Number} id Enable a button by it's id, index or value.
        * @return {Boolean}
        */
        enableButton: function(id) {
            if (this.get('disabled')) {
                return false;
            }
            var button = getButton.call(this, id);
            if (button) {
                if (button.get('disabled')) {
                    button.set('disabled', false);
                }
            } else {
                return false;
            }
        },
        /**
        * @method isSelected
        * @description Tells if a button is selected or not.
        * @param {String/Number} id A button by it's id, index or value.
        * @return {Boolean}
        */
        isSelected: function(id) {
            var button = getButton.call(this, id);
            if (button) {
                return button._selected;
            }
            return false;
        },
        /**
        * @method selectButton
        * @description Selects a button in the toolbar.
        * @param {String/Number} id Select a button by it's id, index or value.
        * @param {String} value If this is a Menu Button, check this item in the menu
        * @return {Boolean}
        */
        selectButton: function(id, value) {
            var button = getButton.call(this, id);
            if (button) {
                button.addClass('yui-button-selected');
                button.addClass('yui-button-' + button.get('value') + '-selected');
                button._selected = true;
                if (value) {
                    if (button.buttonType == 'rich') {
                        var _items = button.getMenu().getItems();
                        for (var m = 0; m < _items.length; m++) {
                            if (_items[m].value == value) {
                                _items[m].cfg.setProperty('checked', true);
                                button.set('label', '<span class="yui-toolbar-' + button.get('value') + '-' + (value).replace(/ /g, '-').toLowerCase() + '">' + _items[m]._oText.nodeValue + '</span>');
                            } else {
                                _items[m].cfg.setProperty('checked', false);
                            }
                        }
                    }
                }
            } else {
                return false;
            }
        },
        /**
        * @method deselectButton
        * @description Deselects a button in the toolbar.
        * @param {String/Number} id Deselect a button by it's id, index or value.
        * @return {Boolean}
        */
        deselectButton: function(id) {
            var button = getButton.call(this, id);
            if (button) {
                button.removeClass('yui-button-selected');
                button.removeClass('yui-button-' + button.get('value') + '-selected');
                button.removeClass('yui-button-hover');
                button._selected = false;
            } else {
                return false;
            }
        },
        /**
        * @method deselectAllButtons
        * @description Deselects all buttons in the toolbar.
        * @return {Boolean}
        */
        deselectAllButtons: function() {
            var len = this._buttonList.length;
            for (var i = 0; i < len; i++) {
                this.deselectButton(this._buttonList[i]);
            }
        },
        /**
        * @method disableAllButtons
        * @description Disables all buttons in the toolbar.
        * @return {Boolean}
        */
        disableAllButtons: function() {
            if (this.get('disabled')) {
                return false;
            }
            var len = this._buttonList.length;
            for (var i = 0; i < len; i++) {
                this.disableButton(this._buttonList[i]);
            }
        },
        /**
        * @method enableAllButtons
        * @description Enables all buttons in the toolbar.
        * @return {Boolean}
        */
        enableAllButtons: function() {
            if (this.get('disabled')) {
                return false;
            }
            var len = this._buttonList.length;
            for (var i = 0; i < len; i++) {
                this.enableButton(this._buttonList[i]);
            }
        },
        /**
        * @method resetAllButtons
        * @description Resets all buttons to their initial state.
        * @param {Object} _ex Except these buttons
        * @return {Boolean}
        */
        resetAllButtons: function(_ex) {
            if (!Lang.isObject(_ex)) {
                _ex = {};
            }
            if (this.get('disabled') || !this._buttonList) {
                return false;
            }
            var len = this._buttonList.length;
            for (var i = 0; i < len; i++) {
                var _button = this._buttonList[i];
                if (_button) {
                    var disabled = _button._configs.disabled._initialConfig.value;
                    if (_ex[_button.get('id')]) {
                        this.enableButton(_button);
                        this.selectButton(_button);
                    } else {
                        if (disabled) {
                            this.disableButton(_button);
                        } else {
                            this.enableButton(_button);
                        }
                        this.deselectButton(_button);
                    }
                }
            }
        },
        /**
        * @method destroyButton
        * @description Destroy a button in the toolbar.
        * @param {String/Number} id Destroy a button by it's id or index.
        * @return {Boolean}
        */
        destroyButton: function(id) {
            var button = getButton.call(this, id);
            if (button) {
                var thisID = button.get('id'),
                    new_list = [], i = 0,
                    len = this._buttonList.length;

                button.destroy();
                
                for (i = 0; i < len; i++) {
                    if (this._buttonList[i].get('id') != thisID) {
                        new_list[new_list.length]= this._buttonList[i];
                    }
                }

                this._buttonList = new_list;
            } else {
                return false;
            }
        },
        /**
        * @method destroy
        * @description Destroys the toolbar, all of it's elements and objects.
        * @return {Boolean}
        */
        destroy: function() {
            var len = this._configuredButtons.length, j, i, b;
            for(b = 0; b < len; b++) {
                this.destroyButton(this._configuredButtons[b]);
            }

            this._configuredButtons = null;
        
            this.get('element').innerHTML = '';
            this.get('element').className = '';
            //Brutal Object Destroy
            for (i in this) {
                if (Lang.hasOwnProperty(this, i)) {
                    this[i] = null;
                }
            }
            return true;
        },
        /**
        * @method collapse
        * @description Programatically collapse the toolbar.
        * @param {Boolean} collapse True to collapse, false to expand.
        */
        collapse: function(collapse) {
            var el = Dom.getElementsByClassName('collapse', 'span', this._titlebar);
            if (collapse === false) {
                Dom.removeClass(this.get('cont').parentNode, 'yui-toolbar-container-collapsed');
                if (el[0]) {
                    Dom.removeClass(el[0], 'collapsed');
                    el[0].title = this.STR_COLLAPSE;
                }
                this.fireEvent('toolbarExpanded', { type: 'toolbarExpanded', target: this });
            } else {
                if (el[0]) {
                    Dom.addClass(el[0], 'collapsed');
                    el[0].title = this.STR_EXPAND;
                }
                Dom.addClass(this.get('cont').parentNode, 'yui-toolbar-container-collapsed');
                this.fireEvent('toolbarCollapsed', { type: 'toolbarCollapsed', target: this });
            }
        },
        /**
        * @method toString
        * @description Returns a string representing the toolbar.
        * @return {String}
        */
        toString: function() {
            return 'Toolbar (#' + this.get('element').id + ') with ' + this._buttonList.length + ' buttons.';
        }
    });
/**
* @event buttonClick
* @param {Object} o The object passed to this handler is the button config used to create the button.
* @description Fires when any botton receives a click event. Passes back a single object representing the buttons config object. See <a href="YAHOO.util.Element.html#addListener">Element.addListener</a> for more information on listening for this event.
* @type YAHOO.util.CustomEvent
*/
/**
* @event valueClick
* @param {Object} o The object passed to this handler is the button config used to create the button.
* @description This is a special dynamic event that is created and dispatched based on the value property
* of the button config. See <a href="YAHOO.util.Element.html#addListener">Element.addListener</a> for more information on listening for this event.
* Example:
* <code><pre>
* buttons : [
*   { type: 'button', value: 'test', value: 'testButton' }
* ]</pre>
* </code>
* With the valueClick event you could subscribe to this buttons click event with this:
* tbar.in('testButtonClick', function() { alert('test button clicked'); })
* @type YAHOO.util.CustomEvent
*/
/**
* @event toolbarExpanded
* @description Fires when the toolbar is expanded via the collapse button. See <a href="YAHOO.util.Element.html#addListener">Element.addListener</a> for more information on listening for this event.
* @type YAHOO.util.CustomEvent
*/
/**
* @event toolbarCollapsed
* @description Fires when the toolbar is collapsed via the collapse button. See <a href="YAHOO.util.Element.html#addListener">Element.addListener</a> for more information on listening for this event.
* @type YAHOO.util.CustomEvent
*/
})();
