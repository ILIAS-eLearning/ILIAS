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

namespace ILIAS\UI\examples\Listing\Descriptive;

/**
 * ---
 * description: >
 *   Example for rendering a descriptive list.
 *
 * expected output: >
 *   ILIAS shows a list with titles and despriptions one below the other.
 * ---
 */
function base()
{
    //Init Factory and Renderer
    global $DIC;
    $f = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();

    //Generate List
    $descriptive = $f->listing()->descriptive(
        [
            "Title 1" => "Description 1",
            "Title 2" => "Description 2",
            "Title 3" => "Description 3"]
    );

    //Render
    return $renderer->render($descriptive);
}
