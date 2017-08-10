<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
        "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
      <html xmlns="http://www.w3.org/1999/xhtml" lang="en" xml:lang="en">
<head>

<title>Yahoo! UI Library - Tree Control</title>
<link rel="stylesheet" type="text/css" href="http://us.js2.yimg.com/us.js.yimg.com/lib/common/css/fonts_2.0.0-b3.css" />
<link rel="stylesheet" type="text/css" href="http://us.js2.yimg.com/us.js.yimg.com/lib/common/css/grids_2.0.0-b3.css" />

<link rel="stylesheet" type="text/css" href="css/screen.css" />
<!--
<link rel="stylesheet" type="text/css" href="http://html-new/lib/common/widgets/2/treeview/css/default/treeview_2.0.0-b4.css" />
-->
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

      <div id="treeDiv1">
      </div>
	  <div class="treeDiv">
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
//<![CDATA[
	var tree;
	var nodes = [];
    var count = 0;
	
	function treeInit() {
		buildRandomTextNodeTree();
	}
	
	function buildRandomTextNodeTree() {
		// tree = new YAHOO.widget.TreeView("treeDiv1");
		// tree = new YAHOO.widget.TreeView(document.getElementById("treeDiv1"));
		tree = new YAHOO.widget.TreeView(
                YAHOO.util.Dom.getElementsByClassName("treeDiv")[0]);

        tree.subscribe("collapseComplete", function(node) {

                //setTimeout(tree.removeChildren(node), 100);
                tree.removeChildren(node);

            });

		for (var i = 0; i < Math.floor((Math.random()*4) + 3); i++) {
            var current = nodes.length;
            nodes[current] = 
                new YAHOO.widget.TextNode("label-" + i, tree.getRoot(), false);
			// tmpNode.collapse();
			// tmpNode.expand();
			// buildRandomTextBranch(tmpNode);
			buildLargeBranch(nodes[current]);
		}

		tree.draw();
	}

	var callback = null, first, last;

	function buildLargeBranch(node) {
		if (node.depth < 10) {
			YAHOO.log("buildRandomTextBranch: " + node.index);
			for ( var i = 0; i < 10; i++ ) {

                var current = nodes.length;
				nodes[current] = 
                    new YAHOO.widget.TextNode(node.label + "-" + i, node, false);
                
                if (i==0) first = nodes[current]; 
			}

            last = nodes[current]; 
		}
	}

	function buildRandomTextBranch(node) {
		if (node.depth < 10) {
			YAHOO.log("buildRandomTextBranch: " + node.index);
			for ( var i = 0; i < Math.floor(Math.random() * 4) ; i++ ) {
                var current = nodes.length;
				nodes[current] = new YAHOO.widget.TextNode(node.label + "-" + i, node, false);
				buildRandomTextBranch(nodes[current]);
			}
		}
	}

    function removeChildren(node) {
        tree.removeChildren(node);
    }

    function addChildren(node) {
        // buildRandomTextBranch(node);
        buildLargeBranch(node);
        node.refresh();
    }

    function insertBefore(node) {
        var current = nodes.length;
        nodes[current] = new YAHOO.widget.TextNode( "before " + node.label + " " + count++);
        nodes[current].insertBefore(node);
        node.parent.refresh();
    }

    function insertAfter(node) {
        var current = nodes.length;
        nodes[current] = new YAHOO.widget.TextNode( "after " + node.label + " " + count++);
        nodes[current].insertAfter(node);
        node.parent.refresh();
    }

    function buttonAction(e) {
        var target = YAHOO.util.Event.getTarget(e, true);

        if (target.type == "button") {
            var action = target.value;
            switch (action) {
                case "remove":
                    removeChildren(nodes[0])
                    break;
                case "add":
                    addChildren(nodes[0])
                    break;
                case "insertBefore":
                    insertBefore(first);
                    insertBefore(last);
                    break;
                case "insertAfter":
                    insertAfter(first);
                    insertAfter(last);
                    break;
                default:
            }
        }
    }


    YAHOO.util.Event.addListener(window, "load", treeInit);

    /*
    YAHOO.util.Event.addListener("remove", "click", 
            function() { removeChildren(nodes[0]) });
    YAHOO.util.Event.addListener("add", "click", 
            function() { addChildren(nodes[0]) });
    */

    YAHOO.util.Event.addListener("actions", "click", buttonAction);

//]]>
</script>

<div id="actions">
    <input type="button" value="remove" />
    <input type="button" value="add" />
    <input type="button" value="insertBefore" />
    <input type="button" value="insertAfter" />
</div>
  </body>
</html>
 
