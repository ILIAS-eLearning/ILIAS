<?php

/* Copyright (c) 1998-2011 ILIAS open source, Extended GPL, see docs/LICENSE */


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
	var $repository_mode = false;
	
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
		
		$ilCtrl->saveParameter($this, "notes_only");
		$this->only = $_GET["notes_only"];

		$this->rep_obj_id = $a_rep_obj_id;
		$this->obj_id = $a_obj_id;
		$this->obj_type = $a_obj_type;
		$this->inc_sub = $a_include_subobjects;
		
		// auto-detect object type
		if(!$this->obj_type && $a_rep_obj_id)
		{
			$this->obj_type = ilObject::_lookupType($a_rep_obj_id);
		}
		
		$this->ajax = $ilCtrl->isAsynch();
		
		$this->ctrl =& $ilCtrl;
		$this->lng =& $lng;
		
		$this->anchor_jump = true;
		$this->add_note_form = false;
		$this->edit_note_form = false;
		$this->private_enabled = false;
		$notes_settings = new ilSetting("notes");
		$id = $this->rep_obj_id."_".$this->obj_id."_".$this->obj_type;
		//if ($notes_settings->get("activate_".$id))
		if (ilNote::commentsActivated($this->rep_obj_id, $this->obj_id, $this->obj_type))
		{
			$this->public_enabled = true;
		}
		else
		{
			$this->public_enabled = false;
		}
		$this->enable_hiding = false;
		$this->targets_enabled = false;
		$this->multi_selection = false;
		$this->export_html = false;
		$this->print = false;
		$this->comments_settings = false;
		
		$this->note_img = array(
			IL_NOTE_UNLABELED => array(
				"img" => ilUtil::getImagePath("note_unlabeled.svg"),
				"alt" => $lng->txt("note")),
			IL_NOTE_IMPORTANT => array(
				"img" => ilUtil::getImagePath("note_unlabeled.svg"),
				"alt" => $lng->txt("note").", ".$lng->txt("important")),
			IL_NOTE_QUESTION => array(
				"img" => ilUtil::getImagePath("note_unlabeled.svg"),
				"alt" => $lng->txt("note").", ".$lng->txt("question")),
			IL_NOTE_PRO => array(
				"img" => ilUtil::getImagePath("note_unlabeled.svg"),
				"alt" => $lng->txt("note").", ".$lng->txt("pro")),
			IL_NOTE_CONTRA => array(
				"img" => ilUtil::getImagePath("note_unlabeled.svg"),
				"alt" => $lng->txt("note").", ".$lng->txt("contra"))
			);
			
		$this->comment_img = array(
			IL_NOTE_UNLABELED => array(
				"img" => ilUtil::getImagePath("comment_unlabeled.svg"),
				"alt" => $lng->txt("notes_comment")),
			IL_NOTE_IMPORTANT => array(
				"img" => ilUtil::getImagePath("comment_unlabeled.svg"),
				"alt" => $lng->txt("notes_comment").", ".$lng->txt("important")),
			IL_NOTE_QUESTION => array(
				"img" => ilUtil::getImagePath("comment_unlabeled.svg"),
				"alt" => $lng->txt("notes_comment").", ".$lng->txt("question")),
			IL_NOTE_PRO => array(
				"img" => ilUtil::getImagePath("comment_unlabeled.svg"),
				"alt" => $lng->txt("notes_comment").", ".$lng->txt("pro")),
			IL_NOTE_CONTRA => array(
				"img" => ilUtil::getImagePath("comment_unlabeled.svg"),
				"alt" => $lng->txt("notes_comment").", ".$lng->txt("contra"))
			);
		
		// default: notes for repository objects
		$this->setRepositoryMode(true);
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
	
	/**
	 * Set repository mode
	 * 
	 * @param bool $a_value 
	 */
	function setRepositoryMode($a_value)
	{
		$this->repository_mode = (bool)$a_value;
	}

	
	/**
	 * Get only notes html
	 *
	 * @param
	 * @return
	 */
	function getOnlyNotesHTML()
	{
		global $ilCtrl;
		$ilCtrl->setParameter($this, "notes_only", "notes");
		$this->only = "notes";
		return $this->getNotesHTML($a_init_form = true);
	}
	
	/**
	 * Get only comments html
	 *
	 * @param
	 * @return
	 */
	function getOnlyCommentsHTML()
	{
		global $ilCtrl;
		$ilCtrl->setParameter($this, "notes_only", "comments");
		$this->only = "comments";
		return $this->getNotesHTML($a_init_form = true);
	}
	
	
	/***
	* get note lists html code
	*/
	function getNotesHTML($a_init_form = true)
	{
		global $ilUser, $lng, $ilCtrl, $ilSetting;
		
		$lng->loadLanguageModule("notes");

		$ntpl = new ilTemplate("tpl.notes_and_comments.html", true, true,
			"Services/Notes");

		// check, whether column is hidden due to processing in other column
		$hide_comments = ($this->only == "notes");
		$hide_notes = ($this->only == "comments");
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


// temp workaround: only show comments (if both have been activated)
if ($this->private_enabled && $this->public_enabled
	&& $this->only != "notes")
{
	$this->private_enabled = false;
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
		
		// #15948 - public enabled vs. comments_settings
		$comments_col = false; 
		if ($this->public_enabled && (!$this->delete_note || $this->public_deletion_enabled || $ilSetting->get("comments_del_user", 0))
			&& !$hide_comments /* && $ilUser->getId() != ANONYMOUS_USER_ID */)
		{
			$ntpl->setVariable("COMMENTS", $this->getNoteListHTML(IL_NOTE_PUBLIC, $a_init_form));
			$comments_col = true;
		}
		
		// Comments Settings
		if ($this->comments_settings && !$hide_comments && !$this->delete_note
			&& !$this->edit_note_form && !$this->add_note_form && $ilUser->getId() != ANONYMOUS_USER_ID)
		{
			$notes_settings = new ilSetting("notes");
			$id = $this->rep_obj_id."_".$this->obj_id."_".$this->obj_type;
			//$active = $notes_settings->get("activate_".$id);
			$active = ilNote::commentsActivated($this->rep_obj_id, $this->obj_id, $this->obj_type);

			if ($active)
			{
				$this->renderLink($ntpl, "comments_settings", $lng->txt("notes_deactivate_comments"),
					"deactivateComments", "notes_top");
				$ntpl->setCurrentBlock("comments_settings2");
			}
			else
			{
				$this->renderLink($ntpl, "comments_settings", $lng->txt("notes_activate_comments"),
					"activateComments", "notes_top");
				$ntpl->setCurrentBlock("comments_settings2");

				if ($this->ajax && !$comments_col)
				{
					$ntpl->setVariable("COMMENTS_MESS",
						$ntpl->getMessageHTML($lng->txt("comments_feature_currently_not_activated_for_object"), "info"));
				}
			}
			$ntpl->parseCurrentBlock();
			
			if (!$comments_col)
			{
				$ntpl->setVariable("COMMENTS", "");
			}
			
			$comments_col = true;
		}
		
		if ($comments_col)
		{
			$ntpl->setCurrentBlock("comments_col");
			if ($nodes_col)
			{
//				$ntpl->touchBlock("comments_style");
			}
			$ntpl->parseCurrentBlock();
		}

		if ($this->ajax)
		{
			echo $ntpl->get();
			exit;
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
			//$notes_settings->set("activate_".$id, 1);
			ilNote::activateComments($this->rep_obj_id, $this->obj_id, $this->obj_type, true);
		}
		
		$ilCtrl->redirectByClass("ilnotegui", "showNotes", "", $this->ajax);
//		$ilCtrl->redirectByClass("ilnotegui", "getNotesHtml", "", $this->ajax);
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
			//$notes_settings->set("activate_".$id, 0);
			ilNote::activateComments($this->rep_obj_id, $this->obj_id, $this->obj_type, false);
		}
		
		$ilCtrl->redirectByClass("ilnotegui", "showNotes", "", $this->ajax);
		//$ilCtrl->redirectByClass("ilnotegui", "getNotesHtml", "", $this->ajax);
	}

	/**
	* get notes/comments list as html code
	*/
	function getNoteListHTML($a_type = IL_NOTE_PRIVATE, $a_init_form = true)
	{
		global $lng, $ilCtrl, $ilUser;
		
		include_once("./Services/User/classes/class.ilUserUtil.php");

		$suffix = ($a_type == IL_NOTE_PRIVATE)
			? "private"
			: "public";
		
		/* user settings are deprecated
		$user_setting_notes_public_all = $ilUser->getPref("notes_pub_all");
		$user_setting_notes_by_type = $ilUser->getPref("notes_".$suffix);		 
		*/
		$user_setting_notes_public_all = "y";
		$user_setting_notes_by_type = "y";
		
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
			$user_setting_notes_public_all, $this->repository_mode, (bool)$_SESSION["comments_sort_asc"]);

		$all_notes = ilNote::_getNotesOfObject($this->rep_obj_id, $this->obj_id,
			$this->obj_type, $a_type, $this->inc_sub, $filter,
			"", $this->repository_mode);

		$tpl = new ilTemplate("tpl.notes_list.html", true, true, "Services/Notes");

		if ($this->ajax)
		{
			include_once("./Services/UIComponent/Glyph/classes/class.ilGlyphGUI.php");
			$tpl->setCurrentBlock("close_img");
			$tpl->setVariable("CLOSE_IMG", ilGlyphGUI::get(ilGlyphGUI::CLOSE));
			$tpl->parseCurrentBlock();
		}
		
		// show counter if notes are hidden
		$cnt_str = (count($all_notes) > 0)
			? " (".count($all_notes).")"
			: "";

		// title
		if ($this->ajax)
		{			
			switch($this->obj_type)
			{
				case "catr":
				case "crsr":
					include_once "Services/ContainerReference/classes/class.ilContainerReference.php";
					$title = ilContainerReference::_lookupTitle($this->rep_obj_id);
					break;
				
				default:
					$title = ilObject::_lookupTitle($this->rep_obj_id);
					break;
			}
			
			$img = ilUtil::img(ilObject::_getIcon($this->rep_obj_id, "tiny"));
			
			// add sub-object if given
			if($this->obj_id)
			{		
				$sub_title = $this->getSubObjectTitle($this->rep_obj_id, $this->obj_id);				
				if($sub_title)
				{
					$title .= " - ".$sub_title;
				}
			}
			
			$tpl->setCurrentBlock("title");
			$tpl->setVariable("TITLE", $img." ".$title);
			$tpl->parseCurrentBlock();
		}

		if ($this->delete_note)
		{
			$cnt_str = "";
		}
		if ($a_type == IL_NOTE_PRIVATE)
		{
			$tpl->setVariable("TXT_NOTES", $lng->txt("private_notes").$cnt_str);
			$ilCtrl->setParameterByClass("ilnotegui", "note_type", IL_NOTE_PRIVATE);
		}
		else
		{
			$tpl->setVariable("TXT_NOTES", $lng->txt("notes_public_comments").$cnt_str);
			$ilCtrl->setParameterByClass("ilnotegui", "note_type", IL_NOTE_PUBLIC);
		}
		$anch = $this->anchor_jump
			? "notes_top"
			: "";
		$tpl->setVariable("FORMACTION", $ilCtrl->getFormAction($this, "getNotesHTML", $anch));
		if ($this->ajax)
		{
			$os = "onsubmit = \"ilNotes.cmdAjaxForm(event, '".
				$ilCtrl->getFormActionByClass("ilnotegui", "", "", true).
				"'); return false;\"";
			$tpl->setVariable("ON_SUBMIT_FORM", $os);
			$tpl->setVariable("FORM_ID", "Ajax");
		}
		
		if ($this->export_html || $this->print)
		{
			$tpl->touchBlock("print_style");
		}
		
		// show add new note button
		if (!$this->add_note_form && !$this->edit_note_form && !$this->delete_note &&
			!$this->export_html && !$this->print &&	$ilUser->getId() != ANONYMOUS_USER_ID)
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
			if ($user_setting_notes_by_type == "n")
			{
				if ($a_type == IL_NOTE_PUBLIC)
				{
					$txt = $lng->txt("notes_show_comments");
				}
				else
				{
					$txt = $lng->txt("show_".$suffix."_notes");
				}
				$this->renderLink($tpl, "show_notes", $txt, "showNotes", "notes_top");
			}
			else
			{
				// never individually hide for anonymous users
				if (($ilUser->getId() != ANONYMOUS_USER_ID))
				{
					if ($a_type == IL_NOTE_PUBLIC)
					{
						$txt = $lng->txt("notes_hide_comments");
					}
					else
					{
						$txt = $lng->txt("hide_".$suffix."_notes");
					}
					$this->renderLink($tpl, "hide_notes", $txt, "hideNotes", "notes_top");
					
					// show all public notes / my notes only switch
					if ($a_type == IL_NOTE_PUBLIC)
					{
						if ($user_setting_notes_public_all == "n")
						{
							$this->renderLink($tpl, "all_pub_notes", $lng->txt("notes_all_comments"),
								"showAllPublicNotes", "notes_top");
						}
						else
						{
							$this->renderLink($tpl, "my_pub_notes", $lng->txt("notes_my_comments"),
								"showMyPublicNotes", "notes_top");
						}
					}
				}
			}
		}
		
		// show add new note text area
		if (!$this->edit_note_form && $user_setting_notes_by_type != "n" && 
			!$this->delete_note && $ilUser->getId() != ANONYMOUS_USER_ID)
		{
			if ($a_init_form)
			{
				$this->initNoteForm("create", $a_type);
			}

			$tpl->setCurrentBlock("edit_note_form");
//			$tpl->setVariable("EDIT_FORM", $this->form->getHTML());
			$tpl->setVariable("EDIT_FORM", $this->form_tpl->get());
			$tpl->parseCurrentBlock();

			$tpl->parseCurrentBlock();
			$tpl->setCurrentBlock("note_row");
			$tpl->parseCurrentBlock();
		}
		
		// list all notes
		if ($user_setting_notes_by_type != "n" || !$this->enable_hiding)
		{
			$reldates = ilDatePresentation::useRelativeDates();
			ilDatePresentation::setUseRelativeDates(false);
			
			if(sizeof($notes))
			{
				if((int)$_SESSION["comments_sort_asc"] == 1)
				{
					$sort_txt = $lng->txt("notes_sort_desc");
					$sort_cmd = "listSortDesc";
				}
				else
				{
					$sort_txt = $lng->txt("notes_sort_asc");
					$sort_cmd = "listSortAsc";
				}	
				$this->renderLink($tpl, "sort_list", $sort_txt, $sort_cmd, $anch);		
			}
			
			$notes_given = false;
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
//					$tpl->setVariable("EDIT_FORM", $this->form->getHTML());
					$tpl->setVariable("EDIT_FORM", $this->form_tpl->get());
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
						$ilCtrl->setParameterByClass("ilnotegui", "note_id", $note->getId());
						$this->renderLink($tpl, "delete_note", $lng->txt("delete"),
								"deleteNote", "note_".$note->getId());
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
							$ilCtrl->setParameterByClass("ilnotegui", "note_id", $note->getId());
							$this->renderLink($tpl, "edit_note", $lng->txt("edit"),
								"editNoteForm", "note_edit");
						}
					}
					
					$tpl->setVariable("CNT_COL", $cnt_col);
					
					// output author account
					if ($a_type == IL_NOTE_PUBLIC && ilObject::_exists($note->getAuthor()))
					{
						//$tpl->setCurrentBlock("author");
						//$tpl->setVariable("VAL_AUTHOR", ilObjUser::_lookupLogin($note->getAuthor()));
						//$tpl->parseCurrentBlock();
						$tpl->setCurrentBlock("user_img");
						$tpl->setVariable("USR_IMG",
							ilObjUser::_getPersonalPicturePath($note->getAuthor(), "xxsmall"));
						$tpl->setVariable("USR_ALT", $lng->txt("user_image").": ".
							ilObjUser::_lookupLogin($note->getAuthor()));
						$tpl->parseCurrentBlock();
						$tpl->setVariable("TXT_USR",
							ilUserUtil::getNamePresentation($note->getAuthor(), false, false)." - ");
					}
					
					// last edited
					if ($note->getUpdateDate() != null)
					{
						$tpl->setVariable("TXT_LAST_EDIT", $lng->txt("last_edited_on"));
						$tpl->setVariable("DATE_LAST_EDIT",
							ilDatePresentation::formatDate(new ilDate($note->getUpdateDate(), IL_CAL_DATETIME)));
					}
					else
					{
						$tpl->setVariable("VAL_DATE",
							ilDatePresentation::formatDate(new ilDate($note->getCreationDate(), IL_CAL_DATETIME)));
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
					$text = (trim($note->getText()) != "")
						? nl2br($note->getText())
						: "<p class='subtitle'>".$lng->txt("note_content_removed")."</p>";
					$tpl->setVariable("NOTE_TEXT", $text);
					$tpl->setVariable("VAL_SUBJECT", $note->getSubject());
					$tpl->setVariable("NOTE_ID", $note->getId());
					$tpl->setVariable("CLASS", $a_type == IL_NOTE_PUBLIC
						? "ilComment"
						: "ilNote");
					$tpl->parseCurrentBlock();
				}
				$tpl->setCurrentBlock("note_row");
				$tpl->parseCurrentBlock();
				$notes_given = true;
			}
			
			if (!$notes_given)
			{
				$tpl->setCurrentBlock("no_notes");
				if ($a_type == IL_NOTE_PUBLIC)
				{
					$tpl->setVariable("NO_NOTES", $lng->txt("notes_no_comments"));
				}
/*				else
				{
					$tpl->setVariable("NO_NOTES", $lng->txt("notes_no_notes"));
				}*/
				$tpl->parseCurrentBlock();
			}
			
			ilDatePresentation::setUseRelativeDates($reldates);			
			
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
				$tpl->setVariable("IMG_ARROW", ilUtil::getImagePath("arrow_downright.svg"));
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
		
		// message
		switch($_GET["note_mess"] != "" ? $_GET["note_mess"] : $this->note_mess)
		{
			case "mod":
				$mtype = "success";
				$mtxt = $lng->txt("msg_obj_modified");
				break;
				
			case "ntsdel":
				$mtype = "success";
				$mtxt = ($a_type == IL_NOTE_PRIVATE) 
					? $lng->txt("notes_notes_deleted")
					: $lng->txt("notes_comments_deleted");
				break;

			case "ntdel":
				$mtype = "success";
				$mtxt = ($a_type == IL_NOTE_PRIVATE) 
					? $lng->txt("notes_note_deleted")
					: $lng->txt("notes_comment_deleted");
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
			$tpl->setVariable("MESS", $tpl->getMessageHTML($mtxt, $mtype));
		}
		else
		{
			$tpl->setVariable("MESS", "");
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
	 * Get sub object title if available with callback
	 * 
	 * @param int $parent_obj_id
	 * @param int $sub_obj_id
	 * @return string 
	 */
	protected function getSubObjectTitle($parent_obj_id, $sub_obj_id)
	{
		global $objDefinition, $ilCtrl;
		
		$parent_type = ilObject::_lookupType($parent_obj_id);				
		$parent_class = "ilObj".$objDefinition->getClassName($parent_type)."GUI";						
		$parent_path = $ilCtrl->lookupClassPath($parent_class);
		include_once $parent_path;
		if(method_exists($parent_class, "lookupSubObjectTitle"))
		{
			return call_user_func_array(array($parent_class, "lookupSubObjectTitle"), array($parent_obj_id, $sub_obj_id));			
		}
	}
	
	/**
	* Check whether deletion is allowed
	*/
	function checkDeletion($a_note)
	{
		global $ilUser, $ilSetting;
		
		if ($ilUser->getId() == ANONYMOUS_USER_ID)
		{
			return false;
		}				
				
		$is_author = ($a_note->getAuthor() == $ilUser->getId());
		
		if ($a_note->getType() == IL_NOTE_PRIVATE && $is_author)
		{
			return true;
		}
		
		if ($a_note->getType() == IL_NOTE_PUBLIC && $this->public_deletion_enabled)
		{
			return true;
		}
		
		if ($a_note->getType() == IL_NOTE_PUBLIC && $is_author && $ilSetting->get("comments_del_user", 0))
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
		
		$this->form_tpl = new ilTemplate("tpl.notes_edit.html", true, true, "Services/Notes");
		if ($a_note)
		{
			$this->form_tpl->setVariable("VAL_NOTE", ilUtil::prepareFormOutput($a_note->getText()));
			$this->form_tpl->setVariable("NOTE_ID", $a_note->getId());
		}

		if ($a_mode == "create")
		{
			$this->form_tpl->setVariable("TXT_CMD", ($a_type == IL_NOTE_PUBLIC)
				? $lng->txt("note_add_comment")
				: $lng->txt("note_add_note"));
			$this->form_tpl->setVariable("CMD", "addNote");
		}
		else
		{
			$this->form_tpl->setVariable("TXT_CMD",  ($a_type == IL_NOTE_PUBLIC)
				? $lng->txt("note_update_comment")
				: $lng->txt("note_update_note"));
			$this->form_tpl->setVariable("CMD", "updateNote");
		}

return;
		include_once("Services/Form/classes/class.ilPropertyFormGUI.php");
		$this->form = new ilPropertyFormGUI();
		$this->form->setOpenTag(false);
		$this->form->setCloseTag(false);
		$this->form->setDisableStandardMessage(true);
	
		// subject
/*		$ti = new ilTextInputGUI($this->lng->txt("subject"), "sub_note");
		$ti->setRequired(true);
		$ti->setMaxLength(200);
		$ti->setSize(40);
		if ($a_note)
		{
			$ti->setValue($a_note->getSubject());
		}
		$this->form->addItem($ti);*/
		
		// text
//		$ta = new ilTextAreaInputGUI(($a_type == IL_NOTE_PUBLIC)
//			? $lng->txt("notes_comment")
//			: $lng->txt("note"), "note");
		$ta = new ilTextAreaInputGUI("", "note");
		$ta->setCols(40);
		$ta->setRows(4);
		if ($a_note)
		{
			$ta->setValue($a_note->getText());
		}
		$this->form->addItem($ta);
		
		// label
/*		$options = array(
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
		$this->form->addItem($si); */
		
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
/*			$this->form->addCommandButton("cancelAddNote", $lng->txt("cancel"));
			$this->form->setTitle($a_type == IL_NOTE_PUBLIC
				? $lng->txt("notes_add_comment")
				: $lng->txt("notes_add_note"));*/
		}
		else
		{
			$this->form->addCommandButton("updateNote", $lng->txt("save"));
/*			$this->form->addCommandButton("cancelUpdateNote", $lng->txt("cancel"));
			$this->form->setTitle($a_type == IL_NOTE_PUBLIC
				? $lng->txt("notes_edit_comment")
				: $lng->txt("notes_edit_note"));*/
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
			$tpl->setVariable("TXT_LAST_EDIT", $lng->txt("last_edited_on"));
			$tpl->setVariable("DATE_LAST_EDIT",
				ilDatePresentation::formatDate(new ilDate($note->getUpdateDate(), IL_CAL_DATETIME)));
		}
		else
		{
			//$tpl->setVariable("TXT_CREATED", $lng->txt("create_date"));
			$tpl->setVariable("VAL_DATE",
				ilDatePresentation::formatDate(new ilDate($note->getCreationDate(), IL_CAL_DATETIME)));
		}
		
		$tpl->setVariable("VAL_SUBJECT", $note->getSubject());
		$text = (trim($note->getText()) != "")
			? nl2br($note->getText())
			: "<p class='subtitle'>".$lng->txt("note_content_removed")."</p>";
		$tpl->setVariable("NOTE_TEXT", $text);
		$this->showTargets($tpl, $target["rep_obj_id"], $note_id, $target["obj_type"], $target["obj_id"]);
		return $tpl->get();
	}
	
	/**
	* show related objects as links
	*/
	function showTargets(&$tpl, $a_rep_obj_id, $a_note_id, $a_obj_type, $a_obj_id)
	{
		global $tree, $ilAccess, $objDefinition, $ilUser;

		if ($this->targets_enabled)
		{
			if ($a_rep_obj_id > 0)
			{
				// get all visible references of target object
				
				// repository
				$ref_ids = ilObject::_getAllReferences($a_rep_obj_id);
				if($ref_ids)
				{
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
									include_once("./Modules/Scorm2004/classes/class.ilSCORM2004Node.php");
									$sub_title = ilSCORM2004Node::_lookupTitle($a_obj_id);
									$sub_icon = ilUtil::getImagePath("icon_".$a_obj_type.".svg");
								}
							}
							else if ($type == "poll")
							{
								include_once "Services/Link/classes/class.ilLink.php";
								$title = ilObject::_lookupTitle($a_rep_obj_id);
								$link = ilLink::_getLink($vis_ref_id, "poll");
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
								
								// for references, get original title
								// (link will lead to orignal, which basically is wrong though)
								if($a_obj_type == "crsr" || $a_obj_type == "catr")
								{
									include_once "Services/ContainerReference/classes/class.ilContainerReference.php";
									$tgt_obj_id = ilContainerReference::_lookupTargetId($a_rep_obj_id);
									$title = ilObject::_lookupTitle($tgt_obj_id);
								}
								else
								{
									$title = ilObject::_lookupTitle($a_rep_obj_id);
								}
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
				// personal workspace
				else
				{
					// we only need 1 instance
					if(!$this->wsp_tree)
					{
						include_once "Services/PersonalWorkspace/classes/class.ilWorkspaceTree.php";
						include_once "Services/PersonalWorkspace/classes/class.ilWorkspaceAccessHandler.php";
						$this->wsp_tree = new ilWorkspaceTree($ilUser->getId());		
						$this->wsp_access_handler = new ilWorkspaceAccessHandler($this->wsp_tree);
					}
					$node_id = $this->wsp_tree->lookupNodeId($a_rep_obj_id);
					if($this->wsp_access_handler->checkAccess("visible", "", $node_id))
					{						
						$path = $this->wsp_tree->getPathFull($node_id);
						if($path)
						{						
							$item = array_pop($path);
							$parent = array_pop($path);

							if(!$parent["title"])
							{
								$parent["title"] = $this->lng->txt("wsp_personal_workspace");
							}
							
							// sub-objects
							$additional = null;
							if($a_obj_id)
							{
								$sub_title = $this->getSubObjectTitle($a_rep_obj_id, $a_obj_id);
								if($sub_title)
								{
									$item["title"] .= " (".$sub_title.")";	
									$additional = "_".$a_obj_id;
								}								
							}
														
							$link = ilWorkspaceAccessHandler::getGotoLink($node_id, $a_rep_obj_id, $additional);							
						}
						// shared resource
						else
						{					
							$owner = ilObject::_lookupOwner($a_rep_obj_id);
							$parent["title"] = $this->lng->txt("wsp_tab_shared").
								" (".ilObject::_lookupOwnerName($owner).")";
							$item["title"] = ilObject::_lookupTitle($a_rep_obj_id);
							$link = "ilias.php?baseClass=ilPersonalDesktopGUI&cmd=jumpToWorkspace&dsh=".
								$owner;
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
					
						
						// :TODO: no images in template ?
						
						$tpl->setVariable("TXT_CONTAINER", $parent["title"]);
						$tpl->setVariable("IMG_CONTAINER",
							ilObject::_getIcon($parent["obj_id"], "tiny"));
						
						$tpl->setVariable("TXT_TARGET", $item["title"]);
						$tpl->setVariable("IMG_TARGET",
							ilObject::_getIcon($a_rep_obj_id, "tiny"));

						$tpl->parseCurrentBlock();								
					}
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

		//if ($this->form->checkInput())
		if ($_POST["note"] != "")
		{
			$note = new ilNote();
			$note->setObject($this->obj_type, $this->rep_obj_id, $this->obj_id);	
			$note->setInRepository($this->repository_mode);
			$note->setType($_GET["note_type"]);
			$note->setAuthor($ilUser->getId());
			$note->setText(ilUtil::stripslashes($_POST["note"]));
//			$note->setSubject($_POST["sub_note"]);
//			$note->setLabel($_POST["note_label"]);
			$note->create();
			
			$this->notifyObserver("new", $note);
			
			$ilCtrl->setParameter($this, "note_mess", "mod");
//			$ilCtrl->redirect($this, "showNotes", "notes_top", $this->ajax);
		}
		$ilCtrl->redirect($this, "showNotes", "notes_top", $this->ajax);
//		$this->note_mess = "frmfld";
//		$this->form->setValuesByPost();
//		return $this->addNoteForm(false);;
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

//		if ($this->form->checkInput())
//		if ($_POST["note"] != "")
//		{
			$note->setText(ilUtil::stripSlashes($_POST["note"]));
			$note->setSubject(ilUtil::stripSlashes($_POST["sub_note"]));
			$note->setLabel(ilUtil::stripSlashes($_POST["note_label"]));
			if ($this->checkEdit($note))
			{
				$note->update();
				
				$this->notifyObserver("update", $note);
				
				$ilCtrl->setParameter($this, "note_mess", "mod");
			}
			$ilCtrl->redirect($this, "showNotes", "notes_top", $this->ajax);
//		}
$ilCtrl->redirect($this, "showNotes", "notes_top", $this->ajax);
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
		$ilCtrl->redirect($this, "showNotes", "notes_top", $this->ajax);
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
	
	/**
	 * Init javascript
	 */
	function initJavascript($a_ajax_url)
	{
		global $tpl;

		include_once("./Services/YUI/classes/class.ilYuiUtil.php");
		ilYuiUtil::initPanel();
		include_once("./Services/jQuery/classes/class.iljQueryUtil.php");
		iljQueryUtil::initjQuery();
		$tpl->addJavascript("./Services/Notes/js/ilNotes.js");

		$tpl->addOnLoadCode("ilNotes.setAjaxUrl('".$a_ajax_url."');");
	}
	
	/**
	 * Get list notes js call
	 *
	 * @param string $a_hash
	 * @param string $a_update_code
	 * @return string 
	 */
	function getListNotesJSCall($a_hash, $a_update_code = null)
	{
		if ($a_update_code === null)
		{
			$a_update_code = "null";
		}
		else
		{
			$a_update_code = "'".$a_update_code."'";
		}
		
		return "ilNotes.listNotes(event, '".$a_hash."', ".$a_update_code.");";
	}
	
	/**
	 * Get list comments js call
	 *
	 * @param string $a_hash
	 * @param string $a_update_code
	 * @return string 
	 */
	function getListCommentsJSCall($a_hash, $a_update_code = null)
	{
		if ($a_update_code === null)
		{
			$a_update_code = "null";
		}
		else
		{
			$a_update_code = "'".$a_update_code."'";
		}
		
		return "ilNotes.listComments(event, '".$a_hash."', ".$a_update_code.");";
	}
	
	/**
	 * Combine properties to hash
	 * 
	 * @param string $a_node_type
	 * @param int $a_node_id
	 * @param int $a_sub_id
	 * @param string $a_sub_type
	 * @return string 
	 */
	protected static function buildAjaxHash($a_node_type, $a_node_id, $a_sub_id, $a_sub_type)
	{
		return $a_node_type.";".$a_node_id.";".$a_sub_id.";".$a_sub_type;
	}
	
	/**
	 * Render a link
	 */
	function renderLink($a_tpl, $a_var, $a_txt, $a_cmd, $a_anchor = "")
	{
		global $ilCtrl;
		
		$low_var = strtolower($a_var);
		$up_var = strtoupper($a_var);

		if ($this->ajax)
		{
			$a_tpl->setVariable("LINK_".$up_var, "#");
			$oc = "onclick = \"ilNotes.cmdAjaxLink(event, '".
				$ilCtrl->getLinkTargetByClass("ilnotegui", $a_cmd, "", true).
				"');\"";
			$a_tpl->setVariable("ON_CLICK_".$up_var, $oc);
		}
		else
		{
			$a_tpl->setVariable("LINK_".$up_var,
				$ilCtrl->getLinkTargetByClass("ilnotegui", $a_cmd, $a_anchor));
		}
		
		$a_tpl->setCurrentBlock($low_var);
		$a_tpl->setVariable("TXT_".$up_var, $a_txt);
		$a_tpl->parseCurrentBlock();
	}

	/**
	 * Add observer
	 * 
	 * @param string|array $a_callback 
	 */
	function addObserver($a_callback)
	{
		$this->observer[] = $a_callback;
	}
	
	/**
	 * Notify observers on update/create
	 * 
	 * @param string $a_action
	 * @param ilNote $a_note 
	 */
	protected function notifyObserver($a_action, $a_note)
	{
		if(sizeof($this->observer))
		{
			foreach($this->observer as $item)
			{
				$param = $a_note->getObject();			
				$param["action"] = $a_action;
				$param["note_id"] = $a_note->getId();
				
				call_user_func_array($item, $param);				
			}
		}
	}	
	
	protected function listSortAsc()
	{
		$_SESSION["comments_sort_asc"] = 1;
		return $this->getNotesHtml();
	}
	
	protected function listSortDesc()
	{
		$_SESSION["comments_sort_asc"] = 0;
		return $this->getNotesHtml();
	}		
}

?>