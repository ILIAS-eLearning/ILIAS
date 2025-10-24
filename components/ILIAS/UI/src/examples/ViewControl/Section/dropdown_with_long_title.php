<?php

declare(strict_types=1);

namespace ILIAS\UI\examples\ViewControl\Section;

/**
 * ---
 * expected output: >
 *   ILIAS shows three controls next to each other: A "Back" glyph, a dropdown
 *   "Second section with a very long title to check the responsive behaviour" and a "Next" glyph.
 *   When clicking on "Second Section..." a dropdown with three entries "First Section", "Second Section..."
 *   and "Third Section" is displayed. The dropdown button will have a maximum size of
 *   around 50% of the view width on smaller screens and around 400px on larger screens.
 * ---
 */
function dropdown_with_long_title(): string
{
    //Loading factories
    global $DIC;
    $f = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();


    //Here the real magic to draw the controls
    $back = $f->button()->standard("Back", "#");
    $next = $f->button()->standard("Next", "#");
    $middle = $f->dropdown()->standard(
        [
            $f->link()->standard("First Section", "#"),
            $f->link()->standard("Second section with a very long title to check the responsive behaviour", "#"),
            $f->link()->standard("Third Section", "#")
        ]
    )->withLabel("Second section with a very long title to check the responsive behaviour");
    $view_control_section = $f->viewControl()->section($back, $middle, $next);
    $html = $renderer->render($view_control_section);
    return $html;
}
