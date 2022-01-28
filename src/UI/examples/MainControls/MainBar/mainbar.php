<?php declare(strict_types=1);

namespace ILIAS\UI\examples\MainControls\MainBar;

function mainbar()
{
    global $DIC;
    $f = $DIC['ui.factory'];
    $renderer = $DIC['ui.renderer'];

    $url = 'src/UI/examples/Layout/Page/Standard/ui_mainbar.php?ui_mainbar=1';
    $to_page = $f->button()->standard('Mainbar', $url);
    $txt = $f->legacy('<p>Better head over to a preview of page to see a mainbar in its entire beauty...</p>');
    return $renderer->render([
        $txt,
        $to_page
    ]);
}
