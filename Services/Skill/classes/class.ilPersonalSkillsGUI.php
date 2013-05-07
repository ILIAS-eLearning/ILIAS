<?php

/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Skill/classes/class.ilPersonalSkill.php");
include_once("./Services/Skill/classes/class.ilSkillProfile.php");

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
	protected $offline_mode;
	protected $skill_tree;
	static $skill_tt_cnt = 1;
	protected $actual_levels = array();
	protected $gap_self_eval_levels = array();
	protected $mode = "";
	
	/**
	 * Contructor
	 *
	 * @access public
	 */
	public function __construct()
	{
		global $ilCtrl, $lng, $ilHelp, $ilSetting;

		$lng->loadLanguageModule('skmg');
		
		$ilHelp->setScreenIdComponent("skill");
		
		$ilCtrl->saveParameter($this, "skill_id");
		$ilCtrl->saveParameter($this, "tref_id");
		$ilCtrl->saveParameter($this, "profile_id");

		include_once("./Services/Skill/classes/class.ilSkillTree.php");
		$this->skill_tree = new ilSkillTree();
		
		$this->use_materials = !$ilSetting->get("disable_personal_workspace");
	}
	
	/**
	 * Set profile id
	 *
	 * @param  $a_val 	
	 */
	function setProfileId($a_val)
	{
		$this->profile_id = $a_val;
	}
	
	/**
	 * Get profile id
	 *
	 * @return  
	 */
	function getProfileId()
	{
		return $this->profile_id;
	}
	
	/**
	 * Set self evaluation levels for gap analysis
	 *
	 * @param array $a_val self evaluation values key1: base_skill_id, key2: tref_id: value: level id	
	 */
	function setGapAnalysisSelfEvalLevels($a_val)
	{
		$this->gap_self_eval_levels = $a_val;
	}
	
	/**
	 * Get self evaluation levels for gap analysis
	 *
	 * @return array self evaluation values key1: base_skill_id, key2: tref_id: value: level id
	 */
	function getGapAnalysisSelfEvalLevels()
	{
		return $this->gap_self_eval_levels;
	}
	
	/**
	 * Execute command
	 *
	 * @access public
	 *
	 */
	public function executeCommand()
	{
		global $ilCtrl, $tpl, $lng, $ilUser;

		$next_class = $ilCtrl->getNextClass($this);
		
		$profiles = ilSkillProfile::getProfilesOfUser($ilUser->getId());
		
		// determin standard command
		$std_cmd = "listSkills";
		if (count($profiles) > 0)
		{
//			$std_cmd = "listProfiles";
		}
		
		$cmd = $ilCtrl->getCmd($std_cmd);
		
		$tpl->setTitle($lng->txt("skills"));
		$tpl->setTitleIcon(ilUtil::getImagePath("icon_skmg_b.png"));

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
	
	function setOfflineMode($a_file_path)
	{
		$this->offline_mode = $a_file_path;
	}

	/**
	 * List skills
	 */
	function listSkills()
	{
		global $tpl, $ilTabs, $lng, $ilCtrl, $ilToolbar, $ilUser;

		$this->setTabs("list_skills");
		
		include_once("./Services/Skill/classes/class.ilSkillTree.php");
		$stree = new ilSkillTree();
		
		// skill selection / add new personal skill
		$ilToolbar->addFormButton($lng->txt("skmg_add_skill"),
			"listSkillsForAdd");
		$ilToolbar->setFormAction($ilCtrl->getFormAction($this));
		
		$skills = ilPersonalSkill::getSelectedUserSkills($ilUser->getId());
		$html = "";
		foreach ($skills as $s)
		{
			$path = $stree->getSkillTreePath($s["skill_node_id"]);

			// check draft
			foreach ($path as $p)
			{
				if ($p["draft"])
				{
					continue(2);
				}
			}


			$html.= $this->getSkillHTML($s["skill_node_id"], 0, true);
		}
		
		// list skills
//		include_once("./Services/Skill/classes/class.ilPersonalSkillTableGUI.php");
//		$sktab = new ilPersonalSkillTableGUI($this, "listSkills");
		
		$tpl->setContent($html);

	}

	/**
	 * Get skill presentation HTML
	 *
	 * @return
	 */
	function getSkillHTML($a_top_skill_id, $a_user_id = 0, $a_edit = false)
	{
		global $ilUser, $lng, $ilCtrl, $ilSetting;
		
		$this->tooltips = array();

		if ($a_user_id == 0)
		{
			$user = $ilUser;
		}
		else
		{
			$user = new ilObjUser($a_user_id);
		}

		$tpl = new ilTemplate("tpl.skill_pres.html", true, true, "Services/Skill");
		
		include_once("./Services/UIComponent/Tooltip/classes/class.ilTooltipGUI.php");
		
		include_once("./Services/Skill/classes/class.ilSkillTree.php");
		$stree = new ilSkillTree();
		
		include_once("./Services/Skill/classes/class.ilSkillTreeNode.php");
		include_once("./Services/Skill/classes/class.ilSkillTreeNodeFactory.php");
		
		// general settings for the action drop down
		include_once("Services/UIComponent/AdvancedSelectionList/classes/class.ilAdvancedSelectionListGUI.php");
		$act_list = new ilAdvancedSelectionListGUI();
		$act_list->setListTitle($lng->txt("actions"));
		$act_list->setSelectionHeaderClass("small");
//		$act_list->setLinksMode("il_ContainerItemCommand2");
		$act_list->setHeaderIcon(ilAdvancedSelectionListGUI::DOWN_ARROW_DARK);
		$act_list->setUseImages(false);
		
		$b_skills = ilSkillTreeNode::getSkillTreeNodes($a_top_skill_id, true);
		foreach ($b_skills as $bs)
		{
			$path = $stree->getSkillTreePath($bs["id"], $bs["tref"]);

			// check draft
			foreach ($path as $p)
			{
				if ($p["draft"])
				{
					continue(2);
				}
			}
			reset($path);
		
			$skill = ilSkillTreeNodeFactory::getInstance($bs["id"]);
			$level_data = $skill->getLevelData();

			if ($this->mode == "gap")
			{
				if ($this->getProfileId() > 0)
				{
					$this->renderProfileTargetRow($tpl, $level_data, $a_top_skill_id, $bs["id"], $bs["tref"]);
				}
				$this->renderActualLevelsRow($tpl, $level_data, $a_top_skill_id, $bs["id"], $bs["tref"]);
				$this->renderGapSelfEvalRow($tpl, $level_data, $a_top_skill_id, $bs["id"], $bs["tref"]);
				$this->renderSuggestedResources($tpl, $level_data, $bs["id"], $bs["tref"]);
			}
			else
			{
				$this->renderSelfEvaluationRow($tpl, $level_data, $a_top_skill_id, $bs["id"], $bs["tref"]);
				$this->renderMaterialsRow($tpl, $level_data, $a_top_skill_id, $bs["id"], $bs["tref"]);
			}
			
			$too_low = true;
			$current_target_level = 0;

			foreach ($level_data as $k => $v)
			{
		// level
				$tpl->setCurrentBlock("level_td");
				$tpl->setVariable("VAL_LEVEL", $v["title"]);
				$tt_id = "skmg_skl_tt_".self::$skill_tt_cnt;
				self::$skill_tt_cnt++;
				$tpl->setVariable("TT_ID", $tt_id);
				if ($v["description"] != "")
				{
					ilTooltipGUI::addTooltip($tt_id, $v["description"]);
				}
				$tpl->parseCurrentBlock();

				
		// profile targel level
/*
				foreach ($this->profile_levels as $pl)
				{
					if ($pl["level_id"] == $v["id"] &&
						$pl["base_skill_id"] == $v["skill_id"])
					{
						$too_low = true;
						$current_target_level = $v["id"];
					}
				}
				else
				{
					$tpl->setVariable("VAL_SELF_EVAL", " ");
				}
				$tpl->parseCurrentBlock();
				if ($v["id"] == $se_level)
				{
					$found = true;
				}

				// assigned materials
				if ($this->use_materials)
				{

					$mat_cnt = ilPersonalSkill::countAssignedMaterial($user->getId(),
						$bs["tref"], $v["id"]);
					if ($mat_cnt == 0)
					{
						$tpl->setCurrentBlock("material_td");
						$tpl->setVariable("VAL_MATERIAL", " ");
						$tpl->parseCurrentBlock();
					}
					else
					{					
						// links to material files
						$tpl->setCurrentBlock("material_links");
											
						$mat_tt = array();
						$cnt = 1;
						foreach(ilPersonalSkill::getAssignedMaterial($user->getId(),
							$bs["tref"], $v["id"]) as $item)
						{												
							$mat_data = $this->getMaterialInfo($item["wsp_id"]);
							$tpl->setVariable("URL_MATERIAL", $mat_data[1]);
							$tpl->setVariable("TXT_MATERIAL", $cnt);
							
							// tooltip
							$mat_tt_id = "skmg_skl_tt_mat_".self::$skill_tt_cnt;
							self::$skill_tt_cnt++;
							$tpl->setVariable("TOOLTIP_MATERIAL_ID", $mat_tt_id);
							
							if(!$this->offline_mode)
							{
								ilTooltipGUI::addTooltip($mat_tt_id, $mat_data[0]);
							}
							else
							{							
								$this->tooltips[] = ilTooltipGUI::getTooltip($mat_tt_id, $mat_data[0]);
							}
							
							$tpl->parseCurrentBlock();
							$cnt++;
						}																	
						
						$tpl->setCurrentBlock("material_td");
						$tpl->setVariable("CLASS_MAT", "ilSkillMat");
						$tpl->parseCurrentBlock();
					}
				}
*/
			}
			
			
			$title = $sep = "";
			$found = false;
			foreach ($path as $p)
			{
				if ($found)
				{
					$title.= $sep.$p["title"];
					$sep = " > ";
				}
				if ($a_top_skill_id == $p["child"])
				{
					$found = true;
				}
			}
			
			$tpl->setCurrentBlock("skill");
			$tpl->setVariable("BSKILL_TITLE", $title);
/*			$tpl->setVariable("TXT_LEVEL", $lng->txt("skmg_level"));
			$tpl->setVariable("TXT_SELF_EVAL", $lng->txt("skmg_self_evaluation"));
			if ($this->use_materials)
			{
				$tpl->setVariable("TXT_MATERIAL", $lng->txt("skmg_material"));
			}*/
			$tpl->setVariable("TXT_TARGET", $lng->txt("skmg_target_level"));
			$tpl->setVariable("TXT_360_SURVEY", $lng->txt("skmg_360_survey"));
			
			if ($a_edit)
			{
				$act_list->flush();
				$act_list->setId("act_".$a_top_skill_id."_".$bs["id"]);
				$ilCtrl->setParameterByClass("ilpersonalskillsgui", "skill_id", $a_top_skill_id);
				$ilCtrl->setParameterByClass("ilpersonalskillsgui", "tref_id", $bs["tref"]);
				$ilCtrl->setParameterByClass("ilpersonalskillsgui", "basic_skill_id", $bs["id"]);
				if ($this->use_materials)
				{
					$act_list->addItem($lng->txt('skmg_assign_materials'), "",
						$ilCtrl->getLinkTargetByClass("ilpersonalskillsgui", "assignMaterials"));
				}
				$act_list->addItem($lng->txt('skmg_self_evaluation'), "",
					$ilCtrl->getLinkTargetByClass("ilpersonalskillsgui", "selfEvaluation"));
				$tpl->setVariable("ACTIONS2", $act_list->getHTML());
			}
			
			$tpl->parseCurrentBlock();
			
		}
		
		$tpl->setVariable("SKILL_TITLE", ilSkillTreeNode::_lookupTitle($a_top_skill_id));
		
		if ($a_edit)
		{
			$act_list->flush();
			$act_list->setId("act_".$a_top_skill_id);
			$ilCtrl->setParameterByClass("ilpersonalskillsgui", "skill_id", $a_top_skill_id);
//			$act_list->addItem($lng->txt('skmg_assign_materials'), "",
//				$ilCtrl->getLinkTargetByClass("ilpersonalskillsgui", "assignMaterials"));
			$act_list->addItem($lng->txt('skmg_remove_skill'), "",
				$ilCtrl->getLinkTargetByClass("ilpersonalskillsgui", "confirmSkillRemove"));
			$tpl->setVariable("ACTIONS1", $act_list->getHTML());
		}
		

		
		return $tpl->get();
	}
	
	function getTooltipsJs()
	{
		return $this->tooltips;
	}
	
	/**
	 * Get material file name and goto url
	 * 
	 * @param int $a_wsp_id
	 * @return array caption, url 
	 */
	function getMaterialInfo($a_wsp_id)
	{
		global $ilUser;
		
		if(!$this->ws_tree)
		{
			include_once "Services/PersonalWorkspace/classes/class.ilWorkspaceTree.php";
			include_once "Services/PersonalWorkspace/classes/class.ilWorkspaceAccessHandler.php";
			$this->ws_tree = new ilWorkspaceTree($ilUser->getId());
			$this->ws_access = new ilWorkspaceAccessHandler($caption);
		}
		
		$obj_id = $this->ws_tree->lookupObjectId($a_wsp_id);
		$caption = ilObject::_lookupTitle($obj_id);
		
		if(!$this->offline_mode)
		{
			$url = $this->ws_access->getGotoLink($a_wsp_id, $obj_id);
		}
		else
		{	
			$url = $this->offline_mode."file_".$obj_id."/";
						
			// all possible material types for now
			switch(ilObject::_lookupType($obj_id))
			{
				case "tstv":
					include_once "Modules/Test/classes/class.ilObjTestVerification.php";
					$obj = new ilObjTestVerification($obj_id, false);
					$url .= $obj->getOfflineFilename();
					break;
					
				case "excv":
					include_once "Modules/Exercise/classes/class.ilObjExerciseVerification.php";
					$obj = new ilObjExerciseVerification($obj_id, false);
					$url .= $obj->getOfflineFilename();
					break;
				
				case "file":
					$file = new ilObjFile($obj_id, false);
					$url .= $file->getFilename();
					break;
			}						
		}
		
		return array($caption, $url);
	}
	
	/**
	 * Add personal skill
	 */
	function addSkill()
	{
		global $ilUser, $ilCtrl, $lng;
		
		ilPersonalSkill::addPersonalSkill($ilUser->getId(), (int) $_GET["obj_id"]);
		
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
		if ($_GET["skill_id"] > 0)
		{
			$_POST["id"][] = $_GET["skill_id"];
		}
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
	
	
	//
	// Materials assignments
	//
	
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
		$ilCtrl->saveParameter($this, "tref_id");
		
		include_once("./Services/Skill/classes/class.ilSkillTreeNode.php");
		$tpl->setTitle(ilSkillTreeNode::_lookupTitle((int) $_GET["skill_id"]));
		$tpl->setTitleIcon(ilUtil::getImagePath("icon_".
			ilSkillTreeNode::_lookupType((int) $_GET["skill_id"]).
			"_b.png"));
		 
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
			(int) $_GET["skill_id"], (int) $_GET["tref_id"], $cur_basic_skill_id);
		
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
		global $tpl, $ilUser, $ilCtrl, $ilTabs, $lng, $ilSetting;
		
		if(!$ilSetting->get("disable_personal_workspace"))
		{
			ilUtil::sendInfo($lng->txt("skmg_ass_materials_from_workspace")." » <a href='ilias.php?baseClass=ilPersonalDesktopGUI&amp;cmd=jumpToWorkspace'>".$lng->txt("personal_workspace")."</a>");
		}
		
		$ilCtrl->saveParameter($this, "skill_id");
		$ilCtrl->saveParameter($this, "level_id");
		$ilCtrl->saveParameter($this, "tref_id");
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
		$tb->addFormButton($lng->txt("select"),
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
					(int) $_GET["tref_id"],
					(int) $_GET["basic_skill_id"], (int) $_GET["level_id"], (int) $w);
			}
			ilUtil::sendSuccess($lng->txt("msg_obj_modified"), true);
		}
		
		$ilCtrl->saveParameter($this, "skill_id");
		$ilCtrl->saveParameter($this, "level_id");
		$ilCtrl->saveParameter($this, "tref_id");
		$ilCtrl->saveParameter($this, "basic_skill_id");
		
		$ilCtrl->redirect($this, "assignMaterials");
	}
	
	
	/**
	 * Remove material
	 */
	function removeMaterial()
	{
		global $ilUser, $lng, $ilCtrl;
		
		ilPersonalSkill::removeMaterial($ilUser->getId(), (int) $_GET["tref_id"],
			(int) $_GET["level_id"],
			(int) $_GET["wsp_id"]);
		ilUtil::sendSuccess($lng->txt("msg_obj_modified"), true);
		$ilCtrl->redirect($this, "assignMaterials");
	}
	
	
	//
	// Self evaluation
	//
	
	/**
	 * Assign materials to skill levels
	 *
	 * @param
	 * @return
	 */
	function selfEvaluation()
	{
		global $ilTabs, $lng, $ilCtrl, $tpl, $ilToolbar;
		
		$ilTabs->setBackTarget($lng->txt("back"),
			$ilCtrl->getLinkTarget($this, "listSkills"));
		
		$ilCtrl->saveParameter($this, "skill_id");
		$ilCtrl->saveParameter($this, "basic_skill_id");
		$ilCtrl->saveParameter($this, "tref_id");
		
		include_once("./Services/Skill/classes/class.ilSkillTreeNode.php");
		$tpl->setTitle(ilSkillTreeNode::_lookupTitle((int) $_GET["skill_id"]));
		$tpl->setTitleIcon(ilUtil::getImagePath("icon_".
			ilSkillTreeNode::_lookupType((int) $_GET["skill_id"]).
			"_b.png"));
		 
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
			"selfEvaluation");
		
		$ilToolbar->setFormAction($ilCtrl->getFormAction($this));
		
		// table
		include_once("./Services/Skill/classes/class.ilSelfEvaluationSimpleTableGUI.php");
		$tab = new ilSelfEvaluationSimpleTableGUI($this, "selfEvaluation",
			(int) $_GET["skill_id"], (int) $_GET["tref_id"], $cur_basic_skill_id);
		
		$tpl->setContent($tab->getHTML());
		
	}

	/**
	 * Save self evaluation
	 */
	function saveSelfEvaluation()
	{
		global $ilUser, $lng, $ilCtrl;
		
		ilPersonalSkill::saveSelfEvaluation($ilUser->getId(), (int) $_GET["skill_id"],
			(int) $_GET["tref_id"], (int) $_GET["basic_skill_id"], (int) $_POST["se"]);
		ilUtil::sendSuccess($lng->txt("msg_obj_modified"), true);
		
/*		$ilCtrl->saveParameter($this, "skill_id");
		$ilCtrl->saveParameter($this, "level_id");
		$ilCtrl->saveParameter($this, "tref_id");
		$ilCtrl->saveParameter($this, "basic_skill_id");*/
		
		$ilCtrl->redirect($this, "listSkills");

	}
	
	/**
	 * LIst skills for adding
	 *
	 * @param
	 * @return
	 */
	function listSkillsForAdd()
	{
		global $ilUser, $tpl, $ilCtrl, $lng, $ilTabs;

		ilUtil::sendInfo($lng->txt("skmg_select_skill"));
		
		$ilTabs->setBackTarget($lng->txt("back"),
			$ilCtrl->getLinkTarget($this, ""));
		
		include_once("./Services/Skill/classes/class.ilSkillTree.php");
		$skill_tree = new ilSkillTree();
		
		require_once ("./Services/Skill/classes/class.ilPersonalSkillExplorer.php");
		$exp = new ilPersonalSkillExplorer($ilCtrl->getLinkTarget($this, "listSkillsForAdd"));
		$exp->setTargetGet("obj_id");
		
		$exp->setExpandTarget($ilCtrl->getLinkTarget($this, "listSkillsForAdd"));
		
		if ($_GET["skpexpand"] == "")
		{
			$expanded = $skill_tree->readRootId();
		}
		else
		{
			$expanded = $_GET["skpexpand"];
		}

		if ($_GET["obj_id"] > 0)
		{
			$path = $this->skill_tree->getPathId($_GET["obj_id"]);
			$exp->setForceOpenPath($path);
			$exp->highlightNode($_GET["obj_id"]);
		}
		else
		{
			$exp->highlightNode($this->skill_tree->readRootId());
		}
		$exp->setExpand($expanded);
		// build html-output
		$exp->setOutput(0);
		$output = $exp->getOutput();

		// asynchronous output
		if ($ilCtrl->isAsynch())
		{
			echo $output; exit;
		}

		$tpl->setContent($output);

	}
	
	/**
	 * List profiles
	 *
	 * @param
	 * @return
	 */
	function listProfiles()
	{
		global $ilCtrl, $ilToolbar, $ilUser, $lng, $tpl;
		
		$profiles = ilSkillProfile::getProfilesOfUser($ilUser->getId());
		
		if (count($profiles) == 0)
		{
			return;
		}
		
		// select profiles
		include_once("./Services/Form/classes/class.ilSelectInputGUI.php");
		$options = array();
		foreach ($profiles as $p)
		{
			$options[$p["id"]] = $p["title"];
		}
		
		if (!isset($options[$_GET["profile_id"]]))
		{
			$_GET["profile_id"] = (int) key($options);
			$ilCtrl->setParameter($this, "profile_id", $_GET["profile_id"]);
		}
		$current_profile_id = $_GET["profile_id"];
		
		$si = new ilSelectInputGUI($lng->txt("skmg_profile"), "");
		$si->setOptions($options);
		$si->setValue($current_profile_id);
		$ilToolbar->addInputItem($si, true);
		$ilToolbar->addFormButton($lng->txt("select"),
			"selectProfile");
		$ilToolbar->setFormAction($ilCtrl->getFormAction($this));
		
		$this->setProfileId($current_profile_id);
		
		$tpl->setContent($this->getGapAnalysisHTML());
	}

	/**
	 * Set gap analysis actual status mode "per type"
	 *
	 * @param string $a_type type
	 */
	function setGapAnalysisActualStatusModePerType($a_type, $a_cat_title = "")
	{
		$this->gap_mode = "max_per_type";
		$this->gap_mode_type = $a_type;
		$this->gap_cat_title = $a_cat_title;
		$this->mode = "gap";
	}

	/**
	 * Set gap analysis actual status mode "per object"
	 *
	 * @param integer $a_obj_id object id
	 */
	function setGapAnalysisActualStatusModePerObject($a_obj_id, $a_cat_title = "")
	{
		$this->gap_mode = "max_per_object";
		$this->gap_mode_obj_id = $a_obj_id;
		$this->gap_cat_title = $a_cat_title;
		$this->mode = "gap";
	}

	/**
	 * Get gap analysis html
	 *
	 * @param
	 * @return
	 */
	function getGapAnalysisHTML($a_user_id = 0, $a_skills = null)
	{
		global $ilUser, $lng;
		
//		$this->setTabs("list_skills");
		
		if ($a_user_id == 0)
		{
			$user_id = $ilUser->getId();
		}
		else
		{
			$user_id = $a_user_id;
		}

		$skills = array();
		if ($this->getProfileId() > 0)
		{
			$profile = new ilSkillProfile($this->getProfileId());
			$this->profile_levels = $profile->getSkillLevels();

			foreach ($this->profile_levels as $l)
			{
				$skills[] = array(
					"base_skill_id" => $l["base_skill_id"],
					"tref_id" => $l["tref_id"],
					"level_id" => $l["level_id"]
					);
			}
		}
		else if (is_array($a_skills))
		{
			$skills = $a_skills;
		}

		// get actual levels for gap analysis
		$this->actual_levels = array();
		include_once("./Services/Skill/classes/class.ilBasicSkill.php");
		foreach ($skills as $sk)
		{
			$bs = new ilBasicSkill($sk["base_skill_id"]);
			if ($this->gap_mode == "max_per_type")
			{
				$max = $bs->getMaxLevelPerType($sk["tref_id"], $this->gap_mode_type, $user_id);
				$this->actual_levels[$sk["base_skill_id"]][$sk["tref_id"]] = $max;
			}
			else if ($this->gap_mode == "max_per_object")
			{
				$max = $bs->getMaxLevelPerObject($sk["tref_id"], $this->gap_mode_obj_id, $user_id);
				$this->actual_levels[$sk["base_skill_id"]][$sk["tref_id"]] = $max;
			}
		}

		$incl_self_eval = false;
		if (count($this->getGapAnalysisSelfEvalLevels() > 0))
		{
			$incl_self_eval = true;
			$self_vals = $this->getGapAnalysisSelfEvalLevels();
		}

		if (count($skills) >= 3)
		{
			$max_cnt = 0;
			$leg_labels = array();
//var_dump($this->profile_levels);
			//foreach ($this->profile_levels as $k => $l)
			foreach ($skills as $k => $l)
			{
				//$bs = new ilBasicSkill($l["base_skill_id"]);
				$bs = new ilBasicSkill($l["base_skill_id"]);
				$leg_labels[] = $bs->getTitle();
				$levels = $bs->getLevelData();
				$cnt = 0;
				foreach ($levels as $lv)
				{
					$cnt++;
					if ($l["level_id"] == $lv["id"])
					{
						$skills[$k]["target_cnt"] = $cnt;
					}
					if ($this->actual_levels[$l["base_skill_id"]][$l["tref_id"]] == $lv["id"])
					{
						$skills[$k]["actual_cnt"] = $cnt;
					}
					if ($incl_self_eval)
					{
						if ($self_vals[$l["base_skill_id"]][$l["tref_id"]] == $lv["id"])
						{
							$skills[$k]["self_cnt"] = $cnt;
						}
					}
					$max_cnt = max($max_cnt, $cnt);
				}
			}
			
//			$leg_labels = array("AAAAA", "BBBBB", "CCCCC");
			
//var_dump($this->profile_levels);
//var_dump($this->actual_levels);

			include_once("./Services/Chart/classes/class.ilChart.php");
			$chart = new ilChart("gap_chart", 600, 300);
			$chart->setYAxisMax($max_cnt);
			$chart->setLegLabels($leg_labels);
			
			// target level
			$cd = new ilChartData("spider");
			$cd->setLabel($lng->txt("skmg_target_level"));
			$cd->setFill("true", "#A0A0A0");

			// other users
			$cd2 = new ilChartData("spider");
			if ($this->gap_cat_title != "")
			{
				$cd2->setLabel($this->gap_cat_title);
			}
			else if ($this->gap_mode == "max_per_type")
			{
				$cd2->setLabel($lng->txt("objs_".$this->gap_mode_type));
			}
			else if ($this->gap_mode == "max_per_object")
			{
				$cd2->setLabel(ilObject::_lookupTitle($this->gap_mode_obj_id));
			}
			$cd2->setFill("true", "#8080FF");
			
			// self evaluation
			if ($incl_self_eval)
			{
				$cd3 = new ilChartData("spider");
				$cd3->setLabel($lng->txt("skmg_self_evaluation"));
				$cd3->setFill("true", "#FF8080");
			}
			
			// fill in data
			$cnt = 0;
			foreach ($skills as $pl)
			{
				$cd->addPoint($cnt, (int) $pl["target_cnt"]);
				$cd2->addPoint($cnt, (int) $pl["actual_cnt"]);
				if ($incl_self_eval)
				{
					$cd3->addPoint($cnt, (int) $pl["self_cnt"]);
				}
				$cnt++;
			}
			
			// add data to chart
			if ($this->getProfileId() > 0)
			{
				$chart->addData($cd);
			}
			$chart->addData($cd2);
			if ($incl_self_eval && count($this->getGapAnalysisSelfEvalLevels()) > 0)
			{
				$chart->addData($cd3);
			}
			
			$lg = new ilChartLegend();
			$chart->setLegend($lg);
			
			$chart_html = $chart->getHTML();
		}

		$stree = new ilSkillTree();
		$html = "";
		foreach ($skills as $s)
		{
			$path = $stree->getSkillTreePath($s["base_skill_id"]);

			// check draft
			foreach ($path as $p)
			{
				if ($p["draft"])
				{
					continue(2);
				}
			}
			
			$html.= $this->getSkillHTML($s["base_skill_id"], $user_id, false);
		}

		// list skills
//		include_once("./Services/Skill/classes/class.ilPersonalSkillTableGUI.php");
//		$sktab = new ilPersonalSkillTableGUI($this, "listSkills");
		
		return $chart_html.$html;
	}
	
	/**
	 * Select profile
	 *
	 * @param
	 * @return
	 */
	function selectProfile()
	{
		global $ilCtrl;
		
		$ilCtrl->setParameter($this, "profile_id", $_GET["profile_id"]);
		$ilCtrl->redirect($this, "listProfiles");
	}
	
	/**
	 * Render self evaluation row
	 *
	 * @param
	 * @return
	 */
	function renderSelfEvaluationRow($a_tpl, $a_levels, $a_top_skill_id, $a_base_skill, $a_tref_id = 0, $a_user_id = 0)
	{
		global $ilUser, $lng;
		
		if ($a_user_id == 0)
		{
			$a_user_id = $ilUser->getId();
		}
		
		$se_level = ilPersonalSkill::getSelfEvaluation($a_user_id,
			$a_top_skill_id, $a_tref_id, $a_base_skill);

		// check, if current self eval level is in current level data
		$valid_sel_level = false;
		if ($se_level > 0)
		{
			foreach ($a_levels as $k => $v)
			{
				if ($v["id"] == $se_level)
				{
					$valid_sel_level = true;
				}
			}
		}
		reset($a_levels);
		$found = false;
		foreach ($a_levels as $k => $v)
		{
			$a_tpl->setCurrentBlock("val_level_td");
			if ($valid_sel_level && !$found)
			{
				$a_tpl->setVariable("VAL_LEVEL", "x");
				$a_tpl->setVariable("TD_CLASS", "ilSkillSelf");
			}
			else
			{
				$a_tpl->setVariable("VAL_LEVEL", " ");
			}
			$a_tpl->parseCurrentBlock();
			if ($v["id"] == $se_level)
			{
				$found = true;
			}
		}
		
		$a_tpl->setCurrentBlock("value_row");
		$a_tpl->setVariable("TXT_VAL_TITLE", $lng->txt("skmg_self_evaluation"));
		$a_tpl->parseCurrentBlock();
	}
	
	/**
	 * Render materials row
	 *
	 * @param
	 * @return
	 */
	function renderMaterialsRow($a_tpl, $a_levels, $a_top_skill_id, $a_base_skill, $a_tref_id = 0, $a_user_id = 0)
	{
		global $ilUser, $lng;
		
		if ($a_user_id == 0)
		{
			$a_user_id = $ilUser->getId();
		}
		
		foreach ($a_levels as $k => $v)
		{
			$mat_cnt = ilPersonalSkill::countAssignedMaterial($a_user_id,
				$a_tref_id, $v["id"]);
			if ($mat_cnt == 0)
			{
				$a_tpl->setCurrentBlock("val_level_td");
				$a_tpl->setVariable("VAL_LEVEL", " ");
				$a_tpl->parseCurrentBlock();
			}
			else
			{					
				// links to material files
				$a_tpl->setCurrentBlock("level_link");
									
				$mat_tt = array();
				$cnt = 1;
				foreach(ilPersonalSkill::getAssignedMaterial($a_user_id,
					$a_tref_id, $v["id"]) as $item)
				{												
					$mat_data = $this->getMaterialInfo($item["wsp_id"]);
					$a_tpl->setVariable("HREF_LINK", $mat_data[1]);
					$a_tpl->setVariable("TXT_LINK", $cnt);
					
					// tooltip
					$mat_tt_id = "skmg_skl_tt_mat_".self::$skill_tt_cnt;
					self::$skill_tt_cnt++;
					$a_tpl->setVariable("LEVEL_LINK_ID", $mat_tt_id);
					
					if(!$this->offline_mode)
					{
						ilTooltipGUI::addTooltip($mat_tt_id, $mat_data[0]);
					}
					else
					{							
						$this->tooltips[] = ilTooltipGUI::getTooltip($mat_tt_id, $mat_data[0]);
					}
					
					$a_tpl->parseCurrentBlock();
					$cnt++;
				}																	
				
				$a_tpl->setCurrentBlock("val_level_td");
				$a_tpl->setVariable("TD_CLASS", "ilSkillMat");
				$a_tpl->parseCurrentBlock();
			}
		}
		
		$a_tpl->setCurrentBlock("value_row");
		$a_tpl->setVariable("TXT_VAL_TITLE", $lng->txt("skmg_material"));
		$a_tpl->parseCurrentBlock();
	}

	/**
	 * Render profile target row
	 *
	 * @param
	 * @return
	 */
	function renderProfileTargetRow($a_tpl, $a_levels, $a_top_skill_id, $a_base_skill, $a_tref_id = 0, $a_user_id = 0)
	{
		global $ilUser, $lng;
		
		if ($a_user_id == 0)
		{
			$a_user_id = $ilUser->getId();
		}
		
		$profile = new ilSkillProfile($this->getProfileId());
		$profile_levels = $profile->getSkillLevels();

		foreach ($a_levels as $k => $v)
		{
			$a_tpl->setCurrentBlock("val_level_td");
			$target_level = false;
			foreach ($profile_levels as $pl)
			{
				if ($pl["level_id"] == $v["id"] &&
					$pl["base_skill_id"] == $v["skill_id"])
				{
					$target_level = true;
					$current_target_level = $v["id"];
				}
			}
			if ($target_level)
			{
				$too_low = true;
				$a_tpl->setVariable("VAL_LEVEL", "x");
				$a_tpl->setVariable("TD_CLASS", "ilSkillSelf");
			}
			else
			{
				$a_tpl->setVariable("VAL_LEVEL", " ");
			}
			$a_tpl->parseCurrentBlock();
		}
		
		$a_tpl->setCurrentBlock("value_row");
		$a_tpl->setVariable("TXT_VAL_TITLE", $lng->txt("skmg_target_level"));
		$a_tpl->parseCurrentBlock();
	}

	/**
	 * Render actual levels row (gap analysis)
	 *
	 * @param
	 * @return
	 */
	function renderActualLevelsRow($a_tpl, $a_levels, $a_top_skill_id, $a_base_skill, $a_tref_id = 0, $a_user_id = 0)
	{
		global $ilUser, $lng;

		if ($a_user_id == 0)
		{
			$a_user_id = $ilUser->getId();
		}

		$profile = new ilSkillProfile($this->getProfileId());
		$profile_levels = $profile->getSkillLevels();

		foreach ($a_levels as $k => $v)
		{
			$a_tpl->setCurrentBlock("val_level_td");
			$survey_level = false;
			if ($this->actual_levels[$v["skill_id"]][$a_tref_id] == $v["id"])
			{
				$survey_level = true;
				$too_low = false;
			}
			if ($survey_level)
			{
				$a_tpl->setVariable("VAL_LEVEL", "x");
				$a_tpl->setVariable("TD_CLASS", "ilSkillSelf");
			}
			else
			{
				$a_tpl->setVariable("VAL_LEVEL", " ");
			}
			$a_tpl->parseCurrentBlock();
		}
		
		$a_tpl->setCurrentBlock("value_row");
		if ($this->gap_cat_title != "")
		{
			$a_tpl->setVariable("TXT_VAL_TITLE", $this->gap_cat_title);
		}
		else if ($this->gap_mode == "max_per_type")
		{
			$a_tpl->setVariable("TXT_VAL_TITLE", $lng->txt("objs_".$this->gap_mode_type));
		}
		else if ($this->gap_mode == "max_per_object")
		{
			$a_tpl->setVariable("TXT_VAL_TITLE", ilObject::_lookupTitle($this->gap_mode_obj_id));
		}
		
		$a_tpl->parseCurrentBlock();
	}

	/**
	 * Render actual levels row (gap analysis)
	 *
	 * @param
	 * @return
	 */
	function renderGapSelfEvalRow($a_tpl, $a_levels, $a_top_skill_id, $a_base_skill, $a_tref_id = 0, $a_user_id = 0)
	{
		global $ilUser, $lng;

		if ($a_user_id == 0)
		{
			$a_user_id = $ilUser->getId();
		}

		$self_vals = $this->getGapAnalysisSelfEvalLevels();
		if (count($self_vals) == 0)
		{
			return;
		}
		
		foreach ($a_levels as $k => $v)
		{
			$a_tpl->setCurrentBlock("val_level_td");
			$survey_level = false;
			if ($self_vals[$v["skill_id"]][$a_tref_id] == $v["id"])
			{
				$survey_level = true;
			}
			if ($survey_level)
			{
				$a_tpl->setVariable("VAL_LEVEL", "x");
				$a_tpl->setVariable("TD_CLASS", "ilSkillSelf");
			}
			else
			{
				$a_tpl->setVariable("VAL_LEVEL", " ");
			}
			$a_tpl->parseCurrentBlock();
		}
		
		$a_tpl->setCurrentBlock("value_row");
		$a_tpl->setVariable("TXT_VAL_TITLE", $lng->txt("skmg_self_evaluation"));
		
		$a_tpl->parseCurrentBlock();
	}

	/**
	 * Render suggested resources
	 *
	 * @param
	 * @return
	 */
	function renderSuggestedResources($a_tpl, $a_levels, $a_base_skill, $a_tref_id)
	{
		global $lng;
		
		if ($this->getProfileId() == 0)
		{
			return;
		}
		
		$profile = new ilSkillProfile($this->getProfileId());
		$profile_levels = $profile->getSkillLevels();

		$too_low = true;
		$current_target_level = 0;

		foreach ($a_levels as $k => $v)
		{
			foreach ($this->profile_levels as $pl)
			{
				if ($pl["level_id"] == $v["id"] &&
					$pl["base_skill_id"] == $v["skill_id"])
				{
					$too_low = true;
					$current_target_level = $v["id"];
				}
			}
			
			if ($this->actual_levels[$v["skill_id"]][0] == $v["id"])
			{
				$too_low = false;
			}
		}
		
		// suggested resources
		if ($too_low)
		{
			include_once("./Services/Skill/classes/class.ilSkillResources.php");
			$skill_res = new ilSkillResources($a_base_skill, $a_tref_id);
			$res = $skill_res->getResources();
			$imp_resources = array();
			foreach ($res as $level)
			{
				foreach($level as $r)
				{
					if ($r["imparting"] == true &&
						$current_target_level == $r["level_id"])
					{
						$imp_resources[] = $r;
					}
				}
			}
			foreach($imp_resources as $r)
			{
				$ref_id = $r["rep_ref_id"];
				$obj_id = ilObject::_lookupObjId($ref_id);
				$title = ilObject::_lookupTitle($obj_id);
				include_once("./classes/class.ilLink.php");
				$a_tpl->setCurrentBlock("resource_item");
				$a_tpl->setVariable("TXT_RES", $title);
				$a_tpl->setVariable("HREF_RES", ilLink::_getLink($ref_id));
				$a_tpl->parseCurrentBlock();
			}
			if (count($imp_resources) > 0)
			{
				$a_tpl->touchBlock("resources_list");
				$a_tpl->setCurrentBlock("resources");
				$a_tpl->setVariable("SUGGESTED_MAT_MESS", $lng->txt("skmg_skill_needs_impr_res"));
				$a_tpl->parseCurrentBlock();
			}
			else
			{
				$a_tpl->setCurrentBlock("resources");
				$a_tpl->setVariable("SUGGESTED_MAT_MESS", $lng->txt("skmg_skill_needs_impr_no_res"));
				$a_tpl->parseCurrentBlock();
			}
		}
		else
		{
			$a_tpl->setCurrentBlock("resources");
			$a_tpl->setVariable("SUGGESTED_MAT_MESS", $lng->txt("skmg_skill_no_needs_impr"));
			$a_tpl->parseCurrentBlock();
		}
	}
	
}
?>