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

$tpl = new Template("tpl.lo_edit.html", false, true);

$tpl->setVariable("TXT_PAGEHEADLINE", $lng->txt("lo_edit"));

include("./include/inc.lo_buttons.php");

$tpl->setVariable("TXT_SEQUENCES", $lng->txt("sequences"));
$tpl->setVariable("TXT_ONLINE_CHAPTER", $lng->txt("online_chapter"));
$tpl->setVariable("TXT_VISIBLE_LAYERS", $lng->txt("visible_layers"));
$tpl->setVariable("TXT_REFRESH", $lng->txt("refresh"));
$tpl->setVariable("TXT_FAQ_EXERCISE", $lng->txt("faq_exercise"));
$tpl->setVariable("TXT_CHAPTER_NUMBER", $lng->txt("chapter_number"));
$tpl->setVariable("TXT_CHAPTER_TITLE", $lng->txt("chapter_title"));
$tpl->setVariable("TXT_SEQUENCE", $lng->txt("sequence"));
$tpl->setVariable("TXT_FORUM", $lng->txt("forum"));

//$lng->txt("no_title")

$row = array();
$row["nr"] = "1";
$row["title"] = "test1";
$row["status"] = "on";

$tpl->setVariable("FORMACTION", "lo_edit.php");
$tpl->setVariable("SEQ_CHECKED", "checked");
$tpl->setVariable("ONL_CHECKED", "checked");

$tpl->setCurrentBlock("level");
$tpl->setVariable("SEL_VALUE", "tblrow".(($i%2)+1));
$tpl->setVariable("SEL_OPTION", "1");
$tpl->setVariable("SEL_SELECTED", "selected");
$tpl->parseCurrentBlock();

$tpl->setCurrentBlock("row");
$tpl->setVariable("ROWCOL", "tblrow".(($i%2)+1));
$tpl->setVariable("NUMBER", $row["nr"]);
$tpl->setVariable("TITLE", $row["title"]);
$tpl->setVariable("STATUS", $row["status"]);
$tpl->setVariable("TXT_STATUS", $lng->txt("status"));
if ($row["status"] == "on")
	$switchstatus = "off";
else
	$switchstatus = "on";
$tpl->setVariable("LINK_SWITCHSTATUS", "lo_edit.php?set=".$switchstatus."&amp;lo=".$lo."&amp;id=".$row["nr"]);
$tpl->setVariable("TXT_STATUS", $lng->txt("set_".$switchstatus."line"));

//subchapter
$tpl->setVariable("TXT_NEWSUBCHAPTER", $lng->txt("subchapter_new"));
$tpl->setVariable("LINK_NEWSUBCHAPTER", "lo_edit.php?cmd=newsubchapter&amp;lo=".$lo."&amp;id=".$row["nr"]);

//edit title
$tpl->setVariable("TXT_EDIT", $lng->txt("edit"));
$tpl->setVariable("LINK_EDIT", "lo_edit.php?cmd=edittitle&amp;lo=".$lo."&amp;id=".$row["nr"]);


//enumeration
$tpl->setVariable("ENUMERATE_STATUS", "on");
$tpl->setVariable("TXT_ENUMERATE", $lng->txt("enumerate"));
$tpl->setVariable("LINK_ENUMERATE", "lo_edit.php?cmd=setenumeration&amp;value=1&amp;lo=".$lo."&amp;id=".$row["nr"]);

$tpl->parseCurrentBlock();

$tplmain->setVariable("PAGECONTENT",$tpl->get());
$tplmain->show();

?>