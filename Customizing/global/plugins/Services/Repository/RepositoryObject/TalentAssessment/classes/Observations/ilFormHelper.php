<?php

namespace CaT\Plugins\TalentAssessment\Observations;
use CaT\Plugins\TalentAssessment\ilActions;

trait ilFormHelper {
	protected function getDropDown($obj_id, $value) {
		$options = array("1.0"=>"1,0"
						, "1.5"=>"1,5"
						, "2.0"=>"2,0"
						, "2.5"=>"2,5"
						, "3.0"=>"3,0"
						, "3.5"=>"3,5"
						, "4.0"=>"4,0"
						, "4.5"=>"4,5"
						, "5.0"=>"5,0"
					);
		$drop = new \ilSelectInputGUI("", ilActions::SI_PREFIX."[".$obj_id."]");
		$drop->setOptions($options);
		$drop->setValue($value);
		return $drop->render();
	}
}