<!doctype html public "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>

<head>
<title>Yahoo! UI Library - Tree Control</title>
<link rel="stylesheet" type="text/css" href="css/screen.css">
</head>
<body>

<link rel="stylesheet" type="text/css" href="css/local/tree.css">

<div id="pageTitle">
	<h3>Tree Control</h3>
</div>

<?php include('inc-alljs.php'); ?>
<?php include('inc-rightbar.php'); ?>

<script type="text/javascript" src="../../build/animation/animation.js"></script>

  <div id="content">
    <form name="mainForm" action="javscript:;">
	<div class="newsItem">
	  <h3>Animated TreeView Widget</h3>
	  <p> </p>

	  <div id="expandcontractdiv">
		<a href="javascript:tree.expandAll()">Expand all</a>
		<a href="javascript:tree.collapseAll()">Collapse all</a>
		<a href="javascript:tree.removeChildren(nodes[0])">remove</a>
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
	var nodeIndex;
	
	function treeInit() {
		buildRandomTextNodeTree();
	}

    function handleExpand(node) {
        YAHOO.log("handle expand " + node.index);
    }
	
    function handleCollapse(node) {
        YAHOO.log("handle collapse " + node.index);
        // return false; // return false to cancel the collapse
    }
	
    function handleAnimStart(info) {
        YAHOO.log("handle animStart " + info.node.index);
    }
	
    function handleAnimComplete(info) {
        YAHOO.log("handle animComplete " + info.node.index);
    }
	
    function handleLabelClick(node) {
        YAHOO.log("handle labelClick " + node.toString());
    }

	function buildRandomTextNodeTree() {
		tree = new YAHOO.widget.TreeView("treeDiv1");
		tree.setExpandAnim(YAHOO.widget.TVAnim.FADE_IN);
		tree.setCollapseAnim(YAHOO.widget.TVAnim.FADE_OUT);

        tree.subscribe("expand", handleExpand);
        tree.subscribe("collapse", handleCollapse);
        tree.subscribe("animStart", handleAnimStart);
        tree.subscribe("animComplete", handleAnimComplete);
        tree.subscribe("labelClick", handleLabelClick);

		for (var i = 0; i < Math.floor((Math.random()*4) + 3); i++) {
			var tmpNode = new YAHOO.widget.TextNode("<>&^%label-" + i, tree.getRoot(), false);
            nodes.push(tmpNode);
			buildRandomTextBranch(tmpNode);

		}

		tree.draw();
	}

	var callback = null;

	function buildRandomTextBranch(node) {
		if (node.depth < 6) {
			YAHOO.log("buildRandomTextBranch: " + node.index);
			for ( var i = 0; i < Math.floor(Math.random() * 6) ; i++ ) {
				var tmpNode = new YAHOO.widget.TextNode(node.label + "-" + i, node, false);
                nodes.push(tmpNode);
				buildRandomTextBranch(tmpNode);
			}
		}
	}

    function buttonAction(e) {
        var target = YAHOO.util.Event.getTarget(e, true);

        if (target.type == "button") {
            var action = target.value;
            switch (action) {
                case "remove":
                    tree.setCollapseAnim(null);
                    tree.setExpandAnim(null);
                case "add":
                    //addChildren(nodes[0])
                    //break;
                case "insertBefore":
                    //insertBefore(first);
                    //insertBefore(last);
                    //break;
                case "insertAfter":
                    //insertAfter(first);
                    //insertAfter(last);
                    //break;
                default:
            }
        }
    }

    YAHOO.util.Event.addListener("actions", "click", buttonAction);
    YAHOO.util.Event.addListener("treeDiv1", "click", function(e) {
                var n = tree.getNodeByElement(YAHOO.util.Event.getTarget(e));
                YAHOO.log("TREE click on node: " + n.label, "warn");
            });


    treeInit();

  </script>

<div id="actions">
    <input type="button" value="remove" />
</div>
  </body>
</html>
 
