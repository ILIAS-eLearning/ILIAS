<?php namespace ILIAS\GlobalScreen\Scope\Tool\Factory;

use Closure;

/**
 * Class supportsTerminating
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
interface supportsTerminating
{

    /**
     * @param Closure $callback which is called, when a user explicitly
     *                          terminates  a Tool via the GUI. This callback
     *                          is called asynchronously.
     *
     *
     * @return supportsTerminating|Tool
     */
    public function withTerminatedCallback(Closure $callback) : supportsTerminating;


    /**
     * @return Closure|null
     */
    public function getTerminatedCallback() : ?Closure;


    /**
     * @return bool
     */
    public function hasTerminatedCallback() : bool;
}
