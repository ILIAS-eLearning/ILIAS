<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
        "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
      <html xmlns="http://www.w3.org/1999/xhtml" lang="en" xml:lang="en">
<head>
<title>Yahoo! UI Library - Tree Control</title>
<link rel="stylesheet" type="text/css" href="css/screen.css" />
<link rel="stylesheet" type="text/css" href="css/local/tree.css" />
</head>
  
<body>

<div id="pageTitle">
	<h3>Tree Control</h3>
</div>

<?php include('inc-alljs.php'); ?>
<?php include('inc-rightbar.php'); ?>






<style>
.detMapColA { width:240px; float:left; }
.detMapColA .detMapLeftCol { background-color:#EBF0F4; border:solid 1px #7A9BB3; padding:10px; margin-bottom:10px; height:638px; }
.detMapLeftCol .thingsToDoTitle { font-weight:bold; margin-left:20px; width:180px; }
</style>

<!--
<style>
.ygtvtp, .ygtvtph, .ygtvlp, .ygtvlph {background: url(http://makeover.corp.yahoo.com/lib/common/widgets/2/treeview/examples/img/menu/expand.gif) 0 3px no-repeat; width:1em; height 22px; cursor:pointer; }
/*.ygtvtn, .ygtvln,*/ .ygtvtm, .ygtvtmh, .ygtvlm, .ygtvlmh {background: url(http://makeover.corp.yahoo.com/lib/common/widgets/2/treeview/examples/img/menu/collapse.gif) 0 3px no-repeat; width:1em; height 22px; cursor:pointer; }
.mapOverlayMenu .ygtvitem .overlayListItemNum { float:left; text-align:right; margin:0 8px 0 2px; width:18px; }
.mapOverlayMenu .ygtvitem .overlayListItemName { float:left; width:154px; }
#attrOverlayTopNodeGray, #restOverlayTopNodeGray, #shopOverlayTopNodeGray, #nightlifeOverlayTopNodeGray { color:gray; text-decoration:none; cursor:default; margin-left:12px; }
.ygtreeHTMLNode { font: 10px verdana; }
</style>
-->

<style>
.mapOverlayMenu .ygtvitem .overlayListItemNum { float:left; text-align:right; margin:0 8px 0 2px; width:18px; }
.mapOverlayMenu .ygtvitem .overlayListItemName { float:left; width:154px; }
#attrOverlayTopNodeGray, #restOverlayTopNodeGray, #shopOverlayTopNodeGray, #nightlifeOverlayTopNodeGray { color:gray; text-decoration:none; cursor:default; margin-left:12px; }
.ygtreeHTMLNode { font: 10px verdana; }
</style>

<div class='display'>
  <div class='detMapColA'>
    <div class='detMapLeftCol'>
      <div class='thingsToDoTitle'>See things to do near here</div>
      <div class='mapOverlayMenu' id='mapTreeView'></div>
    </div>
  </div>
</div>

<script>
var yfcMapTreeView = null;
var attrType = 'attr';
var restType = 'rest';
var shopType = 'shop';
var nightlifeType = 'nightlife';

var attrList = ['four', 'three', 'one', 'two'];
var restList = ['two', 'one', 'three', 'four'];
var nightlifeList = ['one', 'three', 'four', 'two'];

var treeData = { attr:attrList, rest:restList, nightlife:nightlifeList };

setupTreeView();

function setupTreeView() {

  yfcMapTreeView = new YAHOO.widget.TreeView('mapTreeView');
  var root = yfcMapTreeView.getRoot();
  var nodeObj;
  var node;
  var childNode;
  yfcMapTreeView.topNodes = new Array();

  // attractions
  nodeObj = {label:'Attractions', type:attrType, typeString:'attractions'};
  yfcMapTreeView.topNodes[attrType] = new YAHOO.widget.TextNode(nodeObj,root,false);
  yfcMapTreeView.topNodes[attrType].labelStyle = 'overlayTopNode';
    yfcMapTreeView.topNodes[attrType].labelElId = 'attrOverlayTopNode';
    nodeObj = {label:'empty', type:attrType};
    childNode = new YAHOO.widget.TextNode(nodeObj,yfcMapTreeView.topNodes[attrType],false);
  // restaurants
  nodeObj = {label:'Restaurants', type:restType, typeString:'restaurants'};
  yfcMapTreeView.topNodes[restType] = new YAHOO.widget.TextNode(nodeObj,root,false);
  yfcMapTreeView.topNodes[restType].labelStyle = 'overlayTopNode';
    yfcMapTreeView.topNodes[restType].labelElId = 'restOverlayTopNode';
    nodeObj = {label:'empty', type:restType};
    childNode = new YAHOO.widget.TextNode(nodeObj,yfcMapTreeView.topNodes[restType],false);
  // shopping
  nodeObj = {label:'Shopping', type:shopType, typeString:'shopping'};
  yfcMapTreeView.topNodes[shopType] = new YAHOO.widget.TextNode(nodeObj,root,false);
  yfcMapTreeView.topNodes[shopType].labelStyle = 'overlayTopNode';
    yfcMapTreeView.topNodes[shopType].labelElId = 'shopOverlayTopNodeGray';
  // nightlife
  nodeObj = {label:'Nightlife', type:nightlifeType, typeString:'nightlife'};
  yfcMapTreeView.topNodes[nightlifeType] = new YAHOO.widget.TextNode(nodeObj,root,false);
  yfcMapTreeView.topNodes[nightlifeType].labelStyle = 'overlayTopNode';
    yfcMapTreeView.topNodes[nightlifeType].labelElId = 'nightlifeOverlayTopNode';
    nodeObj = {label:'empty', type:nightlifeType};
    childNode = new YAHOO.widget.TextNode(nodeObj,yfcMapTreeView.topNodes[nightlifeType],false);


  yfcMapTreeView.onExpand = treeViewExpand;
  yfcMapTreeView.onCollapse = treeViewCollapse;
  yfcMapTreeView.draw();


}

function treeViewExpand(node) {
  while (node.children.length>0) {
    var childNode = node.children[0];
    yfcMapTreeView.removeNode(childNode);
  }
  for(var i=0;i<treeData[node.data.type].length;i++) {
    var currObj = treeData[node.data.type][i];
    var htmlEntry = '<div class="ygtreeHTMLNode" id="treeNode'+node.data.type+i+'"><div class="overlayListItemNum">'+(i+1)+'.</div><div class="overlayListItemName">'+currObj+'</div></div>';
    nodeObj = {html:htmlEntry};
    childNode = new YAHOO.widget.HTMLNode(nodeObj,node,false);
  }

  // node.refresh();
  // return false;
}

function treeViewCollapse(node) {
}

</script>


  </body>
</html>
 
