<?php
/**
 * bookmark view
 *
 * @author Peter Gabriel <pgabriel@databay.de>
 * @package ilias-layout
 * @version $Id$
 */
include_once("./include/ilias_header.inc");
include("./include/inc.main.php");

$tplbtn = new Template("tpl.buttons.html", true, true);
$tplbtn->setCurrentBlock("btn_cell");
$tplbtn->setVariable("BTN_LINK","bookmark_newfolder.php");
$tplbtn->setVariable("BTN_TXT","_New Folder");
$tplbtn->parseCurrentBlock();
$tplbtn->setCurrentBlock("btn_cell");
$tplbtn->setVariable("BTN_LINK","bookmark_new.php");
$tplbtn->setVariable("BTN_TXT","_New Bookmark");
$tplbtn->parseCurrentBlock();
$tplbtn->setCurrentBlock("btn_row");
$tplbtn->parseCurrentBlock();

$tpl = new Template("tpl.bookmarks.html", true, true);
$tpl->setVariable("BUTTONS",$tplbtn->get());

$tplmain->setVariable("PAGETITLE","_ILIAS - Bookmarks");
$tpl->setVariable("TXT_PAGEHEADLINE","_Bookmarks");

$bm = $ilias->account->getBookmarks();

foreach ($bm as $row)
{
	$i++;
	$tpl->setCurrentBlock("bookmarkrow");
	$tpl->setVariable("ROWCOL","tblrow".(($i%2)+1));
	$tpl->setVariable("URL", $row["url"]);
	$tpl->setVariable("DESC", $row["desc"]);
	$tpl->parseCurrentBlock();
}


$tplmain->setVariable("PAGECONTENT",$tpl->get());
$tplmain->show();

?>