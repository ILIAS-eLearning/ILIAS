<?php

declare(strict_types=1);

namespace ILIAS\UI\Examples\Entity\Standard;

/**
 * ---
 * description: >
 *   Full example showing how the entity could be used to describe a video object with all of its features.
 *
 * expected output: >
 *  This example shows a representation of a made up video object.
 *  - A thumbnail is shown as a secondary identifier that indents all following elements
 *  - the title as primary identifier
 *  - a dropdown with managing options
 *  - upload date and publisher as Featured Property
 *  - Workflow buttons
 *  - duration adn a description as main details
 *  Below is an example workflow that was given to the entity to generate the workflow buttons on this entity.
 *  Two of the four steps are marked as not completed and available. These two options are rendered as buttons inside
 *  the entity.
 * ---
 */
function video_object()
{
    global $DIC;
    $f = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();

    /*
     * Basic Construction
     */

    $primary_id = "Mountains through the ages - the formation of giants";
    $secondary_id = $f->image()->responsive("assets/ui-examples/images/Image/mountains.jpg", "Some mountains in the dusk");

    // creating the entity object now so it can be filled in the logic section
    $entity = $f->entity()->standard(
        $primary_id,
        $secondary_id
    );

    /*
     * Priority Areas
     */

    $glyph_calendar = $f->symbol()->glyph()->calendar()->withLabel("Published on");
    $glyph_user = $f->symbol()->glyph()->user()->withLabel("Created by");

    $featured_properties = $f->listing()->property()
        ->withProperty($glyph_calendar, '24.01.2025')
        ->withProperty($glyph_user, 'BBC England, Co-Production: ARD/ZDF, Canal Plus')
    ;

    $entity = $entity
        ->withFeaturedProperties($featured_properties)
    ;

    /*
     * Dropdown Actions
     */

    $managing_actions = [
        $f->button()->shy("Copy", "https://www.ilias.de"),
        $f->button()->shy("Delete", "https://www.github.com")
    ];
    $entity = $entity->withManagingActions(...$managing_actions);

    /*
     * Generating Action Buttons from Workflow
     */

    $workflow_factory = $f->listing()->workflow();
    $dummy_step = $workflow_factory->step('', '');

    // Creating Workflow Steps
    $steps = [
        $workflow_factory->step("Upload video file", "Upload an .mp4 file or start a recording.", "#")
            ->withAvailability($dummy_step::NOT_ANYMORE)->withStatus($dummy_step::SUCCESSFULLY),
        $workflow_factory->step("Cut video", "Trim or remove parts of the video.", "#")
            ->withAvailability($dummy_step::AVAILABLE)->withStatus($dummy_step::NOT_STARTED),
        $workflow_factory->step("Add subtitles", "You must upload or generate subtitles for every video.", "#")
            ->withAvailability($dummy_step::AVAILABLE)->withStatus($dummy_step::NOT_STARTED),
        $workflow_factory->step("Publish", "Set who can see this video.", "#")
            ->withAvailability($dummy_step::NOT_AVAILABLE)->withStatus($dummy_step::NOT_AVAILABLE),
    ];

    $video_workflow = $workflow_factory->linear("Video Curation", $steps);

    $entity = $entity->withWorkflow($video_workflow);

    /*
     * All Other Semantic Groups
     */

    $glyph_time = $f->symbol()->glyph()->time()->withLabel("Duration");

    $main_details_01 = $f->listing()->property()
        ->withProperty($glyph_time, '45:00')
    ;
    $main_details_02 = $f->listing()->property()
        ->withProperty('Description', "A fascinating look on the forces of nature that are able to move unimaginable tons of rocks. Find out how seemingly immovable landscape has transformed drastically through the incredible forces set free by earthquakes, vulcanos and water. This award-winning documentary traces the movement of the world's greatest mountain ranges throughout millions of years.", false)
    ;

    $entity = $entity
        ->withMainDetails($main_details_01, $main_details_02)
    ;

    return $renderer->render([$entity]);
}
