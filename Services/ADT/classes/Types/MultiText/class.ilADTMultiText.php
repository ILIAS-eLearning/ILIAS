<?php

class ilADTMultiText extends ilADT
{
    protected $values; // [array]
    
    
    // definition
    
    protected function isValidDefinition(ilADTDefinition $a_def)
    {
        return ($a_def instanceof ilADTMultiTextDefinition);
    }
    
    public function reset()
    {
        parent::reset();
        
        $this->values = null;
    }
    
    
    // properties
    
    public function setTextElements(array $a_values = null)
    {
        if (is_array($a_values)) {
            if (sizeof($a_values)) {
                foreach ($a_values as $idx => $element) {
                    $a_values[$idx]= trim($element);
                    if (!$a_values[$idx]) {
                        unset($a_values[$idx]);
                    }
                }
                $a_values = array_unique($a_values);
            }
            if (!sizeof($a_values)) {
                $a_values = null;
            }
        }
        $this->values = $a_values;
    }
    
    public function getTextElements()
    {
        return $this->values;
    }
    
    
    // comparison
    
    public function equals(ilADT $a_adt)
    {
        if ($this->getDefinition()->isComparableTo($a_adt)) {
            return ($this->getCheckSum() == $a_adt->getCheckSum());
        }
    }
                
    public function isLarger(ilADT $a_adt)
    {
        // return null?
    }

    public function isSmaller(ilADT $a_adt)
    {
        // return null?
    }
    
    
    // null
    
    public function isNull()
    {
        $all = $this->getTextElements();
        return (!is_array($all) || !sizeof($all));
    }
    
    
    // validation
    
    public function isValid()
    {
        $valid = parent::isValid();
        
        if (!$this->isNull()) {
            $max_size = $this->getDefinition()->getMaxSize();
            if ($max_size && $max_size < sizeof($this->getTextElements())) {
                $valid = false;
                $this->addValidationError(self::ADT_VALIDATION_ERROR_MAX_SIZE);
            }

            $max_len = $this->getDefinition()->getMaxLength();
            if ($max_len) {
                foreach ($this->getTextElements() as $element) {
                    if ($max_len < strlen($element)) {
                        $valid = false;
                        $this->addValidationError(self::ADT_VALIDATION_ERROR_MAX_LENGTH);
                    }
                }
            }
        }
            
        return $valid;
    }
    
    
    // check
    
    public function getCheckSum()
    {
        if (!$this->isNull()) {
            $elements = $this->getTextElements();
            sort($elements);
            return md5(implode("", $elements));
        }
    }
}
