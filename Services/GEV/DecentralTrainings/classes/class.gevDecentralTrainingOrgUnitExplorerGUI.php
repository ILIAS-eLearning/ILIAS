<?php
require_once("Services/TEP/classes/class.ilTEPOrgUnitExplorerGUI.php");
require_once("Services/GEV/Utils/classes/class.gevSettings.php");
class gevDecentralTrainingOrgUnitExplorerGUI extends ilTEPOrgUnitExplorerGUI {

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

			if($a_parent_node_id == gevOrgUnitUtils::getUVGOrgUnitRefId()) {
				$gev_settings = gevSettings::getInstance();
				$uvg_template_node = $gev_settings->getDBVPOUTemplateUnitId();

				foreach ($units as $key => $value) {
					if($value["obj_id"] == $uvg_template_node) {
						unset($units[$key]);
						continue;
					}

					$viewable["view"][$key] = $value;
				}
			} else {
				foreach ($units as $key => $value) {
					$viewable["view_rekru"][$key] = $value;
				}
			}

			$this->setSelectableOrgUnitIds($viewable);
			return $units;
		}

		return array();
	}
}