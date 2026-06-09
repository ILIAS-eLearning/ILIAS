<?php

declare(strict_types=1);

namespace ILIAS\UI\examples\Input\Field\LengthOfTime;

use DateInterval;
use ILIAS\UI\Component\Input\Field\LengthOfTimeFieldPatterns;
use ILIAS\UI\Implementation\Component\Input\Field\LengthOfTime;

/**
 * ---
 * description: >
 *   This example shows how to create and render a basic input field and attach it to a form.
 *   It does not contain any data processing.
 *
 * expected output: >
 *   ILIAS shows an input field titled "Basic Input". You can enter letters and numbers.
 * ---
 */
function base()
{
    global $DIC;
    $ui = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();

    $time_interval = DateInterval::createFromDateString("1 hour 37 minutes");
    $field_pattern = LengthOfTimeFieldPatterns::hoursMinutes;

    $length_of_time_field = $ui->input()->field()->lengthOfTime("Session duration", null, $field_pattern)
                        ->withValue(["hours" => 1, "minutes" => 97]);
    $form = $ui->input()->container()->form()->standard(
        "#",
        [
            0 => $length_of_time_field
        ],
    );

    return $renderer->render($form);
}
