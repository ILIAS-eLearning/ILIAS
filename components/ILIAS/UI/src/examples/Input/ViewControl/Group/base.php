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

namespace ILIAS\UI\examples\Input\ViewControl\Group;

use ILIAS\Data\Factory as DataFactory;
use ILIAS\Data\Order;
use ILIAS\UI\Implementation\Component\Input\ViewControl\Pagination;

/**
 * ---
 * expected output: >
 *   ILIAS shows the rendered Component.
 * ---
 */
function base(): string
{
    global $DIC;
    $f = $DIC->ui()->factory();
    $r = $DIC->ui()->renderer();
    $d = new DataFactory();

    $pagination = $f
        ->input()
        ->viewControl()
        ->pagination()
        ->withTotalCount(932)
        ->withValue([Pagination::FNAME_OFFSET => 31, Pagination::FNAME_LIMIT => 10]);

    $sortation = $f->input()->viewControl()->sortation([
        'Field 1, ascending' => $d->order('field1', Order::ASC),
        'Field 1, descending' => $d->order('field1', Order::DESC),
        'Field 2, descending' => $d->order('field2', Order::DESC),
    ]);

    $group = $f->input()->viewControl()->group([$pagination, $sortation]);

    // view this in a ViewControlContainer with active request
    $vc_container = $f->input()->container()->viewControl()->standard([$group])->withRequest(
        $DIC->http()->request()
    );

    return $r->render([
        $f->legacy()->content('<pre>' . print_r($vc_container->getData(), true) . '</pre>'),
        $vc_container
    ]);
}
