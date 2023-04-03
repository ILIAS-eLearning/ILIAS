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

/**
 * Class TopParentItemRenderer
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class TopParentItemRenderer extends BaseTypeRenderer
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
        $f = $this->ui_factory;
        $slate = $f->mainControls()->slate()->combined($item->getTitle(), $this->getStandardSymbol($item));
        /**
         * @var $child isItem
         */
        foreach ($item->getChildren() as $child) {
            $component = $child->getTypeInformation()->getRenderer()->getComponentForItem($child, false);
            if ($this->isComponentSupportedForCombinedSlate($component)) {
                $slate = $slate->withAdditionalEntry($component);
            }
        }

        return $slate;
    }
}
