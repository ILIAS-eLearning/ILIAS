<?php

declare(strict_types=1);

namespace ILIAS\UI\examples\Input\Field\SwitchableGroup;

use ILIAS\UI\URLBuilder;

/**
 * ---
 * description: >
 *   Example showing how a dependent group (aka sub form) might be attached to a radio while being disabled.
 *
 * expected output: >
 *   ILIAS shows a group of two radio buttons titled "Pinned Switchable Group".
 *   Selecting a radio button is not possible, but inputs below the selected
 *   option will still work as usual.
 * ---
 */
function with_disabled_group_switch()
{
    global $DIC;
    $ui = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();
    $df = new \ILIAS\Data\Factory();
    $refinery = $DIC['refinery'];
    $request = $DIC->http()->request();
    $query = $DIC->http()->wrapper()->query();

    $here_uri = $df->uri($request->getUri()->__toString());
    $url_builder = new URLBuilder($here_uri);
    $example_namespace = ['input', 'switchable_group'];
    list($url_builder, $example_name) = $url_builder->acquireParameters($example_namespace, "example_name");
    $url_builder = $url_builder->withParameter($example_name, "pinned");

    $group1 = $ui->input()->field()->group(
        [
            $ui->input()->field()->text("Item 1.1", "Just some field"),
            $ui->input()->field()->text("Item 1.2", "Just some field")
        ],
        "Group 1"
    );
    $group2 = $ui->input()->field()->group(
        [
            $ui->input()->field()->text("Item 2.1", "Just some field"),
            $ui->input()->field()->text("Item 2.2", "Just some field")
        ],
        "Group 2"
    );

    $sg = $ui->input()->field()->switchableGroup(
        [$group1,$group2],
        "Pinned Switchable Group",
        "nothing to pick here."
    )
    ->withDisabledGroupSwitch(true)
    ->withValue(1);

    $form_action = $url_builder->buildURI()->__toString();
    $form = $ui->input()->container()->form()->standard($form_action, [$sg]);

    if ($query->has($example_name->getName())
        && $query->retrieve($example_name->getName(), $refinery->custom()->transformation(fn($v) => $v === 'pinned'))
    ) {
        $form = $form->withRequest($request);
        $result = $form->getData();
    } else {
        $result = "No result yet.";
    }

    return
        "<pre>" . htmlspecialchars(print_r($result, true), ENT_QUOTES) . "</pre><br/>" .
        $renderer->render($form);

}
