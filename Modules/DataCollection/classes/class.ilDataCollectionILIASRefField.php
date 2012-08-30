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
class ilDataCollectionILIASRefField extends ilDataCollectionRecordField{

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
		$dclTable = new ilDataCollectionTable($this->getField()->getTableId());
		$this->dcl_obj_id = $dclTable->getCollectionObject()->getId();
	}

	public function getFormInput(){
		global $lng;
		return $lng->txt("dcl_editable_in_table_gui");
	}

	public function getHTML(){
		global $ilCtrl;
		$value = $this->getValue();
		$link = ilLink::_getStaticLink($value);
		$id = ilObject::_lookupObjId($value);
		$html = "<a href='".$link."'>".ilObject::_lookupTitle($id)."</a>";
		return $html;
	}

	public function getExportValue(){
		$value = $this->getValue();
		$link = ilLink::_getStaticLink($value);
		return $link;
	}
}
?>