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

class ilADTMultiEnumDBBridge extends ilADTDBBridge
{
    public const TABLE_NAME = 'adv_md_values_enum';
    public const SEPARATOR = "~|~";

    protected bool $fake_single = false;

    public function getTable(): ?string
    {
        return self::TABLE_NAME;
    }

    protected function isValidADT(ilADT $a_adt): bool
    {
        return ($a_adt instanceof ilADTMultiEnum);
    }

    public function setFakeSingle(bool $a_status): void
    {
        $this->fake_single = $a_status;
    }

    protected function doSingleFake(): bool
    {
        return $this->fake_single;
    }

    public function readRecord(array $a_row): void
    {
        if (isset($a_row[$this->getElementId()])) {
            $this->getADT()->addSelection((int) $a_row[$this->getElementId()]);
        }
    }

    public function afterInsert(): void
    {
        $this->afterUpdate();
    }

    public function afterUpdate(): void
    {
        $this->deleteIndices();
        $this->insertIndices();
    }

    public function prepareInsert(array &$a_fields): void
    {
        $a_fields = [];
    }

    protected function deleteIndices(): void
    {
        $this->db->query(
            'delete from ' . $this->getTable() . ' ' .
            'where ' . $this->buildPrimaryWhere()
        );
    }

    protected function insertIndices(): void
    {
        foreach ((array) $this->getADT()->getSelections() as $index) {
            $fields = $this->getPrimary();
            $fields['value_index'] = [ilDBConstants::T_INTEGER, $index];
            $num_row = $this->db->insert($this->getTable(), $fields);
        }
    }

    public function supportsDefaultValueColumn(): bool
    {
        return false;
    }
}
