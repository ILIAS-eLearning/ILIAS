<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once './Modules/DataCollection/classes/class.ilDataCollectionField.php';
include_once './Modules/DataCollection/classes/class.ilDataCollectionDatatype.php';

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
class ilDataCollectionStandardField extends ilDataCollectionField
{
	/*
	 * doRead
	 */
	public function doRead()
	{
		global $ilLog;
		$message = "Standard fields cannot be read from DB";
		ilUtil::sendFailure($message);
		$ilLog->write("[ilDataCollectionStandardField] ".$message);
	}
	
	/*
	 * doCreate
	 */
	public function doCreate()
	{
		global $ilLog;
		$message = "Standard fields cannot be written to DB";
		ilUtil::sendFailure($message);
		$ilLog->write("[ilDataCollectionStandardField] ".$message);
	}
	
	/*
	 * doUpdate
	 */
	public function doUpdate()
	{
		$this->updateVisibility();
		$this->updateFilterability();
		$this->updateExportability();
	}


	/**
	 * @param ilDataCollectionStandardField $original_record
	 */
	public function cloneStructure(ilDataCollectionStandardField $original_record) {
		$this->setEditable($original_record->isEditable());
		$this->setLocked($original_record->getLocked());
		$this->setFilterable($original_record->isFilterable());
		$this->setVisible($original_record->isVisible());
		$this->setOrder($original_record->getOrder());
		$this->setRequired($original_record->getRequired());
		$this->setUnique($original_record->isUnique());
		$this->setExportable($original_record->getExportable());

		$this->doUpdate();
	}
	
	/*
	 * getLocked
	 */
	public function getLocked()
	{
		return true;
	}
	
	/*
	 * _getStandardFieldsAsArray
	 */
	static function _getStandardFieldsAsArray()
	{

		//TODO: this isn't particularly pretty especially as $lng is used in the model. On the long run the standard fields should be refactored into "normal" fields.
		global $lng;
		$stdfields = array(
			array("id"=>"id", "title" => $lng->txt("dcl_id"), "description" => $lng->txt("dcl_id_description"), "datatype_id" => ilDataCollectionDatatype::INPUTFORMAT_NUMBER, "required" => true),
			array("id"=>"create_date", "title" => $lng->txt("dcl_creation_date"), "description" => $lng->txt("dcl_creation_date_description"), "datatype_id" => ilDataCollectionDatatype::INPUTFORMAT_DATETIME, "required" => true),
			array("id"=>"last_update", "title" => $lng->txt("dcl_last_update"), "description" => $lng->txt("dcl_last_update_description"), "datatype_id" => ilDataCollectionDatatype::INPUTFORMAT_DATETIME, "required" => true),
			array("id"=>"owner", "title" => $lng->txt("dcl_owner"), "description" => $lng->txt("dcl_owner_description"), "datatype_id" => ilDataCollectionDatatype::INPUTFORMAT_TEXT, "required" => true),
			array("id"=>"last_edit_by", "title" => $lng->txt("dcl_last_edited_by"), "description" => $lng->txt("dcl_last_edited_by_description"), "datatype_id" => ilDataCollectionDatatype::INPUTFORMAT_TEXT, "required" => true),
		    array('id' => 'comments', 'title' => $lng->txt('dcl_comments'), 'description' => $lng->txt('dcl_comments_description'), 'datatype_id' => ilDataCollectionDatatype::INPUTFORMAT_NONE, 'required' => false),
        );
		return $stdfields;
	}
	
	/*
	 * _getStandardFields
	 */
	static function _getStandardFields($table_id)
	{
		$stdFields = array();
		foreach(self::_getStandardFieldsAsArray() as $array)
		{
			$array["table_id"] = $table_id;
			//$array["datatype_id"] = self::_getDatatypeForId($array["id"]);
			$field = new ilDataCollectionStandardField();
			$field->buildFromDBRecord($array);
			$stdFields[] = $field;
		}
		return $stdFields;
	}

	/*
	 * _isStandardField
	 */
	static function _isStandardField($field_id)
	{
		$return = false;
		foreach(self::_getStandardFieldsAsArray() as $field)
		{
			if($field["id"] == $field_id)
			{
				$return = true;
			}
		}
			
		return $return;
	}

	/**
	 * gives you the datatype id of a specified standard field.
	 * @param $id the id of the standardfield eg. "create_date"
	 */
	public static function _getDatatypeForId($id)
	{
        $datatype = null;
        foreach (self::_getStandardFieldsAsArray() as $fields_data) {
            if ($id == $fields_data['id']) {
                $datatype = $fields_data['datatype_id'];
                break;
            }
        }
        return $datatype;
	}

	/*
	 * isStandardField
	 */
	public function isStandardField()
	{
		return true;
	}
	
	/*
	 * isUnique
	 */
	public function isUnique()
	{
		return false;
	}
}

?>