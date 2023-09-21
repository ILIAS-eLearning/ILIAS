<?php

declare(strict_types=1);

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


class ilADTIntegerSearchBridgeSingle extends ilADTSearchBridgeSingle
{
    protected function isValidADTDefinition(ilADTDefinition $a_adt_def): bool
    {
        return ($a_adt_def instanceof ilADTIntegerDefinition);
    }

    // table2gui / filter

    public function loadFilter(): void
    {
        $value = $this->readFilter();
        if ($value !== null) {
            $this->getADT()->setNumber($value);
        }
    }

    // form

    public function addToForm(): void
    {
        $def = $this->getADT()->getCopyOfDefinition();

        $number = new ilNumberInputGUI($this->getTitle(), $this->getElementId());
        $number->setSize(10);

        $min = $def->getMin();
        // do not set min value for search
        if ($min !== null) {
            //$number->setMinValue($min);
        }
        $max = $def->getMax();
        // do not set min value for search
        if ($max !== null) {
            #$number->setMaxValue($max);
            #$length = strlen((string) $max);
            #$number->setSize($length);
            #$number->setMaxLength($length);
        }
        $number->setValue((string) $this->getADT()->getNumber());
        $this->addToParentElement($number);
    }

    public function importFromPost(array $a_post = null): bool
    {
        $post = $this->extractPostValues($a_post);

        if ($post && $this->shouldBeImportedFromPost($post)) {
            $item = $this->getForm()->getItemByPostVar($this->getElementId());
            $item->setValue($post);
            $this->getADT()->setNumber($post);
        } else {
            $this->getADT()->setNumber();
        }
        return true;
    }

    // db

    public function getSQLCondition(string $a_element_id, int $mode = self::SQL_LIKE, array $quotedWords = []): string
    {
        if (!$this->isNull() && $this->isValid()) {
            return $a_element_id . " = " . $this->db->quote($this->getADT()->getNumber(), "integer");
        }
        return '';
    }

    public function isInCondition(ilADT $a_adt): bool
    {
        if ($this->getADT()->getCopyOfDefinition()->isComparableTo($a_adt)) {
            return $this->getADT()->equals($a_adt);
        }
        return false;
    }

    //  import/export

    public function getSerializedValue(): string
    {
        if (!$this->isNull() && $this->isValid()) {
            return serialize(array($this->getADT()->getNumber()));
        }
        return '';
    }

    public function setSerializedValue(string $a_value): void
    {
        $a_value = unserialize($a_value);
        if (is_array($a_value)) {
            $this->getADT()->setNumber($a_value[0]);
        }
    }
}
