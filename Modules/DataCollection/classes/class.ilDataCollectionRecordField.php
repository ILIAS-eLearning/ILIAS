<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once './Modules/DataCollection/exceptions/class.ilDataCollectionInputException.php';

/**
 * Class ilDataCollectionField
 *
 * @author  Martin Studer <ms@studer-raimann.ch>
 * @author  Marcel Raimann <mr@studer-raimann.ch>
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 * @author  Oskar Truffer <ot@studer-raimann.ch>
 * @author  Stefan Wanzenried <sw@studer-raimann.ch>
 * @version $Id:
 *
 * @ingroup ModulesDataCollection
 */
class ilDataCollectionRecordField {

	/**
	 * @var int
	 */
	protected $id;
	/**
	 * @var ilDataCollectionField
	 */
	protected $field;
	/**
	 * @var ilDataCollectionRecord
	 */
	protected $record;
	/**
	 * @var string
	 */
	protected $value;
	/**
	 * @var ilLanguage
	 */
	protected $lng;
	/**
	 * @var ilObjUser
	 */
	protected $user;
	/**
	 * @var ilCtrl
	 */
	protected $ctrl;
	/**
	 * @var ilDB
	 */
	protected $db;


	/**
	 * @param ilDataCollectionRecord $record
	 * @param ilDataCollectionField  $field
	 */
	public function __construct(ilDataCollectionRecord $record, ilDataCollectionField $field) {
		global $lng, $ilCtrl, $ilUser, $ilDB;
		$this->record = $record;
		$this->field = $field;
		$this->lng = $lng;
		$this->ctrl = $ilCtrl;
		$this->user = $ilUser;
		$this->db = $ilDB;
		$this->doRead();
	}


	/**
	 * Read object data from database
	 */
	protected function doRead() {
		$query = "SELECT * FROM il_dcl_record_field WHERE field_id = " . $this->db->quote($this->field->getId(), "integer") . " AND record_id = "
			. $this->db->quote($this->record->getId(), "integer");
		$set = $this->db->query($query);
		$rec = $this->db->fetchAssoc($set);
		$this->id = $rec['id'];

		if ($this->id == NULL) {
			$this->doCreate();
		}
		$this->loadValue();
	}


	/**
	 * Create object in database
	 */
	protected function doCreate() {
		$id = $this->db->nextId("il_dcl_record_field");
		$query = "INSERT INTO il_dcl_record_field (id, record_id, field_id) VALUES (" . $this->db->quote($id, "integer") . ", "
			. $this->db->quote($this->record->getId(), "integer") . ", " . $this->db->quote($this->field->getId(), "text") . ")";
		$this->db->manipulate($query);
		$this->id = $id;
	}


	/**
	 * Update object in database
	 */
	public function doUpdate() {
		//$this->loadValue(); //Removed Mantis #0011799
		$datatype = $this->field->getDatatype();
		$query = "DELETE FROM il_dcl_stloc" . $datatype->getStorageLocation() . "_value WHERE record_field_id = "
			. $this->db->quote($this->id, "integer");
		$this->db->manipulate($query);
		$next_id = $this->db->nextId("il_dcl_stloc" . $datatype->getStorageLocation() . "_value");

		// This is a workaround to ensure that date values in stloc3 are never stored as NULL, which is not allowed
		if ($datatype->getStorageLocation() == 3 && (is_null($this->value) || empty($this->value))) {
			$this->value = '0000-00-00 00:00:00';
		}

		$this->db->insert("il_dcl_stloc" . $datatype->getStorageLocation() . "_value", array(
				"value" => array( $datatype->getDbType(), $this->value ),
				"record_field_id " => array( "integer", $this->id ),
				"id" => array( "integer", $next_id )
			));
	}


	/**
	 * Delete record field in database
	 */
	public function delete() {
		$datatype = $this->field->getDatatype();
		$query = "DELETE FROM il_dcl_stloc" . $datatype->getStorageLocation() . "_value WHERE record_field_id = "
			. $this->db->quote($this->id, "integer");
		$this->db->manipulate($query);

		$query2 = "DELETE FROM il_dcl_record_field WHERE id = " . $this->db->quote($this->id, "integer");
		$this->db->manipulate($query2);
	}


	/**
	 * @return string
	 */
	public function getValue() {
		$this->loadValue();

		return $this->value;
	}


	/**
	 * Set value for record field
	 *
	 * @param mixed $value
	 * @param bool  $omit_parsing If true, does not parse the value and stores it in the given format
	 */
	public function setValue($value, $omit_parsing = false) {
		$this->loadValue();
		if (!$omit_parsing) {
			$tmp = $this->field->getDatatype()->parseValue($value, $this);
			$old = $this->value;
			//if parse value fails keep the old value
			if ($tmp !== false) {
				$this->value = $tmp;
				//delete old file from filesystem
				// TODO Does not belong here, create separate class ilDataCollectionFileField and overwrite setValue method
				if ($old && $this->field->getDatatypeId() == ilDataCollectionDatatype::INPUTFORMAT_FILE) {
					$this->record->deleteFile($old);
				}
			}
		} else {
			$this->value = $value;
		}
	}


	/**
	 * @return mixed
	 */
	public function getFormInput() {
		$datatype = $this->field->getDatatype();

		return $datatype->parseFormInput($this->getValue(), $this);
	}


	/**
	 * @return int|string
	 */
	public function getExportValue() {
		$datatype = $this->field->getDatatype();

		return $datatype->parseExportValue($this->getValue());
	}


	/**
	 * @return mixed used for the sorting.
	 */
	public function getPlainText() {
		return $this->getExportValue();
	}


	/**
	 * @return string
	 */
	public function getHTML($link = true) {
		$datatype = $this->field->getDatatype();

		return $datatype->parseHTML($this->getValue(), $this, $link);
	}


	/**
	 * @return string
	 * @description This method is used in the view definition of a single record (detail view)
	 */
	public function getSingleHTML() {
		return $this->getHTML(false);
	}


	/**
	 * Load the value
	 */
	protected function loadValue() {
		if ($this->value === NULL) {
			$datatype = $this->field->getDatatype();
			$query = "SELECT * FROM il_dcl_stloc" . $datatype->getStorageLocation() . "_value WHERE record_field_id = "
				. $this->db->quote($this->id, "integer");
			$set = $this->db->query($query);
			$rec = $this->db->fetchAssoc($set);
			$this->value = $rec['value'];
		}
	}


	/**
	 * @return ilDataCollectionField
	 */
	public function getField() {
		return $this->field;
	}


	/**
	 * @return int
	 */
	public function getId() {
		return $this->id;
	}


	/**
	 * @return ilDataCollectionRecord
	 */
	public function getRecord() {
		return $this->record;
	}
}

?>
