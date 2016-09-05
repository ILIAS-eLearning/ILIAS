<?php

namespace CaT\Plugins\TalentAssessment\Observations;

require_once("Services/CaTUIComponents/classes/class.catHSpacerGUI.php");

class ilObservationsListGUI {
	public function __construct($parent_obj) {
		global $tpl;

		$this->gTpl = $tpl;
		$this->parent_obj = $parent_obj;
	}

	public function render() {
		$obs = $this->parent_obj->getActions()->getObservationListData($this->parent_obj->getObjId());
		$html = "";
		$spacer = new \catHSpacerGUI();

		foreach ($obs as $key => $ob) {
			$gui = new ilObservationsListTableGUI($this->parent_obj, $ob);
			$html .= $gui->getHtml();
			$html .= $spacer->render();
		}

		$html.= $this->renderLegend();

		return $html;
	}

	protected function renderLegend() {
		$legend = array(array("points"=>1, "description"=>"points_description_1")
					  , array("points"=>2, "description"=>"points_description_2")
					  , array("points"=>3, "description"=>"points_description_3")
					  , array("points"=>4, "description"=>"points_description_4")
					  , array("points"=>5, "description"=>"points_description_5")
				);
		$gui = new ilObservationsListLegendTableGUI($this->parent_obj, $legend);
		return $gui->getHtml();
	}
}