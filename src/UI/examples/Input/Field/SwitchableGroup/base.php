<?php

declare(strict_types=1);

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
        "Switchable Group number two",
        "with byline"
    );
    $group3 = $ui->input()->field()->group([], 'No items in this group', 'but a byline');

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
