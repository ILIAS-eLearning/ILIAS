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
use ILIAS\GlobalScreen\Identification\IdentificationInterface;
use ILIAS\GlobalScreen\Identification\NullIdentification;
use ILIAS\GlobalScreen\Scope\MainMenu\Factory\AbstractBaseItem;
use ILIAS\GlobalScreen\Scope\MainMenu\Factory\hasAction;
use ILIAS\GlobalScreen\Scope\MainMenu\Factory\hasContent;
use ILIAS\GlobalScreen\Scope\MainMenu\Factory\hasSymbol;
use ILIAS\GlobalScreen\Scope\MainMenu\Factory\hasTitle;
use ILIAS\GlobalScreen\Scope\MainMenu\Factory\isChild;
use ILIAS\GlobalScreen\Scope\MainMenu\Factory\isItem;
use ILIAS\GlobalScreen\Scope\MainMenu\Factory\isParent;
use ILIAS\GlobalScreen\Scope\MainMenu\Factory\isTopItem;
use ILIAS\GlobalScreen\Scope\SymbolDecoratorTrait;
use ILIAS\UI\Component\Component;
use ILIAS\UI\Component\Symbol\Symbol;
use ILIAS\UI\Implementation\Component\Symbol\Glyph\Glyph;

/**
 * Class Lost
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class Lost extends AbstractBaseItem implements hasContent, isTopItem, isParent, isChild, hasTitle, hasAction, hasSymbol
{
    use SymbolDecoratorTrait;

    /**
     * @var mixed[]
     */
    private $children = [];
    /**
     * @var \ILIAS\GlobalScreen\Identification\IdentificationInterface
     */
    private $parent;
    /**
     * @var string
     */
    private $title = '';

    /**
     * @inheritDoc
     */
    public function __construct(IdentificationInterface $provider_identification)
    {
        parent::__construct($provider_identification);
        $this->parent = new NullIdentification();
    }

    /**
     * @inheritDoc
     */
    public function withTitle(string $title) : hasTitle
    {
        $this->title = $title;

        return $this;
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
    public function withContent(Component $ui_component) : hasContent
    {
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function withContentWrapper(Closure $content_wrapper) : hasContent
    {
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getContent() : Component
    {
        global $DIC;

        return $DIC->ui()->factory()->legacy("");
    }

    /**
     * @inheritDoc
     */
    public function withParent(IdentificationInterface $identification) : isItem
    {
        $this->parent = $identification;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function hasParent() : bool
    {
        return $this->parent instanceof isParent;
    }

    /**
     * @inheritDoc
     */
    public function getParent() : IdentificationInterface
    {
        return $this->parent;
    }

    /**
     * @inheritDoc
     */
    public function overrideParent(IdentificationInterface $identification) : isItem
    {
        $this->parent = $identification;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getChildren() : array
    {
        return $this->children;
    }

    /**
     * @inheritDoc
     * @param isChild[] $children
     */
    public function withChildren(array $children) : isParent
    {
        $this->children = $children;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function appendChild(isItem $child) : isParent
    {
        $this->children[] = $child;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function removeChild(isItem $child) : isParent
    {
        $this->children = array_filter($this->children, static function (isItem $item) use ($child) : bool {
            return $item->getProviderIdentification()->serialize() !== $child->getProviderIdentification()->serialize();
        });

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function hasChildren() : bool
    {
        return $this->children !== [];
    }

    /**
     * @inheritDoc
     */
    public function withAction(string $action) : hasAction
    {
        // noting to to
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getAction() : string
    {
        return "#";
    }

    /**
     * @inheritDoc
     */
    public function withIsLinkToExternalAction(bool $is_external) : hasAction
    {
        // noting to to
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function isLinkWithExternalAction() : bool
    {
        return false;
    }

    /**
     * @inheritDoc
     */
    public function withSymbol(Symbol $symbol) : hasSymbol
    {
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getSymbol() : Symbol
    {
        return new Glyph(Glyph::MORE, '');
    }

    /**
     * @inheritDoc
     */
    public function hasSymbol() : bool
    {
        return false;
    }
}
