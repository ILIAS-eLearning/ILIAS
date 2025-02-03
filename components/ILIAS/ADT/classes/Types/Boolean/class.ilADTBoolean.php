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

class ilADTBoolean extends ilADT
{
    protected ?bool $value;

    // definition

    protected function isValidDefinition(ilADTDefinition $a_def): bool
    {
        return ($a_def instanceof ilADTBooleanDefinition);
    }

    public function reset(): void
    {
        parent::reset();
        $this->value = null;
    }

    // properties

    public function setStatus(?bool $a_value = null): void
    {
        $this->value = $a_value;
    }

    public function getStatus(): ?bool
    {
        return $this->value;
    }

    // comparison

    public function equals(ilADT $a_adt): ?bool
    {
        if ($this->getDefinition()->isComparableTo($a_adt)) {
            return ($this->getStatus() === $a_adt->getStatus());
        }
        return null;
    }

    public function isLarger(ilADT $a_adt): ?bool
    {
        return null;
    }

    public function isSmaller(ilADT $a_adt): ?bool
    {
        return null;
    }

    // null

    public function isNull(): bool
    {
        return $this->getStatus() === null;
    }

    public function isValid(): bool
    {
        return true;
    }

    // check

    public function getCheckSum(): ?string
    {
        if (!$this->isNull()) {
            return (string) $this->getStatus();
        }
        return null;
    }

    // stdClass

    public function exportStdClass(): ?stdClass
    {
        if (!$this->isNull()) {
            $obj = new stdClass();
            $obj->value = $this->getStatus();
            return $obj;
        }
        return null;
    }

    public function importStdClass(?stdClass $a_std): void
    {
        if (is_object($a_std)) {
            $this->setStatus((bool) $a_std->value);
        }
    }
}
