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
include("./classes/class.Bookmarks.php");

$myBm = new Bookmarks($ilias->db, $ilias->account->Id);

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
	exit();
}

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

$tpl = new Template("tpl.bookmarks.html", true, true);
$tpl->setVariable("BUTTONS",$tplbtn->get());

$tplmain->setVariable("PAGETITLE", "ILIAS - ".$lng->txt("bookmarks"));
$tpl->setVariable("TXT_PAGEHEADLINE", $lng->txt("bookmarks"));
$tpl->setVariable("TXT_URL", $lng->txt("url"));
$tpl->setVariable("TXT_DESCRIPTION", $lng->txt("description"));

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

$tplmain->setVariable("PAGECONTENT",$tpl->get());
$tplmain->show();

?>