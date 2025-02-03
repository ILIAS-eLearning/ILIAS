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

namespace ILIAS\UI\examples\ViewControl\Sortation;

/**
 * ---
 * description: >
 *   This can be used, when space is very scarce and the label cannot be displayed
 *
 * expected output: >
 *   ILIAS shows a control with two arrows. Clicking the arrows will open a dropdown menu with three shy buttons
 *   "Default Ordering", "Most Recent Ordering" and "Oldest Ordering". Clicking the button will reload the website.
 *   The control is still the same as before.
 * ---
 */
function small()
{
    //Loading factories
    global $DIC;
    $f = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();

    $options = array(
        'default_option' => 'Default Ordering',
        'latest' => 'Most Recent Ordering',
        'oldest' => 'Oldest Ordering'
    );

    //Hide the label
    $s = $f->viewControl()->sortation($options, 'oldest')
        ->withTargetURL($DIC->http()->request()->getRequestTarget(), 'sortation');

    $item = $f->item()->standard("See the Viewcontrol in a toolbar")
            ->withDescription("When space is limited, the label will be omitted.");
    return $renderer->render(
        $f->panel()->standard("Small space ", [$item])
            ->withViewControls([$s])
    );
}
