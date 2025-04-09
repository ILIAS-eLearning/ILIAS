<?php

declare(strict_types=1);

namespace ILIAS\UI\examples\Item\Standard;

/**
 * ---
 * description: >
 *   Example for rendering a standard item with an lead avatar.
 *
 * expected output: >
 *   ILIAS shows two very similiar boxes including the following informations: A heading with a dummy text in small writings
 *   ("Lorem ipsum...") below. Beneath those you can see a fine line and more informations about "Last Login"
 *   and "Location". Additionally an action menu is displayed in the box on the right top. On the left side an avatar is
 *   displayed.
 * ---
 */
function with_lead_avatar()
{
    global $DIC;
    $f = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();
    $actions = $f->dropdown()->standard(array(
        $f->button()->shy("ILIAS", "https://www.ilias.de"),
        $f->button()->shy("GitHub", "https://www.github.com")
    ));
    $app_item1 = $f->item()->standard("Max Mustermann")
        ->withActions($actions)
        ->withProperties(array(
            "Last Login" => "24.11.2011",
            "Location" => "Hamburg"))
        ->withDescription("Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua.")
        ->withLeadAvatar($f->symbol()->avatar()->letter('mm'));
    $app_item2 = $f->item()->standard("Erika Mustermann")
        ->withActions($actions)
        ->withProperties(array(
            "Last Login" => "3.12.2018",
            "Location" => "Berlin"))
        ->withDescription("Lorem ipsum dolor sit amet, consetetur sadipscing elitr, sed diam nonumy eirmod tempor invidunt ut labore et dolore magna aliquyam erat, sed diam voluptua.")
        ->withLeadAvatar($f->symbol()->avatar()->picture('./assets/images/placeholder/no_photo_xsmall.jpg', 'demo.user'));
    return $renderer->render([$app_item1, $f->divider()->horizontal(), $app_item2]);
}
