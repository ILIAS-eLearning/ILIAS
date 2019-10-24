<?php
/**
 * With lead image in narrow_container
 */
function with_lead_image_in_narrow_container()
{
    global $DIC;
    $f = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();

    $image = $f->image()->responsive(
        "src/UI/examples/Image/Avatar.png",
        "Thumbnail Example"
    );

    $actions = $f->dropdown()->standard(array(
        $f->button()->shy("ILIAS", "https://www.ilias.de"),
        $f->button()->shy("GitHub", "https://www.github.com")
    ));

    $list_item1 = $f->item()->standard("Johnny Bravo with a very long name that should not fit")
        ->withActions($actions)
        ->withProperties(array(
            "Address" => "Main Street 44, 3012 Bern"))
        ->withDescription("[user1]")
        ->withLeadImage($image);

    $list_item2 = $f->item()->standard("Max Mustermann")
        ->withActions($actions)
        ->withProperties(array(
            "Address" => "Main Street 45, 3012 Bern"))
        ->withDescription("[user2]")
        ->withLeadImage($image);

    $list_item3 = $f->item()->standard("George Smith")
        ->withActions($actions)
        ->withProperties(array(
            "Address" => "Main Street 46, 3012 Bern"))
        ->withDescription("[user3]")
        ->withLeadImage($image);

    $std_list = $f->panel()->listing()->standard("", array(
        $f->item()->group("Your Contacts", array(
            $list_item1,
            $list_item2
        )),
        $f->item()->group("All Users", array(
            $list_item3
        ))
    ));

    return "<h3>List in il-narrow-content container</h3>" .
        "This concept is currently under discussion and not implemented by any container."
        . "<div class='il-narrow-content' style='max-width:300px;'>"
        . $renderer->render($std_list)
        . "</div>"
        . "<h3>Same list without il-narrow-content container</h3>"
        . $renderer->render($std_list);
}
