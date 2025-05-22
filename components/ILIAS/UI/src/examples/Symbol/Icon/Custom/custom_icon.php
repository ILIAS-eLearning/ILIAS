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

namespace ILIAS\UI\examples\Symbol\Icon\Custom;

/**
 * ---
 * description: >
 *   Example for rendering custom icons.
 *
 * expected output: >
 *   ILIAS shows a custom icon in three different sizes.
 *   Below those icons another custom icon with an abbrevation (two letters) is displayed in three different sizes.
 * ---
 */
function custom_icon()
{
    global $DIC;
    $f = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();

    $buffer = array();

    $path = './assets/ui-examples/images/Icon/my_custom_icon.svg';
    $ico = $f->symbol()->icon()->custom($path, 'Example');

    $buffer[] = $renderer->render($ico)
        . ' Small Custom Icon';

    $buffer[] = $renderer->render($ico->withSize('medium'))
        . ' Medium Custom Icon';

    $buffer[] = $renderer->render($ico->withSize('large'))
        . ' Large Custom Icon';


    //Note that the svg needs to contain strictly valid xml to work with abbreviations.
    //Some exports e.g. form illustrator seem to be not properly formatted by default.
    $path = './assets/images/standard/icon_fold.svg';
    $ico = $f->symbol()->icon()->custom($path, 'Example')
        ->withAbbreviation('FD');

    $buffer[] = $renderer->render($ico)
        . ' Small Custom Icon with Abbreviation';

    $buffer[] = $renderer->render($ico->withSize('medium'))
        . ' Medium Custom Icon with Abbreviation';

    $buffer[] = $renderer->render($ico->withSize('large'))
        . ' Large Custom Icon with Abbreviation';


    return implode('<br><br>', $buffer);
}
