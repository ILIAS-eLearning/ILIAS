<?php

declare(strict_types=1);

/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

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
