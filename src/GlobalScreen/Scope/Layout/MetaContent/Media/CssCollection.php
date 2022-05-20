<?php namespace ILIAS\GlobalScreen\Scope\Layout\MetaContent\Media;

/******************************************************************************
 *
 * This file is part of ILIAS, a powerful learning management system.
 *
 * ILIAS is licensed with the GPL-3.0, you should have received a copy
 * of said license along with the source code.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 *      https://www.ilias.de
 *      https://github.com/ILIAS-eLearning
 *
 *****************************************************************************/
/**
 * Class CssCollection
 *
 * @package ILIAS\Services\UICore\Page\Media
 */
class CssCollection extends AbstractCollection
{

    /**
     * @param Css $item
     */
    public function addItem(Css $item)
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
