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

namespace ILIAS\UI\examples\Input\Field\Markdown;

/**
 * ---
 * expected output: >
 *   ILIAS shows the rendered Component.
 * ---
 */
use ilUIMarkdownPreviewGUI;

/**
 * ---
 * description: >
 *  The example shows how to create and render a basic markdown field and attach it to a form.
 *
 * expected output: >
 *   ILIAS shows the rendered Component.
 * ---
 */

function with_limits()
{
    global $DIC;

    // retrieve dependencies
    $md_renderer = new ilUIMarkdownPreviewGUI();
    $query_wrapper = $DIC->http()->wrapper()->query();
    $inputs = $DIC->ui()->factory()->input();
    $renderer = $DIC->ui()->renderer();
    $request = $DIC->http()->request();

    // declare form and input
    $markdown_input = $inputs->field()->markdown($md_renderer, 'Markdown Input', 'Just a markdown input.');
    $markdown_input = $markdown_input->withMinLimit(1)->withMaxLimit(20);
    $form = $inputs->container()->form()->standard('#', [$markdown_input]);

    // please use ilCtrl to generate an appropriate link target
    // and check it's command instead of this.
    if ('POST' === $request->getMethod()) {
        $form = $form->withRequest($request);
        $data = $form->getData();
    } else {
        $data = 'no results yet.';
    }

    return
        '<pre>' . print_r($data, true) . '</pre>' .
        $renderer->render($form);
}
