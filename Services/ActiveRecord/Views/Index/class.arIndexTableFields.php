<?php
require_once('./Customizing/global/plugins/Libraries/ActiveRecord/Views/class.arViewField.php');
require_once('./Customizing/global/plugins/Libraries/ActiveRecord/Views/class.arViewFields.php');

/**
 * GUI-Class arIndexTableFields
 *
 * @author  Timon Amstutz <timon.amstutz@ilub.unibe.ch>
 * @version 2.0.7
 *
 */
class arIndexTableFields extends arViewFields {

	const FIELD_CLASS = 'arIndexTableField';
	/**
	 * @var array
	 */
	protected $selectable_columns = array();


	/**
	 * Get selectable columns
	 *
	 * @param       arIndexTableGUI $translator used as translating instance
	 *
	 * @return        array
	 */
	function getSelectableColumns(arIndexTableGUI $translator) {
		if (empty($this->selectable_columns)) {
			foreach ($this->getFields() as $field) {
				/**
				 * @var arIndexTableField $field
				 */
				if ($field->getVisible()) {
					$this->selectable_columns[$field->getName()] = array(
						"txt" => $translator->txt($field->getTxt()),
						"default" => $field->getVisibleDefault()
					);
				}
			}
		}

		return $this->selectable_columns;
	}
}