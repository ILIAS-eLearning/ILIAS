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
	* constructor, specifies notes set
	*
	* @param	$a_rep_obj_id	int		object id of repository object (0 for personal desktop)
	* @param	$a_obj_id		int		subobject id (0 for repository items, user id for personal desktop)
	* @param	$a_obj_type		string	"pd" for personal desktop
	* @param	$a_include_subobjects	string		include all subobjects of rep object (e.g. pages)
	*/
	function ilNoteGUI($a_rep_obj_id, $a_obj_id, $a_obj_type, $a_include_subobjects = false)
	{
		global $ilCtrl;
		
		$this->rep_obj_id = $a_rep_obj_id;
		$this->obj_id = $a_obj_id;
		$this->obj_type = $a_obj_type;
		$this->inc_sub = $a_include_subobjects;
		
		$this->ctrl =& $ilCtrl;
		
		$this->add_note_form = false;
		$this->edit_note_form = false;
		$this->private_enabled = false;
		$this->public_enabled = false;
		$this->enable_hiding = true;
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
	* enable hiding
	*/
	function enableHiding($a_enable = true)
	{
		$this->enable_hiding = $a_enable;
	}
	
	/***
	* get note lists html code
	*/
	function getNotesHTML()
	{
		global $ilUser;
		
		$html = "";
		if ($this->private_enabled && ($ilUser->getId() != ANONYMOUS_USER_ID))
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
		
		if ($this->delete_note)
		{
			$filter = $_GET["note_id"];
		}
		
		$notes = ilNote::_getNotesOfObject($this->rep_obj_id, $this->obj_id,
			$this->obj_type, $a_type, $this->inc_sub, $filter);
		
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
		$tpl->setVariable("FORMACTION", $ilCtrl->getFormAction($this));
		
		// show add new note button
		if (!$this->add_note_form && !$this->edit_note_form &&
			($ilUser->getId() != ANONYMOUS_USER_ID))
		{
			if (!$this->inc_sub)	// we cannot offer add button if aggregated notes
			{						// are displayed
				$tpl->setCurrentBlock("add_note_btn");
				$tpl->setVariable("TXT_ADD_NOTE", $lng->txt("add_note"));
				$tpl->setVariable("LINK_ADD_NOTE", $ilCtrl->getLinkTargetByClass("ilnotegui", "addNoteForm").
					"#note_edit");
				$tpl->parseCurrentBlock();
			}
		}
		
		// show show/hide button for note list
		if (count($notes) > 0 && $this->enable_hiding)
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
				// never individually hide for anonymous users
				if (($ilUser->getId() != ANONYMOUS_USER_ID))
				{
					$tpl->setCurrentBlock("hide_notes");
					$tpl->setVariable("LINK_HIDE_NOTES", $this->ctrl->getLinkTargetByClass("ilnotegui", "hideNotes"));
					$tpl->setVariable("TXT_HIDE_NOTES", $lng->txt("hide_".$suffix."_notes"));
					$tpl->parseCurrentBlock();
				}
			}
		}
		
		// show add new note text area
		if ($this->add_note_form && $a_type == $_GET["note_type"])
		{
			$tpl->setCurrentBlock("edit_note");
			$tpl->setVariable("TXT_SUBJECT", $lng->txt("subject"));
			$tpl->setVariable("TXT_NOTE", $lng->txt("note"));
			$tpl->setVariable("NOTE_SUBJECT", "");
			$tpl->setVariable("SUB_NOTE", "sub_note");
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
		if ($ilUser->getPref("notes_".$suffix) != "n" || !$this->enable_hiding)
		{
			foreach($notes as $note)
			{
				if ($this->edit_note_form && ($note->getId() == $_GET["note_id"])
					&& $a_type == $_GET["note_type"])
				{
					$tpl->setCurrentBlock("edit_note_form");
					$tpl->setVariable("TXT_SUBJECT", $lng->txt("subject"));
					$tpl->setVariable("TXT_NOTE", $lng->txt("note"));
					$tpl->setVariable("NOTE_SUBJECT",
						ilUtil::prepareFormOutput($note->getSubject()));
					$tpl->setVariable("SUB_NOTE", "sub_note");
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
					if ($note->getAuthor() == $ilUser->getId()
						&& ($ilUser->getId() != ANONYMOUS_USER_ID))
					{
						// only private notes can be deleted
						if ($a_type == IL_NOTE_PRIVATE)
						{
							$tpl->setCurrentBlock("delete_note");
							$tpl->setVariable("TXT_DELETE_NOTE", $lng->txt("delete"));
							$ilCtrl->setParameterByClass("ilnotegui", "note_id", $note->getId());
							$tpl->setVariable("LINK_DELETE_NOTE",
								$ilCtrl->getLinkTargetByClass("ilnotegui", "deleteNote")
								."#note_edit");
							$tpl->parseCurrentBlock();
						}

						$tpl->setCurrentBlock("edit_note");
						$tpl->setVariable("TXT_EDIT_NOTE", $lng->txt("edit"));
						$ilCtrl->setParameterByClass("ilnotegui", "note_id", $note->getId());
						$tpl->setVariable("LINK_EDIT_NOTE",
							$ilCtrl->getLinkTargetByClass("ilnotegui", "editNoteForm")
							."#note_edit");
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
					$tpl->setVariable("TXT_CREATED", $lng->txt("create_date"));
					$tpl->setVariable("VAL_DATE", $note->getCreationDate());
					$tpl->setVariable("NOTE_TEXT", nl2br($note->getText()));
					$tpl->setVariable("VAL_SUBJECT", $note->getSubject());
					$tpl->parseCurrentBlock();
				}
				$tpl->setCurrentBlock("note_row");
				$tpl->parseCurrentBlock();
			}
		}
				
		return $tpl->get();
	}
	
	/**
	* notes overview on personal desktop
	* shows 10 recent notes
	*/
	function _getPDOverviewNoteListHTML()
	{
		global $lng, $ilUser, $ilCtrl;
		
		//$notes = ilNote::_getNotesOfObject($this->rep_obj_id, $this->obj_id, $this->obj_type, $a_type);
		
		$tpl = new ilTemplate("tpl.pd_notes_overview.html", true, true, "Services/Notes");
		$tpl->setVariable("TXT_NOTES", $lng->txt("notes"));
		$showdetails = $ilUser->getPref('show_pd_notes_details') == 'y';
		// add details link
		if ($showdetails)
		{
			$tpl->setCurrentBlock("hide_details");
			$tpl->setVariable("LINK_HIDE_DETAILS",
				$ilCtrl->getLinkTargetByClass("ilpersonaldesktopgui",
					"hidePDNotesDetails"));
			$tpl->setVariable("TXT_HIDE_DETAILS",
				$this->lng->txt("hide_details"));
			$tpl->parseCurrentBlock();
		}
		else
		{
			$tpl->setCurrentBlock("show_details");
			$tpl->setVariable("LINK_SHOW_DETAILS",
				$ilCtrl->getLinkTargetByClass("ilpersonaldesktopgui",
					"showPDNotesDetails"));
			$tpl->setVariable("TXT_SHOW_DETAILS",
				$this->lng->txt("show_details"));
			$tpl->parseCurrentBlock();
		}

		// get last ten notes
		include_once("Services/Notes/classes/class.ilNote.php");
		$notes = ilNote::_getLastNotesOfUser();

		foreach($notes as $note)
		{
			$rowclass = ($rowclass != "tblrow1")
				? "tblrow1"
				: "tblrow2";
			$tpl->setCurrentBlock("note");
			$tpl->setVariable("ROWCLASS", $rowclass);
			$tpl->setVariable("VAL_SUBJECT", $note->getSubject());
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

			// details
			if ($showdetails)
			{
				$tpl->setVariable("NOTE_TEXT", $note->getText());
				$tpl->setVariable("TXT_CREATED", $lng->txt("create_date"));
				$tpl->setVariable("VAL_DATE", $note->getCreationDate());
			}
			$tpl->parseCurrentBlock();
			$tpl->setCurrentBlock("note_row");
			$tpl->parseCurrentBlock();
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
			$note->setSubject(ilUtil::stripSlashes($_POST["sub_note"]));
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
		$note->setSubject(ilUtil::stripSlashes($_POST["sub_note"]));
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
	* get notes list including add note area
	*/ 
	function deleteNote()
	{
		$this->delete_note = true;
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