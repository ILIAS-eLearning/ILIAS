<?php

declare(strict_types=1);

namespace ILIAS\UI\Examples\Entity\Standard;

function base()
{
    global $DIC;
    $f = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();

    $flatten = fn ($ar): string => implode(' - ', array_map(fn ($pair) => implode(': ', array_filter($pair)), $ar));

    $secondary_id = $f->symbol()->icon()->standard('crs', 'some course', 'medium');
    $actions = [
        $f->button()->shy("ILIAS", "https://www.ilias.de"),
        $f->button()->shy("GitHub", "https://www.github.com")
    ];
    $prio_reactions = [
        $f->symbol()->glyph()->love()
            ->withCounter($f->counter()->status(2)),
        $f->symbol()->glyph()->comment()
            ->withCounter($f->counter()->novelty(3))
            ->withCounter($f->counter()->status(7))
    ];
    $reactions = [
        $f->button()->tag('tag', '#')
    ];

    $details = $flatten([
        ['detail', '7'],
        ['', 'unlabled detail'],
        ['another detail', 'anothervalue']
    ]);


    $status = [
        $f->symbol()->icon()->custom('./templates/default/images/learning_progress/in_progress.svg', 'incomplete'),
        $f->legacy('personal status')
    ];


    $entity = $f->entity()->standard(
        'primary id',
        $secondary_id
    )
    ->withFeaturedProperties('Status: offline')
    ->withMainDetails('This is a descriptive text. This is a descriptive text. This is a descriptive text.')
    ->withBlockingAvailabilityConditions('there are blocking conditions!')
    ->withPersonalStatus($status)
    ->withAvailability('available: until 2024/12/24')
    ->withDetails($details)
    ->withPrioritizedReactions($prio_reactions)
    ->withReactions($reactions)
    ->withActions($actions)
    ;

    return $renderer->render($entity);
}
