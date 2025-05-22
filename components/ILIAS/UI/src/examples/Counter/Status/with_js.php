<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

declare(strict_types=1);

namespace ILIAS\UI\examples\Counter\Status;

/**
 * ---
 * description: >
 *   Base example for rendering a JS status counter
 *
 * note: >
 *   Counters also offer an interface for manipulations through JS.
 *   Checkout src/UI/templates/js/Counter/counter.js for a complete spec.
 *
 * expected output: >
 *   ILIAS shows different JS-exmaples which can get altered directly:
 *   - Click once to raise the status counter to 10.
 *   - Click once to raise the novelty counter to 1.
 *   - Click once to change the novelty counter to a status counter.
 *   - Click once to change the status counter 3 to a novelty counter 7.
 * ---
 */
function with_js()
{
    global $DIC;
    $f = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();

    //Note that both counters have to be present to perform JS actions on them
    $like = $f->symbol()->glyph()->love("#")
        ->withCounter($f->counter()->novelty(3))
        ->withCounter($f->counter()->status(0));

    $set_status_button = $f->button()->bulky($like, "Set Status Counter to 10 on click.", "#")
        ->withAdditionalOnLoadCode(
            function ($id) {
                return "
                $(\"#$id\").click(function() { 
                    il.UI.counter.getCounterObject($(this)).setStatusTo(10);
                });";
            }
        );

    $increment_novelty_button = $f->button()->bulky($like, "Increment Novelty Counter by on click", "#")
        ->withAdditionalOnLoadCode(
            function ($id) {
                return "
                $(\"#$id\").click(function() { 
                    il.UI.counter.getCounterObject($(this)).incrementNoveltyCount(1);
                });";
            }
        );

    $set_novelty_count_to_status_button = $f->button()->bulky($like, "Set Novelty Count to status on click", "#")
        ->withAdditionalOnLoadCode(
            function ($id) {
                return "
                $(\"#$id\").click(function() { 
                    il.UI.counter.getCounterObject($(this)).setTotalNoveltyToStatusCount(1);
                });";
            }
        );

    //What will the value of Status be after click?
    $combined_button = $f->button()->bulky($like, "Some chained actions", "#")
        ->withAdditionalOnLoadCode(
            function ($id) {
                return "
                $(\"#$id\").click(function() { 
                    var counter = il.UI.counter.getCounterObject($(this));
                    counter.setNoveltyTo(3);
                    counter.setStatusTo(3);
                    counter.incrementStatusCount(1);
                    counter.setTotalNoveltyToStatusCount();
                    console.log(
                        counter.getStatusCount()
                    );
                });";
            }
        );

    return $renderer->render([$set_status_button,$increment_novelty_button,$set_novelty_count_to_status_button,$combined_button]);
}
