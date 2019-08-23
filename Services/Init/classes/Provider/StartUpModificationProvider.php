<?php

namespace ILIAS\Init\Provider;

use ILIAS\GlobalScreen\Scope\Layout\Factory\MainBarModification;
use ILIAS\GlobalScreen\Scope\Layout\Provider\AbstractModificationProvider;
use ILIAS\GlobalScreen\ScreenContext\Stack\CalledContexts;
use ILIAS\GlobalScreen\ScreenContext\Stack\ContextCollection;
use ILIAS\UI\Component\MainControls\MainBar;

/**
 * Class StartUpModificationProvider
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class StartUpModificationProvider extends AbstractModificationProvider
{

    /**
     * @inheritDoc
     */
    public function isInterestedInContexts() : ContextCollection
    {
        return $this->context_collection->external();
    }


    /**
     * This removes the MainBar
     *
     * @inheritDoc
     */
    public function getMainBarModification(CalledContexts $screen_context_stack) : ?MainBarModification
    {
        return $this->factory->mainbar()->withModification(function (?MainBar $current) : ?MainBar { return null; })->withLowPriority();
    }
}
