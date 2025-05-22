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

namespace ILIAS\UI\examples\Button\Toggle;

/**
 * ---
 * description: >
 *   Example for rendering a Toggle Button with tooltips
 *
 * expected output: >
 *   Hovering over the rendered switch shows following tooltips: "tooltip: ilias" and "tooltip: learning management system".
 * ---
 */
function with_tooltip()
{
    global $DIC;
    $f = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();

    $button = $f->button()->toggle('', '#', '#')
                          ->withAriaLabel("Switch the State of XY")
                          ->withHelpTopics(
                              ...$f->helpTopics("ilias", "learning management system")
                          );

    return $renderer->render([$button]);
}
