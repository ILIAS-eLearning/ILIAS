<?php

namespace CaT\Plugins\TalentAssessment\Observations;

require_once("Services/CaTUIComponents/classes/class.catHSpacerGUI.php");

class ilObservationsOverviewGUI {
	public function __construct($parent_obj) {
		global $tpl;

		$this->gTpl = $tpl;
		$this->parent_obj = $parent_obj;
	}

	public function render() {
		$obj_id = $this->parent_obj->getObjId();
		$actions = $this->parent_obj->getActions();

		$observator = $actions->getAssignedUser($obj_id, $actions->getAssignedUser($obj_id));
		$obs = $actions->getObservationOverviewData($obj_id, $observator);
		$html = "";
		$spacer = new \catHSpacerGUI();

		foreach ($obs as $key => $ob) {
			$gui = new ilObservationsOverviewTableGUI($this->parent_obj, $ob, $observator);
			$html .= $gui->getHtml();
			$html .= $spacer->render();
		}

		return $html;
	}
}