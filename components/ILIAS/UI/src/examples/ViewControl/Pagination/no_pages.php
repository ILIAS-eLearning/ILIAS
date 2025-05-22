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

namespace ILIAS\UI\examples\ViewControl\Pagination;

/**
 * ---
 * description: >
 *   Example for a pagination view control with no pages
 *
 * expected output: >
 *   A Pagination with one page only will render as empty string which results into an empty display.
 * ---
 */
function no_pages()
{
    global $DIC;
    $factory = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();
    $url = $DIC->http()->request()->getRequestTarget();

    $pagination = $factory->viewControl()->pagination()
        ->withPageSize(10)
        ->withTotalEntries(10);

    return $renderer->render($pagination);
}
