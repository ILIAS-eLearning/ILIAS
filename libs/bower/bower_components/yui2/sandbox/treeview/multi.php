<!doctype html public "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>

<head>
<title>Yahoo! UI Library - Tree Control</title>
<link rel="stylesheet" type="text/css" href="css/screen.css">
<link rel="stylesheet" type="text/css" href="../../build/reset-fonts-grids/reset-fonts-grids.css" />
<link rel="stylesheet" type="text/css" href="../../build/base/base.css" />
</head>
  
<body onload="treeInit()">

<link rel="stylesheet" type="text/css" href="css/multi/tree.css">
<style>
#treecontaner {width: 550px;}
#tree1 {width:120px;padding: 10px;float:left;}
#tree2 {width:120px;padding: 10px;float:left;}
#tree3 {width:120px;padding: 10px;float:left;}
</style>

<div id="pageTitle">
	<h3>Tree Control</h3>
</div>

<?php include('inc-alljs.php'); ?>
<?php include('inc-rightbar.php'); ?>

  <div id="content">
    <form name="mainForm" action="javscript:;">
	<div class="newsItem">
	  <h3>Multiple trees with different styles</h3>
	  <p>
		
	  </p>

      <div id="treecontainer">
	  <div id="tree1"></div>
	  <div id="tree2"></div>
	  <div id="tree3" class="treemenu"></div>
      </div>

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

	function treeInit() {
		buildRandomTextNodeTree(new YAHOO.widget.TreeView("tree1"));
		buildRandomTextNodeTree(new YAHOO.widget.TreeView("tree2"));
		buildRandomTextNodeTree(new YAHOO.widget.TreeView("tree3"));
	}
	
	function buildRandomTextNodeTree(tree) {

		for (var i = 0; i < Math.floor((Math.random()*4) + 3); i++) {
			var tmpNode = new YAHOO.widget.TextNode("label-" + i, tree.getRoot(), false);
			buildRandomTextBranch(tmpNode);
		}

		tree.draw();
	}

	var callback = null;

	function buildRandomTextBranch(node) {
		if (node.depth < 1) {
			YAHOO.log("buildRandomTextBranch: " + node.index);
			for ( var i = 0; i < Math.floor(Math.random() * 4 + 2) ; i++ ) {
				var tmpNode = new YAHOO.widget.TextNode(node.label + "-" + i, node, false);
				buildRandomTextBranch(tmpNode);
			}
		}
	}

</script>

  </body>
</html>
 
