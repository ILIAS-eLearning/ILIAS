<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN" "http://www.w3.org/TR/REC-html40/loose.dtd">

<html>
<head>
<title></title>
<link rel="stylesheet" type="text/css" href="ilias.css">
</head>
<body link="#000099" alink="#000099" vlink="#000099" marginwidth="0" marginheight="0">

<?php
require_once "PEAR.php";
require_once "DB.php";
require_once "Auth/Auth.php";
require_once "classes/class.ilias.php";
include_once "function.library.php";
include_once "classes/class.util.php";
include_once "classes/class.tree.php";

$ilias =& new ILIAS;

if (empty($id))
{
	$id = 1;
}

$nodes = array();
$params = explode("|",$id);
$id = $params[0];

foreach ($params as $val)
{
	$nodes[] = $val;
}

$tree = new Tree(0,1,1);

// display tree
$Tree = $tree->buildTree($nodes);

// get tree information			
$exp_data = $tree->display($Tree,$id,0,$open);

$explorer .= "<table cellpadding=\"0\" cellspacing=\"0\" border=\"0\">\n";
$explorer .= "<tr>\n";
$explorer .= "<td nowrap align=\"left\"><img src=\"./images/icon_cat.gif\" border=\"0\"></td>\n";$a = 0;
$explorer .= "<td nowrap align=\"left\"><a href=\"content.php?obj_id=".$tree->RootId."&parent=".$a."\" target=\"content\">Root</a></td>\n";
$explorer .= "</tr>\n";
$explorer .= "</table>\n";
			
foreach ($exp_data as $node_id => $node_info)
{
	$explorer .= "<table cellpadding=\"0\" cellspacing=\"0\" border=\"0\">\n";
	$explorer .= "<tr>\n";
	
	if (count($node_info["tab"] > 0))
	{
		foreach ($node_info["tab"] as $tab)
		{
			$col .= "<img src=\"./images/browser/".$tab.".gif\" border=\"0\">";
		}
	}
	
	if (!empty($node_info["expstr"]))
	{
		$col .= "<a href=\"".$PHP_SELF.$node_info["expstr"]."\"><img src=\"./images/browser/".$node_info["expander"]."\" border=\"0\"></a>";
	}
	
	$col .= "<img src=\"./images/icon_".$node_info["icon"].".gif\" border=\"0\">";

	$explorer .= "<td nowrap>".$col."</td>\n";
	$explorer .= "<td nowrap align=\"left\"><a href=\"content.php".$node_info["open"]."\" target=\"content\">".$node_info["title"]."</a></td>\n";
	$explorer .= "</tr>\n";
	$explorer .= "</table>\n";
	
	$col = "";
}

echo $explorer;

?>

</body>
</html>
