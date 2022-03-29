<?php declare(strict_types=1);

/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * external link form bridge
 * @author  Stefan Meyer <meyer@leifos.com>
 * @ingroup ServicesADT
 */
class ilADTExternalLinkFormBridge extends ilADTFormBridge
{
    protected ilLogger $logger;

    public function __construct(ilADT $a_adt)
    {
        global $DIC;
        parent::__construct($a_adt);

        $this->logger = $DIC->logger()->amet();
    }

    /**
     * Is valid type
     * @param ilADT $a_adt
     * @return bool
     */
    protected function isValidADT(ilADT $a_adt) : bool
    {
        return $a_adt instanceof ilADTExternalLink;
    }

    /**
     * Add element to form
     */
    public function addToForm() : void
    {
        $def = $this->getADT()->getCopyOfDefinition();

        $both = new ilCombinationInputGUI($this->getTitle(), $this->getElementId());
        $this->addBasicFieldProperties($both, $def);

        $title = new ilTextInputGUI('', $this->getElementId() . '_title');
        $title->setMaxLength(250);
        $title->setValue($this->getADT()->getTitle());
        $both->addCombinationItem('title', $title, $this->lng->txt('title'));

        $url = new ilTextInputGUI('', $this->getElementId() . '_url');
        $url->setSize(250);
        $url->setValue($this->getADT()->getUrl());
        $both->addCombinationItem('url', $url, $this->lng->txt('url'));

        $this->addToParentElement($both);
    }

    /**
     * Import from post
     */
    public function importFromPost() : void
    {
        $this->getADT()->setUrl($this->getForm()->getInput($this->getElementId() . '_url'));
        $this->getADT()->setTitle($this->getForm()->getInput($this->getElementId() . '_title'));

        $combination = $this->getForm()->getItemByPostVar($this->getElementId());
        if (!$combination instanceof ilCombinationInputGUI) {
            $this->logger->warning('Cannot find combination input gui');
            return;
        }

        $combination->getCombinationItem('url')->setValue($this->getADT()->getUrl());
        $combination->getCombinationItem('title')->setValue($this->getADT()->getTitle());
    }
}
