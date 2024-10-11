<?php

declare(strict_types=1);

namespace ILIAS\UI\examples\Modal\Interruptive;

use ILIAS\Data\FormMethod;

/**
 * ---
 * description: >
 *   An example showing how you can change the form's method
 *   and pass parameters in the query.
 *
 * expected output: >
 *   ILIAS shows a button; on click, a Modal will open.
 *   When submitting the modal, the query-results are shown
 *   as characteristic value listing with two collections of values.
 * ---
 */
function with_method_get()
{
    global $DIC;
    $factory = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();
    $query = $DIC->http()->wrapper()->query();
    $refinery = $DIC->refinery();

    $if = $factory->modal()->interruptiveItem();
    $items = [
        $if->keyValue('id-1', 'KEY 1', 'VALUE 1', 'imodal'),
        $if->keyValue('id-2', 'KEY 2', 'VALUE 2', 'imodal'),
        $if->keyValue('id-3', 'KEY 3', 'VALUE 3', 'other'),
        $if->standard('id-4', 'KEY 4', null, 'description 4', 'other'),
    ];
    $modal = $factory->modal()->interruptive(
        'Modal with GET-method',
        'Am I interrupting you?',
        $DIC->http()->request()->getUri()->__toString(),
        FormMethod::GET
    )
    ->withAffectedItems($items);

    $trigger = $factory->button()->standard('Show Modal', $modal->getShowSignal());

    $out = $factory->legacy('');
    $divider = $factory->divider()->horizontal();
    if ($query->has('imodal')) {
        $ids1 = $query->retrieve('imodal', $refinery->identity());
        $ids2 = $query->retrieve('other', $refinery->identity());
        $out = $factory->listing()->characteristicValue()->text([
            'imodal' => print_r($ids1, true),
            'other' => print_r($ids2, true),
        ]);
    }

    return $renderer->render([$out, $divider, $modal, $trigger]);
}
