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

class ilADTEnumDefinition extends ilADTDefinition
{
    protected array $options = [];
    protected bool $numeric; // [bool]

    public function getType(): string
    {
        return "Enum";
    }

    // default

    public function reset(): void
    {
        parent::reset();

        $this->options = array();
        $this->setNumeric(true);
    }

    // properties

    public function isNumeric(): bool
    {
        return $this->numeric;
    }

    public function setNumeric(bool $a_value): void
    {
        $this->numeric = $a_value;
    }

    public function getOptions(): array
    {
        return $this->options;
    }

    public function setOptions(array $a_values)
    {
        if ($this->isNumeric()) {
            foreach (array_keys($a_values) as $key) {
                if (!is_numeric($key)) {
                    throw new Exception("ilADTEnum was expecting numeric option keys");
                }
            }
        }

        $this->options = $a_values;
    }

    // comparison

    public function isComparableTo(ilADT $a_adt): bool
    {
        // has to be enum-based
        return ($a_adt instanceof ilADTEnum);
    }

    public function getADTInstance(): ilADTEnum
    {
        if ($this->isNumeric()) {
            $class = "ilADTEnumNumeric";
        } else {
            $class = "ilADTEnumText";
        }
        return new $class($this);
    }
}
