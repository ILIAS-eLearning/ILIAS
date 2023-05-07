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
namespace ILIAS\GlobalScreen\Scope\Layout\MetaContent\Media;

/**
 * Class CssCollection
 * @package ILIAS\Services\UICore\Page\Media
 */
class CssCollection extends AbstractCollection
{
    /**
     * @param Css $item
     */
    public function addItem(Css $item) : void
    {
        $real_path = realpath(parse_url($item->getContent(), PHP_URL_PATH));
        foreach ($this->getItems() as $css) {
            if (realpath(parse_url($css->getContent(), PHP_URL_PATH)) === $real_path) {
                return;
            }
        }
        $this->items[] = $item;
    }

    /**
     * @return Css[]
     */
    public function getItemsInOrderOfDelivery() : array
    {
        return parent::getItemsInOrderOfDelivery();
    }
}
