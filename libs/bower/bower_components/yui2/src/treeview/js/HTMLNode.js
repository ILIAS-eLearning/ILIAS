(function () {
    var Dom = YAHOO.util.Dom,
        Lang = YAHOO.lang,
        Event = YAHOO.util.Event;

/**
 * This implementation takes either a string or object for the
 * oData argument.  If is it a string, it will use it for the display
 * of this node (and it can contain any html code).  If the parameter
 * is an object,it looks for a parameter called "html" that will be
 * used for this node's display.
 * @namespace YAHOO.widget
 * @class HTMLNode
 * @extends YAHOO.widget.Node
 * @constructor
 * @param oData {object} a string or object containing the data that will
 * be used to render this node.
 * Providing a string is the same as providing an object with a single property named html.
 * All values in the oData will be used to set equally named properties in the node
 * as long as the node does have such properties, they are not undefined, private or functions.
 * All other attributes are made available in noderef.data, which
 * can be used to store custom attributes.  TreeView.getNode(s)ByProperty
 * can be used to retrieve a node by one of the attributes.
 * @param oParent {YAHOO.widget.Node} this node's parent node
 * @param expanded {boolean} the initial expanded/collapsed state (deprecated; use oData.expanded)
 * @param hasIcon {boolean} specifies whether or not leaf nodes should
 * be rendered with or without a horizontal line and/or toggle icon. If the icon
 * is not displayed, the content fills the space it would have occupied.
 * This option operates independently of the leaf node presentation logic
 * for dynamic nodes.
 * (deprecated; use oData.hasIcon)
 */
var HN =  function(oData, oParent, expanded, hasIcon) {
    if (oData) {
        this.init(oData, oParent, expanded);
        this.initContent(oData, hasIcon);
    }
};


YAHOO.widget.HTMLNode = HN;
YAHOO.extend(HN, YAHOO.widget.Node, {

    /**
     * The CSS class for the html content container.  Defaults to ygtvhtml, but
     * can be overridden to provide a custom presentation for a specific node.
     * @property contentStyle
     * @type string
     */
    contentStyle: "ygtvhtml",


    /**
     * The HTML content to use for this node's display
     * @property html
     * @type string
     */
    html: null,

/**
     * The node type
     * @property _type
     * @private
     * @type string
     * @default "HTMLNode"
     */
    _type: "HTMLNode",

    /**
     * Sets up the node label
     * @method initContent
     * @param oData {object} An html string or object containing an html property
     * @param hasIcon {boolean} determines if the node will be rendered with an
     * icon or not
     */
    initContent: function(oData, hasIcon) {
        this.setHtml(oData);
        this.contentElId = "ygtvcontentel" + this.index;
        if (!Lang.isUndefined(hasIcon)) { this.hasIcon  = hasIcon; }

        this.logger = new YAHOO.widget.LogWriter(this.toString());
    },

    /**
     * Synchronizes the node.html, and the node's content
     * @method setHtml
     * @param o {object |string | HTMLElement } An html string, an object containing an html property or an HTML element
     */
    setHtml: function(o) {
        this.html = (Lang.isObject(o) && 'html' in o) ? o.html : o;

        var el = this.getContentEl();
        if (el) {
            if (o.nodeType && o.nodeType == 1 && o.tagName) {
                el.innerHTML = "";
            } else {
                el.innerHTML = this.html;
            }
        }

    },

    // overrides YAHOO.widget.Node
    // If property html is a string, it sets the innerHTML for the node
    // If it is an HTMLElement, it defers appending it to the tree until the HTML basic structure is built
    getContentHtml: function() {
        if (typeof this.html === "string") {
            return this.html;
        } else {

            HN._deferredNodes.push(this);
            if (!HN._timer) {
                HN._timer = window.setTimeout(function () {
                    var n;
                    while((n = HN._deferredNodes.pop())) {
                        n.getContentEl().appendChild(n.html);
                    }
                    HN._timer = null;
                },0);
            }
            return "";
        }
    },

      /**
     * Returns an object which could be used to build a tree out of this node and its children.
     * It can be passed to the tree constructor to reproduce this node as a tree.
     * It will return false if any node loads dynamically, regardless of whether it is loaded or not.
     * @method getNodeDefinition
     * @return {Object | false}  definition of the tree or false if any node is defined as dynamic
     */
    getNodeDefinition: function() {
        var def = HN.superclass.getNodeDefinition.call(this);
        if (def === false) { return false; }
        def.html = this.html;
        return def;

    }
});

    /**
    * An array of HTMLNodes created with HTML Elements that had their rendering
    * deferred until the basic tree structure is rendered.
    * @property _deferredNodes
    * @type YAHOO.widget.HTMLNode[]
    * @default []
    * @private
    * @static
    */
HN._deferredNodes = [];
    /**
    * A system timer value used to mark whether a deferred operation is pending.
    * @property _timer
    * @type System Timer
    * @default null
    * @private
    * @static
    */
HN._timer = null;
})();
