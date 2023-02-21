<?php

declare(strict_types=1);

namespace ILIAS\UI\examples\Input\ViewControl\Sortation;

function base()
{
    global $DIC;
    $f = $DIC['ui.factory'];
    $r = $DIC['ui.renderer'];

    $sortation = $f->input()->viewControl()->sortation(
        [
            'field1:ASC' => 'Field 1, ascending',
            'field1:DESC' => 'Field 1, descending',
            'field2:ASC' => 'Field 2, ascending'

        ]
    );

    return $r->render($sortation);
}
