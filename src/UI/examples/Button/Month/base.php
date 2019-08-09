<?php
function base()
{
    global $DIC;
    $f = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();

    return $renderer->render($f->button()->month("02-2017")->withOnLoadCode(function ($id) {
        return "$(\"#$id\").on('il.ui.button.month.changed', function(el, id, month) { alert(\"Clicked: \" + id + ' with ' + month);});";
    }));
}
