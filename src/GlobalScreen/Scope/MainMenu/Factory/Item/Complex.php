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

use Closure;
use ILIAS\GlobalScreen\Scope\MainMenu\Factory\AbstractChildItem;
use ILIAS\GlobalScreen\Scope\MainMenu\Factory\hasContent;
use ILIAS\GlobalScreen\Scope\MainMenu\Factory\hasSymbol;
use ILIAS\GlobalScreen\Scope\MainMenu\Factory\hasSymbolTrait;
use ILIAS\GlobalScreen\Scope\MainMenu\Factory\hasTitle;
use ILIAS\GlobalScreen\Scope\MainMenu\Factory\isChild;
use ILIAS\GlobalScreen\Scope\MainMenu\Factory\isInterchangeableItem;
use ILIAS\GlobalScreen\Scope\MainMenu\Factory\isInterchangeableItemTrait;
use ILIAS\GlobalScreen\Scope\MainMenu\Factory\supportsAsynchronousLoading;
use ILIAS\GlobalScreen\Scope\SymbolDecoratorTrait;
use ILIAS\UI\Component\Component;

/**
 * Class Complex
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class Complex extends AbstractChildItem implements
    hasContent,
    hasTitle,
    hasSymbol,
    supportsAsynchronousLoading,
    isInterchangeableItem,
    isChild
{
    use SymbolDecoratorTrait;
    use hasSymbolTrait;
    use isInterchangeableItemTrait;

    /**
     * @var \Closure|null
     */
    private $content_wrapper;
    /**
     * @var \ILIAS\UI\Component\Component|null
     */
    private $content;
    /**
     * @var string
     */
    private $title = '';
    /**
     * @var bool
     */
    private $supports_async_loading = false;

    /**
     * @inheritDoc
     */
    public function withContentWrapper(Closure $content_wrapper) : hasContent
    {
        $clone = clone($this);
        $clone->content_wrapper = $content_wrapper;

        return $clone;
    }

    /**
     * @inheritDoc
     */
    public function withContent(Component $ui_component) : hasContent
    {
        $clone = clone($this);
        $clone->content = $ui_component;

        return $clone;
    }

    /**
     * @inheritDoc
     */
    public function getContent() : Component
    {
        if ($this->content_wrapper !== null) {
            $wrapper = $this->content_wrapper;

            return $wrapper();
        }

        return $this->content;
    }

    /**
     * @param string $title
     * @return Complex
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
}
