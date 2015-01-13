<?php
require_once('./Customizing/global/plugins/Libraries/ActiveRecord/Views/class.arViewField.php');
require_once('./Customizing/global/plugins/Libraries/ActiveRecord/Views/class.arViewFields.php');

/**
 * GUI-Class arEditFields
 *
 * @author  Timon Amstutz <timon.amstutz@ilub.unibe.ch>
 * @version 2.0.7
 *
 */
class arEditFields extends arViewFields {

	const FIELD_CLASS = 'arEditField';


	/**
	 * @return bool
	 */
	public function sortFields() {
		uasort($this->fields, function (arEditField $field_a, arEditField $field_b) {
			//If both fields are or are not subelements, then let the position decide which is displayed first
			if (($field_a->getSubelementOf()) == ($field_b->getSubelementOf())) {
				return $field_a->getPosition() > $field_b->getPosition();
			} //If only one of the elements is a subelement, then the other has to be generated first
			else {
				return $field_a->getSubelementOf();
			}
		});
	}
}