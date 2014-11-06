<?php

/* Copyright (c) 1998-2012 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Skill/classes/class.ilSkillProfile.php");

/**
 * Skill profile GUI class
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 * @ilCtrl_Calls ilSkillProfileGUI: ilRepositorySearchGUI
 * @ingroup Skill/Profile
 */
class ilSkillProfileGUI
{
	protected $profile = null;
	
	/**
	 * Constructor
	 */
	function __construct()
	{
		global $ilCtrl;
		
		$ilCtrl->saveParameter($this, "sprof_id");
		
		if ((int) $_GET["sprof_id"] > 0)
		{
			$this->id = (int) $_GET["sprof_id"];
		}
		
		if ($this->id > 0)
		{
			$this->profile = new ilSkillProfile($this->id);
		}
	}
	
	/**
	 * Execute command
	 */
	function executeCommand()
	{
		global $ilCtrl, $lng;
		
		$cmd = $ilCtrl->getCmd("listProfiles");
		$next_class = $ilCtrl->getNextClass();
		switch($next_class)
		{
			case 'ilrepositorysearchgui':
				include_once('./Services/Search/classes/class.ilRepositorySearchGUI.php');
				$user_search = new ilRepositorySearchGUI();
				$user_search->setTitle($lng->txt('skmg_add_user_to_profile'));
				$user_search->setCallback($this, 'assignUser');

				// Set tabs
				//$this->tabs_gui->setTabActive('user_assignment');
				$ilCtrl->setReturn($this, 'listUsers');
				$ret = $ilCtrl->forwardCommand($user_search);
				break;
			
			default:
				if (in_array($cmd, array("listProfiles", "create", "edit", "save", "update",
					"confirmDeleteProfiles", "deleteProfiles", "showLevels", "assignLevel",
					"assignLevelSelectSkill", "assignLevelToProfile",
					"confirmLevelAssignmentRemoval", "removeLevelAssignments",
					"showUsers", "assignUser",
					"confirmUserRemoval", "removeUsers")))
				{
					$this->$cmd();
				}
				break;
		}
	}
	
	/**
	 * Set tabs
	 *
	 * @param
	 * @return
	 */
	function setTabs($a_active)
	{
		global $ilTabs, $lng, $ilCtrl, $tpl, $ilHelp;
		
		$tpl->setTitle($lng->txt("skmg_profile").": ".
			$this->profile->getTitle());
		$tpl->setDescription("");
		
		$ilTabs->clearTargets();
		$ilHelp->setScreenIdComponent("skmg_prof");
		
		$ilTabs->setBackTarget($lng->txt("back"),
			$ilCtrl->getLinkTarget($this, ""));

		// users
		$ilTabs->addTab("users",
			$lng->txt("skmg_assigned_users"),
			$ilCtrl->getLinkTarget($this, "showUsers"));
		
		// levels
		$ilTabs->addTab("levels",
			$lng->txt("skmg_assigned_skill_levels"),
			$ilCtrl->getLinkTarget($this, "showLevels"));
		
		// settings
		$ilTabs->addTab("settings",
			$lng->txt("settings"),
			$ilCtrl->getLinkTarget($this, "edit"));

		$ilTabs->activateTab($a_active);
	}
	
	
	/**
	 * List profiles
	 */
	function listProfiles()
	{
		global $tpl, $ilToolbar, $lng, $ilCtrl;
		
		$ilToolbar->addButton($lng->txt("skmg_add_profile"),
			$ilCtrl->getLinkTarget($this, "create"));
		
		include_once("./Services/Skill/classes/class.ilSkillProfileTableGUI.php");
		$tab = new ilSkillProfileTableGUI($this, "listProfiles");
		
		$tpl->setContent($tab->getHTML());
	}
	
	/**
	 * Create
	 */
	function create()
	{
		global $tpl;
		
		$form = $this->initProfileForm("create");
		$tpl->setContent($form->getHTML());
	}
	
	/**
	 * Edit
	 */
	function edit()
	{
		global $tpl;
		
		$this->setTabs("settings");
		$form = $this->initProfileForm("edit");
		$tpl->setContent($form->getHTML());
	}
	
	
	/**
	 * Init profile form.
	 *
	 * @param string $a_mode edit mode
	 */
	public function initProfileForm($a_mode = "edit")
	{
		global $lng, $ilCtrl;
	
		include_once("Services/Form/classes/class.ilPropertyFormGUI.php");
		$form = new ilPropertyFormGUI();
		
		// title
		$ti = new ilTextInputGUI($lng->txt("title"), "title");
		$ti->setMaxLength(200);
		$ti->setSize(40);
		$ti->setRequired(true);
		$form->addItem($ti);
		
		// description
		$desc = new ilTextAreaInputGUI($lng->txt("description"), "description");
		$desc->setCols(40);
		$desc->setRows(4);
		$form->addItem($desc);
	
		// save and cancel commands
		if ($a_mode == "create")
		{
			$form->addCommandButton("save", $lng->txt("save"));
			$form->addCommandButton("listProfiles", $lng->txt("cancel"));
			$form->setTitle($lng->txt("skmg_add_profile"));
		}
		else
		{
			// set values
			$ti->setValue($this->profile->getTitle());
			$desc->setValue($this->profile->getDescription());
			
			$form->addCommandButton("update", $lng->txt("save"));
			$form->addCommandButton("listProfiles", $lng->txt("cancel"));
			$form->setTitle($lng->txt("skmg_edit_profile"));
		}

		$form->setFormAction($ilCtrl->getFormAction($this));

		return $form;
	}
	
	/**
	 * Save profile form
	 */
	public function save()
	{
		global $tpl, $lng, $ilCtrl;
	
		$form = $this->initProfileForm("create");
		if ($form->checkInput())
		{
			$prof = new ilSkillProfile();
			$prof->setTitle($form->getInput("title"));
			$prof->setDescription($form->getInput("description"));
			$prof->create();
			ilUtil::sendSuccess($lng->txt("msg_obj_modified"), true);
			$ilCtrl->redirect($this, "listProfiles");
		}
		else
		{
			$form->setValuesByPost();
			$tpl->setContent($form->getHtml());
		}
	}
	
	/**
	 * Update 
	 */
	function update()
	{
		global $lng, $ilCtrl, $tpl;
		
		$form = $this->initProfileForm("edit");
		if ($form->checkInput())
		{
			$this->profile->setTitle($form->getInput("title"));
			$this->profile->setDescription($form->getInput("description"));
			$this->profile->update();
			
			ilUtil::sendInfo($lng->txt("msg_obj_modified"), true);
			$ilCtrl->redirect($this, "listProfiles");
		}
		else
		{
			$form->setValuesByPost();
			$tpl->setContent($form->getHtml());
		}
	}
	
	/**
	 * Confirm profile deletion
	 */
	function confirmDeleteProfiles()
	{
		global $ilCtrl, $tpl, $lng;
			
		if (!is_array($_POST["id"]) || count($_POST["id"]) == 0)
		{
			ilUtil::sendInfo($lng->txt("no_checkbox"), true);
			$ilCtrl->redirect($this, "listProfiles");
		}
		else
		{
			include_once("./Services/Utilities/classes/class.ilConfirmationGUI.php");
			$cgui = new ilConfirmationGUI();
			$cgui->setFormAction($ilCtrl->getFormAction($this));
			$cgui->setHeaderText($lng->txt("skmg_delete_profiles"));
			$cgui->setCancel($lng->txt("cancel"), "listProfiles");
			$cgui->setConfirm($lng->txt("delete"), "deleteProfiles");
			
			foreach ($_POST["id"] as $i)
			{
				$cgui->addItem("id[]", $i, ilSkillProfile::lookupTitle($i));
			}
			
			$tpl->setContent($cgui->getHTML());
		}
	}
	
	/**
	 * Delete profiles
	 */
	function deleteProfiles()
	{
		global $ilCtrl, $tpl, $lng;
			
		if (is_array($_POST["id"]))
		{
			foreach ($_POST["id"] as $i)
			{
				$prof = new ilSkillProfile($i);
				$prof->delete();
			}
			ilUtil::sendInfo($lng->txt("msg_obj_modified"), true);
		}
		
		$ilCtrl->redirect($this, "listProfiles");
	}
	
	////
	//// skill profile levels
	////
	
	/**
	 * Show skill levels
	 *
	 * @param
	 * @return
	 */
	function showLevels()
	{
		global $tpl, $ilCtrl, $lng, $ilToolbar;
		
		$this->setTabs("levels");
		
		$ilToolbar->addButton($lng->txt("skmg_assign_level"),
			$ilCtrl->getLinkTarget($this, "assignLevel")
			);
		
		include_once("./Services/Skill/classes/class.ilSkillProfileLevelsTableGUI.php");
		$tab = new ilSkillProfileLevelsTableGUI($this, "showLevels", $this->profile);
		$tpl->setContent($tab->getHTML());
	}
	
	/**
	 * Assign Level
	 */
	function assignLevel()
	{
		global $lng, $ilTabs, $ilCtrl, $tpl;
		
		$tpl->setTitle($lng->txt("skmg_profile").": ".
			$this->profile->getTitle());
		$tpl->setDescription("");

		//$this->setTabs("levels");
		
		ilUtil::sendInfo($lng->txt("skmg_select_skill_level_assign"));
		
		$ilTabs->clearTargets();
		$ilTabs->setBackTarget($lng->txt("back"),
			$ilCtrl->getLinkTarget($this, "showLevels"));

		include_once("./Services/Skill/classes/class.ilSkillSelectorGUI.php");
		$exp = new ilSkillSelectorGUI($this, "assignLevel", $this, "assignLevelSelectSkill", "cskill_id");
		if (!$exp->handleCommand())
		{
			$tpl->setContent($exp->getHTML());
		}
	}
	
	/**
	 * Output level table for profile assignment
	 */
	function assignLevelSelectSkill()
	{
		global $tpl, $lng, $ilCtrl, $ilTabs;

		$ilCtrl->saveParameter($this, "cskill_id");
		
		$tpl->setTitle($lng->txt("skmg_profile").": ".
			$this->profile->getTitle());
		$tpl->setDescription("");

		$ilTabs->clearTargets();
		$ilTabs->setBackTarget($lng->txt("back"),
			$ilCtrl->getLinkTarget($this, "showLevels"));

		include_once("./Services/Skill/classes/class.ilSkillLevelProfileAssignmentTableGUI.php");
		$tab = new ilSkillLevelProfileAssignmentTableGUI($this, "assignLevelSelectSkill",
			$_GET["cskill_id"]);
		$tpl->setContent($tab->getHTML());
	}
	
	/**
	 * Assign level to profile
	 */
	function assignLevelToProfile()
	{
		global $ilCtrl, $lng;

		$parts = explode(":", $_GET["cskill_id"]);

		$this->profile->addSkillLevel((int) $parts[0],
			(int) $parts[1], (int) $_GET["level_id"]);
		$this->profile->update();
		
		ilUtil::sendSuccess($lng->txt("msg_obj_modified"), true);
		$ilCtrl->redirect($this, "showLevels");
	}
	
	/**
	 * Confirm level assignment removal
	 */
	function confirmLevelAssignmentRemoval()
	{
		global $ilCtrl, $tpl, $lng;
		
		$this->setTabs("levels");
			
		if (!is_array($_POST["ass_id"]) || count($_POST["ass_id"]) == 0)
		{
			ilUtil::sendInfo($lng->txt("no_checkbox"), true);
			$ilCtrl->redirect($this, "showLevels");
		}
		else
		{
			include_once("./Services/Utilities/classes/class.ilConfirmationGUI.php");
			$cgui = new ilConfirmationGUI();
			$cgui->setFormAction($ilCtrl->getFormAction($this));
			$cgui->setHeaderText($lng->txt("skmg_confirm_remove_level_ass"));
			$cgui->setCancel($lng->txt("cancel"), "showLevels");
			$cgui->setConfirm($lng->txt("remove"), "removeLevelAssignments");
			
			include_once("./Services/Skill/classes/class.ilBasicSkill.php");
			foreach ($_POST["ass_id"] as $i)
			{
				$id_arr = explode(":", $i);
				$cgui->addItem("ass_id[]", $i,
					ilBasicSkill::_lookupTitle($id_arr[0]).": ".
					ilBasicSkill::lookupLevelTitle($id_arr[2]));
			}
			
			$tpl->setContent($cgui->getHTML());
		}
	}

	/**
	 * Remove level assignment
	 *
	 * @param
	 * @return
	 */
	function removeLevelAssignments()
	{
		global $ilCtrl;
		
		if (is_array($_POST["ass_id"]))
		{
			foreach ($_POST["ass_id"] as $i)
			{
				$id_arr = explode(":", $i);
				$this->profile->removeSkillLevel($id_arr[0], $id_arr[1], $id_arr[2]);
			}
			$this->profile->update();
		}
		
		$ilCtrl->redirect($this, "showLevels");
	}
	
	/**
	 * Show users
	 */
	function showUsers()
	{
		global $lng, $tpl, $ilToolbar;
		
		// add member
		include_once './Services/Search/classes/class.ilRepositorySearchGUI.php';
		ilRepositorySearchGUI::fillAutoCompleteToolbar(
			$this,
			$ilToolbar,
			array(
				'auto_complete_name'	=> $lng->txt('user'),
				'submit_name'			=> $lng->txt('skmg_assign_user')
			)
		);
		
		$this->setTabs("users");
		
		include_once("./Services/Skill/classes/class.ilSkillProfileUserTableGUI.php");
		$tab = new ilSkillProfileUserTableGUI($this, "showUsers",
			$this->profile);
		$tpl->setContent($tab->getHTML());
	}

	/**
	 * Assign user
	 *
	 * @param
	 * @return
	 */
	function assignUser()
	{
		global $ilCtrl, $lng;
		
		$user_id = ilObjUser::_lookupId(ilUtil::stripSlashes($_POST["user_login"]));
		if ($user_id > 0)
		{
			$this->profile->addUserToProfile($user_id);
			ilUtil::sendSuccess($lng->txt("msg_obj_modified"), true);
		}
	
		$ilCtrl->redirect($this, "showUsers");
	}
	
	/**
	 * Confirm user removal
	 */
	function confirmUserRemoval()
	{
		global $ilCtrl, $tpl, $lng;

		$this->setTabs("users");

		if (!is_array($_POST["id"]) || count($_POST["id"]) == 0)
		{
			ilUtil::sendInfo($lng->txt("no_checkbox"), true);
			$ilCtrl->redirect($this, "showUsers");
		}
		else
		{
			include_once("./Services/Utilities/classes/class.ilConfirmationGUI.php");
			$cgui = new ilConfirmationGUI();
			$cgui->setFormAction($ilCtrl->getFormAction($this));
			$cgui->setHeaderText($lng->txt("skmg_confirm_user_removal"));
			$cgui->setCancel($lng->txt("cancel"), "showUsers");
			$cgui->setConfirm($lng->txt("remove"), "removeUsers");

			foreach ($_POST["id"] as $i)
			{
				$name = ilObjUser::_lookupName($i);
				$cgui->addItem("id[]", $i,
					$name["lastname"].", ".$name["firstname"].
					" [".$name["login"]."]");
			}

			$tpl->setContent($cgui->getHTML());
		}
	}
	
	/**
	 * Remove users
	 */
	function removeUsers()
	{
		global $ilCtrl, $lng;

		if (is_array($_POST["id"]))
		{
			foreach ($_POST["id"] as $i)
			{
				$this->profile->removeUserFromProfile((int) $i);
			}
			ilUtil::sendSuccess($lng->txt("msg_obj_modified"), true);
		}
		$ilCtrl->redirect($this, "showUsers");
	}
}

?>
