/**
 * A custom YAHOO.widget.Node that handles the unique nature of
 * the virtual, presentationless root node.
 * @namespace YAHOO.widget
 * @class RootNode
 * @extends YAHOO.widget.Node
 * @param oTree {YAHOO.widget.TreeView} The tree instance this node belongs to
 * @constructor
 */
YAHOO.widget.RootNode = function(oTree) {
    // Initialize the node with null params.  The root node is a
    // special case where the node has no presentation.  So we have
    // to alter the standard properties a bit.
    this.init(null, null, true);

    /*
     * For the root node, we get the tree reference from as a param
     * to the constructor instead of from the parent element.
     */
    this.tree = oTree;
};

YAHOO.extend(YAHOO.widget.RootNode, YAHOO.widget.Node, {

   /**
     * The node type
     * @property _type
      * @type string
     * @private
     * @default "RootNode"
     */
    _type: "RootNode",

    // overrides YAHOO.widget.Node
    getNodeHtml: function() {
        return "";
    },

    toString: function() {
        return this._type;
    },

    loadComplete: function() {
        this.tree.draw();
    },

   /**
     * Count of nodes in tree.
    * It overrides Nodes.getNodeCount because the root node should not be counted.
     * @method getNodeCount
     * @return {int} number of nodes in the tree
     */
    getNodeCount: function() {
        for (var i = 0, count = 0;i< this.children.length;i++) {
            count += this.children[i].getNodeCount();
        }
        return count;
    },

  /**
     * Returns an object which could be used to build a tree out of this node and its children.
     * It can be passed to the tree constructor to reproduce this node as a tree.
     * Since the RootNode is automatically created by treeView,
     * its own definition is excluded from the returned node definition
     * which only contains its children.
     * @method getNodeDefinition
     * @return {Object | false}  definition of the tree or false if any child node is defined as dynamic
     */
    getNodeDefinition: function() {

        for (var def, defs = [], i = 0; i < this.children.length;i++) {
            def = this.children[i].getNodeDefinition();
            if (def === false) { return false;}
            defs.push(def);
        }
        return defs;
    },

    collapse: function() {},
    expand: function() {},
    getSiblings: function() { return null; },
    focus: function () {}

});
