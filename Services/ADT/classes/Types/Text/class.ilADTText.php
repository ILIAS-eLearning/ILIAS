<?php

declare(strict_types=1);

class ilADTText extends ilADT
{
    protected ?string $value;

    // definition

    protected function isValidDefinition(ilADTDefinition $a_def): bool
    {
        return $a_def instanceof ilADTTextDefinition;
    }

    public function reset(): void
    {
        parent::reset();
        $this->value = null;
    }

    // properties

    public function setText(?string $a_value = null): void
    {
        if ($a_value !== null) {
            $a_value = trim($a_value);
        }
        $this->value = $a_value;
    }

    public function getText(): ?string
    {
        return $this->value;
    }

    public function getLength(): int
    {
        if (function_exists("mb_strlen")) {
            return mb_strlen((string) $this->getText(), "UTF-8");
        } else {
            return strlen((string) $this->getText());
        }
    }

    // comparison

    public function equals(ilADT $a_adt): ?bool
    {
        if ($this->getDefinition()->isComparableTo($a_adt)) {
            return !strcmp($this->getText(), $a_adt->getText());
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
        return !$this->getLength();
    }

    // validation

    public function isValid(): bool
    {
        $valid = parent::isValid();
        if (!$this->isNull()) {
            $max = $this->getDefinition()->getMaxLength();
            if ($max && $max < $this->getLength()) {
                $valid = false;
                $this->addValidationError(self::ADT_VALIDATION_ERROR_MAX_LENGTH);
            }
        }
        return $valid;
    }

    public function getCheckSum(): ?string
    {
        if (!$this->isNull()) {
            return md5($this->getText());
        }
        return null;
    }

    public function exportStdClass(): ?stdClass
    {
        if (!$this->isNull()) {
            $obj = new stdClass();
            $obj->value = $this->getText();
            return $obj;
        }
        return null;
    }

    public function importStdClass(?stdClass $a_std): void
    {
        if (is_object($a_std)) {
            $this->setText($a_std->value);
        }
    }
}
