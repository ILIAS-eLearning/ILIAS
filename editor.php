<?php
/**
 * editor view
 *
 * @author Peter Gabriel <pgabriel@databay.de>
 * @package ilias
 * @version $Id$
 */
include_once("./include/ilias_header.inc");
include("./include/inc.main.php");

$tpl = new Template("tpl.editor.html", false, true);

$tpl->setVariable("TXT_PAGEHEADLINE", $lng->txt("lo_edit"));

$tplbtn = new Template("tpl.buttons.html", true, true);
$tplbtn->setCurrentBlock("btn_cell");
$tplbtn->setVariable("BTN_LINK","???.php");
$tplbtn->setVariable("BTN_TXT", $lng->txt("test_intern"));

$tplbtn->parseCurrentBlock();
$tplbtn->setCurrentBlock("btn_cell");
$tplbtn->setVariable("BTN_LINK","lo_new.php");
$tplbtn->setVariable("BTN_TXT", $lng->txt("lo_new"));

$tplbtn->parseCurrentBlock();
$tplbtn->setCurrentBlock("btn_cell");
$tplbtn->setVariable("BTN_LINK","crs_edit.php");
$tplbtn->setVariable("BTN_TXT", $lng->txt("courses"));

$tplbtn->parseCurrentBlock();
$tplbtn->setCurrentBlock("btn_row");
$tplbtn->parseCurrentBlock();

$tpl->setVariable("BUTTONS",$tplbtn->get());

$tpl->setVariable("TXT_TITLE", $lng->txt("title"));
$tpl->setVariable("TXT_ONLINE_VERSION", $lng->txt("online_version"));
$tpl->setVariable("TXT_OFFLINE_VERSION", $lng->txt("offline_version"));
$tpl->setVariable("TXT_PUBLISHED", $lng->txt("published"));

for ($i = 0; $i < 5; $i++)
{
	$id = $i;
	$tpl->setCurrentBlock("row");
	$tpl->setVariable("ROWCOL", "tblrow".(($i%2)+1));
	$tpl->setVAriable("DATE", date("d.m.Y H:i:s"));
	$tpl->setVAriable("TITLE", $lng->txt("lo").$i);
	$status = "on";
	$switchstatus = "off";
	$tpl->setVariable("LINK_STATUS", "editor.php?func=set".$switchstatus."line&amp;id=".$id);
	$tpl->setVariable("LINK_GENERATE", "editor.php?func=generate&amp;id=".$id);
	$tpl->setVariable("LINK_ANNOUNCE", "mail.php?func=announce&amp;id=".$id);
	$tpl->setVariable("LINK_EDIT", "lo_edit.php?id=".$id);
	$tpl->setVAriable("STATUS", $status);
	$tpl->setVariable("TXT_LO_SET_STATUS", $lng->txt("set_".$switchstatus."line"));
	$tpl->setVariable("TXT_ANNOUNCE", $lng->txt("announce"));
	$tpl->setVariable("TXT_GENERATE", $lng->txt("generate"));
	$tpl->setVariable("TXT_EDIT", $lng->txt("edit"));
	$tpl->setVAriable("TITLE", $lng->txt("lo").$i);
	
	$tpl->parseCurrentBlock();
}
$tpl->setCurrentBlock("tbl_lo");
$tpl->setVariable("LO_HEADER", $lng->txt("lo_last_visited"));
$tpl->setVariable("LO_HDR_TIME", $lng->txt("time"));
$tpl->setVariable("LO_HDR_TITLE", $lng->txt("lo"));
$tpl->setVariable("LO_HDR_PAGE", $lng->txt("page"));
$tpl->parseCurrentBlock();

$tplmain->setVariable("PAGECONTENT",$tpl->get());
$tplmain->show();

?>