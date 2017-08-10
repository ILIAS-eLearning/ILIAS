<!doctype html public "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>

<head>
<title>Yahoo! UI Library - Tree Control</title>
<link rel="stylesheet" type="text/css" href="css/screen.css">
</head>
  
<body>

<link rel="stylesheet" type="text/css" href="css/check/tree.css">

<div id="pageTitle">
	<h3>Tree Control</h3>
</div>

<?php include('inc-alljs.php'); ?>
<?php include('inc-rightbar.php'); ?>

<script type="text/javascript" src="js/TaskNode.js"></script>

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
		<a href="javascript:checkAll()">Check all</a>
		<a href="javascript:uncheckAll()">Uncheck all</a>
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

<!--
<input type="button" id="showButton" />
-->

<script type="text/javascript">

	var tree;
	var nodes = [];
	var nodeIndex;
	
	YAHOO.util.Event.onAvailable("treeDiv1", buildRandomTextNodeTree)
	
	function buildRandomTextNodeTree() {
		tree = new YAHOO.widget.TreeView("treeDiv1");

		for (var i = 0; i < Math.floor((Math.random()*4) + 3); i++) {
			var tmpNode = new YAHOO.widget.TaskNode("task-" + i, tree.getRoot(), false);
            //tmpNode.onCheckClick = onCheckClick;
			buildRandomTextBranch(tmpNode);
		}
        
        tree.subscribe("checkClick", onCheckClick);
        tree.subscribe("labelClick", onLabelClick);
		tree.draw();
	}

	var callback = null;

	function buildRandomTextBranch(node) {
		if (node.depth < 5) {
			YAHOO.log("buildRandomTextBranch: " + node.index);
			for ( var i = 0; i < Math.floor(Math.random() * 4) ; i++ ) {
				var tmpNode = new YAHOO.widget.TaskNode(node.label + "-" + i, node, false);
                // tmpNode.onCheckClick = onCheckClick;
				buildRandomTextBranch(tmpNode);
			}
		} else {
		    // tmpNode = new YAHOO.widget.TaskNode(node.label + "-" + i, node, false, true);
        }
	}


    function onCheckClick(node) {
        YAHOO.log(node.label + " check was clicked, new state: " + 
                node.checkState);
    }

    function checkAll() {
        var topNodes = tree.getRoot().children;
        for(var i=0; i<topNodes.length; ++i) {
            topNodes[i].check();
        }
    }

    function uncheckAll() {
        var topNodes = tree.getRoot().children;
        for(var i=0; i<topNodes.length; ++i) {
            topNodes[i].uncheck();
        }
    }

   function onLabelClick(node) {
       /*
       new YAHOO.widget.TaskNode("new", node, false);
       node.refresh();
       return false;
       */

       node.getLabelEl().style.backgroundColor = 'red';
   }



</script>

  </body>
</html>
 
