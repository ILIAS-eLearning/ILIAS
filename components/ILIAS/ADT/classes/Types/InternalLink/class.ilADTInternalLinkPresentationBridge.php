<?php

declare(strict_types=1);

/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * external link presentation bridge
 * @author  Stefan Meyer <meyer@leifos.com>
 * @ingroup ServicesADT
 */
class ilADTInternalLinkPresentationBridge extends ilADTPresentationBridge
{
    protected ilAccessHandler $access;

    protected ilObjUser $user;

    public function __construct(ilADT $a_adt)
    {
        global $DIC;
        parent::__construct($a_adt);

        $this->access = $DIC->access();
        $this->user = $DIC->user();
    }

    /**
     * Is valid type
     * @param ilADT $a_adt
     * @return bool
     */
    protected function isValidADT(ilADT $a_adt): bool
    {
        return $a_adt instanceof ilADTInternalLink;
    }

    /**
     * Get html
     * @return string
     */
    public function getHTML(): string
    {
        if ($this->getADT()->isNull()) {
            return '';
        }

        if (!$this->getADT()->isValid()) {
            return '';
        }

        if ($this->access->checkAccess('read', '', $this->getADT()->getTargetRefId())) {
            $title = ilObject::_lookupTitle(ilObject::_lookupObjId($this->getADT()->getTargetRefId()));
            $link = ilLink::_getLink($this->getADT()->getTargetRefId());

            return $this->decorate(
                '<a href="' . $link . '">' . $title . '</a>'
            );
        }
        if ($this->access->checkAccess('visible', '', $this->getADT()->getTargetRefId())) {
            $title = ilObject::_lookupTitle(ilObject::_lookupObjId($this->getADT()->getTargetRefId()));

            return $this->decorate($title);
        }
        return '';
    }

    /**
     * Get soratable
     * @return
     */
    public function getSortable()
    {
        if (!$this->getADT()->isNull()) {
            return $this->getADT()->getTargetRefId();
        }
        return 0;
    }
}
