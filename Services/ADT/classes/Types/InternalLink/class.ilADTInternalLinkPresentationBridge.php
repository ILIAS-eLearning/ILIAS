<?php

/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * external link presentation bridge
 *
 * @author Stefan Meyer <meyer@leifos.com>
 * @ingroup ServicesADT
 */
class ilADTInternalLinkPresentationBridge extends ilADTPresentationBridge
{

    /**
     * Is valid type
     * @param ilADT $a_adt
     * @return bool
     */
    protected function isValidADT(ilADT $a_adt)
    {
        return $a_adt instanceof ilADTInternalLink;
    }

    /**
     * Get html
     * @return string
     */
    public function getHTML()
    {
        if ($this->getADT()->isNull()) {
            return;
        }
        
        if (!$this->getADT()->isValid()) {
            return;
        }
        
        $access = $GLOBALS['DIC']->access();
        $user = $GLOBALS['DIC']->user();
        
        if ($access->checkAccess('read', '', $this->getADT()->getTargetRefId())) {
            $title = ilObject::_lookupTitle(ilObject::_lookupObjId($this->getADT()->getTargetRefId()));
            $link = ilLink::_getLink($this->getADT()->getTargetRefId());

            return $this->decorate(
                '<a href="' . $link . '">' . $title . '</a>'
            );
        }
        if ($access->checkAccess('visible', '', $this->getADT()->getTargetRefId())) {
            $title = ilObject::_lookupTitle(ilObject::_lookupObjId($this->getADT()->getTargetRefId()));

            return $this->decorate($title);
        }
        return;
    }

    /**
     * Get soratable
     * @return type
     */
    public function getSortable()
    {
        if (!$this->getADT()->isNull()) {
            return $this->getADT()->getTargetRefId();
        }
    }
}
