<?php namespace ILIAS\UICore;

use ILIAS\Data\URI;
use ILIAS\GlobalScreen\Scope\Layout\Factory\ContentModification;
use ILIAS\GlobalScreen\Scope\Layout\Factory\FooterModification;
use ILIAS\GlobalScreen\Scope\Layout\Provider\AbstractModificationProvider;
use ILIAS\GlobalScreen\Scope\Layout\Provider\ModificationProvider;
use ILIAS\GlobalScreen\ScreenContext\Stack\CalledContexts;
use ILIAS\GlobalScreen\ScreenContext\Stack\ContextCollection;
use ILIAS\UI\Component\Legacy\Legacy;
use ILIAS\UI\Component\MainControls\Footer;

/**
 * Class ilPageContentProvider
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class PageContentProvider extends AbstractModificationProvider implements ModificationProvider
{

    /**
     * @var string
     */
    private static $content = "";
    /**
     * @var string
     */
    private static $perma_link = "";


    /**
     * @param string $content
     */
    public static function setContent(string $content) : void
    {
        self::$content = $content;
    }


    /**
     * @param string $perma_link
     */
    public static function setPermaLink(string $perma_link) : void
    {
        self::$perma_link = $perma_link;
    }


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
        return $this->globalScreen()->layout()->factory()->content()->withModification(function (Legacy $content) : Legacy {
            $ui = $this->dic->ui();

            return $ui->factory()->legacy($ui->renderer()->render($content) . self::$content);
        })->withLowPriority();
    }


    /**
     * @inheritDoc
     */
    public function getFooterModification(CalledContexts $screen_context_stack) : ?FooterModification
    {
        return $this->globalScreen()->layout()->factory()->footer()->withModification(function () : Footer {
            $f = $this->dic->ui()->factory();

            $links = [];
            // ILIAS Version and Text
            $ilias_version = $this->dic->settings()->get('ilias_version');
            $text = "powered by ILIAS (v{$ilias_version})";

            // Imprint
            if ($_REQUEST["baseClass"] !== "ilImprintGUI" && \ilImprint::isActive()) {
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
            if (\ilObjLanguageAccess::_checkTranslate() || !\ilObjLanguageAccess::_isPageTranslation()) {
                $translation_url = \ilObjLanguageAccess::_getTranslationLink();
                $translation_title = $this->dic->language()->txt('translation');
                $links[] = $f->link()->standard($translation_title, $translation_url);
            }

            $footer = $f->mainControls()->footer($links, $text);

            if (self::$perma_link !== "") {
                $footer = $footer->withPermanentURL(new URI(self::$perma_link));
            }

            return $footer;
        });
    }
}
