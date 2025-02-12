<?php

declare(strict_types=1);

namespace ILIAS\UI\examples\Input\Field\SwitchableGroup;

/**
 * ---
 * description: >
 *   Example showing two Switchable Group Fields provided with existing value(s).
 *
 * expected output: >
 *   ILIAS shows two Switchable Group Fields. The first (above) one has the "Group 2" option
 *   selected with the "Item 2" Field having NO value. The second (below) one has the "Group 2"
 *   option selected with the "Item 2" Field having "some existing text value" as a value.
 * ---
 */
function with_value(): string
{
    global $DIC;
    $factory = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();

    $group_one = $factory->input()->field()->group([$factory->input()->field()->text("Item 1", "Just some field")], "Group 1");
    $group_two = $factory->input()->field()->group([$factory->input()->field()->text("Item 2", "Just some field")], "Group 2");

    $switchable_group = $factory->input()->field()->switchableGroup(
        [$group_one, $group_two],
        "Switchable Group with existing value(s)",
    );

    // tells the input only which option is selected
    $direct_value = $switchable_group->withValue(1);

    // tells the input which option is selected and what value(s) it has
    $nested_value = $switchable_group->withValue([1, ['some existing text value']]);

    $form = $factory->input()->container()->form()->standard('#', [$direct_value, $nested_value]);

    return $renderer->render($form);
}
