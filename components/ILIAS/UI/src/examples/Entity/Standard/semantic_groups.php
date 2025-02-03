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

namespace ILIAS\UI\Examples\Entity\Standard;

/**
 * ---
 * expected output: >
 *   This example shows/identifies the semantic groups of entites;
 *   from top to bottom, left to right, the order of groups is this:
 *   - blocking conditions (left) and actions in a dropdown (right)
 *   - secondary indentifier (it indents all the latter) and featured properties
 *   - primary identifier
 *   - personal status
 *   - main details
 *   - availability
 *   - details
 *   - reactions (the tag) and prioritized reactions (the 'like' glyph)
 * ---
 */
function semantic_groups()
{
    global $DIC;
    $f = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();

    $entity = $f->entity()->standard('Primary Identifier', 'Secondary Identifier')
        ->withBlockingAvailabilityConditions($f->legacy()->content('Blocking Conditions'))
        ->withFeaturedProperties($f->legacy()->content('Featured_properties'))
        ->withPersonalStatus($f->legacy()->content('Personal Status'))
        ->withMainDetails($f->legacy()->content('Main Details'))
        ->withAvailability($f->legacy()->content('Availability'))
        ->withDetails($f->legacy()->content('Details'))
        ->withReactions($f->button()->tag('reaction', '#'))
        ->withPrioritizedReactions($f->symbol()->glyph()->like())
        ->withActions($f->button()->shy('action', '#'))
    ;

    return $renderer->render($entity);
}
