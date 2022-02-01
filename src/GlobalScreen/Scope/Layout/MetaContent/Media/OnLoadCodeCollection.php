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
 * Class OnLoadCodeCollection
 *
 * @package ILIAS\Services\UICore\Page\Media
 */
class OnLoadCodeCollection extends AbstractCollection
{

    /**
     * @param OnLoadCode $item
     */
    public function addItem(OnLoadCode $item)
    {
        $this->items[] = $item;
    }


    /**
     * @return OnLoadCode[]
     */
    public function getItemsInOrderOfDelivery() : array
    {
        return parent::getItemsInOrderOfDelivery();
    }
}
