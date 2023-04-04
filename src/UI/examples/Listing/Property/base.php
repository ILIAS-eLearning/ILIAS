<?php

declare(strict_types=1);

namespace ILIAS\UI\examples\Listing\Property;

function base()
{
    global $DIC;
    $f = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();

    $props = $f->listing()->property()
        ->withProperty('Title', 'Some Title')
        ->withProperty('number', '7')
        ->withProperty(
            'status',
            [
                $f->symbol()->icon()->custom('./templates/default/images/learning_progress/in_progress.svg', 'incomplete'),
                $f->legacy('in progress')
            ],
            false
        );

    return $renderer->render($props);
}
