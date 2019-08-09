<?php
function with_load_anim()
{
    global $DIC;
    $f = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();

    return $renderer->render(
        $f->button()->primary("Goto ILIAS", "")
            ->withLoadingAnimationOnClick(true)
            ->withOnLoadCode(function ($id) {
                return
                    "$('#$id').click(function(e) { if (!$('#$id').hasClass('disabled')) {alert('Do Stuff');}});";
            })
    );
}
