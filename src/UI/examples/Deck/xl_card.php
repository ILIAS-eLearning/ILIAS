<?php

/* Copyright (c) 2016 Timon Amstutz <timon.amstutz@ilub.unibe.ch> Extended GPL, see docs/LICENSE */

function xl_card()
{
    //Init Factory and Renderer
    global $DIC;
    $f = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();

    //Generate some content
    $content = $f->listing()->descriptive(
        array(
            "Entry 1" => "Some text",
            "Entry 2" => "Some more text",
        )
    );

    //Define the some responsive image
    $image = $f->image()->responsive(
        "./templates/default/images/HeaderIcon.svg",
        "Thumbnail Example"
    );

    //Define the card by using the content and the image
    $card = $f->card()->standard(
        "Title",
        $image
    )->withSections(array(
        $content
    ));

    //Define the extra large deck
    $deck = $f->deck(array($card,$card,$card))->withExtraLargeCardsSize();

    //Render
    return $renderer->render($deck);
}
