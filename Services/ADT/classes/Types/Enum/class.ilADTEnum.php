<?php

declare(strict_types=1);

abstract class ilADTEnum extends ilADT
{
    /**
     * @var mixed
     */
    protected $value;

    protected function isValidDefinition(ilADTDefinition $a_def): bool
    {
        return ($a_def instanceof ilADTEnumDefinition);
    }

    public function reset(): void
    {
        parent::reset();
        $this->value = null;
    }

    // properties

    /**
     * @param string|int $a_value
     * @return string|int
     */
    abstract protected function handleSelectionValue($a_value);

    public function setSelection($a_value = null)
    {
        if ($a_value !== null) {
            $a_value = $this->handleSelectionValue($a_value);
            if (!$this->isValidOption($a_value)) {
                $a_value = null;
            }
        }
        $this->value = $a_value;
    }

    /**
     * @return mixed
     */
    public function getSelection()
    {
        return $this->value;
    }

    public function isValidOption($a_value): bool
    {
        $a_value = $this->handleSelectionValue($a_value);
        return array_key_exists($a_value, $this->getDefinition()->getOptions());
    }

    // comparison

    public function equals(ilADT $a_adt): ?bool
    {
        if ($this->getDefinition()->isComparableTo($a_adt)) {
            return ($this->getSelection() === $a_adt->getSelection());
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
        return $this->getSelection() === null;
    }

    // check

    public function getCheckSum(): ?string
    {
        if (!$this->isNull()) {
            return (string) $this->getSelection();
        }
        return null;
    }

    // stdClass

    public function exportStdClass(): ?stdClass
    {
        if (!$this->isNull()) {
            $obj = new stdClass();
            $obj->value = $this->getSelection();
            return $obj;
        }
        return null;
    }

    public function importStdClass(?stdClass $a_std): void
    {
        if (is_object($a_std)) {
            $this->setSelection($a_std->value);
        }
    }
}
