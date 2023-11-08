<?php

declare(strict_types=1);

namespace ILIAS\UI\examples\Item\Notification;

function include_aggregates()
{
    global $DIC;
    $f = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();
    $refinery = $DIC->refinery();
    $request_wrapper = $DIC->http()->wrapper()->query();

    $close_url = $_SERVER['REQUEST_URI'] . '&aggregate_closed=true';

    //If closed, an ajax request is fired to the set close_url
    if (
        $request_wrapper->has('aggregate_closed') &&
        $request_wrapper->retrieve('aggregate_closed', $refinery->kindlyTo()->bool())
    ) {
        //Do Some Magic needed to be done, when this item is closed.
        exit;
    }

    //Some generic notification Items
    $generic_icon1 = $f->symbol()->icon()->standard("cal", "generic");
    $generic_title1 = $f->link()->standard("Aggregate of Item", "#");
    $generic_item1 = $f->item()->notification($generic_title1, $generic_icon1)
                                       ->withDescription("Is shown when top item is clicked")
                                       ->withProperties(["Property 1" => "Content 1", "Property 2" => "Content 2"])
                                       ->withActions(
                                           $f->dropdown()->standard([
                                               $f->button()->shy("Link to ilias.de", "https://www.ilias.de"),
                                               $f->button()->shy("Link to github", "https://www.github.com")
                                           ])
                                       )
                                        ->withCloseAction($close_url);

    $generic_title2 = $f->link()->standard("Item", "just_opens_the_list_of_aggregates");
    $generic_item2 = $f->item()->notification($generic_title2, $generic_icon1)
                       ->withDescription("Notification Item with Aggregates")
                       ->withProperties(["Property 1" => "Content 1", "Property 2" => "Content 2"])
                       ->withAggregateNotifications([$generic_item1, $generic_item1]);


    return $renderer->render($generic_item2);
}
