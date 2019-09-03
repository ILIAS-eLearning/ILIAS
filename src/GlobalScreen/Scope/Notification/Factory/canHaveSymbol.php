<?php namespace ILIAS\GlobalScreen\Scope\Notification\Factory;

use ILIAS\UI\Component\Symbol\Symbol;

/**
 * Interface canHaveSymbol
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
interface canHaveSymbol
{

    /**
     * @param Symbol $symbol
     *
     * @return canHaveSymbol
     */
    public function withSymbol(Symbol $symbol) : self;


    /**
     * @return bool
     */
    public function hasSymbol() : bool;


    /**
     * @return Symbol
     */
    public function getSymbol() : Symbol;
}
