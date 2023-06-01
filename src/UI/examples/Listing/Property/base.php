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
            $renderer->render(
                $f->symbol()->icon()->custom('./templates/default/images/learning_progress/in_progress.svg', 'incomplete'),
            ) . ' in progress',
            false
        );

    $props2 = $props->withItems([
        ['a', "1"],
        ['y', "25", false],
        ['link', $f->link()->standard('Goto ILIAS', 'http://www.ilias.de')]
    ]);

    return $renderer->render([
            $props,
            $f->divider()->horizontal(),
            $props2
    ]);
}
