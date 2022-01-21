<?php declare(strict_types=1);

class ilADTGroup extends ilADT
{
    protected array $elements = [];

    public function __clone()
    {
        if (is_array($this->elements)) {
            foreach ($this->elements as $id => $element) {
                $this->elements[$id] = clone $element;
            }
        }
    }

    protected function isValidDefinition(ilADTDefinition $a_def) : bool
    {
        return ($a_def instanceof ilADTGroupDefinition);
    }

    protected function setDefinition(ilADTDefinition $a_def) : void
    {
        parent::setDefinition($a_def);

        $this->elements = array();

        foreach ($this->getDefinition()->getElements() as $name => $def) {
            $this->addElement((string) $name, $def);
        }
    }

    public function reset() : void
    {
        parent::reset();

        $elements = $this->getElements();
        if (is_array($elements)) {
            foreach ($elements as $element) {
                $element->reset();
            }
        }
    }

    protected function addElement(string $a_name, ilADTDefinition $a_def) : void
    {
        $this->elements[$a_name] = ilADTFactory::getInstance()->getInstanceByDefinition($a_def);
    }

    public function hasElement(string $a_name) : bool
    {
        return array_key_exists($a_name, $this->elements);
    }

    public function getElement(string $a_name) : ?ilADT
    {
        if ($this->hasElement($a_name)) {
            return $this->elements[$a_name];
        }
        return null;
    }

    public function getElements() : array
    {
        return $this->elements;
    }

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

    public function isNull() : bool
    {
        return !count($this->getElements());
    }

    public function getValidationErrorsByElements() : array
    {
        return $this->validation_errors;
    }

    /**
     * @inheritcoc
     */
    public function getValidationErrors() : array
    {
        return array_keys($this->validation_errors);
    }

    protected function addElementValidationError(string $a_element_id, string $a_error_code) : void
    {
        $this->validation_errors[$a_error_code] = $a_element_id;
    }

    public function isValid() : bool
    {
        $valid = parent::isValid();
        if (!$this->isNull()) {
            foreach ($this->getElements() as $element_id => $element) {
                if (!$element->isValid()) {
                    foreach ($element->getValidationErrors() as $error) {
                        $this->addElementValidationError($element_id, $error);
                    }
                    $valid = false;
                }
            }
        }
        return $valid;
    }

    /**
     * @inheritcoc
     */
    public function translateErrorCode(string $a_code) : string
    {
        if (isset($this->validation_errors[$a_code])) {
            $element_id = $this->validation_errors[$a_code];
            $element = $this->getElement($element_id);
            if ($element) {
                return $element->translateErrorCode($a_code);
            }
        }
        return parent::translateErrorCode($a_code);
    }

    public function getCheckSum() : ?string
    {
        if (!$this->isNull()) {
            $tmp = [];
            foreach ($this->getElements() as $element) {
                $tmp[] = $element->getCheckSum();
            }
            return md5(implode(",", $tmp));
        }
        return null;
    }

    public function exportStdClass() : ?stdClass
    {
        $obj = new stdClass();
        foreach ($this->getElements() as $id => $element) {
            $obj->$id = $element->exportStdClass();
        }
        return $obj;
    }

    public function importStdClass(?stdClass $a_std) : void
    {
        if (is_object($a_std)) {
            foreach ($this->getElements() as $id => $element) {
                $element->importStdClass($a_std->$id);
            }
        }
    }
}
