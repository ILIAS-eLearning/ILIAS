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

print_r($_POST);
//form has been submitted
if ($_POST["submit"] != "")
{
	if ($_POST["note_text"] != "" && $_POST["lo_id"] != "" && $_POST["lo_title"] != "")
	{
		echo "Speichere Notiz !";
		
		//create new note
		$obj_id = $myNote->createObject($_POST["lo_title"], $_POST["note_text"]);
		//save to database
		$myNote->saveNote($obj_id, $_POST["lo_id"], $_POST ["note_text"]);
		//save in table tree
		$myNoteFolder->addNote($obj_id);
	}
}


$tpl->addBlockFile("CONTENT", "content", "tpl.note_new.html");
$tpl->addBlockFile("BUTTONS", "buttons", "tpl.buttons.html");

$tpl->setCurrentBlock("btn_row");
$tpl->parseCurrentBlock();
$tpl->setVariable("TXT_LOID", "Learning Object ID");
$tpl->setVariable("TXT_LOTITLE", "Learning Object Title");
$tpl->setVariable("TXT_NOTETEXT", "Notiztext");
$tpl->setVariable("TXT_SAVE", $lng->txt("save"));
$tpl->parseCurrentBlock();

$tpl->show();

?>
