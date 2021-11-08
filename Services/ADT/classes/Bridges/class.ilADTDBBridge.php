<?php declare(strict_types=1);
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * ADT DB bridge base class
 * @author  Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @ingroup ServicesADT
 */
abstract class ilADTDBBridge
{
    protected ilADT $adt;
    protected string $table;
    protected string $id;
    protected array $primary = [];

    protected ilDBInterface $db;

    /**
     * Constructor
     * @param ilADT $a_adt
     */
    public function __construct(ilADT $a_adt)
    {
        global $DIC;

        $this->db = $DIC->database();
        $this->setADT($a_adt);
    }

    abstract protected function isValidADT(ilADT $a_adt) : bool;

    protected function setADT(ilADT $a_adt) : void
    {
        if (!$this->isValidADT($a_adt)) {
            throw new \InvalidArgumentException('ADTDBBridge Type mismatch.');
        }
        $this->adt = $a_adt;
    }

    public function getADT() : ilADT
    {
        return $this->adt;
    }

    public function setTable(string $a_table) : void
    {
        $this->table = $a_table;
    }

    public function getTable() : ?string
    {
        return $this->table;
    }

    /**
     * Set element id (aka DB column[s] [prefix])
     * @param string $a_value
     */
    public function setElementId(string $a_value) : void
    {
        $this->id = $a_value;
    }

    /**
     * Get element id
     * @return string | null
     */
    public function getElementId() : ?string
    {
        return $this->id;
    }

    /**
     * Set primary fields (in MDB2 format)
     * @param array $a_value
     */
    public function setPrimary(array $a_value) : void
    {
        $this->primary = $a_value;
    }

    /**
     * Get primary fields
     * @return array
     */
    public function getPrimary() : array
    {
        return $this->primary;
    }

    /**
     * Convert primary keys array to sql string
     * @return string
     * @see ilADTActiveRecord (:TODO: needed for multi)
     */
    public function buildPrimaryWhere() : string
    {
        $sql = [];
        foreach ($this->primary as $field => $def) {
            $sql[] = $field . "=" . $this->db->quote($def[1], $def[0]);
        }
        return implode(" AND ", $sql);
    }

    /**
     * Import DB values to ADT
     * @param array $a_row
     */
    abstract public function readRecord(array $a_row) : void;

    /**
     * Prepare ADT values for insert
     * @param array &$a_fields
     */
    abstract public function prepareInsert(array &$a_fields) : void;

    /**
     * After insert hook to enable sub-tables
     */
    public function afterInsert() : void
    {
    }

    public function prepareUpdate(array &$a_fields) : void
    {
        $this->prepareInsert($a_fields);
    }

    /**
     * After update hook to enable sub-tables
     */
    public function afterUpdate() : void
    {
    }

    /**
     * After delete hook to enable sub-tables
     */
    public function afterDelete() : void
    {
    }

    /**
     * true if table storage relies on the default 'value' column
     * @return bool
     */
    public function supportsDefaultValueColumn() : bool
    {
        return true;
    }
}
