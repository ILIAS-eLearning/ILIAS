<?php

/* Copyright (c) 2015 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Element;

/**
 * This tags a counter object.
 */
interface Counter extends \ILIAS\UI\Element {
    /**
     * Get the type of the counter.
     *
     * @return  CounterType
     */
    public function type();

    /**
     * Get the number on the counter.
     *
     * @return  int
     */
    public function amount();
}

// Tags for the different types of counters.
class CounterType {};
final class NoveltyCounterType extends CounterType {};
final class StatusCounterType extends CounterType {};
