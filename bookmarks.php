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


//$myBm = new ilBookmark($ilias->account->getId());


/**
* list bookmarks of user
*/
function list_bookmarks(&$bookmarks)
{
	global $tpl, $ilias, $lng;

	$tpl->addBlockFile("CONTENT", "content", "tpl.bookmarks.html");
	$tpl->addBlockFile("BUTTONS", "buttons", "tpl.buttons.html");

	//$ilias->error_obj->sendInfo("Attention: Functions to handle bookmarks is not implemented yet.");

	// menu
	$tpl->setCurrentBlock("btn_cell");		// new folder
	$tpl->setVariable("BTN_LINK","bookmark_newfolder.php");
	$tpl->setVariable("BTN_TXT", $lng->txt("new_folder"));
	$tpl->parseCurrentBlock();
	$tpl->setCurrentBlock("btn_cell");		// new bookmark
	$tpl->setVariable("BTN_LINK","bookmarks.php?cmd=new_bookmark");
	$tpl->setVariable("BTN_TXT", $lng->txt("bookmark_new"));
	$tpl->parseCurrentBlock();
	$tpl->setCurrentBlock("btn_row");
	$tpl->parseCurrentBlock();

	// list folders and bookmarks
	$bm = $bookmarks->getBookmarkList();
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

	$tpl->setVariable("TXT_BOOKMARKS", $lng->txt("bookmarks"));
	$tpl->setVariable("TXT_URL", $lng->txt("url"));
	$tpl->setVariable("TXT_DESCRIPTION", $lng->txt("description"));
	$tpl->parseCurrentBlock();

	$tpl->show();
}


/*
* edit bookmark form
*/
function edit_bookmark_form($mode, &$bookmarks)
{
	global $tpl, $lng;

	$tpl->addBlockFile("CONTENT", "content", "tpl.bookmark_new.html");
	//$tpl->addBlockFile("BUTTONS", "buttons", "tpl.buttons.html");

	$tpl->setVariable("PAGETITLE", "ILIAS - ".$lng->txt("bookmarks"));
	$tpl->setVariable("TXT_PAGEHEADLINE", $lng->txt("bookmark_new"));
	$tpl->setVariable("TXT_URL", $lng->txt("url"));
	$tpl->setVariable("TXT_DESCRIPTION", $lng->txt("description"));

	// folder selection list
	$folders = $bookmarks->getFolders();
	foreach ($folders as $folder)
	{
		$tpl->setCurrentBlock("selfolders");
		$tpl->setVariable("SEL_OPTION", $folder["name"]);
		$tpl->setVariable("SEL_VALUE", $folder["id"]);
		$tpl->parseCurrentBlock();
	}

	$tpl->setVariable("TXT_TOP", $lng->txt("top"));
	$tpl->setVariable("TXT_NAME", $lng->txt("name"));
	$tpl->setVariable("TXT_FOLDER", $lng->txt("folder"));
	$tpl->setVariable("TXT_SAVE", $lng->txt("save"));
	$tpl->setVariable("TXT_FOLDER_NEW", $lng->txt("folder_new"));
	$tpl->setVariable("FORMACTION", "bookmarks.php?cmd=$mode_bookmark");
	$tpl->parseCurrentBlock();

	//$tpl->setVariable("PAGECONTENT",$tpl->get());
	$tpl->show();

}


/*
* save bookmark
*/
function save_bookmark($mode, &$bookmarks)
{
	global $tpl, $lng;
}


//
// main
//

// get bookmarks object of current user
//$bookmarks = new ilBookmark($ilias->account->Id);

$cmd = $_GET["cmd"];
if(empty($cmd))
{
	$cmd = "display";
}

$type = (empty($_POST["type"])) ? $_GET["type"] : $_POST["type"];

if(!empty($type))
{
	$cmd.= $objDefinition->getClassName($type);
}

/*if (isset($_GET["bm_id"]))
{
	require_once "./classes/class.ilBookmarkGUI.php";
	$bookmarkGUI = new ilBookmarkGUI($_GET["bm_id"]);
	$bookmarkGUI->$cmd();
}
else
{*/
	require_once "./classes/class.ilBookmarkFolderGUI.php";
	$bookmarkFolderGUI = new ilBookmarkFolderGUI($_GET["bmf_id"]);
//echo "BMFID:".$_GET["bmf_id"].":cmd:$cmd:<br>";
//echo "bookmarkFolderGUI->".$cmd."()<br>"; exit;
	$bookmarkFolderGUI->$cmd();
//}

$tpl->show();

// command processing
/*
switch ($_GET["cmd"])
{
	// new bookmark form
	case "new_bookmark":
		edit_bookmark_form("insert", $bookmarks);
		break;

	// insert new bookmark
	case "insert_bookmark":
		save_bookmark("insert", $bookmarks);
		list_bookmarks($bookmarks);
		break;

	case "del":
		$bookmarks->delete($id);
		break;

	case "edit":
		break;

	// list bookmarks
	default:
		list_bookmarks($bookmarks);
		break;
}*/


?>
