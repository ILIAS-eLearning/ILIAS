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
use ILIAS\UI\Component\Component;

/**
 * Class BaseMetaBarItemRenderer
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class BaseMetaBarItemRenderer extends AbstractMetaBarItemRenderer implements MetaBarItemRenderer
{
    /**
     * @param isItem $item
     * @return Component
     */
    protected function getSpecificComponentForItem(isItem $item) : Component
    {
        return $this->ui->factory()->legacy("no renderer found");
    }
}
