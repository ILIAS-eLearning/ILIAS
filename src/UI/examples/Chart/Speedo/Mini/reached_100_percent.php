<?php
/**
 * Example for rendering a mini Speedo when 100% are reached
 */
function reached_100_percent() {
    //Loading factories
    global $DIC;
    $f = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();

    //Genarating and rendering the mini speedo
    $speedo = $f->chart()->speedo()->mini(100, 100);

    // render
    return $renderer->render($speedo);
}
