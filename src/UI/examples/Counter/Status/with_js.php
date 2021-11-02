<?php declare(strict_types=1);

namespace ILIAS\UI\examples\Counter\Status;

/**
 * Note, counters also offer an interface for manipulations through JS.
 * Checkout: src/UI/templates/js/Counter/counter.js for a complete spec.
 *
 *
 * Example Usage:
 * //Step 1: Get the counter Object
 * var counter = il.UI.counter.getCounterObject($some_jquery_object);
 * //Step 2: Do stuff with the counter Object
 * var novelty_count = counter.setNoveltyCount(3).getNoveltyCount(); //novelty count should be 3
 * novelty_count = counter.setNoveltyToStatus().getNoveltyCount(); //novelty count should be 0, status count 3
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
