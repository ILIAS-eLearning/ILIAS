<?php

declare(strict_types=1);

namespace ILIAS\UI\examples\Card\RepositoryObject;

function with_object_icon_and_progressmeter_mini()
{
    //Init Factory and Renderer
    global $DIC;
    $f = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();

    $icon = $f->symbol()->icon()->standard("crs", 'Course');
    $progressmeter = $f->chart()->progressMeter()->mini(100, 70);

    $content = $f->listing()->descriptive(
        array(
            "Entry 1" => "Some text",
            "Entry 2" => "Some more text",
        )
    );

    $image = $f->image()->responsive(
        "./templates/default/images/HeaderIcon.svg",
        "Thumbnail Example"
    );

    $card = $f->card()->repositoryObject(
        "Title",
        $image
    )->withObjectIcon(
        $icon
    )->withProgress(
        $progressmeter
    )->withSections(
        array(
            $content,
            $content,
        )
    );

    //Render
    return $renderer->render($card);
}
