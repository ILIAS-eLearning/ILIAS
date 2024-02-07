<?php

declare(strict_types=1);

namespace ILIAS\UI\examples\Modal\Interruptive;

/**
 * Example showing an interruptive modal with custom labels.
 */
function with_custom_labels(): string
{
    global $DIC;

    $factory = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();

    $modal = $factory->modal()->interruptive(
        'You are about to perform a dangerous action',
        'Are you sure you want to continue? Use these very descriptive buttons below to continue/abort.',
        '#'
    );

    $modal = $modal->withActionButtonLabel('yes, continue with this action')
                   ->withCancelButtonLabel('no, cancel this dangerous action!');

    $trigger = $DIC->ui()->factory()->button()->standard(
        'perform dangerous action',
        $modal->getShowSignal()
    );

    return $renderer->render([$modal, $trigger]);
}
