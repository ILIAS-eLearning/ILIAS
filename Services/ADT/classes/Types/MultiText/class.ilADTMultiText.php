<?php declare(strict_types=1);

class ilADTMultiText extends ilADT
{
    protected ?array $values;

    protected function isValidDefinition(ilADTDefinition $a_def) : bool
    {
        return $a_def instanceof ilADTMultiTextDefinition;
    }

    public function reset() : void
    {
        parent::reset();
        $this->values = null;
    }

    // properties

    public function setTextElements(?array $a_values = null) : void
    {
        if (is_array($a_values)) {
            if (count($a_values)) {
                foreach ($a_values as $idx => $element) {
                    $a_values[$idx] = trim($element);
                    if (!$a_values[$idx]) {
                        unset($a_values[$idx]);
                    }
                }
                $a_values = array_unique($a_values);
            }
            if (!count($a_values)) {
                $a_values = null;
            }
        }
        $this->values = $a_values;
    }

    public function getTextElements() : ?array
    {
        return $this->values;
    }

    // comparison

    public function equals(ilADT $a_adt) : ?bool
    {
        if ($this->getDefinition()->isComparableTo($a_adt)) {
            return ($this->getCheckSum() == $a_adt->getCheckSum());
        }
        return null;
    }

    public function isLarger(ilADT $a_adt) : ?bool
    {
        return null;
    }

    public function isSmaller(ilADT $a_adt) : ?bool
    {
        return null;
    }


    // null

    /**
     * @return bool
     */
    public function isNull() : bool
    {
        $all = $this->getTextElements();
        return (!is_array($all) || !count($all));
    }

    // validation

    public function isValid() : bool
    {
        $valid = parent::isValid();
        if (!$this->isNull()) {
            $max_size = $this->getDefinition()->getMaxSize();
            if ($max_size && $max_size < count((array) $this->getTextElements())) {
                $valid = false;
                $this->addValidationError(self::ADT_VALIDATION_ERROR_MAX_SIZE);
            }

            $max_len = $this->getDefinition()->getMaxLength();
            if ($max_len) {
                foreach ((array) $this->getTextElements() as $element) {
                    if ($max_len < strlen($element)) {
                        $valid = false;
                        $this->addValidationError(self::ADT_VALIDATION_ERROR_MAX_LENGTH);
                    }
                }
            }
        }
        return $valid;
    }

    public function getCheckSum() : ?string
    {
        if (!$this->isNull()) {
            $elements = $this->getTextElements();
            sort($elements);
            return md5(implode("", $elements));
        }
        return null;
    }

    // stdClass

    public function exportStdClass() : ?stdClass
    {
        if (!$this->isNull()) {
            $obj = new stdClass();
            $obj->value = $this->getTextElements();
            return $obj;
        }
        return null;
    }

    public function importStdClass(?stdClass $a_std) : void
    {
        if (is_object($a_std)) {
            $this->setTextElements($a_std->value);
        }
    }
}
