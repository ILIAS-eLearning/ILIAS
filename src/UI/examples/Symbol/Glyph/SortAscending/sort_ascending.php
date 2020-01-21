<?php
/**
 * Note that this example does not provide any functionality, it just shows, how this Glyph
 * can be rendered. The functionality needs to be provided by some surrounding component (e.g. Table)
 */
function sort_ascending()
{
    global $DIC;
    $f = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();

    $glyph = $f->symbol()->glyph()->sortAscending("#");

    //Showcase the various states of this Glyph
    $list = $f->listing()->descriptive([
        "Active"=>$glyph,
        "Inactive"=>$glyph->withUnavailableAction(),
        "Highlighted"=>$glyph->withHighlight()
    ]);

    return $renderer->render($list);
}
