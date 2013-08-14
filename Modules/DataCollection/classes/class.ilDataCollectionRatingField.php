<?php

/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once 'class.ilDataCollectionRecordField.php';
require_once("./Services/Rating/classes/class.ilRatingGUI.php");

/**
 * Class ilDataCollectionField
 *
 * @author Martin Studer <ms@studer-raimann.ch>
 * @author Marcel Raimann <mr@studer-raimann.ch>
 * @author Fabian Schmid <fs@studer-raimann.ch>
 * @author Oskar Truffer <ot@studer-raimann.ch>
 * @version $Id:
 *
 * @ingroup ModulesDataCollection
 */
class ilDataCollectionRatingField extends ilDataCollectionRecordField{

	/**
	 * @var bool
	 */
	protected $rated;

	/**
	 * @var int
	 */
	protected $dcl_obj_id;

	public function __construct(ilDataCollectionRecord $record, ilDataCollectionField $field){
		parent::__construct($record, $field);
		$dclTable = ilDataCollectionCache::getTableCache($this->getField()->getTableId());
		$this->dcl_obj_id = $dclTable->getCollectionObject()->getId();
	}

	/**
	 * override the loadValue.
	 */
	protected function loadValue(){
		// explicitly do nothing. we don't have to load the value as it is saved somewhere else.
	}

	public function setValue($value){
		// explicitly do nothing. the value is handled via the model and gui of ilRating.
	}

	public function doUpdate(){
		// explicitly do nothing. the value is handled via the model and gui of ilRating.
	}

	public function doRead(){
		// explicitly do nothing. the value is handled via the model and gui of ilRating.
	}

	public function getFormInput(){
		global $lng;
		return $lng->txt("dcl_editable_in_table_gui");
	}

	public function getHTML(){
		global $ilCtrl;
		$rgui = new ilRatingGUI();
		$rgui->setObject($this->getRecord()->getId(), "dcl_record",
		$this->getField()->getId(), "dcl_field");
		$ilCtrl->setParameterByClass("ilratinggui", "field_id", $this->getField()->getId());
		$ilCtrl->setParameterByClass("ilratinggui", "record_id", $this->getRecord()->getId());
        $html = $rgui->getHTML();

		return $html;
	}

	public function getExportValue(){
        $val = ilRating::getOverallRatingForObject($this->getRecord()->getId(), "dcl_record",
            $this->getField()->getId(), "dcl_field");
        return  round($val["avg"],1)." (".$val["cnt"].")";
	}

	public function getValue(){
		return ilRating::getOverallRatingForObject($this->getRecord()->getId(), "dcl_record",
			$this->getField()->getId(), "dcl_field");
	}

	/**
	  * delete
	  */
	public function delete()
	{
		global $ilDB;

		$ilDB->manipulate("DELETE FROM il_rating WHERE ".
			"obj_id = ".$ilDB->quote((int) $this->getRecord()->getId(), "integer")." AND ".
			"obj_type = ".$ilDB->quote("dcl_record", "text")." AND ".
			"sub_obj_id = ".$ilDB->quote((int) $this->getField()->getId(), "integer")." AND ".
			$ilDB->equals("sub_obj_type", "dcl_field", "text", true));

		$query2 = "DELETE FROM il_dcl_record_field WHERE id = ".$ilDB->quote($this->getId(), "integer");
		$ilDB->manipulate($query2);
	}
}
?>