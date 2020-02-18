<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * ADT Active Record helper class
 *
 * This class expects a valid primary for all actions!
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @version $Id$
 * @ingroup ServicesADT
 */
class ilADTActiveRecord
{
    protected $properties; // [ilADTGroupDBBridge]
    
    /**
     * Constructor
     *
     * @param ilADTGroupDBBridge $a_properties
     * @return self
     */
    public function __construct(ilADTGroupDBBridge $a_properties)
    {
        $this->properties = $a_properties;
    }

    /**
     * Read record
     *
     * @return boolean
     */
    public function read()
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        
        // reset all group elements
        $this->properties->getADT()->reset();
        
        $sql = "SELECT * FROM " . $this->properties->getTable() .
            " WHERE " . $this->properties->buildPrimaryWhere();
        $set = $ilDB->query($sql);
        if ($ilDB->numRows($set)) {
            $row = $ilDB->fetchAssoc($set);
            $this->properties->readRecord($row);
            return true;
        }
        return false;
    }
    
    /**
     * Create/insert record
     */
    public function create()
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
            
        $fields = $this->properties->getPrimary();
        $this->properties->prepareInsert($fields);
                    
        $ilDB->insert($this->properties->getTable(), $fields);
        
        // enables subtables
        $this->properties->afterInsert();
    }
    
    /**
     * Update record
     */
    public function update()
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
                
        $fields = array();
        $this->properties->prepareUpdate($fields);
        
        // does return affected rows, but will also return 0 for unchanged records
        $ilDB->update($this->properties->getTable(), $fields, $this->properties->getPrimary());
        
        // enables subtables
        $this->properties->afterUpdate();
    }
    
    /**
     * Delete record
     */
    public function delete()
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
            
        $ilDB->manipulate("DELETE FROM " . $this->properties->getTable() .
            " WHERE " . $this->properties->buildPrimaryWhere());

        // enables subtables
        $this->properties->afterDelete();
    }
}
