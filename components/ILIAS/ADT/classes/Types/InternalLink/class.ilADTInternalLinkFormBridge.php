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
 * external link form bridge
 * @author  Stefan Meyer <meyer@leifos.com>
 * @ingroup ServicesADT
 */
class ilADTInternalLinkFormBridge extends ilADTFormBridge
{
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
     * Add element to form
     */
    public function addToForm(): void
    {
        $def = $this->getADT()->getCopyOfDefinition();

        $subitems = new ilRepositorySelector2InputGUI(
            $this->getTitle(),
            $this->getElementId(),
            false
        );
        $subitems->setValue($this->getADT()->getTargetRefId());
        $exp = $subitems->getExplorerGUI();
        $exp->setSkipRootNode(false);
        $exp->setRootId(ROOT_FOLDER_ID);
        $this->addBasicFieldProperties($subitems, $def);
        $this->addToParentElement($subitems);
    }

    /**
     * Import from post
     */
    public function importFromPost(): void
    {
        $this->getADT()->setTargetRefId((int) $this->getForm()->getInput($this->getElementId()));
    }
}
