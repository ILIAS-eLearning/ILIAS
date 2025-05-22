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

namespace ILIAS\UI\examples\MainControls\Slate\Drilldown;

use ILIAS\UI\examples\Menu\Drilldown;

/**
 * ---
 * expected output: >
 *   ILIAS shows the rendered Component.
 * ---
 */
function drilldownslate()
{
    global $DIC;
    $f = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();

    $ico = $f->symbol()->icon()->standard('', '')->withSize('small')->withAbbreviation('+');
    $uri = new \ILIAS\Data\URI('https://ilias.de');
    $link = [$f->link()->bulky($ico->withAbbreviation('>'), 'Link', $uri)];

    $items = [
        $f->menu()->sub('Switzerland', [
            $f->menu()->sub('Riverine Amphipod', $link),
            $f->menu()->sub('Wildcat', [
                $f->menu()->sub('European Wildcat', $link),
                $f->menu()->sub('African Wildcat', $link)
            ]),
            $link[0]
        ]),

        $f->menu()->sub('Germany', [
            $f->menu()->sub('Otter', $link),
            $f->menu()->sub('Mole', $link),
            $f->menu()->sub('Deer', $link)
        ])
    ];

    $ddmenu = $f->menu()->drilldown('Animal of the year', $items);

    $icon = $f->symbol()->glyph()->comment();
    $slate = $f->maincontrols()->slate()->drilldown('drilldown example', $icon, $ddmenu);

    $triggerer = $f->button()->bulky(
        $slate->getSymbol(),
        $slate->getName(),
        '#'
    )
    ->withOnClick($slate->getToggleSignal());

    return $renderer->render([
        $triggerer,
        $slate
    ]);
}
