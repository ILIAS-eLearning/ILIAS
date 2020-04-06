<?php namespace ILIAS\GlobalScreen\Scope\Layout\MetaContent\Media;

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
