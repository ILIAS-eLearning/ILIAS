<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilBiblEntry
 *
 * @author     Gabriel Comte
 * @author     Fabian Schmid <fs@studer-raimann.ch>
 *
 * @deprecated REFACTOR: use ActiveRecord, Attributes sollten Objecte sein nicht Arrays, beim speichern eines Entries auch die Attribut-Objekte speichern
 */
class ilBiblEntry extends ActiveRecord implements ilBiblEntryInterface {

	const TABLE_NAME = 'il_bibl_entry';

	/**
	 * @return string
	 */
	static function returnDbTableName() {
		return self::TABLE_NAME;
	}

	/**
	 * @return string
	 */
	public function getConnectorContainerName() {
		return self::TABLE_NAME;
	}

	/**
	 * @var
	 *
	 * @con_has_field  true
	 * @con_fieldtype  integer
	 * @con_length     4
	 * @con_is_notnull true
	 * @con_is_primary true
	 * @con_is_unique  true
	 * @con_sequence   true
	 */
	protected $id;
	/**
	 * @var
	 *
	 * @con_has_field  true
	 * @con_fieldtype  integer
	 * @con_length     4
	 */
	protected $data_id;
	/**
	 * @var
	 *
	 * @con_has_field  true
	 * @con_fieldtype  text
	 * @con_length     50
	 * @con_is_notnull true
	 */
	protected $entry_type;

	/**
	 * @param $attributes
	 */
	public function setAttributes($attributes) {
		$this->attributes = $attributes;
	}


	/**
	 * @deprecated REFACTOR nach refactoring von loadAttributes Methoden die getAttributes verwenden entsprechend anpassen. (Statt Array Objekte verwenden)
	 * @return string[]
	 */
	public function getAttributes() {
		return $this->attributes;
	}


	/**
	 * @return integer
	 */
	public function getId() {
		return $this->id;
	}


	/**
	 * @param integer $id
	 */
	public function setId($id) {
		$this->id = $id;
	}


	/**
	 * @return integer
	 */
	public function getDataId() {
		return $this->data_id;
	}


	/**
	 * @param integer $data_id
	 */
	public function setDataId($data_id) {
		$this->data_id = $data_id;
	}

	/**
	 * @return string
	 */
	public function getEntryType() {
		return $this->entry_type;
	}


	/**
	 * @param string $entry_type
	 */
	public function setEntryType($entry_type) {
		$this->entry_type = $entry_type;
	}


}
