<?php
/**
* bookmark view
*
* @author Peter Gabriel <pgabriel@databay.de>
* @version $Id$
* 
* @package application
*/
require_once "./include/inc.header.php";
require_once "./classes/class.Bookmarks.php";

$myBm = new Bookmarks($ilias->account->Id);

$tplbtn = new Template("tpl.buttons.html", true, true);
$tplbtn->setCurrentBlock("btn_cell");
$tplbtn->setVariable("BTN_LINK","bookmark_newfolder.php");
$tplbtn->setVariable("BTN_TXT", $lng->txt("new_folder"));
$tplbtn->parseCurrentBlock();
$tplbtn->setCurrentBlock("btn_cell");
$tplbtn->setVariable("BTN_LINK","bookmark_new.php");
$tplbtn->setVariable("BTN_TXT", $lng->txt("bookmark_new"));
$tplbtn->parseCurrentBlock();
$tplbtn->setCurrentBlock("btn_row");
$tplbtn->parseCurrentBlock();

$tpl = new Template("tpl.bookmark_newfolder.html", true, true);
$tpl->setVariable("BUTTONS",$tplbtn->get());

$tplmain->setVariable("PAGETITLE", "ILIAS - ".$lng->txt("bookmarks"));
$tpl->setVariable("TXT_PAGEHEADLINE", $lng->txt("bookmarks"));
$tpl->setVariable("TXT_URL", $lng->txt("url"));
$tpl->setVariable("TXT_DESCRIPTION", $lng->txt("description"));

$bmf = $myBm->getFolders();

foreach ($bmf as $row)
{
	$i++;
	$tpl->setCurrentBlock("selfolders");
	$tpl->setVariable("SEL_OPTION", $row["name"]);
	$tpl->setVariable("SEL_VALUE", $row["id"]);
	$tpl->parseCurrentBlock();
}

$tpl->setVariable("TXT_TOP", $lng->txt("top"));
$tpl->setVariable("TXT_NAME", $lng->txt("name"));
$tpl->setVariable("TXT_CREATE_IN_FOLDER", $lng->txt("create_in"));
$tpl->setVariable("TXT_SAVE", $lng->txt("save"));
$tpl->setVariable("TXT_FOLDER_NEW", $lng->txt("new_folder"));

$tplmain->setVariable("PAGECONTENT",$tpl->get());
$tplmain->show();
?>