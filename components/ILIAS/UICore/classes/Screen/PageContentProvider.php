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

namespace ILIAS\UICore;

use ILIAS\Data\URI;
use ILIAS\GlobalScreen\Scope\Layout\Factory\ContentModification;
use ILIAS\GlobalScreen\Scope\Layout\Factory\FooterModification;
use ILIAS\GlobalScreen\Scope\Layout\Provider\AbstractModificationProvider;
use ILIAS\GlobalScreen\ScreenContext\Stack\CalledContexts;
use ILIAS\GlobalScreen\ScreenContext\Stack\ContextCollection;
use ILIAS\UI\Component\Legacy\Content;
use ILIAS\UI\Component\MainControls\Footer;
use ILIAS\GlobalScreen\Scope\Layout\Factory\TitleModification;
use ILIAS\GlobalScreen\Scope\Layout\Factory\ShortTitleModification;
use ILIAS\GlobalScreen\Scope\Layout\Factory\ViewTitleModification;

/**
 * @author Fabian Schmid <fs@studer-raimann.ch>
 * @author Michael Jansen <mjansen@databay.de>
 * @author Maximilian Becker <mbecker@databay.de>
 */
class PageContentProvider extends AbstractModificationProvider
{
    private static string $content = "";
    private static string $perma_link = "";
    private static string $title = "";
    private static string $short_title = "";
    private static string $view_title = "";

    public static function setContent(string $content): void
    {
        self::$content = $content;
    }

    public static function setTitle(string $title): void
    {
        self::$title = $title;
    }

    public static function setShortTitle(string $short_title): void
    {
        self::$short_title = $short_title;
    }

    public static function setViewTitle(string $view_title): void
    {
        self::$view_title = $view_title;
    }

    public static function setPermaLink(string $perma_link): void
    {
        self::$perma_link = $perma_link;
    }

    public function isInterestedInContexts(): ContextCollection
    {
        return $this->context_collection->main();
    }

    public function getContentModification(CalledContexts $screen_context_stack): ?ContentModification
    {
        return $this->globalScreen()->layout()->factory()->content()->withModification(function (
            ?Content $content
        ): ?Content {
            $ui = $this->dic->ui();
            return $ui->factory()->legacy()->content(
                $ui->renderer()->render($content) . self::$content
            );
        })->withLowPriority();
    }

    public function getTitleModification(CalledContexts $screen_context_stack): ?TitleModification
    {
        /** @var $modification TitleModification */
        $modification = $this->globalScreen()->layout()->factory()->title()->withModification(
            fn(?string $content): ?string => self::$title
        )->withLowPriority();

        return $modification;
    }

    public function getShortTitleModification(CalledContexts $screen_context_stack): ?ShortTitleModification
    {
        /** @var $modification ShortTitleModification */
        $modification = $this->globalScreen()->layout()->factory()->short_title()->withModification(
            fn(?string $content): ?string => self::$short_title
        )->withLowPriority();

        return $modification;
    }

    public function getViewTitleModification(CalledContexts $screen_context_stack): ?ViewTitleModification
    {
        /** @var $modification ViewTitleModification */
        $modification = $this->globalScreen()->layout()->factory()->view_title()->withModification(
            fn(?string $content): ?string => $this->buildTabTitle() . self::$view_title
        )->withLowPriority();

        return $modification;
    }

    /**
     * @description This method was introduced due to A11y problems, see https://mantis.ilias.de/view.php?id=31534.
     * This is definitely only a workaround, but since this is currently the only way to implement it, it is just introduced...
     * We keep all the logic within this method because we don't want this to become common or even used elsewhere.
     * Hence certain things as anonymous functions...
     */
    private function buildTabTitle(): string
    {
        // This anonymous function generates a translated title from a "tab" array.
        // in some cases the tabs are already translated (dir_text = true), in others not...
        $tab_title_generator = function (array $tab): string {
            $tab_title = ($tab['dir_text'] ?? false) === false ? $this->dic->language()->txt($tab['text']) : $tab['text'] ?? '';
            return $tab_title . ': ';
        };

        // we only know the 'id' of the active tab and don't want to rely on the array index, so we
        // loop over tabs or subtabs to find the "right" one
        $tab_looper = static function (array $tabs, string $active_tab) use ($tab_title_generator): string {
            $tab_title = '';
            foreach ($tabs as $tab) {
                if ($tab['id'] === $active_tab) {
                    $tab_title = $tab_title_generator($tab);
                    break;
                }
            }
            return $tab_title;
        };

        // TABS
        $tabs = $this->dic->tabs()->target; // this only works because target is currently public...
        $active_tab = $this->dic->tabs()->getActiveTab();
        if ($active_tab === '' && isset($tabs[0])) {
            $active_tab = $tabs[0]['id']; // if no tab is active, use the first one
        }

        $tab_title = $tab_looper($tabs, $active_tab);

        // SUBTABS
        $subtab_title = '';
        $subtabs = $this->dic->tabs()->sub_target; // this only works because subtarget is currently public...
        if (count($subtabs) > 1) { // we only need to do something if there are more than one subtabs
            $active_subtab = array_values(
                array_filter($subtabs, static fn(array $subtab): bool => $subtab['activate'] ?? false)
            )[0]['id'] ?? '';

            if ($active_subtab === '' && isset($subtabs[0])) {
                $active_subtab = $subtabs[0]['id']; // if no tab is active, use the first one
            }
            $subtab_title = $tab_looper($subtabs, $active_subtab);
        }

        return $subtab_title . $tab_title;
    }

    /**
     * @deprecated this is needed as long as the PageContentProvider is the only place which stores the permalink
     */
    public static function getPermaLink(): string
    {
        return self::$perma_link;
    }
}
