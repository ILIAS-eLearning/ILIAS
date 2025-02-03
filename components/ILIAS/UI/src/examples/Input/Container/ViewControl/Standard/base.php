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
 *   ILIAS shows the rendered viewcontrols Pagination, Sortation and Field Selection.
 *   Above, the current values are displayed as an array withe the keys
 *   vc_range (for the pagination), vc_sortation and vc_columns (for the field selection).
 *   All of them are operable, i.e. changing any value will reload the page.
 *   The altered values are reflected in the results.
 * ---
 */
function base()
{
    global $DIC;
    $f = $DIC->ui()->factory();
    $r = $DIC->ui()->renderer();
    $refinery = $DIC->refinery();
    $request = $DIC->http()->request();

    $vcs = [
        $f->input()->viewControl()->pagination()
            ->withAdditionalTransformation(
                $refinery->custom()->transformation(
                    fn($v) => ['vc_range' => $v]
                )
            )
            ->withTotalCount(312)
            ->withValue([Pagination::FNAME_OFFSET => 0, Pagination::FNAME_LIMIT => 50]),

        $f->input()->viewControl()->sortation([
                'Field 1, ascending' => new Order('field1', 'ASC'),
                'Field 1, descending' => new Order('field1', 'DESC'),
                'Field 2, descending' => new Order('field2', 'ASC')
            ])
            ->withAdditionalTransformation(
                $refinery->custom()->transformation(
                    fn($v) => ['vc_sortation' => $v]
                )
            )
            ->withValue(['field2', 'ASC']),

        $f->input()->viewControl()->fieldSelection([
                'field1' => 'Feld 1',
                'field2' => 'Feld 2'
            ], 'shown columns', 'apply')
            ->withAdditionalTransformation(
                $refinery->custom()->transformation(
                    fn($v) => ['vc_columns' => $v]
                )
            )
            ->withValue(['field1','field2']),
        $f->input()->viewControl()->mode([
                'mode1' => 'a mode',
                'mode2' => 'another mode'
            ])
            ->withAdditionalTransformation(
                $refinery->custom()->transformation(
                    fn($v) => ['vc_mode' => $v]
                )
            )
            ->withValue('mode2'),
    ];

    $vc_container = $f->input()->container()->viewControl()->standard($vcs)
         ->withAdditionalTransformation(
             $refinery->custom()->transformation(
                 fn($v) => array_filter(array_values($v)) === [] ? null : array_merge(...array_values($v))
             )
         )
        ->withRequest($request);

    return $r->render([
        $f->legacy()->content('<pre>' . print_r($vc_container->getData(), true) . '</pre>'),
        $f->divider()->horizontal(),
        $vc_container
    ]);
}
