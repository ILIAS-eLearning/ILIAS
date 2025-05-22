<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

declare(strict_types=1);

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
