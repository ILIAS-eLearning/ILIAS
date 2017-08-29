<!doctype html public "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>

<head>
<title>Yahoo! UI Library - Tree Control</title>
<link rel="stylesheet" type="text/css" href="css/screen.css"></link>
</head>

<body>
<link rel="stylesheet" type="text/css" href="css/code.css"></link>
<link rel="stylesheet" type="text/css" href="css/folders/tree.css"></link>

<style type="text/css">
    #treewrapper {position:relative;}
	#treediv {position:relative; width:250px;}
	#figure1 {float:right;background-color:#FFFCE9;padding:1em;border:1px solid gray}
    .icon-ppt { display:block; padding-left: 20px; background: transparent url(img/icons.png) 0 0px no-repeat; }
    .icon-dmg { display:block; padding-left: 20px; background: transparent url(img/icons.png) 0 -36px no-repeat; }
    .icon-prv { display:block; padding-left: 20px; background: transparent url(img/icons.png) 0 -72px no-repeat; }
    .icon-gen { display:block; padding-left: 20px; background: transparent url(img/icons.png) 0 -108px no-repeat; }
    .icon-doc { display:block; padding-left: 20px; background: transparent url(img/icons.png) 0 -144px no-repeat; }
    .icon-jar { display:block; padding-left: 20px; background: transparent url(img/icons.png) 0 -180px no-repeat; }
    .icon-zip { display:block; padding-left: 20px; background: transparent url(img/icons.png) 0 -216px no-repeat; }
</style>

<?php include('inc-alljs.php'); ?>
<script>

//Wrap our initialization code in an anonymous function
//to keep out of the global namespace:
(function(){
	var init = function() {

		//create the TreeView instance:
		var tree = new YAHOO.widget.TreeView("treediv");

		//get a reusable reference to the root node:
		var root = tree.getRoot();

		//for Ahmed's documents, we'll use TextNodes.
		//First, create a parent node for his documents:
		var ahmedDocs = new YAHOO.widget.TextNode("Ahmed's Documents", root, true);
			//Create a child node for his Word document:
			var ahmedMsWord = new YAHOO.widget.TextNode("Prospectus", ahmedDocs, false);
			//Now, apply the "icon-doc" style to this node's
			//label:
			ahmedMsWord.labelStyle = "icon-doc";
			var ahmedPpt = new YAHOO.widget.TextNode("Presentation", ahmedDocs, false);
			ahmedPpt.labelStyle = "icon-ppt";
			var ahmedPdf = new YAHOO.widget.TextNode("Prospectus-PDF version", ahmedDocs, false);
			ahmedPdf.labelStyle = "icon-prv";

		//for Susheela's documents, we'll use HTMLNodes.
		//First, create a parent node for her documents:
		var sushDocs = new YAHOO.widget.TextNode("Susheela's Documents", root, true);
			//Create a child node for her zipped files:
			var sushZip = new YAHOO.widget.HTMLNode("Zipped Files", sushDocs, false, true);
			//Now, apply the "icon-zip" style to this HTML node's
			//content:
			sushZip.contentStyle = "icon-zip";
			var sushDmg = new YAHOO.widget.HTMLNode("Files -- .dmg version", sushDocs, false, true);
			sushDmg.contentStyle = "icon-dmg";
			var sushGen = new YAHOO.widget.HTMLNode("Script -- text version", sushDocs, false, true);
			sushGen.contentStyle = "icon-gen";
			var sushJar = new YAHOO.widget.HTMLNode("JAR file", sushDocs, false, true);
			sushJar.contentStyle = "icon-jar";

		tree.draw();
	}
	YAHOO.util.Event.on(window, "load", init);
})();
</script>


</head>

<body>

<div id="pageTitle">
	<h3>Tree Control</h3>
</div>

<?php include('inc-rightbar.php'); ?>

  <div id="content">
	<div class="newsItem">
          <img id="figure1" src="img/icons.png" width="18" height="252" alt="Our icon images are combined into a single png file to reduce HTTP requests." />
	  <h3>Custom Icons in the Tree Control</h3>
	  <p></p>

      <div id="treewrapper">
	    <div id="treediv"> </div>
      </div>

<p id="clear">Many implementations of tree-style controls call for using custom
icons on a per-node basis.  In this example, we'll look at one strategy for
apply custom icons to specific nodes using the <a
href="http://developer.yahoo.com/yui/treeview/">YUI TreeView Control</a>.</p>

<p>We'll start by using a single image containing our icon set and we'll use
the technique known as "<a
href="http://www.alistapart.com/articles/sprites">CSS Sprites</a>" to specify
which icon we want to use for each specific style.  This allows us to combine
seven images in a single HTTP request (<a
        href="http://yuiblog.com/blog/2006/11/28/performance-research-part-1/">read more about why reducing HTTP requests is a good idea</a>).
The raw image is displayed to the right of the tree.</p>


<p>With that image in place, we can now set up our style rules to identify icons for each file type.
We do that by positioning our <code>icons.png</code> image uniquely for each icon we want to display:</p>

<pre><textarea name="code" class="HTML" cols="60" rows="1"><style type="text/css">
.icon-ppt { padding-left: 20px; background: transparent url(img/icons.png) 0 0px no-repeat; }
.icon-dmg { padding-left: 20px; background: transparent url(img/icons.png) 0 -36px no-repeat; }
.icon-prv { padding-left: 20px; background: transparent url(img/icons.png) 0 -72px no-repeat; }
.icon-gen { padding-left: 20px; background: transparent url(img/icons.png) 0 -108px no-repeat; }
.icon-doc { padding-left: 20px; background: transparent url(img/icons.png) 0 -144px no-repeat; }
.icon-jar { padding-left: 20px; background: transparent url(img/icons.png) 0 -180px no-repeat; }
.icon-zip { padding-left: 20px; background: transparent url(img/icons.png) 0 -216px no-repeat; }
</style></textarea></pre>

<p>The effect of these style rules is to create a 20-pixel space to the left of the styled object and to place the icon directly in that space.  The sheet of icons is positioned so that, for example, the zip-file icon will appear when the class <code>icon-zip</code> is applied.</p>

<p>To apply these styles on a per-node basis in TreeView, we use the <a href="http://developer.yahoo.com/yui/docs/YAHOO.widget.TextNode.html#labelStyle">labelStyle</a> property of <a href="http://developer.yahoo.com/yui/docs/YAHOO.widget.TextNode.html">TextNodes</a> and <a href="http://developer.yahoo.com/yui/docs/YAHOO.widget.MenuNode.html">MenuNodes</a> and the <a href="http://developer.yahoo.com/yui/docs/YAHOO.widget.HTMLNode.html#contentStyle">contentStyle</a> property of <a href="http://developer.yahoo.com/yui/docs/YAHOO.widget.HTMLNode.html">HTMLNodes</a>.</p>

<p>Here is the code used to create the TreeView instance above and to create the first node, "Ahmed's Documents," while applying the specific icon styles to each node:</p>

<pre><textarea name="code" class="JScript" cols="60" rows="1">//create the TreeView instance:
var tree = new YAHOO.widget.TreeView("treediv");

//get a reusable reference to the root node:
var root = tree.getRoot();

//for Ahmed's documents, we'll use TextNodes.
//First, create a parent node for his documents:
var ahmedDocs = new YAHOO.widget.TextNode("Ahmed's Documents", root, true);
	//Create a child node for his Word document:
	var ahmedMsWord = new YAHOO.widget.TextNode("Prospectus", ahmedDocs, false);
	//Now, apply the "icon-doc" style to this node's
	//label:
	ahmedMsWord.labelStyle = "icon-doc";
	var ahmedPpt = new YAHOO.widget.TextNode("Presentation", ahmedDocs, false);
	ahmedPpt.labelStyle = "icon-ppt";
	var ahmedPdf = new YAHOO.widget.TextNode("Prospectus-PDF version", ahmedDocs, false);
	ahmedPdf.labelStyle = "icon-prv";</textarea></pre>

<p>The script for creating Susheela's part of the tree is very similar.  Here, we'll use HTMLNodes, and we'll use the <code>contentStyle</code> property to apply the icon style:</p>

<pre><textarea name="code" class="JScript" cols="60" rows="1">//for Susheela's documents, we'll use HTMLNodes.
//First, create a parent node for her documents:
var sushDocs = new YAHOO.widget.TextNode("Susheela's Documents", root, true);
	//Create a child node for her zipped files:
	var sushZip = new YAHOO.widget.HTMLNode("Zipped Files", sushDocs, false, true);
	//Now, apply the "icon-zip" style to this HTML node's
	//content:
	sushZip.contentStyle = "icon-zip";
	var sushDmg = new YAHOO.widget.HTMLNode("Files -- .dmg version", sushDocs, false, true);
	sushDmg.contentStyle = "icon-dmg";
	var sushGen = new YAHOO.widget.HTMLNode("Script -- text version", sushDocs, false, true);
	sushGen.contentStyle = "icon-gen";
	var sushJar = new YAHOO.widget.HTMLNode("JAR file", sushDocs, false, true);
	sushJar.contentStyle = "icon-jar";</textarea></pre>

<p>Note that in this example we're also applying <a href="http://developer.yahoo.com/yui/examples/treeview/css/folders/tree.css">the "folder style" CSS file</a> that is included with the TreeView Control's examples; you can find that file in <a href="http://developer.yahoo.com/yui/download/">the YUI distribution</a> under <code>/examples/treeview/css/folders/tree.css</code>.</p>
</div>
</div>
	</div>
  </div>

      <div id="footerContainer">
        <div id="footer">
          <p>&nbsp;</p>
        </div>
      </div>
    </div>
  </div>
</div>

<!--apply syntax highlighting-->
<script src="js/dpSyntaxHighlighter.js"></script>
<script language="javascript">
dp.SyntaxHighlighter.HighlightAll('code');
</script>
</body>
</html>

