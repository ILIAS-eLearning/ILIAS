<?php namespace ILIAS\GlobalScreen\Scope\MainMenu\Factory\Item;

use ILIAS\GlobalScreen\Scope\MainMenu\Factory\AbstractChildItem;
use ILIAS\GlobalScreen\Scope\MainMenu\Factory\hasTitle;
use ILIAS\GlobalScreen\Scope\MainMenu\Factory\isChild;

/**
 * Class Separator
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class Separator extends AbstractChildItem implements hasTitle, isChild
{

    /**
     * @var  bool
     */
    protected $visible_title = false;
    /**
     * @var string
     */
    protected $title = '';


    /**
     * @param string $title
     *
     * @return Separator
     */
    public function withTitle(string $title) : hasTitle
    {
        $clone = clone($this);
        $clone->title = $title;

        return $clone;
    }


    /**
     * @return string
     */
    public function getTitle() : string
    {
        return $this->title;
    }


    /**
     * @param bool $visible_title
     *
     * @return Separator
     */
    public function withVisibleTitle(bool $visible_title) : Separator
    {
        $clone = clone($this);
        $clone->visible_title = $visible_title;

        return $clone;
    }


    /**
     * @return bool
     */
    public function isTitleVisible() : bool
    {
        return $this->visible_title;
    }
}
