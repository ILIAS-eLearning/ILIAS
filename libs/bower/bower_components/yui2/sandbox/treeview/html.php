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

  <div id="content">
    <form name="mainForm" action="javscript:;">
    <div class="newsItem">
      <h3>HTML Node</h3>
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

var tree, htmlNode;

function treeInit() {
    buildRandomTextNodeTree();
}

function buildRandomTextNodeTree() {
    tree = new YAHOO.widget.TreeView("treeDiv1");

    for (var i = 0; i < Math.floor((Math.random()*4) + 3); i++) {
        var tmpNode = new YAHOO.widget.TextNode("label-" + i, tree.getRoot(), false);
        buildRandomTextBranch(tmpNode);
        // var tmpNode2 = new YAHOO.widget.HTMLNode("label-" + i, tree.getRoot(), false, true);
        buildRandomTextBranch(tmpNode);
        // buildRandomTextBranch(tmpNode2);
    }

    tree.draw();

    htmlNode.setHtml("new html");

}

var callback = null;

function buildRandomTextBranch(node) {
    if (node.depth < 3) {
        YAHOO.log("buildRandomTextBranch: " + node.index);
        for ( var i = 0; i < Math.floor(Math.random() * 4) + 1 ; i++ ) {
            tmpNode = new YAHOO.widget.TextNode(node.label + "-" + i, node, false);
            // tmpNode2 = new YAHOO.widget.HTMLNode(node.html + "-" + i, node, false, true);
            // if (node.depth == 2) {
                buildRandomHTMLBranch(tmpNode);
                // buildRandomHTMLBranch(tmpNode2);
            // } else {
                // buildRandomTextBranch(tmpNode);
            // }
        }
    }
}

var counter = 0;
function buildRandomHTMLBranch(node) {
    YAHOO.log("buildRandomHTMLBranch: " + node.index);

    var id = "htmlnode_" + counter++;

    var html = '<div id="' + id + '"' +
                ' style="border:1px solid #aaaaaa; ' +
                ' position:relative; ' +
                ' height:100px; width:200px; ' +
                ' margin-bottom:10px; ' +
                ' background-color: #c5dbfc">' +
                'Info ' + id + '<a href="http://www.yahoo.com">blah!</a></div>';

    // new YAHOO.widget.HTMLNode(html, node, false, true);
    htmlNode = new YAHOO.widget.HTMLNode(html, node, false, false);
}

YAHOO.util.Event.addListener(window, "load", treeInit);

</script>

</body>
</html>

