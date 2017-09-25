<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/Skill/classes/class.ilVirtualSkillTreeExplorerGUI.php");

/**
 * Explorer class that works on tree objects (Services/Tree)
 *
 * @author	Alex Killing <alex.killing@gmx.de>
 * @version	$Id$
 *
 * @ingroup ServicesUIComponent
 */
class ilSkillSelectorGUI extends ilVirtualSkillTreeExplorerGUI
{
	/**
	 * @var ilCtrl
	 */
	protected $ctrl;

	/**
	 * Constructor
	 */
	public function __construct($a_parent_obj, $a_parent_cmd, $a_select_gui, $a_select_cmd, $a_select_par = "selected_skill")
	{
		global $DIC;

		$this->ctrl = $DIC->ctrl();
		parent::__construct("skill_sel", $a_parent_obj, $a_parent_cmd);
		$this->select_gui = (is_object($a_select_gui))
			? strtolower(get_class($a_select_gui))
			: $a_select_gui;
		$this->select_cmd = $a_select_cmd;
		$this->select_par = $a_select_par;
		$this->setSkipRootNode(true);
	}

	/**
	 * Set skill to be opened
	 *
	 * @param
	 */
	function setSkillSelected($a_id)
	{
		$this->setNodeSelected($this->vtree->getCSkillIdForVTreeId($a_id));
	}

	/**
	 * Get selected skills (from POST)
	 */
	function getSelectedSkills()
	{
		$skills = array();
		$pa = $_POST[$this->select_postvar];
		if (is_array($pa))
		{
			foreach ($pa as $p)
			{
				$skills[] = $this->vtree->getCSkillIdForVTreeId($p);
			}
		}
		return $skills;
	}
	
	/**
	 * Get href for node
	 *
	 * @param mixed $a_node node object/array
	 * @return string href attribute
	 */
	function getNodeHref($a_node)
	{
		$ilCtrl = $this->ctrl;
		
		// we have a tree id like <skl_tree_id>:<skl_template_tree_id>
		// and make a "common" skill id in format <skill_id>:<tref_id>
		
		$id_parts = explode(":", $a_node["id"]);
		if ($id_parts[1] == 0)
		{
			// skill in main tree
			$skill_id = $a_node["id"];
		}
		else
		{
			// skill in template
			$skill_id = $id_parts[1].":".$id_parts[0];
		}
		
		$ilCtrl->setParameterByClass($this->select_gui, $this->select_par, $skill_id);
		$ret = $ilCtrl->getLinkTargetByClass($this->select_gui, $this->select_cmd);
		$ilCtrl->setParameterByClass($this->select_gui, $this->select_par, "");
		
		return $ret;
	}

	/**
	 * Is clickable
	 *
	 * @param
	 * @return
	 */
	function isNodeClickable($a_node)
	{
		return $this->nodeHasAction($a_node);
	}
	
	protected function isNodeSelectable($a_node)
	{
		return $this->nodeHasAction($a_node);
	}
	
	private function nodeHasAction($a_node)
	{
		if (in_array($a_node["type"], array("skll", "sktp")))
		{
			return true;
		}
		// references that refer directly to a (basic) skill template
		if ($a_node["type"] == "sktr" && ilSkillTreeNode::_lookupType($a_node["skill_id"]) == "sktp")
		{
			return true;
		}

		return false;
	}
}

?>
