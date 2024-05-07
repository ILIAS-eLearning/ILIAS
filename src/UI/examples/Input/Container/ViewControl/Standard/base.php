<?php

declare(strict_types=1);

namespace ILIAS\UI\examples\Input\Container\ViewControl\Standard;

use ILIAS\Data\Order;
use ILIAS\UI\Implementation\Component\Input\ViewControl\Pagination;

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
                    fn ($v) => ['vc_range' => $v]
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
                    fn ($v) => ['vc_sortation' => $v]
                )
            )
            ->withValue(['field2', 'ASC']),

        $f->input()->viewControl()->fieldSelection([
                'field1' => 'Feld 1',
                'field2' => 'Feld 2'
            ], 'shown columns', 'apply')
            ->withAdditionalTransformation(
                $refinery->custom()->transformation(
                    fn ($v) => ['vc_columns' => $v]
                )
            )
            ->withValue(['field1','field2']),
    ];

    $vc_container = $f->input()->container()->viewControl()->standard($vcs)
         ->withAdditionalTransformation(
             $refinery->custom()->transformation(
                 fn ($v) => array_filter(array_values($v)) === [] ? null : array_merge(...array_values($v))
             )
         )
        ->withRequest($request);

    return $r->render([
        $f->legacy('<pre>' . print_r($vc_container->getData(), true) . '</pre>'),
        $f->divider()->horizontal(),
        $vc_container
    ]);
}
