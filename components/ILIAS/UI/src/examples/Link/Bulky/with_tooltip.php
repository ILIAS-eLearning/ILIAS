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

namespace ILIAS\UI\examples\Link\Bulky;

/**
 * ---
 * description: >
 *   The Bulky Links in this example point to ilias.de and includes tooltips
 *   Note the exact look of the Bulky Links is mostly defined by the
 *   surrounding container.
 *
 * expected output: >
 *   ILIAS shows a bulky link. Hovering over the link will show tooltips
 * ---
 */
function with_tooltip()
{
    global $DIC;
    $f = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();

    $target = new \ILIAS\Data\URI("https://ilias.de");
    $glyph = $f->symbol()->glyph()->comment();

    $link = $f->link()->bulky($glyph, 'Link to ilias.de with Glyph', $target)
        ->withHelpTopics(
            ...$f->helpTopics("ilias", "learning management system")
        );

    return $renderer->render([
        $link
    ]);
}
