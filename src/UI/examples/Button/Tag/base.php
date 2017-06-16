<?php
function base() {
    global $DIC;
    $f = $DIC->ui()->factory();
    $df = new \ILIAS\Data\Factory;
    $renderer = $DIC->ui()->renderer();
    $buffer = array();

    $tag = $f->button()->tag("simple tag", "#");

    foreach (range(1,5) as $w) {
        $buffer[] = $renderer->render($tag->withRelevance($w));
    }

    $col = $df->color('#00ff00');
    $forecol = $df->color('#d4190b');

    $buffer[] = '<hr>with fix colors:<br>';
    $buffer[] = $renderer->render(
        $tag->withBackgroundColor($col)
            ->withForegroundColor($forecol)
    );

    $buffer[] = '<hr>with unavailable action:<br>';
    $tag = $tag->withUnavailableAction();
    foreach (range(1,5) as $w) {
        $buffer[] = $renderer->render($tag->withRelevance($w));
    }

    return implode(' ', $buffer);
}
