<?php declare(strict_types=1);

namespace ILIAS\UI\examples\MainControls\Slate\Drilldown;

use ILIAS\UI\examples\Menu\Drilldown;

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
