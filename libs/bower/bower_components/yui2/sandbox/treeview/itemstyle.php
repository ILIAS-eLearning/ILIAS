<!doctype html public "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
<head>
<title>Yahoo! UI Library - Tree Control</title>
<link rel="stylesheet" type="text/css" href="css/screen.css">
<?php include('inc-alljs.php'); ?>
</head>
  
<body onload="treeInit()">

<link rel="stylesheet" type="text/css" href="css/menu/tree.css">

<div id="pageTitle">
	<h3>Tree Control</h3>
</div>

<?php include('inc-rightbar.php'); ?>

  <div id="content">
    <form name="mainForm" action="javscript:;">
	<div class="newsItem">
	  <h3>Menu TreeView Widget</h3>
	  <p>
		The presentation differences between the default treeview widget and this
		one were accomplished by modifying the css styles in tree.css.  The
		functionality is the same, except the menu does not allow multiple siblings
		to be expanded at one time (when you expand a node, all of its expanded 
		siblings are collapsed.
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
	var nodes = new Array();
	var nodeIndex = 0;
	
	function treeInit() {
		buildRandomTextNodeTree();
	}

	function buildRandomTextNodeTree() {
		tree = new YAHOO.widget.TreeView("treeDiv1");
		tree.onExpand = function(node) {
			// alert(node.index + " was expanded"); 
		}

		tree.onCollapse = function(node) {
			// alert(node.index + " was collapsed"); 
		}

		for (var i = 0; i < Math.floor((Math.random()*4) + 3); i++) {
			var thisId = nodeIndex++;
			var thisLabel = "menu" + i;
			// nodes[thisId] = new YAHOO.widget.TextNode({ label: thisLabel }, tree.getRoot(), false);
			// nodes[thisId] = new YAHOO.widget.MenuNode({label:thisLabel, href:"http://asdf"}, tree.getRoot(), false);
			nodes[thisId] = new YAHOO.widget.MenuNode(thisLabel, tree.getRoot(), false);

			var p1 = nodes[thisId];
			var l1 = thisLabel;

			for (var j = 0; j < Math.floor(Math.random()*6) + 1; j++) { 
				thisId = nodeIndex++;
				thisLabel = l1 + "-" + j;
				// nodes[thisId] = new YAHOO.widget.TextNode({ label: thisLabel }, p1, true);
					var data = {
							id: thisId,
							label: thisLabel, 
							href: "javascript:onLabelClick(" + thisId + ")" 
                    }

				nodes[thisId] = new YAHOO.widget.MenuNode(data, p1, false);

				var p2 = nodes[thisId];
				var l2 = thisLabel;

				for (var k =0; k < Math.floor(Math.random()*6) + 1; k++) { 
					thisId = nodeIndex++;
					thisLabel = l2 + "-" + k;
					var data = {
							id: thisId,
							label: thisLabel, 
							href: "javascript:deleteNode(" + thisId + ")" 
						}
					nodes[thisId] = new YAHOO.widget.TextNode(data, p2, false);
					// nodes[thisId] = new YAHOO.widget.MenuNode(thisLabel, p2, false);
				}

			}

		}

		// nodes[0] = new YAHOO.widget.TextNode(tree.getRoot(), false, "label-0");
		tree.draw();
	}

	var selectedId = null;
	function onLabelClick(id) {

		var node = tree.getNodeByProperty("id", id);
		// alert(node.label);

		var el = node.getEl()

        YAHOO.log("pos: " + YAHOO.util.Dom.getXY(el));

        el.style.backgroundColor = "#c5dbfc";
		

		if (selectedId != null) {
			node = tree.getNodeByProperty("id", selectedId);
			node.getLabelEl().style.backgroundColor = "white";
		}

		selectedId = id;
	}

    function deleteNode(id) {
		tree.removeNode( tree.getNodeByProperty("id", id), true );
	}

</script>

  </body>
</html>
 
