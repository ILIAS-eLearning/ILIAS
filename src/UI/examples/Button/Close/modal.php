<?php declare(strict_types=1);

namespace ILIAS\UI\examples\Button\Close;

/**
 * This second example shows a scenario in which the Close Button is used in an overlay
 * as indicated in the purpose description. Note that in the Modal the Close Button
 * is properly placed in the top right corner.
 */
function modal()
{
    global $DIC;
    $factory = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();

    $modal = $factory->modal()->roundtrip(
        'Close Button Demo',
        $factory->legacy('See the Close Button in the top right corner.')
    );
    $button1 = $factory->button()->standard('Show Close Button Demo', '#')
        ->withOnClick($modal->getShowSignal());

    return $renderer->render([$button1, $modal]);
}
