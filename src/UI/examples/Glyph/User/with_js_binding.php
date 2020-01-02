<?php
function with_js_binding()
{
    global $DIC;
    $f = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();

    return $renderer->render(
        $f->glyph()->user("#")
            ->withOnLoadCode(function ($id) {
                return
                    "$(\"#$id\").click(function() { alert(\"Clicked: $id\"); return false; });";
            })
    );
}
