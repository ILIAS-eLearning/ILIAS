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

namespace ILIAS\GlobalScreen\Scope\Tool\Collector\Renderer;

use ILIAS\GlobalScreen\Collector\Renderer\DecoratorApplierTrait;
use ILIAS\GlobalScreen\Scope\MainMenu\Collector\Renderer\BaseTypeRenderer;
use ILIAS\GlobalScreen\Scope\MainMenu\Collector\Renderer\SlateSessionStateCode;
use ILIAS\GlobalScreen\Scope\MainMenu\Factory\isItem;
use ILIAS\GlobalScreen\Scope\Tool\Factory\Tool;
use ILIAS\UI\Component\Component;

/**
 * @author Fabian Schmid <fabian@sr.solutions>
 */
class ToolItemRenderer extends BaseTypeRenderer
{
    use SlateSessionStateCode;
    use DecoratorApplierTrait;


    public function getComponentForItem(isItem $item, bool $with_content = false): Component
    {
        /**
         * @var $item Tool
         */

        $symbol = $this->getStandardSymbol($item);

        $slate = $this->ui_factory->mainControls()->slate()->legacy($item->getTitle(), $symbol, $item->getContent());

        $slate = $this->addOnloadCode($slate, $item);

        return $this->applyComponentDecorator($slate, $item);
    }
}
