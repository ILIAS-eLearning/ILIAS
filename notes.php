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
require_once "./classes/class.NoteObject.php";
require_once "./classes/class.NoteFolderObject.php";

$myNoteFolder = new NoteFolderObject($ilias->account->Id);
//$myNoteFolder = new NoteFolderObject(202);

//form has been submitted
if ($_POST["submit"] = "delete")
{

	if ($_POST["id"] != "")
	{
		echo "Lösche Notizen !";
		$myNoteFolder->deleteNotes($id);
	}
}

if ($_GET["cmd"] != "")
{
	switch ($_GET["cmd"])
	{
		case "view":
			echo "DELETE!";
			break;
		case "edit":
			break;
	}

	header("location: notes.php");
	exit;
}

$tpl->addBlockFile("CONTENT", "content", "tpl.notes.html");
$tpl->addBlockFile("BUTTONS", "buttons", "tpl.buttons.html");

$ilias->error_obj->sendInfo("Attention: Functions to handle bookmarks is not implemented yet.");

$tpl->setCurrentBlock("btn_cell");
$tpl->setVariable("BTN_LINK","note_new.php");
$tpl->setVariable("BTN_TXT", $lng->txt("new_note"));
$tpl->parseCurrentBlock();
$tpl->setCurrentBlock("btn_row");
$tpl->parseCurrentBlock();

$myNotes = $myNoteFolder->getNotes();


foreach ($myNotes as $row)
{
	$i++;
	$tpl->setCurrentBlock("noterow");
	$tpl->setVariable("ROWCOL","tblrow".(($i%2)+1));
	$tpl->setVariable("NOTE_ID", $row->note_id);		//perhaps lo's title is display here
	$tpl->setVariable("TITLE", $row->lo_id);		//perhaps lo's title is display here
	$tpl->setVariable("DESC", $row->text);
	$tpl->setVariable("TXT_EDIT", $lng->txt("edit"));
	$tpl->setVariable("TXT_VIEW", $lng->txt("view"));
//	$tpl->setVariable("LINK_DEL", "notes.php?cmd=del&amp;id=".$row->note_id);
//	$tpl->setVariable("TXT_ARE_YOU_SURE", $lng->txt("are_you_sure"));
	$tpl->setVariable("LINK_EDIT", "note_new.php?cmd=edit&amp;id=".$row->note_id);

	$tpl->parseCurrentBlock();
}

$tpl->setVariable("TXT_NOTES", $lng->txt("notes"));
$tpl->setVariable("TXT_TITLE", $lng->txt("title"));
$tpl->setVariable("TXT_DESCRIPTION", $lng->txt("description"));
$tpl->setVariable("TXT_SAVE", $lng->txt("save"));
$tpl->parseCurrentBlock();

$tpl->show();
?>
