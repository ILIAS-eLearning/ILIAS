<?php
function footer()
{
    global $DIC;
    $f = $DIC->ui()->factory();
    $df = new \ILIAS\Data\Factory();
    $renderer = $DIC->ui()->renderer();

    $text = 'Additional info:';
    $links = [];
    $links[] = $f->link()->standard("Goto ILIAS", "http://www.ilias.de");
    $links[] = $f->link()->standard("Goto ILIAS", "http://www.ilias.de");

    $footer = $f->mainControls()->footer($links, $text)
        ->withPermanentURL(
            $df->uri(
                isset($_SERVER['REQUEST_SCHEME']) ?  $_SERVER['REQUEST_SCHEME']:"http" .
                '://' .
                    (isset($_SERVER['SERVER_NAME']) ?  $_SERVER['SERVER_NAME']:"localhost") .
                ':' .
                    (isset($_SERVER['SERVER_PORT']) ?  $_SERVER['SERVER_PORT']:"80") .
                '/' . ltrim(str_replace(
                    'ilias.php',
                    'goto.php?target=xxx12345',
                    isset($_SERVER['SCRIPT_NAME']) ?  $_SERVER['SCRIPT_NAME']:""
                ), '/')
            )
        );

    return $renderer->render($footer);
}
