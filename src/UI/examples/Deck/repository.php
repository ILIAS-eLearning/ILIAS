<?php

/* Copyright (c) 2018 Jesús López <lopez@leifos.com> Extended GPL, see docs/LICENSE */

function repository()
{
    //Init Factory and Renderer
    global $DIC;
    $f = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();

    $icon = $f->icon()->standard('crs', 'Course')->withIsOutlined(true);

    $items = array(
        $f->button()->shy("Go to Course", "#"),
        $f->button()->shy("Go to Portfolio", "#"),
        $f->divider()->horizontal(),
        $f->button()->shy("ilias.de", "http://www.ilias.de")
    );

    $dropdown = $f->dropdown()->standard($items);


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
    )->withActions(
        $dropdown
    )->withCertificateIcon(
        true
    )->withSections(
        array(
            $content,
            $content,
        )
    );

    //Define the deck
    $deck = $f->deck(array($card,$card,$card,$card,$card,
        $card,$card,$card,$card))->withNormalCardsSize();

    //Render
    return $renderer->render($deck);
}
