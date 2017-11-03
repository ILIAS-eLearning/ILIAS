<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/UIComponent/Explorer2/classes/class.ilTreeExplorerGUI.php");
include_once("./Services/Skill/classes/class.ilSkillTreeNode.php");

/**
 * Explorer for selecting a personal workspace item
 *
 * @author	Alex Killing <alex.killing@gmx.de>
 *
 * @ingroup ServicesPersonalWorkspace
 */
class ilWorkspaceExplorerGUI extends ilTreeExplorerGUI
{
	/**
	 * @var ilCtrl
	 */
	protected $ctrl;

	/**
	 * @var ilLanguage
	 */
	protected $lng;

	protected $selectable_types = array();

	/**
	 * Constructor
	 */
	public function __construct($a_user_id, $a_parent_obj, $a_parent_cmd, $a_select_gui, $a_select_cmd, $a_select_par = "sel_wsp_obj")
	{
		global $DIC;

		$this->ctrl = $DIC->ctrl();
		$this->lng = $DIC->language();
		
		$this->select_gui = (is_object($a_select_gui))
			? strtolower(get_class($a_select_gui))
			: $a_select_gui;
		$this->select_cmd = $a_select_cmd;
		$this->select_par = $a_select_par;

		include_once "Services/PersonalWorkspace/classes/class.ilWorkspaceTree.php";
		include_once "Services/PersonalWorkspace/classes/class.ilWorkspaceAccessHandler.php";

		$this->tree = new ilWorkspaceTree($a_user_id);
		$this->root_id = $this->tree->readRootId();
		$this->access_handler = new ilWorkspaceAccessHandler($this->tree);

		parent::__construct("wsp_sel", $a_parent_obj, $a_parent_cmd, $this->tree);
		$this->setSkipRootNode(false);
		$this->setAjax(true);
		
		$this->setTypeWhiteList(array("wsrt", "wfld"));
	}

	/**
	 * Set selectable types
	 *
	 * @param array $a_val selectable types	
	 */
	function setSelectableTypes($a_val)
	{
		$this->selectable_types = $a_val;
	}
	
	/**
	 * Get selectable types
	 *
	 * @return array selectable types
	 */
	function getSelectableTypes()
	{
		return $this->selectable_types;
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
		
		$ilCtrl->setParameterByClass($this->select_gui, $this->select_par, $a_node["child"]);
		$ret = $ilCtrl->getLinkTargetByClass($this->select_gui, $this->select_cmd);
		$ilCtrl->setParameterByClass($this->select_gui, $this->select_par, "");
		
		return $ret;
	}

	/**
	 * Get node content
	 *
	 * @param array 
	 * @return
	 */
	function getNodeContent($a_node)
	{
		$lng = $this->lng;

		if ($a_node["child"] == $this->tree->getRootId())
		{
			return $lng->txt("wsp_personal_workspace");
		}

		return $a_node["title"];
	}
	
	/**
	 * Is clickable
	 *
	 * @param
	 * @return
	 */
	function isNodeClickable($a_node)
	{
		if (in_array($a_node["type"], $this->getSelectableTypes()))
		{
			return true;
		}
		return false;
	}
	
	/**
	 * get image path (may be overwritten by derived classes)
	 */
	function getNodeIcon($a_node)
	{
		$t = $a_node["type"];
		if (in_array($t, array("sktr")))
		{
			return ilUtil::getImagePath("icon_skll.svg");
		}
		return ilUtil::getImagePath("icon_".$t.".svg");
	}

}

?>
