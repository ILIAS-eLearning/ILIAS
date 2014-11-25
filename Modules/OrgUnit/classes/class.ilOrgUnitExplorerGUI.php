<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */
require_once("./Services/UIComponent/Explorer2/classes/class.ilTreeExplorerGUI.php");

/**
 * Class ilOrgUnitExplorerGUI
 *
 * @author: Oskar Truffer <ot@studer-raimann.ch>
 * @author: Martin Studer <ms@studer-raimann.ch>
 *
 */
class ilOrgUnitExplorerGUI extends ilTreeExplorerGUI {

	/**
	 * @var array
	 */
	protected $stay_with_command = array( "", "render", "view", "infoScreen", "showStaff", "performPaste", "cut" );


	/**
	 * @param $a_expl_id
	 * @param $a_parent_obj
	 * @param $a_parent_cmd
	 * @param $a_tree
	 */
	public function __construct($a_expl_id, $a_parent_obj, $a_parent_cmd, $a_tree) {
		parent::__construct($a_expl_id, $a_parent_obj, $a_parent_cmd, $a_tree);
		$this->setAjax(true);
		$this->setTypeWhiteList(array( "orgu" ));
	}


	/**
	 * @param mixed $node
	 *
	 * @return string
	 */
	public function getNodeContent($node) {
		global $lng;
		if ($node["title"] == "__OrgUnitAdministration") {
			$node["title"] = $lng->txt("objs_orgu");
		}
		if ($node["child"] == $_GET["ref_id"]) {
			return "<span class='ilExp2NodeContent ilHighlighted'>" . $node["title"] . "</span>";
		} else {
			return $node["title"];
		}
	}


	/**
	 * @return mixed
	 */
	public function getRootNode() {
		return $this->getTree()->getNodeData(ilObjOrgUnit::getRootOrgRefId());
	}


	/**
	 * Get node icon
	 * Return custom icon of OrgUnit type if existing
	 *
	 * @param array $a_node
	 *
	 * @return string
	 */
	public function getNodeIcon($a_node) {
		global $ilias;
		if ($ilias->getSetting('custom_icons')) {
			$icons_cache = ilObjOrgUnit::getIconsCache();
			$obj_id = ilObject::_lookupObjId($a_node["child"]);
			if (isset($icons_cache[$obj_id])) {
				return $icons_cache[$obj_id];
			}
		}

		return ilObject::_getIcon($obj_id, "tiny", $a_node["type"]);
	}


	/**
	 * @param mixed $node
	 *
	 * @return string
	 */
	public function getNodeHref($node) {
		global $ilCtrl;
		if ($ilCtrl->getCmd() == "performPaste") {
			$ilCtrl->setParameterByClass("ilObjOrgUnitGUI", "target_node", $node["child"]);
		}
		$ilCtrl->setParameterByClass("ilObjOrgUnitGUI", "ref_id", $node["child"]);

		return $this->getLinkTarget();
	}


	/**
	 * @return string
	 */
	protected function getLinkTarget() {
		global $ilCtrl;

		if ($ilCtrl->getCmdClass() == "ilobjorgunitgui" AND in_array($ilCtrl->getCmd(), $this->stay_with_command)) {
			return $ilCtrl->getLinkTargetByClass($ilCtrl->getCmdClass(), $ilCtrl->getCmd());
		} else {
			return $ilCtrl->getLinkTargetByClass("ilobjorgunitgui", "view");
		}
	}


	/**
	 * Get childs of node
	 *
	 * @param                  $a_parent_node_id
	 *
	 * @global ilAccessHandler $ilAccess
	 * @internal param int $a_parent_id parent id
	 * @return array childs
	 */
	public function getChildsOfNode($a_parent_node_id) {
		global $ilAccess;

		$wl = $this->getTypeWhiteList();
		if (is_array($wl) && count($wl) > 0) {
			$childs = $this->tree->getChildsByTypeFilter($a_parent_node_id, $wl, $this->getOrderField());
		} else {
			$childs = $this->tree->getChilds($a_parent_node_id, $this->getOrderField());
		}

		// apply black list filter
		$bl = $this->getTypeBlackList();
		if (is_array($bl) && count($bl) > 0) {
			$bl_childs = array();
			foreach ($childs as $k => $c) {
				if (!in_array($c["type"], $bl)) {
					$bl_childs[$k] = $c;
				}
			}

			return $bl_childs;
		}

		//Check Access
		foreach ($childs as $key => $child) {
			if (!$ilAccess->checkAccess('visible', '', $child['ref_id'])) {
				unset($childs[$key]);
			}
		}

		return $childs;
	}


	/**
	 * Sort childs
	 *
	 * @param array $a_childs array of child nodes
	 * @param       $a_parent_node_id
	 *
	 * @internal param mixed $a_parent_node parent node
	 *
	 * @return array array of childs nodes
	 */
	public function sortChilds($a_childs, $a_parent_node_id) {
		usort($a_childs, array( __CLASS__, "sortbyTitle" ));

		return $a_childs;
	}


	/**
	 * @param $a
	 * @param $b
	 *
	 * @return int
	 */
	public function sortbyTitle($a, $b) {
		return strcmp($a["title"], $b["title"]);
	}


	/**
	 * Is node clickable?
	 *
	 * @param mixed            $a_node node object/array
	 *
	 * @global ilAccessHandler $ilAccess
	 * @return boolean node clickable true/false
	 */
	public function isNodeClickable($a_node) {
		global $ilAccess;

		if ($ilAccess->checkAccess('read', '', $a_node['ref_id'])) {
			return true;
		}

		return false;
	}
}

?>