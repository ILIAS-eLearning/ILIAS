<!doctype html public "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
<head>
<title>Yahoo! UI Library - Tree Control</title>
<link rel="stylesheet" type="text/css" href="css/screen.css">
<style type="text/javascript">
    #icon-mode label { 
        display: block;
    }
</style>
</head>

<body>

<link rel="stylesheet" type="text/css" href="css/local/tree.css">

<div id="pageTitle">
	<h3>Tree Control</h3>
</div>

<?php include('inc-alljs.php'); ?>
<?php include('inc-rightbar.php'); ?>
<script type="text/javascript" src="../../build/connection/connection.js"></script>

  <div id="content">
    <form name="mainForm" action="javscript:;">
	<div class="newsItem">
	  <h3>Load on demand TreeView Widget</h3>
	  <p>
		The data for the node's children is fetched dynamically when the node
		is expanded the first time.
	  </p>
      <div id="icon-mode">
        <label class="label">
          <input type="radio" id="mode0" name="mode" value ="0" checked />
          Expanded nodes without children have +/-
        </label>
        <label>
          <input type="radio" id="mode1" name="mode" value ="1" />
          Expanded nodes without children look like leaf nodes
        </label>
      </form>

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

	var tree;
	var usedWords = [];

    var currentIconMode = 999;

    function changeIconMode() {
        var newVal = parseInt(this.value);
        if (newVal != currentIconMode) {
            currentIconMode = newVal;
            YAHOO.util.Dom.get("treeDiv1").innerHTML = "";
            buildLazyLoadTree();
        }
    }

	function treeInit() {
        YAHOO.util.Event.on(["mode0", "mode1"], "click", changeIconMode);

		// buildLazyLoadTree();
		changeIconMode();
	}

	function buildLazyLoadTree() {
		tree = new YAHOO.widget.TreeView("treeDiv1");
		tree.setDynamicLoad(buildRandomTextBranch, currentIconMode);

		// buildRandomTextBranch(tree.getRoot(), finishTreeLoad, true);
		// buildRandomTextBranch(tree.getRoot(), function() {tree.draw()}, true);
		buildRandomTextBranch(tree.getRoot(), function(){tree.getRoot().loadComplete()}, true);
		// buildRandomTextBranch(tree.getRoot(), tree.getRoot().loadComplete, true);
	}

    function testRedraw() {
        document.getElementById("treeDiv1").innerHTML = "";
        tree.draw();
    }


    function testNewNodeRender() {
        tree.getRoot().children = [];
	    var tmp = new YAHOO.widget.TextNode("test", tree.getRoot(), false);
        // finishTreeLoad();
        tree.getRoot().loadComplete();
    }

	function finishTreeLoad(o) { tree.draw(); }

	function buildRandomTextBranch(n, onCompleteCallback, initial) {
		// Abort if the node is at the max depth
		if (n.depth >= 10) {
			onCompleteCallback();
			return;
		}

		YAHOO.log("buildRandomTextBranch: " + n.index);

		// The first set of nodes, the labels are random words.  The children
		// of these nodes have labels that are related to the top-level nodes
		var url = (initial) ? '/yui.OLD/rand_word_list.php?' :
							  '/yui.OLD/levenshtein.php?word=' + n.label + '&';

		// Bust the cache, the change to POST does not appear to prevent 
        // caching issues in IE
		url += getRandomStr(8) + '=' + getRandomStr(8);

        var transInfo = { 
                            success:  handleResponse, 
                            failure:  handleFailure, 
                            argument: { "node": n, "callback": onCompleteCallback }
                        };

		YAHOO.util.Connect.asyncRequest( "POST", url, transInfo, null );

	}

	function handleFailure(o) {
		YAHOO.log("handleFailure: " + o.statusText);
		YAHOO.log("getAllResponseHeaders: " + o.getAllResponseHeaders);
		YAHOO.log("responseText: " + o.responseText);
		YAHOO.log("status: " + o.status);
        if (o && o.argument && o.argument.callback) {
		    o.argument.callback();
        } else {
		    YAHOO.log("callback not avail: " + o.status);
        }
    }

	function handleResponse(o) {
		// Only process if the request is complete
		YAHOO.log("handleResponse: " + o.statusText);
		YAHOO.log("getAllResponseHeaders: " + o.getAllResponseHeaders);

		if (o && o.responseText && o.responseText.length > 1) {
            // The response is a comma separated list of wodrs
			var words = o.responseText.split(",");
			var count = 0;

			for ( var i = 0; i < words.length; i++ ) {

				// query the used word list so that we avoid repeat words
				if (!usedWords[words[i]]) {

                    // get the node from the Connect response
                    var node = o.argument.node;

                    // create a new text node
					n = new YAHOO.widget.TextNode(words[i], node, false);

                    // Only use words once
					usedWords[words[i]] = true;

                    // keep the list managable
					if (count++ > 4) { break; } // generate no more than 5 children
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

YAHOO.util.Event.addListener(window, "load", treeInit);

</script>

  </body>
</html>
 
