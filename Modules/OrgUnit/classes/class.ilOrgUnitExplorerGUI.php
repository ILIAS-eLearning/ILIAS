<?php
	require_once("./Services/UIComponent/Explorer2/classes/class.ilTreeExplorerGUI.php");

	class ilOrgUnitExplorerGUI extends ilTreeExplorerGUI {

		protected $stay_with_command = array("", "render", "view", "infoScreen", "showStaff");

		public function getNodeContent($node){
			global $lng;
			if($node["title"] == "__OrgUnitAdministration")
				$node["title"] = $lng->txt("objs_orgu");
			if($node["child"] == $_GET["ref_id"])
				return "<span class='ilExp2NodeContent ilHighlighted'>".$node["title"]."</span>";
			else
				return $node["title"];
		}

		public function getRootNode(){
			return $this->getTree()->getNodeData(ilObjOrgUnit::getRootOrgRefId());
		}

		/**
		 * Get node icon
		 *
		 * @param array
		 * @return
		 */
		function getNodeIcon($a_node)
		{
			$obj_id = ilObject::_lookupObjId($a_node["child"]);
			return ilObject::_getIcon($obj_id, "tiny", $a_node["type"]);
		}

		function getNodeHref($node){
			global $ilCtrl;
			$ilCtrl->setParameterByClass("ilObjOrgUnitGUI", "ref_id", $node["child"]);
			return $this->getLinkTarget();
		}

		protected function getLinkTarget(){
			global $ilCtrl;
			if($ilCtrl->getCmdClass() == "ilobjorgunitgui" && in_array($ilCtrl->getCmd(), $this->stay_with_command))
				return $ilCtrl->getLinkTargetByClass($ilCtrl->getCmdClass(), $ilCtrl->getCmd());
			else
				return $ilCtrl->getLinkTargetByClass("ilobjorgunitgui", "view");
		}
	}
?>