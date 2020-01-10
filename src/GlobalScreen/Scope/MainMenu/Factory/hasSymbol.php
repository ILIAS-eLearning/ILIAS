<?php namespace ILIAS\GlobalScreen\Scope\MainMenu\Factory;

use Closure;
use ILIAS\UI\Component\Symbol\Symbol;

/**
 * Interface hasSymbol
 * Methods for Entries with Symbols
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
interface hasSymbol extends isItem
{

    /**
     * @param Symbol $symbol
     * @return hasSymbol
     */
    public function withSymbol(Symbol $symbol) : hasSymbol;

    /**
     * @return Symbol
     */
    public function getSymbol() : Symbol;

    /**
     * @return bool
     */
    public function hasSymbol() : bool;

    /**
     * @param Closure $symbol_decorator
     * @return hasSymbol
     */
    public function addSymbolDecorator(Closure $symbol_decorator) : hasSymbol;

    /**
     * @return Closure|null
     */
    public function getSymbolDecorator() : ?Closure;
}
