/**
 * A menu-specific implementation that differs from TextNode in that only
 * one sibling can be expanded at a time.
 * @namespace YAHOO.widget
 * @class MenuNode
 * @extends YAHOO.widget.TextNode
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
 * @constructor
 */
YAHOO.widget.MenuNode = function(oData, oParent, expanded) {
    YAHOO.widget.MenuNode.superclass.constructor.call(this,oData,oParent,expanded);

   /*
     * Menus usually allow only one branch to be open at a time.
     */
    this.multiExpand = false;

};

YAHOO.extend(YAHOO.widget.MenuNode, YAHOO.widget.TextNode, {

    /**
     * The node type
     * @property _type
     * @private
    * @default "MenuNode"
     */
    _type: "MenuNode"

});
