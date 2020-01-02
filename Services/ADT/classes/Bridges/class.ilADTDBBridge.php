<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * ADT DB bridge base class
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @version $Id$
 * @ingroup ServicesADT
 */
abstract class ilADTDBBridge
{
    protected $adt; // [ilADT]
    protected $table; // [string]
    protected $id; // [string]
    protected $primary = []; // [array]
    
    /**
     * Constructor
     *
     * @param ilADT $a_adt
     * @return self
     */
    public function __construct(ilADT $a_adt)
    {
        $this->setADT($a_adt);
    }
    
    //
    // properties
    //
    
    /**
     * Check if given ADT is valid
     *
     * :TODO: This could be avoided with type-specifc constructors
     * :TODO: bridge base class?
     *
     * @param ilADT $a_adt
     */
    abstract protected function isValidADT(ilADT $a_adt);
    
    /**
     * Set ADT
     *
     * @throws Exception
     * @param ilADT $a_adt
     */
    protected function setADT(ilADT $a_adt)
    {
        if (!$this->isValidADT($a_adt)) {
            throw new Exception('ADTDBBridge Type mismatch.');
        }
        
        $this->adt = $a_adt;
    }
    
    /**
     * Get ADT
     *
     * @return ilADT
     */
    public function getADT()
    {
        return $this->adt;
    }
    
    /**
     * Set table name
     *
     * @param string $a_table
     */
    public function setTable($a_table)
    {
        $this->table = (string) $a_table;
    }
    
    /**
     * Get table name
     *
     * @return string
     */
    public function getTable()
    {
        return $this->table;
    }
    
    /**
     * Set element id (aka DB column[s] [prefix])
     *
     * @param string $a_value
     */
    public function setElementId($a_value)
    {
        $this->id = (string) $a_value;
    }
    
    /**
     * Get element id
     *
     * @return string
     */
    public function getElementId()
    {
        return $this->id;
    }
    
    /**
     * Set primary fields (in MDB2 format)
     *
     * @param array $a_value
     */
    public function setPrimary(array $a_value)
    {
        $this->primary = $a_value;
    }
    
    /**
     * Get primary fields
     *
     * @return array
     */
    public function getPrimary()
    {
        return $this->primary;
    }
    
    /**
     * Convert primary keys array to sql string
     *
     * @see ilADTActiveRecord (:TODO: needed for multi)
     * @return string
     */
    public function buildPrimaryWhere()
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
                
        $sql = array();
        
        foreach ($this->primary as $field => $def) {
            $sql[] = $field . "=" . $ilDB->quote($def[1], $def[0]);
        }
        
        return implode(" AND ", $sql);
    }
    
    
    //
    // CRUD
    //
    
    /**
     * Import DB values to ADT
     *
     * @param array $a_row
     */
    abstract public function readRecord(array $a_row);
    
    /**
     * Prepare ADT values for insert
     *
     * @param array &$a_fields
     */
    abstract public function prepareInsert(array &$a_fields);
    
    /**
     * After insert hook to enable sub-tables
     */
    public function afterInsert()
    {
    }
    
    /**
     * Prepare ADT values for update
     *
     * @see prepareInsert()
     * @param array &$a_fields
     */
    public function prepareUpdate(array &$a_fields)
    {
        $this->prepareInsert($a_fields);
    }
    
    /**
     * After update hook to enable sub-tables
     */
    public function afterUpdate()
    {
    }

    /**
     * After delete hook to enable sub-tables
     */
    public function afterDelete()
    {
    }
}
