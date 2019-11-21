<?php

namespace ILIAS\GlobalScreen\Scope\MainMenu\Collector\Map;

use Closure;

/**
 * Class Walkable
 *
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 * @internal
 */
interface Walkable
{

    /**
     * @param Closure $c
     */
    public function walk(Closure $c) : void;
}
