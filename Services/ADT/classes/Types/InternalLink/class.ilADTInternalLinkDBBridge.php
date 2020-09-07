<?php

/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Abstract internal link db bridge
 *
 * @author Stefan Meyer <meyer@leifos.com>
 * @ingroup ServicesADT
 */
class ilADTInternalLinkDBBridge extends ilADTDBBridge
{
    /**
     * check valid type
     * @param ilADT $a_adt
     * @return bool
     */
    protected function isValidADT(ilADT $a_adt)
    {
        return $a_adt instanceof ilADTInternalLink;
    }
    
    /**
     * read record
     * @param array $a_row
     */
    public function readRecord(array $a_row)
    {
        $this->getADT()->setTargetRefId($a_row[$this->getElementId()]);
    }
    
    /**
     * prepare insert
     * @param array $a_fields
     */
    public function prepareInsert(array &$a_fields)
    {
        $a_fields[$this->getElementId()] = ["integer",$this->getADT()->getTargetRefId()];
    }
}
