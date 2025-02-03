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

namespace ILIAS\UI\examples\Menu\Drilldown;

/**
 * ---
 * description: >
 *   The example shows how to create and render a basic markdown field and attach it to a form.
 *
 *   0 Animal of the year
 *   1    Switzerland
 *   1.1    Riverine Amphipod (gammarus fossarum)
 *   1.2    Wildcat
 *   1.2.1    European Wildcat
 *   1.2.2    African Wildcat
 *   2    Germany
 *   2.1    Otter
 *   2.2    Mole
 *          --divider--
 *   2.3    Deer
 *
 * expected output: >
 *   ILIAS shows a box titled "Animal of the year" and two drilldowns including a hover effect.
 *   Clicking the drilldowns opens up more sub-entries or links pointing to the ILIAS page or open up a modal.
 *   Clicking the heading will lead one level back.
 * ---
 */
function drilldown()
{
    global $DIC;
    $f = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();

    $ico = $f->symbol()->icon()->standard('', '')->withSize('small')->withAbbreviation('+');
    $image = $f->image()->responsive("assets/ui-examples/images/Image/mountains.jpg", "Some mountains in the dusk");
    $page = $f->modal()->lightboxImagePage($image, 'Mountains');
    $modal = $f->modal()->lightbox($page);
    $button = $f->button()->bulky($ico->withAbbreviation('>'), 'Modal', '')
        ->withOnClick($modal->getShowSignal());

    $uri = new \ILIAS\Data\URI('https://ilias.de');
    $link = $f->link()->bulky($ico->withAbbreviation('>'), 'Link', $uri);
    $divider = $f->divider()->horizontal();

    $items = [
        $f->menu()->sub('Switzerland', [
            $f->menu()->sub('Riverine Amphipod', [$button, $link]),
            $f->menu()->sub('Wildcat', [
                $f->menu()->sub('European Wildcat', [$button, $link]),
                $f->menu()->sub('African Wildcat', [$button, $link, $divider, $link])
            ]),
            $button,
            $link
        ]),

        $f->menu()->sub('Germany', [
            $f->menu()->sub('Otter', [$button, $link]),
            $f->menu()->sub('Mole', [$button, $link]),
            $divider,
            $f->menu()->sub('Deer', [$button, $link])
        ])
    ];

    $dd = $f->menu()->drilldown('Animal of the year', $items);

    return $renderer->render([
        $dd,
        $modal
    ]);
}


function toBulky(string $label): \ILIAS\UI\Component\Button\Bulky
{
    global $DIC;
    $f = $DIC->ui()->factory();
    $ico = $f->symbol()->icon()->standard('', '')
        ->withSize('small')
        ->withAbbreviation('+');

    return $f->button()->bulky($ico, $label, '');
}
