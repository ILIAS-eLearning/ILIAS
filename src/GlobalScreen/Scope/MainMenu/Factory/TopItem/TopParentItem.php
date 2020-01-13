<?php namespace ILIAS\GlobalScreen\Scope\MainMenu\Factory\TopItem;

use ILIAS\GlobalScreen\Scope\MainMenu\Factory\AbstractParentItem;
use ILIAS\GlobalScreen\Scope\MainMenu\Factory\hasTitle;
use ILIAS\GlobalScreen\Scope\MainMenu\Factory\isTopItem;

/**
 * Class TopParentItem
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class TopParentItem extends AbstractParentItem implements isTopItem, hasTitle
{

    /**
     * @var string
     */
    protected $title = '';
    /**
     * @var string
     */
    protected $icon_path = '';


    /**
     * @param string $title
     *
     * @return TopParentItem
     */
    public function withTitle(string $title) : hasTitle
    {
        $clone = clone($this);
        $clone->title = $title;

        return $clone;
    }


    /**
     * @inheritDoc
     */
    public function getTitle() : string
    {
        return $this->title;
    }
}
