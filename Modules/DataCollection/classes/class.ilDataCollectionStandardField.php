<?php

include_once './Modules/DataCollection/classes/class.ilDataCollectionField.php';
include_once './Modules/DataCollection/classes/class.ilDataCollectionDatatype.php';

class ilDataCollectionStandardField extends ilDataCollectionField
{

    static function _getStandardFieldsAsArray(){
        $stdfields = array(
            array("id"=>"id", "title" => "id", "Description" => "The internal ID", "datatype_id" => ilDataCollectionDatatype::INPUTFORMAT_NUMBER, "required" => true),
            array("id"=>"table_id", "title" => "Table id", "description" => "The internal ID of the table", "datatype_id" => ilDataCollectionDatatype::INPUTFORMAT_NUMBER, "required" => true),
            array("id"=>"create_date", "title" => "Creation Date", "description" => "The date this record was created", "datatype_id" => ilDataCollectionDatatype::INPUTFORMAT_DATETIME, "required" => true),
            array("id"=>"last_update", "title" => "Last Update", "description" => "The last time this record was updated", "datatype_id" => ilDataCollectionDatatype::INPUTFORMAT_DATETIME, "required" => true),
            array("id"=>"owner", "title" => "Owner", "description" => "The owner of this record", "datatype_id" => ilDataCollectionDatatype::INPUTFORMAT_TEXT, "required" => true),
			array("id"=>"last_edit_by", "title" => "Last edited by", "description" => "The user who did the last edit on this record", "datatype_id" => ilDataCollectionDatatype::INPUTFORMAT_TEXT, "required" => true)
        );
        return $stdfields;
    }

    static function _getStandardFields($table_id){
        $stdFields = array();
        foreach(self::_getStandardFieldsAsArray() as $array){
            $array["table_id"] = $table_id;
            $field = new ilDataCollectionStandardField();
            $field->buildFromDBRecord($array);
            array_push($stdFields, $field);
        }
        return $stdFields;
    }


    static function _isStandardField($field_id){
        $return = false;
        foreach(self::_getStandardFieldsAsArray() as $field)
            if($field["id"] == $field_id)
                $return = true;
        return $return;
    }

    public function isStandardField(){
        return true;
    }

	public function isUnique(){
		return false;
	}


    function doRead(){
        global $ilLog;
        $message = "Standard fields cannot be read from DB";
        ilUtil::sendFailure($message);
        $ilLog->write("[ilDataCollectionStandardField] ".$message);
    }

    function doCreate(){
        global $ilLog;
        $message = "Standard fields cannot be written to DB";
        ilUtil::sendFailure($message);
        $ilLog->write("[ilDataCollectionStandardField] ".$message);
    }

    function doUpdate(){
        $this->updateVisibility();
    }
}
