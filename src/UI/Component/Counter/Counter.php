<?php

/* Copyright (c) 2015 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Component\Counter;

/**
 * This tags a counter object.
 */
interface Counter extends \ILIAS\UI\Component\Component
{
    // Types of counters:
    const NOVELTY = "novelty";
    const STATUS = "status";

    /**
     * Get the type of the counter.
     *
     * @return	string	One of the counter types.
     */
    public function getType();

    /**
     * Get the number on the counter.
     *
     * @return	int
     */
    public function getNumber();
}
