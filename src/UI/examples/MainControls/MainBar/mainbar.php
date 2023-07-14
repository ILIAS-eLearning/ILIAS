<?php

declare(strict_types=1);

namespace ILIAS\UI\examples\MainControls\MainBar;

function mainbar(): string
{
    global $DIC;
    $f = $DIC['ui.factory'];
    $renderer = $DIC['ui.renderer'];
    $ctrl = $DIC['ilCtrl'];


    $ctrl->setParameterByClass('ilsystemstyledocumentationgui', 'node_id', 'LayoutPageStandardStandard');
    $ctrl->setParameterByClass('ilsystemstyledocumentationgui', 'new_ui', '1');
    $url = $ctrl->getLinkTargetByClass('ilsystemstyledocumentationgui', 'entries');
    $to_page = $f->link()->standard('Full Screen Page Layout', $url);
    $txt = $f->legacy('<p>Better head over to a preview of page to see a mainbar in its entire beauty...</p>');
    return $renderer->render([
        $txt,
        $to_page
    ]);
}
