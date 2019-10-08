<?php

function with_card()
{
    global $DIC;
    $f = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();

    $block = $f->panel()->standard(
        "Panel Title",
        $f->panel()->sub("Sub Panel Title", $f->legacy("Some Content"))
            ->withCard($f->card()->standard("Card Heading")->withSections(array($f->legacy("Card Content"))))
    );

    return $renderer->render($block);
}
