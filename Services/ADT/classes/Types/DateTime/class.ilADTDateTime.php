<?php declare(strict_types=1);

class ilADTDateTime extends ilADT
{
    protected ?ilDateTime $value;

    // definition

    protected function isValidDefinition(ilADTDefinition $a_def) : bool
    {
        return $a_def instanceof ilADTDateTimeDefinition;
    }

    public function reset() : void
    {
        parent::reset();
        $this->value = null;
    }

    // properties

    public function setDate(ilDateTime $a_value = null)
    {
        if ($a_value && $a_value->isNull()) {
            $a_value = null;
        }
        $this->value = $a_value;
    }

    public function getDate() : ?ilDateTime
    {
        return $this->value;
    }

    // comparison

    public function equals(ilADT $a_adt) : ?bool
    {
        if ($this->getDefinition()->isComparableTo($a_adt)) {
            if (!$this->isNull() && !$a_adt->isNull()) {
                // could use checksum...
                $value = $this->getDate()->get(IL_CAL_UNIX);
                $other = $a_adt->getDate()->get(IL_CAL_UNIX);
                return ($value == $other);
            }
        }
        return null;
    }

    public function isLarger(ilADT $a_adt) : ?bool
    {
        if ($this->getDefinition()->isComparableTo($a_adt)) {
            if (!$this->isNull() && !$a_adt->isNull()) {
                $value = $this->getDate()->get(IL_CAL_UNIX);
                $other = $a_adt->getDate()->get(IL_CAL_UNIX);
                return ($value > $other);
            }
        }
        return null;
    }

    public function isSmaller(ilADT $a_adt) : ?bool
    {
        if ($this->getDefinition()->isComparableTo($a_adt)) {
            if (!$this->isNull() && !$a_adt->isNull()) {
                $value = $this->getDate()->get(IL_CAL_UNIX);
                $other = $a_adt->getDate()->get(IL_CAL_UNIX);
                return ($value < $other);
            }
        }
        return null;
    }

    // null

    public function isNull() : bool
    {
        return !$this->value instanceof ilDateTime || $this->value->isNull();
    }

    public function getCheckSum() : ?string
    {
        if (!$this->isNull()) {
            return (string) $this->getDate()->get(IL_CAL_UNIX);
        }
        return null;
    }

    // stdClass

    public function exportStdClass() : ?stdClass
    {
        if (!$this->isNull()) {
            $obj = new stdClass();
            $obj->value = $this->getDate()->get(IL_CAL_UNIX);
            return $obj;
        }
        return null;
    }

    public function importStdClass(?stdClass $a_std) : void
    {
        if (is_object($a_std)) {
            $this->setDate(new ilDateTime($a_std->value, IL_CAL_UNIX));
        }
    }
}
