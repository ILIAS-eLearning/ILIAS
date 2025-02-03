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

namespace ILIAS\UI\examples\Card\RepositoryObject;

/**
 * ---
 * description: >
 *   Example for rendering a repository card. Note that those cards are used if more visual information about the
 *   repository object type is needed.
 *
 * expected output: >
 *   ILIAS shows a ILIAS-Logo with a title below. The logo's size will change accordingly to the size of the browser/desktop.
 * ---
 */
function base()
{
    //Init Factory and Renderer
    global $DIC;
    $f = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();

    $image = $f->image()->responsive(
        "./assets/images/logo/HeaderIcon.svg",
        "Thumbnail Example"
    );

    $card = $f->card()->repositoryObject("RepositoryObject Card Title", $image);

    //Render
    return $renderer->render($card);
}
