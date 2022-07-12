<?php declare(strict_types=1);
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * ADT DB bridge base class
 * @author  Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @version $Id$
 * @ingroup ServicesADT
 */
abstract class ilADTActiveRecordBridge
{
    protected ilADT $adt;
    protected ?string $id;
    protected ?string $table;
    protected array $primary = [];

    public function __construct(ilADT $a_adt)
    {
        $this->setADT($a_adt);
    }

    abstract protected function isValidADT(ilADT $a_adt) : bool;

    /**
     * Set ADT
     * @param ilADT $a_adt
     * @throws InvalidArgumentException
     */
    protected function setADT(ilADT $a_adt) : void
    {
        if (!$this->isValidADT($a_adt)) {
            throw new \InvalidArgumentException('ADTActiveRecordBridge Type mismatch.');
        }
        $this->adt = $a_adt;
    }

    /**
     * Get ADT
     * @return ilADT
     */
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
     * @param string[] $a_value
     */
    public function setPrimary(array $a_value) : void
    {
        $this->primary = $a_value;
    }

    /**
     * Get primary fields
     * @return string[]
     */
    public function getPrimary() : array
    {
        return $this->primary;
    }

    /**
     * Convert ADT to active record fields
     * @return array
     */
    abstract public function getActiveRecordFields() : array;

    /**
     * Get field value
     * @param string $a_field_name
     * @return
     */
    abstract public function getFieldValue(string $a_field_name);

    /**
     * Set field value
     * @param string $a_field_name
     * @param string|int       $a_field_value
     */
    abstract public function setFieldValue(string $a_field_name, $a_field_value) : void;
}
