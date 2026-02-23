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

namespace ILIAS\UI\examples\Input\Field\Radio;

/**
 * ---
 * description: >
 *   An example showing a Radio Field with an Option Filter
 *
 * expected output: >
 *   A form with a Radio with Search that can be expanded, collapsed and filtered.
 *   When expanded, there is a list of options and a search input field.
 *   When entering letters into the search input, only matching options remain visible.
 *   An option can be selected and will be pinned to the top of the list.
 *   A clear filter button resets the search input fields and reveals all options.
 *   When collapsed, the selected options are still being shown as a read-only preview.
 *   On screen readers, the number of filtered results is announced.
 * ---
 */
function with_has_option_filter_email_templates(): string
{
    //Step 1: Declare dependencies
    global $DIC;
    $ui = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();

    //Step 2: define the radio with options
    $options = array(
        "1" => "Welcome (External Guest)",
        "2" => "Welcome (Office)",
        "3" => "Welcome (International)",
        "4" => "Reminder Expiration",
        "5" => "Reminder Deadline",
        "6" => "Passed",
        "7" => "Failed - Try Again",
        "8" => "Failed Permanently",
        "9" => "Notification for Trainer",
        "10" => "Notification for Leader",
        "11" => "Notification for Staff",
        "12" => "Hotel Booking Request",
        "13" => "Hotel Booking Information Participant",
        "14" => "Hotel Booking Information Trainer",
    );

    $radio = $ui->input()->field()->radio("Email Template", "Choose the wording for your email. You can add custom templates in the administration settings.");
    $radio = $radio->withHasOptionFilter(true);

    foreach ($options as $value => $label) {
        $radio = $radio->withOption((string) $value, $label);
    }

    //Step 3: define form and form actions
    $form = $ui->input()->container()->form()->standard('#', ['radio' => $radio]);

    //Step 4: Render the radio with the enclosing form.
    return $renderer->render($form);
}
