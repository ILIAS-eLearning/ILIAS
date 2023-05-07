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
namespace ILIAS\GlobalScreen\Scope\MainMenu\Collector\Renderer;

use ILIAS\GlobalScreen\Collector\Renderer\isSupportedTrait;
use ILIAS\GlobalScreen\Scope\MainMenu\Factory\isItem;
use ILIAS\UI\Component\Component;
use ILIAS\GlobalScreen\Scope\MainMenu\Factory\Item\Link;
use ILIAS\UI\Component\MainControls\Slate\Slate;

/**
 * Class LinkListItemRenderer
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class LinkListItemRenderer extends BaseTypeRenderer
{
    use MakeSlateAsync, SlateSessionStateCode {
        MakeSlateAsync::hash insteadof SlateSessionStateCode;
        MakeSlateAsync::unhash insteadof SlateSessionStateCode;
    }
    use isSupportedTrait;

    /**
     * @inheritDoc
     */
    public function getComponentWithContent(isItem $item) : Component
    {
        $slate = $this->ui_factory->mainControls()->slate()->combined($item->getTitle(), $this->getStandardSymbol($item));
        /**
         * @var $link Link|Slate
         */
        foreach ($item->getLinks() as $link) {
            if (!$link->isVisible()) {
                continue;
            }
            $link = $this->ui_factory->link()->bulky($this->getStandardSymbol($link), $link->getTitle(), $this->getURI($link->getAction()));
            $slate = $slate->withAdditionalEntry($link);
        }

        return $slate;
    }
}
