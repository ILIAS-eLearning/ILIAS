<?php
/**
 * Created by JetBrains PhpStorm.
 * User: root
 * Date: 8/9/12
 * Time: 9:44 AM
 * To change this template use File | Settings | File Templates.
 */
class ilDataCollectionRecordField
{
    private $id;
    private $field;
    private $record;
    private $value;

    function __construct(ilDataCollectionRecord $record, $field){
        $this->record = $record;
        $this->field = $field;
        $this->doRead();
    }

    private function doRead(){
        global $ilDB;
        $query = "SELECT * FROM il_dcl_record_field WHERE field_id = ".$this->field->getId()." AND record_id = ".$this->record->getId();
        $set = $ilDB->query($query);
        $this->id = $ilDB->fetchAssoc($set)['id'];
        if($this->id == Null)
            $this->doCreate();
    }

    private function doCreate(){
        global $ilDB;

        $id = $ilDB->nextId("il_dcl_record_field");
        $query = "INSERT INTO il_dcl_record_field (id, record_id, field_id) VALUES (".$id.", ".$this->record->getId().", ".$this->field->getId().")";
        $ilDB->manipulate($query);
        $this->id = $id;
    }

    function getValue(){
        $this->loadValue();
        return $this->value;
    }

    function hasValue(){
        $this->loadValue();
        return $this->value != Null;
    }

    function setValue($value){
        $type = $this->field->getDatatype()->getId();
        if(!ilDataCollectionDatatype::checkValidity($type, $value))
            throw new ilDataCollectionWrongTypeException();
        else
            $this->value = $value;
    }

    function doUpdate(){
        global $ilDB;
        $datatype = $this->field->getDatatype();
        if($this->hasValue()){
            $ilDB->update("il_dcl_stloc".$datatype->getStorageLocation()."_value", array(
                "value" => array($datatype->getDbType(), $this->value)
            ), array(
                "record_field_id" => array("integer", $this->id)
            ));
        }else{
            $nextId = $ilDB->nextId("il_dcl_stloc".$datatype->getStorageLocation()."_value");
            $query = "INSERT INTO il_dcl_stloc".$datatype->getStorageLocation()."_value
                        (id, record_field_id, value) VALUES (".$nextId.", ".$this->id.", ".$this->value.")";
            $ilDB->manipulate($query);
        }
    }



    private function loadValue(){
        if($this->value == Null){
            global $ilDB;
            $datatype = $this->field->getDatatype();
            $query = "SELECT * FROM il_dcl_stloc".$datatype->getStorageLocation()."_value WHERE record_field_id = ".$this->id;
            $set = $ilDB->query($query);
            $this->value = $ilDB->fetchAssoc($set)['value'];
        }
    }

}
