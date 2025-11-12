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

namespace ILIAS\UI\examples\Input\Field\MultiSelect;

/**
 * ---
 * description: >
 *   An example showing a Multi-Select Input with an Option Filter
 *
 * expected output: >
 *   A Multi-Select with an Option Filter that can be expanded, collapsed and filtered.
 *   When expanded, there is a list of options and a search input field.
 *   When entering letters into the search input, only matching options remain visible.
 *   Multiple options can be selected and will be pinned to the top of the list.
 *   A clear filter button resets the search input fields and reveals all options.
 *   When collapsed, the selected options are still being shown as a read-only preview.
 *   On screen readers, the number of filtered results is announced.
 * ---
 */
function with_has_option_filter(): string
{
    global $DIC;
    $ui = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();

    $json_data = file_get_contents(ILIAS_ABSOLUTE_PATH . '/public/assets/ui-examples/misc/multiselect_searchable_data.json');
    $data = json_decode($json_data, true);

    $multi_select = $ui->input()->field()->multiselect("Group Members", $data)
                    ->withHasOptionFilter();

    $form = $ui->input()->container()->form()->standard('#', [$multi_select]);

    return $renderer->render($form);
}
