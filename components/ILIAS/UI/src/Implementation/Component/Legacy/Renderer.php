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
use ILIAS\UI\Implementation\Component\LatexAwareRendererTrait;
use ILIAS\UI\Implementation\Render\ResourceRegistry;
use ILIAS\UI\Implementation\Render\LatexAwareRenderer;

/**
 * Class Renderer
 * @package ILIAS\UI\Implementation\Component\Legacy\Html
 */
class Renderer extends AbstractComponentRenderer implements LatexAwareRenderer
{
    use LatexAwareRendererTrait;

    /**
     * @param Component\LatexAwareComponent $component
     */
    public function render(Component\Component $component, RendererInterface $default_renderer): string
    {
        if (!$component instanceof Component\Legacy\Legacy) {
            $this->cannotHandleComponent($component);
        }

        $component = $this->registerSignals($component);
        $this->bindJavaScript($component);

        if ($component->isLatexEnabled()) {
            return $this->addLatexEnabling($component->getContent());
        } elseif ($component->isLatexDisabled()) {
            return $this->addLatexDisabling($component->getContent());
        }

        return $component->getContent();
    }

    public function registerResources(ResourceRegistry $registry): void
    {
        parent::registerResources($registry);
        $this->registerMathJaxResources($registry);
    }

    protected function registerSignals(Legacy $component): Component\JavaScriptBindable
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
}
