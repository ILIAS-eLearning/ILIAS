<?php

namespace CaT\Plugins\TalentAssessment\Observations;

class ilObservationsReportGUI {
	public function __construct($parent_obj) {
		global $tpl;

		$this->gTpl = $tpl;
		$this->parent_obj = $parent_obj;
	}

	public function show() {
		$this->gTpl->setContent("sdsd");
	}
}