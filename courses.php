<?php

/**
 * user view of courses
 *
 * @author Peter Gabriel <pgabriel@databay.de>
 * @package ilias-layout
 * @version $Id$
 */
include_once("./include/ilias_header.inc");
include("./include/inc.main.php");

$tpl = new IntegratedTemplate($TPLPATH);
$tpl->loadTemplateFile("tpl.courses.html", true, true);

$tplbtn = new IntegratedTemplate($TPLPATH);
$tplbtn->loadTemplateFile("tpl.buttons.html", true, true);
$tplbtn->setCurrentBlock("btn_cell");
$tplbtn->setVariable("BTN_LINK","lessons.php");
$tplbtn->setVariable("BTN_TXT","_lessons");
$tplbtn->parseCurrentBlock();
$tplbtn->setCurrentBlock("btn_row");
$tplbtn->parseCurrentBlock();

$tpl->setVariable("TXT_PAGEHEADLINE","Available Courses");
$tpl->setVariable("BUTTONS",$tplbtn->get());

for ($i = 0; $i<2; $i++) 
{
	for ($j = 0; $j<3; $j++) 
	{
		$tpl->setCurrentBlock("subcategory");	
		$tpl->setVariable("ROWCOL","tblrow".(($j%2)+1));
		$tpl->setVariable("TITLE","title".$j);
		$tpl->setVariable("IMG_AND_LINK","img".$j);
		$tpl->parseCurrentBlock();
	}
	$tpl->touchBlock("subcategory_others");
	$tpl->setCurrentBlock("category");
	$tpl->setVariable("CAT_TITLE","CATEGORY".$i);	
	$tpl->parseCurrentBlock();
	
}

$tplmain->setVariable("PAGECONTENT",$tpl->get());
$tplmain->show();

?>