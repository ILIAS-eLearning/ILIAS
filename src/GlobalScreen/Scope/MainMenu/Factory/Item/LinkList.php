<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

declare(strict_types=1);

namespace ILIAS\GlobalScreen\Scope\MainMenu\Factory\Item;

use ILIAS\GlobalScreen\Scope\MainMenu\Factory\AbstractChildItem;
use ILIAS\GlobalScreen\Scope\MainMenu\Factory\hasSymbol;
use ILIAS\GlobalScreen\Scope\MainMenu\Factory\hasSymbolTrait;
use ILIAS\GlobalScreen\Scope\MainMenu\Factory\hasTitle;
use ILIAS\GlobalScreen\Scope\MainMenu\Factory\isChild;
use ILIAS\GlobalScreen\Scope\MainMenu\Factory\isInterchangeableItem;
use ILIAS\GlobalScreen\Scope\MainMenu\Factory\isInterchangeableItemTrait;
use ILIAS\GlobalScreen\Scope\MainMenu\Factory\supportsAsynchronousLoading;
use ILIAS\GlobalScreen\Scope\SymbolDecoratorTrait;
use InvalidArgumentException;
use ReflectionFunction;
use ReflectionException;
use Generator;

/**
 * Class LinkList
 * @package ILIAS\GlobalScreen\MainMenu\Item
 */
class LinkList extends AbstractChildItem implements
    hasTitle,
    supportsAsynchronousLoading,
    hasSymbol,
    isInterchangeableItem,
    isChild
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
    protected $links = [];
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
     * @param array|callable|Generator $links
     */
    public function withLinks($links) : self
    {
        if (is_callable($links)) {
            try {
                $r = new ReflectionFunction($links);
                $links = $r->isGenerator() ? iterator_to_array($links()) : $links();
            } catch (ReflectionException $e) {
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
