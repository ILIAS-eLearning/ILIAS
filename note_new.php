<?php
/**
* note_new
*
* @author Matthias Maschke <m.maschke@uni-koeln.de>
* @version $Id$
*
* @package application
*/
require_once "./include/inc.header.php";
require_once "./classes/class.NoteObject.php";
require_once "./classes/class.NoteFolderObject.php";

$myNote = new NoteObject();
$myNoteFolder = new NoteFolderObject($ilias->account->Id);

if ($_GET["cmd"] == "edit")
{
	$myNotes = $myNoteFolder->getNotes($_GET["id"]);

	$tpl->setVariable("LOTITLE", $myNotes->lo_title);
	$tpl->setVariable("NOTETEXT", $myNotes->text);
	$tpl->setVariable("LOID", $myNotes->lo_id);
	$tpl->setVariable("FORMACTION", "notes.php?cmd=update&amp;id=".$_GET["id"]);
}
else
	$tpl->setVariable("FORMACTION", "notes.php?cmd=save");


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
