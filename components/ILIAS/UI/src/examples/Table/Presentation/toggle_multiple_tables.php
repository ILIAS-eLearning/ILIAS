<?php

declare(strict_types=1);

namespace ILIAS\UI\examples\Table\Presentation;

use ILIAS\UI\Component\Signal;

/**
 * You can also leave out "further fields" and use alignments instead,
 * add one or more Blocks and Layouts to the content of the row and add an leading image.
 */
function toggle_multiple_tables()
{
    global $DIC;
    /** @var ILIAS\UI\Factory $ui_factory */
    $ui_factory = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();
    $tpl = $DIC['tpl'];

    $tpl->addCss('src/UI/examples/Table/Presentation/presentation_alignment_example.css');

    //example data
    $data = included_data_multiple();

    $mapping_closure = function ($row, $record, $ui_factory, $environment) {
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
            );
    };

    $ptable1 = $ui_factory->table()->presentation(
        'First Dataset', //title
        [],
        $mapping_closure
    );

    $ptable2 = $ui_factory->table()->presentation(
        'Second Dataset', //title
        [],
        $mapping_closure
    );

    $on_load_code_builder = static fn(array $toggle_signals): \Closure
        => static function (string $id) use ($toggle_signals): string {
            $toggler = array_reduce(
                $toggle_signals,
                fn(string $c, Signal $v) => "{$c}$(document).trigger('{$v->getId()}',"
                    . '{"options" : ' . json_encode($v->getOptions()) . '}); ',
                ''
            );

            return "document.getElementById('{$id}').addEventListener('click', "
                . '(e) => {' . $toggler . '}'
                . ');';
        };

    $actions = $ui_factory->dropdown()->standard(
        [
            $ui_factory->button()->shy('expand all', '')->withOnLoadCode(
                $on_load_code_builder([
                    $ptable1->getExpandAllSignal(),
                    $ptable2->getExpandAllSignal()
                ])
            ),
            $ui_factory->button()->shy('collapse all', '')->withOnLoadCode(
                $on_load_code_builder([
                    $ptable1->getCollapseAllSignal(),
                    $ptable2->getCollapseAllSignal()
                ])
            )
        ]
    );

    //apply data to table and render
    return $renderer->render(
        $ui_factory->panel()->standard(
            'Some User Records',
            [
                $ptable1->withData($data),
                $ptable2->withData($data)
            ]
        )->withActions($actions)
    );
}

function included_data_multiple()
{
    return [
        [
            'title' => 'Online Presentation of some Insurance Topic',
            'type' => 'Webinar',
            'begin_date' => (new \DateTime())->modify('+1 day')->format('d.m.Y'),
            'bookings_available' => '3',
            'target_group' => 'Employees, Field Service',
            'goals' => 'Lorem Ipsum....',
            'topics' => '<ul><li>Tranportations</li><li>Europapolice</li></ul>',
            'date' => (new \DateTime())->modify('+1 day')->format('d.m.Y')
                . ' - '
                . (new \DateTime())->modify('+2 day')->format('d.m.Y'),
            'location' => 'Hamburg',
            'address' => 'Hauptstraße 123',
            'fee' => '380 €'
        ],
        [
            'title' => 'Workshop: Life Insurance 2017',
            'type' => 'Face 2 Face',
            'begin_date' => '12.12.2017',
            'bookings_available' => '12',
            'target_group' => 'Agencies, Field Service',
            'goals' => 'Life insurance (or life assurance, especially in the Commonwealth   of Nations), is a contract between an insurance policy holder and an insurer or assurer, where the insurer promises to pay a designated beneficiary a sum of money (the benefit) in exchange for a premium, upon the death of an insured person (often the policy holder). Depending on the contract, other events such as terminal illness or critical illness can also trigger payment. The policy holder typically pays a premium, either regularly or as one lump sum. Other expenses (such as funeral expenses) can also be included in the benefits.',
            'topics' => 'Life-based contracts tend to fall into two major categories:
                        <ul><li>Protection policies – designed to provide a benefit, typically a lump sum payment, in the event of a specified occurrence. A common form - more common in years past - of a protection policy design is term insurance.</li>
                        <li>Investment policies – the main objective of these policies is to facilitate the growth of capital by regular or single premiums. Common forms (in the U.S.) are whole life, universal life, and variable life policies.</li></ul>',
            'date' => '12.12.2017 - 14.12.2017',
            'location' => 'Cologne',
            'address' => 'Holiday Inn, Am Dom 12, 50667 Köln',
            'fee' => '500 €'
        ],
        [
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
        ]
    ];
}
