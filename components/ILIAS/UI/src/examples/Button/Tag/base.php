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

namespace ILIAS\UI\examples\Button\Tag;

/**
 * ---
 * descriptions: >
 *   Example for rendering a Tag Button.
 *
 * expected output: >
 *   ILIAS shows four different buttons titled "simple tag" in following order:
 *   1. Five buttons in shaded colors. The cursor will change while hovering above the button which confirms that the
 *      buttons are clickable.
 *   2. with unavailable action: five grey buttons which are not clickable.
 *   3. with additional class(es): a very broad button with a loading animation on the right side.
 *   4. with fix colors: three buttons with different background colors and different text colors. Hovering over the
 *      buttons will change the cursor which confirms that the buttons are clickable.
 * ---
 */
function base()
{
    global $DIC;
    $f = $DIC->ui()->factory();
    $df = new \ILIAS\Data\Factory();
    $renderer = $DIC->ui()->renderer();
    $buffer = array();

    $tag = $f->button()->tag("simple tag", "#");

    $possible_relevances = array(
        $tag::REL_VERYLOW,
        $tag::REL_LOW,
        $tag::REL_MID,
        $tag::REL_HIGH,
        $tag::REL_VERYHIGH
    );

    foreach ($possible_relevances as $w) {
        $buffer[] = $renderer->render($tag->withRelevance($w));
    }

    $buffer[] = '<hr>with unavailable action:<br>';
    $no_action_tag = $tag->withUnavailableAction();
    foreach ($possible_relevances as $w) {
        $buffer[] = $renderer->render($no_action_tag->withRelevance($w));
    }

    $buffer[] = '<hr>with additional class(es):<br>';

    $tag = $tag->withRelevance($tag::REL_VERYLOW);
    $buffer[] = $renderer->render(
        $tag->withClasses(array('il-btn-with-loading-animation',"btn-bulky"))
    );

    $lightcol = $df->color('#00ff00');
    $darkcol = $df->color('#00aa00');
    $forecol = $df->color('#d4190b');

    $buffer[] = '<hr>with fix colors:<br>';
    $tag = $tag->withBackgroundColor($lightcol);
    $buffer[] = $renderer->render($tag);
    $buffer[] = $renderer->render($tag->withBackgroundColor($darkcol));

    $buffer[] = '<br><br>';
    $buffer[] = $renderer->render(
        $tag->withBackgroundColor($lightcol)
            ->withForegroundColor($forecol)
    );

    return implode(' ', $buffer);
}
