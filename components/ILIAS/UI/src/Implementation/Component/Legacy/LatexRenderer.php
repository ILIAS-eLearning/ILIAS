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

namespace ILIAS\UI\Implementation\Component\Legacy;

use ILIAS\UI\Implementation\Render\ResourceRegistry;
use ILIAS\UI\Renderer as RendererInterface;
use ILIAS\UI\Component;

/**
 * Renderer for content with enabled latex processing
 * This is implemented as a separate renderer to register the resources only when specific LatexContent is used
 *
 * The MathJax js library is invasive:
 *  - it scans the whole page for elements with "tex2jax_process"
 *  - it modifies their content
 *  - it adds additional assets dynamically
 * Therefore Mathjax should not be included if simple legacy content is used
 */
class LatexRenderer extends Renderer
{
    public function render(Component\Component $component, RendererInterface $default_renderer): string
    {
        if (!$component instanceof Component\Legacy\LatexContent) {
            $this->cannotHandleComponent($component);
        }
        $tpl = $this->getTemplate("tpl.latex_content.html", true, true);
        $tpl->setVariable('CONTENT', parent::render($component, $default_renderer));
        return $tpl->get();
    }

    public function registerResources(ResourceRegistry $registry): void
    {
        parent::registerResources($registry);
        $registry->register('assets/js/mathjax_config.js');
        $registry->register('node_modules/mathjax/es5/tex-chtml-full.js');
    }
}
