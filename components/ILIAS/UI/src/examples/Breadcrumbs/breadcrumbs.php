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

namespace ILIAS\UI\examples\Breadcrumbs;

/**
 * ---
 * description: >
 *   Example showing how to construct Breadcrumbs with an array of Links
 *   and extending the Breadcrumbs afterwards.
 *
 * expected output: >
 *   ILIAS shows two rows of clickable links separated by simple arrows (>).
 * ---
 */
function breadcrumbs()
{
    global $DIC;
    $renderer = $DIC->ui()->renderer();
    $f = $DIC->ui()->factory();

    $crumbs = array(
        $f->link()->standard("entry1", '#'),
        $f->link()->standard("entry2", '#'),
        $f->link()->standard("entry3", '#'),
        $f->link()->standard("entry4", '#')
    );

    $bar = $f->breadcrumbs($crumbs);

    $bar_extended = $bar->withAppendedItem(
        $f->link()->standard("entry5", '#')
    );

    return $renderer->render($bar)
        . $renderer->render($bar_extended);
}
