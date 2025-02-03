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
 *   Multi-Select without options
 *
 * expected output: >
 *   ILIAS shows an empty field with the following text: "No options". Clicking "Save" will result into following output:
 *
 *   Array
 *   (
 *       [empty] =>
 *   )
 * ---
 */
function empty_options()
{
    global $DIC;
    $ui = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();
    $request = $DIC->http()->request();

    $multi = $ui->input()->field()
        ->multiselect("No options", []);

    $form = $ui->input()->container()->form()->standard('#', ['empty' => $multi]);

    if ($request->getMethod() == "POST") {
        try {
            $form = $form->withRequest($request);
            $result = $form->getData();
        } catch (\InvalidArgumentException $e) {
            $result = "No result. Probably, the other form was used.";
        }
    } else {
        $result = "No result yet.";
    }

    return
        "<pre>" . print_r($result, true) . "</pre><br/>" .
        $renderer->render($form);
}
