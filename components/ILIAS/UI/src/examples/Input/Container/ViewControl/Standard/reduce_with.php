<?php

declare(strict_types=1);

namespace ILIAS\UI\examples\Input\Container\ViewControl\Standard;

use ILIAS\Data\Order;
use ILIAS\UI\Implementation\Component\Input\ViewControl\Pagination;

/**
 * ---
 * expected output: >
 *   ILIAS shows a JSON like that:
 *   {
 *       "Standard View Control Container Input": [
 *          {
 *              "Pagination View Control Input": []
 *          },
 *          {
 *              "Sortation View Control Input": []
 *          },
 *          {
 *              "Field Selection View Control Input": []
 *          }
 *       ]
 *   }
 * ---
 */
function reduce_with()
{
    global $DIC;
    $f = $DIC->ui()->factory();
    $r = $DIC->ui()->renderer();

    $vcs = [
        $f->input()->viewControl()->pagination(),
        $f->input()->viewControl()->sortation([
                'Field 1, ascending' => new Order('field1', 'ASC'),
                'Field 1, descending' => new Order('field1', 'DESC'),
                'Field 2, descending' => new Order('field2', 'ASC')
            ]),
        $f->input()->viewControl()->fieldSelection([
                'field1' => 'Feld 1',
                'field2' => 'Feld 2'
            ], 'shown columns', 'apply'),
    ];

    $vc_container = $f->input()->container()->viewControl()->standard($vcs);

    $array = $vc_container->reduceWith(
        fn($c, $res) => [$c->getCanonicalName() => $res]
    );

    return $r->render([
        $f->legacy()->content('<pre>' . print_r(json_encode($array, JSON_PRETTY_PRINT), true) . '</pre>')
    ]);
}
