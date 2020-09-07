<?php namespace ILIAS\GlobalScreen\Scope\MainMenu\Factory\Item;

use ILIAS\GlobalScreen\Scope\MainMenu\Factory\AbstractChildItem;
use ILIAS\GlobalScreen\Scope\MainMenu\Factory\hasTitle;
use InvalidArgumentException;

/**
 * Class LinkList
 *
 * @package ILIAS\GlobalScreen\MainMenu\Item
 */
class LinkList extends AbstractChildItem implements hasTitle
{

    /**
     * @var string
     */
    protected $title = '';
    /**
     * @var Link[]
     */
    protected $links;


    /**
     * @param string $title
     *
     * @return Link
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
     * @param array|callable $links
     *
     * @return LinkList
     */
    public function withLinks($links) : LinkList
    {
        if (is_callable($links)) {
            $links = $links();
            if (!is_array($links)) {
                throw new InvalidArgumentException("withLinks only accepts arrays of Links or a callable providing them");
            }
        }
        foreach ($links as $link) {
            if (!$link instanceof Link) {
                throw new InvalidArgumentException("withLinks only accepts arrays of Links or a callable providing them");
            }
        }
        $clone = clone($this);
        $clone->links = $links;

        return $clone;
    }


    /**
     * @return Link[]
     */
    public function getLinks() : array
    {
        return $this->links;
    }
}
