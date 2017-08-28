<!doctype html public "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>

<head>
<title>Yahoo! UI Library - Tree Control</title>
<link rel="stylesheet" type="text/css" href="css/screen.css">
</head>
  
<body onload="treeInit()">

<link rel="stylesheet" type="text/css" href="css/folders/tree.css">

<style>

    .icon0 { padding-left: 20px; background: transparent url(img/icons.png) 0 0px no-repeat; }
    .icon1 { padding-left: 20px; background: transparent url(img/icons.png) 0 -36px no-repeat; }
    .icon2 { padding-left: 20px; background: transparent url(img/icons.png) 0 -72px no-repeat; }
    .icon3 { padding-left: 20px; background: transparent url(img/icons.png) 0 -108px no-repeat; }
    .icon4 { padding-left: 20px; background: transparent url(img/icons.png) 0 -144px no-repeat; }
    .icon5 { padding-left: 20px; background: transparent url(img/icons.png) 0 -180px no-repeat; }
    .icon6 { padding-left: 20px; background: transparent url(img/icons.png) 0 -216px no-repeat; }

</style>

<div id="pageTitle">
	<h3>Tree Control</h3>
</div>

<?php include('inc-alljs.php'); ?>
<?php include('inc-rightbar.php'); ?>

  <div id="content">
    <form name="mainForm" action="javscript:;">
	<div class="newsItem">
	  <h3>Folders with file icons</h3>
	  <p>
		This example uses HTMLNode to create the custom icon presentation for the leaf nodes.
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

	var tree, type=0;
	
	function treeInit() {
		buildRandomTextNodeTree();
	}
	
	function buildRandomTextNodeTree() {
		tree = new YAHOO.widget.TreeView("treeDiv1");

		for (var i = 0; i < Math.floor((Math.random()*4) + 3); i++) {
			var tmpNode = new YAHOO.widget.TextNode("label-" + i, tree.getRoot(), false);
			buildRandomTextBranch(tmpNode);
		}

		tree.draw();
	}

	var callback = null;

	function buildRandomTextBranch(node) {
		if (node.depth < 2) {
			YAHOO.log("buildRandomTextBranch: " + node.index);
			for ( var i = 0; i < (Math.floor(Math.random() * 2) + 2) ; i++ ) {
				var tmpNode = new YAHOO.widget.TextNode(node.label + "-" + i, node, false);
				buildRandomTextBranch(tmpNode);
			}
		} else {
			//tmpNode = new YAHOO.example.IconNode(type, node.label + "-" + i, node, false);
			for ( var i = 0; i < (Math.floor(Math.random() * 2) + 4) ; i++ ) {
                tmpNode = new YAHOO.widget.HTMLNode(node.label + "-" + i, node, false, true);
                tmpNode.contentStyle = "icon" + type++;
                if (type > 6) { // just outputting one of each type for demonstration
                    type = 0;
                }
            }
        }
	}


</script>

  </body>
</html>
 
