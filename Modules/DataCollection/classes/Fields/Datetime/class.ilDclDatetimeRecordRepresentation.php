<?php
require_once('./Modules/DataCollection/classes/Fields/Base/class.ilDclBaseRecordRepresentation.php');

/**
 * Class ilDclDateTimeRecordRepresentation
 *
 * @author  Michael Herren <mh@studer-raimann.ch>
 * @version 1.0.0
 */
class ilDclDatetimeRecordRepresentation extends ilDclBaseRecordRepresentation {

	/**
	 * Outputs html of a certain field
	 * @param mixed $value
	 * @param bool|true $link
	 *
	 * @return string
	 */
	public function getHTML($link = true) {
		$value = $this->getRecordField()->getValue();
		return ilDatePresentation::formatDate(new ilDate($value, IL_CAL_DATE));
	}


	/**
	 * function parses stored value to the variable needed to fill into the form for editing.
	 *
	 * @param $value
	 *
	 * @return mixed
	 */
	public function parseFormInput($value) {
		if (!$value || $value == "-") {
			return NULL;
		}
		return substr($value, 0, - 9);
	}
}