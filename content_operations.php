<?php
include_once "include/ilias_header.inc";

$rbacadmin = new RbacAdminH($ilias->db);
$tree = new Tree($_GET["obj_id"],1,1);

// Template generieren
$tplContent = new Template("content_operations.html",true,true);
$tplContent->setVariable($ilias->ini["layout"]);
// Show path
$path = $tree->showPath($tree->getPathFull($_GET["parent"],1),"content.php");
$tplContent->setVariable("TREEPATH",$path);

$tplContent->setVariable("OBJ_SELF","content.php?obj_id=$obj_id&parent=$parent");
$tplContent->setVariable("CMD","save");
$tplContent->setVariable("OBJ_ID",$_GET["obj_id"]);
$tplContent->setVariable("TPOS",$_GET["parent"]);		

// determine sort direction
if(!$_GET["direction"] || $_GET["direction"] == 'ASC')
{
	$tplContent->setVariable("DIR",'DESC');
}
if($_GET["direction"] == 'DESC')
{
	$tplContent->setVariable("DIR",'ASC');
}

// BEGIN ROW
$tplContent->setCurrentBlock("row",true);

$ops_valid = $rbacadmin->getOperationsOnType($_GET["obj_id"]);
if($ops_arr = getOperationList('',$_GET["order"],$_GET["direction"]))
{
	$options = array("e" => "enabled","d" => "disabled");
	foreach ($ops_arr as $key => $ops)
	{
		if(in_array($ops["ops_id"],$ops_valid))
		{
			$ops_status = 'e';
		}
		else
		{
			$ops_status = 'd';
		}
		$obj = $ops["ops_id"];
		$ops_options = TUtil::formSelect($ops_status,"id[$obj]",$options);
		
		// color changing
		if ($key % 2)
		{
			$css_row = "row_high";	
		}
		else
		{
			$css_row = "row_low";
		}

		$tplContent->setVariable("LINK_TARGET","object.php?obj_id=".$obj_id."&parent=".$parent."&cmd=edit");	
		$tplContent->setVariable("OPS_TITLE",$ops["operation"]);
		$tplContent->setVariable("OPS_DESC",$ops["desc"]);
		$tplContent->setVariable("IMG_TYPE","icon_perm_b.gif");
		$tplContent->setVariable("ALT_IMG_TYPE","ops");
		$tplContent->setVariable("CSS_ROW",$css_row);
		$tplContent->setVariable("OPS_ID",$ops["ops_id"]);
		$tplContent->setVariable("OPS_STATUS",$ops_options);
		$tplContent->parseCurrentBlock("row");
	}
	$tplContent->touchBlock("options");
}
else
{
	$tplContent->setCurrentBlock("notfound");
	$tplContent->setVariable("MESSAGE","No Permission to read");
	$tplContent->parseCurrentBlock();
}
if($_GET["message"])
{
	$tplContent->setCurrentBlock("sys_message");
	$tplContent->setVariable("ERROR_MESSAGE",$_GET["message"]);
	$tplContent->parseCurrentBlock();
}

include_once "include/ilias_footer.inc";
?>