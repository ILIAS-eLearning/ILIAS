<?php declare(strict_types=1);

/* Copyright (c) 2015 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Component\Counter;

use ILIAS\UI\Component\Component;

/**
 * This tags a counter object.
 */
interface Counter extends Component
{
    // Types of counters:
    public const NOVELTY = "novelty";
    public const STATUS = "status";

    /**
     * Get the type of the counter.
     *
     * @return	string	One of the counter types.
     */
    public function getType() : string;

    /**
     * Get the number on the counter.
     */
    public function getNumber() : int;
}
