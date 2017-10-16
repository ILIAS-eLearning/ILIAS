(function() {
var Dom = YAHOO.util.Dom,
    Event = YAHOO.util.Event,
    Lang = YAHOO.lang;

YAHOO.widget.Toolbar.prototype.addButton = function(oButton, after) {
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
                /* REMOVED FOR SAFARI */
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
};



})();
