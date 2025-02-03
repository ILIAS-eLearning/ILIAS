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
 *   Entities arrange information about e.g. an object into semantic groups;
 *   this example focusses on the possible contents of those groups and shows
 *   a possible representation of a made up event.
 *   From top to bottom, left to right:
 *   - There is a precondition; it links to ilias.de.
 *   - An action-dropdown is available with two entries linking to ilias/github.
 *   - An icon indents the following.
 *   - Prominently featured is the event's date proptery.
 *   - Only after that, the title of the event is displayed in bold.
 *   - A progress meter ("in progress") is followed by detailed properties:
 *     - Room information
 *     - Description
 *     - in one line: Available seats and availability of the event
 *     - in the next line: duration and the information of available redording
 *   - The bottom "row" shows two tags on the left
 *   - and two glyphs on the right, the first one with status counter, the second one with
 *     both status- and novelty counter.
 * ---
 */
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
        ? $blocking->withProperty("Available Seats", (string) $av_data['Available Seats']) : $blocking;
    $availability = ($av_data['Available Seats'] > 0)
        ? $availability->withProperty("Available Seats", (string) $av_data['Available Seats']) : $availability;

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

    $status = $f->legacy()->content(
        $renderer->render($f->symbol()->icon()->custom('./assets/images/learning_progress/in_progress.svg', 'incomplete'))
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
