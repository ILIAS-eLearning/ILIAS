<?php
include_once "include/ilias_header.inc";

//var_dump($_GET);
$rbacadmin = new RbacAdminH($ilias->db);

// Template generieren
$tplContent = new Template("content_operations.html",true,true);
$tplContent->setVariable($ilias->ini["layout"]);
// Show path
$tree = new Tree($_GET["obj_id"],1,1);
$path = showPath($tree->getPathFull(),"content.php");
$tplContent->setVariable("TREEPATH",$path);

$tplContent->setVariable("OBJ_SELF","content.php?obj_id=$obj_id&parent=$parent");
$tplContent->setVariable("CMD","save");
$tplContent->setVariable("OBJ_ID",$_GET["obj_id"]);
$tplContent->setVariable("TPOS",$_GET["parent"]);		

// BEGIN ROW
$tplContent->setCurrentBlock("row",true);

$ops_valid = $rbacadmin->getOperationsOnType($_GET["obj_id"]);
if($ops_arr = getOperationList())
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
		$tplContent->setVariable("IMG_TYPE","icon_type.gif");
		$tplContent->setVariable("ALT_IMG_TYPE","Operation");
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
include_once "include/ilias_footer.inc";
?>