<?php
/**
 * Example for rendering a responsive Speedo with an diagnostic score only
 */
function only_diagnostic_score() {
    //Loading factories
    global $DIC;
    $f = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();

    //Genarating and rendering the responsive speedo
    $speedo = $f->chart()->speedo()->responsive(100, 0, 75, 50);

    // render
    return $renderer->render($speedo);
}
