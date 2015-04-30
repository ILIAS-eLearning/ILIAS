<?php

/**
 * Class ilDataCollectionNReferenceField
 *
 * @author Oskar Truffer <ot@studer-raimann.ch>
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ilDataCollectionNReferenceField extends ilDataCollectionReferenceField {

	/**
	 * @var int
	 */
	protected $max_reference_length = 20;


	public function doUpdate() {
		global $ilDB;

		$values = $this->getValue();
		if (!is_array($values)) {
			$values = array( $values );
		}
		$datatype = $this->field->getDatatype();

		$query = "DELETE FROM il_dcl_stloc" . $datatype->getStorageLocation() . "_value WHERE record_field_id = "
			. $ilDB->quote($this->id, "integer");
		$ilDB->manipulate($query);

		if (!count($values) || $values[0] == 0) {
			return;
		}

		$query = "INSERT INTO il_dcl_stloc" . $datatype->getStorageLocation() . "_value (value, record_field_id, id) VALUES";
		foreach ($values as $value) {
			$next_id = $ilDB->nextId("il_dcl_stloc" . $datatype->getStorageLocation() . "_value");
			$query .= " (" . $ilDB->quote($value, $datatype->getDbType()) . ", " . $ilDB->quote($this->getId(), "integer") . ", "
				. $ilDB->quote($next_id, "integer") . "),";
		}
		$query = substr($query, 0, - 1);
		$ilDB->manipulate($query);
	}


	/**
	 * @return string
	 */
	public function getValue() {
		$this->loadValue();

		return $this->value;
	}


	protected function loadValueSorted() {
		if ($this->value === NULL) {

			global $ilDB;
			$datatype = $this->field->getDatatype();
			$refField = ilDataCollectionCache::getFieldCache($this->getField()->getFieldRef());

			$supported_internal_types = array(
				ilDataCollectionDatatype::INPUTFORMAT_ILIAS_REF,
				ilDataCollectionDatatype::INPUTFORMAT_MOB,
				ilDataCollectionDatatype::INPUTFORMAT_FILE,
			);

			$supported_types = array_merge(array(
				ilDataCollectionDatatype::INPUTFORMAT_TEXT,
				ilDataCollectionDatatype::INPUTFORMAT_NUMBER,
				ilDataCollectionDatatype::INPUTFORMAT_BOOLEAN,
			), $supported_internal_types);
			$datatypeId = $refField->getDatatypeId();
			if (in_array($datatypeId, $supported_types)) {
				if (in_array($datatypeId, $supported_internal_types)) {
					$query = "SELECT stlocOrig.value AS value,  ilias_object.title AS value_ref ";
				} else {
					$query = "SELECT stlocOrig.value AS value,  stlocRef.value AS value_ref ";
				}
				$query .= "FROM il_dcl_stloc" . $datatype->getStorageLocation() . "_value AS stlocOrig  ";

				$query .= " INNER JOIN il_dcl_record_field AS refField ON stlocOrig.value = refField.record_id AND refField.field_id = "
					. $ilDB->quote($refField->getId(), "integer");
				$query .= " INNER JOIN il_dcl_stloc" . $refField->getStorageLocation()
					. "_value AS stlocRef ON stlocRef.record_field_id = refField.id ";
			} else {
				$query = "SELECT stlocOrig.value AS value ";
				$query .= "FROM il_dcl_stloc" . $datatype->getStorageLocation() . "_value AS stlocOrig  ";
			}

			switch ($datatypeId) {
				case ilDataCollectionDatatype::INPUTFORMAT_ILIAS_REF:
					$query .= " INNER JOIN object_reference AS ilias_ref ON ilias_ref.ref_id = stlocRef.value ";
					$query .= " INNER JOIN object_data AS ilias_object ON ilias_object.obj_id = ilias_ref.obj_id ";
					break;
				case ilDataCollectionDatatype::INPUTFORMAT_MOB:
				case ilDataCollectionDatatype::INPUTFORMAT_FILE:
					$query .= " INNER JOIN object_data AS ilias_object ON ilias_object.obj_id =  stlocRef.value ";
					break;
			}
			$query .= " WHERE stlocOrig.record_field_id = " . $ilDB->quote($this->id, "integer");
			if (in_array($datatypeId, $supported_types)) {
				$query .= " ORDER BY value_ref ASC";
			}

			$set = $ilDB->query($query);

			$this->value = array();
			while ($rec = $ilDB->fetchAssoc($set)) {
				$this->value[] = $rec['value'];
			}
		}
	}


	protected function loadValue() {
		if ($this->value === NULL) {
			global $ilDB;
			$datatype = $this->field->getDatatype();
			$query = "SELECT * FROM il_dcl_stloc" . $datatype->getStorageLocation() . "_value WHERE record_field_id = "
				. $ilDB->quote($this->id, "integer");
			$set = $ilDB->query($query);
			$this->value = array();
			while ($rec = $ilDB->fetchAssoc($set)) {
				$this->value[] = $rec['value'];
			}
		}
	}


	/**
	 * @description this funciton is used to in the viewdefinition of a single record.
	 *
	 * @return mixed
	 */
	public function getSingleHTML($options = NULL) {

		// if we are in a record view and the n-ref should be displayed as a link to it's reference
		$values = $this->getValue();
		$record_field = $this;

		if (!$values || !count($values)) {
			return "";
		}

		$tpl = $this->buildTemplate($record_field, $values, $options);

		return $tpl->get();
	}


	/**
	 * @param $record_field
	 * @param $values
	 * @param $options
	 *
	 * @return ilTemplate
	 */
	public function buildTemplate($record_field, $values, $options) {
		$tpl = new ilTemplate("tpl.reference_list.html", true, true, "Modules/DataCollection");
		$tpl->setCurrentBlock("reference_list");
		foreach ($values as $value) {
			$ref_record = ilDataCollectionCache::getRecordCache($value);
			if (!$ref_record->getTableId() || !$record_field->getField() || !$record_field->getField()->getTableId()) {
				//the referenced record_field does not seem to exist.
				$record_field->setValue(0);
				$record_field->doUpdate();
			} else {
				$tpl->setCurrentBlock("reference");
				if (!$options) {
					$tpl->setVariable("CONTENT", $ref_record->getRecordFieldHTML($this->getField()->getFieldRef()));
				} else {
					$tpl->setVariable("CONTENT", $this->getLinkHTML($options['link']['name'], $value));
				}
				$tpl->parseCurrentBlock();
			}
		}
		$tpl->parseCurrentBlock();

		return $tpl;
	}


	/**
	 * @param null $link
	 * @param      $value
	 *
	 * @return string
	 */
	protected function getLinkHTML($link, $value) {
		if ($link == "[" . $this->getField()->getTitle() . "]") {
			$link = NULL;
		}

		return parent::getLinkHTML($link, $value);
	}


	/**
	 * @return array|mixed|string
	 */
	public function getHTML() {
		$values = $this->getValue();
		$record_field = $this;

		if (!$values OR !count($values)) {
			return "";
		}

		$html = "";
		$cut = false;
		$tpl = new ilTemplate("tpl.reference_hover.html", true, true, "Modules/DataCollection");
		$tpl->setCurrentBlock("reference_list");
		foreach ($values as $value) {
			$ref_record = ilDataCollectionCache::getRecordCache($value);
			if (!$ref_record->getTableId() OR !$record_field->getField() OR !$record_field->getField()->getTableId()) {
				//the referenced record_field does not seem to exist.
				$record_field->setValue(NULL);
				$record_field->doUpdate();
			} else {
				if ((strlen($html) < $this->max_reference_length)) {
					$html .= $ref_record->getRecordFieldHTML($this->getField()->getFieldRef()) . ", ";
				} else {
					$cut = true;
				}
				$tpl->setCurrentBlock("reference");
				$tpl->setVariable("CONTENT", $ref_record->getRecordFieldHTML($this->getField()->getFieldRef()));
				$tpl->parseCurrentBlock();
			}
		}
		$html = substr($html, 0, - 2);
		if ($cut) {
			$html .= "...";
		}
		$tpl->setVariable("RECORD_ID", $this->getRecord()->getId());
		$tpl->setVariable("ALL", $html);
		$tpl->parseCurrentBlock();

		return $tpl->get();
	}


	/**
	 * @return int|string
	 */
	public function getExportValue() {
		$values = $this->getValue();
		$names = array();
		foreach ($values as $value) {
			if ($value) {
				$ref_rec = ilDataCollectionCache::getRecordCache($value);
				$names[] = $ref_rec->getRecordField($this->getField()->getFieldRef())->getValue();
			}
		}
		$string = "";
		foreach ($names as $name) {
			$string .= $name . ", ";
		}
		if (!count($names)) {
			return "";
		}
		$string = substr($string, 0, - 2);

		return $string;
	}
}

?>