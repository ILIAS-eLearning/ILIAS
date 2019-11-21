<?php namespace ILIAS\GlobalScreen\Scope\Layout\MetaContent\Media;

/**
 * Class JsCollection
 *
 * @package ILIAS\Services\UICore\Page\Media
 */
class JsCollection extends AbstractCollection
{

    /**
     * @var Js[]
     */
    protected $path_storage = [];


    /**
     * @param Js $item
     */
    public function addItem(Js $item)
    {
        $basename = $this->stripPath($item->getContent());
        if (!array_key_exists($basename, $this->items)) {
            $this->storeItem($item);
        } else {
            $existing = $this->items[$basename];
            if (($existing instanceof Js) && $existing->getBatch() > $item->getBatch()) {
                $this->storeItem($item);
            }
        }
    }


    private function storeItem(
        js $item
    ) {
        $strip_path = $this->stripPath($item->getContent());
        $this->items[$strip_path] = $item;
        $this->path_storage[$strip_path] = $item->getBatch();
    }


    /**
     * @return Js[]
     */
    public function getItemsInOrderOfDelivery() : array
    {
        $ordered = [];
        foreach ($this->getItems() as $js) {
            $ordered['pos_' . (string) $js->getBatch()][] = $js;
        }
        ksort($ordered);
        $ordered_all = [];
        foreach ($ordered as $item) {
            foreach ($item as $js) {
                $ordered_all[] = $js;
            }
        }

        return $ordered_all;
    }
}