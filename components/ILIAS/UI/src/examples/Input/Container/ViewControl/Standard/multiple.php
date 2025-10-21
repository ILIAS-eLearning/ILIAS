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

namespace ILIAS\UI\examples\Input\Container\ViewControl\Standard;

use ILIAS\Data\Order;
use ILIAS\UI\Implementation\Component\Input\ViewControl\Pagination;

/**
 * ---
 * expected output: >
 *   ILIAS shows three containers with a variety and doubles of View Controls.
 *   Each View Controls may be oeprated independently of all others, i.e., the
 *   selected values are reflected in the respective Control only.
 *   Above the View Controls, the names of the (internal) fields are shown -
 *   they should be unique.
 * ---
 */
function multiple()
{
    global $DIC;
    $f = $DIC->ui()->factory();
    $r = $DIC->ui()->renderer();
    $refinery = $DIC->refinery();
    $request = $DIC->http()->request();

    $vcs = [

        $f->input()->viewControl()->mode([
                'mode1' => 'Mode 1',
                'mode2' => 'Mode 2'
            ])
            ->withValue('mode2'),

        $f->input()->viewControl()->mode([
                'mode1a' => 'Mode 1a',
                'mode2a' => 'Mode 2a'
            ])
            ->withValue('mode1a'),

        $f->input()->viewControl()->sortation([
            'Field 1, ascending' => new Order('field1', 'ASC'),
            'Field 1, descending' => new Order('field1', 'DESC'),
        ]),
        $f->input()->viewControl()->sortation([
            'Field 1, ascending' => new Order('field1', 'ASC'),
            'Field 1, descending' => new Order('field1', 'DESC'),
        ]),
        $f->input()->viewControl()->fieldSelection([
            'field1' => 'Feld 1',
            'field2' => 'Feld 2'
        ], 'shown columns', 'apply'),
        $f->input()->viewControl()->fieldSelection([
            'field1' => 'Feld 1',
            'field2' => 'Feld 2'
        ], 'shown columns', 'apply')
    ];

    $vc_fac = $f->input()->container()->viewControl();

    $vc_container = $vc_fac->standard($vcs)
        ->withRequest($request);
    $vc_container2 = $vc_fac->standard($vcs)
        ->withRequest($request);
    $vc_container3 = $f->input()->container()->viewControl()->standard($vcs)
        ->withRequest($request);


    $fn = fn($i) => $i->getName()
        ?? array_map(fn($ii) => $ii->getName(), $i->getInputGroup()->getInputs());
    $names = array_map($fn, $vc_container->getInputs());
    $names2 = array_map($fn, $vc_container2->getInputs());
    $names3 = array_map($fn, $vc_container3->getInputs());

    return $r->render([
        $f->legacy()->content('<pre>'),
        $f->legacy()->content(print_r(json_encode($names, JSON_PRETTY_PRINT), true)),
        $f->divider()->horizontal(),
        $f->legacy()->content(print_r(json_encode($names2, JSON_PRETTY_PRINT), true)),
        $f->divider()->horizontal(),
        $f->legacy()->content(print_r(json_encode($names3, JSON_PRETTY_PRINT), true)),
        $f->legacy()->content('</pre>'),
        $f->divider()->horizontal(),
        $vc_container,
        $f->divider()->horizontal(),
        $vc_container2,
        $f->divider()->horizontal(),
        $vc_container3,
    ]);
}
