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

namespace ILIAS\UI\examples\Listing\Property;

/**
 * ---
 * expected output: >
 *   ILIAS shows the rendered Component.
 * ---
 */
function base()
{
    global $DIC;
    $f = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();

    $props = $f->listing()->property()
        ->withProperty('Title', 'Some Title')
        ->withProperty('number', '7')
        ->withProperty(
            'status',
            $renderer->render(
                $f->symbol()->icon()->custom('./assets/images/learning_progress/in_progress.svg', 'incomplete'),
            ) . ' in progress',
            false
        );

    $props2 = $props->withItems([
        ['a', "1"],
        ['y', "25", false],
        ['link', $f->link()->standard('Goto ILIAS', 'http://www.ilias.de')]
    ]);

    return $renderer->render([
            $props,
            $f->divider()->horizontal(),
            $props2
    ]);
}
