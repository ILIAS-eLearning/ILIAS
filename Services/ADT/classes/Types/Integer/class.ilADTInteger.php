<?php declare(strict_types=1);

class ilADTInteger extends ilADT
{
    protected ?int $value;

    // definition

    protected function isValidDefinition(ilADTDefinition $a_def) : bool
    {
        return $a_def instanceof ilADTIntegerDefinition;
    }

    public function reset() : void
    {
        parent::reset();

        $this->value = null;
    }

    // properties

    public function setNumber($a_value = null)
    {
        $this->value = $this->getDefinition()->handleNumber($a_value);
    }

    public function getNumber() : ?int
    {
        return $this->value;
    }

    // comparison

    public function equals(ilADT $a_adt) : ?bool
    {
        if ($this->getDefinition()->isComparableTo($a_adt)) {
            return ($this->getNumber() == $a_adt->getNumber());
        }
        return null;
    }

    public function isLarger(ilADT $a_adt) : ?bool
    {
        if ($this->getDefinition()->isComparableTo($a_adt)) {
            return ($this->getNumber() > $a_adt->getNumber());
        }
        return null;
    }

    public function isSmaller(ilADT $a_adt) : ?bool
    {
        if ($this->getDefinition()->isComparableTo($a_adt)) {
            return ($this->getNumber() < $a_adt->getNumber());
        }
        return null;
    }

    // null

    public function isNull() : bool
    {
        return $this->getNumber() === null;
    }

    public function isValid() : bool
    {
        $valid = parent::isValid();
        $num = $this->getNumber();
        if ($num !== null) {
            $min = $this->getDefinition()->getMin();
            if ($min !== null && $num < $min) {
                $this->addValidationError(self::ADT_VALIDATION_ERROR_MIN);
                $valid = false;
            }

            $max = $this->getDefinition()->getMax();
            if ($max !== null && $num > $max) {
                $this->addValidationError(self::ADT_VALIDATION_ERROR_MAX);
                $valid = false;
            }
        }
        return $valid;
    }

    public function getCheckSum() : ?string
    {
        if (!$this->isNull()) {
            return (string) $this->getNumber();
        }
        return null;
    }

    // stdClass

    public function exportStdClass() : ?stdClass
    {
        if (!$this->isNull()) {
            $obj = new stdClass();
            $obj->value = $this->getNumber();
            return $obj;
        }
        return null;
    }

    public function importStdClass(?stdClass $a_std) : void
    {
        if (is_object($a_std)) {
            $this->setNumber($a_std->value);
        }
    }
}
