<?php

require_once "Services/ADT/classes/Bridges/class.ilADTSearchBridge.php";

abstract class ilADTSearchBridgeSingle extends ilADTSearchBridge
{
    protected $adt; // [ilADT]
    
    protected function setDefinition(ilADTDefinition $a_adt_def)
    {
        if ($this->isValidADTDefinition($a_adt_def)) {
            $this->adt = ilADTFactory::getInstance()->getInstanceByDefinition($a_adt_def);
            return;
        }
                
        throw new Exception('ilADTSearchBridge type mismatch.');
    }
    
    /**
     * Get ADT
     *
     * @return ilADT
     */
    public function getADT()
    {
        return $this->adt;
    }
    
    public function isNull()
    {
        return $this->getADT()->isNull();
    }
    
    public function isValid()
    {
        return $this->getADT()->isValid();
    }
    
    public function validate()
    {
        if (!$this->isValid()) {
            $tmp = array();
            
            $mess = $this->getADT()->getValidationErrors();
            foreach ($mess as $error_code) {
                $tmp[] = $this->getADT()->translateErrorCode($error_code);
            }
            
            $field = $this->getForm()->getItemByPostvar($this->getElementId());
            $field->setAlert(implode("<br />", $tmp));
            
            return false;
        }
        
        return true;
    }
}
