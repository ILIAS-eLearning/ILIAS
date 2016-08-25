<?php
namespace CaT\Plugins\CareerGoal\Observations;

use CaT\Plugins\CareerGoal\ilActions;

trait ilFormHelper {
	/**
	 * @param 	string	$code
	 * @return	string
	 */
	abstract protected function txt($code);

	/**
	 * Complete the form with observation input controls.
	 *
	 * @param 	ilPropertyFormGUI 	$form
	 *
	 * @return null
	 */
	public function addObservationFormItems(\ilPropertyFormGUI $form, $requirements_options) {
		$ti = new \ilTextInputGUI($this->txt("title"), ilActions::F_OBSERVATION_TITLE);
		$ti->setRequired(true);
		$form->addItem($ti);

		$ti = new \ilTextAreaInputGUI($this->txt("description"), ilActions::F_OBSERVATION_DESCRIPTION);
		$form->addItem($ti);

		require_once("Services/Form/classes/class.ilMultiSelectInputGUI.php");
		$mi = new \ilMultiSelectInputGUI($this->txt("requirements"), ilActions::F_OBSERVATION_REQUIREMENTS);
		$mi->setOptions($requirements_options);
		$mi->setRequired(true);
		$form->addItem($mi);

		$hi = new \ilHiddenInputGUI(ilActions::F_OBSERVATION_OBJ_ID);
		$form->addItem($hi);

		$hi = new \ilHiddenInputGUI(ilActions::F_OBSERVATION_CAREER_GOAL_ID);
		$form->addItem($hi);
	}

	/**
	 *
	 *
	 * @param int 	$obj_id
	 * @param int 	$position;
	 */
	public function getFilledSortItems($obj_id, $position) {
		$items = array();

		$ti = new \ilTextInputGUI($this->txt("title"), ilActions::F_OBSERVATION_POSITION."_".$obj_id);
		$ti->setSize(4);
		$ti->setValue($position);
		$items[] = $ti;

		$hi = new \ilHiddenInputGUI(ilActions::F_OBSERVATION_OBJ_ID."[]");
		$hi->setValue($obj_id);
		$items[] = $hi;

		return $items;
	}
}