<?php

declare(strict_types=1);

namespace ILIAS\UI\examples\Legacy\LatexContent;

/**
 * ---
 * description: >
 *   Example for rendering a legacy content with laTeX code.
 *
 * expected output: >
 *   ILIAS shows a box including a formula which is rendered by MathJax.
 * ---
 */
function base()
{
    //Init Factory and Renderer
    global $DIC;
    $f = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();

    //Init Component
    $legacy = $f->legacy()->latexContent('[tex]f(x)=\int_{-\infty}^x e^{-t^2}dt[/tex]');

    //Render
    return $renderer->render($legacy);
}
