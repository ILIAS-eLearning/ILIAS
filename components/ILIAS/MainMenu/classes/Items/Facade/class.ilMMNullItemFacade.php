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

use ILIAS\GlobalScreen\Identification\IdentificationInterface;
use ILIAS\GlobalScreen\Scope\MainMenu\Collector\MainMenuMainCollector as Main;
use ILIAS\MainMenu\Provider\CustomMainBarProvider;
use ILIAS\GlobalScreen\Scope\MainMenu\Factory\isChild;

/**
 * Class ilMMNullItemFacade
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ilMMNullItemFacade extends ilMMCustomItemFacade implements ilMMItemFacadeInterface
{
    private ?string $parent_identification = "";
    private bool $active_status;
    protected bool $top_item = false;


    /**
     * @inheritDoc
     */
    public function __construct(IdentificationInterface $identification, Main $collector)
    {
        $this->identification = $identification;
        parent::__construct($identification, $collector);
    }


    /**
     * @inheritDoc
     */
    #[\Override]
    public function isTopItem(): bool
    {
        return $this->top_item;
    }


    /**
     * @inheritDoc
     */
    #[\Override]
    public function setIsTopItm(bool $top_item): void
    {
        $this->top_item = $top_item;
    }


    /**
     * @inheritDoc
     */
    #[\Override]
    public function isEmpty(): bool
    {
        return true;
    }


    /**
     * @inheritDoc
     */
    #[\Override]
    public function setActiveStatus(bool $status): void
    {
        $this->active_status = $status;
    }


    /**
     * @inheritDoc
     */
    #[\Override]
    public function setParent(string $parent): void
    {
        $this->parent_identification = $parent;
    }


    #[\Override]
    public function create(): void
    {
        $s = new ilMMCustomItemStorage();
        $s->setIdentifier(uniqid());
        $s->setType($this->type);
        $s->setTopItem($this->isTopItem());
        $s->setAction($this->action);
        $s->setDefaultTitle($this->default_title);
        $s->create();

        $this->custom_item_storage = $s;

        global $DIC;
        $provider = new CustomMainBarProvider($DIC);
        $this->raw_item = $provider->getSingleCustomItem($s);
        if ($this->parent_identification && $this->raw_item instanceof isChild) {
            global $DIC;
            $this->raw_item = $this->raw_item->withParent($DIC->globalScreen()->identification()->fromSerializedIdentification($this->parent_identification));
        }

        $this->identification = $this->raw_item->getProviderIdentification();

        $this->mm_item = new ilMMItemStorage();
        $this->mm_item->setPosition(9999999); // always the last on the top item
        $this->mm_item->setIdentification($this->raw_item->getProviderIdentification()->serialize());
        $this->mm_item->setParentIdentification($this->parent_identification);
        $this->mm_item->setActive($this->active_status);
        if ($this->raw_item instanceof isChild) {
            $this->mm_item->setParentIdentification($this->raw_item->getParent()->serialize());
        }

        parent::create();
    }


    #[\Override]
    public function isAvailable(): bool
    {
        return false;
    }


    /**
     * @inheritDoc
     */
    #[\Override]
    public function isAlwaysAvailable(): bool
    {
        return false;
    }


    /**
     * @inheritDoc
     */
    #[\Override]
    public function getProviderNameForPresentation(): string
    {
        return $this->identification->getProviderNameForPresentation();
    }


    /**
     * @inheritDoc
     */
    #[\Override]
    public function isDeletable(): bool
    {
        return true;
    }


    /**
     * @inheritDoc
     */
    #[\Override]
    public function supportsRoleBasedVisibility(): bool
    {
        return true;
    }
}
