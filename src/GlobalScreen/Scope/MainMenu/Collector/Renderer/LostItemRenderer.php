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

use ILIAS\GlobalScreen\Scope\MainMenu\Factory\isItem;
use ILIAS\UI\Component\Component;

/**
 * Class LostItemRenderer
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class LostItemRenderer extends BaseTypeRenderer
{
    /**
     * @inheritDoc
     */
    public function getComponentWithContent(isItem $item) : Component
    {
        /**
         * @var $item \ILIAS\GlobalScreen\Scope\MainMenu\Factory\Item\Lost
         */
        if ($item->hasChildren()) {
            $r = new TopParentItemRenderer();

            return $r->getComponentForItem($item);
        }

        return $this->ui_factory->button()->bulky($this->getStandardSymbol($item), "{$item->getTypeInformation()->getTypeNameForPresentation()}", "");
    }
}
