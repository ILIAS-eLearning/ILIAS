(function () {
    var Dom = YAHOO.util.Dom,
        Lang = YAHOO.lang,
        Event = YAHOO.util.Event;
/**
 * The base class for all tree nodes.  The node's presentation and behavior in
 * response to mouse events is handled in Node subclasses.
 * @namespace YAHOO.widget
 * @class Node
 * @uses YAHOO.util.EventProvider
 * @param oData {object} a string or object containing the data that will
 * be used to render this node, and any custom attributes that should be
 * stored with the node (which is available in noderef.data).
 * All values in oData will be used to set equally named properties in the node
 * as long as the node does have such properties, they are not undefined, private or functions,
 * the rest of the values will be stored in noderef.data
 * @param oParent {Node} this node's parent node
 * @param expanded {boolean} the initial expanded/collapsed state (deprecated, use oData.expanded)
 * @constructor
 */
YAHOO.widget.Node = function(oData, oParent, expanded) {
    if (oData) { this.init(oData, oParent, expanded); }
};

YAHOO.widget.Node.prototype = {

    /**
     * The index for this instance obtained from global counter in YAHOO.widget.TreeView.
     * @property index
     * @type int
     */
    index: 0,

    /**
     * This node's child node collection.
     * @property children
     * @type Node[]
     */
    children: null,

    /**
     * Tree instance this node is part of
     * @property tree
     * @type TreeView
     */
    tree: null,

    /**
     * The data linked to this node.  This can be any object or primitive
     * value, and the data can be used in getNodeHtml().
     * @property data
     * @type object
     */
    data: null,

    /**
     * Parent node
     * @property parent
     * @type Node
     */
    parent: null,

    /**
     * The depth of this node.  We start at -1 for the root node.
     * @property depth
     * @type int
     */
    depth: -1,

    /**
     * The node's expanded/collapsed state
     * @property expanded
     * @type boolean
     */
    expanded: false,

    /**
     * Can multiple children be expanded at once?
     * @property multiExpand
     * @type boolean
     */
    multiExpand: true,

    /**
     * Should we render children for a collapsed node?  It is possible that the
     * implementer will want to render the hidden data...  @todo verify that we
     * need this, and implement it if we do.
     * @property renderHidden
     * @type boolean
     */
    renderHidden: false,

    /**
     * This flag is set to true when the html is generated for this node's
     * children, and set to false when new children are added.
     * @property childrenRendered
     * @type boolean
     */
    childrenRendered: false,

    /**
     * Dynamically loaded nodes only fetch the data the first time they are
     * expanded.  This flag is set to true once the data has been fetched.
     * @property dynamicLoadComplete
     * @type boolean
     */
    dynamicLoadComplete: false,

    /**
     * This node's previous sibling
     * @property previousSibling
     * @type Node
     */
    previousSibling: null,

    /**
     * This node's next sibling
     * @property nextSibling
     * @type Node
     */
    nextSibling: null,

    /**
     * We can set the node up to call an external method to get the child
     * data dynamically.
     * @property _dynLoad
     * @type boolean
     * @private
     */
    _dynLoad: false,

    /**
     * Function to execute when we need to get this node's child data.
     * @property dataLoader
     * @type function
     */
    dataLoader: null,

    /**
     * This is true for dynamically loading nodes while waiting for the
     * callback to return.
     * @property isLoading
     * @type boolean
     */
    isLoading: false,

    /**
     * The toggle/branch icon will not show if this is set to false.  This
     * could be useful if the implementer wants to have the child contain
     * extra info about the parent, rather than an actual node.
     * @property hasIcon
     * @type boolean
     */
    hasIcon: true,

    /**
     * Used to configure what happens when a dynamic load node is expanded
     * and we discover that it does not have children.  By default, it is
     * treated as if it still could have children (plus/minus icon).  Set
     * iconMode to have it display like a leaf node instead.
     * @property iconMode
     * @type int
     */
    iconMode: 0,

    /**
     * Specifies whether or not the content area of the node should be allowed
     * to wrap.
     * @property nowrap
     * @type boolean
     * @default false
     */
    nowrap: false,

 /**
     * If true, the node will alway be rendered as a leaf node.  This can be
     * used to override the presentation when dynamically loading the entire
     * tree.  Setting this to true also disables the dynamic load call for the
     * node.
     * @property isLeaf
     * @type boolean
     * @default false
     */
    isLeaf: false,

/**
     * The CSS class for the html content container.  Defaults to ygtvhtml, but
     * can be overridden to provide a custom presentation for a specific node.
     * @property contentStyle
     * @type string
     */
    contentStyle: "",


    /**
     * The generated id that will contain the data passed in by the implementer.
     * @property contentElId
     * @type string
     */
    contentElId: null,

/**
 * Enables node highlighting.  If true, the node can be highlighted and/or propagate highlighting
 * @property enableHighlight
 * @type boolean
 * @default true
 */
    enableHighlight: true,

/**
 * Stores the highlight state.  Can be any of:
 * <ul>
 * <li>0 - not highlighted</li>
 * <li>1 - highlighted</li>
 * <li>2 - some children highlighted</li>
 * </ul>
 * @property highlightState
 * @type integer
 * @default 0
 */

 highlightState: 0,

 /**
 * Tells whether highlighting will be propagated up to the parents of the clicked node
 * @property propagateHighlightUp
 * @type boolean
 * @default false
 */

 propagateHighlightUp: false,

 /**
 * Tells whether highlighting will be propagated down to the children of the clicked node
 * @property propagateHighlightDown
 * @type boolean
 * @default false
 */

 propagateHighlightDown: false,

 /**
  * User-defined className to be added to the Node
  * @property className
  * @type string
  * @default null
  */

 className: null,

 /**
     * The node type
     * @property _type
     * @private
     * @type string
     * @default "Node"
*/
    _type: "Node",

    /*
    spacerPath: "http://l.yimg.com/a/i/space.gif",
    expandedText: "Expanded",
    collapsedText: "Collapsed",
    loadingText: "Loading",
    */

    /**
     * Initializes this node, gets some of the properties from the parent
     * @method init
     * @param oData {object} a string or object containing the data that will
     * be used to render this node
     * @param oParent {Node} this node's parent node
     * @param expanded {boolean} the initial expanded/collapsed state
     */
    init: function(oData, oParent, expanded) {

        this.data = {};
        this.children   = [];
        this.index      = YAHOO.widget.TreeView.nodeCount;
        ++YAHOO.widget.TreeView.nodeCount;
        this.contentElId = "ygtvcontentel" + this.index;

        if (Lang.isObject(oData)) {
            for (var property in oData) {
                if (oData.hasOwnProperty(property)) {
                    if (property.charAt(0) != '_'  && !Lang.isUndefined(this[property]) && !Lang.isFunction(this[property]) ) {
                        this[property] = oData[property];
                    } else {
                        this.data[property] = oData[property];
                    }
                }
            }
        }
        if (!Lang.isUndefined(expanded) ) { this.expanded  = expanded;  }

        this.logger     = new YAHOO.widget.LogWriter(this.toString());

        /**
         * The parentChange event is fired when a parent element is applied
         * to the node.  This is useful if you need to apply tree-level
         * properties to a tree that need to happen if a node is moved from
         * one tree to another.
         *
         * @event parentChange
         * @type CustomEvent
         */
        this.createEvent("parentChange", this);

        // oParent should never be null except when we create the root node.
        if (oParent) {
            oParent.appendChild(this);
        }
    },

    /**
     * Certain properties for the node cannot be set until the parent
     * is known. This is called after the node is inserted into a tree.
     * the parent is also applied to this node's children in order to
     * make it possible to move a branch from one tree to another.
     * @method applyParent
     * @param {Node} parentNode this node's parent node
     * @return {boolean} true if the application was successful
     */
    applyParent: function(parentNode) {
        if (!parentNode) {
            return false;
        }

        this.tree   = parentNode.tree;
        this.parent = parentNode;
        this.depth  = parentNode.depth + 1;

        // @todo why was this put here.  This causes new nodes added at the
        // root level to lose the menu behavior.
        // if (! this.multiExpand) {
            // this.multiExpand = parentNode.multiExpand;
        // }

        this.tree.regNode(this);
        parentNode.childrenRendered = false;

        // cascade update existing children
        for (var i=0, len=this.children.length;i<len;++i) {
            this.children[i].applyParent(this);
        }

        this.fireEvent("parentChange");

        return true;
    },

    /**
     * Appends a node to the child collection.
     * @method appendChild
     * @param childNode {Node} the new node
     * @return {Node} the child node
     * @private
     */
    appendChild: function(childNode) {
        if (this.hasChildren()) {
            var sib = this.children[this.children.length - 1];
            sib.nextSibling = childNode;
            childNode.previousSibling = sib;
        }
        this.children[this.children.length] = childNode;
        childNode.applyParent(this);

        // part of the IE display issue workaround. If child nodes
        // are added after the initial render, and the node was
        // instantiated with expanded = true, we need to show the
        // children div now that the node has a child.
        if (this.childrenRendered && this.expanded) {
            this.getChildrenEl().style.display = "";
        }

        return childNode;
    },

    /**
     * Appends this node to the supplied node's child collection
     * @method appendTo
     * @param parentNode {Node} the node to append to.
     * @return {Node} The appended node
     */
    appendTo: function(parentNode) {
        return parentNode.appendChild(this);
    },

    /**
    * Inserts this node before this supplied node
    * @method insertBefore
    * @param node {Node} the node to insert this node before
    * @return {Node} the inserted node
    */
    insertBefore: function(node) {
        this.logger.log("insertBefore: " + node);
        var p = node.parent;
        if (p) {

            if (this.tree) {
                this.tree.popNode(this);
            }

            var refIndex = node.isChildOf(p);
            //this.logger.log(refIndex);
            p.children.splice(refIndex, 0, this);
            if (node.previousSibling) {
                node.previousSibling.nextSibling = this;
            }
            this.previousSibling = node.previousSibling;
            this.nextSibling = node;
            node.previousSibling = this;

            this.applyParent(p);
        }

        return this;
    },

    /**
    * Inserts this node after the supplied node
    * @method insertAfter
    * @param node {Node} the node to insert after
    * @return {Node} the inserted node
    */
    insertAfter: function(node) {
        this.logger.log("insertAfter: " + node);
        var p = node.parent;
        if (p) {

            if (this.tree) {
                this.tree.popNode(this);
            }

            var refIndex = node.isChildOf(p);
            this.logger.log(refIndex);

            if (!node.nextSibling) {
                this.nextSibling = null;
                return this.appendTo(p);
            }

            p.children.splice(refIndex + 1, 0, this);

            node.nextSibling.previousSibling = this;
            this.previousSibling = node;
            this.nextSibling = node.nextSibling;
            node.nextSibling = this;

            this.applyParent(p);
        }

        return this;
    },

    /**
    * Returns true if the Node is a child of supplied Node
    * @method isChildOf
    * @param parentNode {Node} the Node to check
    * @return {boolean} The node index if this Node is a child of
    *                   supplied Node, else -1.
    * @private
    */
    isChildOf: function(parentNode) {
        if (parentNode && parentNode.children) {
            for (var i=0, len=parentNode.children.length; i<len ; ++i) {
                if (parentNode.children[i] === this) {
                    return i;
                }
            }
        }

        return -1;
    },

    /**
     * Returns a node array of this node's siblings, null if none.
     * @method getSiblings
     * @return Node[]
     */
    getSiblings: function() {
        var sib =  this.parent.children.slice(0);
        for (var i=0;i < sib.length && sib[i] != this;i++) {}
        sib.splice(i,1);
        if (sib.length) { return sib; }
        return null;
    },

    /**
     * Shows this node's children
     * @method showChildren
     */
    showChildren: function() {
        if (!this.tree.animateExpand(this.getChildrenEl(), this)) {
            if (this.hasChildren()) {
                this.getChildrenEl().style.display = "";
            }
        }
    },

    /**
     * Hides this node's children
     * @method hideChildren
     */
    hideChildren: function() {
        this.logger.log("hiding " + this.index);

        if (!this.tree.animateCollapse(this.getChildrenEl(), this)) {
            this.getChildrenEl().style.display = "none";
        }
    },

    /**
     * Returns the id for this node's container div
     * @method getElId
     * @return {string} the element id
     */
    getElId: function() {
        return "ygtv" + this.index;
    },

    /**
     * Returns the id for this node's children div
     * @method getChildrenElId
     * @return {string} the element id for this node's children div
     */
    getChildrenElId: function() {
        return "ygtvc" + this.index;
    },

    /**
     * Returns the id for this node's toggle element
     * @method getToggleElId
     * @return {string} the toggel element id
     */
    getToggleElId: function() {
        return "ygtvt" + this.index;
    },


    /*
     * Returns the id for this node's spacer image.  The spacer is positioned
     * over the toggle and provides feedback for screen readers.
     * @method getSpacerId
     * @return {string} the id for the spacer image
     */
    /*
    getSpacerId: function() {
        return "ygtvspacer" + this.index;
    },
    */

    /**
     * Returns this node's container html element
     * @method getEl
     * @return {HTMLElement} the container html element
     */
    getEl: function() {
        return Dom.get(this.getElId());
    },

    /**
     * Returns the div that was generated for this node's children
     * @method getChildrenEl
     * @return {HTMLElement} this node's children div
     */
    getChildrenEl: function() {
        return Dom.get(this.getChildrenElId());
    },

    /**
     * Returns the element that is being used for this node's toggle.
     * @method getToggleEl
     * @return {HTMLElement} this node's toggle html element
     */
    getToggleEl: function() {
        return Dom.get(this.getToggleElId());
    },
    /**
    * Returns the outer html element for this node's content
    * @method getContentEl
    * @return {HTMLElement} the element
    */
    getContentEl: function() {
        return Dom.get(this.contentElId);
    },


    /*
     * Returns the element that is being used for this node's spacer.
     * @method getSpacer
     * @return {HTMLElement} this node's spacer html element
     */
    /*
    getSpacer: function() {
        return document.getElementById( this.getSpacerId() ) || {};
    },
    */

    /*
    getStateText: function() {
        if (this.isLoading) {
            return this.loadingText;
        } else if (this.hasChildren(true)) {
            if (this.expanded) {
                return this.expandedText;
            } else {
                return this.collapsedText;
            }
        } else {
            return "";
        }
    },
    */

  /**
     * Hides this nodes children (creating them if necessary), changes the toggle style.
     * @method collapse
     */
    collapse: function() {
        // Only collapse if currently expanded
        if (!this.expanded) { return; }

        // fire the collapse event handler
        var ret = this.tree.onCollapse(this);

        if (false === ret) {
            this.logger.log("Collapse was stopped by the abstract onCollapse");
            return;
        }

        ret = this.tree.fireEvent("collapse", this);

        if (false === ret) {
            this.logger.log("Collapse was stopped by a custom event handler");
            return;
        }


        if (!this.getEl()) {
            this.expanded = false;
        } else {
            // hide the child div
            this.hideChildren();
            this.expanded = false;

            this.updateIcon();
        }

        // this.getSpacer().title = this.getStateText();

        ret = this.tree.fireEvent("collapseComplete", this);

    },

    /**
     * Shows this nodes children (creating them if necessary), changes the
     * toggle style, and collapses its siblings if multiExpand is not set.
     * @method expand
     */
    expand: function(lazySource) {
        // Only expand if currently collapsed.
        if (this.isLoading || (this.expanded && !lazySource)) {
            return;
        }

        var ret = true;

        // When returning from the lazy load handler, expand is called again
        // in order to render the new children.  The "expand" event already
        // fired before fething the new data, so we need to skip it now.
        if (!lazySource) {
            // fire the expand event handler
            ret = this.tree.onExpand(this);

            if (false === ret) {
                this.logger.log("Expand was stopped by the abstract onExpand");
                return;
            }

            ret = this.tree.fireEvent("expand", this);
        }

        if (false === ret) {
            this.logger.log("Expand was stopped by the custom event handler");
            return;
        }

        if (!this.getEl()) {
            this.expanded = true;
            return;
        }

        if (!this.childrenRendered) {
            this.logger.log("children not rendered yet");
            this.getChildrenEl().innerHTML = this.renderChildren();
        } else {
            this.logger.log("children already rendered");
        }

        this.expanded = true;

        this.updateIcon();

        // this.getSpacer().title = this.getStateText();

        // We do an extra check for children here because the lazy
        // load feature can expose nodes that have no children.

        // if (!this.hasChildren()) {
        if (this.isLoading) {
            this.expanded = false;
            return;
        }

        if (! this.multiExpand) {
            var sibs = this.getSiblings();
            for (var i=0; sibs && i<sibs.length; ++i) {
                if (sibs[i] != this && sibs[i].expanded) {
                    sibs[i].collapse();
                }
            }
        }

        this.showChildren();

        ret = this.tree.fireEvent("expandComplete", this);
    },

    updateIcon: function() {
        if (this.hasIcon) {
            var el = this.getToggleEl();
            if (el) {
                el.className = el.className.replace(/\bygtv(([tl][pmn]h?)|(loading))\b/gi,this.getStyle());
            }
        }
        el = Dom.get('ygtvtableel' + this.index);
        if (el) {
            if (this.expanded) {
                Dom.replaceClass(el,'ygtv-collapsed','ygtv-expanded');
            } else {
                Dom.replaceClass(el,'ygtv-expanded','ygtv-collapsed');
            }
        }
    },

    /**
     * Returns the css style name for the toggle
     * @method getStyle
     * @return {string} the css class for this node's toggle
     */
    getStyle: function() {
        // this.logger.log("No children, " + " isDyanmic: " + this.isDynamic() + " expanded: " + this.expanded);
        if (this.isLoading) {
            this.logger.log("returning the loading icon");
            return "ygtvloading";
        } else {
            // location top or bottom, middle nodes also get the top style
            var loc = (this.nextSibling) ? "t" : "l";

            // type p=plus(expand), m=minus(collapase), n=none(no children)
            var type = "n";
            if (this.hasChildren(true) || (this.isDynamic() && !this.getIconMode())) {
            // if (this.hasChildren(true)) {
                type = (this.expanded) ? "m" : "p";
            }

            // this.logger.log("ygtv" + loc + type);
            return "ygtv" + loc + type;
        }
    },

    /**
     * Returns the hover style for the icon
     * @return {string} the css class hover state
     * @method getHoverStyle
     */
    getHoverStyle: function() {
        var s = this.getStyle();
        if (this.hasChildren(true) && !this.isLoading) {
            s += "h";
        }
        return s;
    },

    /**
     * Recursively expands all of this node's children.
     * @method expandAll
     */
    expandAll: function() {
        var l = this.children.length;
        for (var i=0;i<l;++i) {
            var c = this.children[i];
            if (c.isDynamic()) {
                this.logger.log("Not supported (lazy load + expand all)");
                break;
            } else if (! c.multiExpand) {
                this.logger.log("Not supported (no multi-expand + expand all)");
                break;
            } else {
                c.expand();
                c.expandAll();
            }
        }
    },

    /**
     * Recursively collapses all of this node's children.
     * @method collapseAll
     */
    collapseAll: function() {
        for (var i=0;i<this.children.length;++i) {
            this.children[i].collapse();
            this.children[i].collapseAll();
        }
    },

    /**
     * Configures this node for dynamically obtaining the child data
     * when the node is first expanded.  Calling it without the callback
     * will turn off dynamic load for the node.
     * @method setDynamicLoad
     * @param fmDataLoader {function} the function that will be used to get the data.
     * @param iconMode {int} configures the icon that is displayed when a dynamic
     * load node is expanded the first time without children.  By default, the
     * "collapse" icon will be used.  If set to 1, the leaf node icon will be
     * displayed.
     */
    setDynamicLoad: function(fnDataLoader, iconMode) {
        if (fnDataLoader) {
            this.dataLoader = fnDataLoader;
            this._dynLoad = true;
        } else {
            this.dataLoader = null;
            this._dynLoad = false;
        }

        if (iconMode) {
            this.iconMode = iconMode;
        }
    },

    /**
     * Evaluates if this node is the root node of the tree
     * @method isRoot
     * @return {boolean} true if this is the root node
     */
    isRoot: function() {
        return (this == this.tree.root);
    },

    /**
     * Evaluates if this node's children should be loaded dynamically.  Looks for
     * the property both in this instance and the root node.  If the tree is
     * defined to load all children dynamically, the data callback function is
     * defined in the root node
     * @method isDynamic
     * @return {boolean} true if this node's children are to be loaded dynamically
     */
    isDynamic: function() {
        if (this.isLeaf) {
            return false;
        } else {
            return (!this.isRoot() && (this._dynLoad || this.tree.root._dynLoad));
            // this.logger.log("isDynamic: " + lazy);
            // return lazy;
        }
    },

    /**
     * Returns the current icon mode.  This refers to the way childless dynamic
     * load nodes appear (this comes into play only after the initial dynamic
     * load request produced no children).
     * @method getIconMode
     * @return {int} 0 for collapse style, 1 for leaf node style
     */
    getIconMode: function() {
        return (this.iconMode || this.tree.root.iconMode);
    },

    /**
     * Checks if this node has children.  If this node is lazy-loading and the
     * children have not been rendered, we do not know whether or not there
     * are actual children.  In most cases, we need to assume that there are
     * children (for instance, the toggle needs to show the expandable
     * presentation state).  In other times we want to know if there are rendered
     * children.  For the latter, "checkForLazyLoad" should be false.
     * @method hasChildren
     * @param checkForLazyLoad {boolean} should we check for unloaded children?
     * @return {boolean} true if this has children or if it might and we are
     * checking for this condition.
     */
    hasChildren: function(checkForLazyLoad) {
        if (this.isLeaf) {
            return false;
        } else {
            return ( this.children.length > 0 ||
                (checkForLazyLoad && this.isDynamic() && !this.dynamicLoadComplete)
            );
        }
    },

    /**
     * Expands if node is collapsed, collapses otherwise.
     * @method toggle
     */
    toggle: function() {
        if (!this.tree.locked && ( this.hasChildren(true) || this.isDynamic()) ) {
            if (this.expanded) { this.collapse(); } else { this.expand(); }
        }
    },

    /**
     * Returns the markup for this node and its children.
     * @method getHtml
     * @return {string} the markup for this node and its expanded children.
     */
    getHtml: function() {

        this.childrenRendered = false;

        return ['<div class="ygtvitem" id="' , this.getElId() , '">' ,this.getNodeHtml() , this.getChildrenHtml() ,'</div>'].join("");
    },

    /**
     * Called when first rendering the tree.  We always build the div that will
     * contain this nodes children, but we don't render the children themselves
     * unless this node is expanded.
     * @method getChildrenHtml
     * @return {string} the children container div html and any expanded children
     * @private
     */
    getChildrenHtml: function() {


        var sb = [];
        sb[sb.length] = '<div class="ygtvchildren" id="' + this.getChildrenElId() + '"';

        // This is a workaround for an IE rendering issue, the child div has layout
        // in IE, creating extra space if a leaf node is created with the expanded
        // property set to true.
        if (!this.expanded || !this.hasChildren()) {
            sb[sb.length] = ' style="display:none;"';
        }
        sb[sb.length] = '>';

        // this.logger.log(["index", this.index,
                         // "hasChildren", this.hasChildren(true),
                         // "expanded", this.expanded,
                         // "renderHidden", this.renderHidden,
                         // "isDynamic", this.isDynamic()]);

        // Don't render the actual child node HTML unless this node is expanded.
        if ( (this.hasChildren(true) && this.expanded) ||
                (this.renderHidden && !this.isDynamic()) ) {
            sb[sb.length] = this.renderChildren();
        }

        sb[sb.length] = '</div>';

        return sb.join("");
    },

    /**
     * Generates the markup for the child nodes.  This is not done until the node
     * is expanded.
     * @method renderChildren
     * @return {string} the html for this node's children
     * @private
     */
    renderChildren: function() {

        this.logger.log("rendering children for " + this.index);

        var node = this;

        if (this.isDynamic() && !this.dynamicLoadComplete) {
            this.isLoading = true;
            this.tree.locked = true;

            if (this.dataLoader) {
                this.logger.log("Using dynamic loader defined for this node");

                setTimeout(
                    function() {
                        node.dataLoader(node,
                            function() {
                                node.loadComplete();
                            });
                    }, 10);

            } else if (this.tree.root.dataLoader) {
                this.logger.log("Using the tree-level dynamic loader");

                setTimeout(
                    function() {
                        node.tree.root.dataLoader(node,
                            function() {
                                node.loadComplete();
                            });
                    }, 10);

            } else {
                this.logger.log("no loader found");
                return "Error: data loader not found or not specified.";
            }

            return "";

        } else {
            return this.completeRender();
        }
    },

    /**
     * Called when we know we have all the child data.
     * @method completeRender
     * @return {string} children html
     */
    completeRender: function() {
        this.logger.log("completeRender: " + this.index + ", # of children: " + this.children.length);
        var sb = [];

        for (var i=0; i < this.children.length; ++i) {
            // this.children[i].childrenRendered = false;
            sb[sb.length] = this.children[i].getHtml();
        }

        this.childrenRendered = true;

        return sb.join("");
    },

    /**
     * Load complete is the callback function we pass to the data provider
     * in dynamic load situations.
     * @method loadComplete
     */
    loadComplete: function() {
        this.logger.log(this.index + " loadComplete, children: " + this.children.length);
        this.getChildrenEl().innerHTML = this.completeRender();
        if (this.propagateHighlightDown) {
            if (this.highlightState === 1 && !this.tree.singleNodeHighlight) {
                for (var i = 0; i < this.children.length; i++) {
                this.children[i].highlight(true);
            }
            } else if (this.highlightState === 0 || this.tree.singleNodeHighlight) {
                for (i = 0; i < this.children.length; i++) {
                    this.children[i].unhighlight(true);
                }
            } // if (highlighState == 2) leave child nodes with whichever highlight state they are set
        }

        this.dynamicLoadComplete = true;
        this.isLoading = false;
        this.expand(true);
        this.tree.locked = false;
    },

    /**
     * Returns this node's ancestor at the specified depth.
     * @method getAncestor
     * @param {int} depth the depth of the ancestor.
     * @return {Node} the ancestor
     */
    getAncestor: function(depth) {
        if (depth >= this.depth || depth < 0)  {
            this.logger.log("illegal getAncestor depth: " + depth);
            return null;
        }

        var p = this.parent;

        while (p.depth > depth) {
            p = p.parent;
        }

        return p;
    },

    /**
     * Returns the css class for the spacer at the specified depth for
     * this node.  If this node's ancestor at the specified depth
     * has a next sibling the presentation is different than if it
     * does not have a next sibling
     * @method getDepthStyle
     * @param {int} depth the depth of the ancestor.
     * @return {string} the css class for the spacer
     */
    getDepthStyle: function(depth) {
        return (this.getAncestor(depth).nextSibling) ?
            "ygtvdepthcell" : "ygtvblankdepthcell";
    },

    /**
     * Get the markup for the node.  This may be overrided so that we can
     * support different types of nodes.
     * @method getNodeHtml
     * @return {string} The HTML that will render this node.
     */
    getNodeHtml: function() {
        this.logger.log("Generating html");
        var sb = [];

        sb[sb.length] = '<table id="ygtvtableel' + this.index + '" border="0" cellpadding="0" cellspacing="0" class="ygtvtable ygtvdepth' + this.depth;
        sb[sb.length] = ' ygtv-' + (this.expanded?'expanded':'collapsed');
        if (this.enableHighlight) {
            sb[sb.length] = ' ygtv-highlight' + this.highlightState;
        }
        if (this.className) {
            sb[sb.length] = ' ' + this.className;
        }
        sb[sb.length] = '"><tr class="ygtvrow">';

        for (var i=0;i<this.depth;++i) {
            sb[sb.length] = '<td class="ygtvcell ' + this.getDepthStyle(i) + '"><div class="ygtvspacer"></div></td>';
        }

        if (this.hasIcon) {
            sb[sb.length] = '<td id="' + this.getToggleElId();
            sb[sb.length] = '" class="ygtvcell ';
            sb[sb.length] = this.getStyle() ;
            sb[sb.length] = '"><a href="#" class="ygtvspacer">&#160;</a></td>';
        }

        sb[sb.length] = '<td id="' + this.contentElId;
        sb[sb.length] = '" class="ygtvcell ';
        sb[sb.length] = this.contentStyle  + ' ygtvcontent" ';
        sb[sb.length] = (this.nowrap) ? ' nowrap="nowrap" ' : '';
        sb[sb.length] = ' >';
        sb[sb.length] = this.getContentHtml();
        sb[sb.length] = '</td></tr></table>';

        return sb.join("");

    },
    /**
     * Get the markup for the contents of the node.  This is designed to be overrided so that we can
     * support different types of nodes.
     * @method getContentHtml
     * @return {string} The HTML that will render the content of this node.
     */
    getContentHtml: function () {
        return "";
    },

    /**
     * Regenerates the html for this node and its children.  To be used when the
     * node is expanded and new children have been added.
     * @method refresh
     */
    refresh: function() {
        // this.loadComplete();
        this.getChildrenEl().innerHTML = this.completeRender();

        if (this.hasIcon) {
            var el = this.getToggleEl();
            if (el) {
                el.className = el.className.replace(/\bygtv[lt][nmp]h*\b/gi,this.getStyle());
            }
        }
    },

    /**
     * Node toString
     * @method toString
     * @return {string} string representation of the node
     */
    toString: function() {
        return this._type + " (" + this.index + ")";
    },
    /**
    * array of items that had the focus set on them
    * so that they can be cleaned when focus is lost
    * @property _focusHighlightedItems
    * @type Array of DOM elements
    * @private
    */
    _focusHighlightedItems: [],
    /**
    * DOM element that actually got the browser focus
    * @property _focusedItem
    * @type DOM element
    * @private
    */
    _focusedItem: null,

    /**
    * Returns true if there are any elements in the node that can
    * accept the real actual browser focus
    * @method _canHaveFocus
    * @return {boolean} success
    * @private
    */
    _canHaveFocus: function() {
        return this.getEl().getElementsByTagName('a').length > 0;
    },
    /**
    * Removes the focus of previously selected Node
    * @method _removeFocus
    * @private
    */
    _removeFocus:function () {
        if (this._focusedItem) {
            Event.removeListener(this._focusedItem,'blur');
            this._focusedItem = null;
        }
        var el;
        while ((el = this._focusHighlightedItems.shift())) {  // yes, it is meant as an assignment, really
            Dom.removeClass(el,YAHOO.widget.TreeView.FOCUS_CLASS_NAME );
        }
    },
    /**
    * Sets the focus on the node element.
    * It will only be able to set the focus on nodes that have anchor elements in it.
    * Toggle or branch icons have anchors and can be focused on.
    * If will fail in nodes that have no anchor
    * @method focus
    * @return {boolean} success
    */
    focus: function () {
        var focused = false, self = this;

        if (this.tree.currentFocus) {
            this.tree.currentFocus._removeFocus();
        }

        var  expandParent = function (node) {
            if (node.parent) {
                expandParent(node.parent);
                node.parent.expand();
            }
        };
        expandParent(this);

        Dom.getElementsBy  (
            function (el) {
                return (/ygtv(([tl][pmn]h?)|(content))/).test(el.className);
            } ,
            'td' ,
            self.getEl().firstChild ,
            function (el) {
                Dom.addClass(el, YAHOO.widget.TreeView.FOCUS_CLASS_NAME );
                if (!focused) {
                    var aEl = el.getElementsByTagName('a');
                    if (aEl.length) {
                        aEl = aEl[0];
                        aEl.focus();
                        self._focusedItem = aEl;
                        Event.on(aEl,'blur',function () {
                            self.tree.fireEvent('focusChanged',{oldNode:self.tree.currentFocus,newNode:null});
                            self.tree.currentFocus = null;
                            self._removeFocus();
                        });
                        focused = true;
                    }
                }
                self._focusHighlightedItems.push(el);
            }
        );
        if (focused) {
            this.tree.fireEvent('focusChanged',{oldNode:this.tree.currentFocus,newNode:this});
            this.tree.currentFocus = this;
        } else {
            this.tree.fireEvent('focusChanged',{oldNode:self.tree.currentFocus,newNode:null});
            this.tree.currentFocus = null;
            this._removeFocus();
        }
        return focused;
    },

  /**
     * Count of nodes in a branch
     * @method getNodeCount
     * @return {int} number of nodes in the branch
     */
    getNodeCount: function() {
        for (var i = 0, count = 0;i< this.children.length;i++) {
            count += this.children[i].getNodeCount();
        }
        return count + 1;
    },

      /**
     * Returns an object which could be used to build a tree out of this node and its children.
     * It can be passed to the tree constructor to reproduce this node as a tree.
     * It will return false if the node or any children loads dynamically, regardless of whether it is loaded or not.
     * @method getNodeDefinition
     * @return {Object | false}  definition of the tree or false if the node or any children is defined as dynamic
     */
    getNodeDefinition: function() {

        if (this.isDynamic()) { return false; }

        var def, defs = Lang.merge(this.data), children = [];



        if (this.expanded) {defs.expanded = this.expanded; }
        if (!this.multiExpand) { defs.multiExpand = this.multiExpand; }
        if (this.renderHidden) { defs.renderHidden = this.renderHidden; }
        if (!this.hasIcon) { defs.hasIcon = this.hasIcon; }
        if (this.nowrap) { defs.nowrap = this.nowrap; }
        if (this.className) { defs.className = this.className; }
        if (this.editable) { defs.editable = this.editable; }
        if (!this.enableHighlight) { defs.enableHighlight = this.enableHighlight; }
        if (this.highlightState) { defs.highlightState = this.highlightState; }
        if (this.propagateHighlightUp) { defs.propagateHighlightUp = this.propagateHighlightUp; }
        if (this.propagateHighlightDown) { defs.propagateHighlightDown = this.propagateHighlightDown; }
        defs.type = this._type;



        for (var i = 0; i < this.children.length;i++) {
            def = this.children[i].getNodeDefinition();
            if (def === false) { return false;}
            children.push(def);
        }
        if (children.length) { defs.children = children; }
        return defs;
    },


    /**
     * Generates the link that will invoke this node's toggle method
     * @method getToggleLink
     * @return {string} the javascript url for toggling this node
     */
    getToggleLink: function() {
        return 'return false;';
    },

    /**
    * Sets the value of property for this node and all loaded descendants.
    * Only public and defined properties can be set, not methods.
    * Values for unknown properties will be assigned to the refNode.data object
    * @method setNodesProperty
    * @param name {string} Name of the property to be set
    * @param value {any} value to be set
    * @param refresh {boolean} if present and true, it does a refresh
    */
    setNodesProperty: function(name, value, refresh) {
        if (name.charAt(0) != '_'  && !Lang.isUndefined(this[name]) && !Lang.isFunction(this[name]) ) {
            this[name] = value;
        } else {
            this.data[name] = value;
        }
        for (var i = 0; i < this.children.length;i++) {
            this.children[i].setNodesProperty(name,value);
        }
        if (refresh) {
            this.refresh();
        }
    },
    /**
    * Toggles the highlighted state of a Node
    * @method toggleHighlight
    */
    toggleHighlight: function() {
        if (this.enableHighlight) {
            // unhighlights only if fully highligthed.  For not or partially highlighted it will highlight
            if (this.highlightState == 1) {
                this.unhighlight();
            } else {
                this.highlight();
            }
        }
    },

    /**
    * Turns highlighting on node.
    * @method highlight
    * @param _silent {boolean} optional, don't fire the highlightEvent
    */
    highlight: function(_silent) {
        if (this.enableHighlight) {
            if (this.tree.singleNodeHighlight) {
                if (this.tree._currentlyHighlighted) {
                    this.tree._currentlyHighlighted.unhighlight(_silent);
                }
                this.tree._currentlyHighlighted = this;
            }
            this.highlightState = 1;
            this._setHighlightClassName();
            if (!this.tree.singleNodeHighlight) {
                if (this.propagateHighlightDown) {
                    for (var i = 0;i < this.children.length;i++) {
                        this.children[i].highlight(true);
                    }
                }
                if (this.propagateHighlightUp) {
                    if (this.parent) {
                        this.parent._childrenHighlighted();
                    }
                }
            }
            if (!_silent) {
                this.tree.fireEvent('highlightEvent',this);
            }
        }
    },
    /**
    * Turns highlighting off a node.
    * @method unhighlight
    * @param _silent {boolean} optional, don't fire the highlightEvent
    */
    unhighlight: function(_silent) {
        if (this.enableHighlight) {
            // might have checked singleNodeHighlight but it wouldn't really matter either way
            this.tree._currentlyHighlighted = null;
            this.highlightState = 0;
            this._setHighlightClassName();
            if (!this.tree.singleNodeHighlight) {
                if (this.propagateHighlightDown) {
                    for (var i = 0;i < this.children.length;i++) {
                        this.children[i].unhighlight(true);
                    }
                }
                if (this.propagateHighlightUp) {
                    if (this.parent) {
                        this.parent._childrenHighlighted();
                    }
                }
            }
            if (!_silent) {
                this.tree.fireEvent('highlightEvent',this);
            }
        }
    },
    /**
    * Checks whether all or part of the children of a node are highlighted and
    * sets the node highlight to full, none or partial highlight.
    * If set to propagate it will further call the parent
    * @method _childrenHighlighted
    * @private
    */
    _childrenHighlighted: function() {
        var yes = false, no = false;
        if (this.enableHighlight) {
            for (var i = 0;i < this.children.length;i++) {
                switch(this.children[i].highlightState) {
                    case 0:
                        no = true;
                        break;
                    case 1:
                        yes = true;
                        break;
                    case 2:
                        yes = no = true;
                        break;
                }
            }
            if (yes && no) {
                this.highlightState = 2;
            } else if (yes) {
                this.highlightState = 1;
            } else {
                this.highlightState = 0;
            }
            this._setHighlightClassName();
            if (this.propagateHighlightUp) {
                if (this.parent) {
                    this.parent._childrenHighlighted();
                }
            }
        }
    },

    /**
    * Changes the classNames on the toggle and content containers to reflect the current highlighting
    * @method _setHighlightClassName
    * @private
    */
    _setHighlightClassName: function() {
        var el = Dom.get('ygtvtableel' + this.index);
        if (el) {
            el.className = el.className.replace(/\bygtv-highlight\d\b/gi,'ygtv-highlight' + this.highlightState);
        }
    }

};

YAHOO.augment(YAHOO.widget.Node, YAHOO.util.EventProvider);
})();
