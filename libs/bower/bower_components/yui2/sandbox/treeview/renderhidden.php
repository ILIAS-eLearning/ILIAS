<!doctype html public "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>

<head>
<title>Yahoo! UI Library - Tree Control</title>
<link rel="stylesheet" type="text/css" href="css/screen.css">
</head>
  
<body onload="treeInit()">

<link rel="stylesheet" type="text/css" href="css/local/tree.css">

<div id="pageTitle">
	<h3>Tree Control</h3>
</div>

<?php include('inc-alljs.php'); ?>
<?php include('inc-rightbar.php'); ?>

  <div id="content">
    <form name="mainForm" action="javscript:;">
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




<script type="text/javascript">

	var tree;
	var nodes = [];
	var nodeIndex = 0;
	
	function treeInit() {
		buildRandomTextNodeTree();

        // grab the last node to see if it is available
        var node = tree.getNodeByProperty("id", nodeIndex - 1);
        var el = node.getEl();
        el.style.backgroundColor = "yellow";
	}
	
	function buildRandomTextNodeTree() {
		tree = new YAHOO.widget.TreeView("treeDiv1");

		for (var i = 0; i < Math.floor((Math.random()*4) + 3); i++) {
            var data = {label: "label-" + i, id: nodeIndex++};
			tmpNode = new YAHOO.widget.TextNode(data, tree.getRoot(), false);
            tmpNode.renderHidden = true;
            nodes.push(tmpNode);

			// tmpNode.collapse();
			// tmpNode.expand();
			buildRandomTextBranch(tmpNode);
			// buildLargeBranch(tmpNode);
		}

		tree.draw();


	}

	var callback = null;

	function buildLargeBranch(node) {
		if (node.depth < 10) {
			YAHOO.log("buildRandomTextBranch: " + node.index);
			for ( var i = 0; i < 10; i++ ) {
                data = {label: node.label + "-" + i, id: nodeIndex++};
				tmpNode = new YAHOO.widget.TextNode(data, node, false);
                tmpNode.renderHidden = true;
                nodes.push(tmpNode);
			}
		}
	}

	function buildRandomTextBranch(node) {
		if (node.depth < 10) {
			YAHOO.log("buildRandomTextBranch: " + node.index);
			for ( var i = 0; i < Math.floor(Math.random() * 4) ; i++ ) {
                data = {label: node.label + "-" + i, id: nodeIndex++};
				tmpNode = new YAHOO.widget.TextNode(data, node, false);
                tmpNode.renderHidden = true;
                nodes.push(tmpNode);
				buildRandomTextBranch(tmpNode);
			}

		}
	}

</script>

  </body>
</html>
 
