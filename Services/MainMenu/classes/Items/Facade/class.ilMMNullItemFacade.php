<?php

use ILIAS\GlobalScreen\Identification\IdentificationInterface;
use ILIAS\GlobalScreen\Scope\MainMenu\Collector\MainMenuMainCollector as Main;

/**
 * Class ilMMNullItemFacade
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ilMMNullItemFacade extends ilMMCustomItemFacade implements ilMMItemFacadeInterface
{

    /**
     * @var string
     */
    private $parent_identification = "";
    /**
     * @var
     */
    private $active_status;
    /**
     * @var bool
     */
    protected $top_item = false;


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
    public function isTopItem() : bool
    {
        return $this->top_item;
    }


    /**
     * @inheritDoc
     */
    public function setIsTopItm(bool $top_item)
    {
        $this->top_item = $top_item;
    }


    /**
     * @inheritDoc
     */
    public function isEmpty() : bool
    {
        return true;
    }


    /**
     * @inheritDoc
     */
    public function setActiveStatus(bool $status)
    {
        $this->active_status = $status;
    }


    /**
     * @inheritDoc
     */
    public function setParent(string $parent)
    {
        $this->parent_identification = $parent;
    }


    public function create()
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
        $provider = new ilMMCustomProvider($DIC);
        $this->gs_item = $provider->getSingleCustomItem($s);
        if ($this->parent_identification && $this->gs_item instanceof \ILIAS\GlobalScreen\Scope\MainMenu\Factory\isChild) {
            global $DIC;
            $this->gs_item = $this->gs_item->withParent($DIC->globalScreen()->identification()->fromSerializedIdentification($this->parent_identification));
        }

        $this->identification = $this->gs_item->getProviderIdentification();

        $this->mm_item = new ilMMItemStorage();
        $this->mm_item->setPosition(9999999); // always the last on the top item
        $this->mm_item->setIdentification($this->gs_item->getProviderIdentification()->serialize());
        $this->mm_item->setParentIdentification($this->parent_identification);
        $this->mm_item->setActive($this->active_status);
        if ($this->gs_item instanceof \ILIAS\GlobalScreen\Scope\MainMenu\Factory\isChild) {
            $this->mm_item->setParentIdentification($this->gs_item->getParent()->serialize());
        }

        parent::create();
    }


    public function isAvailable() : bool
    {
        return false;
    }


    /**
     * @inheritDoc
     */
    public function isAlwaysAvailable() : bool
    {
        return false;
    }


    /**
     * @inheritDoc
     */
    public function getProviderNameForPresentation() : string
    {
        return $this->identification->getProviderNameForPresentation();
    }
}
