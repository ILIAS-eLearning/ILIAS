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

use ILIAS\GlobalScreen\Identification\IdentificationInterface;
use ILIAS\GlobalScreen\Scope\MetaBar\Provider\AbstractStaticMetaBarProvider;
use ILIAS\GlobalScreen\Scope\MetaBar\Provider\StaticMetaBarProvider;
use ILIAS\UI\Implementation\Component\Button\Bulky as BulkyButton;
use ILIAS\UI\Implementation\Component\Link\Bulky as BulkyLink;

class ilHelpMetaBarProvider extends AbstractStaticMetaBarProvider
{
    use ilHelpDisplayed;

    private function getId(): IdentificationInterface
    {
        return $this->if->identifier('help');
    }

    public function getMetaBarItems(): array
    {
        global $DIC;

        $mb = $this->globalScreen()->metaBar();

        $f = $DIC->ui()->factory();

        $title = $DIC->language()->txt("help");

        if ($this->showHelpTool()) {
            // position should be 0, see bug #26794
            $item = $mb->topLinkItem($this->getId())
                       ->addComponentDecorator(static function (ILIAS\UI\Component\Component $c): ?ILIAS\UI\Component\Component {
                           if ($c instanceof BulkyButton || $c instanceof BulkyLink) {
                               return $c->withAdditionalOnLoadCode(static function (string $id): string {
                                   return "$('#$id').on('click', function() {
                                    console.log('trigger help slate');
                                    $('body').trigger('il-help-toggle-slate');
                                    return false;
                                })";
                               });
                           }
                           return null;
                       })
                       ->withSymbol($f->symbol()->glyph()->help())
                       ->withTitle($title)
                       ->withPosition(0);

            return [$item];
        }

        return [];
    }
}
