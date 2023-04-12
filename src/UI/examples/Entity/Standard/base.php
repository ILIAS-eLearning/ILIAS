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
    $entity = $entity->withActions($actions);

    /*
    * Logic for Pulling Availabilty Properties to Blocking Conditions
    */

    $av_data = ['Available Seats' => 4, 'Available' => 'until 24.12.2023', 'Expected Preconditions' => 'UI Design 101', 'Passed Courses' => 'Painting'];

    $blocking = $f->listing()->property();
    $availability = $f->listing()->property();

    $precondition_link = $f->button()->shy("Preconditions", "http://www.ilias.de");

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

    $status = [
        $f->symbol()->icon()->custom('./templates/default/images/learning_progress/in_progress.svg', 'incomplete'),
        $f->legacy('in progress')
    ];

    $entity = $entity
    ->withPersonalStatus($status)
    ->withDetails($details)
    ->withReactions($reactions)
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

    $details = $f->listing()->property()
        ->withProperty('detail: ', '7')
        ->withProperty('detail2', 'unlabled detail', false);

    $details2 = $f->listing()->property()
        ->withProperty('another detail: ', 'anothervalue');

    $status = [
        $f->symbol()->icon()->custom('./templates/default/images/learning_progress/in_progress.svg', 'incomplete'),
        $f->legacy('personal status')
    ];

    $availability = $f->listing()->property()
        ->withProperty('available', 'until 2024/12/24');

    $entity = $f->entity()->standard(
        'primary id',
        $secondary_id
    )
    ->withFeaturedProperties('Status: offline')
    ->withMainDetails('This is a descriptive text. This is a descriptive text. This is a descriptive text.')
    ->withBlockingAvailabilityConditions('there are blocking conditions!')
    ->withPersonalStatus($status)
    ->withAvailability($availability)
    ->withDetails([$details, $details2])
    ->withPrioritizedReactions($prio_reactions)
    ;

    return $renderer->render($entity);
}
