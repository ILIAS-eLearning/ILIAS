<?php
function with_aggregates()
{
    global $DIC;
    $f = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();
    $close_url = $_SERVER['REQUEST_URI'];

    //Some generic notification Items
    $generic_icon1 = $f->symbol()->icon()->standard("cal", "generic");
    $generic_title1 = $f->link()->standard("Generic 1", "link_to_generic_repo");
    $generic_item1 = $f->item()->notification($generic_title1, $generic_icon1)
                                       ->withDescription("Some description.")
                                       ->withProperties(["Property 1" => "Content 1", "Property 2" => "Content 2"])
                                       ->withActions(
                                           $f->dropdown()->standard([
                                               $f->button()->shy("Possible Action of this Item", "https://www.ilias.de"),
                                               $f->button()->shy("Other Possible Action of this Item", "https://www.github.com")
                                           ])
                                       )
                                        ->withCloseAction($close_url);

    $generic_title2 = $f->link()->standard("Generic 2", "just_opens_the_list_of_aggregates");
    $generic_item2 = $f->item()->notification($generic_title2, $generic_icon1)
                                       ->withDescription("Some description describing the aggregates attached.")
                                       ->withProperties(["Property 1" => "Content 1", "Property 2" => "Content 2"])
                                       ->withAggregateNotifications([$generic_item1, $generic_item1]);


    return $renderer->render($generic_item2);
}
