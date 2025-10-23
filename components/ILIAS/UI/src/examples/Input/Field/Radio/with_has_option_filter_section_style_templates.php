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
 *   An example using the Radio Input with an Option Filter for selecting a section design from a content style.
 *   We use a heavily styled preview as the label.
 *
 * expected output: >
 *   A Radio with Search allowing to filter through mockups of content style sections.
 *   When expanded, there is a list of options and a search input field.
 *   When entering letters into the search input, only matching options remain visible.
 *   An option can be selected and will be pinned to the top of the list.
 *   A clear filter button resets the search input fields and reveals all options.
 *   When collapsed, the selected options are still being shown as a read-only preview.
 *   On screen readers, the number of filtered results is announced.
 * ---
 */
function with_has_option_filter_section_style_templates(): string
{
    //Step 1: Declare dependencies
    global $DIC;
    $ui = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();
    $DIC->ui()->mainTemplate()->addCss('./assets/ui-examples/css/radio_filterable_section_style.css');

    //Step 2: define the radio with options
    $template1 = <<<HTML
<span class="ilc_section_Card" style="min-height: auto;">Card</span>
HTML;

    $template2 = <<<HTML
<span class="ilc_section_Citation" style="min-height: auto;">Citation</span>
HTML;

    $template3 = <<<HTML
<span class="ilc_section_Example" style="min-height: auto;">Example</span>
HTML;

    $template4 = <<<HTML
<span class="ilc_section_Excursus" style="min-height: auto;">Excursus</span>
HTML;

    $options = array(
        "1" => $template1,
        "2" => $template2,
        "3" => $template3,
        "4" => $template4,
    );

    $single_select = $ui->input()->field()->radio("Content Style", "Edit and add more styles by using a custom content style.")
                    ->withHasOptionFilter(true);

    foreach ($options as $value => $label) {
        $single_select = $single_select->withOption((string) $value, $label);
    }

    $single_select = $single_select->withValue("2");

    //Step 3: define form and form actions
    $form = $ui->input()->container()->form()->standard('#', ['radio' => $single_select]);

    //Step 4: Render the radio with the enclosing form.
    return $renderer->render($form);
}
