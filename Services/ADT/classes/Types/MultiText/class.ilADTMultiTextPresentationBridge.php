<?php

class ilADTMultiTextPresentationBridge extends ilADTPresentationBridge
{
    protected function isValidADT(ilADT $a_adt) : bool
    {
        return ($a_adt instanceof ilADTMultiText);
    }
    
    public function getHTML() : string
    {
        if (!$this->getADT()->isNull()) {
            $res = array();
            foreach ($this->getADT()->getTextElements() as $item) {
                if (trim($item)) {
                    $res[] = $this->decorate($item);
                }
            }
            return implode(", ", $res);
        }
    }
    
    public function getSortable() : mixed
    {
        if (!$this->getADT()->isNull()) {
            return implode(";", $this->getADT()->getTextElements());
        }
    }
}
