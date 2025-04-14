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

namespace ILIAS\UI\examples\Input\Field\Tag;

/**
 * ---
 * description: >
 *   The example shows how to create and render a basic tag input field and attach it to a
 *   form. This example does not contain any data processing.
 *
 * expected output: >
 *   ILIAS shows an input field titled "Orderable TagInput". The Tag, 'Interesting',
 *   'Boring', 'Animating' are already displayed and can be removed through clicking
 *   the "X". Tags can be sorted by dragging and dropping them in the
 * ---
 */
function base_with_orderable()
{
    /** @var \ILIAS\DI\Container $DIC */
    global $DIC;
    $ui = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();

    $tag_input = $ui->input()->field()->tag(
        'Orderable TagInput',
        ['Interesting', 'Boring', 'Animating', 'Repetitious'],
        'Just some tags'
    )->withValue(['Interesting', 'Boring', 'Animating'])
    ->withOrderable(true);

    $form = $ui->input()->container()->form()->standard('#', [$tag_input]);

    return  $renderer->render($form);
}
