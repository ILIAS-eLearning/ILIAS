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
*
* @ingroup ServicesNotes
*/
class ilNoteGUI
{
	var $public_deletion_enabled = false;
	
	/**
	* constructor, specifies notes set
	*
	* @param	$a_rep_obj_id	int		object id of repository object (0 for personal desktop)
	* @param	$a_obj_id		int		subobject id (0 for repository items, user id for personal desktop)
	* @param	$a_obj_type		string	"pd" for personal desktop
	* @param	$a_include_subobjects	string		include all subobjects of rep object (e.g. pages)
	*/
	function ilNoteGUI($a_rep_obj_id = "", $a_obj_id = "", $a_obj_type = "", $a_include_subobjects = false)
	{
		global $ilCtrl, $lng;
		
		$lng->loadLanguageModule("notes");

		$this->rep_obj_id = $a_rep_obj_id;
		$this->obj_id = $a_obj_id;
		$this->obj_type = $a_obj_type;
		$this->inc_sub = $a_include_subobjects;
		
		$this->ctrl =& $ilCtrl;
		$this->lng =& $lng;
		
		$this->anchor_jump = true;
		$this->add_note_form = false;
		$this->edit_note_form = false;
		$this->private_enabled = false;
		$notes_settings = new ilSetting("notes");
		$id = $this->rep_obj_id."_".$this->obj_id."_".$this->obj_type;
		if ($notes_settings->get("activate_".$id))
		{
			$this->public_enabled = true;
		}
		else
		{
			$this->public_enabled = false;
		}
		$this->enable_hiding = true;
		$this->targets_enabled = false;
		$this->multi_selection = false;
		$this->export_html = false;
		$this->print = false;
		$this->comments_settings = false;
		
		$this->note_img = array(
			IL_NOTE_UNLABELED => array(
				"img" => ilUtil::getImagePath("note_unlabeled.gif"),
				"alt" => $lng->txt("note")),
			IL_NOTE_IMPORTANT => array(
				"img" => ilUtil::getImagePath("note_important.gif"),
				"alt" => $lng->txt("note").", ".$lng->txt("important")),
			IL_NOTE_QUESTION => array(
				"img" => ilUtil::getImagePath("note_question.gif"),
				"alt" => $lng->txt("note").", ".$lng->txt("question")),
			IL_NOTE_PRO => array(
				"img" => ilUtil::getImagePath("note_pro.gif"),
				"alt" => $lng->txt("note").", ".$lng->txt("pro")),
			IL_NOTE_CONTRA => array(
				"img" => ilUtil::getImagePath("note_contra.gif"),
				"alt" => $lng->txt("note").", ".$lng->txt("contra"))
			);
			
		$this->comment_img = array(
			IL_NOTE_UNLABELED => array(
				"img" => ilUtil::getImagePath("comment_unlabeled.gif"),
				"alt" => $lng->txt("notes_comment")),
			IL_NOTE_IMPORTANT => array(
				"img" => ilUtil::getImagePath("comment_important.gif"),
				"alt" => $lng->txt("notes_comment").", ".$lng->txt("important")),
			IL_NOTE_QUESTION => array(
				"img" => ilUtil::getImagePath("comment_question.gif"),
				"alt" => $lng->txt("notes_comment").", ".$lng->txt("question")),
			IL_NOTE_PRO => array(
				"img" => ilUtil::getImagePath("comment_pro.gif"),
				"alt" => $lng->txt("notes_comment").", ".$lng->txt("pro")),
			IL_NOTE_CONTRA => array(
				"img" => ilUtil::getImagePath("comment_contra.gif"),
				"alt" => $lng->txt("notes_comment").", ".$lng->txt("contra"))
			);
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
	* enable private notes
	*/
	function enableCommentsSettings($a_enable = true)
	{
		$this->comments_settings = $a_enable;
	}
	
	/**
	* enable public notes
	*/
	function enablePublicNotesDeletion($a_enable = true)
	{
		$this->public_deletion_enabled =  $a_enable;
	}

	/**
	* enable hiding
	*/
	function enableHiding($a_enable = true)
	{
		$this->enable_hiding = $a_enable;
	}
	
	/**
	* enable target objects
	*/
	function enableTargets($a_enable = true)
	{
		$this->targets_enabled = $a_enable;
	}

	/**
	* enable multi selection (checkboxes and commands)
	*/
	function enableMultiSelection($a_enable = true)
	{
		$this->multi_selection = $a_enable;
	}

	/**
	* enable anchor for form jump
	*/
	function enableAnchorJump($a_enable = true)
	{
		$this->anchor_jump = $a_enable;
	}

	/***
	* get note lists html code
	*/
	function getNotesHTML($a_init_form = true)
	{
		global $ilUser, $lng, $ilCtrl;
		
		$lng->loadLanguageModule("notes");

		$ntpl = new ilTemplate("tpl.notes_and_comments.html", true, true,
			"Services/Notes");

		// check, whether column is hidden due to processing in other column
		$hide_notes = $hide_comments = false;
		switch($ilCtrl->getCmd())
		{
			case "addNoteForm":
			case "editNoteForm":
			case "addNote":
			case "updateNote":
				if ($_GET["note_type"] == IL_NOTE_PRIVATE)
				{
					$hide_comments = true;
				}
				if ($_GET["note_type"] == IL_NOTE_PUBLIC)
				{
					$hide_notes = true;
				}
				break;
		}
			
		$nodes_col = false;
		if ($this->private_enabled && ($ilUser->getId() != ANONYMOUS_USER_ID)
			&& !$hide_notes)
		{
			$ntpl->setCurrentBlock("notes_col");
			$ntpl->setVariable("NOTES", $this->getNoteListHTML(IL_NOTE_PRIVATE, $a_init_form));
			$ntpl->parseCurrentBlock();
			$nodes_col = true;
		}
		
		$comments_col = false;
		if ($this->public_enabled && (!$this->delete_note || $this->public_deletion_enabled)
			&& !$hide_comments)
//		if ($this->public_enabled && ($ilUser->getId() != ANONYMOUS_USER_ID)
//			&& !$hide_comments)
		{
			$ntpl->setVariable("COMMENTS", $this->getNoteListHTML(IL_NOTE_PUBLIC, $a_init_form));
			$comments_col = true;
		}
		
		// Comments Settings
		if ($this->comments_settings && !$hide_comments && !$this->delete_note
			&& !$this->edit_note_form && !$this->add_note_form)
		{
			$notes_settings = new ilSetting("notes");
			$id = $this->rep_obj_id."_".$this->obj_id."_".$this->obj_type;
			$active = $notes_settings->get("activate_".$id);

			$ntpl->setCurrentBlock("comments_settings");
			if ($active)
			{
				$ntpl->setVariable("TXT_COMMENTS_SETTINGS", $lng->txt("notes_deactivate_comments"));
				$ntpl->setVariable("HREF_COMMENTS_SETTINGS",
					$ilCtrl->getLinkTargetByClass("ilnotegui", "deactivateComments", "notes_top"));
			}
			else
			{
				$ntpl->setVariable("TXT_COMMENTS_SETTINGS", $lng->txt("notes_activate_comments"));
				$ntpl->setVariable("HREF_COMMENTS_SETTINGS",
					$ilCtrl->getLinkTargetByClass("ilnotegui", "activateComments", "notes_top"));
			}
			$ntpl->parseCurrentBlock();
			$comments_col = true;
		}
		
		if ($comments_col)
		{
			$ntpl->setCurrentBlock("comments_col");
			// scorm2004-start
			if ($nodes_col)
			{
				$ntpl->touchBlock("comments_style");
			}
			// scorm2004-end
			$ntpl->parseCurrentBlock();
		}
		
		switch($_GET["note_mess"] != "" ? $_GET["note_mess"] : $this->note_mess)
		{
			case "mod":
				$mtype = "success";
				$mtxt = $lng->txt("msg_obj_modified");
				break;
				
			case "ntsdel":
				$mtype = "success";
				$mtxt = $lng->txt("notes_notes_deleted");
				break;

			case "ntdel":
				$mtype = "success";
				$mtxt = $lng->txt("notes_note_deleted");
				break;
				
			case "frmfld":
				$mtype = "failure";
				$mtxt = $lng->txt("form_input_not_valid");
				break;

			case "qdel":
				$mtype = "question";
				$mtxt = $lng->txt("info_delete_sure");
				break;
				
			case "noc":
				$mtype = "failure";
				$mtxt = $lng->txt("no_checkbox");
				break;
		}
		
		if ($mtxt != "")
		{
			$ntpl->setVariable("NOTE_MESS", $ntpl->getMessageHTML($mtxt, $mtype));
		}
		
		return $ntpl->get();
	}
	
	/**
	* Activate Comments
	*/
	function activateComments()
	{
		global $ilCtrl;
		
		$notes_settings = new ilSetting("notes");
		
		if ($this->comments_settings)
		{
			$id = $this->rep_obj_id."_".$this->obj_id."_".$this->obj_type;
			$notes_settings->set("activate_".$id, 1);
		}
		
		$ilCtrl->redirectByClass("ilnotegui", "getNotesHtml");
	}

	/**
	* Deactivate Comments
	*/
	function deactivateComments()
	{
		global $ilCtrl;
		
		$notes_settings = new ilSetting("notes");
		
		if ($this->comments_settings)
		{
			$id = $this->rep_obj_id."_".$this->obj_id."_".$this->obj_type;
			$notes_settings->set("activate_".$id, 0);
		}
		
		$ilCtrl->redirectByClass("ilnotegui", "getNotesHtml");
	}

	/**
	* get notes/comments list as html code
	*/
	function getNoteListHTML($a_type = IL_NOTE_PRIVATE, $a_init_form = true)
	{
		global $lng, $ilCtrl, $ilUser, $ilAccess, $tree, $objDefinition;

		$suffix = ($a_type == IL_NOTE_PRIVATE)
			? "private"
			: "public";
		
		if ($this->delete_note || $this->export_html || $this->print)
		{
			if ($_GET["note_id"] != "")
			{
				$filter = $_GET["note_id"];
			}
			else
			{
				$filter = $_POST["note"];
			}
		}
		$notes = ilNote::_getNotesOfObject($this->rep_obj_id, $this->obj_id,
			$this->obj_type, $a_type, $this->inc_sub, $filter,
			$ilUser->getPref("notes_pub_all"), $this->public_deletion_enabled);
		$all_notes = ilNote::_getNotesOfObject($this->rep_obj_id, $this->obj_id,
			$this->obj_type, $a_type, $this->inc_sub, $filter,
			"", $this->public_deletion_enabled);

		$tpl = new ilTemplate("tpl.notes_list.html", true, true, "Services/Notes");
		
		// show counter if notes are hidden
		$cnt_str = (count($all_notes) > 0)
			? " (".count($all_notes).")"
			: "";
		
		if ($a_type == IL_NOTE_PUBLIC)
		{
			$tpl->setVariable("IMG_NOTES", ilUtil::getImagePath("icon_comment.gif"));
		}
		else
		{
			$tpl->setVariable("IMG_NOTES", ilUtil::getImagePath("icon_note.gif"));
		}
		if ($this->delete_note)
		{
			$cnt_str = "";
		}
		if ($a_type == IL_NOTE_PRIVATE)
		{
			$tpl->setVariable("TXT_NOTES", $lng->txt("private_notes").$cnt_str);
			$tpl->setVariable("ALT_NOTES", $lng->txt("icon")." ".$lng->txt("private_notes"));
			$ilCtrl->setParameterByClass("ilnotegui", "note_type", IL_NOTE_PRIVATE);
		}
		else
		{
			$tpl->setVariable("TXT_NOTES", $lng->txt("notes_public_comments").$cnt_str);
			$tpl->setVariable("ALT_NOTES", $lng->txt("icon")." ".$lng->txt("notes_public_comments"));
			$ilCtrl->setParameterByClass("ilnotegui", "note_type", IL_NOTE_PUBLIC);
		}
		$anch = $this->anchor_jump
			? "notes_top"
			: "";
		$tpl->setVariable("FORMACTION", $ilCtrl->getFormAction($this, "getNotesHTML", $anch));
		
		if ($this->export_html || $this->print)
		{
			$tpl->touchBlock("print_style");
		}
		
		// show add new note button
		if (!$this->add_note_form && !$this->edit_note_form && !$this->delete_note &&
			!$this->export_html && !$this->print &&
			($ilUser->getId() != ANONYMOUS_USER_ID))
		{
			if (!$this->inc_sub)	// we cannot offer add button if aggregated notes
			{						// are displayed
				if ($this->rep_obj_id > 0 || $a_type != IL_NOTE_PUBLIC)
				{
					$tpl->setCurrentBlock("add_note_btn");
					if ($a_type == IL_NOTE_PUBLIC)
					{
						$tpl->setVariable("TXT_ADD_NOTE", $lng->txt("notes_add_comment"));
					}
					else
					{
						$tpl->setVariable("TXT_ADD_NOTE", $lng->txt("add_note"));
					}
					$tpl->setVariable("LINK_ADD_NOTE", $ilCtrl->getLinkTargetByClass("ilnotegui", "addNoteForm").
						"#note_edit");
					$tpl->parseCurrentBlock();
				}
			}
		}
		
		// show show/hide button for note list
		if (count($all_notes) > 0 && $this->enable_hiding && !$this->delete_note
			&& !$this->export_html && !$this->print && !$this->edit_note_form
			&& !$this->add_note_form)
		{
			if ($ilUser->getPref("notes_".$suffix) == "n")
			{
				$tpl->setCurrentBlock("show_notes");
				$tpl->setVariable("LINK_SHOW_NOTES",
					$this->ctrl->getLinkTargetByClass("ilnotegui", "showNotes", "notes_top"));
				if ($a_type == IL_NOTE_PUBLIC)
				{
					$tpl->setVariable("TXT_SHOW_NOTES", $lng->txt("notes_show_comments"));
				}
				else
				{
					$tpl->setVariable("TXT_SHOW_NOTES", $lng->txt("show_".$suffix."_notes"));
				}
				$tpl->parseCurrentBlock();
			}
			else
			{
				// never individually hide for anonymous users
				if (($ilUser->getId() != ANONYMOUS_USER_ID))
				{
					$tpl->setCurrentBlock("hide_notes");
					$tpl->setVariable("LINK_HIDE_NOTES",
						$this->ctrl->getLinkTargetByClass("ilnotegui", "hideNotes", "notes_top"));
					if ($a_type == IL_NOTE_PUBLIC)
					{
						$tpl->setVariable("TXT_HIDE_NOTES", $lng->txt("notes_hide_comments"));
					}
					else
					{
						$tpl->setVariable("TXT_HIDE_NOTES", $lng->txt("hide_".$suffix."_notes"));
					}
					$tpl->parseCurrentBlock();
					
					// show all public notes / my notes only switch
					if ($a_type == IL_NOTE_PUBLIC)
					{
						if ($ilUser->getPref("notes_pub_all") == "n")
						{
							$tpl->setCurrentBlock("all_pub_notes");
							$tpl->setVariable("LINK_ALL_PUB_NOTES",
								$this->ctrl->getLinkTargetByClass("ilnotegui", "showAllPublicNotes", "notes_top"));
							$tpl->setVariable("TXT_ALL_PUB_NOTES", $lng->txt("notes_all_comments"));
							$tpl->parseCurrentBlock();
						}
						else
						{
							$tpl->setCurrentBlock("my_pub_notes");
							$tpl->setVariable("LINK_MY_PUB_NOTES",
								$this->ctrl->getLinkTargetByClass("ilnotegui", "showMyPublicNotes", "notes_top"));
							$tpl->setVariable("TXT_MY_PUB_NOTES", $lng->txt("notes_my_comments"));
							$tpl->parseCurrentBlock();
						}
					}
				}
			}
		}
		
		// show add new note text area
		if ($this->add_note_form && $a_type == $_GET["note_type"])
		{
			if ($a_init_form)
			{
				$this->initNoteForm("create", $a_type);
			}

			$tpl->setCurrentBlock("edit_note_form");
			$tpl->setVariable("EDIT_FORM", $this->form->getHTML());
			$tpl->parseCurrentBlock();

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
					if ($a_init_form)
					{
						$this->initNoteForm("edit", $a_type, $note);
					}
					$tpl->setCurrentBlock("edit_note_form");
					$tpl->setVariable("EDIT_FORM", $this->form->getHTML());
					$tpl->parseCurrentBlock();
				}
				else
				{
					$cnt_col = 2;
					
					// delete note stuff for all private notes
					if ($this->checkDeletion($note)
						&& !$this->delete_note
						&& !$this->export_html && !$this->print
						&& !$this->edit_note_form && !$this->add_note_form)
					{
						$tpl->setCurrentBlock("delete_note");
						$tpl->setVariable("TXT_DELETE_NOTE", $lng->txt("delete"));
						$ilCtrl->setParameterByClass("ilnotegui", "note_id", $note->getId());
						$tpl->setVariable("LINK_DELETE_NOTE",
							$ilCtrl->getLinkTargetByClass("ilnotegui", "deleteNote")
							."#note_".$note->getId());
						$tpl->parseCurrentBlock();
					}
					
					// checkboxes in multiselection mode
					if ($this->multi_selection && !$this->delete_note)
					{
						$tpl->setCurrentBlock("checkbox_col");
						$tpl->setVariable("CHK_NOTE", "note[]");
						$tpl->setVariable("CHK_NOTE_ID", $note->getId());
						$tpl->parseCurrentBlock();
						$cnt_col = 1;
					}
					
					// edit note stuff for all private notes
					if ($this->checkEdit($note))
					{

						if (!$this->delete_note && !$this->export_html && !$this->print
							&& !$this->edit_note_form && !$this->add_note_form)
						{
							$tpl->setCurrentBlock("edit_note");
							$tpl->setVariable("TXT_EDIT_NOTE", $lng->txt("edit"));
							$ilCtrl->setParameterByClass("ilnotegui", "note_id", $note->getId());
							$tpl->setVariable("LINK_EDIT_NOTE",
								$ilCtrl->getLinkTargetByClass("ilnotegui", "editNoteForm")
								."#note_edit");
							$tpl->parseCurrentBlock();
						}
					}
					
					$tpl->setVariable("CNT_COL", $cnt_col);
					
					// output author account
					if ($a_type == IL_NOTE_PUBLIC && ilObject::_exists($note->getAuthor()))
					{
						$tpl->setCurrentBlock("author");
						$tpl->setVariable("VAL_AUTHOR", ilObjUser::_lookupLogin($note->getAuthor()));
						$tpl->parseCurrentBlock();
						$tpl->setCurrentBlock("user_img");
						$tpl->setVariable("USR_IMG",
							ilObjUser::_getPersonalPicturePath($note->getAuthor(), "xxsmall"));
						$tpl->setVariable("USR_ALT", $lng->txt("user_image").": ".
							ilObjUser::_lookupLogin($note->getAuthor()));
						$tpl->setVariable("TXT_USR",
							ilObjUser::_lookupLogin($note->getAuthor()));
						$tpl->parseCurrentBlock();
					}
					
					// last edited
					if ($note->getUpdateDate() != null)
					{
						$tpl->setCurrentBlock("last_edit");
						$tpl->setVariable("TXT_LAST_EDIT", $lng->txt("last_edited_on"));
						$tpl->setVariable("DATE_LAST_EDIT",
							ilDatePresentation::formatDate(new ilDateTime($note->getUpdateDate(), IL_CAL_DATETIME)));
						$tpl->parseCurrentBlock();
					}
					
					// hidden note ids for deletion
					if ($this->delete_note)
					{
						$tpl->setCurrentBlock("delete_ids");
						$tpl->setVariable("HID_NOTE", "note[]");
						$tpl->setVariable("HID_NOTE_ID", $note->getId());
						$tpl->parseCurrentBlock();						
					}
					$target = $note->getObject();
					
					// target objects							
					$this->showTargets($tpl, $this->rep_obj_id, $note->getId(),
						$target["obj_type"], $target["obj_id"]);
					
					$rowclass = ($rowclass != "tblrow1")
						? "tblrow1"
						: "tblrow2";
					if (!$this->export_html && !$this->print)
					{
						$tpl->setCurrentBlock("note_img");
						if ($a_type == IL_NOTE_PUBLIC)
						{
							$tpl->setVariable("IMG_NOTE", $this->comment_img[$note->getLabel()]["img"]);
							$tpl->setVariable("ALT_NOTE", $this->comment_img[$note->getLabel()]["alt"]);
						}
						else
						{
							$tpl->setVariable("IMG_NOTE", $this->note_img[$note->getLabel()]["img"]);
							$tpl->setVariable("ALT_NOTE", $this->note_img[$note->getLabel()]["alt"]);
						}
						$tpl->parseCurrentBlock();
					}
					else
					{
						switch ($note->getLabel())
						{
							case IL_NOTE_UNLABELED:
								$tpl->setVariable("EXP_ICON", "[&nbsp;]");
								break;
								
							case IL_NOTE_IMPORTANT:
								$tpl->setVariable("EXP_ICON", "[!]");
								break;
								
							case IL_NOTE_QUESTION:
								$tpl->setVariable("EXP_ICON", "[?]");
								break;

							case IL_NOTE_PRO:
								$tpl->setVariable("EXP_ICON", "[+]");
								break;
								
							case IL_NOTE_CONTRA:
								$tpl->setVariable("EXP_ICON", "[-]");
								break;
						}
					}
					$tpl->setCurrentBlock("note");
					$tpl->setVariable("ROWCLASS", $rowclass);
					$tpl->setVariable("TXT_DATE", $lng->txt("date"));
					$tpl->setVariable("TXT_CREATED", $lng->txt("create_date"));
					$tpl->setVariable("VAL_DATE",
						ilDatePresentation::formatDate(new ilDateTime($note->getCreationDate(), IL_CAL_DATETIME)));
					$tpl->setVariable("NOTE_TEXT", nl2br($note->getText()));
					$tpl->setVariable("VAL_SUBJECT", $note->getSubject());
					$tpl->setVariable("NOTE_ID", $note->getId());
					$tpl->parseCurrentBlock();
				}
				$tpl->setCurrentBlock("note_row");
				$tpl->parseCurrentBlock();
			}
			
			// multiple items commands
			if ($this->multi_selection && !$this->delete_note && !$this->edit_note_form
				&& count($notes) > 0)
			{
				if ($a_type == IL_NOTE_PRIVATE)
				{
					$tpl->setCurrentBlock("delete_cmd");
					$tpl->setVariable("TXT_DELETE_NOTES", $this->lng->txt("delete"));
					$tpl->parseCurrentBlock();
				}
				
				$tpl->setCurrentBlock("multiple_commands");
				$tpl->setVariable("TXT_SELECT_ALL", $this->lng->txt("select_all"));
				$tpl->setVariable("IMG_ARROW", ilUtil::getImagePath("arrow_downright.gif"));
				$tpl->setVariable("ALT_ARROW", $this->lng->txt("actions"));
				$tpl->setVariable("TXT_PRINT_NOTES", $this->lng->txt("print"));
				$tpl->setVariable("TXT_EXPORT_NOTES", $this->lng->txt("exp_html"));
				$tpl->parseCurrentBlock();
			}

			// delete / cancel row
			if ($this->delete_note)
			{
				$tpl->setCurrentBlock("delete_cancel");
				$tpl->setVariable("TXT_DEL_NOTES", $this->lng->txt("delete"));
				$tpl->setVariable("TXT_CANCEL_DEL_NOTES", $this->lng->txt("cancel"));
				$tpl->parseCurrentBlock();
			}
			
			// print
			if ($this->print)
			{
				$tpl->touchBlock("print_js");
				$tpl->setCurrentBlock("print_back");
				$tpl->setVariable("LINK_BACK", $this->ctrl->getLinkTarget($this, "showNotes"));
				$tpl->setVariable("TXT_BACK", $this->lng->txt("back"));
				$tpl->parseCurrentBlock();
			}
		}
		
		if ($this->delete_note && count($notes) == 0)
		{
			return "";
		}
		else
		{
			return $tpl->get();
		}
	}
	
	/**
	* Check whether deletion is allowed
	*/
	function checkDeletion($a_note)
	{
		global $ilUser;
		
		if ($ilUser->getId() == ANONYMOUS_USER_ID)
		{
			return false;
		}
		
		// delete note stuff for all private notes
		if (($a_note->getType() == IL_NOTE_PRIVATE && $a_note->getAuthor() == $ilUser->getId()) ||
			($a_note->getType() == IL_NOTE_PUBLIC &&
				($this->public_deletion_enabled))
			)
		{
			return true;
		}

		return false;
	}
	
	/**
	* Check edit
	*/
	function checkEdit($a_note)
	{
		global $ilUser;

		if ($a_note->getAuthor() == $ilUser->getId()
			&& ($ilUser->getId() != ANONYMOUS_USER_ID))
		{
			return true;
		}
		return false;
	}
	
	
	/**
	* Init note form.
	*
	* @param        int        $a_mode        Edit Mode
	*/
	public function initNoteForm($a_mode = "edit", $a_type, $a_note = null)
	{
		global $lng, $ilCtrl;

		include_once("Services/Form/classes/class.ilPropertyFormGUI.php");
		$this->form = new ilPropertyFormGUI();
		$this->form->setOpenTag(false);
		$this->form->setCloseTag(false);
		$this->form->setDisableStandardMessage(true);
	
		// subject
		$ti = new ilTextInputGUI($this->lng->txt("subject"), "sub_note");
		$ti->setRequired(true);
		$ti->setMaxLength(200);
		$ti->setSize(40);
		if ($a_note)
		{
			$ti->setValue($a_note->getSubject());
		}
		$this->form->addItem($ti);
		
		// text
		$ta = new ilTextAreaInputGUI(($a_type == IL_NOTE_PUBLIC)
			? $lng->txt("notes_comment")
			: $lng->txt("note"), "note");
		$ta->setCols(40);
		$ta->setRows(4);
		if ($a_note)
		{
			$ta->setValue($a_note->getText());
		}
		$this->form->addItem($ta);
		
		// label
		$options = array(
			IL_NOTE_UNLABELED => $lng->txt("unlabeled"),
			IL_NOTE_QUESTION => $lng->txt("question"),
			IL_NOTE_IMPORTANT => $lng->txt("important"),
			IL_NOTE_PRO => $lng->txt("pro"),
			IL_NOTE_CONTRA => $lng->txt("contra"),
			);
		$si = new ilSelectInputGUI($this->lng->txt("notes_label"), "note_label");
		$si->setOptions($options);
		if ($a_note)
		{
			$si->setValue($a_note->getLabel());
		}
		$this->form->addItem($si);
		
		// hidden note id
		if ($a_note)
		{ 
			$hi = new ilHiddenInputGUI("note_id");
			$hi->setValue($_GET["note_id"]);
			$this->form->addItem($hi);
		}

		// save and cancel commands
		if ($a_mode == "create")
		{
			$this->form->addCommandButton("addNote", $lng->txt("save"));
			$this->form->addCommandButton("cancelAddNote", $lng->txt("cancel"));
			$this->form->setTitle($a_type == IL_NOTE_PUBLIC
				? $lng->txt("notes_add_comment")
				: $lng->txt("notes_add_note"));
		}
		else
		{
			$this->form->addCommandButton("updateNote", $lng->txt("save"));
			$this->form->addCommandButton("cancelUpdateNote", $lng->txt("cancel"));
			$this->form->setTitle($a_type == IL_NOTE_PUBLIC
				? $lng->txt("notes_edit_comment")
				: $lng->txt("notes_edit_note"));
		}
		
		$ilCtrl->setParameter($this, "note_type", $a_type);
		$this->form->setFormAction($this->ctrl->getFormAction($this));
	 
	}
	
	/**
	* Note display for personal desktop
	*/
	function getPDNoteHTML($note_id)
	{
		global $lng, $ilCtrl, $ilUser;

		$tpl = new ilTemplate("tpl.pd_note.html", true, true, "Services/Notes");
		$note = new ilNote($note_id);
		$target = $note->getObject();
		
		if ($note->getAuthor() != $ilUser->getId())
		{
			return;
		}
		
		$img = ilUtil::getImagePath("note_".$note->getLabel().".gif");
		$alt = $lng->txt("note");
		
		$tpl->setCurrentBlock("edit_note");
		$ilCtrl->setParameterByClass("ilnotegui", "rel_obj", $target["rep_obj_id"]);
		$ilCtrl->setParameterByClass("ilnotegui", "note_id", $note_id);
		$ilCtrl->setParameterByClass("ilnotegui", "note_type", $note->getType());
		$tpl->setVariable("LINK_EDIT_NOTE",
			$ilCtrl->getLinkTargetByClass(array("ilpersonaldesktopgui", "ilpdnotesgui", "ilnotegui"),
			"editNoteForm"));
		$tpl->setVariable("TXT_EDIT_NOTE", $lng->txt("edit"));
		$tpl->parseCurrentBlock();
		$ilCtrl->clearParametersByClass("ilnotegui");
		
		$tpl->setCurrentBlock("note_img");
		$tpl->setVariable("IMG_NOTE", $this->note_img[$note->getLabel()]["img"]);
		$tpl->setVariable("ALT_NOTE", $this->note_img[$note->getLabel()]["alt"]);
		$tpl->parseCurrentBlock();
		
		// last edited
		if ($note->getUpdateDate() != null)
		{
			$tpl->setCurrentBlock("last_edit");
			$tpl->setVariable("TXT_LAST_EDIT", $lng->txt("last_edited_on"));
			$tpl->setVariable("DATE_LAST_EDIT",
				ilDatePresentation::formatDate(new ilDateTime($note->getUpdateDate(), IL_CAL_DATETIME)));
			$tpl->parseCurrentBlock();
		}

		$tpl->setVariable("TXT_CREATED", $lng->txt("create_date"));
		$tpl->setVariable("VAL_DATE",
			ilDatePresentation::formatDate(new ilDateTime($note->getCreationDate(), IL_CAL_DATETIME)));
		$tpl->setVariable("VAL_SUBJECT", $note->getSubject());
		$tpl->setVariable("NOTE_TEXT", nl2br($note->getText()));
		$this->showTargets($tpl, $target["rep_obj_id"], $note_id, $target["obj_type"], $target["obj_id"]);
		return $tpl->get();
	}
	
	/**
	* show related objects as links
	*/
	function showTargets(&$tpl, $a_rep_obj_id, $a_note_id, $a_obj_type, $a_obj_id)
	{
		global $tree, $ilAccess, $objDefinition;

		if ($this->targets_enabled)
		{
			if ($a_rep_obj_id > 0)
			{

				// get all visible references of target object
				$ref_ids = ilObject::_getAllReferences($a_rep_obj_id);
				$vis_ref_ids = array();

				foreach($ref_ids as $ref_id)
				{
					if ($ilAccess->checkAccess("visible", "", $ref_id))
					{
						$vis_ref_ids[] = $ref_id;
					}
				}

				// output links to targets
				if (count($vis_ref_ids) > 0)
				{
					foreach($vis_ref_ids as $vis_ref_id)
					{
						$type = ilObject::_lookupType($vis_ref_id, true);
						$sub_link = $sub_title = "";
						if ($type == "sahs")		// bad hack, needs general procedure
						{
							$link = "goto.php?target=sahs_".$vis_ref_id;
							$title = ilObject::_lookupTitle($a_rep_obj_id);
							if ($a_obj_type == "sco" || $a_obj_type == "seqc" || $a_obj_type == "chap" || $a_obj_type == "pg")
							{
								$sub_link = "goto.php?target=sahs_".$vis_ref_id."_".$a_obj_id;
								include_once("./Modules/Scorm2004/classes/class.ilScorm2004Node.php");
								$sub_title = ilScorm2004Node::_lookupTitle($a_obj_id);
								$sub_icon = ilUtil::getImagePath("icon_".$a_obj_type."_s.gif");
							}
						}
						else if ($a_obj_type != "pg")
						{
							if (!is_object($this->item_list_gui[$type]))
							{
								$class = $objDefinition->getClassName($type);
								$location = $objDefinition->getLocation($type);
								$full_class = "ilObj".$class."ListGUI";
								include_once($location."/class.".$full_class.".php");
								$this->item_list_gui[$type] = new $full_class();
							}
							$title = ilObject::_lookupTitle($a_rep_obj_id);
							$this->item_list_gui[$type]->initItem($vis_ref_id, $a_rep_obj_id, $title);
							$link = $this->item_list_gui[$type]->getCommandLink("infoScreen");
							
							// workaround, because # anchor can't be passed through frameset
							$link = ilUtil::appendUrlParameterString($link, "anchor=note_".$a_note_id);
							
							$link = $this->item_list_gui[$type]->appendRepositoryFrameParameter($link)."#note_".$a_note_id;
						}
						else
						{
							$title = ilObject::_lookupTitle($a_rep_obj_id);
							$link = "goto.php?target=pg_".$a_obj_id."_".$vis_ref_id;
						}
						
						$par_id = $tree->getParentId($vis_ref_id);
						
						// sub object link
						if ($sub_link != "")
						{
							if ($this->export_html || $this->print)
							{
								$tpl->setCurrentBlock("exp_target_sub_object");
							}
							else
							{
								$tpl->setCurrentBlock("target_sub_object");
								$tpl->setVariable("LINK_SUB_TARGET", $sub_link);
							}
							$tpl->setVariable("TXT_SUB_TARGET", $sub_title);
							$tpl->setVariable("IMG_SUB_TARGET", $sub_icon);
							$tpl->parseCurrentBlock();
						}
						
						// container and object link
						if ($this->export_html || $this->print)
						{
							$tpl->setCurrentBlock("exp_target_object");
						}
						else
						{
							$tpl->setCurrentBlock("target_object");
							$tpl->setVariable("LINK_TARGET", $link);
						}
						$tpl->setVariable("TXT_CONTAINER",
							ilObject::_lookupTitle(
							ilObject::_lookupObjId($par_id)));
						$tpl->setVariable("IMG_CONTAINER",
							ilObject::_getIcon(
							ilObject::_lookupObjId($par_id), "tiny"));
						$tpl->setVariable("TXT_TARGET", $title);
						$tpl->setVariable("IMG_TARGET",
							ilObject::_getIcon($a_rep_obj_id, "tiny"));

						$tpl->parseCurrentBlock();
					}
					$tpl->touchBlock("target_objects");
				}
			}
		}
	}

	/**
	* get notes list including add note area
	*/ 
	function addNoteForm($a_init_form = true)
	{
		global $ilUser;
		
		$suffix = ($_GET["note_type"] == IL_NOTE_PRIVATE)
			? "private"
			: "public";
		$ilUser->setPref("notes_".$suffix, "y");

		$this->add_note_form = true;
		return $this->getNotesHTML($a_init_form);
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
		global $ilUser, $lng, $ilCtrl;

		$this->initNoteForm("create", $_GET["note_type"]);

		if ($this->form->checkInput())
		{
			$note = new ilNote();
			$note->setObject($this->obj_type, $this->rep_obj_id, $this->obj_id);			
			$note->setType($_GET["note_type"]);
			$note->setAuthor($ilUser->getId());
			$note->setText($_POST["note"]);
			$note->setSubject($_POST["sub_note"]);
			$note->setLabel($_POST["note_label"]);
			$note->create();
			$ilCtrl->setParameter($this, "note_mess", "mod");
			$ilCtrl->redirect($this, "showNotes", "notes_top");
		}
		
		$this->note_mess = "frmfld";
		$this->form->setValuesByPost();
		return $this->addNoteForm(false);;
	}

	/**
	* update note
	*/
	function updateNote()
	{
		global $ilUser, $lng, $ilCtrl;

		$note = new ilNote(ilUtil::stripSlashes($_POST["note_id"]));
		$this->initNoteForm("edit", $note->getType(),
			$note);

		if ($this->form->checkInput())
		{
			$note->setText($_POST["note"]);
			$note->setSubject($_POST["sub_note"]);
			$note->setLabel($_POST["note_label"]);
			if ($this->checkEdit($note))
			{
				$note->update();
				$ilCtrl->setParameter($this, "note_mess", "mod");
			}
			$ilCtrl->redirect($this, "showNotes", "notes_top");
		}
		
		$this->note_mess = "frmfld";
		$this->form->setValuesByPost();
		$_GET["note_id"] = $note->getId();
		$_GET["note_type"] = $note->getType();
		return $this->editNoteForm(false);
	}
	
	/**
	* get notes list including add note area
	*/ 
	function editNoteForm($a_init_form = true)
	{
		$this->edit_note_form = true;
		
		return $this->getNotesHTML($a_init_form);
	}

	/**
	* delete note confirmation
	*/ 
	function deleteNote()
	{
		$this->delete_note = true;
		$this->note_mess = "qdel";
		return $this->getNotesHTML();
	}
	
	/**
	* delete notes confirmation
	*/ 
	function deleteNotes()
	{
		global $lng;
		
		if (!$_POST["note"])
		{
			$this->note_mess = "noc";
		}
		else
		{
			$this->delete_note = true;
			$this->note_mess = "qdel";
		}

		return $this->getNotesHTML();
	}

	/**
	* cancel deletion of note
	*/ 
	function cancelDelete()
	{
		return $this->getNotesHTML();
	}
	
	/**
	* cancel deletion of note
	*/ 
	function confirmDelete()
	{
		global $ilCtrl, $lng, $ilUser;

		$cnt = 0;
		foreach($_POST["note"] as $id)
		{
			$note = new ilNote($id);
			if ($this->checkDeletion($note))
			{
				$note->delete();
				$cnt++;
			}
		}
		if ($cnt > 1)
		{
			$ilCtrl->setParameter($this, "note_mess", "ntsdel");
		}
		else
		{
			$ilCtrl->setParameter($this, "note_mess", "ntdel");
		}
		$ilCtrl->redirect($this, "showNotes", "notes_top");
	}

	/**
	* export selected notes to html
	*/ 
	function exportNotesHTML()
	{
		$tpl = new ilTemplate("tpl.main.html", true, true);

		$this->export_html = true;
		$this->multi_selection = false;
		$tpl->setVariable("CONTENT", $this->getNotesHTML());
		ilUtil::deliverData($tpl->get(), "notes.html");
	}
	
	/**
	* notes print view screen
	*/
	function printNotes()
	{
		$tpl = new ilTemplate("tpl.main.html", true, true);

		$this->print = true;
		$this->multi_selection = false;
		$tpl->setVariable("CONTENT", $this->getNotesHTML());
		echo $tpl->get(); exit;
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

	/**
	* show all public notes to user
	*/
	function showAllPublicNotes()
	{
		global $ilUser;
		
		$ilUser->writePref("notes_pub_all", "y");
		
		return $this->getNotesHTML();
	}

	/**
	* show only public notes of user
	*/
	function showMyPublicNotes()
	{
		global $ilUser;
		
		$ilUser->writePref("notes_pub_all", "n");
		
		return $this->getNotesHTML();
	}
}
?>