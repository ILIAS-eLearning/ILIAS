<?php

/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Abstract external link db bridge
 *
 * @author Stefan Meyer <meyer@leifos.com>
 * @ingroup ServicesADT
 */
class ilADTExternalLinkDBBridge extends ilADTDBBridge
{
    /**
     * check valid type
     * @param ilADT $a_adt
     * @return bool
     */
    protected function isValidADT(ilADT $a_adt)
    {
        return $a_adt instanceof ilADTExternalLink;
    }
    
    /**
     * read record
     * @param array $a_row
     */
    public function readRecord(array $a_row)
    {
        $this->getADT()->setUrl($a_row[$this->getElementId() . '_value']);
        $this->getADT()->setTitle($a_row[$this->getElementId() . '_title']);
    }
    
    /**
     * prepare insert
     * @param array $a_fields
     */
    public function prepareInsert(array &$a_fields)
    {
        $a_fields[$this->getElementId() . '_value'] = ["text",$this->getADT()->getUrl()];
        $a_fields[$this->getElementId() . '_title'] = ['text',$this->getADT()->getTitle()];
    }
}
