<?php
namespace CaT\Plugins\CareerGoal\Requirements;

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
	public function addRequirementFormItems(\ilPropertyFormGUI $form) {
		$ti = new \ilTextInputGUI($this->txt("title"), ilActions::F_REQUIREMENT_TITLE);
		$ti->setRequired(true);
		$form->addItem($ti);

		$ti = new \ilTextAreaInputGUI($this->txt("description"), ilActions::F_REQUIREMENT_DESCRIPTION);
		$form->addItem($ti);

		$hi = new \ilHiddenInputGUI(ilActions::F_REQUIREMENT_OBJ_ID);
		$form->addItem($hi);

		$hi = new \ilHiddenInputGUI(ilActions::F_REQUIREMENT_CAREER_GOAL_ID);
		$form->addItem($hi);
	}

	public function getFilledSortItems($obj_id, $position) {
		$items = array();

		$ti = new \ilTextInputGUI($this->txt("title"), ilActions::F_REQUIREMENT_POSITION."_".$obj_id);
		$ti->setSize(4);
		$ti->setValue($position);
		$items[] = $ti;

		$hi = new \ilHiddenInputGUI(ilActions::F_REQUIREMENT_OBJ_ID."[]");
		$hi->setValue($obj_id);
		$items[] = $hi;

		return $items;
	}
}