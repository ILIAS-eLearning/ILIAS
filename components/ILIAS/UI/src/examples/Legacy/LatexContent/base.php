<?php

declare(strict_types=1);

namespace ILIAS\UI\examples\Legacy\LatexContent;

/**
 * ---
 * description: >
 *   Example for rendering a legacy content with laTeX code.
 *   The content can be text or HTML.
 *   LaTeX code within is embedded in the delimiters [tex] and [/tex]
 *
 * expected output: >
 *   ILIAS shows the string 'This should be rendered as a formula: '
 *   followed by a mathematical function definition with an integral.
 *   The function definition is rendered graphically.
 *   The rendering may take a tenth of a second when the page is shown.
 *   Before that the LaTeX source code is shown.
 *   A right click with the mouse on the rendered expression will show a popup menu from MathJax.
 *   Here you can set different display options.
 * ---
 */
function base()
{
    //Init Factory and Renderer
    global $DIC;
    $f = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();

    //Init Component
    $legacy = $f->legacy()->latexContent('This should be rendered as a formula: [tex]f(x)=\int_{-\infty}^x e^{-t^2}dt[/tex]');

    //Render
    return $renderer->render($legacy);
}
