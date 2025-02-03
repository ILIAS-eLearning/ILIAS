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

namespace ILIAS\UI\examples\Symbol\Icon\Standard;

/**
 * ---
 * description: >
 *   Example for rendering a standard icon.
 *
 * expected output: >
 *   ILIAS shows a standard icon in three different sizes.
 * ---
 */
function default_icon()
{
    global $DIC;
    $f = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();

    $buffer = array();

    $ico = $f->symbol()->icon()->standard('someExample', 'Example');
    $ico = $ico->withAbbreviation('E');

    $buffer[] = $renderer->render($ico)
        . ' Small Example with Short Abbreviation';

    $buffer[] = $renderer->render($ico->withSize('medium'))
        . ' Medium Example with Short Abbreviation';

    $buffer[] = $renderer->render($ico->withSize('large'))
        . ' Large Example with Short Abbreviation';


    $ico = $f->symbol()->icon()->standard('someOtherExample', 'Example');
    $ico = $ico->withAbbreviation('LA');

    $buffer[] = $renderer->render($ico->withSize('small'))
        . ' Small Example with Long Abbreviation';

    $buffer[] = $renderer->render($ico->withSize('medium'))
        . ' Medium Example with Long Abbreviation';

    $buffer[] = $renderer->render($ico->withSize('large'))
        . ' Large Example with Long Abbreviation';


    return implode('<br><br>', $buffer);
}
