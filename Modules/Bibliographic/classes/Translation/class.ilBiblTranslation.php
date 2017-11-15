<?php
/**
 * Class ilBiblTranslation
 *
 * @author: Benjamin Seglias   <bs@studer-raimann.ch>
 */

class ilBiblTranslation {

	const TABLE_NAME = 'il_bibl_translation';

	/**
	 * @return string
	 * @deprecated
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
	 * @con_is_notnull true
	 * @con_is_unique  true
	 */
	protected $field_id;
	/**
	 * @var
	 *
	 * @con_has_field  true
	 * @con_fieldtype  text
	 * @con_length     2
	 * @con_is_notnull true
	 */
	protected $language_key;
	/**
	 * @var
	 *
	 * @con_has_field  true
	 * @con_fieldtype  text
	 * @con_length     8
	 * @con_is_notnull true
	 */
	protected $translation;


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
	public function getFieldId() {
		return $this->field_id;
	}


	/**
	 * @param integer $field_id
	 */
	public function setFieldId($field_id) {
		$this->field_id = $field_id;
	}


	/**
	 * @return string
	 */
	public function getLanguageKey() {
		return $this->language_key;
	}


	/**
	 * @param string $language_key
	 */
	public function setLanguageKey($language_key) {
		$this->language_key = $language_key;
	}


	/**
	 * @return string
	 */
	public function getTranslation() {
		return $this->translation;
	}


	/**
	 * @param string $translation
	 */
	public function setTranslation($translation) {
		$this->translation = $translation;
	}
}