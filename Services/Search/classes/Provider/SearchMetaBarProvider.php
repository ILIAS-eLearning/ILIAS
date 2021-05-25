<?php

namespace ILIAS\Search\Provider;

use ILIAS\GlobalScreen\Identification\IdentificationInterface;
use ILIAS\GlobalScreen\Scope\MetaBar\Provider\AbstractStaticMetaBarProvider;
use ILIAS\GlobalScreen\Scope\MetaBar\Provider\StaticMetaBarProvider;
use ilMainMenuSearchGUI;
use ilSearchSettings;
use ilUIHookProcessor;

/**
 * Class SearchMetaBarProvider
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class SearchMetaBarProvider extends AbstractStaticMetaBarProvider implements StaticMetaBarProvider
{

    /**
     * @return IdentificationInterface
     */
    private function getId() : IdentificationInterface
    {
        return $this->if->identifier('search');
    }


    /**
     * @inheritDoc
     */
    public function getAllIdentifications() : array
    {
        return [$this->getId()];
    }


    /**
     * @inheritDoc
     */
    public function getMetaBarItems() : array
    {
        $content = function () {
            $main_search = new ilMainMenuSearchGUI();
            $html = "";

            // user interface plugin slot + default rendering
            $uip = new ilUIHookProcessor(
                "Services/MainMenu",
                "main_menu_search",
                array("main_menu_gui" => $this, "main_menu_search_gui" => $main_search)
            );
            if (!$uip->replaced()) {
                $html = $main_search->getHTML();
            }

            return $this->dic->ui()->factory()->legacy($uip->getHTML($html));
        };

        $mb = $this->globalScreen()->metaBar();

        $item = $mb
            ->topLegacyItem($this->getId())
            ->withLegacyContent($content())
            ->withSymbol($this->dic->ui()->factory()->symbol()->glyph()->search())
            ->withTitle($this->dic->language()->txt("search"))
            ->withPosition(1)
            ->withAvailableCallable(
                function () {
                    return (bool) $this->dic->rbac()->system()->checkAccess('search', ilSearchSettings::_getSearchSettingRefId());
                }
            );

        return [$item];
    }
}
