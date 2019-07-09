<?php namespace ILIAS\GlobalScreen\Scope\Layout\Factory;

use Closure;

/**
 * Class Modifier
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
interface Modifier
{

    /**
     * @return bool
     */
    public function isFinal() : bool;


    /**
     * @param Closure $closure
     *
     * @return Modifier
     */
    public function withModification(Closure $closure) : Modifier;


    /**
     * @return bool
     */
    public function hasValidModification() : bool;


    /**
     * @return Closure
     */
    public function getModification() : Closure;


    /**
     * @return string|null
     */
    public function getClosureFirstArgumentTypeOrNull() : ?string;


    /**
     * @return string
     */
    public function getClosureReturnType() : string;
}
