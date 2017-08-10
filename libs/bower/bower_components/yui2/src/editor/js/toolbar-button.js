(function() {
var Dom = YAHOO.util.Dom,
    Event = YAHOO.util.Event,
    Lang = YAHOO.lang;
    /**
     * @module editor    
     * @description <p>Creates a rich custom Toolbar Button. Primarily used with the Rich Text Editor's Toolbar</p>
     * @class ToolbarButtonAdvanced
     * @namespace YAHOO.widget
     * @requires yahoo, dom, element, event, container_core, menu, button
     * 
     * Provides a toolbar button based on the button and menu widgets.
     * @constructor
     * @class ToolbarButtonAdvanced
     * @param {String/HTMLElement} el The element to turn into a button.
     * @param {Object} attrs Object liternal containing configuration parameters.
    */
    if (YAHOO.widget.Button) {
        YAHOO.widget.ToolbarButtonAdvanced = YAHOO.widget.Button;
        /**
        * @property buttonType
        * @private
        * @description Tells if the Button is a Rich Button or a Simple Button
        */
        YAHOO.widget.ToolbarButtonAdvanced.prototype.buttonType = 'rich';
        /**
        * @method checkValue
        * @param {String} value The value of the option that we want to mark as selected
        * @description Select an option by value
        */
        YAHOO.widget.ToolbarButtonAdvanced.prototype.checkValue = function(value) {
            var _menuItems = this.getMenu().getItems();
            if (_menuItems.length === 0) {
                this.getMenu()._onBeforeShow();
                _menuItems = this.getMenu().getItems();
            }
            for (var i = 0; i < _menuItems.length; i++) {
                _menuItems[i].cfg.setProperty('checked', false);
                if (_menuItems[i].value == value) {
                    _menuItems[i].cfg.setProperty('checked', true);
                }
            }      
        };
    } else {
        YAHOO.widget.ToolbarButtonAdvanced = function() {};
    }


    /**
     * @description <p>Creates a basic custom Toolbar Button. Primarily used with the Rich Text Editor's Toolbar</p><p>Provides a toolbar button based on the button and menu widgets, &lt;select&gt; elements are used in place of menu's.</p>
     * @class ToolbarButton
     * @namespace YAHOO.widget
     * @requires yahoo, dom, element, event
     * @extends YAHOO.util.Element
     * 
     * 
     * @constructor
     * @param {String/HTMLElement} el The element to turn into a button.
     * @param {Object} attrs Object liternal containing configuration parameters.
    */

    YAHOO.widget.ToolbarButton = function(el, attrs) {
        YAHOO.log('ToolbarButton Initalizing', 'info', 'ToolbarButton');
        YAHOO.log(arguments.length + ' arguments passed to constructor', 'info', 'Toolbar');
        
        if (Lang.isObject(arguments[0]) && !Dom.get(el).nodeType) {
            attrs = el;
        }
        var local_attrs = (attrs || {});

        var oConfig = {
            element: null,
            attributes: local_attrs
        };

        if (!oConfig.attributes.type) {
            oConfig.attributes.type = 'push';
        }
        
        oConfig.element = document.createElement('span');
        oConfig.element.setAttribute('unselectable', 'on');
        oConfig.element.className = 'yui-button yui-' + oConfig.attributes.type + '-button';
        oConfig.element.innerHTML = '<span class="first-child"><a href="#">LABEL</a></span>';
        oConfig.element.firstChild.firstChild.tabIndex = '-1';
        oConfig.attributes.id = (oConfig.attributes.id || Dom.generateId());
        oConfig.element.id = oConfig.attributes.id;

        YAHOO.widget.ToolbarButton.superclass.constructor.call(this, oConfig.element, oConfig.attributes);
    };

    YAHOO.extend(YAHOO.widget.ToolbarButton, YAHOO.util.Element, {
        /**
        * @property buttonType
        * @private
        * @description Tells if the Button is a Rich Button or a Simple Button
        */
        buttonType: 'normal',
        /**
        * @method _handleMouseOver
        * @private
        * @description Adds classes to the button elements on mouseover (hover)
        */
        _handleMouseOver: function() {
            if (!this.get('disabled')) {
                this.addClass('yui-button-hover');
                this.addClass('yui-' + this.get('type') + '-button-hover');
            }
        },
        /**
        * @method _handleMouseOut
        * @private
        * @description Removes classes from the button elements on mouseout (hover)
        */
        _handleMouseOut: function() {
            this.removeClass('yui-button-hover');
            this.removeClass('yui-' + this.get('type') + '-button-hover');
        },
        /**
        * @method checkValue
        * @param {String} value The value of the option that we want to mark as selected
        * @description Select an option by value
        */
        checkValue: function(value) {
            if (this.get('type') == 'menu') {
                var opts = this._button.options;
                if (opts) {
                    for (var i = 0; i < opts.length; i++) {
                        if (opts[i].value == value) {
                            opts.selectedIndex = i;
                        }
                    }
                }
            }
        },
        /** 
        * @method init
        * @description The ToolbarButton class's initialization method
        */        
        init: function(p_oElement, p_oAttributes) {
            YAHOO.widget.ToolbarButton.superclass.init.call(this, p_oElement, p_oAttributes);

            this.on('mouseover', this._handleMouseOver, this, true);
            this.on('mouseout', this._handleMouseOut, this, true);
            this.on('click', function(ev) {
                Event.stopEvent(ev);
                return false;
            }, this, true);
        },
        /**
        * @method initAttributes
        * @description Initializes all of the configuration attributes used to create 
        * the toolbar.
        * @param {Object} attr Object literal specifying a set of 
        * configuration attributes used to create the toolbar.
        */        
        initAttributes: function(attr) {
            YAHOO.widget.ToolbarButton.superclass.initAttributes.call(this, attr);
            /**
            * @attribute value
            * @description The value of the button
            * @type String
            */            
            this.setAttributeConfig('value', {
                value: attr.value
            });
            /**
            * @attribute menu
            * @description The menu attribute, see YAHOO.widget.Button
            * @type Object
            */            
            this.setAttributeConfig('menu', {
                value: attr.menu || false
            });
            /**
            * @attribute type
            * @description The type of button to create: push, menu, color, select, spin
            * @type String
            */            
            this.setAttributeConfig('type', {
                value: attr.type,
                writeOnce: true,
                method: function(type) {
                    var el, opt;
                    if (!this._button) {
                        this._button = this.get('element').getElementsByTagName('a')[0];
                    }
                    switch (type) {
                        case 'select':
                        case 'menu':
                            el = document.createElement('select');
                            el.id = this.get('id');
                            var menu = this.get('menu');
                            for (var i = 0; i < menu.length; i++) {
                                opt = document.createElement('option');
                                opt.innerHTML = menu[i].text;
                                opt.value = menu[i].value;
                                if (menu[i].checked) {
                                    opt.selected = true;
                                }
                                el.appendChild(opt);
                            }
                            this._button.parentNode.replaceChild(el, this._button);
                            Event.on(el, 'change', this._handleSelect, this, true);
                            this._button = el;
                            break;
                    }
                }
            });

            /**
            * @attribute disabled
            * @description Set the button into a disabled state
            * @type String
            */            
            this.setAttributeConfig('disabled', {
                value: attr.disabled || false,
                method: function(disabled) {
                    if (disabled) {
                        this.addClass('yui-button-disabled');
                        this.addClass('yui-' + this.get('type') + '-button-disabled');
                    } else {
                        this.removeClass('yui-button-disabled');
                        this.removeClass('yui-' + this.get('type') + '-button-disabled');
                    }
                    if ((this.get('type') == 'menu') || (this.get('type') == 'select')) {
                        this._button.disabled = disabled;
                    }
                }
            });

            /**
            * @attribute label
            * @description The text label for the button
            * @type String
            */            
            this.setAttributeConfig('label', {
                value: attr.label,
                method: function(label) {
                    if (!this._button) {
                        this._button = this.get('element').getElementsByTagName('a')[0];
                    }
                    if (this.get('type') == 'push') {
                        this._button.innerHTML = label;
                    }
                }
            });

            /**
            * @attribute title
            * @description The title of the button
            * @type String
            */            
            this.setAttributeConfig('title', {
                value: attr.title
            });

            /**
            * @config container
            * @description The container that the button is rendered to, handled by Toolbar
            * @type String
            */            
            this.setAttributeConfig('container', {
                value: null,
                writeOnce: true,
                method: function(cont) {
                    this.appendTo(cont);
                }
            });

        },
        /** 
        * @private
        * @method _handleSelect
        * @description The event fired when a change event gets fired on a select element
        * @param {Event} ev The change event.
        */        
        _handleSelect: function(ev) {
            var tar = Event.getTarget(ev);
            var value = tar.options[tar.selectedIndex].value;
            this.fireEvent('change', {type: 'change', value: value });
        },
        /** 
        * @method getMenu
        * @description A stub function to mimic YAHOO.widget.Button's getMenu method
        */        
        getMenu: function() {
            return this.get('menu');
        },
        /** 
        * @method destroy
        * @description Destroy the button
        */        
        destroy: function() {
            Event.purgeElement(this.get('element'), true);
            this.get('element').parentNode.removeChild(this.get('element'));
            //Brutal Object Destroy
            for (var i in this) {
                if (Lang.hasOwnProperty(this, i)) {
                    this[i] = null;
                }
            }       
        },
        /** 
        * @method fireEvent
        * @description Overridden fireEvent method to prevent DOM events from firing if the button is disabled.
        */        
        fireEvent: function(p_sType, p_aArgs) {
            //  Disabled buttons should not respond to DOM events
            if (this.DOM_EVENTS[p_sType] && this.get('disabled')) {
                Event.stopEvent(p_aArgs);
                return;
            }
        
            YAHOO.widget.ToolbarButton.superclass.fireEvent.call(this, p_sType, p_aArgs);
        },
        /**
        * @method toString
        * @description Returns a string representing the toolbar.
        * @return {String}
        */        
        toString: function() {
            return 'ToolbarButton (' + this.get('id') + ')';
        }
        
    });
})();
