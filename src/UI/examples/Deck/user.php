<?php
/* Copyright (c) 2016 Timon Amstutz <timon.amstutz@ilub.unibe.ch> Extended GPL, see docs/LICENSE */

function user() {
    //Init Factory and Renderer
    global $DIC;
    $f = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();

    $address = $f->listing()->descriptive(
        array(
            "Address" => "Hochschlustrasse 6",
            "" => "3006 Bern",
        )
    );

    $contact = $f->listing()->descriptive(
        array(
            "Contact" => "timon.amstutz@ilub.unibe.ch",
        )
    );

    $meta_content = $f->generic()->container(array($address,$contact));

    $actions = $f->generic()->container(array(
        $f->button()->standard("Request Contact",""),
        $f->button()->standard("Send Mail","")
    ));

    $image = $f->image()->responsive(
        "./templates/default/images/no_photo_xsmall.svg", "Thumbnail Example");

    $card = $f->card(
        "Timon Amstutz",
        $image
    )->withContentSections(array($meta_content,$actions));

    $deck = $f->deck(array($card,$card,$card,$card,$card,$card,$card))
        ->withCardsSize(ILIAS\UI\Component\Deck\Deck::SIZE_L);

    //Render
    return $renderer->render($deck);
}
