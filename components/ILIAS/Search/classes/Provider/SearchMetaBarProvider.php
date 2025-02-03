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
    private function getId(): IdentificationInterface
    {
        return $this->if->identifier('search');
    }


    /**
     * @inheritDoc
     */
    public function getAllIdentifications(): array
    {
        return [$this->getId()];
    }


    /**
     * @inheritDoc
     */
    public function getMetaBarItems(): array
    {
        $content = function () {
            $main_search = new ilMainMenuSearchGUI();
            $html = "";

            // user interface plugin slot + default rendering
            $uip = new ilUIHookProcessor(
                "components/ILIAS/MainMenu",
                "main_menu_search",
                array("main_menu_gui" => $this, "main_menu_search_gui" => $main_search)
            );
            if (!$uip->replaced()) {
                $html = $main_search->getHTML();
            }

            return $this->dic->ui()->factory()->legacy()->content($uip->getHTML($html))->withAdditionalOnLoadCode(
                fn($id) => 'il.SearchMainMenu.init()'
            );
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
                    return $this->dic->rbac()->system()->checkAccess('search', ilSearchSettings::_getSearchSettingRefId());
                }
            );

        return [$item];
    }
}
