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

namespace ILIAS\GlobalScreen\Scope\MetaBar\Factory;

use ILIAS\GlobalScreen\Identification\IdentificationInterface;
use ILIAS\GlobalScreen\Scope\MetaBar\Collector\Renderer\TopLegacyItemRenderer;
use ILIAS\UI\Component\Legacy\Legacy;
use ILIAS\UI\Component\Symbol\Symbol;

/**
 * Class TopLegacyItem
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class TopLegacyItem extends AbstractBaseItem implements isItem, hasSymbol, hasTitle
{
    use \ILIAS\GlobalScreen\Scope\SymbolDecoratorTrait;

    /**
     * @var \ILIAS\UI\Component\Symbol\Symbol|null
     */
    protected $symbol;
    /**
     * @var string
     */
    protected $title = "";
    /**
     * @var \ILIAS\UI\Component\Legacy\Legacy|null
     */
    protected $content;

    /**
     * @inheritDoc
     */
    public function __construct(IdentificationInterface $provider_identification)
    {
        parent::__construct($provider_identification);
        $this->renderer = new TopLegacyItemRenderer();
    }

    /**
     * @inheritDoc
     */
    public function withSymbol(Symbol $symbol) : hasSymbol
    {
        $clone = clone($this);
        $clone->symbol = $symbol;

        return $clone;
    }

    /**
     * @inheritDoc
     */
    public function getSymbol() : Symbol
    {
        return $this->symbol;
    }

    /**
     * @inheritDoc
     */
    public function hasSymbol() : bool
    {
        return ($this->symbol instanceof Symbol);
    }

    /**
     * @inheritDoc
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

    public function withLegacyContent(Legacy $content) : self
    {
        $clone = clone $this;
        $clone->content = $content;

        return $clone;
    }

    /**
     * @return Legacy
     */
    public function getLegacyContent() : Legacy
    {
        return $this->content;
    }

    /**
     * @return bool
     */
    public function hasLegacyContent() : bool
    {
        return ($this->content instanceof Legacy);
    }
}
