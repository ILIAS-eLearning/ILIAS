<?php namespace ILIAS\GlobalScreen\Scope\MainMenu\Factory;

use ILIAS\UI\Component\Symbol\Symbol;

/**
 * Trait hasSymbolTrait
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
trait hasSymbolTrait
{
    /**
     * @var Symbol
     */
    protected $symbol;

    /**
     * @inheritDoc
     */
    public function withSymbol(Symbol $symbol) : hasSymbol
    {
        // bugfix mantis 25526: make aria labels mandatory
        if (($symbol instanceof Glyph\Glyph && $symbol->getAriaLabel() === "") ||
            ($symbol instanceof Icon\Icon && $symbol->getLabel() === "")) {
            throw new \LogicException("the symbol's aria label MUST be set to ensure accessibility");
        }

        $clone = clone $this;
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
        return $this->symbol instanceof Symbol;
    }
}
