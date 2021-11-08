<?php declare(strict_types=1);
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * ADT Active Record helper class
 * This class expects a valid primary for all actions!
 * @author  Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @ingroup ServicesADT
 */
class ilADTActiveRecord
{
    protected ilADTGroupDBBridge $properties;

    protected ilDBInterface $db;

    /**
     * Constructor
     * @param ilADTGroupDBBridge $a_properties
     */
    public function __construct(ilADTGroupDBBridge $a_properties)
    {
        global $DIC;

        $this->db = $DIC->database();
        $this->properties = $a_properties;
    }

    /**
     * Read record
     * @return bool
     */
    public function read() : bool
    {
        // reset all group elements
        $this->properties->getADT()->reset();

        $sql = "SELECT * FROM " . $this->properties->getTable() .
            " WHERE " . $this->properties->buildPrimaryWhere();
        $set = $this->db->query($sql);
        if ($this->db->numRows($set)) {
            $row = $this->db->fetchAssoc($set);
            $this->properties->readRecord($row);
            return true;
        }
        return false;
    }

    public function create() : void
    {
        $fields = $this->properties->getPrimary();
        $this->properties->prepareInsert($fields);
        $this->db->insert($this->properties->getTable(), $fields);
        $this->properties->afterInsert();
    }

    public function update() : void
    {
        $fields = array();
        $this->properties->prepareUpdate($fields);
        $this->db->update($this->properties->getTable(), $fields, $this->properties->getPrimary());
        $this->properties->afterUpdate();
    }

    public function delete() : void
    {
        $this->db->manipulate("DELETE FROM " . $this->properties->getTable() .
            " WHERE " . $this->properties->buildPrimaryWhere());
        $this->properties->afterDelete();
    }
}
