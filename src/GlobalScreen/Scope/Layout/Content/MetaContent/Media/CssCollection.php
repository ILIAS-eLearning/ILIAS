<?php namespace ILIAS\GlobalScreen\Scope\Layout\Content\MetaContent\Media;

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
        $basename = basename(parse_url($item->getContent(), PHP_URL_PATH));
        foreach ($this->getItems() as $css) {
            if (basename(parse_url($css->getContent(), PHP_URL_PATH)) === $basename) {
                return;
            }
        }
        $this->items[] = $item;
    }


    /**
     * @return Css[]
     */
    public function getItems() : array
    {
        return parent::getItems();
    }


    /**
     * @return Css[]
     */
    public function getItemsInOrderOfDelivery() : array
    {
        return parent::getItemsInOrderOfDelivery();
    }
}