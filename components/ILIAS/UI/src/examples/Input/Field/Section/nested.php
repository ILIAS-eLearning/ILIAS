<?php

declare(strict_types=1);

namespace ILIAS\UI\examples\Input\Field\Section;

/**
 * ---
 * description: >
 *   Example showing how sections can be nested inside one another.
 *
 * expected output: >
 *   The headline html tags should make this nested structure clear to screen readers (h2, h3, h4).
 *   The nested sections should be visibly recognizable as being a sub-section of the parent section.
 * ---
 */
function nested()
{
    // Step 0: Declare dependencies
    global $DIC;
    $ui = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();

    // Step 1: Define inputs for the innermost section
    $text_input = $ui->input()->field()->text("Text Input", "Enter some text here.");
    $multi_select = $ui->input()->field()->multiselect(
        "Multi-Select",
        [
            "1" => "Option 1",
            "2" => "Option 2",
            "3" => "Option 3",
        ],
        "Choose one or more options"
    );

    $inner_section = $ui->input()->field()->section(
        [$text_input, $multi_select],
        "Inner Section",
        "This is the innermost section."
    )->withRequired(true);

    // Step 2: Define inputs for the middle section
    $dropdown = $ui->input()->field()->select(
        "Dropdown",
        [
            "1" => "Choice 1",
            "2" => "Choice 2",
            "3" => "Choice 3",
        ],
        "Select a single choice"
    );

    $middle_section = $ui->input()->field()->section(
        [$dropdown, $inner_section],
        "Middle Section",
        "This section contains the inner section and a dropdown."
    );

    // Step 3: Define the outer section
    $number_input = $ui->input()->field()->numeric("Numeric Input", "Enter a number.");
    $outer_section = $ui->input()->field()->section(
        [$number_input, $middle_section],
        "Outer Section",
        "This is the top-level section containing all other sections."
    );

    // Step 4: Define the form and attach the outer section
    $form = $ui->input()->container()->form()->standard("#", [$outer_section]);

    // Step 5: Render the form (no submission or processing, purely for display)
    return $renderer->render($form);
}
