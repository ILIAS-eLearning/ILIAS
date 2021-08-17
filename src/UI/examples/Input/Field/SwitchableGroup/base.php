<?php declare(strict_types=1);

namespace ILIAS\UI\examples\Input\Field\SwitchableGroup;

/**
 * Example showing how a dependant group (aka sub form) might be attached to a radio.
 */
function base()
{
    //Step 0: Declare dependencies
    global $DIC;
    $ui = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();
    $request = $DIC->http()->request();
    $data = new \ILIAS\Data\Factory();

    //Step 1: Define the groups (with their fields and a label each)
    $group1 = $ui->input()->field()->group(
        [
            "field_1_1" => $ui->input()->field()->text("Item 1.1", "Just some field"),
            "field_1_2" => $ui->input()->field()->text("Item 1.2", "Just some other field"),
            "field_1_3" => $ui->input()->field()->datetime("Item 1.3", "a date")->withFormat($data->dateFormat()->germanShort())
        ],
        "Switchable Group number one (with numeric key)"
    );
    $group2 = $ui->input()->field()->group(
        [
            "field_2_1" => $ui->input()->field()->text("Item 2", "Just another field")
                ->withValue('some val')
        ],
        "Switchable Group number two"
    );
    $group3 = $ui->input()->field()->group([], 'No items in this group');

    //Step 2: Switchable Group - one or the other:
    $sg = $ui->input()->field()->switchableGroup(
        [
            "1" => $group1,
            "g2" => $group2,
            "g3" => $group3
        ],
        "Pick One",
        "...or the other"
    );

    $form = $ui->input()->container()->form()->standard(
        '#',
        [
            'switchable_group' => $sg,
            'switchable_group_required' => $sg->withRequired(true),
            'switchable_group_preset' => $sg->withValue("g2")
                                      ->withLabel("Again, Pick One")
                                      ->withByline("... or the other. 
                                      Second option is selected by default here.")
        ]
    );

    //Step 3: implement some form data processing.
    if ($request->getMethod() == "POST") {
        $form = $form->withRequest($request);
        $result = $form->getData();
    } else {
        $result = "No result yet.";
    }

    //Step 4: Render.
    return
        "<pre>" . htmlspecialchars(print_r($result, true), ENT_QUOTES) . "</pre><br/>" .
        $renderer->render($form);
}
