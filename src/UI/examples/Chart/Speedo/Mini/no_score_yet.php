<?php
/**
 * Example for rendering a mini Speedo when no score is given
 */
function no_score_yet() {
    //Loading factories
    global $DIC;
    $f = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();

    //Genarating and rendering the mini speedo
    $speedo = $f->chart()->speedo()->mini(100,0);

    // render
    return $renderer->render($speedo);
}
