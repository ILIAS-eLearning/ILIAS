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
require_once "./classes/class.ilObjNote.php";
require_once "./classes/class.ilObjNoteFolder.php";

$myNote = new ilObjNote();
$myNoteFolder = new ilObjNoteFolder($ilias->account->getId());

if ($_GET["cmd"] == "edit")
{
	$myNotes = $myNoteFolder->viewNote($_GET["id"]);
	$obj_note = getObject($_GET["id"]);		//gets title out of object_data

	$tpl->setVariable("LOTITLE", $obj_note["title"]);
	$tpl->setVariable("NOTETEXT", $myNotes->text);
	$tpl->setVariable("LOID", $myNotes->lo_id);
	if($myNotes->important =='y') $tpl->setVariable("IMPORTANT_CHECKED", "checked");
	if($myNotes->good =='y') $tpl->setVariable("GOOD_CHECKED", "checked");
	if($myNotes->bad =='y') $tpl->setVariable("BAD_CHECKED", "checked");
	if($myNotes->question =='y') $tpl->setVariable("QUESTION_CHECKED", "checked");
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
