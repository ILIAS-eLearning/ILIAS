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

namespace ILIAS\UI\examples\Input\Field\SwitchableGroup;

/**
 * ---
 * description: >
 *   Example showing how a dependent group (aka sub form) might be attached to a radio.
 *
 * expected output: >
 *   ILIAS shows three radio button groups titled "Pick One", "Pick One*" and "Again, Pick One" with a byline each.
 *   According to your selection of the radio button three input fields, one input field with a standard text or no
 *   input field will be displayed. The second option in the third group is already activated.
 *   Please select some radio buttons ("Pick One*" is required), fill out the input fields and click "Save". Afterwards
 *   ILIAS will display following content in the box above:
 *
 *   Array
 *   (
 *      [switchable_group] => Array
 *      (
 *          [0] => 1
 *          [1] => Array
 *          (
 *              [field_1_1] => Text 1
 *              [field_1_2] => Text 2
 *              [field_1_3] => DateTimeImmutable Object
 *              (
 *                  [date] => 2022-08-01 00:00:00.000000
 *                  [timezone_type] => 3
 *                  [timezone] => Europe/Berlin
 *              )
 *          )
 *      )
 *      [switchable_group_required] => Array
 *      (
 *          [0] => g2
 *          [1] => Array
 *          (
 *              [field_2_1] => Verpflichtender Text
 *          )
 *      )
 *      [switchable_group_preset] => Array
 *      (
 *          [0] => g3
 *          [1] => Array
 *          (
 *          )
 *      )
 *   )
 * ---
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
