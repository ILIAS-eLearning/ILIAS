<?php
/**
 * Max Example for rendering a responsive Speedo with an diagnostic score only
 */
function only_diagnostic_score() {
    //Loading factories
    global $DIC;
    $f = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();

    //Genarating and rendering the responsive speedo
    $speedo = $f->chart()->speedo()->responsive(array(
        'goal' => 400,
        'score' => 0,
        'diagnostic' => 200,
    ));

    // render
    return $renderer->render($speedo);
}
