<?php

declare(strict_types=1);

namespace ILIAS\UI\examples\Table\Presentation;

use ILIAS\UI\Component\Table\PresentationRow;
use ILIAS\UI\Factory;

/**
 * Example showing a presentation table without any data and hence no entries, which
 * will automatically display an according message.
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
