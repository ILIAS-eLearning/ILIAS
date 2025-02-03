<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

declare(strict_types=1);
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

    abstract protected function isValidADT(ilADT $a_adt): bool;

    /**
     * Set ADT
     * @param ilADT $a_adt
     * @throws InvalidArgumentException
     */
    protected function setADT(ilADT $a_adt): void
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
    public function getADT(): ilADT
    {
        return $this->adt;
    }

    public function setTable(string $a_table): void
    {
        $this->table = $a_table;
    }

    public function getTable(): ?string
    {
        return $this->table;
    }

    /**
     * Set element id (aka DB column[s] [prefix])
     * @param string $a_value
     */
    public function setElementId(string $a_value): void
    {
        $this->id = $a_value;
    }

    /**
     * Get element id
     * @return string | null
     */
    public function getElementId(): ?string
    {
        return $this->id;
    }

    /**
     * Set primary fields (in MDB2 format)
     * @param string[] $a_value
     */
    public function setPrimary(array $a_value): void
    {
        $this->primary = $a_value;
    }

    /**
     * Get primary fields
     * @return string[]
     */
    public function getPrimary(): array
    {
        return $this->primary;
    }

    /**
     * Convert ADT to active record fields
     * @return array
     */
    abstract public function getActiveRecordFields(): array;

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
    abstract public function setFieldValue(string $a_field_name, $a_field_value): void;
}
