<?php

function with_view_controls()
{
    global $DIC;

    $factory = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();

    $actions = $factory->dropdown()->standard(array(
        $factory->button()->shy("ILIAS", "https://www.ilias.de"),
        $factory->button()->shy("GitHub", "https://www.github.com")
    ));

    $legacy = $factory->legacy("Legacy content");

    $url = $DIC->http()->request()->getRequestTarget();

    $sort_options = array(
        'internal_rating' => 'Best',
        'date_desc' => 'Most Recent',
        'date_asc' => 'Oldest',
    );
    $sortation = $factory->viewControl()->sortation($sort_options)->withTargetURL($url, "");

    $parameter_name = 'page';
    $current_page = (int) (array_key_exists($parameter_name, $_GET) ? $_GET[$parameter_name] : 0);

    $pagination = $factory->viewControl()->pagination()
        ->withTargetURL($url, $parameter_name)
        ->withTotalEntries(98)
        ->withPageSize(10)
        ->withCurrentPage($current_page);


    $panel = $factory->panel()->standard(
        "Panel Title",
        $factory->legacy("Some Content")
    )
        ->withActions($actions)
        ->withViewControls(array($sortation, $pagination));

    return $renderer->render($panel);
}
