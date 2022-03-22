<?php namespace ILIAS\GlobalScreen\Scope\MainMenu\Factory\Item;

use ILIAS\GlobalScreen\Scope\MainMenu\Factory\AbstractChildItem;
use ILIAS\GlobalScreen\Scope\MainMenu\Factory\hasSymbol;
use ILIAS\GlobalScreen\Scope\MainMenu\Factory\hasSymbolTrait;
use ILIAS\GlobalScreen\Scope\MainMenu\Factory\hasTitle;
use ILIAS\GlobalScreen\Scope\MainMenu\Factory\isChild;
use ILIAS\GlobalScreen\Scope\MainMenu\Factory\isInterchangeableItem;
use ILIAS\GlobalScreen\Scope\MainMenu\Factory\isInterchangeableItemTrait;
use ILIAS\GlobalScreen\Scope\MainMenu\Factory\supportsAsynchronousLoading;
use ILIAS\GlobalScreen\Scope\MainMenu\Factory\SymbolDecoratorTrait;
use InvalidArgumentException;

/**
 * Class LinkList
 * @package ILIAS\GlobalScreen\MainMenu\Item
 */
class LinkList extends AbstractChildItem implements hasTitle, supportsAsynchronousLoading, hasSymbol, isInterchangeableItem
{
    use SymbolDecoratorTrait;
    use hasSymbolTrait;
    use isInterchangeableItemTrait;

    /**
     * @var string
     */
    protected $title = '';
    /**
     * @var Link[]
     */
    protected $links;
    /**
     * @var bool
     */
    protected $supports_async_loading = false;

    /**
     * @param string $title
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
     * @param array|callable|\Generator $links
     * @return LinkList
     */
    public function withLinks($links) : LinkList
    {
        if (is_callable($links)) {
            try {
                $r = new \ReflectionFunction($links);
                if ($r->isGenerator()) {
                    $links = iterator_to_array($links());
                } else {
                    $links = $links();
                }
            } catch (\ReflectionException $e) {
                $links = false;
            }

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

    /**
     * @inheritDoc
     */
    public function withSupportsAsynchronousLoading(bool $supported) : supportsAsynchronousLoading
    {
        $clone = clone($this);
        $clone->supports_async_loading = $supported;

        return $clone;
    }

    /**
     * @inheritDoc
     */
    public function supportsAsynchronousLoading() : bool
    {
        return $this->supports_async_loading;
    }
    
    public function isVisible() : bool
    {
        $visible_links = 0;
        foreach ($this->getLinks() as $link) {
            if ($link->isVisible()) {
                $visible_links++;
            }
        }
        return $visible_links > 0 && parent::isVisible();
    }
    
}
