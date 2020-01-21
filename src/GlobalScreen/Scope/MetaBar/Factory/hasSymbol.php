<?php namespace ILIAS\GlobalScreen\Scope\MetaBar\Factory;

use ILIAS\UI\Component\Symbol\Symbol;

/**
 * Interface hasSymbol
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
interface hasSymbol extends isItem
{

    /**
     * @param Symbol $symbol
     *
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
}
