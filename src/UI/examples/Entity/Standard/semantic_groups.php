<?php

declare(strict_types=1);

namespace ILIAS\UI\Examples\Entity\Standard;

function semantic_groups()
{
    global $DIC;
    $f = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();

    $entity = $f->entity()->standard('Primary Identifier', 'Secondary Identifier')
        ->withBlockingAvailabilityConditions('Blocking Conditions')
        ->withFeaturedProperties('Featured_properties')
        ->withPersonalStatus('Personal Status')
        ->withMainDetails('Main Details')
        ->withAvailability('Availability')
        ->withDetails('Details')
        ->withReactions([$f->button()->tag('reaction', '#')])
        ->withPrioritizedReactions([$f->symbol()->glyph()->like()])
        ->withActions([$f->button()->shy('action','#')])
    ;

    return $renderer->render([
        $f->image()->responsive(
            'src/UI/examples/Entity/Standard/semantic_groups.png', 
            'Overview of the arrangement of semantig Groups within the Entity'
        ),
        $f->divider()->horizontal(),
        $entity
    ]);
}
