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
		$ni = new \ilNumberInputGUI($this->txt(""), ilActions::F_LOWMARK);
		$ni->setMinValue(0);
		$ni->setMaxValue(5);
		$ni->setInfo($this->txt(""));
		$ni->setRequired(true);
		$ni->allowDecimals(true);
		$form->addItem($ni);

		$ni = new \ilNumberInputGUI($this->txt(""), ilActions::F_SHOULD_SPECIFICATION);
		$ni->setMinValue(0);
		$ni->setMaxValue(5);
		$ni->setInfo($this->txt(""));
		$ni->setRequired(true);
		$ni->allowDecimals(true);
		$form->addItem($ni);

		$ti = new \ilTextAreaInputGUI($this->txt(""), ilActions::F_DEFAULT_TEXT_FAILED);
		$ti->setRequired(true);
		$ti->setInfo($this->txt(""));
		$form->addItem($ti);

		$ti = new \ilTextAreaInputGUI($this->txt(""), ilActions::F_DEFAULT_TEXT_PARTIAL);
		$ti->setRequired(true);
		$ti->setInfo($this->txt(""));
		$form->addItem($ti);

		$ti = new \ilTextAreaInputGUI($this->txt(""), ilActions::F_DEFAULT_TEXT_SUCCESS);
		$ti->setRequired(true);
		$ti->setInfo($this->txt(""));
		$form->addItem($ti);
	}
}