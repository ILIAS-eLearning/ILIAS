<!doctype html public "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
<head>
<title>Yahoo! UI Library - Tree Control</title>
<link rel="stylesheet" type="text/css" href="css/screen.css">
<style type="text/css">
    .emLabel, .emLabel:link, .emLabel:visited, .emLabel:hover { 
        font-weight: bold;
    }
</style>
</head>
  
<body onload="treeInit()">

<link rel="stylesheet" type="text/css" href="css/menu/tree.css">

<div id="pageTitle">
	<h3>Tree Control</h3>
</div>

<?php include('inc-alljs.php'); ?>
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
//<![CDATA[
var tree;
(function() {

    var Event = YAHOO.util.Event;
    var MenuNode = YAHOO.widget.MenuNode;

    function buildTree() {
        tree = new YAHOO.widget.TreeView("treeDiv1");

        for (var i = 0; i < Math.floor((Math.random()*4) + 3); i++) {
            var tmpNode = new MenuNode("label-" + i, tree.getRoot(), false);
            buildBranch(tmpNode);
        }

       // Trees with TextNodes will fire an event for when the label is clicked:
       tree.subscribe("labelClick", function(node) {
                if (node.data.disabled) {
                    alert("disabled");
                }
           });

        tree.draw();
    }

    function buildBranch(node) {
        if (node.depth < 10) {
            YAHOO.log("buildRandomTextBranch: " + node.index);
            for ( var i = 0; i < 10; i++ ) {
                var data = {
                    "label": node.label + "-" + i,
                    "disabled": (i%2==0) // disable ever other leaf node
                }
                new MenuNode(data, node, false);
            }
        }
    }

    Event.addListener(window, "load", buildTree);

})();

//]]>
</script>


</script>

  </body>
</html>
 
