<?php
namespace CaT\Plugins\CareerGoal\Settings;

use CaT\Plugins\CareerGoal\ilActions;

trait ilFormHelper {
	/**
	 * @param 	string	$code
	 * @return	string
	 */
	abstract protected function txt($code);

	/**
	 * Complete the form with setting input controls.
	 *
	 * @param 	ilPropertyFormGUI 	$form
	 *
	 * @return null
	 */
	public function addSettingsFormItems(\ilPropertyFormGUI $form) {
		$ni = new \ilNumberInputGUI($this->txt("lowmark"), ilActions::F_LOWMARK);
		$ni->setMinValue(0);
		$ni->setMaxValue(5);
		$ni->setInfo($this->txt("lowmark_info"));
		$ni->setRequired(true);
		$ni->allowDecimals(true);
		$form->addItem($ni);

		$ni = new \ilNumberInputGUI($this->txt("sheduled_specification"), ilActions::F_SHOULD_SPECIFICATION);
		$ni->setMinValue(0);
		$ni->setMaxValue(5);
		$ni->setInfo($this->txt("sheduled_specification_info"));
		$ni->setRequired(true);
		$ni->allowDecimals(true);
		$form->addItem($ni);

		$ti = new \ilTextAreaInputGUI($this->txt("default_text_failed"), ilActions::F_DEFAULT_TEXT_FAILED);
		$ti->setRequired(true);
		$ti->setInfo($this->txt("default_text_failed_info"));
		$form->addItem($ti);

		$ti = new \ilTextAreaInputGUI($this->txt("default_text_partial"), ilActions::F_DEFAULT_TEXT_PARTIAL);
		$ti->setRequired(true);
		$ti->setInfo($this->txt("default_text_partial_info"));
		$form->addItem($ti);

		$ti = new \ilTextAreaInputGUI($this->txt("default_text_success"), ilActions::F_DEFAULT_TEXT_SUCCESS);
		$ti->setRequired(true);
		$ti->setInfo($this->txt("default_text_success_info"));
		$form->addItem($ti);
	}
}