<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
        "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
      <html xmlns="http://www.w3.org/1999/xhtml" lang="en" xml:lang="en">
<head>
<title>Yahoo! UI Library - Tree Control</title>
<link rel="stylesheet" type="text/css" href="css/screen.css" />
<link rel="stylesheet" type="text/css" href="css/local/tree.css" />
</head>
  
<body>

<div id="pageTitle">
	<h3>Tree Control</h3>
</div>

<?php include('inc-alljs.php'); ?>
<?php include('inc-rightbar.php'); ?>

  <div id="content">
    <form id="mainForm" action="javscript:;">
	<div class="newsItem">
	  <h3>Default TreeView Widget</h3>
	  <p>
		
	  </p>

	  <div id="expandcontractdiv">
		<a href="javascript:tree.expandAll()">Expand all</a>
		<a href="javascript:tree.collapseAll()">Collapse all</a>
	  </div>
	  <div id="treeDiv1"></div>

	</div>
	</form>
  </div>
	
      <div id="footerContainer">
        <div id="footer">
          <p>&nbsp;</p>
        </div>
      </div>
    </div>
  </div>
</div>

<p>
<input type="button" id="add-node" value="Add Node" />

<input type="button" id="add-child" value="Add Child" />
</p>


<script type="text/javascript">
//<![CDATA[
	var tree;
	var nodes = new Array();
	var nodeIndex;
    var emptyNode;
	
	function treeInit() {
		//buildRandomTextNodeTree();
 tree = new YAHOO.widget.TreeView( "treeDiv1" );
 var root = tree.getRoot();
 var node1 = new YAHOO.widget.MenuNode( "Node 1", root, false );
 var node11 = new YAHOO.widget.MenuNode( "Node 1-1", node1, false );
 var node12 = new YAHOO.widget.MenuNode( "Node 1-2", node1, false );
 var node2 = new YAHOO.widget.MenuNode( "Node 2", root, false );
 var node21 = new YAHOO.widget.MenuNode( "Node 2-1", node2, false );
 var node22 = new YAHOO.widget.MenuNode( "Node 2-2", node2, false );
 var node3 = new YAHOO.widget.MenuNode( "Node 3", root, false );
 node3.insertBefore( node2 );
 var node31 = new YAHOO.widget.MenuNode( "Node 3-1", node3, false );
 var node32 = new YAHOO.widget.MenuNode( "Node 3-2", node3, false );
 tree.draw();
	}
	
	function buildRandomTextNodeTree() {
		tree = new YAHOO.widget.TreeView("treeDiv1");
		emptyNode = new YAHOO.widget.MenuNode("emptyNode", tree.getRoot(), false);

		for (var i = 0; i < Math.floor((Math.random()*4) + 3); i++) {
			var tmpNode = new YAHOO.widget.MenuNode("label-" + i, tree.getRoot(), false);
			// tmpNode.collapse();
			// tmpNode.expand();
			// buildRandomTextBranch(tmpNode);
			buildLargeBranch(tmpNode);
		}

		tree.draw();
	}

	var callback = null;

	function buildLargeBranch(node) {
		if (node.depth < 10) {
			YAHOO.log("buildRandomTextBranch: " + node.index);
			for ( var i = 0; i < 10; i++ ) {
				new YAHOO.widget.MenuNode(node.label + "-" + i, node, false);
			}
		}
	}

	function buildRandomTextBranch(node) {
		if (node.depth < 10) {
			YAHOO.log("buildRandomTextBranch: " + node.index);
			for ( var i = 0; i < Math.floor(Math.random() * 4) ; i++ ) {
                var label = (node.label || "new") + "-" + i
				var tmpNode = new YAHOO.widget.MenuNode(label, node, false);
				buildRandomTextBranch(tmpNode);
			}
		}
	}

    function addTopLevelNode() {
        var n = tree.getRoot();
        buildRandomTextBranch(n);
        n.refresh();
    }

    function addChildNode() {
        buildRandomTextBranch(emptyNode);
        emptyNode.refresh();
    }

    YAHOO.util.Event.addListener(window, "load", treeInit);
    YAHOO.util.Event.addListener("add-node", "click", addTopLevelNode);
    YAHOO.util.Event.addListener("add-child", "click", addChildNode);

//]]>
</script>

  </body>
</html>
 
