<?php

namespace CaT\Plugins\TalentAssessment\Observations;
use CaT\Plugins\TalentAssessment\ilActions;

trait ilFormHelper {
	/**
	 * @param 	string	$code
	 * @return	string
	 */
	abstract protected function txt($code);

	/**
	 * get a drop down box
	 *
	 * @param 	int 	$obj_id
	 * @param 	int 	$value
	 *
	 * @return \ilSelectInputGUI
	 */
	protected function getDropDown($obj_id, $value, $finished) {
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
		$drop->setDisabled($finished);
		return $drop->render();
	}

	/**
	 * add form item for report view
	 *
	 * @param \ilPropertyFormGUI 	$form
	 */
	protected function addReportFormItem(\ilPropertyFormGUI $form, $finished) {
		$ne = new \ilNonEditableValueGUI($this->txt("potential"), ilActions::F_POTENTIAL);
		$form->addItem($ne);

		$tea = new \ilTextAreaInputGUI($this->txt("result_text"), ilActions::F_RESULT_COMMENT);
		$tea->setRows(10);
		$tea->setDisabled($finished);
		$form->addItem($tea);

		$tea = new \ilTextAreaInputGUI($this->txt("judgement_text"), ilActions::F_JUDGEMENT_TEXT);
		$tea->setRows(10);
		$tea->setInfo($this->txt("judgement_text_info"));
		$tea->setDisabled($finished);
		$form->addItem($tea);
	}

	/**
	 * 
	 *
	 * @param array 	string => string
	 * @param \CaT\Plugins\TalentAssessment\Settings\TalentAssessment $settings
	 * @param string 	$potential_text
	 *
	 * @return array 	string => string
	 */
	protected function getReportFormValues($values, $settings, $potential_text) {
		$values[ilActions::F_POTENTIAL] = $this->txt($potential_text);
		$values[ilActions::F_RESULT_COMMENT] = $settings->getResultComment();
		$values[ilActions::F_JUDGEMENT_TEXT] = $settings->getTextForPotential();

		return $values;
	}
}