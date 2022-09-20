<?php

declare(strict_types=1);

use ILIAS\GlobalScreen\Scope\MainMenu\Collector\MainMenuMainCollector as Main;
use ILIAS\GlobalScreen\Scope\MainMenu\Factory\Item\Lost;
use ILIAS\GlobalScreen\Identification\IdentificationInterface;

/******************************************************************************
 *
 * This file is part of ILIAS, a powerful learning management system.
 *
 * ILIAS is licensed with the GPL-3.0, you should have received a copy
 * of said license along with the source code.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 *      https://www.ilias.de
 *      https://github.com/ILIAS-eLearning
 *
 *****************************************************************************/
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
    public function __construct(IdentificationInterface $identification, Main $collector)
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
    public function isCustom(): bool
    {
        return false;
    }


    /**
     * @inheritDoc
     */
    public function isEditable(): bool
    {
        return (!$this->raw_item instanceof Lost);
    }


    /**
     * @inheritDoc
     */
    public function isDeletable(): bool
    {
        return ($this->raw_item instanceof Lost);
    }




    // Setter


    /**
     * @inheritDoc
     */
    public function setType(string $type): void
    {
        $this->type = $type;
    }


    /**
     * @inheritDoc
     */
    public function setAction(string $action): void
    {
        // Setting action not possible for non custom items
    }
}
