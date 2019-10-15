<?php
/**
 * Note that this example is rather artificial, since the close button is only used in other components
 * (see purpose). This examples just shows how one could render the button if implementing
 * such a component. Note that in some cases additional CSS would be needed for placing
 * the button properly by the surrounding component.
 */
function base()
{
    global $DIC;
    $f = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();

    return $renderer->render($f->button()->close());
}
