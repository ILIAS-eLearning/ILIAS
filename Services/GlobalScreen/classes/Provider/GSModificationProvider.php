<?php

namespace ILIAS\GlobalScreen\Provider;

use ILIAS\GlobalScreen\Client\ModeToggle;
use ILIAS\GlobalScreen\Scope\Layout\Factory\ContentModification;
use ILIAS\GlobalScreen\Scope\Layout\Provider\AbstractModificationProvider;
use ILIAS\GlobalScreen\ScreenContext\Stack\CalledContexts;
use ILIAS\GlobalScreen\ScreenContext\Stack\ContextCollection;
use ILIAS\UI\Component\Legacy\Legacy;

/**
 * Class GSModificationProvider
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class GSModificationProvider extends AbstractModificationProvider
{

    /**
     * @inheritDoc
     */
    public function isInterestedInContexts() : ContextCollection
    {
        return $this->context_collection->main();
    }


    /**
     * @inheritDoc
     */
    public function getContentModification(CalledContexts $screen_context_stack) : ?ContentModification
    {
        return null;
        return $this->factory->content()->withModification(function (Legacy $c) : Legacy {
            $ui = $this->dic->ui();
            $m = new ModeToggle();

            return $ui->factory()->legacy($m->getMode() . $ui->renderer()->render($c));
        })->withHighPriority();
    }
}
