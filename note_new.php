<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2001 ILIAS open source, University of Cologne            |
	|                                                                             |
	| This program is free software; you can redistribute it and/or               |
	| modify it under the terms of the GNU General Public License                 |
	| as published by the Free Software Foundation; either version 2              |
	| of the License, or (at your option) any later version.                      |
	|                                                                             |
	| This program is distributed in the hope that it will be useful,             |
	| but WITHOUT ANY WARRANTY; without even the implied warranty of              |
	| MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the               |
	| GNU General Public License for more details.                                |
	|                                                                             |
	| You should have received a copy of the GNU General Public License           |
	| along with this program; if not, write to the Free Software                 |
	| Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA. |
	+-----------------------------------------------------------------------------+
*/


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

$myNote = new ilObjNote($_GET["id"], false);
$myNoteFolder = new ilObjNoteFolder($ilias->account->getId());

if ($_GET["cmd"] == "edit")
{
	$myNotes = $myNoteFolder->viewNote($_GET["id"]);

	$tpl->setVariable("LOTITLE", $myNote->getTitle());
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
