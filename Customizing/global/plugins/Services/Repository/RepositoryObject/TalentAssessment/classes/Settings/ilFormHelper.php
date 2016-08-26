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
		$si->setRequired(true);
		$si->setOptions($career_goal_options);
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
		$si->setOptions($venue_options);
		$form->addItem($si);

		$si = new \ilSelectInputGUI($this->txt("org_unit"), ilActions::F_ORG_UNIT);
		$si->setOptions($org_unit_options);
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
	 *
	 * @return null
	 */
	public function addSettingsFormItemsUpdate(\ilPropertyFormGUI $form, array $career_goal_options, array $venue_options, array $org_unit_options) {
		require_once('Services/Form/classes/class.ilDateDurationInputGUI.php');
		require_once('Services/Form/classes/class.ilNonEditableValueGUI.php');

		$ne = new \ilNonEditableValueGUI($this->txt("state"), ilActions::F_STATE);
		$form->addItem($ne);

		$si = new \ilSelectInputGUI($this->txt("career_goal"), ilActions::F_CAREER_GOAL);
		$si->setRequired(true);
		$si->setOptions($career_goal_options);
		$form->addItem($si);

		$ti = new \ilTextInputGUI($this->txt("username"), ilActions::F_USERNAME);
		$ti->setRequired(true);
		$ti->setInfo($this->txt("username_info"));
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
		$form->addItem($du);

		$si = new \ilSelectInputGUI($this->txt("venue"), ilActions::F_VENUE);
		$si->setOptions($venue_options);
		$form->addItem($si);

		$si = new \ilSelectInputGUI($this->txt("org_unit"), ilActions::F_ORG_UNIT);
		$si->setOptions($org_unit_options);
		$si->setInfo($this->txt("org_unit_info"));
		$form->addItem($si);
	}
}