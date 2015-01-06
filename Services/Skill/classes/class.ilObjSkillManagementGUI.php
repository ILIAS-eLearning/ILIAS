<?php

/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Object/classes/class.ilObjectGUI.php");

/**
 * Skill management main GUI class
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 *
 * @ilCtrl_Calls ilObjSkillManagementGUI: ilPermissionGUI, ilSkillProfileGUI
 * @ilCtrl_isCalledBy ilObjSkillManagementGUI: ilAdministrationGUI
 *
 * @ingroup ServicesSkill
 */
class ilObjSkillManagementGUI extends ilObjectGUI
{
	protected $skill_tree;
	
	/**
	 * Contructor
	 *
	 * @access public
	 */
	public function __construct($a_data, $a_id, $a_call_by_reference = true, $a_prepare_output = true)
	{
		global $ilCtrl;

		$this->type = 'skmg';
		parent::ilObjectGUI($a_data, $a_id, $a_call_by_reference, $a_prepare_output);

		$this->lng->loadLanguageModule('skmg');

		include_once("./Services/Skill/classes/class.ilSkillTree.php");
		$this->skill_tree = new ilSkillTree();

		$ilCtrl->saveParameter($this, "obj_id");
	}

	/**
	 * Execute command
	 *
	 * @access public
	 *
	 */
	public function executeCommand()
	{
		global $rbacsystem, $ilErr, $ilAccess, $ilTabs;

		$next_class = $this->ctrl->getNextClass($this);
		$cmd = $this->ctrl->getCmd();

		$this->prepareOutput();

		if(!$ilAccess->checkAccess('read','',$this->object->getRefId()))
		{
			$ilErr->raiseError($this->lng->txt('no_permission'),$ilErr->WARNING);
		}

		switch($next_class)
		{
			case 'ilskillrootgui':
				include_once("./Services/Skill/classes/class.ilSkillRootGUI.php");
				$skrt_gui = new ilSkillRootGUI((int) $_GET["obj_id"], $this);
				$skrt_gui->setParentGUI($this);
				$ret = $this->ctrl->forwardCommand($skrt_gui);
				break;

			case 'ilskillcategorygui':
				$this->tabs_gui->activateTab("skills");
				include_once("./Services/Skill/classes/class.ilSkillCategoryGUI.php");
				$scat_gui = new ilSkillCategoryGUI((int) $_GET["obj_id"]);
				$scat_gui->setParentGUI($this);
				$this->showTree(false, $scat_gui, "listItems");
				$ret = $this->ctrl->forwardCommand($scat_gui);
				break;

			case 'ilbasicskillgui':
				$this->tabs_gui->activateTab("skills");
				include_once("./Services/Skill/classes/class.ilBasicSkillGUI.php");
				$skill_gui = new ilBasicSkillGUI((int) $_GET["obj_id"]);
				$skill_gui->setParentGUI($this);
				$this->showTree(false, $skill_gui, "edit");
				$ret = $this->ctrl->forwardCommand($skill_gui);
				break;

			case 'ilskilltemplatecategorygui':
				$this->tabs_gui->activateTab("skill_templates");
				include_once("./Services/Skill/classes/class.ilSkillTemplateCategoryGUI.php");
				$sctp_gui = new ilSkillTemplateCategoryGUI((int) $_GET["obj_id"], (int) $_GET["tref_id"]);
				$sctp_gui->setParentGUI($this);
				$this->showTree(((int) $_GET["tref_id"] == 0), $sctp_gui, "listItems");
				$ret = $this->ctrl->forwardCommand($sctp_gui);
				break;

			case 'ilbasicskilltemplategui':
				$this->tabs_gui->activateTab("skill_templates");
				include_once("./Services/Skill/classes/class.ilBasicSkillTemplateGUI.php");
				$sktp_gui = new ilBasicSkillTemplateGUI((int) $_GET["obj_id"], (int) $_GET["tref_id"]);
				$sktp_gui->setParentGUI($this);
				$this->showTree(((int) $_GET["tref_id"] == 0), $sktp_gui, "edit");
				$ret = $this->ctrl->forwardCommand($sktp_gui);
				break;

			case 'ilskilltemplatereferencegui':
				$this->tabs_gui->activateTab("skills");
				include_once("./Services/Skill/classes/class.ilSkillTemplateReferenceGUI.php");
				$sktr_gui = new ilSkillTemplateReferenceGUI((int) $_GET["tref_id"]);
				$sktr_gui->setParentGUI($this);
				$this->showTree(false, $sktr_gui, "listItems");
				$ret = $this->ctrl->forwardCommand($sktr_gui);
				break;

			case "ilskillprofilegui":
				$ilTabs->activateTab("profiles");
				include_once("./Services/Skill/classes/class.ilSkillProfileGUI.php");
				$skprof_gui = new ilSkillProfileGUI();
				$ret = $this->ctrl->forwardCommand($skprof_gui);
				break;
				
			case 'ilpermissiongui':
				$this->tabs_gui->activateTab('permissions');
				include_once("Services/AccessControl/classes/class.ilPermissionGUI.php");
				$perm_gui =& new ilPermissionGUI($this);
				$ret = $this->ctrl->forwardCommand($perm_gui);
				break;

			default:
				if(!$cmd || $cmd == 'view')
				{
					$cmd = "editSkills";
				}
				
				if ($cmd == "showTree")
				{
					$this->showTree($_GET["templates_tree"]);
				}
				else
				{
					$this->$cmd();
				}
				break;
		}
		return true;
	}

	/**
	 * Get tabs
	 *
	 * @access public
	 *
	 */
	public function getAdminTabs()
	{
		global $rbacsystem, $ilAccess, $lng;

		if ($rbacsystem->checkAccess("visible,read",$this->object->getRefId()))
		{
			$this->tabs_gui->addTab("skills",
				$lng->txt("skmg_skills"),
				$this->ctrl->getLinkTarget($this, "editSkills"));

			$this->tabs_gui->addTab("skill_templates",
				$lng->txt("skmg_skill_templates"),
				$this->ctrl->getLinkTarget($this, "editSkillTemplates"));

			$this->tabs_gui->addTab("settings",
				$lng->txt("settings"),
				$this->ctrl->getLinkTarget($this, "editSettings"));

			$this->tabs_gui->addTab("profiles",
				$lng->txt("skmg_skill_profiles"),
				$this->ctrl->getLinkTargetByClass("ilskillprofilegui"));

			if (DEVMODE == 1)
			{
				$this->tabs_gui->addTab("test",
					"Test (DEVMODE)",
					$this->ctrl->getLinkTarget($this, "test"));
			}
		}

		if ($rbacsystem->checkAccess('edit_permission',$this->object->getRefId()))
		{
			$this->tabs_gui->addTab("permissions",
				$lng->txt("perm_settings"),
				$this->ctrl->getLinkTargetByClass('ilpermissiongui',"perm"));
		}
	}

	/**
	* Edit news settings.
	*/
	public function editSettings()
	{
		global $ilCtrl, $lng, $ilSetting, $ilTabs;

		$ilTabs->activateTab("settings");

		include_once("./Services/Skill/classes/class.ilSkillManagementSettings.php");
		$skmg_set = new ilSkillManagementSettings();
		$enable_skmg = $skmg_set->isActivated();

		include_once("./Services/Form/classes/class.ilPropertyFormGUI.php");
		$form = new ilPropertyFormGUI();
		$form->setFormAction($ilCtrl->getFormAction($this));
		$form->setTitle($lng->txt("skmg_settings"));
		
		// Enable skill management
		$cb_prop = new ilCheckboxInputGUI($lng->txt("skmg_enable_skmg"),
			"enable_skmg");
		$cb_prop->setValue("1");
		$cb_prop->setChecked($enable_skmg);
		$form->addItem($cb_prop);
		
		// command buttons
		$form->addCommandButton("saveSettings", $lng->txt("save"));

		$this->tpl->setContent($form->getHTML());
	}

	/**
	* Save skill management settings
	*/
	public function saveSettings()
	{
		global $ilCtrl, $ilSetting;
		
		include_once("./Services/Skill/classes/class.ilSkillManagementSettings.php");
		$skmg_set = new ilSkillManagementSettings();
		$skmg_set->activate((int) $_POST["enable_skmg"]);
		
		ilUtil::sendSuccess($this->lng->txt("settings_saved"),true);
		
		$ilCtrl->redirect($this, "editSettings");
	}

	/**
	 * Edit skills
	 */
	function editSkills()
	{
		global $tpl, $ilTabs, $lng, $ilCtrl;

		$ilTabs->activateTab("skills");

		$ilCtrl->setParameterByClass("ilobjskillmanagementgui", "obj_id", $this->skill_tree->getRootId());
		$ilCtrl->redirectByClass("ilskillrootgui", "listSkills");

	}


	/**
	 * Save all titles of chapters/scos/pages
	 */
	function saveAllTitles($a_succ_mess = true)
	{
		global $ilCtrl, $lng;

		if (is_array($_POST["title"]))
		{
			include_once("./Services/Skill/classes/class.ilSkillTreeNodeFactory.php");
			foreach($_POST["title"] as $id => $title)
			{
				$node_obj = ilSkillTreeNodeFactory::getInstance($id);
				if (is_object($node_obj))
				{
					// update title
					ilSkillTreeNode::_writeTitle($id, ilUtil::stripSlashes($title));
				}
			}
			if ($a_succ_mess)
			{
				ilUtil::sendSuccess($lng->txt("msg_obj_modified"), true);
			}
		}
		$ilCtrl->redirect($this, "editSkills");
	}

	/**
	 * Save all titles of chapters/scos/pages
	 */
	function saveAllTemplateTitles($a_succ_mess = true)
	{
		global $ilCtrl, $lng;

		if (is_array($_POST["title"]))
		{
			include_once("./Services/Skill/classes/class.ilSkillTreeNodeFactory.php");
			foreach($_POST["title"] as $id => $title)
			{
				$node_obj = ilSkillTreeNodeFactory::getInstance($id);
				if (is_object($node_obj))
				{
					// update title
					ilSkillTreeNode::_writeTitle($id, ilUtil::stripSlashes($title));
				}
			}
			if ($a_succ_mess)
			{
				ilUtil::sendSuccess($lng->txt("msg_obj_modified"), true);
			}
		}
		$ilCtrl->redirect($this, "editSkillTemplates");
	}


	/**
	 * Expand all
	 */
	function expandAll($a_redirect = true)
	{
		$_GET["skexpand"] = "";
		$n_id = ($_GET["obj_id"] > 0)
			? $_GET["obj_id"]
			: $this->skill_tree->readRootId();
		$stree = $this->skill_tree->getSubTree($this->skill_tree->getNodeData($n_id));
		$n_arr = array();
		foreach ($stree as $n)
		{
			$n_arr[] = $n["child"];
			$_SESSION["skexpand"] = $n_arr;
		}
		$this->saveAllTitles(false);
	}

	/**
	* Collapse all
	*/
	function collapseAll($a_redirect = true)
	{
		$_GET["skexpand"] = "";
		$n_id = ($_GET["obj_id"] > 0)
			? $_GET["obj_id"]
			: $this->skill_tree->readRootId();
		$stree = $this->skill_tree->getSubTree($this->skill_tree->getNodeData($n_id));
		$old = $_SESSION["skexpand"];
		foreach ($stree as $n)
		{
			if (in_array($n["child"], $old) && $n["child"] != $n_id)
			{
				$k = array_search($n["child"], $old);
				unset($old[$k]);
			}
		}
		$_SESSION["skexpand"] = $old;
		$this->saveAllTitles(false);
	}

	/**
	 * confirm deletion screen of skill tree nodes
	 */
	function deleteNodes($a_gui)
	{
		global $lng, $tpl, $ilCtrl, $ilTabs, $ilToolbar;

		if(!isset($_POST["id"]))
		{
			$this->ilias->raiseError($this->lng->txt("no_checkbox"),$this->ilias->error_obj->MESSAGE);
		}

		$ilTabs->clearTargets();

		// check usages
		$mode = "";
		$cskill_ids = array();
		foreach($_POST["id"] as $id)
		{
			if (in_array(ilSkillTreeNode::_lookupType($id), array("skll", "scat", "sktr")))
			{
				if ($mode == "templates")
				{
					$this->ilias->raiseError("Skill Deletion - type mismatch.",$this->ilias->error_obj->MESSAGE);
				}
				$mode = "basic";
				$skill_id = $id;
				$tref_id = 0;
				if (ilSkillTreeNode::_lookupType($id) == "sktr")
				{
					include_once("./Services/Skill/classes/class.ilSkillTemplateReference.php");
					$skill_id = ilSkillTemplateReference::_lookupTemplateId($id);
					$tref_id = $id;
				}
				$cskill_ids[] = array("skill_id" => $skill_id, "tref_id" => $tref_id);
			}
			if (in_array(ilSkillTreeNode::_lookupType($id), array("sktp", "sctp")))
			{
				if ($mode == "basic")
				{
					$this->ilias->raiseError("Skill Deletion - type mismatch.",$this->ilias->error_obj->MESSAGE);
				}
				$mode = "templates";

				foreach (ilSkillTemplateReference::_lookupTrefIdsForTemplateId($id) as $tref_id)
				{
					$cskill_ids[] = array("skill_id" => $id, "tref_id" => $tref_id);
				}
			}
			// for cats, skills and template references, get "real" usages
			// for skill and category templates check usage in references
		}

		if ($mode == "basic" || $mode == "templates")
		{
			include_once("./Services/Skill/classes/class.ilSkillUsage.php");
			$u = new ilSkillUsage();
			$usages = $u->getAllUsagesInfoOfSubtrees($cskill_ids);
			if (count($usages) > 0)
			{
				$html = "";
				foreach ($usages as $k => $usage)
				{
					include_once("./Services/Skill/classes/class.ilSkillUsageTableGUI.php");
					$tab = new ilSkillUsageTableGUI($this, "showUsage", $k, $usage);
					$html.= $tab->getHTML()."<br/><br/>";
				}
				$tpl->setContent($html);
				$ilToolbar->addButton($lng->txt("back"),
					$ilCtrl->getLinkTarget($a_gui, "cancelDelete"));
				ilUtil::sendFailure($lng->txt("skmg_cannot_delete_nodes_in_use"));
				return;
			}
		}
		else
		{
			$this->ilias->raiseError("Skill Deletion - type mismatch.",$this->ilias->error_obj->MESSAGE);
		}
		
		// SAVE POST VALUES
		$_SESSION["saved_post"] = $_POST["id"];

		include_once("./Services/Utilities/classes/class.ilConfirmationGUI.php");
		$confirmation_gui = new ilConfirmationGUI();

		$ilCtrl->setParameter($a_gui, "tmpmode", $_GET["tmpmode"]);
		$a_form_action = $this->ctrl->getFormAction($a_gui);
		$confirmation_gui->setFormAction($a_form_action);
		$confirmation_gui->setHeaderText($this->lng->txt("info_delete_sure"));

		// Add items to delete
		include_once("./Services/Skill/classes/class.ilSkillTreeNodeFactory.php");
		foreach($_POST["id"] as $id)
		{
			if ($id != IL_FIRST_NODE)
			{
				$node_obj = ilSkillTreeNodeFactory::getInstance($id);
				$confirmation_gui->addItem("id[]", $node_obj->getId(),
					$node_obj->getTitle(), ilUtil::getImagePath("icon_".$node_obj->getType().".svg"));
			}
		}

		$confirmation_gui->setCancel($lng->txt("cancel"), "cancelDelete");
		$confirmation_gui->setConfirm($lng->txt("confirm"), "confirmedDelete");

		$tpl->setContent($confirmation_gui->getHTML());
	}

	/**
	 * cancel delete
	 */
	function cancelDelete()
	{
		$this->ctrl->redirect($this, "editSkills");
	}

	/**
	 * Delete chapters/scos/pages
	 */
	function confirmedDelete()
	{
		global $ilCtrl;

		// delete all selected objects
		include_once("./Services/Skill/classes/class.ilSkillTreeNodeFactory.php");
		foreach ($_POST["id"] as $id)
		{
			if ($id != IL_FIRST_NODE)
			{
				$obj = ilSkillTreeNodeFactory::getInstance($id);
				$node_data = $this->skill_tree->getNodeData($id);
				if (is_object($obj))
				{
					$obj->delete();
				}
				if($this->skill_tree->isInTree($id))
				{
					$this->skill_tree->deleteTree($node_data);
				}
			}
		}

		// feedback
		ilUtil::sendInfo($this->lng->txt("info_deleted"),true);
	}

	//
	//
	//	Test
	//
	//

	/**
	 * Test getCompletionDateForTriggerRefId
	 *
	 * @param
	 * @return
	 */
	function test()
	{
		global $tpl, $lng, $ilCtrl, $ilTabs;

		$this->setTestSubTabs("test");

		$ilTabs->activateTab("test");

		include_once("./Services/Skill/classes/class.ilBasicSkill.php");

		include_once("Services/Form/classes/class.ilPropertyFormGUI.php");
		$this->form = new ilPropertyFormGUI();

		$this->form->addCommandButton("test", $lng->txt("execute"));

		$this->form->setTitle("getCompletionDateForTriggerRefId()");
		$this->form->setFormAction($ilCtrl->getFormAction($this));

		// user id
		$ti = new ilTextInputGUI("User ID(s)", "user_id");
		$ti->setMaxLength(200);
		$ti->setInfo("Separate multiple IDs by :");
		$ti->setValue($_POST["user_id"]);
		$this->form->addItem($ti);

		// ref id
		$ti = new ilTextInputGUI("Ref ID(s)", "ref_id");
		$ti->setMaxLength(200);
		$ti->setInfo("Separate multiple IDs by :");
		$ti->setValue($_POST["ref_id"]);
		$this->form->addItem($ti);

		$result = "";
		if (isset($_POST["user_id"]))
		{
			$user_ids = explode(":", $_POST["user_id"]);
			$ref_ids = explode(":", $_POST["ref_id"]);
			if (count($user_ids) <= 1)
			{
				$user_ids = $user_ids[0];
			}
			if (count($ref_ids) == 1)
			{
				$ref_ids = $ref_ids[0];
			}
			else if (count($ref_ids) == 0)
			{
				$ref_ids = null;
			}

			$result = ilBasicSkill::getCompletionDateForTriggerRefId($user_ids, $ref_ids);
			$result = "<br />Result:<br />".var_export($result, true);
		}

		$tpl->setContent($this->form->getHTML().$result);

	}

	/**
	 * Test checkUserCertificateForTriggerRefId
	 *
	 * @param
	 * @return
	 */
	function testCert()
	{
		global $tpl, $lng, $ilCtrl, $ilTabs;

		$this->setTestSubTabs("cert");
		$ilTabs->activateTab("test");

		include_once("./Services/Skill/classes/class.ilBasicSkill.php");

		include_once("Services/Form/classes/class.ilPropertyFormGUI.php");
		$this->form = new ilPropertyFormGUI();

		$this->form->addCommandButton("testCert", $lng->txt("execute"));

		$this->form->setTitle("checkUserCertificateForTriggerRefId()");
		$this->form->setFormAction($ilCtrl->getFormAction($this));

		// user id
		$ti = new ilTextInputGUI("User ID(s)", "user_id");
		$ti->setMaxLength(200);
		$ti->setInfo("Separate multiple IDs by :");
		$ti->setValue($_POST["user_id"]);
		$this->form->addItem($ti);

		// ref id
		$ti = new ilTextInputGUI("Ref ID(s)", "ref_id");
		$ti->setMaxLength(200);
		$ti->setInfo("Separate multiple IDs by :");
		$ti->setValue($_POST["ref_id"]);
		$this->form->addItem($ti);

		$result = "";
		if (isset($_POST["user_id"]))
		{
			$user_ids = explode(":", $_POST["user_id"]);
			$ref_ids = explode(":", $_POST["ref_id"]);
			if (count($user_ids) <= 1)
			{
				$user_ids = $user_ids[0];
			}
			if (count($ref_ids) == 1)
			{
				$ref_ids = $ref_ids[0];
			}
			else if (count($ref_ids) == 0)
			{
				$ref_ids = null;
			}

			$result = ilBasicSkill::checkUserCertificateForTriggerRefId($user_ids, $ref_ids);
			$result = "<br />Result:<br />".var_export($result, true);
		}

		$tpl->setContent($this->form->getHTML().$result);

	}

	/**
	 * Test getTriggerOfAllCertificates
	 *
	 * @param
	 * @return
	 */
	function testAllCert()
	{
		global $tpl, $lng, $ilCtrl, $ilTabs;

		$this->setTestSubTabs("all_cert");
		$ilTabs->activateTab("test");

		include_once("./Services/Skill/classes/class.ilBasicSkill.php");

		include_once("Services/Form/classes/class.ilPropertyFormGUI.php");
		$this->form = new ilPropertyFormGUI();

		$this->form->addCommandButton("testAllCert", $lng->txt("execute"));

		$this->form->setTitle("getTriggerOfAllCertificates()");
		$this->form->setFormAction($ilCtrl->getFormAction($this));

		// user id
		$ti = new ilTextInputGUI("User ID(s)", "user_id");
		$ti->setMaxLength(200);
		$ti->setInfo("Separate multiple IDs by :");
		$ti->setValue($_POST["user_id"]);
		$this->form->addItem($ti);

		$result = "";
		if (isset($_POST["user_id"]))
		{
			$user_ids = explode(":", $_POST["user_id"]);
			$ref_ids = explode(":", $_POST["ref_id"]);
			if (count($user_ids) <= 1)
			{
				$user_ids = $user_ids[0];
			}

			$result = ilBasicSkill::getTriggerOfAllCertificates($user_ids);
			$result = "<br />Result:<br />".var_export($result, true);
		}

		$tpl->setContent($this->form->getHTML().$result);

	}

	/**
	 * Test getSkillLevelsForTrigger
	 *
	 * @param
	 * @return
	 */
	function testLevels()
	{
		global $tpl, $lng, $ilCtrl, $ilTabs;

		$this->setTestSubTabs("levels");
		$ilTabs->activateTab("test");

		include_once("./Services/Skill/classes/class.ilBasicSkill.php");

		include_once("Services/Form/classes/class.ilPropertyFormGUI.php");
		$this->form = new ilPropertyFormGUI();

		$this->form->addCommandButton("testLevels", $lng->txt("execute"));

		$this->form->setTitle("getTriggerOfAllCertificates()");
		$this->form->setFormAction($ilCtrl->getFormAction($this));

		// user id
		$ti = new ilTextInputGUI("Ref ID", "ref_id");
		$ti->setMaxLength(200);
		$ti->setValue($_POST["ref_id"]);
		$this->form->addItem($ti);

		$result = "";
		if (isset($_POST["ref_id"]))
		{
			$result = ilBasicSkill::getSkillLevelsForTrigger($_POST["ref_id"]);
			$result = "<br />Result:<br />".var_export($result, true);
		}

		$tpl->setContent($this->form->getHTML().$result);

	}


	/**
	 * Set test subtabs
	 *
	 * @param
	 * @return
	 */
	function setTestSubtabs($a_act)
	{
		global $ilTabs, $ilCtrl;

		$ilTabs->addSubtab("test",
			"getCompletionDateForTriggerRefId",
			$ilCtrl->getLinkTarget($this, "test"));

		$ilTabs->addSubtab("cert",
			"checkUserCertificateForTriggerRefId",
			$ilCtrl->getLinkTarget($this, "testCert"));

		$ilTabs->addSubtab("all_cert",
			"getTriggerOfAllCertificates",
			$ilCtrl->getLinkTarget($this, "testAllCert"));

		$ilTabs->addSubtab("levels",
			"getSkillLevelsForTrigger",
			$ilCtrl->getLinkTarget($this, "testLevels"));

		$ilTabs->activateSubtab($a_act);

	}

	//
	// Skill Templates
	//
	
	/**
	 * Edit skill templates
	 */
	function editSkillTemplates()
	{
		global $tpl, $ilTabs, $lng, $ilCtrl;

		$ilTabs->activateTab("skill_templates");
		$ilCtrl->setParameterByClass("ilobjskillmanagementgui", "obj_id", $this->skill_tree->getRootId());
		$ilCtrl->redirectByClass("ilskillrootgui", "listTemplates");

	}

	//
	// Tree
	//
	
	/**
	 * Show Editing Tree
	 */
	function showTree($a_templates, $a_gui = "", $a_gui_cmd = "")
	{
		global $ilUser, $tpl, $ilCtrl, $lng;

		if ($a_templates)
		{
			if ($_GET["obj_id"] == "" || $_GET["obj_id"] == 1)
			{
				return;
			}

			if ($_GET["obj_id"] > 1)
			{
				$path = $this->skill_tree->getPathId($_GET["obj_id"]);
				include_once("./Services/Skill/classes/class.ilSkillTreeNode.php");
				if (ilSkillTreeNode::_lookupType($path[1]) == "sktp")
				{
					return;
				}
			}
		}
		
		$ilCtrl->setParameter($this, "templates_tree", $a_templates);
		
		if ($a_templates)
		{
			include_once("./Services/Skill/classes/class.ilSkillTemplateTreeExplorerGUI.php");
			$exp = new ilSkillTemplateTreeExplorerGUI($this, "showTree");
		}
		else
		{
			include_once("./Services/Skill/classes/class.ilSkillTreeExplorerGUI.php");
			$exp = new ilSkillTreeExplorerGUI($this, "showTree", $a_templates);
		}
		if (!$exp->handleCommand())
		{
			$tpl->setLeftNavContent($exp->getHTML());
		}
	}

}
?>