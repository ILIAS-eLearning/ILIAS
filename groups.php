<?php
/**
 * groups
 *
 * @author Peter Gabriel <pgabriel@databay.de>
 * @package ilias
 * @version $Id$
 */
include_once("./include/ilias_header.inc");
include("./include/inc.main.php");


$grp_sys[] = array("name" => "Administrator",
				"desc" => "System Administrators",
				"owner" => "System Administrator [root]"
			);

$tplbtn = new Template("tpl.buttons.html", true, true);
$tplbtn->setCurrentBlock("btn_cell");
$tplbtn->setVariable("BTN_LINK","");
$tplbtn->setVariable("BTN_TXT","new group");
$tplbtn->parseCurrentBlock();
$tplbtn->setCurrentBlock("btn_row");
$tplbtn->parseCurrentBlock();

$tpl = new Template("tpl.groups.html", false, true);
$tpl->setVariable("BUTTONS",$tplbtn->get());

$tpl->setVariable("TXT_PAGEHEADLINE","Groups");


$i=0;
foreach ($grp_sys as $row)
{
	$i++;
	$tpl->setCurrentBlock("group_row");
	$tpl->setVariable("ROWCOL","tblrow".(($i%2)+1));
	$tpl->setVariable("GRP_NAME", $row["name"]);
	$tpl->setVariable("GRP_DESC", $row["desc"]);
	$tpl->setVariable("GRP_OWNER", $row["owner"]);
	$tpl->parseCurrentBlock();
}

$tpl->setCurrentBlock("group");
$tpl->setVariable("TXT_GRP_TITLE","_System Groups");
$tpl->setVariable("TXT_NAME","_name");
$tpl->setVariable("TXT_DESC","_desc");
$tpl->setVariable("TXT_OWNER","_owner");

$tpl->parseCurrentBlock("group");

$tplmain->setVariable("PAGECONTENT",$tpl->get());
$tplmain->show();

?>