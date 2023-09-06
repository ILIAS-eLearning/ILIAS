<?php

declare(strict_types=1);

namespace ILIAS\UI\Examples\Entity\Standard;

function base()
{
    global $DIC;
    $f = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();

    /*
    * Basic Construction
    */

    $primary_id = "Building Better UX by Considering Information Architecture and User Intent";
    $secondary_id = $f->symbol()->icon()->standard('crs', 'course icon', 'large');

    // creating the entity object now so it can be filled in the logic section
    $entity = $f->entity()->standard(
        $primary_id,
        $secondary_id
    );

    /*
    * Dropdown Actions
    */

    $actions = [
        $f->button()->shy("ILIAS", "https://www.ilias.de"),
        $f->button()->shy("GitHub", "https://www.github.com")
    ];
    $entity = $entity->withActions(...$actions);

    /*
    * Logic for Pulling Availabilty Properties to Blocking Conditions
    */

    $av_data = ['Available Seats' => 4, 'Available' => 'until 24.12.2023', 'Expected Preconditions' => 'UI Design 101', 'Passed Courses' => 'Painting'];

    $blocking = $f->listing()->property();
    $availability = $f->listing()->property();

    $precondition_link = $f->link()->standard("Preconditions", "http://www.ilias.de");

    // If preconditions aren't met
    $blocking = ($av_data['Expected Preconditions'] === $av_data['Passed Courses'])
        ? $blocking : $blocking->withProperty("Preconditions", $precondition_link, false);

    // If no more seats are available
    $blocking = ($av_data['Available Seats'] === 0)
        ? $blocking->withProperty("Available Seats", (string)$av_data['Available Seats']) : $blocking;
    $availability = ($av_data['Available Seats'] > 0)
        ? $availability->withProperty("Available Seats", (string)$av_data['Available Seats']) : $availability;

    // all remaining availability properties
    $availability = $availability->withProperty("Available", $av_data['Available']);

    $entity = $entity
        ->withBlockingAvailabilityConditions($blocking)
        ->withAvailability($availability);

    /*
    * All Other Semantic Groups
    */

    $reactions = [
        $f->button()->tag('UX/UI', '#'), $f->button()->tag('First Semester', '#')
    ];

    $details = $f->listing()->property()
        ->withProperty('Duration', '90 minutes')
        ->withProperty('Recording', 'recording available', false)
    ;

    $status = $f->legacy(
        $renderer->render($f->symbol()->icon()->custom('./templates/default/images/learning_progress/in_progress.svg', 'incomplete'))
        . ' in progress'
    );

    $entity = $entity
      ->withPersonalStatus($status)
      ->withDetails($details)
      ->withReactions(...$reactions)
    ;

    /*
    * Priority Areas
    */

    $featured_properties = $f->listing()->property()
        ->withProperty('Event Date', '14.02.2023');

    $prio_reactions = [
        $f->symbol()->glyph()->love()
            ->withCounter($f->counter()->status(2)),
        $f->symbol()->glyph()->comment()
            ->withCounter($f->counter()->novelty(3))
            ->withCounter($f->counter()->status(7))
    ];

    $main_detail_1 = $f->listing()->property()
        ->withProperty('Room', '7')
    ;
    $main_detail_2 = $f->listing()->property()
        ->withProperty('Description', 'This lecture is an introduction to basic concepts fundamental for an intuitive user experience. These basic principles are not directly connected to the visual design, yet they help us to discover a hierarchy in relevance that needs to be respected for the visual appearance.', false)
    ;

    $entity = $entity
        ->withFeaturedProperties($featured_properties)
        ->withMainDetails($main_detail_1, $main_detail_2)
        ->withPrioritizedReactions(...$prio_reactions)
    ;

    return $renderer->render($entity);
}
