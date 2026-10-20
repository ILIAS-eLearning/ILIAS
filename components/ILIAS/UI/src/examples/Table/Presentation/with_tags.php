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

namespace ILIAS\UI\examples\Table\Presentation;

use ILIAS\UI\Component\Table\PresentationRow;
use ILIAS\UI\Factory;

/**
 * ---
 * description: >
 *   Example showing a presentation table with Tags in its rows. The Tags are
 *   shown below the headline and are not part of it.
 *
 * expected output: >
 *   ILIAS shows a table titled "Presentation Table with Tags" with two rows.
 *   Below the headline of the first row two Tags are shown, below the headline
 *   of the second row three Tags. The Tags stay visible when the row is expanded.
 *   Clicking the headline still expands and collapses the row.
 * ---
 */
function with_tags(): string
{
    global $DIC;

    $factory = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();

    $mapping = static fn(
        PresentationRow $row,
        mixed $record,
        Factory $ui_factory,
        mixed $environment
    ): PresentationRow => $row
        ->withHeadline($record['title'])
        ->withSubheadline($record['type'])
        ->withTags(
            ...array_map(
                static fn(string $tag) => $ui_factory->button()->tag($tag, '#'),
                $record['tags']
            )
        )
        ->withContent(
            $ui_factory->listing()->descriptive(['Description' => $record['description']])
        );

    $table = $factory->table()->presentation(
        'Presentation Table with Tags',
        [],
        $mapping
    );

    $data = [
        [
            'title' => 'Introduction to Statistics',
            'type' => 'Course',
            'tags' => ['Online', 'Mandatory'],
            'description' => 'Basic terms and methods of descriptive statistics.'
        ],
        [
            'title' => 'Final Exam',
            'type' => 'Test',
            'tags' => ['Offline', 'Mandatory', 'Certificate'],
            'description' => 'Written exam on all chapters of the course.'
        ]
    ];

    return $renderer->render($table->withData($data));
}
