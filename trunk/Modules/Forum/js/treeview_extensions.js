YAHOO.widget.ilHtmlFrmTreeNode = function(oData, oParent, expanded, hasIcon, hasChildren) {
	if (oData) {
		this.init(oData, oParent, expanded);
		this.initContent(oData, hasIcon);
		this.setLeafNode((typeof hasChildren == "undefined" || hasChildren == null) ? false : !hasChildren);
	}
};

YAHOO.extend(YAHOO.widget.ilHtmlFrmTreeNode, YAHOO.widget.HTMLNode, {
	is_leafnode: false,
	isLeafNode: function() {
		return this.is_leafnode;
	},
	setLeafNode: function(status) {
		this.is_leafnode = status;
	},
	getHoverStyle: function() {
		var s = this.getStyle();

		if (this.isLeafNode()) {
			return s;
		}

		if (this.hasChildren(true) && !this.isLoading) {
			s += "h";
		}

		return s;
	},
	getStyle: function() {
		if (this.isLeafNode()) {
			var loc = (this.nextSibling) ? "t" : "l";
			var type = "n";
			return "ygtv" + loc + type;
		}

		if (this.isLoading) {
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

			return "ygtv" + loc + type;
		}
	}
});

function ExtendedTreeView(config) {
	ExtendedTreeView.superclass.constructor.apply(this, arguments);
}
ExtendedTreeView.NAME = "extendedtreeview";
YAHOO.extend(ExtendedTreeView, YAHOO.widget.TreeView, {
	currentNodeId: null,
	onloadNodes: [],
	setOnloadNodes: function(nodes) {
		this.onloadNodes = nodes;
		return this;
	},
	getOnloadNodes: function() {
		return this.onloadNodes;
	},
	onloadNodesFetchedWithChildren: [],
	setOnloadNodesFetchedWithChildren: function(nodes) {
		this.onloadNodesFetchedWithChildren = nodes;
		return this;
	},
	getOnloadNodesFetchedWithChildren: function() {
		return this.onloadNodesFetchedWithChildren;
	},
	expandedNodes: [],
	setExpandedNodes: function(nodes) {
		this.expandedNodes = nodes;
		return this;
	},
	getExpandedNodes: function() {
		return this.expandedNodes;
	},
	expandCertainNodes: function(nodes) {
		this.setExpandedNodes(nodes);
		this.rExpandNodes(this.getRoot());
	},
	rExpandNodes: function(node) {
		var length = node.children.length;
		for (var i = 0; i < length; i++) {
			var c = node.children[i];
			if (this.isNodeExpanded(c)) {
				c.expand();
			}
			this.rExpandNodes(c);
		}
	},
	isNodeExpanded: function(node) {
		var matches = node.data.match(/id='frm_node_(\d+)'/);
		if (typeof matches == "object" && typeof matches[1] != "undefined") {
			var length = this.getExpandedNodes().length;
			for (var i = 0; i < length; i++) {
				if (matches[1] == this.getExpandedNodes()[i]) {
					return true;
				}
			}
		}

		return false;
	}
});