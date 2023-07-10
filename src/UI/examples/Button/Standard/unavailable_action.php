<?php

declare(strict_types=1);

namespace ILIAS\UI\examples\Button\Standard;

/**
 * This example provides the given button with an unavailable action. Note
 * that the disabled attribute is set in the DOM. No action must be fired, even
 * if done by keyboard
 */
function unavailable_action()
{
    global $DIC;
    $f = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();

    $button = $f->button()->standard('Unavailable', '#')->withUnavailableAction();

    return $renderer->render([$button]);
}
