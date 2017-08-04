<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilDclSelectionRecordFieldModel
 *
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 */
abstract class ilDclSelectionRecordFieldModel extends ilDclBaseRecordFieldModel {

	/**
	 * @return array|mixed|string
	 */
	public function getValue() {
		if ($this->getField()->isMulti() && !is_array($this->value)) {
			return array($this->value);
		}
		if (!$this->getField()->isMulti() && is_array($this->value)) {
			return (count($this->value) == 1) ? array_shift($this->value) : '';
		}
		return $this->value;
	}


	public function parseExportValue($value) {
		return is_array($value) ? implode(', ', $value) : $value;
	}
}