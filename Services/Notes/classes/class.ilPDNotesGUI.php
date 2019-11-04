<?php

/* Copyright (c) 1998-2012 ILIAS open source, Extended GPL, see docs/LICENSE */


include_once("Services/Notes/classes/class.ilNote.php");

/**
* Private Notes on PD
*
* @author Alex Killing <alex.killing@gmx.de>
* @version $Id$
*
* @ilCtrl_Calls ilPDNotesGUI: ilNoteGUI
*
*/
class ilPDNotesGUI
{
	/**
	 * @var ilCtrl
	 */
	protected $ctrl;

	/**
	 * @var ilObjUser
	 */
	protected $user;

	/**
	 * @var ilTabsGUI
	 */
	protected $tabs;

	/**
	 * @var ilHelpGUI
	 */
	protected $help;

	/**
	 * @var ilSetting
	 */
	protected $settings;

	/**
	 * @var ilAccessHandler
	 */
	protected $access;

	/**
	 * @var ilToolbarGUI
	 */
	protected $toolbar;

	var $tpl;
	var $lng;
	
	const PUBLIC_COMMENTS = "publiccomments";
	const PRIVATE_NOTES = "privatenotes";

	/**
	* Constructor
	*
	* @access	public
	*/
	function __construct()
	{
		global $DIC;

		$this->user = $DIC->user();
		$this->tabs = $DIC->tabs();
		$this->help = $DIC["ilHelp"];
		$this->settings = $DIC->settings();
		$this->access = $DIC->access();
		$this->toolbar = $DIC->toolbar();
		$tpl = $DIC["tpl"];
		$lng = $DIC->language();
		$ilCtrl = $DIC->ctrl();
		$ilUser = $DIC->user();
		$ilTabs = $DIC->tabs();
		$ilHelp = $DIC["ilHelp"];

		$ilHelp->setScreenIdComponent("note");

		$lng->loadLanguageModule("notes");
		
		// initiate variables
		$this->tpl = $tpl;
		$this->lng = $lng;
		$this->ctrl = $ilCtrl;
		
		// link from ilPDNotesBlockGUI
		if($_GET["rel_obj"])
		{			
			$mode = ($_GET["note_type"] == IL_NOTE_PRIVATE) ? self::PRIVATE_NOTES : self::PUBLIC_COMMENTS;
			$ilUser->writePref("pd_notes_mode", $mode);
			$ilUser->writePref("pd_notes_rel_obj".$mode, $_GET["rel_obj"]);
		}		
		// edit link
		else if($_REQUEST["note_id"])
		{
			$note = new ilNote($_REQUEST["note_id"]);
			$mode = ($note->getType() == IL_NOTE_PRIVATE) ? self::PRIVATE_NOTES : self::PUBLIC_COMMENTS;
			$obj = $note->getObject();
			$ilUser->writePref("pd_notes_mode", $mode);
			$ilUser->writePref("pd_notes_rel_obj".$mode, $obj["rep_obj_id"]);
		}
	}

	/**
	* execute command
	*/
	function executeCommand()
	{
		$next_class = $this->ctrl->getNextClass();
		switch($next_class)
		{
			case "ilnotegui":
				// scorm2004-start
				$this->setTabs();
				// scorm2004-end
				$this->displayHeader();
				$this->view();		// forwardCommand is invoked in view() method
				break;
				
			default:
				// scorm2004-start
				$this->setTabs();
				// scorm2004-end
				$cmd = $this->ctrl->getCmd("view");
				$this->displayHeader();
				$this->$cmd();
				break;
		}
		$this->tpl->show(true);
		return true;
	}

	/**
	* display header and locator
	*/
	function displayHeader()
	{
		$ilSetting = $this->settings;

		$t = $this->lng->txt("notes");
		if (!$ilSetting->get("disable_notes") && !$ilSetting->get("disable_comments"))
		{
			$t = $this->lng->txt("notes_and_comments");
		}
		if ($ilSetting->get("disable_notes"))
		{
			$t = $this->lng->txt("notes_comments");
		}

		$this->tpl->setTitle($t);

		// catch feedback message
		// display infopanel if something happened
		ilUtil::infoPanel();

	}

	/*
	* display notes
	*/
	function view()
	{
		$ilUser = $this->user;
		$lng = $this->lng;
		$ilSetting = $this->settings;
		$ilAccess = $this->access;
		$ilToolbar = $this->toolbar;

		//$this->tpl->addBlockFile("ADM_CONTENT", "objects", "tpl.table.html")
		include_once("Services/Notes/classes/class.ilNoteGUI.php");
				
		// output related item selection (if more than one)
		include_once("Services/Notes/classes/class.ilNote.php");
		$rel_objs = ilNote::_getRelatedObjectsOfUser($this->getMode());
//var_dump($rel_objs);
		// prepend personal dektop, if first object 
//		if ($rel_objs[0]["rep_obj_id"] > 0 && $this->getMode() == ilPDNotesGUI::PRIVATE_NOTES)
		if ($this->getMode() == ilPDNotesGUI::PRIVATE_NOTES)
		{

			$rel_objs = array_merge(array(0), $rel_objs);
		}

		// #9410
		if(!$rel_objs && $this->getMode() == ilPDNotesGUI::PUBLIC_COMMENTS)
		{
			$lng->loadLanguageModule("notes");
			ilUtil::sendInfo($lng->txt("msg_no_search_result"));
			return;
		}

		$first = true;
		foreach ($rel_objs as $r)
		{
			if ($first)	// take first one as default
			{
				$this->current_rel_obj = $r["rep_obj_id"];				
				$current_ref_ids = $r["ref_ids"];				
			}
			if ($r["rep_obj_id"] == $ilUser->getPref("pd_notes_rel_obj".$this->getMode()))
			{
				$this->current_rel_obj = $r["rep_obj_id"];
				$current_ref_ids = $r["ref_ids"];		
			}
			$first = false;
		}		
		if ($this->current_rel_obj > 0)
		{
			$notes_gui = new ilNoteGUI($this->current_rel_obj, 0,
				ilObject::_lookupType($this->current_rel_obj),true);
		}
		else
		{
			$notes_gui = new ilNoteGUI(0, $ilUser->getId(), "pd");
		}
		
		if ($this->getMode() == ilPDNotesGUI::PRIVATE_NOTES)
		{
			$notes_gui->enablePrivateNotes(true);
			$notes_gui->enablePublicNotes(false);
		}
		else
		{
			$notes_gui->enablePrivateNotes(false);
			$notes_gui->enablePublicNotes(true);
			
			// #13707
			if ($this->current_rel_obj > 0 && 
				sizeof($current_ref_ids) &&
				$ilSetting->get("comments_del_tutor", 1)) 
			{
				foreach($current_ref_ids as $ref_id)
				{
					if($ilAccess->checkAccess("write", "", $ref_id))
					{
						$notes_gui->enablePublicNotesDeletion(true);
						break;
					}	
				}
			}																	
		}
		$notes_gui->enableHiding(false);
		$notes_gui->enableTargets(true);
		$notes_gui->enableMultiSelection(true);
		$notes_gui->enableAnchorJump(false);

		$next_class = $this->ctrl->getNextClass($this);

		if ($next_class == "ilnotegui")
		{
			$html = $this->ctrl->forwardCommand($notes_gui);
		}
		else
		{
			if ($this->getMode() == ilPDNotesGUI::PRIVATE_NOTES)
			{
				$html = $notes_gui->getOnlyNotesHTML();
			}
			else
			{
				$html = $notes_gui->getOnlyCommentsHTML();
			}			
		}
		
		if (count($rel_objs) > 1 ||
			($rel_objs[0]["rep_obj_id"] > 0))
		{			
			$ilToolbar->setFormAction($this->ctrl->getFormAction($this));
			
			foreach($rel_objs as $obj)
			{				
				if ($obj["rep_obj_id"] > 0)
				{
					$type = ilObject::_lookupType($obj["rep_obj_id"]);
					$type_str = (in_array($type, array("lm", "htlm", "sahs")))
						? $lng->txt("learning_module")
						: $lng->txt("obj_".$type);
					$caption = $type_str.": ".ilObject::_lookupTitle($obj["rep_obj_id"]);
				}
				else
				{
					$caption = $lng->txt("personal_desktop");
				}
				
				$options[$obj["rep_obj_id"]] = $caption;				
			}
			
			include_once "Services/Form/classes/class.ilSelectInputGUI.php";
			$rel = new ilSelectInputGUI($lng->txt("related_to"), "rel_obj");
			$rel->setOptions($options);
			$rel->setValue($this->current_rel_obj);			
			$ilToolbar->addStickyItem($rel);

			$btn = ilSubmitButton::getInstance();
			$btn->setCaption('change');
			$btn->setCommand('changeRelatedObject');
			$ilToolbar->addStickyItem($btn);
		}
		
		$this->tpl->setContent($html);	
	}
	
	/**
	* change related object
	*/
	function changeRelatedObject()
	{
		$ilUser = $this->user;
		
		$ilUser->writePref("pd_notes_rel_obj".$this->getMode(), $_POST["rel_obj"]);				
		$this->ctrl->redirect($this);
	}

	// scorm2004-start
	/**
	* Show subtabs
	*/
	function setTabs()
	{
		$ilTabs = $this->tabs;
		$ilSetting = $this->settings;
		$ilCtrl = $this->ctrl;

		if(!$ilSetting->get("disable_notes"))
		{
			$ilTabs->addTarget("private_notes",
				$ilCtrl->getLinkTarget($this, "showPrivateNotes"), "", "", "",
				($this->getMode() == ilPDNotesGUI::PRIVATE_NOTES));
		}

		if(!$ilSetting->get("disable_comments"))
		{
			$ilTabs->addTarget("notes_public_comments",
				$ilCtrl->getLinkTarget($this, "showPublicComments"), "", "", "",
				($this->getMode() == ilPDNotesGUI::PUBLIC_COMMENTS));
		}
	}
	
	/**
	* Show private notes
	*/
	function showPrivateNotes()
	{
		$ilUser = $this->user;
		$ilCtrl = $this->ctrl;
		
		$ilUser->writePref("pd_notes_mode", ilPDNotesGUI::PRIVATE_NOTES);
		$ilCtrl->redirect($this, "");
	}
	
	/**
	* Show public comments
	*/
	function showPublicComments()
	{
		$ilUser = $this->user;
		$ilCtrl = $this->ctrl;
		$ilSetting = $this->settings;
		
		if($ilSetting->get("disable_comments"))
		{
			$ilCtrl->redirect($this, "showPrivateNotes");
		}
		
		$ilUser->writePref("pd_notes_mode", ilPDNotesGUI::PUBLIC_COMMENTS);
		$ilCtrl->redirect($this, "");
	}

	/**
	* Get current mode
	*/
	function getMode()
	{
		$ilUser = $this->user;
		$ilSetting = $this->settings;
		
		if ($ilUser->getPref("pd_notes_mode") == ilPDNotesGUI::PUBLIC_COMMENTS &&
			!$ilSetting->get("disable_comments"))
		{
			return ilPDNotesGUI::PUBLIC_COMMENTS;
		}
		else
		{
			return ilPDNotesGUI::PRIVATE_NOTES;
		}
	}
}
?>
