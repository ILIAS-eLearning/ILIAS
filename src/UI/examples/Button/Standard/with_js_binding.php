<?php declare(strict_types=1);

namespace ILIAS\UI\examples\Button\Standard;

function with_js_binding()
{
    global $DIC;
    $f = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();

    return $renderer->render(
        $f->button()->standard("Goto ILIAS", "#")
            ->withOnLoadCode(function ($id) {
                return
                    "$(\"#$id\").click(function() { alert(\"Clicked: $id\"); return false;});";
            })
    );
}
