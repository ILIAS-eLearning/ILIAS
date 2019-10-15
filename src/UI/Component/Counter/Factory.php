<?php
/* Copyright (c) 2015, 2016 Richard Klees <richard.klees@concepts-and-training.de> Extended GPL, see docs/LICENSE */
namespace ILIAS\UI\Component\Counter;

/**
 * This is how the factory for UI elements looks. This should provide access
 * to all UI elements at some point.
 */
interface Factory
{
    /**
     * ---
     * description:
     *   purpose: >
     *       The Status counter is used to display information about the
     *       total number of some items like users active on the system or total
     *       number of comments.
     *   composition: >
     *       The Status Counter is a non-obstrusive Counter.
     *   effect: >
     *       Status Counters convey information, they are not interactive.
     *
     * context:
     *   - The Status Counter is used in the ‘Who is online?’ Tool.
     * rules:
     *   style:
     *       1: >
     *          The Status Counter MUST be displayed on the lower right of the item
     *          it accompanies.
     *       2: >
     *          The Status Counter SHOULD have a non-obstrusive background color,
     *          such as grey.
     * ---
     *
     * @param   int         $number
     * @return  \ILIAS\UI\Component\Counter\Counter
     */
    public function status($number);

    /**
     * ---
     * description:
     *   purpose: >
     *       Novelty counters inform users about the arrival or creation of new items of the kind indicated
     *       by the accompanying glyph.
     *   composition: >
     *       A Novelty Counter is an obtrusive counter.
     *   effect: >
     *      They count down / disappear as soon as the change has been consulted
     *      by the user.
     *
     * context:
     *   - Novelty Counters are found in the Mail in the Top Navigation.
     *   - Novelty Counters indicate new Comments.
     *
     * rules:
     *   usage:
     *       1: The Novelty Counter MAY be used with the Status Counter.
     *   interaction:
     *       2: >
     *          There MUST be a way for the user to consult the changes indicated
     *          by the counter.
     *       3: >
     *          After the consultation, the Novelty Counter SHOULD disappear or
     *          the number it contains is reduced by one.
     *       4: >
     *          Depending on the content, the reduced number MAY be added in
     *          an additional Status Counter.
     *   style:
     *       5: >
     *          The Novelty Counter MUST be displayed on the top at the 'end of
     *          the line' in reading direction of the item it accompanies. This
     *          would be top right for latin script and top left for arabic script.
     *       6: >
     *          The Novelty Counter SHOULD have an obstrusive background color,
     *          such as red or orange.
     * ---
     *
     * @param   int         $number
     * @return  \ILIAS\UI\Component\Counter\Counter
     */
    public function novelty($number);
}
