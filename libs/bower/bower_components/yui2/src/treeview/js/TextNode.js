(function () {
    var Dom = YAHOO.util.Dom,
        Lang = YAHOO.lang,
        Event = YAHOO.util.Event;
/**
 * The default node presentation.  The first parameter should be
 * either a string that will be used as the node's label, or an object
 * that has at least a string property called label.  By default,  clicking the
 * label will toggle the expanded/collapsed state of the node.  By
 * setting the href property of the instance, this behavior can be
 * changed so that the label will go to the specified href.
 * @namespace YAHOO.widget
 * @class TextNode
 * @extends YAHOO.widget.Node
 * @constructor
 * @param oData {object} a string or object containing the data that will
 * be used to render this node.
 * Providing a string is the same as providing an object with a single property named label.
 * All values in the oData will be used to set equally named properties in the node
 * as long as the node does have such properties, they are not undefined, private or functions.
 * All attributes are made available in noderef.data, which
 * can be used to store custom attributes.  TreeView.getNode(s)ByProperty
 * can be used to retrieve a node by one of the attributes.
 * @param oParent {YAHOO.widget.Node} this node's parent node
 * @param expanded {boolean} the initial expanded/collapsed state (deprecated; use oData.expanded)
 */
YAHOO.widget.TextNode = function(oData, oParent, expanded) {

    if (oData) {
        if (Lang.isString(oData)) {
            oData = { label: oData };
        }
        this.init(oData, oParent, expanded);
        this.setUpLabel(oData);
    }

    this.logger     = new YAHOO.widget.LogWriter(this.toString());
};

YAHOO.extend(YAHOO.widget.TextNode, YAHOO.widget.Node, {

    /**
     * The CSS class for the label href.  Defaults to ygtvlabel, but can be
     * overridden to provide a custom presentation for a specific node.
     * @property labelStyle
     * @type string
     */
    labelStyle: "ygtvlabel",

    /**
     * The derived element id of the label for this node
     * @property labelElId
     * @type string
     */
    labelElId: null,

    /**
     * The text for the label.  It is assumed that the oData parameter will
     * either be a string that will be used as the label, or an object that
     * has a property called "label" that we will use.
     * @property label
     * @type string
     */
    label: null,

    /**
     * The text for the title (tooltip) for the label element
     * @property title
     * @type string
     */
    title: null,

    /**
     * The href for the node's label.  If one is not specified, the href will
     * be set so that it toggles the node.
     * @property href
     * @type string
     */
    href: null,

    /**
     * The label href target, defaults to current window
     * @property target
     * @type string
     */
    target: "_self",

    /**
     * The node type
     * @property _type
     * @private
     * @type string
     * @default "TextNode"
     */
    _type: "TextNode",


    /**
     * Sets up the node label
     * @method setUpLabel
     * @param oData string containing the label, or an object with a label property
     */
    setUpLabel: function(oData) {

        if (Lang.isString(oData)) {
            oData = {
                label: oData
            };
        } else {
            if (oData.style) {
                this.labelStyle = oData.style;
            }
        }

        this.label = oData.label;

        this.labelElId = "ygtvlabelel" + this.index;

    },

    /**
     * Returns the label element
     * @for YAHOO.widget.TextNode
     * @method getLabelEl
     * @return {object} the element
     */
    getLabelEl: function() {
        return Dom.get(this.labelElId);
    },

    // overrides YAHOO.widget.Node
    getContentHtml: function() {
        var sb = [];
        sb[sb.length] = this.href ? '<a' : '<span';
        sb[sb.length] = ' id="' + Lang.escapeHTML(this.labelElId) + '"';
        sb[sb.length] = ' class="' + Lang.escapeHTML(this.labelStyle)  + '"';
        if (this.href) {
            sb[sb.length] = ' href="' + Lang.escapeHTML(this.href) + '"';
            sb[sb.length] = ' target="' + Lang.escapeHTML(this.target) + '"';
        }
        if (this.title) {
            sb[sb.length] = ' title="' + Lang.escapeHTML(this.title) + '"';
        }
        sb[sb.length] = ' >';
        sb[sb.length] = Lang.escapeHTML(this.label);
        sb[sb.length] = this.href?'</a>':'</span>';
        return sb.join("");
    },



  /**
     * Returns an object which could be used to build a tree out of this node and its children.
     * It can be passed to the tree constructor to reproduce this node as a tree.
     * It will return false if the node or any descendant loads dynamically, regardless of whether it is loaded or not.
     * @method getNodeDefinition
     * @return {Object | false}  definition of the tree or false if this node or any descendant is defined as dynamic
     */
    getNodeDefinition: function() {
        var def = YAHOO.widget.TextNode.superclass.getNodeDefinition.call(this);
        if (def === false) { return false; }

        // Node specific properties
        def.label = this.label;
        if (this.labelStyle != 'ygtvlabel') { def.style = this.labelStyle; }
        if (this.title) { def.title = this.title; }
        if (this.href) { def.href = this.href; }
        if (this.target != '_self') { def.target = this.target; }

        return def;

    },

    toString: function() {
        return YAHOO.widget.TextNode.superclass.toString.call(this) + ": " + this.label;
    },

    // deprecated
    onLabelClick: function() {
        return false;
    },
    refresh: function() {
        YAHOO.widget.TextNode.superclass.refresh.call(this);
        var label = this.getLabelEl();
        label.innerHTML = this.label;
        if (label.tagName.toUpperCase() == 'A') {
            label.href = this.href;
            label.target = this.target;
        }
    }




});
})();
