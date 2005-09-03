<?php
/*
	+-----------------------------------------------------------------------------+
	| ILIAS open source                                                           |
	+-----------------------------------------------------------------------------+
	| Copyright (c) 1998-2005 ILIAS open source, University of Cologne            |
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

include_once ("Services/Notes/classes/class.ilNote.php");

/**
* Notes GUI class. An instance of this class handles all notes
* (and their lists) of an object.
*
* @author	Alex Killing <alex.killing@gmx.de>
* @version	$Id$
*/
class ilNoteGUI
{
	
	/**
	* constructor
	*/
	function ilNoteGUI($a_rep_obj_id, $a_obj_id, $a_obj_type)
	{
		global $ilCtrl;
		
		$this->rep_obj_id = $a_rep_obj_id;
		$this->obj_id = $a_obj_id;
		$this->obj_type = $a_obj_type;
		
		$this->ctrl =& $ilCtrl;
		
		$this->add_note_form = false;
		$this->edit_note_form = false;
		$this->private_enabled = false;
		$this->public_enabled = false;
	}
	
	/**
	* enable private notes
	*/
	function enablePrivateNotes($a_enable = true)
	{
		$this->private_enabled = $a_enable;
	}
	
	/**
	* enable public notes
	*/
	function enablePublicNotes($a_enable = true)
	{
		$this->public_enabled =  $a_enable;
	}

	/**
	* execute command
	*/
	function &executeCommand()
	{
		$cmd = $this->ctrl->getCmd("getNotesHTML");
		$next_class = $this->ctrl->getNextClass($this);
		
		switch($next_class)
		{
			default:
				return $this->$cmd();
				break;
		}
	}
	
	
	/***
	* get note lists html code
	*/
	function getNotesHTML()
	{
		$html = "";
		if ($this->private_enabled)
		{
			$html.= $this->getNoteListHTML(IL_NOTE_PRIVATE);
		}
		
		if ($this->public_enabled)
		{
			$html.= $this->getNoteListHTML(IL_NOTE_PUBLIC);
		}
		
		return $html;
	}

	/**
	* get notes list as html code
	*/
	function getNoteListHTML($a_type = IL_NOTE_PRIVATE)
	{
		global $lng, $ilCtrl, $ilUser;
		
		$suffix = ($a_type == IL_NOTE_PRIVATE)
			? "private"
			: "public";
		
		$notes = ilNote::_getNotesOfObject($this->rep_obj_id, $this->obj_id, $this->obj_type, $a_type);
		
		$tpl = new ilTemplate("tpl.notes_list.html", true, true, "Services/Notes");
		
		// show counter if notes are hidden
		$cnt_str = ($ilUser->getPref("notes_".$suffix) == "n"
			&& count($notes) > 0)
			? " (".count($notes).")"
			: "";
		
		if ($a_type == IL_NOTE_PRIVATE)
		{
			$tpl->setVariable("TXT_NOTES", $lng->txt("private_notes").$cnt_str);
			$ilCtrl->setParameterByClass("ilnotegui", "note_type", IL_NOTE_PRIVATE);
		}
		else
		{
			$tpl->setVariable("TXT_NOTES", $lng->txt("public_notes").$cnt_str);
			$ilCtrl->setParameterByClass("ilnotegui", "note_type", IL_NOTE_PUBLIC);
		}
		$tpl->setVariable("FORMACTION", $ilCtrl->getFormActionByClass("ilnotegui"));
		
		// show add new note button
		if (!$this->add_note_form && !$this->edit_note_form)
		{
			$tpl->setCurrentBlock("add_note_btn");
			$tpl->setVariable("TXT_ADD_NOTE", $lng->txt("add_note"));
			$tpl->setVariable("LINK_ADD_NOTE", $ilCtrl->getLinkTargetByClass("ilnotegui", "addNoteForm"));
			$tpl->parseCurrentBlock();
		}
		
		// show show/hide button for note list
		if (count($notes) > 0)
		{
			if ($ilUser->getPref("notes_".$suffix) == "n")
			{
				$tpl->setCurrentBlock("show_notes");
				$tpl->setVariable("LINK_SHOW_NOTES", $this->ctrl->getLinkTargetByClass("ilnotegui", "showNotes"));
				$tpl->setVariable("TXT_SHOW_NOTES", $lng->txt("show_".$suffix."_notes"));
				$tpl->parseCurrentBlock();
			}
			else
			{
				$tpl->setCurrentBlock("hide_notes");
				$tpl->setVariable("LINK_HIDE_NOTES", $this->ctrl->getLinkTargetByClass("ilnotegui", "hideNotes"));
				$tpl->setVariable("TXT_HIDE_NOTES", $lng->txt("hide_".$suffix."_notes"));
				$tpl->parseCurrentBlock();
			}
		}
		
		// show add new note text area
		if ($this->add_note_form && $a_type == $_GET["note_type"])
		{
			$tpl->setCurrentBlock("edit_note");
			$tpl->setVariable("TA_NOTE", "note");
			$tpl->setVariable("NOTE_CONTENT", "");
			$tpl->setVariable("BTN_ADD_NOTE", "addNote");
			$tpl->setVariable("TXT_ADD_NOTE", $lng->txt("add"));
			$tpl->setVariable("BTN_CANCEL_ADD_NOTE", "cancelAddNote");
			$tpl->setVariable("TXT_CANCEL_ADD_NOTE", $lng->txt("cancel"));
			$tpl->setVariable("VAL_LABEL_NONE", IL_NOTE_UNLABELED);
			$tpl->setVariable("TXT_LABEL_NONE", $lng->txt("unlabeled"));
			$tpl->setVariable("VAL_LABEL_QUESTION", IL_NOTE_QUESTION);
			$tpl->setVariable("TXT_LABEL_QUESTION", $lng->txt("question"));
			$tpl->setVariable("VAL_LABEL_IMPORTANT", IL_NOTE_IMPORTANT);
			$tpl->setVariable("TXT_LABEL_IMPORTANT", $lng->txt("important"));
			$tpl->parseCurrentBlock();
			$tpl->setCurrentBlock("note_row");
			$tpl->parseCurrentBlock();
		}

		// list all notes
		if ($ilUser->getPref("notes_".$suffix) != "n")
		{
			foreach($notes as $note)
			{
				if ($this->edit_note_form && ($note->getId() == $_GET["note_id"])
					&& $a_type == $_GET["note_type"])
				{
					$tpl->setCurrentBlock("edit_note_form");
					$tpl->setVariable("TA_NOTE", "note");
					$tpl->setVariable("NOTE_CONTENT",
						ilUtil::prepareFormOutput($note->getText()));
					$tpl->setVariable("BTN_ADD_NOTE", "updateNote");
					$tpl->setVariable("TXT_ADD_NOTE", $lng->txt("save"));
					$tpl->setVariable("BTN_CANCEL_ADD_NOTE", "cancelUpdateNote");
					$tpl->setVariable("TXT_CANCEL_ADD_NOTE", $lng->txt("cancel"));
					$tpl->setVariable("VAL_LABEL_NONE", IL_NOTE_UNLABELED);
					$tpl->setVariable("TXT_LABEL_NONE", $lng->txt("unlabeled"));
					$tpl->setVariable("VAL_LABEL_QUESTION", IL_NOTE_QUESTION);
					$tpl->setVariable("TXT_LABEL_QUESTION", $lng->txt("question"));
					$tpl->setVariable("VAL_LABEL_IMPORTANT", IL_NOTE_IMPORTANT);
					$tpl->setVariable("TXT_LABEL_IMPORTANT", $lng->txt("important"));
					$tpl->setVariable("VAL_NOTE_ID", $_GET["note_id"]);
					switch($note->getLabel())
					{
						case IL_NOTE_UNLABELED:
							$tpl->setVariable("SEL_NONE", 'selected="selected"');
							break;
							
						case IL_NOTE_IMPORTANT:
							$tpl->setVariable("SEL_IMPORTANT", 'selected="selected"');
							break;
							
						case IL_NOTE_QUESTION:
							$tpl->setVariable("SEL_QUESTION", 'selected="selected"');
							break;
					}
					$tpl->parseCurrentBlock();
				}
				else
				{
					// edit note button
					if ($note->getAuthor() == $ilUser->getId())
					{
						$tpl->setCurrentBlock("edit_note");
						$tpl->setVariable("TXT_EDIT_NOTE", $lng->txt("edit"));
						$ilCtrl->setParameterByClass("ilnotegui", "note_id", $note->getId());
						$tpl->setVariable("LINK_EDIT_NOTE",
							$ilCtrl->getLinkTargetByClass("ilnotegui", "editNoteForm"));
						$tpl->parseCurrentBlock();
					}
					
					// output authot account
					if ($a_type == IL_NOTE_PUBLIC)
					{
						$tpl->setCurrentBlock("author");
						$tpl->setVariable("VAL_AUTHOR", ilObjUser::_lookupLogin($note->getAuthor()));
						$tpl->parseCurrentBlock();
					}
					
					// last edited
					if ($note->getUpdateDate() != "0000-00-00 00:00:00")
					{
						$tpl->setCurrentBlock("last_edit");
						$tpl->setVariable("TXT_LAST_EDIT", $lng->txt("last_edited_on"));
						$tpl->setVariable("DATE_LAST_EDIT", $note->getUpdateDate());
						$tpl->parseCurrentBlock();
					}
					
					$rowclass = ($rowclass != "tblrow1")
						? "tblrow1"
						: "tblrow2";
					$tpl->setCurrentBlock("note");
					$tpl->setVariable("ROWCLASS", $rowclass);
					switch ($note->getLabel())
					{
						case IL_NOTE_UNLABELED:
							$tpl->setVariable("IMG_NOTE", ilUtil::getImagePath("note_unlabeled.gif"));
							break;
							
						case IL_NOTE_IMPORTANT:
							$tpl->setVariable("IMG_NOTE", ilUtil::getImagePath("note_important.gif"));
							break;
							
						case IL_NOTE_QUESTION:
							$tpl->setVariable("IMG_NOTE", ilUtil::getImagePath("note_question.gif"));
							break;
					}
					$tpl->setVariable("TXT_DATE", $lng->txt("date"));
					$tpl->setVariable("VAL_DATE", $note->getCreationDate());
					$tpl->setVariable("NOTE_TEXT", nl2br($note->getText()));
					$tpl->parseCurrentBlock();
				}
				$tpl->setCurrentBlock("note_row");
				$tpl->parseCurrentBlock();
			}
		}
				
		return $tpl->get();
	}
	
	/**
	* get notes list including add note area
	*/ 
	function addNoteForm()
	{
		global $ilUser;
		
		$suffix = ($_GET["note_type"] == IL_NOTE_PRIVATE)
			? "private"
			: "public";
		$ilUser->setPref("notes_".$suffix, "y");

		$this->add_note_form = true;
		return $this->getNotesHTML();
	}
	
	/**
	* cancel add note
	*/ 
	function cancelAddNote()
	{
		return $this->getNotesHTML();
	}
	
	/**
	* cancel edit note
	*/ 
	function cancelUpdateNote()
	{
		return $this->getNotesHTML();
	}
	
	/**
	* add note
	*/
	function addNote()
	{
		global $ilUser;
		
		if($_POST["note"] != "")
		{
			$note = new ilNote();
			$note->setObject($this->obj_type, $this->rep_obj_id, $this->obj_id);
			$note->setType($_GET["note_type"]);
			$note->setAuthor($ilUser->getId());
			$note->setText(ilUtil::stripSlashes($_POST["note"]));
			$note->setLabel($_POST["note_label"]);
			$note->create();
		}
		
		return $this->getNotesHTML();
	}

	/**
	* update note
	*/
	function updateNote()
	{
		global $ilUser;

		$note = new ilNote($_POST["note_id"]);
		//$note->setObject($this->obj_type, $this->rep_obj_id, $this->obj_id);
		//$note->setType(IL_NOTE_PRIVATE);
		//$note->setAuthor($ilUser->getId());
		$note->setText(ilUtil::stripSlashes($_POST["note"]));
		$note->setLabel($_POST["note_label"]);
		$note->update();
		
		return $this->getNotesHTML();
	}

	/**
	* get notes list including add note area
	*/ 
	function editNoteForm()
	{
		$this->edit_note_form = true;
		return $this->getNotesHTML();
	}
	
	/**
	* show notes
	*/
	function showNotes()
	{
		global $ilUser;

		$suffix = ($_GET["note_type"] == IL_NOTE_PRIVATE)
			? "private"
			: "public";
		$ilUser->writePref("notes_".$suffix, "y");

		return $this->getNotesHTML();
	}
	
	/**
	* hide notes
	*/
	function hideNotes()
	{
		global $ilUser;

		$suffix = ($_GET["note_type"] == IL_NOTE_PRIVATE)
			? "private"
			: "public";
		$ilUser->writePref("notes_".$suffix, "n");

		return $this->getNotesHTML();
	}

}
?>