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

$myNote = new NoteObject();
$myNoteFolder = new NoteFolderObject($ilias->account->Id);

//form has been submitted
if ($_POST["submit"] = "delete")
{

	if ($_POST["id"] != "")
	{
		$myNoteFolder->deleteNotes($id);
	}
}



if ($_GET["cmd"] != "")
{
	switch ($_GET["cmd"])
	{
		case "save":
			//create new note
			$obj_id = $myNote->createObject($_POST["lo_title"], $_POST["note_text"]);
			//save to database
			$myNote->saveNote($obj_id, $_POST["lo_id"], $_POST["lo_title"], $_POST ["note_text"]);
			//save in table tree
			$myNoteFolder->addNote($obj_id);
			break;

		case "update":
			$myNote->updateNote($_GET["id"], $_POST["note_text"]);
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
	$tpl->setVariable("TITLE", $row->lo_title);		//perhaps lo's title is display here
	$tpl->setVariable("DESC", $row->text);
	$tpl->setVariable("TXT_EDIT", $lng->txt("edit"));
	$tpl->setVariable("TXT_VIEW", $lng->txt("view"));
	$tpl->setVariable("LINK_VIEW", "note_view.php?cmd=view&amp;id=".$row->note_id);
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
