<!doctype html public "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>

<head>
<title>Yahoo! UI Library - Tree Control</title>
<link rel="stylesheet" type="text/css" href="css/screen.css">
</head>
  
<body onload="treeInit()">

<link rel="stylesheet" type="text/css" href="css/check/tree.css">

<div id="pageTitle">
	<h3>Tree Control</h3>
</div>

<?php include('inc-alljs.php'); ?>
<?php include('inc-rightbar.php'); ?>
<script type="text/javascript" src="js/TaskNode.js"></script>
<script type="text/javascript" src="js/CheckOnClickNode.js"></script>

  <div id="content">
    <form name="mainForm" action="javscript:;">
	<div class="newsItem">
	  <h3>Task List</h3>
	  <p>
		The check boxes have three states:
		unchecked, partially checked (some sub-tasks are finished), checked (all sub-tasks complete)
	  </p>

	  <div id="expandcontractdiv">
		<a href="javascript:tree.expandAll()">Expand all</a>
		<a href="javascript:tree.collapseAll()">Collapse all</a>
	  </div>
	  <div id="treeDiv1"></div>

      <input type="button" id="check" onclick="checkSomething()" />

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
	var nodeIndex;
    var tmpNode
	
	function treeInit() {
		buildRandomTextNodeTree();
	}

    function checkSomething() {
        tmpNode.check();
        tree.draw();
    }
	
	function buildRandomTextNodeTree() {
		tree = new YAHOO.widget.TreeView("treeDiv1");

		for (var i = 0; i < Math.floor((Math.random()*4) + 3); i++) {
			tmpNode = new YAHOO.example.CheckOnClickNode("task-" + i, tree.getRoot(), false);
			buildRandomTextBranch(tmpNode);
		}

		tree.draw();
	}

	var callback = null;

	function buildRandomTextBranch(node) {
		if (node.depth < 5) {
			YAHOO.log("buildRandomTextBranch: " + node.index);
			for ( var i = 0; i < Math.floor(Math.random() * 4) ; i++ ) {
				tmpNode = new YAHOO.example.CheckOnClickNode(node.label + "-" + i, node, false);
				buildRandomTextBranch(tmpNode);
			}
		}
	}

</script>

  </body>
</html>
 
