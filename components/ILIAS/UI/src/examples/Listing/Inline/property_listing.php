<?php

declare(strict_types=1);

namespace ILIAS\UI\examples\Listing\Inline;

/**
 * ---
 * description: >
 *   Example for rendering an inline list inside a property list.
 *
 * expected output: >
 *   ILIAS shows two properties in a single row (if space allows for it).
 *   One is a "Languages" property followed with flag icons.
 *   The other property lists video resolutions as text.
 *   The values are separated by commas.
 * ---
 */
function property_listing(): string
{
    //Init Factory and Renderer
    global $DIC;
    $f = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();


    $flag_de = $f->symbol()->icon()->custom(
        "data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIGlkPSJmbGFnLWljb25zLWRlIiB2aWV3Qm94PSIwIDAgNjQwIDQ4MCI+CiAgPHBhdGggZmlsbD0iI2ZjMCIgZD0iTTAgMzIwaDY0MHYxNjBIMHoiLz4KICA8cGF0aCBmaWxsPSIjMDAwMDAxIiBkPSJNMCAwaDY0MHYxNjBIMHoiLz4KICA8cGF0aCBmaWxsPSJyZWQiIGQ9Ik0wIDE2MGg2NDB2MTYwSDB6Ii8+Cjwvc3ZnPgo=",
        "German"
    );

    $flag_gb = $f->symbol()->icon()->custom(
        "data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIGlkPSJmbGFnLWljb25zLWdiIiB2aWV3Qm94PSIwIDAgNjQwIDQ4MCI+CiAgPHBhdGggZmlsbD0iIzAxMjE2OSIgZD0iTTAgMGg2NDB2NDgwSDB6Ii8+CiAgPHBhdGggZmlsbD0iI0ZGRiIgZD0ibTc1IDAgMjQ0IDE4MUw1NjIgMGg3OHY2Mkw0MDAgMjQxbDI0MCAxNzh2NjFoLTgwTDMyMCAzMDEgODEgNDgwSDB2LTYwbDIzOS0xNzhMMCA2NFYweiIvPgogIDxwYXRoIGZpbGw9IiNDODEwMkUiIGQ9Im00MjQgMjgxIDIxNiAxNTl2NDBMMzY5IDI4MXptLTE4NCAyMCA2IDM1TDU0IDQ4MEgwek02NDAgMHYzTDM5MSAxOTFsMi00NEw1OTAgMHpNMCAwbDIzOSAxNzZoLTYwTDAgNDJ6Ii8+CiAgPHBhdGggZmlsbD0iI0ZGRiIgZD0iTTI0MSAwdjQ4MGgxNjBWMHpNMCAxNjB2MTYwaDY0MFYxNjB6Ii8+CiAgPHBhdGggZmlsbD0iI0M4MTAyRSIgZD0iTTAgMTkzdjk2aDY0MHYtOTZ6TTI3MyAwdjQ4MGg5NlYweiIvPgo8L3N2Zz4K",
        "English"
    );

    $languages = $f->listing()->inline([$flag_de, $flag_gb]);
    $resolutions = $f->listing()->inline(["480p", "720p", "1080p", "4k"]);

    $video_properties = $f->listing()->property()
        ->withProperty("Languages", $languages)
        ->withProperty("Resolutions", $resolutions);

    //Render
    return $renderer->render($video_properties);
}
