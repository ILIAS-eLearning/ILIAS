<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once("./Modules/OrgUnit/classes/class.ilOrgUnitExplorerGUI.php");

/**
 * Class ilTEPOrgUnitExplorerGUI
 * 
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @version $Id$
 *
 * @ingroup ServicesTEP
 */
class ilTEPOrgUnitExplorerGUI extends ilExplorerBaseGUI
{		
	protected $sel_orgu_ids; // [array]
	protected $root_node_ref_id; // [int]
	
	// gev-patch start
	public function __construct($a_expl_id, $a_parent_obj, $a_parent_cmd, $a_tree, $a_root_node_ref_id = null)
	{
		$this->root_node_ref_id = $a_root_node_ref_id;
		// gev-patch end
		parent::__construct($a_expl_id, $a_parent_obj, $a_parent_cmd, $a_tree);
		
	}
	
	
	//
	// properties
	// 
	
	public function setSelectableOrgUnitIds(array $a_ids)
	{
		$this->sel_orgu_ids = $a_ids;
	}
	
	public function getSelectableOrgUnitIds()
	{
		return $this->sel_orgu_ids;
	}
	
	//
	// selection only, not clickable
	// 
	
	public function getNodeHref($node)
	{		
		return;
	}
		
	public function isNodeClickable($a_node)
	{
		return;
	}

	protected function getLinkTarget()
	{
		return;
	}


	function getRootNode() {
		return array("ref_id"=>-1,"title"=>"rootNode");
	}
	
	/**
	 * Get childs of node
	 *
	 * @param string $a_parent_id parent node id
	 * @return array childs
	 */
	function getChildsOfNode($a_parent_node_id) {
		$viewable = $this->getSelectableOrgUnitIds();
		
		if ($a_parent_node_id == $this->getRootNode()["ref_id"]) {
			return $this->getAllSelectableOrgUnits();
		}

		if($this->isInArray($a_parent_node_id, $viewable["view_rekru"])) {
			$current = $viewable["view_rekru"][$a_parent_node_id];
			
			require_once("Services/GEV/Utils/classes/class.gevOrgUnitUtils.php");
			$org_unit_utils = gevOrgUnitUtils::getInstance($current["obj_id"]);
			$units = $org_unit_utils->getOrgUnitsOneTreeLevelBelowWithTitle();

			foreach ($units as $key => $value) {
				$viewable["view_rekru"][$key] = $value;
			}

			$this->setSelectableOrgUnitIds($viewable);
			return $units;
		}

		return array();
	}
	
	/**
	 * Get content of a node
	 *
	 * @param mixed $a_node node array or object
	 * @return string content of the node
	 */
	function getNodeContent($a_node) {
		return $a_node["title"];
	}

	/**
	 * Get id of a node
	 *
	 * @param mixed $a_node node array or object
	 * @return string id of node
	 */
	function getNodeId($a_node) {
		return $a_node["ref_id"];
	}

	
	public function isNodeSelectable($a_node) {
		$viewable = $this->getSelectableOrgUnitIds();

		if(in_array($a_node, $viewable["view"]) || in_array($a_node, $viewable["view_rekru"])){
			return true;
		}
	}

	public function isNodeVisible($a_node) {
		return true;
	}

	protected function getAllSelectableOrgUnits() {
		$vis_orgus = $this->getSelectableOrgUnitIds();
		$ret = $vis_orgus["view"];
		foreach ($vis_orgus["view_rekru"] as $key => $value) {
			if(!array_key_exists($key, $ret)) {
				$ret[$key] = $value;
			}
		}
		
		return $ret;
	}

	protected function isInArray($a_serach, $a_array) {
		foreach($a_array as $key => $value) {
			if(in_array($a_serach, $value)) {
				return true;
			}
		}

		return false;
	}
}