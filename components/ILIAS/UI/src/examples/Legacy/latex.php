<?php

declare(strict_types=1);

namespace ILIAS\UI\examples\Legacy;

function latex()
{
    //Init Factory and Renderer
    global $DIC;
    $f = $DIC->ui()->factory();
    $renderer = $DIC->ui()->renderer();

    //Init Component that should render LaTeX
    $enabled = $f->legacy(
        "This LaTeX expression will be rendered by an activated MathJax: [tex]f(x)=\int_{-\infty}^x e^{-t^2}dt[/tex]"
    )->withLatexEnabled();

    //Init Component that should NOT render LaTeX
    $disabled = $f->legacy(
        "This LaTeX expression should never be rendered, 
        even if the context allows it: [tex]f(x)=\int_{-\infty}^x e^{-t^2}dt[/tex]"
    )->withLatexDisabled();

    //Render
    return $renderer->render([$enabled, $disabled]);
}
