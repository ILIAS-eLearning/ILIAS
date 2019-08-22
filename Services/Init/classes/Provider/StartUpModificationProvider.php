<?php

namespace ILIAS\Init\Provider;

use ILIAS\GlobalScreen\Scope\Layout\Factory\MainBarModification;
use ILIAS\GlobalScreen\Scope\Layout\Factory\MetaBarModification;
use ILIAS\GlobalScreen\Scope\Layout\Provider\AbstractModificationProvider;
use ILIAS\GlobalScreen\ScreenContext\Stack\CalledContexts;
use ILIAS\GlobalScreen\ScreenContext\Stack\ContextCollection;
use ILIAS\UI\Component\MainControls\MainBar;
use ILIAS\UI\Component\MainControls\MetaBar;

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


    /**
     * This makes sure no other meta-bar item from the components are shown.
     * We only need a login button.
     *
     * THERE IS NO LOGIN GLYPH ATM.
     *
     * @inheritDoc
     */
    public function getMetaBarModification(CalledContexts $screen_context_stack) : ?MetaBarModification
    {
        return $this->factory->metabar()->withModification(function (?MetaBar $current) : ?MetaBar {

            $factory = $this->dic->ui()->factory();
            $metabar = $factory->mainControls()->metaBar();

            return $metabar->withAdditionalEntry('login', $factory->button()->bulky($factory->symbol()->glyph()->reset(), 'login', "login.php"));
        })->withLowPriority();
    }
}
