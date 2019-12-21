<?php

namespace ILIAS\GlobalScreen\Scope\MainMenu\Collector\Map;

use Closure;

/**
 * Class Filterable
 *
 * @author  Fabian Schmid <fs@studer-raimann.ch>
 * @internal
 */
interface Filterable
{

    /**
     * @param Closure $c
     */
    public function filter(Closure $c) : void;
}
