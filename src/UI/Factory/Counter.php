<?php

/* Copyright (c) 2015 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Factory;

/**
 * This is how the factory for UI elements looks. This should provide access
 * to all UI elements at some point.
 */
interface Counter {
    /**
     * Description
     *  * Purpose: The Status counter is used to display information about the
     *    total number of some items like users active on the system or total
     *    amount of comments.
     *  * Composition: The Status Counter is a non-obstrusive Counter.
     *
     * Rules:
     *  * The Status Counter MUST be displayed on the lower right of the item
     *    it accompanies.
     *  * The Status Counter SHOULD have a non-obstrusive background color,
     *    such as grey.
     *
     * @param   int         $amount
     * @throws  \InvalidArgumentException   if $amount is not an int.
     * @return  \ILIAS\UI\Element\Counter
     */
    public function status($amount);

    /**
     * Description
     *  * Purpose: Novelty Counters inform users about the arrival or creation
     *    of new items of the kind indicated.
     *  * Composition: A Novelty Counter is an obtrusive counter.
     *  * Effect: They count down / disappear as soon as the change has been
     *    consulted by the user.
     *
     * Context:
     *  * Novelty Counters are found in the Mail in the Top Navigation.
     *  * Novelty Counters indicate new Comments.
     *
     * Rules:
     *  * There MUST be a way for the user to consult the changes indicated
     *    by the counter. After the consultation, the Novelty Counter SHOULD
     *    disappear or the number it contains is reduced by one. Depending on
     *    the content, the reduced number might be added in an additional
     *    Status Counter.
     *  * The Novelty Counter MUST be displayed on the top at the 'end of the
     *    line' in reading direction of the item it accompanies. This would be
     *    top right for latin script and top left for arabic script.
     *  * The Novelty Counter SHOULD have an obstrusive background color, such
     *    as red or orange.
     *  * The Novelty Counter MAY be used with the Status Counter.
     *
     * @param   int         $amount
     * @throws  \InvalidArgumentException   if $amount is not an int.
     * @return  \ILIAS\UI\Element\Counter
     */
    public function novelty($amount);
}
