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

namespace ILIAS\UI\examples\Listing\CharacteristicValue\Text;

/**
 * ---
 * description: >
 *   Example for rendering a characteristic text value listing.
 *
 * expected output: >
 *   ILIAS shows a box with a two columns list. The list includes 4 lines which are separated by a fine line. A short
 *   description (e.g. third Item Label) is displayed in the first row. In the second row all items are listed with a
 *   number (e.g. Item 3).
 * ---
 */
function base()
{
    global $DIC; /* @var \ILIAS\DI\Container $DIC */
    $f = $DIC->ui()->factory();
    $r = $DIC->ui()->renderer();

    $items = [
        'Any Label for the First Item' => 'Item 1',
        'Another Label for the Second Item' => 'Item 2',
        'Third Item Label' => 'Item 3',
        'Fourth Item Label' => 'Item 4'
    ];

    $listing = $f->listing()->characteristicValue()->text($items);

    return $r->render($listing);
}
