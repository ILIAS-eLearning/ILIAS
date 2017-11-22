<?php
namespace ILIAS\TMS\TableRelations\Tables;

use ILIAS\TMS\Filter as Filters;


/**
 * Derived fields are constructed from simple table fields and may represent
 * complext calculations over these.
 */
abstract class DerivedField extends Filters\Predicates\Field implements AbstractDerivedField{

	protected $derived_from = array();

	/**
	 * Get all fields from which this field is derived.
	 *
	 * @return	AbstractTableField[]
	 */
	public function derivedFromRecursive() {
		$return = array();
		foreach ($this->derived_from as $field) {
			if($field instanceof AbstractTableField) {
				$return[$field->name()] = $field;
			} elseif($field instanceof self) {
				$return = array_merge($return, $field->derivedFromRecursive());
			} else {
				throw new TableExcepiton('unknown field type');
			}
		}
		return $return;
	}

	/**
	 * Get first order of fields from which this field is derived.
	 *
	 * @return	AbstractTableField[]
	 */
	public function derivedFrom() {
		return array_values($this->derived_from);
	}

	/**
	 * In case of TableFields this function returns a composition of 
	 * field-name and table name for sake of uniqueness. This is not
	 * necessary here, name and name_simple are same.
	 *
	 * @return	string
	 */
	public function name_simple() {
		return $this->name();
	}
}
