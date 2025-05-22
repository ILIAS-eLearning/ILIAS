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

namespace ILIAS\UI\examples\Popover\Standard;

/**
 * ---
 * description: >
 *   Example for rendering a standard popover with vertical scrollbars.
 *
 * expected output: >
 *   ILIAS shows a button titled "Show me some Series".
 *   A click onto the button opens a popover with a longer list of TV-Series.
 *   The list is too big for the popover, but you can scroll down.
 * ---
 */
function show_popover_with_vertical_scrollbars()
{
    global $DIC;
    $factory = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();

    // Note: A list should be rendered with the listing popover, e.g. $factory->popover()->listing()
    // However, at the moment there is no component present representing such list items, so this example
    // also uses a standard popover.

    $series = [
        'Breaking Bad',
        'Big Bang Theory',
        'Dexter',
        'Better Call Saul',
        'Narcos',
        'Ray Donovan',
        'Simpsons',
        'South Park',
        'Fargo',
        'Bloodline',
        'The Walking Dead',
        'New Girl',
        'Sons of Anarchy',
        'How I Met Your Mother',
    ];
    $list = $renderer->render($factory->listing()->unordered($series));
    // Note: The Popover does not restrict the height. It is the responsibility of the content component
    // to define the max height and to display vertical scrollbars, if necessary.
    // At the moment, the renderer of a component is not aware of the context it is rendering the component,
    // e.g. inside a Popover.
    // The inline code below simulates this behaviour. Here we want to reduce the
    // height of the list to 200px and display vertical scrollbars, if needed.
    $content = "<div style='max-height: 200px; overflow-y: auto; padding-right: 10px;'>{$list}</div>";

    $popover = $factory->popover()->standard($factory->legacy()->content($content))->withTitle('Series');
    $button = $factory->button()->standard('Show me some Series', '#')
        ->withOnClick($popover->getShowSignal());

    return $renderer->render([$popover, $button]);
}
