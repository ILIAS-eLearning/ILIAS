<?php
namespace CaT\Plugins\TalentAssessment\Settings;

use CaT\Plugins\TalentAssessment\ilActions;

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
	 * @param 	array 				$career_goal_options
	 * @param 	array 				$venue_options
	 * @param 	array 				$org_unit_options
	 *
	 * @return null
	 */
	public function addSettingsFormItems(\ilPropertyFormGUI $form, array $career_goal_options, array $venue_options, array $org_unit_options) {
		$si = new \ilSelectInputGUI($this->txt("career_goal"), ilActions::F_CAREER_GOAL);
		$options = array(null=>$this->txt("pls_select")) + $career_goal_options;
		$si->setRequired(true);
		$si->setOptions($options);
		$form->addItem($si);

		$ti = new \ilTextInputGUI($this->txt("username"), ilActions::F_USERNAME);
		$ti->setRequired(true);
		$ti->setInfo($this->txt("username_info"));
		$form->addItem($ti);

		require_once('Services/Form/classes/class.ilDateDurationInputGUI.php');
		$du = new \ilDateDurationInputGUI($this->txt("date"), ilActions::F_DATE);
		$du->setShowDate(true);
		$du->setShowTime(true);
		$du->setStartText($this->txt("start"));
		$du->setEndText($this->txt("end"));
		$form->addItem($du);

		$si = new \ilSelectInputGUI($this->txt("venue"), ilActions::F_VENUE);
		$options = array(null=>$this->txt("pls_select")) + $venue_options;
		$si->setOptions($options);
		$form->addItem($si);

		$si = new \ilSelectInputGUI($this->txt("org_unit"), ilActions::F_ORG_UNIT);
		$options = array(null=>$this->txt("pls_select")) + $org_unit_options;
		$si->setOptions($options);
		$si->setInfo($this->txt("org_unit_info"));
		$form->addItem($si);
	}

		/**
	 * Complete the form with setting input controls.
	 *
	 * @param 	ilPropertyFormGUI 	$form
	 * @param 	array 				$career_goal_options
	 * @param 	array 				$venue_options
	 * @param 	array 				$org_unit_options
	 * @param 	bool 				$edit
	 *
	 * @return null
	 */
	public function addSettingsFormItemsUpdate(\ilPropertyFormGUI $form, array $career_goal_options, array $venue_options, array $org_unit_options, $edit) {
		require_once('Services/Form/classes/class.ilDateDurationInputGUI.php');
		require_once('Services/Form/classes/class.ilNonEditableValueGUI.php');

		$ne = new \ilNonEditableValueGUI($this->txt("state"), ilActions::F_STATE);
		$form->addItem($ne);

		$si = new \ilSelectInputGUI($this->txt("career_goal"), ilActions::F_CAREER_GOAL);
		$options = array(null=>$this->txt("pls_select")) + $career_goal_options;
		$si->setRequired(true);
		$si->setOptions($options);
		$si->setDisabled($edit);
		$form->addItem($si);

		$ti = new \ilTextInputGUI($this->txt("username"), ilActions::F_USERNAME);
		$ti->setRequired(true);
		$ti->setInfo($this->txt("username_info"));
		$ti->setDisabled($edit);
		$form->addItem($ti);

		$ne = new \ilNonEditableValueGUI($this->txt("firstname"), ilActions::F_FIRSTNAME);
		$form->addItem($ne);

		$ne = new \ilNonEditableValueGUI($this->txt("lastname"), ilActions::F_LASTNAME);
		$form->addItem($ne);

		$ne = new \ilNonEditableValueGUI($this->txt("email"), ilActions::F_EMAIL);
		$form->addItem($ne);

		$du = new \ilDateDurationInputGUI($this->txt("date"), ilActions::F_DATE);
		$du->setShowDate(true);
		$du->setShowTime(true);
		$du->setStartText($this->txt("start"));
		$du->setEndText($this->txt("end"));
		$du->setDisabled($edit);
		$form->addItem($du);

		$si = new \ilSelectInputGUI($this->txt("venue"), ilActions::F_VENUE);
		$options = array(null=>$this->txt("pls_select")) + $venue_options;
		$si->setOptions($options);
		$si->setDisabled($edit);
		$form->addItem($si);

		$si = new \ilSelectInputGUI($this->txt("org_unit"), ilActions::F_ORG_UNIT);
		$options = array(null=>$this->txt("pls_select")) + $org_unit_options;
		$si->setOptions($options);
		$si->setInfo($this->txt("org_unit_info"));
		$si->setDisabled($edit);
		$form->addItem($si);
	}
}