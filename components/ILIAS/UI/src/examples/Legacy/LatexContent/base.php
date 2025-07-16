<?php

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

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
