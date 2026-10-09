<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

declare(strict_types=1);

namespace ILIAS\UI\examples\Input\ViewControl\Section;

/**
 * ---
 * description: >
 *   Example of a Section View Control using a Dropdown to navigate between non-adjacent sections.
 *
 * expected output: >
 *   ILIAS shows three controls next to each other: a "Back" glyph, a dropdown "Second Section" and a "Next" glyph.
 *   The current section is shown below the controls. Clicking "Back" or "Next" reloads the page with the current
 *   section changed. Selecting an entry in the dropdown reloads the page with the selected section.
 * ---
 */
function dropdown(): string
{
    global $DIC;
    $f = $DIC->ui()->factory();
    $r = $DIC->ui()->renderer();
    $request = $DIC->http()->request();

    $sections = ["First Section", "Second Section", "Third Section"];
    $parameter = "Section";
    $current_section = 1;
    $query_params = $request->getQueryParams();
    if (isset($query_params[$parameter])
        && (is_int($query_params[$parameter])
            || (is_string($query_params[$parameter]) && ctype_digit($query_params[$parameter])))
        && array_key_exists((int) $query_params[$parameter], $sections)
    ) {
        $current_section = (int) $query_params[$parameter];
    }

    $target = $request->getRequestTarget();
    $separator = str_contains($target, "?") ? "&" : "?";
    $action = static fn(int $section): string => $target . $separator . $parameter . "=" . $section;

    $back = $f->button()->standard("Back", $action(($current_section - 1 + count($sections)) % count($sections)));
    $next = $f->button()->standard("Next", $action(($current_section + 1) % count($sections)));
    $section_links = [];
    foreach ($sections as $index => $label) {
        $section_links[] = $f->link()->standard($label, $action($index));
    }
    $middle = $f->dropdown()->standard($section_links)->withLabel($sections[$current_section]);
    $section = $f->input()->viewControl()->section($back, $middle, $next);

    $container = $f->input()->container()->viewControl()->standard([$section])
        ->withRequest($request);

    return $r->render([
        $f->legacy()->content('<p role="status">Current section: ' . $sections[$current_section] . '</p>'),
        $container
    ]);
}
