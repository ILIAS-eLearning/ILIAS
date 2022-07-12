<?php declare(strict_types=1);

namespace ILIAS\UI\examples\Panel\Secondary\Legacy;

function with_viewcontrollers()
{
    global $DIC;

    $factory = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();
    $refinery = $DIC->refinery();
    $request_wrapper = $DIC->http()->wrapper()->query();

    $actions = $factory->dropdown()->standard(array(
        $factory->button()->shy("ILIAS", "https://www.ilias.de"),
        $factory->button()->shy("GitHub", "https://www.github.com")
    ));

    $legacy = $factory->legacy("Legacy content");

    $sort_options = array(
        'internal_rating' => 'Best',
        'date_desc' => 'Most Recent',
        'date_asc' => 'Oldest',
    );
    $sortation = $factory->viewControl()->sortation($sort_options);


    $url = $DIC->http()->request()->getRequestTarget();

    $parameter_name = 'page';
    $current_page = 0;
    if ($request_wrapper->has($parameter_name)) {
        $current_page = $request_wrapper->retrieve($parameter_name, $refinery->kindlyTo()->int());
    }

    $pagination = $factory->viewControl()->pagination()
        ->withTargetURL($url, $parameter_name)
        ->withTotalEntries(98)
        ->withPageSize(10)
        ->withCurrentPage($current_page);

    $panel = $factory->panel()->secondary()->legacy("panel title", $legacy)->withViewControls(array($sortation, $pagination))->withActions($actions);

    return $renderer->render($panel);
}
