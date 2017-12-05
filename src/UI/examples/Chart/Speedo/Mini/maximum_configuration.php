<?php
/**
 * Example for rendering a mini Speedo with minimum configuration
 */
function maximum_configuration() {
    //Loading factories
    global $DIC;
    $f = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();

    //Genarating and rendering the mini speedo
    $speedo = $f->chart()->speedo()->mini(100, 50, 75);

    // render
    return $renderer->render($speedo);
}
