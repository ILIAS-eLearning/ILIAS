<?php

require_once "Services/ADT/classes/Bridges/class.ilADTSearchBridge.php";

abstract class ilADTSearchBridgeRange extends ilADTSearchBridge
{
    protected $adt_lower; // [ilADT]
    protected $adt_upper; // [ilADT]
    
    protected function setDefinition(ilADTDefinition $a_adt_def)
    {
        if ($this->isValidADTDefinition($a_adt_def)) {
            $factory = ilADTFactory::getInstance();
            $this->adt_lower = $factory->getInstanceByDefinition($a_adt_def);
            $this->adt_upper = $factory->getInstanceByDefinition($a_adt_def);
            return;
        }
                
        throw new Exception('ilADTSearchBridge type mismatch.');
    }
    
    /**
     * Get lower ADT
     *
     * @return ilADT
     */
    public function getLowerADT()
    {
        return $this->adt_lower;
    }
    
    /**
     * Get lower ADT
     *
     * @return ilADT
     */
    public function getUpperADT()
    {
        return $this->adt_upper;
    }
    
    public function isNull()
    {
        return ($this->getLowerADT()->isNull() && $this->getUpperADT()->isNull());
    }
    
    public function isValid()
    {
        return ($this->getLowerADT()->isValid() && $this->getUpperADT()->isValid());
    }
    
    public function validate()
    {
        if (!$this->isValid()) {
            $tmp = array();
            $mess = $this->getLowerADT()->getValidationErrors();
            foreach ($mess as $error_code) {
                $tmp[] = $this->getLowerADT()->translateErrorCode($error_code);
            }
            if ($tmp) {
                $field = $this->getForm()->getItemByPostvar($this->addToElementId("lower"));
                $field->setAlert(implode("<br />", $tmp));
            }
            
            $tmp = array();
            $mess = $this->getUpperADT()->getValidationErrors();
            foreach ($mess as $error_code) {
                $tmp[] = $this->getUpperADT()->translateErrorCode($error_code);
            }
            if ($tmp) {
                $field = $this->getForm()->getItemByPostvar($this->addToElementId("upper"));
                $field->setAlert(implode("<br />", $tmp));
            }
            
            return false;
        }
        
        return true;
    }
}
