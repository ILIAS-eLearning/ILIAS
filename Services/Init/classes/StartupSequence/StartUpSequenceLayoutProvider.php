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

namespace ILIAS\Init\StartupSequence;

use ILIAS\GlobalScreen\Scope\Layout\Provider\AbstractModificationProvider;
use ILIAS\GlobalScreen\Scope\Layout\Factory\MainBarModification;
use ILIAS\GlobalScreen\Scope\Layout\Factory\MetaBarModification;
use ILIAS\GlobalScreen\ScreenContext\Stack\CalledContexts;
use ILIAS\GlobalScreen\ScreenContext\Stack\ContextCollection;
use ILIAS\UI\Component\MainControls\MetaBar;
use ILIAS\UI\Component\MainControls\MainBar;

class StartUpSequenceLayoutProvider extends AbstractModificationProvider
{
    public const FORCED_STARTUP_STEP = 'forced_startup_step';

    protected function isForcedStartupStep(CalledContexts $called_contexts): bool
    {
        return $called_contexts->current()->getAdditionalData()->is(self::FORCED_STARTUP_STEP, true);
    }

    public function isInterestedInContexts(): ContextCollection
    {
        return $this->context_collection->main();
    }

    public function getMainBarModification(CalledContexts $screen_context_stack): ?MainBarModification
    {
        if ($this->isForcedStartupStep($screen_context_stack)) {
            $main_bar = $this->globalScreen()->layout()->factory()->mainbar();
            $main_bar = $main_bar->withModification(function (?MainBar $current): ?MainBar {
                return null;
            });

            return $main_bar->withPriority(\ILIAS\GlobalScreen\Scope\Layout\Factory\LayoutModification::PRIORITY_HIGH);
        }

        return null;
    }

    public function getMetaBarModification(CalledContexts $screen_context_stack): ?MetaBarModification
    {
        if ($this->isForcedStartupStep($screen_context_stack)) {
            $meta_bar = $this->globalScreen()->layout()->factory()->metabar();
            $meta_bar = $meta_bar->withModification(function (?MetaBar $current): ?MetaBar {
                return null;
            });

            return $meta_bar->withPriority(\ILIAS\GlobalScreen\Scope\Layout\Factory\LayoutModification::PRIORITY_HIGH);
        }

        return null;
    }
}
