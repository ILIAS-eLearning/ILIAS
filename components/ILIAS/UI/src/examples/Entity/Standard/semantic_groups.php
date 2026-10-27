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
 * description: >
 *   The different semantic locations on an entity.
 * expected output: >
 *   This example shows/identifies the semantic groups of entites;
 *   from top to bottom, left to right, the order of groups is this:
 *   - secondary indentifier (it indents all the latter) and featured properties
 *   - blocking conditions (left) and actions in a dropdown (right)
 *   - primary identifier
 *   - featured properties
 *   - personal status
 *   - a workflow step button
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

    $entity = $f->entity()->standard('demo-entity', 'Primary Identifier', 'Secondary Identifier')
        ->withBlockingAvailabilityConditions($f->legacy()->content('Blocking Conditions'))
        ->withFeaturedProperties($f->legacy()->content('Featured Properties'))
        ->withPersonalStatus($f->legacy()->content('Personal Status'))
        ->withMainDetails($f->legacy()->content('Main Details'))
        ->withAvailability($f->legacy()->content('Availability'))
        ->withDetails($f->legacy()->content('Details'))
        ->withReactions($f->button()->tag('reaction', '#'))
        ->withPrioritizedReactions($f->button()->shy("Prioritized Reaction", "#")->withSymbol($f->symbol()->glyph()->like()))
        ->withManagingActions($f->button()->shy('managing actions', '#'))
    ;

    // to get buttons, they need to be created from a Workflow
    $workflow_factory = $f->listing()->workflow();
    $dummy_step = $workflow_factory->step('', '');

    // Creating Workflow Steps
    $steps = [
        $workflow_factory->step("Workflow Step not longer available", "", "#")
            ->withAvailability($dummy_step::NOT_ANYMORE)->withStatus($dummy_step::SUCCESSFULLY),
        $workflow_factory->step("Start available Workflow Step", "", "#")
            ->withAvailability($dummy_step::AVAILABLE)->withStatus($dummy_step::NOT_STARTED),
        $workflow_factory->step("Workflow Step not yet available", "", "#")
            ->withAvailability($dummy_step::NOT_AVAILABLE)->withStatus($dummy_step::NOT_AVAILABLE),
    ];

    $video_workflow = $workflow_factory->linear("Workflow", $steps);

    $entity = $entity->withWorkflow($video_workflow);

    return $renderer->render($entity);
}
