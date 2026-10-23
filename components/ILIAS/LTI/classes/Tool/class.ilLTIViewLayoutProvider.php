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
use ILIAS\GlobalScreen\Scope\Layout\Factory\ContentModification;
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
 * exit, and on the other pages of LTI without a login (ilLTIViewGUI::printPage()), neither bar is left.
 *
 * The provider looks at every page and changes only those of the LTI view, so that no initialisation has to
 * switch the page to the view beforehand.
 *
 * @author Saúl Díaz <sdiaz@surlabs.com>
 */
class ilLTIViewLayoutProvider extends AbstractModificationProvider
{
    private const int PRIORITY = 63;

    private const string FRAME_LINKS_CODE = 'if (window.self !== window.top) { const update = (root) => root.querySelectorAll(\'a[target="_top"], form[target="_top"]\').forEach((e) => e.setAttribute("target", "_self")); update(document); new MutationObserver(() => update(document)).observe(document.body, {childList: true, subtree: true}); }';

    public function isInterestedInContexts(): ContextCollection
    {
        return $this->context_collection->main();
    }

    /**
     * The content stays as it is; the page of a user who came from a platform gets the styles of the view
     * and of the platform.
     */
    public function getContentModification(CalledContexts $screen_context_stack): ?ContentModification
    {
        if (!$this->getView()->isActive()) {
            return null;
        }

        $this->dic->language()->loadLanguageModule('lti');

        $meta = $this->globalScreen()->layout()->meta();
        $meta->addCss('./assets/css/lti_view.css');
        $css = $this->getView()->getPostData()['launch_presentation_css_url'] ?? '';
        if ($css !== '') {
            $meta->addCss($css);
        }
        // inside the frame of the platform, links meant for the whole window stay in the frame
        $meta->addOnloadCode(self::FRAME_LINKS_CODE);

        return null;
    }

    public function getMainBarModification(CalledContexts $screen_context_stack): ?MainBarModification
    {
        if (!$this->isViewPage($screen_context_stack)) {
            return null;
        }
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
        if (!$this->isViewPage($screen_context_stack)) {
            return null;
        }
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
                    $this->getView()->getExitLink()
                ));
            }
        )->withPriority(self::PRIORITY);
    }

    public function getTitleModification(CalledContexts $screen_context_stack): ?TitleModification
    {
        if (!$this->isViewPage($screen_context_stack)) {
            return null;
        }
        $data = $screen_context_stack->current()->getAdditionalData();
        $page_title = $data->exists(ilLTIViewGUI::GS_PAGE_TITLE) ? (string) $data->get(ilLTIViewGUI::GS_PAGE_TITLE) : null;

        return $this->globalScreen()->layout()->factory()->title()->withModification(
            fn(?string $title): string => $page_title ?? $this->getView()->getTitle()
        )->withPriority(self::PRIORITY);
    }

    /**
     * The path above the launched object is not the business of the platform.
     */
    public function getBreadCrumbsModification(CalledContexts $screen_context_stack): ?BreadCrumbsModification
    {
        if (!$this->isViewPage($screen_context_stack)) {
            return null;
        }
        $context_id = $this->getView()->getContextId();

        return $this->globalScreen()->layout()->factory()->breadcrumbs()->withModification(
            function (?Breadcrumbs $breadcrumbs) use ($context_id): ?Breadcrumbs {
                if ($breadcrumbs === null || $context_id === 0) {
                    return $breadcrumbs;
                }
                $items = $breadcrumbs->getItems();
                foreach ($items as $index => $item) {
                    if (preg_match('/(ref_id=|_)' . $context_id . '(\D|$)/', $item->getAction())) {
                        return $this->dic->ui()->factory()->breadcrumbs(array_slice($items, $index));
                    }
                }
                return $breadcrumbs;
            }
        )->withPriority(self::PRIORITY);
    }

    public function getFooterModification(CalledContexts $screen_context_stack): ?FooterModification
    {
        if (!$this->isViewPage($screen_context_stack)) {
            return null;
        }

        return $this->globalScreen()->layout()->factory()->footer()->withModification(
            static fn(?Footer $footer): ?Footer => null
        )->withPriority(self::PRIORITY);
    }

    /**
     * The pages of a user who came from a platform, and the pages of LTI without a login.
     */
    private function isViewPage(CalledContexts $screen_context_stack): bool
    {
        return $this->getView()->isActive() || $this->isExitMode($screen_context_stack);
    }

    private function isExitMode(CalledContexts $screen_context_stack): bool
    {
        return $screen_context_stack->current()->getAdditionalData()->is(ilLTIViewGUI::GS_EXIT_MODE, true);
    }

    private function getView(): ilLTIViewGUI
    {
        $view = $this->dic['lti'];
        if (!$view instanceof ilLTIViewGUI) {
            throw new LogicException('The LTI view is not provided by the LTI component.');
        }

        return $view;
    }
}
