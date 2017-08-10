(function () {
    var Dom = YAHOO.util.Dom,
        Event = YAHOO.util.Event,
        Lang = YAHOO.lang,
        Widget = YAHOO.widget;



/**
 * The treeview widget is a generic tree building tool.
 * @module treeview
 * @title TreeView Widget
 * @requires yahoo, dom, event
 * @optional animation, json, calendar
 * @namespace YAHOO.widget
 */

/**
 * Contains the tree view state data and the root node.
 *
 * @class TreeView
 * @uses YAHOO.util.EventProvider
 * @constructor
 * @param {string|HTMLElement} id The id of the element, or the element itself that the tree will be inserted into.
 *        Existing markup in this element, if valid, will be used to build the tree
 * @param {Array|Object|String}  oConfig (optional)  If present, it will be used to build the tree via method <a href="#method_buildTreeFromObject">buildTreeFromObject</a>
 *
 */
YAHOO.widget.TreeView = function(id, oConfig) {
    if (id) { this.init(id); }
    if (oConfig) {
        this.buildTreeFromObject(oConfig);
    } else if (Lang.trim(this._el.innerHTML)) {
        this.buildTreeFromMarkup(id);
    }
};

var TV = Widget.TreeView;

TV.prototype = {

    /**
     * The id of tree container element
     * @property id
     * @type String
     */
    id: null,

    /**
     * The host element for this tree
     * @property _el
     * @private
     * @type HTMLelement
     */
    _el: null,

     /**
     * Flat collection of all nodes in this tree.  This is a sparse
     * array, so the length property can't be relied upon for a
     * node count for the tree.
     * @property _nodes
     * @type Node[]
     * @private
     */
    _nodes: null,

    /**
     * We lock the tree control while waiting for the dynamic loader to return
     * @property locked
     * @type boolean
     */
    locked: false,

    /**
     * The animation to use for expanding children, if any
     * @property _expandAnim
     * @type string
     * @private
     */
    _expandAnim: null,

    /**
     * The animation to use for collapsing children, if any
     * @property _collapseAnim
     * @type string
     * @private
     */
    _collapseAnim: null,

    /**
     * The current number of animations that are executing
     * @property _animCount
     * @type int
     * @private
     */
    _animCount: 0,

    /**
     * The maximum number of animations to run at one time.
     * @property maxAnim
     * @type int
     */
    maxAnim: 2,

    /**
     * Whether there is any subscriber to dblClickEvent
     * @property _hasDblClickSubscriber
     * @type boolean
     * @private
     */
    _hasDblClickSubscriber: false,

    /**
     * Stores the timer used to check for double clicks
     * @property _dblClickTimer
     * @type window.timer object
     * @private
     */
    _dblClickTimer: null,

  /**
     * A reference to the Node currently having the focus or null if none.
     * @property currentFocus
     * @type YAHOO.widget.Node
     */
    currentFocus: null,

    /**
    * If true, only one Node can be highlighted at a time
    * @property singleNodeHighlight
    * @type boolean
    * @default false
    */

    singleNodeHighlight: false,

    /**
    * A reference to the Node that is currently highlighted.
    * It is only meaningful if singleNodeHighlight is enabled
    * @property _currentlyHighlighted
    * @type YAHOO.widget.Node
    * @default null
    * @private
    */

    _currentlyHighlighted: null,

    /**
     * Sets up the animation for expanding children
     * @method setExpandAnim
     * @param {string} type the type of animation (acceptable values defined
     * in YAHOO.widget.TVAnim)
     */
    setExpandAnim: function(type) {
        this._expandAnim = (Widget.TVAnim.isValid(type)) ? type : null;
    },

    /**
     * Sets up the animation for collapsing children
     * @method setCollapseAnim
     * @param {string} type of animation (acceptable values defined in
     * YAHOO.widget.TVAnim)
     */
    setCollapseAnim: function(type) {
        this._collapseAnim = (Widget.TVAnim.isValid(type)) ? type : null;
    },

    /**
     * Perform the expand animation if configured, or just show the
     * element if not configured or too many animations are in progress
     * @method animateExpand
     * @param el {HTMLElement} the element to animate
     * @param node {YAHOO.util.Node} the node that was expanded
     * @return {boolean} true if animation could be invoked, false otherwise
     */
    animateExpand: function(el, node) {
        this.logger.log("animating expand");

        if (this._expandAnim && this._animCount < this.maxAnim) {
            // this.locked = true;
            var tree = this;
            var a = Widget.TVAnim.getAnim(this._expandAnim, el,
                            function() { tree.expandComplete(node); });
            if (a) {
                ++this._animCount;
                this.fireEvent("animStart", {
                        "node": node,
                        "type": "expand"
                    });
                a.animate();
            }

            return true;
        }

        return false;
    },

    /**
     * Perform the collapse animation if configured, or just show the
     * element if not configured or too many animations are in progress
     * @method animateCollapse
     * @param el {HTMLElement} the element to animate
     * @param node {YAHOO.util.Node} the node that was expanded
     * @return {boolean} true if animation could be invoked, false otherwise
     */
    animateCollapse: function(el, node) {
        this.logger.log("animating collapse");

        if (this._collapseAnim && this._animCount < this.maxAnim) {
            // this.locked = true;
            var tree = this;
            var a = Widget.TVAnim.getAnim(this._collapseAnim, el,
                            function() { tree.collapseComplete(node); });
            if (a) {
                ++this._animCount;
                this.fireEvent("animStart", {
                        "node": node,
                        "type": "collapse"
                    });
                a.animate();
            }

            return true;
        }

        return false;
    },

    /**
     * Function executed when the expand animation completes
     * @method expandComplete
     */
    expandComplete: function(node) {
        this.logger.log("expand complete: " + this.id);
        --this._animCount;
        this.fireEvent("animComplete", {
                "node": node,
                "type": "expand"
            });
        // this.locked = false;
    },

    /**
     * Function executed when the collapse animation completes
     * @method collapseComplete
     */
    collapseComplete: function(node) {
        this.logger.log("collapse complete: " + this.id);
        --this._animCount;
        this.fireEvent("animComplete", {
                "node": node,
                "type": "collapse"
            });
        // this.locked = false;
    },

    /**
     * Initializes the tree
     * @method init
     * @parm {string|HTMLElement} id the id of the element that will hold the tree
     * @private
     */
    init: function(id) {
        this._el = Dom.get(id);
        this.id = Dom.generateId(this._el,"yui-tv-auto-id-");

    /**
         * When animation is enabled, this event fires when the animation
         * starts
         * @event animStart
         * @type CustomEvent
         * @param {YAHOO.widget.Node} oArgs.node the node that is expanding/collapsing
         * @param {String} oArgs.type the type of animation ("expand" or "collapse")
         */
        this.createEvent("animStart", this);

        /**
         * When animation is enabled, this event fires when the animation
         * completes
         * @event animComplete
         * @type CustomEvent
         * @param {YAHOO.widget.Node} oArgs.node the node that is expanding/collapsing
         * @param {String} oArgs.type the type of animation ("expand" or "collapse")
         */
        this.createEvent("animComplete", this);

        /**
         * Fires when a node is going to be collapsed.  Return false to stop
         * the collapse.
         * @event collapse
         * @type CustomEvent
         * @param {YAHOO.widget.Node} node the node that is collapsing
         */
        this.createEvent("collapse", this);

        /**
         * Fires after a node is successfully collapsed.  This event will not fire
         * if the "collapse" event was cancelled.
         * @event collapseComplete
         * @type CustomEvent
         * @param {YAHOO.widget.Node} node the node that was collapsed
         */
        this.createEvent("collapseComplete", this);

        /**
         * Fires when a node is going to be expanded.  Return false to stop
         * the collapse.
         * @event expand
         * @type CustomEvent
         * @param {YAHOO.widget.Node} node the node that is expanding
         */
        this.createEvent("expand", this);

        /**
         * Fires after a node is successfully expanded.  This event will not fire
         * if the "expand" event was cancelled.
         * @event expandComplete
         * @type CustomEvent
         * @param {YAHOO.widget.Node} node the node that was expanded
         */
        this.createEvent("expandComplete", this);

    /**
         * Fires when the Enter key is pressed on a node that has the focus
         * @event enterKeyPressed
         * @type CustomEvent
         * @param {YAHOO.widget.Node} node the node that has the focus
         */
        this.createEvent("enterKeyPressed", this);

    /**
         * Fires when the label in a TextNode or MenuNode or content in an HTMLNode receives a Click.
    * The listener may return false to cancel toggling and focusing on the node.
         * @event clickEvent
         * @type CustomEvent
         * @param oArgs.event  {HTMLEvent} The event object
         * @param oArgs.node {YAHOO.widget.Node} node the node that was clicked
         */
        this.createEvent("clickEvent", this);

    /**
         * Fires when the focus receives the focus, when it changes from a Node
    * to another Node or when it is completely lost (blurred)
         * @event focusChanged
         * @type CustomEvent
         * @param oArgs.oldNode  {YAHOO.widget.Node} Node that had the focus or null if none
         * @param oArgs.newNode {YAHOO.widget.Node} Node that receives the focus or null if none
         */

        this.createEvent('focusChanged',this);

    /**
         * Fires when the label in a TextNode or MenuNode or content in an HTMLNode receives a double Click
         * @event dblClickEvent
         * @type CustomEvent
         * @param oArgs.event  {HTMLEvent} The event object
         * @param oArgs.node {YAHOO.widget.Node} node the node that was clicked
         */
        var self = this;
        this.createEvent("dblClickEvent", {
            scope:this,
            onSubscribeCallback: function() {
                self._hasDblClickSubscriber = true;
            }
        });

    /**
         * Custom event that is fired when the text node label is clicked.
         *  The node clicked is  provided as an argument
         *
         * @event labelClick
         * @type CustomEvent
         * @param {YAHOO.widget.Node} node the node clicked
    * @deprecated use clickEvent or dblClickEvent
         */
        this.createEvent("labelClick", this);

    /**
     * Custom event fired when the highlight of a node changes.
     * The node that triggered the change is provided as an argument:
     * The status of the highlight can be checked in
     * <a href="YAHOO.widget.Node.html#property_highlightState">nodeRef.highlightState</a>.
     * Depending on <a href="YAHOO.widget.Node.html#property_propagateHighlight">nodeRef.propagateHighlight</a>, other nodes might have changed
     * @event highlightEvent
     * @type CustomEvent
     * @param node {YAHOO.widget.Node} the node that started the change in highlighting state
    */
        this.createEvent("highlightEvent",this);


        this._nodes = [];

        // store a global reference
        TV.trees[this.id] = this;

        // Set up the root node
        this.root = new Widget.RootNode(this);

        var LW = Widget.LogWriter;

        this.logger = (LW) ? new LW(this.toString()) : YAHOO;

        this.logger.log("tree init: " + this.id);

        if (this._initEditor) {
            this._initEditor();
        }

        // YAHOO.util.Event.onContentReady(this.id, this.handleAvailable, this, true);
        // YAHOO.util.Event.on(this.id, "click", this.handleClick, this, true);
    },

    //handleAvailable: function() {
        //var Event = YAHOO.util.Event;
        //Event.on(this.id,
    //},
 /**
     * Builds the TreeView from an object.
     * This is the method called by the constructor to build the tree when it has a second argument.
     *  A tree can be described by an array of objects, each object corresponding to a node.
     *  Node descriptions may contain values for any property of a node plus the following extra properties: <ul>
     * <li>type:  can be one of the following:<ul>
     *    <li> A shortname for a node type (<code>'text','menu','html'</code>) </li>
     *    <li>The name of a Node class under YAHOO.widget (<code>'TextNode', 'MenuNode', 'DateNode'</code>, etc) </li>
     *    <li>a reference to an actual class: <code>YAHOO.widget.DateNode</code></li>
     * </ul></li>
     * <li>children: an array containing further node definitions</li></ul>
     * A string instead of an object will produce a node of type 'text' with the given string as its label.
     * @method buildTreeFromObject
     * @param  oConfig {Array|Object|String}  array containing a full description of the tree.
     *        An object or a string will be turned into an array with the given object or string as its only element.
     *
     */
    buildTreeFromObject: function (oConfig) {
        var logger = this.logger;
        logger.log('Building tree from object');
        var build = function (parent, oConfig) {
            var i, item, node, children, type, NodeType, ThisType;
            for (i = 0; i < oConfig.length; i++) {
                item = oConfig[i];
                if (Lang.isString(item)) {
                    node = new Widget.TextNode(item, parent);
                } else if (Lang.isObject(item)) {
                    children = item.children;
                    delete item.children;
                    type = item.type || 'text';
                    delete item.type;
                    switch (Lang.isString(type) && type.toLowerCase()) {
                        case 'text':
                            node = new Widget.TextNode(item, parent);
                            break;
                        case 'menu':
                            node = new Widget.MenuNode(item, parent);
                            break;
                        case 'html':
                            node = new Widget.HTMLNode(item, parent);
                            break;
                        default:
                            if (Lang.isString(type)) {
                                NodeType = Widget[type];
                            } else {
                                NodeType = type;
                            }
                            if (Lang.isObject(NodeType)) {
                                for (ThisType = NodeType; ThisType && ThisType !== Widget.Node; ThisType = ThisType.superclass.constructor) {}
                                if (ThisType) {
                                    node = new NodeType(item, parent);
                                } else {
                                    logger.log('Invalid type in node definition: ' + type,'error');
                                }
                            } else {
                                logger.log('Invalid type in node definition: ' + type,'error');
                            }
                    }
                    if (children) {
                        build(node,children);
                    }
                } else {
                    logger.log('Invalid node definition','error');
                }
            }
        };
        if (!Lang.isArray(oConfig)) {
            oConfig = [oConfig];
        }


        build(this.root,oConfig);
    },
/**
     * Builds the TreeView from existing markup.   Markup should consist of &lt;UL&gt; or &lt;OL&gt; elements containing &lt;LI&gt; elements.
     * Each &lt;LI&gt; can have one element used as label and a second optional element which is to be a &lt;UL&gt; or &lt;OL&gt;
     * containing nested nodes.
     * Depending on what the first element of the &lt;LI&gt; element is, the following Nodes will be created: <ul>
     *           <li>plain text:  a regular TextNode</li>
     *           <li>anchor &lt;A&gt;: a TextNode with its <code>href</code> and <code>target</code> taken from the anchor</li>
     *           <li>anything else: an HTMLNode</li></ul>
     * Only the first  outermost (un-)ordered list in the markup and its children will be parsed.
     * Nodes will be collapsed unless  an  &lt;LI&gt;  tag has a className called 'expanded'.
     * All other className attributes will be copied over to the Node className property.
     * If the &lt;LI&gt; element contains an attribute called <code>yuiConfig</code>, its contents should be a JSON-encoded object
     * as the one used in method <a href="#method_buildTreeFromObject">buildTreeFromObject</a>.
     * @method buildTreeFromMarkup
     * @param  id {string|HTMLElement} The id of the element that contains the markup or a reference to it.
     */
    buildTreeFromMarkup: function (id) {
        this.logger.log('Building tree from existing markup');
        var build = function (markup) {
            var el, child, branch = [], config = {}, label, yuiConfig;
            // Dom's getFirstChild and getNextSibling skip over text elements
            for (el = Dom.getFirstChild(markup); el; el = Dom.getNextSibling(el)) {
                switch (el.tagName.toUpperCase()) {
                    case 'LI':
                        label = '';
                        config = {
                            expanded: Dom.hasClass(el,'expanded'),
                            title: el.title || el.alt || null,
                            className: Lang.trim(el.className.replace(/\bexpanded\b/,'')) || null
                        };
                        // I cannot skip over text elements here because I want them for labels
                        child = el.firstChild;
                        if (child.nodeType == 3) {
                            // nodes with only whitespace, tabs and new lines don't count, they are probably just formatting.
                            label = Lang.trim(child.nodeValue.replace(/[\n\t\r]*/g,''));
                            if (label) {
                                config.type = 'text';
                                config.label = label;
                            } else {
                                child = Dom.getNextSibling(child);
                            }
                        }
                        if (!label) {
                            if (child.tagName.toUpperCase() == 'A') {
                                config.type = 'text';
                                config.label = child.innerHTML;
                                config.href = child.href;
                                config.target = child.target;
                                config.title = child.title || child.alt || config.title;
                            } else {
                                config.type = 'html';
                                var d = document.createElement('div');
                                d.appendChild(child.cloneNode(true));
                                config.html = d.innerHTML;
                                config.hasIcon = true;
                            }
                        }
                        // see if after the label it has a further list which will become children of this node.
                        child = Dom.getNextSibling(child);
                        switch (child && child.tagName.toUpperCase()) {
                            case 'UL':
                            case 'OL':
                                config.children = build(child);
                                break;
                        }
                        // if there are further elements or text, it will be ignored.

                        if (YAHOO.lang.JSON) {
                            yuiConfig = el.getAttribute('yuiConfig');
                            if (yuiConfig) {
                                yuiConfig = YAHOO.lang.JSON.parse(yuiConfig);
                                config = YAHOO.lang.merge(config,yuiConfig);
                            }
                        }

                        branch.push(config);
                        break;
                    case 'UL':
                    case 'OL':
                        this.logger.log('ULs or OLs can only contain LI elements, not other UL or OL.  This will not work in some browsers','error');
                        config = {
                            type: 'text',
                            label: '',
                            children: build(child)
                        };
                        branch.push(config);
                        break;
                }
            }
            return branch;
        };

        var markup = Dom.getChildrenBy(Dom.get(id),function (el) {
            var tag = el.tagName.toUpperCase();
            return  tag == 'UL' || tag == 'OL';
        });
        if (markup.length) {
            this.buildTreeFromObject(build(markup[0]));
        } else {
            this.logger.log('Markup contains no UL or OL elements','warn');
        }
    },
  /**
     * Returns the TD element where the event has occurred
     * @method _getEventTargetTdEl
     * @private
     */
    _getEventTargetTdEl: function (ev) {
        var target = Event.getTarget(ev);
        // go up looking for a TD with a className with a ygtv prefix
        while (target && !(target.tagName.toUpperCase() == 'TD' && Dom.hasClass(target.parentNode,'ygtvrow'))) {
            target = Dom.getAncestorByTagName(target,'td');
        }
        if (Lang.isNull(target)) { return null; }
        // If it is a spacer cell, do nothing
        if (/\bygtv(blank)?depthcell/.test(target.className)) { return null;}
        // If it has an id, search for the node number and see if it belongs to a node in this tree.
        if (target.id) {
            var m = target.id.match(/\bygtv([^\d]*)(.*)/);
            if (m && m[2] && this._nodes[m[2]]) {
                return target;
            }
        }
        return null;
    },
  /**
     * Event listener for click events
     * @method _onClickEvent
     * @private
     */
    _onClickEvent: function (ev) {
        var self = this,
            td = this._getEventTargetTdEl(ev),
            node,
            target,
            toggle = function (force) {
                node.focus();
                if (force || !node.href) {
                    node.toggle();
                    try {
                        Event.preventDefault(ev);
                    } catch (e) {
                        // @TODO
                        // For some reason IE8 is providing an event object with
                        // most of the fields missing, but only when clicking on
                        // the node's label, and only when working with inline
                        // editing.  This generates a "Member not found" error
                        // in that browser.  Determine if this is a browser
                        // bug, or a problem with this code.  Already checked to
                        // see if the problem has to do with access the event
                        // in the outer scope, and that isn't the problem.
                        // Maybe the markup for inline editing is broken.
                    }
                }
            };

        if (!td) {
            return;
        }

        node = this.getNodeByElement(td);
        if (!node) {
            return;
        }

        // exception to handle deprecated event labelClick
        // @TODO take another look at this deprecation.  It is common for people to
        // only be interested in the label click, so why make them have to test
        // the node type to figure out whether the click was on the label?
        target = Event.getTarget(ev);
        if (Dom.hasClass(target, node.labelStyle) || Dom.getAncestorByClassName(target,node.labelStyle)) {
            this.logger.log("onLabelClick " + node.label);
            this.fireEvent('labelClick',node);
        }
        // http://yuilibrary.com/projects/yui2/ticket/2528946
        // Ensures that any open editor is closed.
        // Since the editor is in a separate source which might not be included,
        // we first need to ensure we have the _closeEditor method available
        if (this._closeEditor) { this._closeEditor(false); }

        //  If it is a toggle cell, toggle
        if (/\bygtv[tl][mp]h?h?/.test(td.className)) {
            toggle(true);
        } else {
            if (this._dblClickTimer) {
                window.clearTimeout(this._dblClickTimer);
                this._dblClickTimer = null;
            } else {
                if (this._hasDblClickSubscriber) {
                    this._dblClickTimer = window.setTimeout(function () {
                        self._dblClickTimer = null;
                        if (self.fireEvent('clickEvent', {event:ev,node:node}) !== false) {
                            toggle();
                        }
                    }, 200);
                } else {
                    if (self.fireEvent('clickEvent', {event:ev,node:node}) !== false) {
                        toggle();
                    }
                }
            }
        }
    },

  /**
     * Event listener for double-click events
     * @method _onDblClickEvent
     * @private
     */
    _onDblClickEvent: function (ev) {
        if (!this._hasDblClickSubscriber) { return; }
        var td = this._getEventTargetTdEl(ev);
        if (!td) {return;}

        if (!(/\bygtv[tl][mp]h?h?/.test(td.className))) {
            this.fireEvent('dblClickEvent', {event:ev, node:this.getNodeByElement(td)});
            if (this._dblClickTimer) {
                window.clearTimeout(this._dblClickTimer);
                this._dblClickTimer = null;
            }
        }
    },
  /**
     * Event listener for mouse over events
     * @method _onMouseOverEvent
     * @private
     */
    _onMouseOverEvent:function (ev) {
        var target;
        if ((target = this._getEventTargetTdEl(ev)) && (target = this.getNodeByElement(target)) && (target = target.getToggleEl())) {
            target.className = target.className.replace(/\bygtv([lt])([mp])\b/gi,'ygtv$1$2h');
        }
    },
  /**
     * Event listener for mouse out events
     * @method _onMouseOutEvent
     * @private
     */
    _onMouseOutEvent: function (ev) {
        var target;
        if ((target = this._getEventTargetTdEl(ev)) && (target = this.getNodeByElement(target)) && (target = target.getToggleEl())) {
            target.className = target.className.replace(/\bygtv([lt])([mp])h\b/gi,'ygtv$1$2');
        }
    },
  /**
     * Event listener for key down events
     * @method _onKeyDownEvent
     * @private
     */
    _onKeyDownEvent: function (ev) {
        var target = Event.getTarget(ev),
            node = this.getNodeByElement(target),
            newNode = node,
            KEY = YAHOO.util.KeyListener.KEY;

        switch(ev.keyCode) {
            case KEY.UP:
                this.logger.log('UP');
                do {
                    if (newNode.previousSibling) {
                        newNode = newNode.previousSibling;
                    } else {
                        newNode = newNode.parent;
                    }
                } while (newNode && !newNode._canHaveFocus());
                if (newNode) { newNode.focus(); }
                Event.preventDefault(ev);
                break;
            case KEY.DOWN:
                this.logger.log('DOWN');
                do {
                    if (newNode.nextSibling) {
                        newNode = newNode.nextSibling;
                    } else {
                        newNode.expand();
                        newNode = (newNode.children.length || null) && newNode.children[0];
                    }
                } while (newNode && !newNode._canHaveFocus);
                if (newNode) { newNode.focus();}
                Event.preventDefault(ev);
                break;
            case KEY.LEFT:
                this.logger.log('LEFT');
                do {
                    if (newNode.parent) {
                        newNode = newNode.parent;
                    } else {
                        newNode = newNode.previousSibling;
                    }
                } while (newNode && !newNode._canHaveFocus());
                if (newNode) { newNode.focus();}
                Event.preventDefault(ev);
                break;
            case KEY.RIGHT:
                this.logger.log('RIGHT');
                var self = this,
                    moveFocusRight,
                    focusOnExpand = function (newNode) {
                        self.unsubscribe('expandComplete',focusOnExpand);
                        moveFocusRight(newNode);
                    };
                moveFocusRight = function (newNode) {
                    do {
                        if (newNode.isDynamic() && !newNode.childrenRendered) {
                            self.subscribe('expandComplete',focusOnExpand);
                            newNode.expand();
                            newNode = null;
                            break;
                        } else {
                            newNode.expand();
                            if (newNode.children.length) {
                                newNode = newNode.children[0];
                            } else {
                                newNode = newNode.nextSibling;
                            }
                        }
                    } while (newNode && !newNode._canHaveFocus());
                    if (newNode) { newNode.focus();}
                };

                moveFocusRight(newNode);
                Event.preventDefault(ev);
                break;
            case KEY.ENTER:
                this.logger.log('ENTER: ' + newNode.href);
                if (node.href) {
                    if (node.target) {
                        window.open(node.href,node.target);
                    } else {
                        window.location(node.href);
                    }
                } else {
                    node.toggle();
                }
                this.fireEvent('enterKeyPressed',node);
                Event.preventDefault(ev);
                break;
            case KEY.HOME:
                this.logger.log('HOME');
                newNode = this.getRoot();
                if (newNode.children.length) {newNode = newNode.children[0];}
                if (newNode._canHaveFocus()) { newNode.focus(); }
                Event.preventDefault(ev);
                break;
            case KEY.END:
                this.logger.log('END');
                newNode = newNode.parent.children;
                newNode = newNode[newNode.length -1];
                if (newNode._canHaveFocus()) { newNode.focus(); }
                Event.preventDefault(ev);
                break;
            // case KEY.PAGE_UP:
                // this.logger.log('PAGE_UP');
                // break;
            // case KEY.PAGE_DOWN:
                // this.logger.log('PAGE_DOWN');
                // break;
            case 107:  // plus key
            case 187:  // plus key
                if (ev.shiftKey) {
                    this.logger.log('Shift-PLUS');
                    node.parent.expandAll();
                } else {
                    this.logger.log('PLUS');
                    node.expand();
                }
                break;
            case 109: // minus key
            case 189: // minus key
                if (ev.shiftKey) {
                    this.logger.log('Shift-MINUS');
                    node.parent.collapseAll();
                } else {
                    this.logger.log('MINUS');
                    node.collapse();
                }
                break;
            default:
                break;
        }
    },
    /**
     * Renders the tree boilerplate and visible nodes
     * @method render
     */
    render: function() {
        var html = this.root.getHtml(),
            el = this.getEl();
        el.innerHTML = html;
        if (!this._hasEvents) {
            Event.on(el, 'click', this._onClickEvent, this, true);
            Event.on(el, 'dblclick', this._onDblClickEvent, this, true);
            Event.on(el, 'mouseover', this._onMouseOverEvent, this, true);
            Event.on(el, 'mouseout', this._onMouseOutEvent, this, true);
            Event.on(el, 'keydown', this._onKeyDownEvent, this, true);
        }
        this._hasEvents = true;
    },

  /**
     * Returns the tree's host element
     * @method getEl
     * @return {HTMLElement} the host element
     */
    getEl: function() {
        if (! this._el) {
            this._el = Dom.get(this.id);
        }
        return this._el;
    },

    /**
     * Nodes register themselves with the tree instance when they are created.
     * @method regNode
     * @param node {Node} the node to register
     * @private
     */
    regNode: function(node) {
        this._nodes[node.index] = node;
    },

    /**
     * Returns the root node of this tree
     * @method getRoot
     * @return {Node} the root node
     */
    getRoot: function() {
        return this.root;
    },

    /**
     * Configures this tree to dynamically load all child data
     * @method setDynamicLoad
     * @param {function} fnDataLoader the function that will be called to get the data
     * @param iconMode {int} configures the icon that is displayed when a dynamic
     * load node is expanded the first time without children.  By default, the
     * "collapse" icon will be used.  If set to 1, the leaf node icon will be
     * displayed.
     */
    setDynamicLoad: function(fnDataLoader, iconMode) {
        this.root.setDynamicLoad(fnDataLoader, iconMode);
    },

    /**
     * Expands all child nodes.  Note: this conflicts with the "multiExpand"
     * node property.  If expand all is called in a tree with nodes that
     * do not allow multiple siblings to be displayed, only the last sibling
     * will be expanded.
     * @method expandAll
     */
    expandAll: function() {
        if (!this.locked) {
            this.root.expandAll();
        }
    },

    /**
     * Collapses all expanded child nodes in the entire tree.
     * @method collapseAll
     */
    collapseAll: function() {
        if (!this.locked) {
            this.root.collapseAll();
        }
    },

    /**
     * Returns a node in the tree that has the specified index (this index
     * is created internally, so this function probably will only be used
     * in html generated for a given node.)
     * @method getNodeByIndex
     * @param {int} nodeIndex the index of the node wanted
     * @return {Node} the node with index=nodeIndex, null if no match
     */
    getNodeByIndex: function(nodeIndex) {
        var n = this._nodes[nodeIndex];
        return (n) ? n : null;
    },

    /**
     * Returns a node that has a matching property and value in the data
     * object that was passed into its constructor.
     * @method getNodeByProperty
     * @param {object} property the property to search (usually a string)
     * @param {object} value the value we want to find (usuall an int or string)
     * @return {Node} the matching node, null if no match
     */
    getNodeByProperty: function(property, value) {
        for (var i in this._nodes) {
            if (this._nodes.hasOwnProperty(i)) {
                var n = this._nodes[i];
                if ((property in n && n[property] == value) || (n.data && value == n.data[property])) {
                    return n;
                }
            }
        }

        return null;
    },

    /**
     * Returns a collection of nodes that have a matching property
     * and value in the data object that was passed into its constructor.
     * @method getNodesByProperty
     * @param {object} property the property to search (usually a string)
     * @param {object} value the value we want to find (usuall an int or string)
     * @return {Array} the matching collection of nodes, null if no match
     */
    getNodesByProperty: function(property, value) {
        var values = [];
        for (var i in this._nodes) {
            if (this._nodes.hasOwnProperty(i)) {
                var n = this._nodes[i];
                if ((property in n && n[property] == value) || (n.data && value == n.data[property])) {
                    values.push(n);
                }
            }
        }

        return (values.length) ? values : null;
    },


    /**
     * Returns a collection of nodes that have passed the test function
     * passed as its only argument.
     * The function will receive a reference to each node to be tested.
     * @method getNodesBy
     * @param {function} a boolean function that receives a Node instance and returns true to add the node to the results list
     * @return {Array} the matching collection of nodes, null if no match
     */
    getNodesBy: function(fn) {
        var values = [];
        for (var i in this._nodes) {
            if (this._nodes.hasOwnProperty(i)) {
                var n = this._nodes[i];
                if (fn(n)) {
                    values.push(n);
                }
            }
        }
        return (values.length) ? values : null;
    },
    /**
     * Returns the treeview node reference for an ancestor element
     * of the node, or null if it is not contained within any node
     * in this tree.
     * @method getNodeByElement
     * @param el {HTMLElement} the element to test
     * @return {YAHOO.widget.Node} a node reference or null
     */
    getNodeByElement: function(el) {

        var p=el, m, re=/ygtv([^\d]*)(.*)/;

        do {

            if (p && p.id) {
                m = p.id.match(re);
                if (m && m[2]) {
                    return this.getNodeByIndex(m[2]);
                }
            }

            p = p.parentNode;

            if (!p || !p.tagName) {
                break;
            }

        }
        while (p.id !== this.id && p.tagName.toLowerCase() !== "body");

        return null;
    },

    /**
     * When in singleNodeHighlight it returns the node highlighted
     * or null if none.  Returns null if singleNodeHighlight is false.
     * @method getHighlightedNode
     * @return {YAHOO.widget.Node} a node reference or null
     */
    getHighlightedNode: function() {
        return this._currentlyHighlighted;
    },


    /**
     * Removes the node and its children, and optionally refreshes the
     * branch of the tree that was affected.
     * @method removeNode
     * @param {Node} node to remove
     * @param {boolean} autoRefresh automatically refreshes branch if true
     * @return {boolean} False is there was a problem, true otherwise.
     */
    removeNode: function(node, autoRefresh) {

        // Don't delete the root node
        if (node.isRoot()) {
            return false;
        }

        // Get the branch that we may need to refresh
        var p = node.parent;
        if (p.parent) {
            p = p.parent;
        }

        // Delete the node and its children
        this._deleteNode(node);

        // Refresh the parent of the parent
        if (autoRefresh && p && p.childrenRendered) {
            p.refresh();
        }

        return true;
    },

    /**
     * wait until the animation is complete before deleting
     * to avoid javascript errors
     * @method _removeChildren_animComplete
     * @param o the custom event payload
     * @private
     */
    _removeChildren_animComplete: function(o) {
        this.unsubscribe(this._removeChildren_animComplete);
        this.removeChildren(o.node);
    },

    /**
     * Deletes this nodes child collection, recursively.  Also collapses
     * the node, and resets the dynamic load flag.  The primary use for
     * this method is to purge a node and allow it to fetch its data
     * dynamically again.
     * @method removeChildren
     * @param {Node} node the node to purge
     */
    removeChildren: function(node) {

        if (node.expanded) {
            // wait until the animation is complete before deleting to
            // avoid javascript errors
            if (this._collapseAnim) {
                this.subscribe("animComplete",
                        this._removeChildren_animComplete, this, true);
                Widget.Node.prototype.collapse.call(node);
                return;
            }

            node.collapse();
        }

        this.logger.log("Removing children for " + node);
        while (node.children.length) {
            this._deleteNode(node.children[0]);
        }

        if (node.isRoot()) {
            Widget.Node.prototype.expand.call(node);
        }

        node.childrenRendered = false;
        node.dynamicLoadComplete = false;

        node.updateIcon();
    },

    /**
     * Deletes the node and recurses children
     * @method _deleteNode
     * @private
     */
    _deleteNode: function(node) {
        // Remove all the child nodes first
        this.removeChildren(node);

        // Remove the node from the tree
        this.popNode(node);
    },

    /**
     * Removes the node from the tree, preserving the child collection
     * to make it possible to insert the branch into another part of the
     * tree, or another tree.
     * @method popNode
     * @param {Node} node to remove
     */
    popNode: function(node) {
        var p = node.parent;

        // Update the parent's collection of children
        var a = [];

        for (var i=0, len=p.children.length;i<len;++i) {
            if (p.children[i] != node) {
                a[a.length] = p.children[i];
            }
        }

        p.children = a;

        // reset the childrenRendered flag for the parent
        p.childrenRendered = false;

         // Update the sibling relationship
        if (node.previousSibling) {
            node.previousSibling.nextSibling = node.nextSibling;
        }

        if (node.nextSibling) {
            node.nextSibling.previousSibling = node.previousSibling;
        }

        if (this.currentFocus == node) {
            this.currentFocus = null;
        }
        if (this._currentlyHighlighted == node) {
            this._currentlyHighlighted = null;
        }

        node.parent = null;
        node.previousSibling = null;
        node.nextSibling = null;
        node.tree = null;

        // Update the tree's node collection
        delete this._nodes[node.index];
    },

    /**
    * Nulls out the entire TreeView instance and related objects, removes attached
    * event listeners, and clears out DOM elements inside the container. After
    * calling this method, the instance reference should be expliclitly nulled by
    * implementer, as in myDataTable = null. Use with caution!
    *
    * @method destroy
    */
    destroy : function() {
        // Since the label editor can be separated from the main TreeView control
        // the destroy method for it might not be there.
        if (this._destroyEditor) { this._destroyEditor(); }
        var el = this.getEl();
        Event.removeListener(el,'click');
        Event.removeListener(el,'dblclick');
        Event.removeListener(el,'mouseover');
        Event.removeListener(el,'mouseout');
        Event.removeListener(el,'keydown');
        for (var i = 0 ; i < this._nodes.length; i++) {
            var node = this._nodes[i];
            if (node && node.destroy) {node.destroy(); }
        }
        el.innerHTML = '';
        this._hasEvents = false;
    },




    /**
     * TreeView instance toString
     * @method toString
     * @return {string} string representation of the tree
     */
    toString: function() {
        return "TreeView " + this.id;
    },

    /**
     * Count of nodes in tree
     * @method getNodeCount
     * @return {int} number of nodes in the tree
     */
    getNodeCount: function() {
        return this.getRoot().getNodeCount();
    },

    /**
     * Returns an object which could be used to rebuild the tree.
     * It can be passed to the tree constructor to reproduce the same tree.
     * It will return false if any node loads dynamically, regardless of whether it is loaded or not.
     * @method getTreeDefinition
     * @return {Object | false}  definition of the tree or false if any node is defined as dynamic
     */
    getTreeDefinition: function() {
        return this.getRoot().getNodeDefinition();
    },

    /**
     * Abstract method that is executed when a node is expanded
     * @method onExpand
     * @param node {Node} the node that was expanded
     * @deprecated use treeobj.subscribe("expand") instead
     */
    onExpand: function(node) { },

    /**
     * Abstract method that is executed when a node is collapsed.
     * @method onCollapse
     * @param node {Node} the node that was collapsed.
     * @deprecated use treeobj.subscribe("collapse") instead
     */
    onCollapse: function(node) { },

    /**
    * Sets the value of a property for all loaded nodes in the tree.
    * @method setNodesProperty
    * @param name {string} Name of the property to be set
    * @param value {any} value to be set
    * @param refresh {boolean} if present and true, it does a refresh
    */
    setNodesProperty: function(name, value, refresh) {
        this.root.setNodesProperty(name,value);
        if (refresh) {
            this.root.refresh();
        }
    },
    /**
    * Event listener to toggle node highlight.
    * Can be assigned as listener to clickEvent, dblClickEvent and enterKeyPressed.
    * It returns false to prevent the default action.
    * @method onEventToggleHighlight
    * @param oArgs {any} it takes the arguments of any of the events mentioned above
    * @return {false} Always cancels the default action for the event
    */
    onEventToggleHighlight: function (oArgs) {
        var node;
        if ('node' in oArgs && oArgs.node instanceof Widget.Node) {
            node = oArgs.node;
        } else if (oArgs instanceof Widget.Node) {
            node = oArgs;
        } else {
            return false;
        }
        node.toggleHighlight();
        return false;
    }


};

/* Backwards compatibility aliases */
var PROT = TV.prototype;
 /**
     * Renders the tree boilerplate and visible nodes.
     *  Alias for render
     * @method draw
     * @deprecated Use render instead
     */
PROT.draw = PROT.render;

/* end backwards compatibility aliases */

YAHOO.augment(TV, YAHOO.util.EventProvider);

/**
 * Running count of all nodes created in all trees.  This is
 * used to provide unique identifies for all nodes.  Deleting
 * nodes does not change the nodeCount.
 * @property YAHOO.widget.TreeView.nodeCount
 * @type int
 * @static
 */
TV.nodeCount = 0;

/**
 * Global cache of tree instances
 * @property YAHOO.widget.TreeView.trees
 * @type Array
 * @static
 * @private
 */
TV.trees = [];

/**
 * Global method for getting a tree by its id.  Used in the generated
 * tree html.
 * @method YAHOO.widget.TreeView.getTree
 * @param treeId {String} the id of the tree instance
 * @return {TreeView} the tree instance requested, null if not found.
 * @static
 */
TV.getTree = function(treeId) {
    var t = TV.trees[treeId];
    return (t) ? t : null;
};


/**
 * Global method for getting a node by its id.  Used in the generated
 * tree html.
 * @method YAHOO.widget.TreeView.getNode
 * @param treeId {String} the id of the tree instance
 * @param nodeIndex {String} the index of the node to return
 * @return {Node} the node instance requested, null if not found
 * @static
 */
TV.getNode = function(treeId, nodeIndex) {
    var t = TV.getTree(treeId);
    return (t) ? t.getNodeByIndex(nodeIndex) : null;
};


/**
     * Class name assigned to elements that have the focus
     *
     * @property TreeView.FOCUS_CLASS_NAME
     * @type String
     * @static
     * @final
     * @default "ygtvfocus"

    */
TV.FOCUS_CLASS_NAME = 'ygtvfocus';



})();
