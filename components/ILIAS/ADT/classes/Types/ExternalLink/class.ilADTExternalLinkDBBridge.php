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
 * Abstract external link db bridge
 * @author  Stefan Meyer <meyer@leifos.com>
 * @ingroup ServicesADT
 */
class ilADTExternalLinkDBBridge extends ilADTDBBridge
{
    /**
     * check valid type
     * @param ilADT $a_adt
     * @return bool
     */
    protected function isValidADT(ilADT $a_adt): bool
    {
        return $a_adt instanceof ilADTExternalLink;
    }

    /**
     * read record
     * @param array $a_row
     */
    public function readRecord(array $a_row): void
    {
        $this->getADT()->setUrl($a_row[$this->getElementId() . '_value']);
        $this->getADT()->setTitle($a_row[$this->getElementId() . '_title']);
    }

    /**
     * prepare insert
     * @param array $a_fields
     */
    public function prepareInsert(array &$a_fields): void
    {
        $a_fields[$this->getElementId() . '_value'] = ["text", $this->getADT()->getUrl()];
        $a_fields[$this->getElementId() . '_title'] = ['text', $this->getADT()->getTitle()];
    }

    /**
     * @return bool
     */
    public function supportsDefaultValueColumn(): bool
    {
        return false;
    }
}
