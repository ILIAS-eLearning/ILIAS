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
use ILIAS\UI\Component\Legacy\Legacy;
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
            ?Legacy $content
        ): ?Legacy {
            $ui = $this->dic->ui();
            return $ui->factory()->legacy(
                $ui->renderer()->render($content) . self::$content
            );
        })->withLowPriority();
    }

    public function getTitleModification(CalledContexts $screen_context_stack): ?TitleModification
    {
        /** @var $modification TitleModification */
        $modification = $this->globalScreen()->layout()->factory()->title()->withModification(
            fn (?string $content): ?string => self::$title
        )->withLowPriority();

        return $modification;
    }

    public function getShortTitleModification(CalledContexts $screen_context_stack): ?ShortTitleModification
    {
        /** @var $modification ShortTitleModification */
        $modification = $this->globalScreen()->layout()->factory()->short_title()->withModification(
            fn (?string $content): ?string => self::$short_title
        )->withLowPriority();

        return $modification;
    }

    public function getViewTitleModification(CalledContexts $screen_context_stack): ?ViewTitleModification
    {
        /** @var $modification ViewTitleModification */
        $modification = $this->globalScreen()->layout()->factory()->view_title()->withModification(
            fn (?string $content): ?string => $this->buildTabTitle() . self::$view_title
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
            if (($tab['dir_text'] ?? false) === false) {
                $tab_title = $this->dic->language()->txt($tab['text']);
            } else {
                $tab_title = $tab['text'] ?? '';
            }
            $tab_title .= ': ';
            return $tab_title;
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
                array_filter($subtabs, static function (array $subtab): bool {
                    return $subtab['activate'] ?? false;
                })
            )[0]['id'] ?? '';

            if ($active_subtab === '' && isset($subtabs[0])) {
                $active_subtab = $subtabs[0]['id']; // if no tab is active, use the first one
            }
            $subtab_title = $tab_looper($subtabs, $active_subtab);
        }

        return $subtab_title . $tab_title;
    }


    public function getFooterModification(CalledContexts $screen_context_stack): ?FooterModification
    {
        return $this->globalScreen()->layout()->factory()->footer()->withModification(function (?Footer $footer): ?Footer {
            $f = $this->dic->ui()->factory();

            $links = [];
            // ILIAS Version and Text
            $ilias_version = ILIAS_VERSION;
            $text = "powered by ILIAS (v{$ilias_version})";

            // Imprint
            $base_class = ($this->dic->http()->wrapper()->query()->has(\ilCtrlInterface::PARAM_BASE_CLASS)) ?
                $this->dic->http()->wrapper()->query()->retrieve(
                    \ilCtrlInterface::PARAM_BASE_CLASS,
                    $this->dic->refinery()->kindlyTo()->string()
                ) : null;

            if ($base_class !== \ilImprintGUI::class && \ilImprint::isActive()) {
                $imprint_title = $this->dic->language()->txt("imprint");
                $imprint_url = \ilLink::_getStaticLink(0, "impr");
                $links[] = $f->link()->standard($imprint_title, $imprint_url);
            }

            // system support contacts
            if (($system_support_url = \ilSystemSupportContactsGUI::getFooterLink()) !== '') {
                $system_support_title = \ilSystemSupportContactsGUI::getFooterText();
                $links[] = $f->link()->standard($system_support_title, $system_support_url);
            }

            // output translation link
            if (\ilObjLanguageAccess::_checkTranslate() && !\ilObjLanguageAccess::_isPageTranslation()) {
                $translation_url = \ilObjLanguageAccess::_getTranslationLink();
                $translation_title = $this->dic->language()->txt('translation');
                $links[] = $f->link()->standard($translation_title, $translation_url)->withOpenInNewViewport(true);
            }

            // accessibility control concept
            if (($accessibility_control_url = \ilAccessibilityControlConceptGUI::getFooterLink()) !== '') {
                $accessibility_control_title = \ilAccessibilityControlConceptGUI::getFooterText();
                $links[] = $f->link()->standard($accessibility_control_title, $accessibility_control_url);
            }

            // report accessibility issue
            if (($accessibility_report_url = \ilAccessibilitySupportContactsGUI::getFooterLink()) !== '') {
                $accessibility_report_title = \ilAccessibilitySupportContactsGUI::getFooterText();
                $links[] = $f->link()->standard($accessibility_report_title, $accessibility_report_url);
            }

            $footer = $f->mainControls()->footer($links, $text);

            $footer = $this->dic['legalDocuments']->modifyFooter($footer);

            if (self::$perma_link !== "") {
                $footer = $footer->withPermanentURL(new URI(self::$perma_link));
            }

            return $footer;
        });
    }
}
