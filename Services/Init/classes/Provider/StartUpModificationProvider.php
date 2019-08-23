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

            // Language-Selection
            $language_glyph = $factory->symbol()->glyph()->settings();
            $language_selection = $factory->mainControls()->slate()->combined('language_selection', $language_glyph);
            $missing_icon = $factory->symbol()->icon()->custom("./src/UI/examples/Layout/Page/Standard/question.svg", 'missing icon');

            $base = $this->getBaseURL();

            foreach ($this->dic->language()->getInstalledLanguages() as $lang_key) {
                $link = $this->appendUrlParameterString($base, "lang=" . $lang_key);
                $link = str_replace("?&", "?", $link);

                $language_name = $this->dic->language()->_lookupEntry($lang_key, "meta", "meta_l_" . $lang_key);
                $language_button = $factory->button()->bulky($missing_icon, $language_name, $link);

                $language_selection = $language_selection->withAdditionalEntry($language_button);
            }

            $metabar = $factory->mainControls()->metaBar();
            $metabar = $metabar->withAdditionalEntry('language_selection', $language_selection);

            return $metabar;
        })->withLowPriority();
    }


    private function appendUrlParameterString(string $existing_url, string $addition) : string
    {
        $url = (is_int(strpos($existing_url, "?")))
            ? $existing_url . "&" . $addition
            : $existing_url . "?" . $addition;

        return $url;
    }


    private function getBaseURL() : string
    {
        $base = substr($_SERVER["REQUEST_URI"], strrpos($_SERVER["REQUEST_URI"], "/") + 1);

        return preg_replace("/&*lang=[a-z]{2}&*/", "", $base);
    }
}
