<?php

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

    public function setStatus(bool $a_value = null): void
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
