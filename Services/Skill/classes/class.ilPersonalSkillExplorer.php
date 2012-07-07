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
require_once("./Services/UIComponent/Explorer/classes/class.ilExplorer.php");

class ilPersonalSkillExplorer extends ilExplorer
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
		$this->setTitleLength(999);
		
		include_once("./Services/Skill/classes/class.ilSkillTree.php");
		$this->tree = new ilSkillTree();
		$this->root_id = $this->tree->readRootId();
		
		$this->setSessionExpandVariable("skpexpand");
		$this->checkPermissions(false);
		$this->setPostSort(false);
		
		$this->setOrderColumn("order_nr");
//		$this->textwidth = 200;

		$this->force_open_path = array();
		
		$this->all_nodes = $this->tree->getSubTree($this->tree->getNodeData($this->root_id));
		foreach ($this->all_nodes as $n)
		{
			$this->node[$n["child"]] = $n;
			$this->child_nodes[$n["parent"]][] = $n;
			$this->parent[$n["child"]] = $n["parent"];
//echo "-$k-"; var_dump($n);
		}
		
		$this->buildSelectableTree($this->root_id);
	}
	
	/**
	 * Build selectable tree
	 *
	 * @param
	 * @return
	 */
	function buildSelectableTree($a_node_id)
	{
		if (ilSkillTreeNode::_lookupSelfEvaluation($a_node_id))
		{
			$this->selectable[$a_node_id] = true;
			$this->selectable[$this->parent[$a_node_id]] = true;
		}
		foreach ($this->getOriginalChildsOfNode($a_node_id) as $n)
		{
			$this->buildSelectableTree($n["child"]);
		}
		if ($this->selectable[$a_node_id] &&
			!ilSkillTreeNode::_lookupDraft($a_node_id))
		{
			$this->selectable_child_nodes[$this->node[$a_node_id]["parent"]][] =
				$this->node[$a_node_id];
		}
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
return;
		$ilCtrl->setParameterByClass("ilskillrootgui", "obj_id",
			$this->tree->readRootId());
		
		$tpl->setCurrentBlock("icon");
		$tpl->setVariable("ICON_IMAGE" , ilUtil::getImagePath("icon_scat_s.png"));
		$tpl->setVariable("TXT_ALT_IMG", $lng->txt("skmg_skills"));
		$tpl->parseCurrentBlock();

		$tpl->setCurrentBlock("link");
		$tpl->setVariable("TITLE", $lng->txt("skmg_skills"));
		if ($this->highlighted == $this->tree->readRootId())
		{
			$tpl->setVariable("A_CLASS", "class='il_HighlightedNode'");
		}
		$tpl->setVariable("LINK_TARGET",
			$ilCtrl->getLinkTargetByClass("ilskillrootgui", "listSkills"));
		$tpl->parseCurrentBlock();
		
		$tpl->touchBlock("element");

		$ilCtrl->setParameterByClass("ilskillrootgui", "obj_id",
			$_GET["obj_id"]);

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
		
/*		$tpl->setCurrentBlock("icon");
		$tpl->setVariable("ICON_IMAGE" , $this->getImage("icon_".$a_option["type"]."_s.png", $a_option["type"], $a_obj_id));
		$tpl->setVariable("TARGET_ID" , "iconid_".$a_node_id);
		$this->iconList[] = "iconid_".$a_node_id;
		$tpl->setVariable("TXT_ALT_IMG", $lng->txt($a_option["desc"]));
		$tpl->parseCurrentBlock();
		
		$this->outputIcons(false);*/
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
		if (!ilSkillTreeNode::_lookupSelfEvaluation($a_obj_id))
		{
			return false;
		}
		return true;
	}
	
	/**
	* build link target
	*/
	function buildLinkTarget($a_node_id, $a_type)
	{
		global $ilCtrl;

		$ilCtrl->setParameterByClass("ilpersonalskillsgui", "obj_id", $a_node_id);
		$ret = $ilCtrl->getLinkTargetByClass("ilpersonalskillsgui", "addSkill");
		$ilCtrl->setParameterByClass("ilpersonalskillsgui", "obj_id", $_GET["obj_id"]);
		
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
	 * Get childs of node (selectable tree)
	 *
	 * @param int $a_parent_id parent id
	 * @return array childs
	 */
	function getChildsOfNode($a_parent_id)
	{
		if (is_array($this->selectable_child_nodes[$a_parent_id]))
		{
			$childs =  $this->selectable_child_nodes[$a_parent_id];
			$childs = ilUtil::sortArray($childs, "order_nr", "asc", true);
			return $childs;
		}
		return array();
	}

	/**
	 * Get original childs of node (whole tree)
	 *
	 * @param int $a_parent_id parent id
	 * @return array childs
	 */
	function getOriginalChildsOfNode($a_parent_id)
	{
		if (is_array($this->child_nodes[$a_parent_id]))
		{
			return $this->child_nodes[$a_parent_id];
		}
		return array();
	}

	/**
	 * get image path (may be overwritten by derived classes)
	 */
	function getImage($a_name, $a_type = "", $a_obj_id = "")
	{
		if (in_array($a_type, array("sktr")))
		{
			return ilUtil::getImagePath("icon_skll_s.png");
		}
		return ilUtil::getImagePath($a_name);
	}

}
?>
