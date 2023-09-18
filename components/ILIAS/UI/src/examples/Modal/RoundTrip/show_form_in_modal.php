<?php

declare(strict_types=1);

namespace ILIAS\UI\examples\Modal\RoundTrip;

function show_form_in_modal()
{
    global $DIC;

    $renderer = $DIC->ui()->renderer();
    $request = $DIC->http()->request();
    $factory = $DIC->ui()->factory();

    // declare roundtrip with inputs and form action.
    $modal = $factory->modal()->roundtrip(
        'roundtrip with form',
        null,
        [
            $factory->input()->field()->text('some text'),
            $factory->input()->field()->numeric('some numbere'),
        ],
        '#'
    );

    // declare something that triggers the modal.
    $open = $factory->button()->standard('open modal', '#')->withOnClick($modal->getShowSignal());

    // please use ilCtrl to generate an appropriate link target
    // and check it's command instead of this.
    if ('POST' === $request->getMethod()) {
        $modal = $modal->withRequest($request);
        $data = $modal->getData();
    } else {
        $data = 'no results yet.';
    }

    return
        '<pre>' . print_r($data, true) . '</pre>' .
        $renderer->render([$open, $modal]);
}
