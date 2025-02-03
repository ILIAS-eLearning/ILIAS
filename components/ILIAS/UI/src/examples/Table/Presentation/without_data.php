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
 *   Example showing a presentation table without any data and hence no entries, which
 *   will automatically display an according message.
 *
 * expected output: >
 *   Instead of several rows with expander glyphs, ILIAS shows a message "No records".
 *   Viewcontrols are still there but have no effect.
 * ---
 */
function without_data(): string
{
    global $DIC;

    $factory = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();

    $minimal_mapping = static fn(
        PresentationRow $row,
        mixed $record,
        Factory $ui_factory,
        mixed $environment
    ): PresentationRow => $row;

    $table = $factory->table()->presentation(
        'Empty Presentation Table',
        [$factory->viewControl()->mode(['All' => '#'], '')],
        $minimal_mapping
    );

    // Note: this is an optional call, it should merely demonstrate that we have
    // an empty table.
    $table->withData([]);

    return $renderer->render($table);
}
