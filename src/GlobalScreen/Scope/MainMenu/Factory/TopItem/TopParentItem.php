<?php namespace ILIAS\GlobalScreen\Scope\MainMenu\Factory\TopItem;

use ILIAS\GlobalScreen\Scope\MainMenu\Factory\AbstractParentItem;
use ILIAS\GlobalScreen\Scope\MainMenu\Factory\hasSymbol;
use ILIAS\GlobalScreen\Scope\MainMenu\Factory\hasSymbolTrait;
use ILIAS\GlobalScreen\Scope\MainMenu\Factory\hasTitle;
use ILIAS\GlobalScreen\Scope\MainMenu\Factory\isTopItem;
use ILIAS\GlobalScreen\Scope\MainMenu\Factory\SymbolDecoratorTrait;
use ILIAS\GlobalScreen\Scope\MainMenu\Factory\supportsAsynchronousLoading;

/**
 * Class TopParentItem
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class TopParentItem extends AbstractParentItem implements isTopItem, hasTitle, hasSymbol, supportsAsynchronousLoading
{
    use SymbolDecoratorTrait;
    use hasSymbolTrait;

    /**
     * @var string
     */
    protected $title = '';
    /**
     * @var bool
     */
    protected $supports_async_loading = false;

    /**
     * @param string $title
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

    public function withSupportsAsynchronousLoading(bool $supported) : supportsAsynchronousLoading
    {
        $clone = clone($this);
        $clone->supports_async_loading = $supported;

        return $clone;
    }

    public function supportsAsynchronousLoading() : bool
    {
        return $this->supports_async_loading;
    }
}
