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
	  <h3>Load on demand TreeView Widget</h3>
	  <p>
		The data for the node's children is fetched dynamically when the node
		is expanded the first time.
	  </p>

	  <div id="expandcontractdiv">
		<a href="javascript:tree.expandAll()">Expand all</a>
		<a href="javascript:tree.collapseAll()">Collapse all</a>
		<a href="javascript:testRedraw()">Redraw</a>
	  </div>
	  <div id="treeDiv1">Loading...</div>

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

	var tree, gActiveNode, gTreeCallback;
	var usedWords = [];

	function treeInit() {
		buildLazyLoadTree();
	}

	function buildLazyLoadTree() {
		tree = new YAHOO.widget.TreeView("treeDiv1");

		buildRandomTextBranch(tree.getRoot(), finishTreeLoad, true);
	}

    function testRedraw() {
        document.getElementById("treeDiv1").innerHTML = "";
        tree.draw();
    }


    function testNewNodeRender() {
        tree.getRoot().children = [];
	    var tmp = new YAHOO.widget.TextNode("test", tree.getRoot(), false);
		tmp.setDynamicLoad(buildRandomTextBranch);
        // finishTreeLoad();
        tree.getRoot().loadComplete();
    }

	function finishTreeLoad(o) { tree.draw(); }

	function buildRandomTextBranch(node, onCompleteCallback, initial) {
		// Abort if the node is at the max depth
		if (node.depth >= 10) {
			onCompleteCallback();
			return;
		}

		YAHOO.log("buildRandomTextBranch: " + node.index);

		// The first set of nodes, the labels are random words.  The children
		// of these nodes have labels that are related to the top-level nodes
		var url = (initial) ? '/yui/rand_word_list.php?' :
							  '/yui/levenshtein.php?word=' + node.label + '&';

		// Bust the cache
		url += getRandomStr(8) + '=' + getRandomStr(8),

		// Keep a global reference to the node and callback while we get the
		// data.  This is safe because the tree control only allows one 
		// dynamic load operation to happen at a time (otherwise, it would be
		// possible that the user would click another branch before the first
		// one was completed ... leading to data being rendered in the wrong
		// place.)
		gActiveNode = node;
		gTreeCallback = onCompleteCallback;

		// var obj = ygConn.getObject();
		// ygConn.http.asyncRequest( obj, 'GET', url, false, handleResponse );
        var transInfo = { 
                            success:  handleResponse, 
                            argument: { node: node, callback: onCompleteCallback }
                        };

		YAHOO.util.Connect.asyncRequest( 'GET', url, transInfo, null );

	}

	function handleResponse(o) {
		// Only process if the request is complete
		YAHOO.log("handleResponse o: " + o.status);

		if (o && o.responseText && o.responseText.length > 1) {
            // The response is a comma separated list of wodrs
			var words = o.responseText.split(",");
			var count = 0;
            // get the node from the Connect response
            var node = o.argument.node;

            for ( var i = 0; i < words.length; i++ ) {

                // query the used word list so that we avoid repeat words
                if (!usedWords[words[i]]) {

                    // create a new text node
                    n = new YAHOO.widget.TextNode(words[i], node, false);
                    n.setDynamicLoad(buildRandomTextBranch, 1);

                    // Only use words once
                    usedWords[words[i]] = true;

                    // keep the list managable
                    if (count++ > 4) { break; } // generate no more than 5 children
                } else {

                    // node.setDynamicLoad(false);
                    // alert("here");
                }
            }
		}

        // Notify the tree that we are finished loading the data
		o.argument.callback();

	}

function getRandomStr(len) {
	var chars = "0123456789ABCDEFGHIJKLMNOPQRSTUVWXTZabcdefghiklmnopqrstuvwxyz";
	var randomstring = '';
	for (var i=0; i<len; i++) {
		var rnum = Math.floor(Math.random() * chars.length);
		randomstring += chars.substring(rnum,rnum+1);
	}

	return randomstring;
}

</script>

  </body>
</html>
 
