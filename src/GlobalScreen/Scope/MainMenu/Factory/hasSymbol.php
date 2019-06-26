<?php namespace ILIAS\GlobalScreen\Scope\MainMenu\Factory;

use ILIAS\UI\Component\Symbol\Symbol;

/**
 * Interface hasSymbol
 *
 * Methods for Entries with Symbols
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
interface hasSymbol
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
