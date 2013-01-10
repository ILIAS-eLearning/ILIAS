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
 * @author Martin Studer <ms@studer-raimann.ch>
 * @author Marcel Raimann <mr@studer-raimann.ch>
 * @author Fabian Schmid <fs@studer-raimann.ch>
 * @author Oskar Truffer <ot@studer-raimann.ch>
 * @version $Id:
 *
 * @ingroup ModulesDataCollection
 */
class ilDataCollectionReferenceField extends ilDataCollectionRecordField{

	/**
	 * @var int
	 */
	protected $dcl_obj_id;



	public function __construct(ilDataCollectionRecord $record, ilDataCollectionField $field){
		parent::__construct($record, $field);
		$dclTable = ilDataCollectionCache::getTableCache($this->getField()->getTableId());
		$this->dcl_obj_id = $dclTable->getCollectionObject()->getId();
	}

    /*
	 * getHTML
	 *
	 * @param array $options
	 * @return array
	 */
	public function getHTML(array $options = array()){
        global $ilCtrl;

		$value = $this->getValue();
        $record_field = $this;

        if(!$value || $value == "-"){
            return "";
        }

        $ref_record = ilDataCollectionCache::getRecordCache($value);
        if(!$ref_record->getTableId() || !$record_field->getField() || !$record_field->getField()->getTableId()){
            //the referenced record_field does not seem to exist.
            $html = "-";
            $record_field->setValue(NULL);
            $record_field->doUpdate();
        }
        else
        {
            $record = $record_field->getRecord();

            if($options['link']['display']) {
                $html = $this->getLinkHTML($options['link']['name']);
            } else {
                $html = $ref_record->getRecordFieldHTML($record_field->getField()->getFieldRef());
            }
        }


		return $html;
	}

    /*
      * get Link
      *
      * @param  string    $link_name
      */
    public function getLinkHTML($link_name = NULL) {
        global $ilCtrl;

        $value = $this->getValue();

        if(!$value || $value == "-"){
            return "";
        }

        $record_field = $this;
        $ref_record = ilDataCollectionCache::getRecordCache($value);

        $objRefField = ilDataCollectionCache::getFieldCache($record_field->getField()->getFieldRef());
        $objRefTable = ilDataCollectionCache::getTableCache($objRefField->getTableId());

        if(!$link_name) {
          $link_name =  $ref_record->getRecordFieldHTML($record_field->getField()->getFieldRef());
        }

        $ilCtrl->setParameterByClass("ildatacollectionrecordviewgui", "record_id", $ref_record->getId());

        $objDataCollectionRecordViewGUI = new ilDataCollectionRecordViewGUI($objRefTable->getCollectionObject());

        $html = "<a href='". $ilCtrl->getLinkTarget($objDataCollectionRecordViewGUI,"renderRecord")."'>".$link_name."</a>";


        return $html;
    }


}
?>