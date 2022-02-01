<?php

namespace ILIAS\Init\Provider;

use ILIAS\GlobalScreen\Scope\Layout\Provider\AbstractModificationProvider;
use ILIAS\GlobalScreen\ScreenContext\Stack\ContextCollection;

/**
 * Class StartUpModificationProvider
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
}
