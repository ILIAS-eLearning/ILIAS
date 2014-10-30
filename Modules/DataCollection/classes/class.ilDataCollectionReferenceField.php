<?php

/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'class.ilDataCollectionRecordField.php';
require_once 'class.ilDataCollectionRecord.php';
require_once 'class.ilDataCollectionField.php';
require_once 'class.ilDataCollectionRecordViewGUI.php';
require_once("./Services/Link/classes/class.ilLink.php");

/**
 * Class ilDataCollectionField
 *
 * @author  Martin Studer <ms@studer-raimann.ch>
 * @author  Marcel Raimann <mr@studer-raimann.ch>
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 * @author  Oskar Truffer <ot@studer-raimann.ch>
 * @version $Id:
 *
 * @ingroup ModulesDataCollection
 */
class ilDataCollectionReferenceField extends ilDataCollectionRecordField {

	/**
	 * @var int
	 */
	protected $dcl_obj_id;
	/**
	 * @var array
	 */
	protected $properties = array();


	/**
	 * @param ilDataCollectionRecord $record
	 * @param ilDataCollectionField  $field
	 */
	public function __construct(ilDataCollectionRecord $record, ilDataCollectionField $field) {
		parent::__construct($record, $field);
		$dclTable = ilDataCollectionCache::getTableCache($this->getField()->getTableId());
		$this->dcl_obj_id = $dclTable->getCollectionObject()->getId();
		$this->properties = $this->field->getProperties();
	}


	/**
	 * @return array|mixed|string
	 */
	public function getHTML() {
		$value = $this->getValue();
		$record_field = $this;

		if (!$value || $value == "-") {
			return "";
		}

		$ref_record = ilDataCollectionCache::getRecordCache($value);
		$html = "";
		if (!$ref_record->getTableId() || !$record_field->getField() || !$record_field->getField()->getTableId()) {
			//the referenced record_field does not seem to exist.
			$record_field->setValue(NULL);
			$record_field->doUpdate();
		} else {
			if ($this->properties[ilDataCollectionField::PROPERTYID_REFERENCE_LINK]) {
				global $ilDB;
				/** @var ilDB $ilDB */
				$ref_record = ilDataCollectionCache::getRecordCache($value);
				$ref_table = $ref_record->getTableId();
				// Checks if a view exists
				$query = "SELECT table_id FROM il_dcl_view WHERE table_id = " . $ref_table . " AND type = " . $ilDB->quote(0, "integer")
					. " AND formtype = " . $ilDB->quote(0, "integer");
				$set = $ilDB->query($query);
				if ($ilDB->numRows($set)) {
					$html = $this->getLinkHTML(NULL, $this->getValue());
				} else {
					$html = $ref_record->getRecordFieldHTML($record_field->getField()->getFieldRef());
				}
			} else {
				$html = $ref_record->getRecordFieldHTML($record_field->getField()->getFieldRef());
			}
		}

		return $html;
	}


	/**
	 * @param null $link_name
	 * @param      $value
	 *
	 * @return string
	 */
	protected function getLinkHTML($link_name = NULL, $value) {
		global $ilCtrl;

		if (!$value || $value == "-") {
			return "";
		}
		$record_field = $this;
		$ref_record = ilDataCollectionCache::getRecordCache($value);
		if (!$link_name) {
			$link_name = $ref_record->getRecordFieldHTML($record_field->getField()->getFieldRef());
		}
		$ilCtrl->setParameterByClass("ildatacollectionrecordviewgui", "record_id", $ref_record->getId());
		$html = "<a href='" . $ilCtrl->getLinkTargetByClass("ilDataCollectionRecordViewGUI", "renderRecord") . "&disable_paging=1'>" . $link_name
			. "</a>";

		return $html;
	}


	/**
	 * @return int|string
	 */
	public function getExportValue() {
		if ($this->getValue()) {
			$ref_rec = ilDataCollectionCache::getRecordCache($this->getValue());

			return $ref_rec->getRecordField($this->getField()->getFieldRef())->getExportValue();
		} else {
			return "";
		}
	}
}

?>