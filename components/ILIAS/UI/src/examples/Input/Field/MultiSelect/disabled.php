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
 *   This example shows an disabled Multi Select Input.
 *
 * expected output: >
 *   ILIAS shows four disabled checkboxes, two of them checked.
 *   You cannot operate any of the checkboxes.
 * ---
 */
function disabled()
{
    global $DIC;
    $ui = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();

    //define options.
    $options = array(
        "1" => "Pick 1",
        "2" => "Pick 2",
        "3" => "Pick 3",
        "4" => "Pick 4",
    );

    $multi = $ui->input()->field()->multiselect("Take your picks", $options, "This is the byline text")
        ->withValue(['2','4'])
        ->withDisabled(true);

    $form = $ui->input()->container()->form()->standard('#', ['multi' => $multi]);
    return $renderer->render($form);
}
