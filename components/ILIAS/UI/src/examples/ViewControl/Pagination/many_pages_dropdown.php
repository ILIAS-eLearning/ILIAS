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

namespace ILIAS\UI\examples\ViewControl\Pagination;

/**
 * ---
 * description: >
 *   Example of rendering a pagination view control with many pages as dropdown
 *
 * expected output: >
 *   ILIAS shows two dropdown fields in between the "Back" (<) and "Next" (>) glyph. Clicking onto one of the dropdown fields
 *   will open a list of numbers. You can navigate to other pages through clicking the number in the dropdown control. If
 *   a number was clicked you will get redirected to the specific page and the number appears also in the label of the second
 *   dropdown. You can also use the glyphs to navigate through the pages, but please note that the "Back" glyph can't be used
 *   on the first page and the "Next" glyph can't be used on the last page.
 * ---
 */
function many_pages_dropdown()
{
    global $DIC;
    $factory = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();
    $url = $DIC->http()->request()->getRequestTarget();
    $refinery = $DIC->refinery();
    $request_wrapper = $DIC->http()->wrapper()->query();

    $parameter_name = 'page3';
    $current_page = 0;
    if ($request_wrapper->has($parameter_name)) {
        $current_page = $request_wrapper->retrieve($parameter_name, $refinery->kindlyTo()->int());
    }

    $pagination = $factory->viewControl()->pagination()
        ->withTargetURL($url, $parameter_name)
        ->withTotalEntries(102)
        ->withPageSize(10)
        ->withDropdownAt(5)
        ->withCurrentPage($current_page);

    $custom_pagination = $pagination
        ->withDropdownLabel('current page is %1$d (of %2$d pages in total)');

    list($range_offset, $range_length) = $pagination->getRange()->unpack();
    $result = "Show $range_length entries starting at $range_offset";

    return $renderer->render([$pagination, $custom_pagination])
        . '<hr>'
        . $result;
}
