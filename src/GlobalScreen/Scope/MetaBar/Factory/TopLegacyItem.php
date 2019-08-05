<?php namespace ILIAS\GlobalScreen\Scope\MetaBar\Factory;

use ILIAS\UI\Component\Legacy\Legacy;
use ILIAS\UI\Component\Symbol\Glyph\Glyph;
use ILIAS\UI\Component\Symbol\Symbol;

/**
 * Class TopLegacyItem
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class TopLegacyItem extends AbstractBaseItem implements isItem, hasSymbol, hasTitle
{

    /**
     * @var Glyph
     */
    protected $glyph;
    /**
     * @var string
     */
    protected $title = "";
    /**
     * @var Legacy
     */
    protected $content = null;


    /**
     * @inheritDoc
     */
    public function withSymbol(Symbol $symbol) : hasSymbol
    {
        $clone = clone($this);
        $clone->glyph = $symbol;

        return $clone;
    }


    /**
     * @inheritDoc
     */
    public function getSymbol() : Symbol
    {
        return $this->glyph;
    }


    /**
     * @inheritDoc
     */
    public function hasSymbol() : bool
    {
        return ($this->glyph instanceof Symbol);
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


    /**
     * @param Legacy $content
     *
     * @return TopLegacyItem
     */
    public function withLegacyContent(Legacy $content) : TopLegacyItem
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
