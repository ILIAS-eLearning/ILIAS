<?php namespace ILIAS\GlobalScreen\Scope\MainMenu\Collector\Renderer;

use ILIAS\GlobalScreen\Scope\MainMenu\Factory\isItem;
use ILIAS\UI\Component\Component;

/**
 * Class TypeRenderer
 *
 * Every Type should have a renderer, if you won't provide on in your
 * TypeInformation, a BaseTypeRenderer is used.
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
interface TypeRenderer
{

    /**
     * @param isItem $item
     *
     * @param bool   $with_async_content
     *
     * @return Component
     */
    public function getComponentForItem(isItem $item, bool $with_async_content = false) : Component;
}
