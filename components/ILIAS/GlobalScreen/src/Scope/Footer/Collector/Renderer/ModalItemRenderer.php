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

namespace ILIAS\GlobalScreen\Scope\Footer\Collector\Renderer;

use ILIAS\UI\Component\Component;
use ILIAS\GlobalScreen\Scope\Footer\Factory\isItem;
use ILIAS\GlobalScreen\Scope\Footer\Factory\Modal;

/**
 * @author Fabian Schmid <fabian@sr.solutions>
 */
class ModalItemRenderer extends AbstractFooterItemRenderer
{
    protected function getSpecificComponentForItem(isItem $item): Component|array
    {
        /** @var Modal $item */
        return $this->ui->factory()->button()->shy($item->getTitle(), '#')
                        ->withOnClick(
                            $item->getModal()->getShowSignal()
                        );
    }

}
