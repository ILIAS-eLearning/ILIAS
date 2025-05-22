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

namespace ILIAS\Test\Presentation;

use ILIAS\GlobalScreen\Scope\Layout\Factory\MainBarModification;
use ILIAS\GlobalScreen\Scope\Layout\Factory\MetaBarModification;
use ILIAS\GlobalScreen\Scope\Layout\Provider\AbstractModificationProvider;
use ILIAS\GlobalScreen\Scope\Layout\Factory\BreadCrumbsModification;
use ILIAS\GlobalScreen\ScreenContext\Stack\CalledContexts;
use ILIAS\GlobalScreen\ScreenContext\Stack\ContextCollection;
use ILIAS\UI\Component\Breadcrumbs\Breadcrumbs;
use ILIAS\UI\Component\MainControls\MetaBar;
use ILIAS\UI\Component\MainControls\MainBar;

class PrintLayoutProvider extends AbstractModificationProvider
{
    public const TEST_CONTEXT_PRINT = 'test_context_print';

    private const MODIFICATION_PRIORITY = 5; //slightly above "low"

    public function isInterestedInContexts(): ContextCollection
    {
        return $this->context_collection->main();
    }

    protected function isTestContextPrint(CalledContexts $called_contexts): bool
    {
        return $called_contexts->current()->getAdditionalData()
            ->is(self::TEST_CONTEXT_PRINT, true);
    }

    public function getMainBarModification(CalledContexts $called_contexts): ?MainBarModification
    {
        if (!$this->isTestContextPrint($called_contexts)) {
            return null;
        }
        return $this->globalScreen()->layout()->factory()->mainbar()->withModification(
            static fn(?MainBar $mainbar): ?MainBar => null
        )->withPriority(self::MODIFICATION_PRIORITY);
    }

    public function getMetaBarModification(CalledContexts $called_contexts): ?MetaBarModification
    {
        if (!$this->isTestContextPrint($called_contexts)) {
            return null;
        }

        return $this->globalScreen()->layout()->factory()->metabar()->withModification(
            static fn(?MetaBar $metabar): ?MetaBar => null
        )->withPriority(self::MODIFICATION_PRIORITY);
    }

    public function getBreadCrumbsModification(CalledContexts $called_contexts): ?BreadCrumbsModification
    {
        if (!$this->isTestContextPrint($called_contexts)) {
            return null;
        }

        return $this->globalScreen()->layout()->factory()->breadcrumbs()->withModification(
            static fn(?Breadcrumbs $breadcrumbs): ?Breadcrumbs => null
        )->withPriority(self::MODIFICATION_PRIORITY);
    }
}
