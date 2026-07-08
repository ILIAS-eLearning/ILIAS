<?php

declare(strict_types=1);

namespace ILIAS\UI\examples\Listing\Inline;

/**
 * ---
 * description: >
 *   Example for rendering an inline list.
 *
 * expected output: >
 *   ILIAS shows the elements of a list horizontally in a row, separated by commas.
 * ---
 */
function base(): string
{
    //Init Factory and Renderer
    global $DIC;
    $f = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();

    //Generate List
    $inline = $f->listing()->inline(
        ["Apple","Banana","Milk", "Toast", "Pumpkin Pie", "Bread"]
    );

    //Render
    return $renderer->render($inline);
}
