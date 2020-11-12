<?php

require_once "Services/ADT/classes/Bridges/class.ilADTDBBridge.php";

abstract class ilADTMultiDBBridge extends ilADTDBBridge
{
    // CRUD
    
    /**
     * Build sub-table name
     *
     * @return string
     */
    protected function getSubTableName()
    {
        // getElementId? => adv_md_values_enum_123
        return $this->getTable() . "_" . $this->getElementId();
    }
    
    public function readRecord(array $a_row)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
                
        $sql = "SELECT " . $this->getElementId() .
            " FROM " . $this->getSubTableName() .
            " WHERE " . $this->buildPrimaryWhere();
        $set = $ilDB->query($sql);
        
        $this->readMultiRecord($set);
    }
    
    /**
     * Import record-rows from sub-table
     *
     * @param object $a_set
     */
    abstract protected function readMultiRecord($a_set);

    public function prepareInsert(array &$a_fields)
    {
        // see afterUpdate()
    }
    
    public function afterInsert()
    {
        $this->afterUpdate();
    }
    
    public function afterUpdate()
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        
        // :TODO: build diff, save difference
        // is this in use? Cannot
        /*
        $ilDB->manipulate("DELETE FROM " . $this->getSubTableName() .
            " WHERE " . $this->buildPrimaryWhere());
        
        foreach ($this->prepareMultiInsert() as $sub_items) {
            $fields = array_merge($this->getPrimary(), $sub_items);
            
            $ilDB->insert($this->getSubTableName(), $fields);
        }
        */
    }
        
    /**
     * Build insert-fields for each "value"
     *
     * @return array
     */
    abstract protected function prepareMultiInsert();
    
    public function afterDelete()
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        // is this in use? Cannot
        /*
        $ilDB->manipulate("DELETE FROM " . $this->getSubTableName() .
            " WHERE " . $this->buildPrimaryWhere());
        */
    }
}
