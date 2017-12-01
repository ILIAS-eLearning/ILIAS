<?php
/**
 * Max Example for rendering a standard Speedo with an diagnostic score only
 */
function only_diagnostic_score() {
    //Loading factories
    global $DIC;
    $f = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();

    //Genarating and rendering the standard speedo
    $speedo = $f->chart()->speedo()->standard(array(
        'goal' => 400,
        'score' => 0,
        'diagnostic' => 200,
    ));

    // render
    return $renderer->render($speedo);
}
