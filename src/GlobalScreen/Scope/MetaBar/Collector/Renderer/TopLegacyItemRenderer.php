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

namespace ILIAS\GlobalScreen\Scope\MetaBar\Collector\Renderer;

use ILIAS\GlobalScreen\Scope\MetaBar\Factory\isItem;
use ILIAS\GlobalScreen\Scope\MetaBar\Factory\TopLegacyItem;
use ILIAS\UI\Component\Component;

/**
 * Class TopLegacyItemRenderer
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class TopLegacyItemRenderer extends AbstractMetaBarItemRenderer
{
    /**
     * @inheritDoc
     */
    protected function getSpecificComponentForItem(isItem $item) : Component
    {
        /**
         * @var $item TopLegacyItem
         */

        return $this->ui->factory()->mainControls()->slate()->legacy(
            $item->getTitle(),
            $this->buildIcon($item),
            $item->getLegacyContent()
        );
    }
}
