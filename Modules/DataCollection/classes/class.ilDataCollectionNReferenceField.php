<?php
/**
 * Created by JetBrains PhpStorm.
 * @author: Oskar Truffer <ot@studer-raimann.ch>
 * Date: 21/02/13
 * Time: 3:16 PM
 * To change this template use File | Settings | File Templates.
 */
class ilDataCollectionNReferenceField extends ilDataCollectionReferenceField{

    private $max_reference_length = 20;
    /*
     * doUpdate
     */
    public function doUpdate()
    {
        global $ilDB;

        $values = $this->getValue();
        if(!is_array($values))
            $values = array($values);
        $datatype = $this->field->getDatatype();

        $query = "DELETE FROM il_dcl_stloc".$datatype->getStorageLocation()."_value WHERE record_field_id = ".$ilDB->quote($this->id, "integer");
        $ilDB->manipulate($query);

        if(!count($values)|| $values[0] == 0)
            return;

        $query = "INSERT INTO il_dcl_stloc".$datatype->getStorageLocation()."_value (value, record_field_id, id) VALUES";
        foreach($values as $value){
            $next_id = $ilDB->nextId("il_dcl_stloc".$datatype->getStorageLocation()."_value");
            $query .= " (".$ilDB->quote($value, $datatype->getDbType()).", ".$ilDB->quote($this->getId(), "integer").", ".$ilDB->quote($next_id, "integer")."),";
        }
        $query = substr($query, 0, -1);
        $ilDB->manipulate($query);
    }

    /*
     * loadValue
     */
    protected function loadValue()
    {
        if($this->value === NULL)
        {
            global $ilDB;
            $datatype = $this->field->getDatatype();
            $query = "SELECT * FROM il_dcl_stloc".$datatype->getStorageLocation()."_value WHERE record_field_id = ".$ilDB->quote($this->id, "integer");
            $set = $ilDB->query($query);
            while($rec = $ilDB->fetchAssoc($set))
                $this->value[] = $rec['value'];
        }
    }

    /**
     * this funciton is used to in the viewdefinition of a single record.
     * @return mixed
     */
    public function getSingleHTML(){
        $values = $this->getValue();
        $record_field = $this;

        if(!$values || !count($values)){
            return "-";
        }

        $tpl = new ilTemplate("tpl.reference_list.html",true, true, "Modules/DataCollection");
        $tpl->setCurrentBlock("reference_list");
        foreach($values as $value){
            $ref_record = ilDataCollectionCache::getRecordCache($value);
            if(!$ref_record->getTableId() || !$record_field->getField() || !$record_field->getField()->getTableId()){
                //the referenced record_field does not seem to exist.
                $record_field->setValue(0);
                $record_field->doUpdate();
            }else{
                $tpl->setCurrentBlock("reference");
                $tpl->setVariable("CONTENT", $ref_record->getRecordFieldHTML($this->getField()->getFieldRef()));
                $tpl->parseCurrentBlock();
            }
        }

        $tpl->parseCurrentBlock();

        return $tpl->get();
    }

    /*
	 * getHTML
	 *
	 * @param array $options
	 * @return array
	 */
    public function getHTML(array $options = array()){
        global $ilCtrl;

        $values = $this->getValue();
        $record_field = $this;

        if(!$values || !count($values)){
            return "-";
        }

        $html = "";
        $tpl = new ilTemplate("tpl.reference_hover.html",true, true, "Modules/DataCollection");
        $tpl->setCurrentBlock("reference_list");
        foreach($values as $value){
            $ref_record = ilDataCollectionCache::getRecordCache($value);
            if(!$ref_record->getTableId() || !$record_field->getField() || !$record_field->getField()->getTableId()){
                //the referenced record_field does not seem to exist.
                $record_field->setValue(NULL);
                $record_field->doUpdate();
            }else{
                if((strlen($html) < $this->max_reference_length))
                    $html .= $ref_record->getRecordFieldHTML($this->getField()->getFieldRef()).", ";
                else
                    $cut = true;
                $tpl->setCurrentBlock("reference");
                $tpl->setVariable("CONTENT", $ref_record->getRecordFieldHTML($this->getField()->getFieldRef()));
                $tpl->parseCurrentBlock();
            }
        }
        $html = substr($html, 0, -2);
        if($cut){
            $html .= "...";
        }
        $tpl->setVariable("RECORD_ID", $this->getRecord()->getId());
        $tpl->setVariable("ALL", $html);
        $tpl->parseCurrentBlock();

        return $tpl->get();
    }

    /*
    * getExportValue
    */
    public function getExportValue()
    {
        $values = $this->getValue();
        $names = array();
        foreach($values as $value){
            if($value){
                $ref_rec = ilDataCollectionCache::getRecordCache($value);
                $names[] = $ref_rec->getRecordField($this->getField()->getFieldRef())->getValue();
            }
        }
        $string = "";
        foreach($names as $name){
            $string.=$name.", ";
        }
        if(!count($names))
            return "";
        $string = substr($string, 0, -2);
        return $string;
    }
}

?>