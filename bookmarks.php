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

if ($_GET["cmd"] != "")
{
	switch ($_GET["cmd"])
	{
		case "del":
			$myBm->delete($id);
			break;
		case "edit":
			break;
	}

	header("location: bookmarks.php");
	exit;
}

$tpl->addBlockFile("CONTENT", "content", "tpl.bookmarks.html");
$tpl->addBlockFile("BUTTONS", "buttons", "tpl.buttons.html");

$tpl->setCurrentBlock("btn_cell");
$tpl->setVariable("BTN_LINK","bookmark_newfolder.php");
$tpl->setVariable("BTN_TXT", $lng->txt("new_folder"));
$tpl->parseCurrentBlock();
$tpl->setCurrentBlock("btn_cell");
$tpl->setVariable("BTN_LINK","bookmark_new.php");
$tpl->setVariable("BTN_TXT", $lng->txt("bookmark_new"));
$tpl->parseCurrentBlock();
$tpl->setCurrentBlock("btn_row");
$tpl->parseCurrentBlock();


$bm = $myBm->getBookmarkList();

foreach ($bm as $row)
{
	$i++;
	$tpl->setCurrentBlock("bookmarkrow");
	$tpl->setVariable("ROWCOL","tblrow".(($i%2)+1));
	$tpl->setVariable("URL", $row["url"]);
	$tpl->setVariable("DESC", $row["name"]);
	$tpl->setVariable("TXT_EDIT", $lng->txt("edit"));
	$tpl->setVariable("TXT_DEL", $lng->txt("delete"));
	$tpl->setVariable("LINK_DEL", "bookmarks.php?cmd=del&amp;id=".$row["id"]);
	$tpl->setVariable("TXT_ARE_YOU_SURE", $lng->txt("are_you_sure"));
	$tpl->setVariable("LINK_EDIT", "bookmark_new.php?cmd=edit&amp;id=".$row["id"]);
	$tpl->parseCurrentBlock();
}

$tpl->setCurrentBlock("content");
$tpl->setVariable("TXT_BOOKMARKS", $lng->txt("bookmarks"));
$tpl->setVariable("TXT_URL", $lng->txt("url"));
$tpl->setVariable("TXT_DESCRIPTION", $lng->txt("description"));
$tpl->parseCurrentBlock();

$tpl->show();
?>