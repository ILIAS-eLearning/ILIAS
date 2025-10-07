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

use ILIAS\UI\Implementation\Render\AbstractComponentRenderer;
use ILIAS\UI\Renderer as RendererInterface;
use ILIAS\UI\Component;
use ILIAS\UI\Implementation\Component as I;
use ILIAS\UI\Implementation\Render\ResourceRegistry;

/**
 * Class Renderer
 * @package ILIAS\UI\Implementation\Component\Legacy\Html
 */
class Renderer extends AbstractComponentRenderer
{
    /**
     * @inheritdocs
     */
    public function render(Component\Component $component, RendererInterface $default_renderer): string
    {

        if ($component instanceof I\Legacy\Content) {
            return $this->renderContent($component, $default_renderer);
        }
        if ($component instanceof I\Legacy\Segment) {
            return $this->renderSegment($component, $default_renderer);
        }

        $this->cannotHandleComponent($component);

    }

    protected function renderContent(Content $component, RendererInterface $default_renderer): string
    {
        $component = $this->registerSignals($component);
        $this->bindJavaScript($component);

        // Wrap LatexContent in a div with a css class that enables the rendering inside
        if ($component instanceof Component\Legacy\LatexContent) {
            $tpl = $this->getTemplate("tpl.latex_content.html", true, true);
            $tpl->setVariable('CONTENT', $component->getContent());
            return $tpl->get();
        }

        return $component->getContent();
    }

    protected function registerSignals(Content $component): Component\JavaScriptBindable
    {
        $custom_signals = $component->getAllCustomSignals();

        return $component->withAdditionalOnLoadCode(function ($id) use ($custom_signals): string {
            $code = "";
            foreach ($custom_signals as $custom_signal) {
                $signal_id = $custom_signal['signal'];
                $signal_code = $custom_signal['js_code'];
                $code .= "$(document).on('$signal_id', function(event, signalData) { $signal_code });";
            }
            return $code;
        });
    }


    /**
     * Register additional resources which are needed for the LatexContent component
     */
    public function registerResources(ResourceRegistry $registry): void
    {
        parent::registerResources($registry);
        $registry->register('assets/js/mathjax_config.js');
        $registry->register('node_modules/mathjax/es5/tex-chtml-full.js');
    }

    protected function renderSegment(Segment $component, RendererInterface $default_renderer): string
    {
        return $component->getSegmentContent();
    }
}
