<?php

declare(strict_types=1);
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

namespace ILIAS\GlobalScreen\Scope\MainMenu\Collector\Renderer;

use ILIAS\GlobalScreen\Scope\MainMenu\Factory\isItem;
use ILIAS\UI\Component\Component;

/**
 * Class SeparatorItemRenderer
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class SeparatorItemRenderer extends BaseTypeRenderer
{
    /**
     * @inheritDoc
     */
    public function getComponentWithContent(isItem $item): Component
    {
        $horizontal = $this->ui_factory->divider()->horizontal();
        if ($item->getTitle()) {
            $horizontal = $horizontal->withLabel($item->getTitle());
        }
        return $horizontal;
    }
}
