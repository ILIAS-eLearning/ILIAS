<?php
require_once "include/ilias_header.inc";

// Template generieren

$tpl->addBlockFile("CONTENT", "content", "tpl.adm_content.html");
$tpl->addBlockFile("ADM_CONTENT", "adm_content", "tpl.adm_operations.html");
$tpl->addBlockFile("LOCATOR", "locator", "tpl.adm_locator.html");

//show tabs
$o = array();
$o["LINK1"] = "content.php?obj_id=".$_GET["obj_id"]."&parent=".$_GET["parent"]."&parent_parent=".$_GET["parent_parent"];
$o["LINK2"] = "./object.php?obj_id=".$_GET["obj_id"]."&parent=".$_GET["parent"]."&cmd=edit";
$o["LINK3"] = "./object.php?obj_id=".$_GET["obj_id"]."&parent=".$_GET["parent"]."&cmd=perm";
$o["LINK4"] = "./object.php?obj_id=".$_GET["obj_id"]."&parent=".$_GET["parent"]."&cmd=owner";
$tpl->setVariable("TABS", TUtil::showTabs(1,$o));

// display path
$path = $tree->showPath($tree->getPathFull($_GET["parent"],$_GET["parent_parent"]),"content.php");

$tpl->setCurrentBlock("locator");
$tpl->setVariable("TXT_PATH", $lng->txt("path"));
$tpl->setVariable("TREEPATH",$path);
$tpl->parseCurrentBlock();

$tpl->setVariable("CMD","save");
$tpl->setVariable("OBJ_ID",$_GET["obj_id"]);
$tpl->setVariable("TPOS",$_GET["parent"]);		
$tpl->setVariable("PAR",$_GET["parent_parent"]);

// determine sort direction
if (!$_GET["direction"] || $_GET["direction"] == 'ASC')
{
	$tpl->setVariable("DIR",'DESC');
}
if ($_GET["direction"] == 'DESC')
{
	$tpl->setVariable("DIR",'ASC');
}

$ops_valid = $rbacadmin->getOperationsOnType($_GET["obj_id"]);

if ($ops_arr = getOperationList('',$_GET["order"],$_GET["direction"]))
{
	$options = array("e" => "enabled","d" => "disabled");

	foreach ($ops_arr as $key => $ops)
	{
		// BEGIN ROW
		$tpl->setCurrentBlock("row");
		if (in_array($ops["ops_id"],$ops_valid))
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
		$css_row = TUtil::switchColor($key, "tblrow1", "tblrow2");

		$tpl->setVariable("LINK_TARGET","object.php?obj_id=".$_GET["obj_id"]."&parent=".$_GET["parent"]."&cmd=edit");	
		$tpl->setVariable("OPS_TITLE",$ops["operation"]);
		$tpl->setVariable("OPS_DESC",$ops["desc"]);
		$tpl->setVariable("IMG_TYPE","icon_perm_b.gif");
		$tpl->setVariable("ALT_IMG_TYPE","ops");
		$tpl->setVariable("CSS_ROW",$css_row);
		$tpl->setVariable("OPS_ID",$ops["ops_id"]);
		$tpl->setVariable("OPS_STATUS",$ops_options);
		$tpl->parseCurrentBlock();
	}

	$tpl->touchBlock("options");
}
else
{
	$tpl->setCurrentBlock("notfound");
	$tpl->setVariable("MESSAGE","No Permission to read");
	$tpl->parseCurrentBlock();
}

if ($_GET["message"])
{
	$tpl->setCurrentBlock("sys_message");
	$tpl->setVariable("ERROR_MESSAGE",stripslashes($_GET["message"]));
	$tpl->parseCurrentBlock();
}

$tpl->setVariable("CONTENT", $tpl->get());	
$tpl->show();

?>