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

namespace ILIAS\UI\examples\MainControls\MainBar;

/**
 * ---
 * expected output: >
 *   ILIAS shows a link "Full Screen Page Layout".
 *   On clicking the link, a new page opens. The mainbar on the new page is
 *   initially closed.
 *
 *   MainBar is big component; the page shows some of its features:
 *   "Tools": >
 *      The Tools-entry is differently colored than the rest of the entries.
 *      Clicking it will open the slate and reveal the tools "Help", "Editor"
 *      and "Closeable Tool". Each of them are clickable and will alter the
 *      slate's content when clicked.
 *      The "X" will remove the "Closeable Tool" from the tools-section.
 *    "Repository": >
 *      The slate in "Repository" is filled with a lot of entries to demonstrate
 *      the vertical scrollbar within it.
 *    "Personal Workspace": >
 *       will contain two entries "Bookmarks", which will open sub-entries (links)
 *       rather than changing the content of the page.
 *    "Organisation": >
 *      There is a larger sub-structure of further slates in "Organisation".
 *      Higher slates ("1") will close all lower levels(1.1, 1.2), but will
 *      re-open to the state the user left the substructure.
 *
 *   Clicking an opened entry will close the slate.
 *   Re-opening will have the state of the substructure preserved.
 * ---
 */
function mainbar(): string
{
    global $DIC;
    $f = $DIC['ui.factory'];
    $renderer = $DIC['ui.renderer'];
    $ctrl = $DIC['ilCtrl'];


    $ctrl->setParameterByClass('ilsystemstyledocumentationgui', 'node_id', 'LayoutPageStandardStandard');
    $ctrl->setParameterByClass('ilsystemstyledocumentationgui', 'new_ui', '1');
    $url = $ctrl->getLinkTargetByClass('ilsystemstyledocumentationgui', 'entries');
    $to_page = $f->link()->standard('Full Screen Page Layout', $url);
    $txt = $f->legacy()->content('<p>Better head over to a preview of page to see a mainbar in its entire beauty...</p>');
    return $renderer->render([
        $txt,
        $to_page
    ]);
}
