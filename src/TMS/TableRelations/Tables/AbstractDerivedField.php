<?php

namespace ILIAS\TMS\TableRelations\Tables;

interface AbstractDerivedField {

	/**
	 * Get all fields from which this field is derived.
	 *
	 * @return	AbstractTableField[]
	 */
	public function derivedFromRecursive();

	/**
	 * Get the name associated with the field to be used by interpreter.
	 *
	 * @return	string
	 */
	public function name();
}
