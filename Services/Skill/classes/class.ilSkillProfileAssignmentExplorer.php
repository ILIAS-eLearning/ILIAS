<?php

/* Copyright (c) 1998-2011 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Skill/classes/class.ilSkillTreeNode.php");

/**
 * Explorer for skill management
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 *
 * @ingroup ServicesSkill
 */
require_once("./classes/class.ilExplorer.php");

class ilSkillProfileAssignmentExplorer extends ilExplorer
{
	/**
	 * id of root folder
	 * @var int root folder id
	 * @access private
	 */
	var $root_id;
	var $slm_obj;
	var $output;

	/**
	* Constructor
	* @access	public
	* @param	string	scriptname
	* @param    int user_id
	*/
	function __construct($a_target, $a_templates = false)
	{
		$this->templates = $a_templates;
		
		parent::ilExplorer($a_target);
		
		$this->setFilterMode(IL_FM_POSITIVE);
		$this->addFilter("skrt");
		$this->addFilter("skll");
		$this->addFilter("scat");
		$this->addFilter("sktr");
		$this->addFilter("level");
		$this->setTitleLength(999);
		
		include_once("./Services/Skill/classes/class.ilSkillTree.php");
		$this->tree = new ilSkillTree();
		$this->root_id = $this->tree->readRootId();
		
		$this->setSessionExpandVariable("skaexpand");
		$this->checkPermissions(false);
		$this->setPostSort(false);
		
		$this->setOrderColumn("order_nr");
//		$this->textwidth = 200;

		$this->force_open_path = array();
	}

	/**
	* set force open path
	*/
	function setForceOpenPath($a_path)
	{
		$this->force_open_path = $a_path;
	}

	/**
	* overwritten method from base class
	* @access	public
	* @param	integer obj_id
	* @param	integer array options
	* @return	string
	*/
	function formatHeader(&$tpl, $a_obj_id,$a_option)
	{
		global $lng, $ilias, $ilCtrl;
	}

	/**
	* overwritten method from base class
	* @access	private
	* @param	integer
	* @param	array
	* @return	string
	*/
	function formatObject(&$tpl, $a_node_id,$a_option,$a_obj_id = 0)
	{
		global $lng;
		
		parent::formatObject($tpl, $a_node_id,$a_option,$a_obj_id);
	}
	
	/**
	* check if links for certain object type are activated
	*
	* @param	string		$a_type			object type
	*
	* @return	boolean		true if linking is activated
	*/
	function isClickable($a_type, $a_obj_id = 0)
	{
		global $ilUser;
		
		if ($a_type == "skll")
		{
			return true;
		}
		return false;
	}
	
	/**
	* build link target
	*/
	function buildLinkTarget($a_node_id, $a_type)
	{
		global $ilCtrl;

		$ilCtrl->setParameterByClass("ilskillprofilegui", "skill_id", $a_node_id);
		$ret = $ilCtrl->getLinkTargetByClass("ilskillprofilegui", "assignLevelSelectSkill");
		$ilCtrl->setParameterByClass("ilskillprofilegui", "skill_id", $_GET["skill_id"]);
		
		return $ret;
	}

	/**
	 * standard implementation for title, may be overwritten by derived classes
	 */
	function buildTitle($a_title, $a_id, $a_type)
	{
		global $lng;
		
		if ($a_type == "sktr")
		{
			include_once("./Services/Skill/classes/class.ilSkillTemplateReference.php");
			$tid = ilSkillTemplateReference::_lookupTemplateId($a_id);
//			$a_title.= " (".ilSkillTreeNode::_lookupTitle($tid).")";
		}
		
/*		if (ilSkillTreeNode::_lookupSelfEvaluation($a_id))
		{
			$a_title.= " [".$lng->txt("add")."]";
		}*/

		return $a_title;
	}

	/**
	* force expansion of node
	*/
	function forceExpanded($a_obj_id)
	{
		if (in_array($a_obj_id, $this->force_open_path))
		{
			return true;
		}
		return false;
	}

	/**
	 * Get frame target
	 */
	function buildFrameTarget($a_type, $a_child = 0, $a_obj_id = 0)
	{
		return "";
	}
	
	/**
	 * Get maximum tree depth
	 *
	 * @param
	 * @return
	 */
	function getMaximumTreeDepth()
	{
		$this->tree->getMaximumDepth();
	}

	/**
	 * get image path (may be overwritten by derived classes)
	 */
	function getImage($a_name, $a_type = "", $a_obj_id = "")
	{
		if (in_array($a_type, array("sktr")))
		{
			return ilUtil::getImagePath("icon_skll_s.gif");
		}
		return ilUtil::getImagePath($a_name);
	}

	/**
	 * Get childs of node
	 *
	 * @param int $a_parent_id parent id
	 * @return array childs
	 */
	function getChildsOfNode($a_parent_id)
	{
		switch (ilSkillTreeNode::_lookupType($a_parent_id))
		{
/*
      'id' => string '1' (length=1)
      'skill_id' => string '4' (length=1)
      'nr' => string '1' (length=1)
      'title' => string 'Ungenügend' (length=11)
      'description' => null
      'trigger_ref_id' => string '0' (length=1)
      'trigger_obj_id' => string '0' (length=1)
*/
			
			default:
				$childs =  parent::getChildsOfNode($a_parent_id);
//var_dump($childs);
				return $childs;
		}
	}

}
?>
