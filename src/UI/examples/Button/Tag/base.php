<?php
function base() {
    global $DIC;
    $f = $DIC->ui()->factory();
    $df = new \ILIAS\Data\Factory;
    $renderer = $DIC->ui()->renderer();
    $buffer = array();

    $tag = $f->button()->tag("simple tag", "#");

    $possible_relevances = array(
        $tag::REL_VERYLOW,
        $tag::REL_LOW,
        $tag::REL_MID,
        $tag::REL_HIGH,
        $tag::REL_VERYHIGH
    );

    foreach ($possible_relevances as $w) {
        $buffer[] = $renderer->render($tag->withRelevance($w));
    }

    $buffer[] = '<hr>with unavailable action:<br>';
    $tag = $tag->withUnavailableAction();
    foreach ($possible_relevances as $w) {
        $buffer[] = $renderer->render($tag->withRelevance($w));
    }

    $buffer[] = '<hr>with additional class(es):<br>';
    $buffer[] = $renderer->render(
        $tag->withClasses(array('std_col_1'))
    );

    $buffer[] = $renderer->render(
        $tag->withClasses(array('std_col_1', 'std_bold'))
    );

    $lightcol = $df->color('#00ff00');
    $darkcol = $df->color('#00aa00');
    $forecol = $df->color('#d4190b');

    $buffer[] = '<hr>with fix colors:<br>';
    $tag = $tag->withBackgroundColor($lightcol);
    $buffer[] = $renderer->render($tag);
    $buffer[] = $renderer->render($tag->withBackgroundColor($darkcol));

    $buffer[] = '<br><br>';
    $buffer[] = $renderer->render(
        $tag->withBackgroundColor($lightcol)
            ->withForegroundColor($forecol)
    );

    return implode(' ', $buffer);
}
