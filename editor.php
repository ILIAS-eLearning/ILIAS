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

for ($i = 0; $i < 5; $i++)
{
	$tpl->setCurrentBlock("row");
	$tpl->setVariable("ROWCOL", "tblrow".(($i%2)+1));
	$tpl->setVAriable("DATE", date("d.m.Y H:i:s"));
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