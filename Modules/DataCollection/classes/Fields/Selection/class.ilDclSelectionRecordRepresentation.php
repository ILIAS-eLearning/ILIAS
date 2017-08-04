<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilDclSelectionRecordRepresentation
 *
 * @author  Theodor Truffer <tt@studer-raimann.ch>
 */
abstract class ilDclSelectionRecordRepresentation extends ilDclBaseRecordRepresentation {

	/**
	 * @param bool $link
	 *
	 * @return string
	 */
	public function getHTML($link = true) {
		$record_field_value = $this->getRecordField()->getValue();
		$options = $this->getField()->getProperty(static::PROP_SELECTION_OPTIONS);

		if ($this->getField()->isMulti()) {
			$values = array();
			foreach ($options as $k => $v) {
				if (in_array($k, $record_field_value)) {
					$values[] = $v;
				}
			}
			return implode('<br>', $values);
		}

		return $options[$record_field_value];
	}


}