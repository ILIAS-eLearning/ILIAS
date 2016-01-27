<?php

/* Copyright (c) 2015 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Factory;

/**
 * This is how the factory for UI elements looks. This should provide access
 * to all UI elements at some point.
 */
interface Counter {
    /**
     * @description
     *  * Purpose: The Status counter is used to display information about the
     *    total number of some items like users active on the system or total
     *    amount of comments.
     *  * ...
     *
     * @param   int         $amount
     * @throws  \InvalidArgumentException   if $amount is not an int.
     * @return  \ILIAS\UI\Element\Counter
     */
    public function status($amount);

    /**
     * @description
     *
     * @param   int         $amount
     * @throws  \InvalidArgumentException   if $amount is not an int.
     * @return  \ILIAS\UI\Element\Counter
     */
    public function novelty($amount);
}
