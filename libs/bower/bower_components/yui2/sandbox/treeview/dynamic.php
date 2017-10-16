<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml"><head>


<meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1"><!--Yahoo! User Interface Library: http://twiki.corp.yahoo.com/view/Devel/PresentationPlatform--><!--Begin YUI CSS infrastructure, including Standard Reset, Standard Fonts, and CSS Page Grids -->

<link rel="stylesheet" type="text/css" href="../../build/fonts/fonts.css">
<link rel="stylesheet" type="text/css" href="../../build/grids/grids.css"><!--end YUI CSS infrastructure--><!--begin YUIL Utilities -->
<link rel="stylesheet" type="text/css" href="../../build/logger/assets/skins/sam/logger.css">

<script src="../../build/yahoo/yahoo.js"></script>
<script src="../../build/event/event.js"></script>
<script src="../../build/dom/dom.js"></script>
<script src="../../build/logger/logger.js"></script>
<script src="../../build/treeview/treeview-debug.js"></script>

<link rel="stylesheet" type="text/css" href="css/code.css">
<link rel="stylesheet" type="text/css" href="css/local/tree.css">

<script>


/*create namespace for examples:*/
YAHOO.namespace("example");

/* Using Crockford's "Module Pattern": */
YAHOO.example.treeExample = function() {

	var tree, currentIconMode;

    function changeIconMode() {
        var newVal = parseInt(this.value);
        if (newVal != currentIconMode) {
            currentIconMode = newVal;
        }

        buildTree();
    }
	
		function loadNodeData(node, fnLoadComplete) {
			//We'll randomize our loader with stock data; in many implementations,
			//this step would be replaced with an XMLHttpRequest call to the server
			//for more data.

            YAHOO.log(node.index + " implementer loadNodeData called, " + node.label, "warn");
			
			//Array of India's States
			var aStates = ["Andhra Pradesh","Arunachal Pradesh","Assam","Bihar","Chhattisgarh","Goa","Gujarat","Haryana","Himachal Pradesh","Jammu and Kashmir","Jharkhand","Karnataka","Kerala","Madhya Pradesh","Maharashtra","Manipur","Meghalaya","Mizoram","Nagaland","Orissa","Punjab","Rajasthan","Sikkim","Tamil Nadu","Tripura","Uttaranchal","Uttar","Pradesh","West Bengal"];
		
			//Random number determines whether a node has children
			var index = Math.round(Math.random()*100);
			
			//if our random number is in range, we'll pretend that this node
			//has children; here, we'll indicate that 70% of nodes have
			//children.
			if (index>30) {
				//We'll use a random number to determine the number of
				//children for each node:
				var childCount = (Math.round(Math.random()*5) + 1);
				
				//This is important: The primary job of the data loader function
				//is to determine whether the node has children and then to 
				//actually create the child nodes if they are needed; here, we'll
				//loop through to create each child node:
				for (var i=0; i<childCount; i++) {
					thisState = aStates[Math.round(Math.random()*27)];
					//var newNode = new YAHOO.widget.TextNode(thisState, node, (i%2==0));
					var newNode = new YAHOO.widget.TextNode(thisState, node, false);
                    if (i > 0) {
                        newNode.isLeaf = true;
                    }
				}
			}

            YAHOO.log("children: " + node.children.length, "warn");
			
			//When we're done creating child nodes, we execute the node's
			//loadComplete callback method which comes in as our loader's
			//second argument (we could also access it at node.loadComplete,
			//if necessary):
			fnLoadComplete();
		}

        function buildTree() {
		   //create a new tree:
		   tree = new YAHOO.widget.TreeView("treeContainer");

           tree.subscribe("expand", function() {
              YAHOO.log("onExpand", "warn");
           });
		   
		   //turn dynamic loading on for entire tree:
		   tree.setDynamicLoad(loadNodeData, currentIconMode);
		   
		   //get root node for tree:
		   var root = tree.getRoot();
		   
		   //add child nodes for tree:
		   var tmpNode1 = new YAHOO.widget.TextNode("First Node", root, false);
		   var tmpNode2 = new YAHOO.widget.TextNode("Second Node", root, true);
		   var tmpNode3 = new YAHOO.widget.TextNode("Third Node", root, false);
		   var tmpNode4 = new YAHOO.widget.TextNode("Fourth Node", root, true);
		   var tmpNode5 = new YAHOO.widget.TextNode("Fifth Node", root, false);

		   var tmpNode6 = new YAHOO.widget.TextNode("Sixth Node", root, true);
           tmpNode6.setDynamicLoad(function(node, loadCompleteCallback) {
                  loadCompleteCallback();
               }, 1);
		   
		   //render tree with these five nodes; all descendants of these nodes
		   //will be generated as needed by the dynamic loader.
		   tree.draw();
		}


	return {
		init: function() {
new YAHOO.widget.LogReader();
            YAHOO.util.Event.on(["mode0", "mode1"], "click", changeIconMode);
            var el = document.getElementById("mode1");
            if (el && el.checked) {
                currentIconMode = parseInt(el.value);
            } else {
                currentIconMode = 0;
            }

            buildTree();
        }

	}
} ();

YAHOO.util.Event.addListener(window, "load", YAHOO.example.treeExample.init, YAHOO.example.treeExample,true)
</script>
<title>Dynamic TreeView Example</title>
</head>
<body id="yahoo" class="yui-skin-sam">


<!-- id: optional property or feature signature -->
<div id="doc" class="yui-t5"><!-- possible values: t1, t2, t3, t4, t5, t6, t7 -->
	<div id="hd">
		<h1>TreeView Example</h1>
	</div>
	<div id="bd">

		<!-- start: primary column from outer template -->
		<div id="yui-main">
			<div class="yui-b">
				<p>In
this example, the TreeView control's dyamic loading functionality is
explored. Dynamic loading of child nodes allows you to optmize
performance by only loading data for and creating the nodes that will
be visible when the tree is rendered. Nodes that are not expanded when
the Tree's draw method is invoked are left childless in the initial
state. When such a node is expanded (either by user action or by
script), a dynamic loader function is called. That function has three
important roles:</p>
				<ol>
				  <li><strong>Check for child nodes:</strong>
The dynamic loader function will check for child nodes by evaluating
in-page data (for example, data held in a JavaScript array or object)
or by retrieving data about the expanding node from the server via
XMLHttpRequest. In the example on this page, an in-page random list
generator is used to generate the Tree structure. </li>
				  <li><strong>Add child nodes, if present:</strong>
If it determines that child node's are present for the expanding node,
the dynamic loader must add those child nodes to the Tree instance.
Because these nodes are only added when needed, the overall complexity
of the Tree's complexity (in JavaScript and in the DOM) is reduced and
its initial render time is much faster.</li>
				  <li><strong>Invoke the expanding node's callback method:</strong>
Once the dynamic loader method determines whether the expanding node
has children (and adds any children that may be present), it must
notify the expanding node's object that dynamic loading is complete. It
does this via a callback method which is passed into the dynamic loader
as an argument.</li>
			  </ol>

			  <h3>Creating a Dynamic Loader Method</h3>
			  <p>In
this example, our dynamic loader method will accomplish its first task
(checking for child nodes) by using a random number generator; we'll
specify that roughly 70% of our nodes have children. When there are
children present, there will be children will between one and six
children (also randomly enumerated) whose labels are drawn from an
array of Indian states.</p>

<p>Our method, which we'll call <code>loadNodeData</code>, will be
passed two arguments by the Tree instance when called: The first is a
reference to the expanding node's node object; the second is the
callback method that we need to call when we're done adding children to
the expanding node. The method as it appears on this page (only the
array of state names has been truncated) follows, with comments
glossing each step:</p>

<pre><textarea name="code" class="JScript" cols="60" rows="1">loadNodeData: function(node, fnLoadComplete) {

	//Array of India's States
	var aStates = ["Andhra Pradesh",
		"Arunachal Pradesh","Assam",
		...
		];

	//Random number determines whether a node has children
	var index = Math.round(Math.random()*100);
	
	//if our random number is in range, we'll pretend that this node
	//has children; here, we'll indicate that 70% of nodes have
	//children.
	if (index&gt;30) {
		//We'll use a random number to determine the number of
		//children for each node:
		var childCount = (Math.round(Math.random()*5) + 1);
		
		//This is important: The primary job of the data loader function
		//is to determine whether the node has children and then to 
		//actually create the child nodes if they are needed; here, we'll
		//loop through to create each child node:
		for (var i=0; i&lt;childCount; i++) {
			thisState = aStates[Math.round(Math.random()*27)];
			var newNode = new YAHOO.widget.TextNode(thisState, node, false);
		}
	}
			
	//When we're done creating child nodes, we execute the node's
	//loadComplete callback method which comes in as our loader's
	//second argument (we could also access it at node.loadComplete,
	//if necessary):
	fnLoadComplete();
}</textarea></pre>
	
				<h3>Setting Up the Tree Instance and Configuring It for Dynamic Loading</h3>
				<p>Creating
the initial state of a Tree object that will be configured for dynamic
loading is no different than for non-dynamic Tree instances &#8212; use the
Tree constructor to create your new instance:</p>

<pre><textarea name="code" class="JScript" cols="60" rows="1">//create a new tree:
tree = new YAHOO.widget.TreeView("treeContainer");</textarea></pre>

<p>In the example on this page, the entire tree is configured for
dynamic loading. That will result in all nodes having their children
populated by the dynamic loader method when they are expanded for the
first time. (You can also choose to specify individual nodes and their
descendants as being dynamically loaded.) To the Tree instance for
dynamic loading, merely pass the instance's <code>setDynamicLoad</code> method a reference to your dynamic loader method:</p>

<pre><textarea name="code" class="JScript" cols="60" rows="1">//turn dynamic loading on for entire tree:
tree.setDynamicLoad(this.loadNodeData);
</textarea></pre>

<p>Having created a Tree instance and configured it for dynamic
loading, we can now add the tree's top-level nodes and then render the
Tree via its <code>draw</code> method:</p>

<pre><textarea name="code" class="JScript" cols="60" rows="1">//add child nodes for tree:
var tmpNode1 = new YAHOO.widget.TextNode("First Node", root, false);
var tmpNode2 = new YAHOO.widget.TextNode("Second Node", root, false);
var tmpNode3 = new YAHOO.widget.TextNode("Third Node", root, false);
var tmpNode4 = new YAHOO.widget.TextNode("Fourth Node", root, false);
var tmpNode5 = new YAHOO.widget.TextNode("Fifth Node", root, false);

//render tree with these five nodes; all descendants of these nodes
//will be generated as needed by the dynamic loader.
tree.draw();
</textarea></pre>

<p>With that, our tree renders on the page, showing its five top-level
nodes. As the user interacts with the tree, child nodes will be added
and displayed based on the output of the <code>loadNodeData</code> method.</p>
<h3>Childless Node Style</h3><p>There are two built-in visual treatments for
childless nodes.  Before a dynamically loaded node is expanded, its icon
indicates that it can be expanded &mdash; this reflects the possibility that
the dynamic loader will find and populate children for that node if it is
expanded.  However, once the Tree determines that a node has no children, it
can reflect the childless state either through the "expanded" icon (<img
        src="">) or by omitting the icon entirely.  In this example, we've
added a control that enables you to experiment with each setting to explore its
visual impact</p><p>The default visual treatment for a childless node is the
"expanded" icon.  To change this setting, pass a second argument to your
<code>setDynamicLoad</code> method &mdash; pass a value of <code>1</code> to
use the iconless visual treatment.</p> </div> </div> <!-- end: primary column
from outer template -->
		
        <!-- start: secondary column from outer template --> <div
        class="yui-b"> <h3>Dynamically Loaded TreeView:</h3> <div
        id="treeContainer"></div> <h3>Childless Node Style:</h3> <dd> <label>
        <input type="radio" id="mode0" name="mode" value ="0" checked />
        Expand/Collapse </label> </dd> <dd> <label> <input type="radio"
        id="mode1" name="mode" value ="1" /> Leaf Node </label> </dd> </div>
        <!-- end: secondary column from outer template -->
		
    </div> <div id="ft"> </div> </div> <script
    src="js/dpSyntaxHighlighter.js"></script> <script language="javascript">
    dp.SyntaxHighlighter.HighlightAll('code'); </script> </body></html>
