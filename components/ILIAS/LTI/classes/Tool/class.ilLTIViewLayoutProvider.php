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

use ILIAS\GlobalScreen\Scope\Layout\Factory\BreadCrumbsModification;
use ILIAS\GlobalScreen\Scope\Layout\Factory\FooterModification;
use ILIAS\GlobalScreen\Scope\Layout\Factory\MainBarModification;
use ILIAS\GlobalScreen\Scope\Layout\Factory\MetaBarModification;
use ILIAS\GlobalScreen\Scope\Layout\Factory\TitleModification;
use ILIAS\GlobalScreen\Scope\Layout\Provider\AbstractModificationProvider;
use ILIAS\GlobalScreen\ScreenContext\Stack\CalledContexts;
use ILIAS\GlobalScreen\ScreenContext\Stack\ContextCollection;
use ILIAS\UI\Component\Breadcrumbs\Breadcrumbs;
use ILIAS\UI\Component\MainControls\Footer;
use ILIAS\UI\Component\MainControls\MainBar;
use ILIAS\UI\Component\MainControls\MetaBar;

/**
 * The page of the LTI view, see ilLTIViewGUI: only the tools in the main bar, only the exit button in the
 * meta bar, the title of the launch, the breadcrumbs from the launched object on and no footer. After the
 * exit, neither bar is left.
 *
 * @author Saúl Díaz <sdiaz@surlabs.com>
 */
class ilLTIViewLayoutProvider extends AbstractModificationProvider
{
    private const int PRIORITY = 63;

    public function isInterestedInContexts(): ContextCollection
    {
        return $this->context_collection->lti();
    }

    public function getMainBarModification(CalledContexts $screen_context_stack): ?MainBarModification
    {
        $exit_mode = $this->isExitMode($screen_context_stack);

        return $this->globalScreen()->layout()->factory()->mainbar()->withModification(
            function (?MainBar $mainbar) use ($exit_mode): ?MainBar {
                if ($mainbar === null) {
                    return null;
                }
                $tools = $mainbar->getToolEntries();
                $mainbar = $mainbar->withClearedEntries();
                if ($exit_mode) {
                    return $mainbar;
                }
                foreach ($tools as $id => $entry) {
                    $mainbar = $mainbar->withAdditionalToolEntry($id, $entry);
                }
                return $mainbar;
            }
        )->withPriority(self::PRIORITY);
    }

    public function getMetaBarModification(CalledContexts $screen_context_stack): ?MetaBarModification
    {
        $exit_mode = $this->isExitMode($screen_context_stack);

        return $this->globalScreen()->layout()->factory()->metabar()->withModification(
            function (?MetaBar $metabar) use ($exit_mode): ?MetaBar {
                if ($metabar === null) {
                    return null;
                }
                $metabar = $metabar->withClearedEntries();
                if ($exit_mode) {
                    return $metabar;
                }
                $factory = $this->dic->ui()->factory();
                return $metabar->withAdditionalEntry('exit', $factory->button()->bulky(
                    $factory->symbol()->glyph()->close(),
                    $this->dic->language()->txt('lti_exit'),
                    ilLTIViewGUI::getInstance()->getExitLink()
                ));
            }
        )->withPriority(self::PRIORITY);
    }

    public function getTitleModification(CalledContexts $screen_context_stack): ?TitleModification
    {
        $exit_mode = $this->isExitMode($screen_context_stack);

        return $this->globalScreen()->layout()->factory()->title()->withModification(
            static fn(?string $title): string => $exit_mode
                ? ilLTIViewGUI::getInstance()->getTitleForExitPage()
                : ilLTIViewGUI::getInstance()->getTitle()
        )->withPriority(self::PRIORITY);
    }

    /**
     * The path above the launched object is not the business of the platform.
     */
    public function getBreadCrumbsModification(CalledContexts $screen_context_stack): ?BreadCrumbsModification
    {
        $context_id = ilLTIViewGUI::getInstance()->getContextId();

        return $this->globalScreen()->layout()->factory()->breadcrumbs()->withModification(
            function (?Breadcrumbs $breadcrumbs) use ($context_id): ?Breadcrumbs {
                if ($breadcrumbs === null || $context_id === 0) {
                    return $breadcrumbs;
                }
                $items = $breadcrumbs->getItems();
                foreach ($items as $index => $item) {
                    if (preg_match('/(ref_id=|_)' . $context_id . '(\D|$)/', (string) $item->getAction())) {
                        return $this->dic->ui()->factory()->breadcrumbs(array_slice($items, $index));
                    }
                }
                return $breadcrumbs;
            }
        )->withPriority(self::PRIORITY);
    }

    public function getFooterModification(CalledContexts $screen_context_stack): ?FooterModification
    {
        return $this->globalScreen()->layout()->factory()->footer()->withModification(
            static fn(?Footer $footer): ?Footer => null
        )->withPriority(self::PRIORITY);
    }

    private function isExitMode(CalledContexts $screen_context_stack): bool
    {
        return $screen_context_stack->current()->getAdditionalData()->is(ilLTIViewGUI::GS_EXIT_MODE, true);
    }
}
