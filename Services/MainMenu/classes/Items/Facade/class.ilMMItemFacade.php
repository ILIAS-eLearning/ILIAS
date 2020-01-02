<?php

use ILIAS\GlobalScreen\Scope\MainMenu\Collector\MainMenuMainCollector as Main;
use ILIAS\GlobalScreen\Scope\MainMenu\Factory\Item\Lost;

/**
 * Class ilMMItemFacade
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class ilMMItemFacade extends ilMMAbstractItemFacade implements ilMMItemFacadeInterface
{

    /**
     * @inheritDoc
     */
    public function __construct(\ILIAS\GlobalScreen\Identification\IdentificationInterface $identification, Main $collector)
    {
        parent::__construct($identification, $collector);
    }


    /**
     * @var string
     */
    protected $type;


    /**
     * @return bool
     */
    public function isCustom() : bool
    {
        return false;
    }


    /**
     * @inheritDoc
     */
    public function isEditable() : bool
    {
        return (!$this->gs_item instanceof Lost);
    }


    /**
     * @inheritDoc
     */
    public function isDeletable() : bool
    {
        return ($this->gs_item instanceof Lost);
    }




    // Setter


    /**
     * @inheritDoc
     */
    public function setType(string $type)
    {
        $this->type = $type;
    }


    /**
     * @inheritDoc
     */
    public function setAction(string $action)
    {
        // Setting action not possible for non custom items
        return;
    }
}
