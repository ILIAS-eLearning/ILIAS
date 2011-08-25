<?php

/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Skill/classes/class.ilPersonalSkill.php");

/**
 * Personal skills GUI class
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 *
 * @ilCtrl_Calls ilPersonalSkillsGUI:
 *
 * @ingroup ServicesSkill
 */
class ilPersonalSkillsGUI
{
	protected $skill_tree;
	
	/**
	 * Contructor
	 *
	 * @access public
	 */
	public function __construct()
	{
		global $ilCtrl, $lng;

		$lng->loadLanguageModule('skmg');
		
		$ilCtrl->saveParameter($this, "skill_id");

		include_once("./Services/Skill/classes/class.ilSkillTree.php");
		$this->skill_tree = new ilSkillTree();
	}

	/**
	 * Execute command
	 *
	 * @access public
	 *
	 */
	public function executeCommand()
	{
		global $ilCtrl, $tpl, $lng;

		$next_class = $ilCtrl->getNextClass($this);
		$cmd = $ilCtrl->getCmd("listSkills");
		
		$tpl->setTitle($lng->txt("skills"));
		$tpl->setTitleIcon(ilUtil::getImagePath("icon_skmg_b.gif"));

		switch($next_class)
		{
			default:
				$this->$cmd();
				break;
		}
		return true;
	}

	/**
	 * Set tabs
	 */
	function setTabs($a_activate)
	{
		global $ilTabs, $lng, $ilCtrl;
		
		// list skills
		$ilTabs->addTab("list_skills",
			$lng->txt("skmg_list_skills"),
			$ilCtrl->getLinkTarget($this, "listSkills"));

		// assign materials
/*		$ilTabs->addTab("assign_materials",
			$lng->txt("skmg_assign_materials"),
			$ilCtrl->getLinkTarget($this, "assignMaterials"));*/

		$ilTabs->activateTab($a_activate);
	}

	/**
	 * List skills
	 */
	function listSkills()
	{
		global $tpl, $ilTabs, $lng, $ilCtrl, $ilToolbar, $ilUser;

		$this->setTabs("list_skills");
		
		
		// skill selection / add new personal skill
		include_once("./Services/Skill/classes/class.ilSkillTreeNode.php");
		$skills = ilSkillTreeNode::getSelectableSkills();
		$options = array();
		foreach ($skills as $s)
		{
			$options[$s["obj_id"]] = $s["title"];
		}
		include_once("./Services/Form/classes/class.ilSelectInputGUI.php");
		$si = new ilSelectInputGUI($lng->txt("skmg_skill"), "skill_node_id");
		$si->setOptions($options);
		$ilToolbar->addInputItem($si);
		
		$ilToolbar->addFormButton($lng->txt("skmg_add_skill"),
			"addPersonalSkill");
		$ilToolbar->setFormAction($ilCtrl->getFormAction($this));
		
		$skills = ilPersonalSkill::getSelectedUserSkills($ilUser->getId());
		$html = "";
		foreach ($skills as $s)
		{
			$html.= $this->getSkillHTML($s["skill_node_id"]);
		}
		
		// list skills
		include_once("./Services/Skill/classes/class.ilPersonalSkillTableGUI.php");
		$sktab = new ilPersonalSkillTableGUI($this, "listSkills");
		
		$tpl->setContent($html.$sktab->getHTML());

	}

	/**
	 * Get skill presentation HTML
	 *
	 * @return
	 */
	function getSkillHTML($a_top_skill_id, $a_user_id = 0, $a_edit = false)
	{
		global $ilUser, $lng;
		
		if ($a_user_id == 0)
		{
			$user = $ilUser;
		}
		else
		{
			$user = new ilObjectUser($a_user_id);
		}

		$tpl = new ilTemplate("tpl.skill_pres.html", true, true, "Services/Skill");
		
		include_once("./Services/Skill/classes/class.ilSkillTree.php");
		$stree = new ilSkillTree();
		
		include_once("./Services/Skill/classes/class.ilSkillTreeNode.php");
		include_once("./Services/Skill/classes/class.ilSkillTreeNodeFactory.php");
		
		$b_skills = ilSkillTreeNode::getSkillTreeNodes($a_top_skill_id, true);
		foreach ($b_skills as $bs)
		{
			$skill = ilSkillTreeNodeFactory::getInstance($bs["id"]);
			foreach ($skill->getLevelData() as $k => $v)
			{

				// level
				$tpl->setCurrentBlock("level_td");
				$tpl->setVariable("VAL_LEVEL", $v["title"]);
				$tpl->parseCurrentBlock();
				
				// self evaluation
				$tpl->setCurrentBlock("self_eval_td");
				$tpl->setVariable("VAL_SELF_EVAL", "x");
				$tpl->parseCurrentBlock();

				// self evaluation
				$tpl->setCurrentBlock("material_td");
				$tpl->setVariable("VAL_MATERIAL", "3");
				$tpl->parseCurrentBlock();
			}
			
			$path = $stree->getPathFull($bs["id"]);
			$title = $sep = "";
			foreach ($path as $p)
			{
				if ($p["type"] != "skrt")
				{
					$title.= $sep.$p["title"];
					$sep = " > ";
				}
			}

			$tpl->setCurrentBlock("skill");
			$tpl->setVariable("BSKILL_TITLE", $title);
			$tpl->setVariable("TXT_LEVEL", $lng->txt("skmg_level"));
			$tpl->setVariable("TXT_SELF_EVAL", $lng->txt("skmg_self_evaluation"));
			$tpl->setVariable("TXT_MATERIAL", $lng->txt("skmg_material"));
			$tpl->parseCurrentBlock();
		}
		
		$tpl->setVariable("SKILL_TITLE", ilSkillTreeNode::_lookupTitle($a_top_skill_id));
		
		return $tpl->get();
	}
	
	
	/**
	 * Add personal skill
	 */
	function addPersonalSkill()
	{
		global $ilUser, $ilCtrl, $lng;
		
		ilPersonalSkill::addPersonalSkill($ilUser->getId(), (int) $_POST["skill_node_id"]);
		
		ilUtil::sendSuccess($lng->txt("msg_object_modified"));
		$ilCtrl->redirect($this, "listSkills");
	}
	
	/**
	 * Confirm skill remove
	 */
	function confirmSkillRemove()
	{
		global $ilCtrl, $tpl, $lng;
			
		include_once("./Services/Skill/classes/class.ilSkillTreeNode.php");
		
		if (!is_array($_POST["id"]) || count($_POST["id"]) == 0)
		{
			ilUtil::sendInfo($lng->txt("no_checkbox"), true);
			$ilCtrl->redirect($this, "listSkills");
		}
		else
		{
			include_once("./Services/Utilities/classes/class.ilConfirmationGUI.php");
			$cgui = new ilConfirmationGUI();
			$cgui->setFormAction($ilCtrl->getFormAction($this));
			$cgui->setHeaderText($lng->txt("skmg_really_remove_skills"));
			$cgui->setCancel($lng->txt("cancel"), "listSkills");
			$cgui->setConfirm($lng->txt("delete"), "removeSkills");
			
			foreach ($_POST["id"] as $i)
			{
				$cgui->addItem("id[]", $i, ilSkillTreeNode::_lookupTitle($i));
			}
			
			$tpl->setContent($cgui->getHTML());
		}
	}
	
	/**
	 * Remove skills
	 */
	function removeSkills()
	{
		global $ilUser, $lng, $ilCtrl;
		
		if (is_array($_POST["id"]))
		{
			foreach ($_POST["id"] as $n_id)
			{
				ilPersonalSkill::removeSkill($ilUser->getId(), $n_id);
			}
		}
		
		ilUtil::sendSuccess($lng->txt("msg_object_modified"));
		$ilCtrl->redirect($this, "listSkills");
	}
	
	/**
	 * Assign materials to skill levels
	 *
	 * @param
	 * @return
	 */
	function assignMaterials()
	{
		global $ilTabs, $lng, $ilCtrl, $tpl, $ilToolbar;
		
		$ilTabs->setBackTarget($lng->txt("back"),
			$ilCtrl->getLinkTarget($this, "listSkills"));
		
		$ilCtrl->saveParameter($this, "skill_id");
		$ilCtrl->saveParameter($this, "basic_skill_id");
		
		include_once("./Services/Skill/classes/class.ilSkillTreeNode.php");
		$tpl->setTitle(ilSkillTreeNode::_lookupTitle((int) $_GET["skill_id"]));
		$tpl->setTitleIcon(ilUtil::getImagePath("icon_".
			ilSkillTreeNode::_lookupType((int) $_GET["skill_id"]).
			"_b.gif"));
		 
		// basic skill selection
// here basic skill id??
		$bs = ilSkillTreeNode::getSkillTreeNodes((int) $_GET["skill_id"], true);
		$options = array();
		foreach ($bs as $b)
		{
			$options[$b["id"]] = ilSkillTreeNode::_lookupTitle($b["id"]);
		}
		
		$cur_basic_skill_id = ((int) $_POST["basic_skill_id"] > 0)
			? (int) $_POST["basic_skill_id"]
			: (((int) $_GET["basic_skill_id"] > 0)
				? (int) $_GET["basic_skill_id"]
				: key($options));

		$ilCtrl->setParameter($this, "basic_skill_id", $cur_basic_skill_id);
			
		include_once("./Services/Form/classes/class.ilSelectInputGUI.php");
		$si = new ilSelectInputGUI($lng->txt("skmg_skill"), "basic_skill_id");
		$si->setOptions($options);
		$si->setValue($cur_basic_skill_id);
		$ilToolbar->addInputItem($si, true);
		$ilToolbar->addFormButton($lng->txt("select"),
			"assignMaterials");
		
		$ilToolbar->setFormAction($ilCtrl->getFormAction($this));
		
		// table
		include_once("./Services/Skill/classes/class.ilSkillAssignMaterialsTableGUI.php");
		$tab = new ilSkillAssignMaterialsTableGUI($this, "assignMaterials",
			(int) $_GET["skill_id"], $cur_basic_skill_id);
		
		$tpl->setContent($tab->getHTML());
		
	}
	
	
	/**
	 * Assign materials to skill level
	 *
	 * @param
	 * @return
	 */
	function assignMaterial()
	{
		global $tpl, $ilUser, $ilCtrl, $ilTabs, $lng;
		
		$ilCtrl->saveParameter($this, "skill_id");
		$ilCtrl->saveParameter($this, "level_id");
		$ilCtrl->saveParameter($this, "basic_skill_id");
		
		$ilTabs->setBackTarget($lng->txt("back"),
			$ilCtrl->getLinkTarget($this, "assignMaterials"));
		
		// get ws tree
		include_once "Services/PersonalWorkspace/classes/class.ilWorkspaceTree.php";
		$tree = new ilWorkspaceTree($ilUser->getId());
		
		// get access handler
		include_once("./Services/PersonalWorkspace/classes/class.ilWorkspaceAccessHandler.php");
		$acc_handler = new ilWorkspaceAccessHandler($tree);
		
		// get es explorer
		include_once("./Services/PersonalWorkspace/classes/class.ilWorkspaceExplorer.php");
		$exp = new ilWorkspaceExplorer(ilWorkspaceExplorer::SEL_TYPE_CHECK, '', 
			'skill_wspexpand', $tree, $acc_handler);
		$exp->setTargetGet('wsp_id');
		$exp->setFiltered(false);
		$exp->removeAllFormItemTypes();
		$exp->addFormItemForType("file");
		$exp->addFormItemForType("tstv");
		$exp->addFormItemForType("excv");

		if($_GET['skill_wspexpand'] == '')
		{
			// not really used as session is already set [see above]
			$expanded = $tree->readRootId();
		}
		else
		{
			$expanded = $_GET['skill_wspexpand'];
		}
		$exp->setCheckedItems(array((int)$_POST['wsp_id']));
		$exp->setExpandTarget($ilCtrl->getLinkTarget($this, 'assignMaterial'));
		$exp->setPostVar('wsp_id[]');
		$exp->setExpand($expanded);
		$exp->setOutput(0);
		
		// fill template
		$mtpl = new ilTemplate("tpl.materials_selection.html", true, true, "Services/Skill");
		$mtpl->setVariable("EXP", $exp->getOutput());
		
		// toolbars
		$tb = new ilToolbarGUI();
		$tb->addFormButton($lng->txt("assign"),
			"selectMaterial");
		$tb->setFormAction($ilCtrl->getFormAction($this));
		$tb->setOpenFormTag(true);
		$tb->setCloseFormTag(false);
		$mtpl->setVariable("TOOLBAR1", $tb->getHTML());
		$tb->setOpenFormTag(false);
		$tb->setCloseFormTag(true);
		$mtpl->setVariable("TOOLBAR2", $tb->getHTML());
		
		$tpl->setContent($mtpl->get());
	}
	
	/**
	 * Select material
	 */
	function selectMaterial()
	{
		global $ilUser, $ilCtrl, $lng;
		
		include_once("./Services/Skill/classes/class.ilPersonalSkill.php");
		if (is_array($_POST["wsp_id"]))
		{
			foreach ($_POST["wsp_id"] as $w)
			{
				ilPersonalSkill::assignMaterial($ilUser->getId(), (int) $_GET["skill_id"],
					(int) $_GET["basic_skill_id"], (int) $_GET["level_id"], (int) $w);
			}
			ilUtil::sendSuccess($lng->txt("msg_obj_modified"), true);
		}
		
		$ilCtrl->saveParameter($this, "skill_id");
		$ilCtrl->saveParameter($this, "level_id");
		$ilCtrl->saveParameter($this, "basic_skill_id");
		
		$ilCtrl->redirect($this, "assignMaterials");
	}
	
	
	/**
	 * Remove material
	 */
	function removeMaterial()
	{
		global $ilUser, $lng, $ilCtrl;
		
		ilPersonalSkill::removeMaterial($ilUser->getId(), (int) $_GET["level_id"],
			(int) $_GET["wsp_id"]);
		ilUtil::sendSuccess($lng->txt("msg_obj_modified"), true);
		$ilCtrl->redirect($this, "assignMaterials");
	}
	
}
?>