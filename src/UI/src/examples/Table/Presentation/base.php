<?php

declare(strict_types=1);

namespace ILIAS\UI\examples\Table\Presentation;

function base()
{
    global $DIC;
    $f = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();

    //build viewcontrols
    $actions = array("All" => "#",	"Upcoming events" => "#");
    $aria_label = "filter entries";
    $view_controls = array(
        $f->viewControl()->mode($actions, $aria_label)->withActive("All")
    );

    //build table
    $ptable = $f->table()->presentation(
        'Presentation Table', //title
        $view_controls,
        function ($row, $record, $ui_factory, $environment) { //mapping-closure
            return $row
                ->withHeadline($record['title'])
                ->withSubheadline($record['type'])
                ->withImportantFields(
                    array(
                        $record['begin_date'],
                        $record['location'],
                        'Available Slots: ' => $record['bookings_available']
                    )
                )

                ->withContent(
                    $ui_factory->listing()->descriptive(
                        array(
                            'Targetgroup' => $record['target_group'],
                            'Goals' => $record['goals'],
                            'Topics' => $record['topics']
                        )
                    )
                )

                ->withFurtherFieldsHeadline('Detailed Information')
                ->withFurtherFields(
                    array(
                        'Location: ' => $record['location'],
                        $record['address'],
                        'Date: ' => $record['date'],
                        'Available Slots: ' => $record['bookings_available'],
                        'Fee: ' => $record['fee']
                    )
                )
                ->withAction(
                    $ui_factory->button()->standard('book course', '#')
                );
        }
    );

    //example data as from an assoc-query, list of arrays
    $data = included_data();

    //apply data to table and render
    return $renderer->render($ptable->withData($data));
}

function included_data()
{
    return array(
        array(
            'title' => 'Online Presentation of some Insurance Topic',
            'type' => 'Webinar',
            'begin_date' => '30.09.2017',
            'bookings_available' => '3',
            'target_group' => 'Employees, Field Service',
            'goals' => 'Lorem Ipsum....',
            'topics' => '<li>Tranportations</li><li>Europapolice</li>',
            'date' => '30.09.2017 - 02.10.2017',
            'location' => 'Hamburg',
            'address' => 'Hauptstraße 123',
            'fee' => '380 €'
        ),
        array(
            'title' => 'Workshop: Life Insurance 2017',
            'type' => 'Face 2 Face',
            'begin_date' => '12.12.2017',
            'bookings_available' => '12',
            'target_group' => 'Agencies, Field Service',
            'goals' => 'Life insurance (or life assurance, especially in the Commonwealth	of Nations), is a contract between an insurance policy holder and an insurer or assurer, where the insurer promises to pay a designated beneficiary a sum of money (the benefit) in exchange for a premium, upon the death of an insured person (often the policy holder). Depending on the contract, other events such as terminal illness or critical illness can also trigger payment. The policy holder typically pays a premium, either regularly or as one lump sum. Other expenses (such as funeral expenses) can also be included in the benefits.',
            'topics' => 'Life-based contracts tend to fall into two major categories:
						<ul><li>Protection policies – designed to provide a benefit, typically a lump sum payment, in the event of a specified occurrence. A common form - more common in years past - of a protection policy design is term insurance.</li>
						<li>Investment policies – the main objective of these policies is to facilitate the growth of capital by regular or single premiums. Common forms (in the U.S.) are whole life, universal life, and variable life policies.</li></ul>',
            'date' => '12.12.2017 - 14.12.2017',
            'location' => 'Cologne',
            'address' => 'Holiday Inn, Am Dom 12, 50667 Köln',
            'fee' => '500 €'
        ),
        array(
            'title' => 'Basics: Preparation for Seminars',
            'type' => 'Online Training',
            'begin_date' => '-',
            'bookings_available' => 'unlimited',
            'target_group' => 'All',
            'goals' => '',
            'topics' => '',
            'date' => '-',
            'location' => 'online',
            'address' => '',
            'fee' => '-'
        )
    );
}
