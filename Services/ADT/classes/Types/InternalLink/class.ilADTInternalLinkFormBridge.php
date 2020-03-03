<?php

/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * external link form bridge
 *
 * @author Stefan Meyer <meyer@leifos.com>
 * @ingroup ServicesADT
 */
class ilADTInternalLinkFormBridge extends ilADTFormBridge
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
     * Add element to form
     */
    public function addToForm()
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
    public function importFromPost()
    {
        $this->getADT()->setTargetRefId($this->getForm()->getInput($this->getElementId()));
    }
}
