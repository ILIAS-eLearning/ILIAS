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

namespace ILIAS\Questions\Presentation;

use ILIAS\GlobalScreen\Scope\Layout\Factory\BreadCrumbsModification;
use ILIAS\GlobalScreen\Scope\Layout\Factory\MainBarModification;
use ILIAS\GlobalScreen\Scope\Layout\Factory\MetaBarModification;
use ILIAS\GlobalScreen\Scope\Layout\Factory\PageBuilderModification;
use ILIAS\GlobalScreen\Scope\Layout\Builder\StandardPageBuilder;
use ILIAS\GlobalScreen\Scope\Layout\Provider\AbstractModificationProvider;
use ILIAS\GlobalScreen\Scope\Layout\Provider\PagePart\PagePartProvider;
use ILIAS\GlobalScreen\ScreenContext\Stack\CalledContexts;
use ILIAS\GlobalScreen\ScreenContext\Stack\ContextCollection;
use ILIAS\UI\Component\Breadcrumbs\Breadcrumbs;
use ILIAS\UI\Component\Layout\Page\Page;
use ILIAS\UI\Component\MainControls\MetaBar;
use ILIAS\UI\Component\MainControls\MainBar;

class LayoutProvider extends AbstractModificationProvider
{
    public const MODE_ENABLED = 'question_edit_mode_enabled';
    public const QUESTIONLIST_ENTRY = 'question_questionlist_entry';
    public const URL_CLOSE_MODE_INFO = 'question_close_mode_info';
    public const URL_CREATE_QUESTION = 'question_create_question_url';

    private const MODIFICATION_PRIORITY = 5; //slightly above "low"

    public function isInterestedInContexts(): ContextCollection
    {
        return $this->context_collection->main();
    }

    protected function isModeEnabled(CalledContexts $called_contexts): bool
    {
        return $called_contexts->current()->getAdditionalData()
            ->is(self::MODE_ENABLED, true);
    }

    public function getBreadCrumbsModification(CalledContexts $called_contexts): ?BreadCrumbsModification
    {
        if (!$this->isModeEnabled($called_contexts)) {
            return null;
        }

        return $this->globalScreen()->layout()->factory()->breadcrumbs()
            ->withModification(
                function (?Breadcrumbs $current): ?Breadcrumbs {
                    return null;
                }
            )->withPriority(self::MODIFICATION_PRIORITY);
    }

    public function getMainBarModification(CalledContexts $called_contexts): ?MainBarModification
    {
        $mainbar = $this->globalScreen()->layout()->factory()->mainbar();

        if (!$this->isModeEnabled($called_contexts)) {
            return null;
        }

        return $mainbar
            ->withModification(
                $this->buildMainBarModification($called_contexts)
            )->withPriority(self::MODIFICATION_PRIORITY);
    }

    public function getMetaBarModification(CalledContexts $called_contexts): ?MetaBarModification
    {
        if (!$this->isModeEnabled($called_contexts)) {
            return null;
        }

        return $this->globalScreen()->layout()->factory()->metabar()
            ->withModification(
                function (?MetaBar $current): ?MetaBar {
                    return null;
                }
            )->withPriority(self::MODIFICATION_PRIORITY);
    }

    public function getPageBuilderDecorator(CalledContexts $called_contexts): ?PageBuilderModification
    {
        if (!$this->isModeEnabled($called_contexts)) {
            return null;
        }

        $mode_info = $this->dic['ui.factory']->mainControls()->modeInfo(
            $this->dic->language()->txt('edit_questions'),
            $called_contexts->current()->getAdditionalData()->get(self::URL_CLOSE_MODE_INFO)
        );

        return $this->factory->page()
            ->withLowPriority()
            ->withModification(
                static function (PagePartProvider $parts) use ($mode_info): Page {
                    $p = new StandardPageBuilder();
                    $page = $p->build($parts);
                    return $page->withModeInfo($mode_info);
                }
            );
    }

    private function buildMainbarModification(CalledContexts $called_contexts): \Closure
    {
        return function (?MainBar $mainbar) use ($called_contexts): ?MainBar {
            if ($mainbar === null) {
                return null;
            }

            $tools = $mainbar->getToolEntries();
            $new_mainbar = array_reduce(
                array_keys($tools),
                static fn(MainBar $c, string $v): MainBar => $c->withAdditionalToolEntry($v, $tools[$v]),
                $mainbar
                    ->withClearedEntries()
                    ->withAdditionalEntry(
                        'create_question',
                        $this->dic['ui.factory']->button()->bulky(
                            $this->dic['ui.factory']->symbol()->icon()->standard('', '')->withAbbreviation('CQ'),
                            $this->dic['lng']->txt('create_question'),
                            $called_contexts->current()->getAdditionalData()->get(self::URL_CREATE_QUESTION)->__toString()
                        )
                    )
            );

            if ($called_contexts->current()->getAdditionalData()->exists(self::QUESTIONLIST_ENTRY)) {
                return $new_mainbar->withAdditionalEntry(
                    'question_list',
                    $called_contexts->current()->getAdditionalData()->get(self::QUESTIONLIST_ENTRY)
                );
            }

            return $new_mainbar;
        };
    }
}
