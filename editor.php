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

$tpl = new IntegratedTemplate($TPLPATH);
$tpl->loadTemplateFile("tpl.editor.html", false, true);

$tpl->setVariable("TXT_PAGEHEADLINE","Edit Lessons");

$tplbtn = new IntegratedTemplate($TPLPATH);
$tplbtn->loadTemplateFile("tpl.buttons.html", true, true);
$tplbtn->setCurrentBlock("btn_cell");
$tplbtn->setVariable("BTN_LINK",".php");
$tplbtn->setVariable("BTN_TXT","Test/Intern");

$tplbtn->parseCurrentBlock();
$tplbtn->setCurrentBlock("btn_cell");
$tplbtn->setVariable("BTN_LINK",".php");
$tplbtn->setVariable("BTN_TXT","Create New Lesson");

$tplbtn->parseCurrentBlock();
$tplbtn->setCurrentBlock("btn_cell");
$tplbtn->setVariable("BTN_LINK",".php");
$tplbtn->setVariable("BTN_TXT","edit Courses");

$tplbtn->parseCurrentBlock();
$tplbtn->setCurrentBlock("btn_row");
$tplbtn->parseCurrentBlock();

$tpl->setVariable("BUTTONS",$tplbtn->get());

for ($i = 0; $i < 5; $i++)
{
	$tpl->setCurrentBlock("row");
	$tpl->setVariable("ROWCOL","tblrow".(($i%2)+1));
	$tpl->setVAriable("DATE",date("d.m.Y H:i:s"));
	$tpl->setVAriable("TITLE","Lesson".$i);
	$tpl->parseCurrentBlock();
}
$tpl->setCurrentBlock("tbl_lo");
$tpl->setVariable("LO_HEADER","Last Visited Lessons");
$tpl->setVariable("LO_HDR_TIME","Time");
$tpl->setVariable("LO_HDR_TITLE","Lesson");
$tpl->setVariable("LO_HDR_PAGE","Page");
$tpl->parseCurrentBlock();

$tplmain->setVariable("PAGECONTENT",$tpl->get());
$tplmain->show();

?>