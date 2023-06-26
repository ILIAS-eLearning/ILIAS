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

namespace ILIAS\UI\Implementation\Component\Input\Container\ViewControl;

use ILIAS\UI\Implementation\Render\AbstractComponentRenderer;
use ILIAS\UI\Implementation\Component\Input\Container\ViewControl;
use ILIAS\UI\Renderer as RendererInterface;
use ILIAS\UI\Component;
use LogicException;

class Renderer extends AbstractComponentRenderer
{
    /**
     * @inheritdoc
     */
    public function render(Component\Component $component, RendererInterface $default_renderer): string
    {
        $this->checkComponent($component);

        if ($component instanceof ViewControl\Standard) {
            return $this->renderStandard($component, $default_renderer);
        }

        throw new LogicException("Cannot render: " . get_class($component));
    }


    protected function getComponentInternalNames(Component\Component $component, array $names = []): array
    {
        if (method_exists($component, 'getInputs')) {
            foreach ($component->getInputs() as $input) {
                $names[] = $input->getName();
                $names = array_merge($names, $this->getComponentInternalNames($input));
            }
        }
        return $names;
    }

    protected function renderStandard(ViewControl\Standard $component, RendererInterface $default_renderer): string
    {
        $tpl = $this->getTemplate("tpl.vc_container.html", true, true);

        $submission_signal = $component->getSubmissionSignal();
        $component = $component->withAdditionalOnLoadCode(
            fn ($id) => "$(document).on('{$submission_signal}', 
                function(event, signalData) { 
                    document.getElementById('{$id}').submit();
                    return false;
                });"
        );
        $id = $this->bindJavaScript($component);

        $input_names = $this->getComponentInternalNames($component);
        $query_params = array_filter(
            $component->getRequest()->getQueryParams(),
            fn ($k) => ! in_array($k, $input_names),
            ARRAY_FILTER_USE_KEY
        );
        foreach ($query_params as $k => $v) {
            $tpl->setCurrentBlock('param');
            $tpl->setVariable("PARAM_NAME", $k);
            $tpl->setVariable("VALUE", $v);
            $tpl->parseCurrentBlock();
        }

        $inputs = array_map(
            fn ($input) => $input->withOnChange($submission_signal),
            $component->getInputs()
        );

        $tpl->setVariable("INPUTS", $default_renderer->render($inputs));
        $tpl->setVariable('ID', $id);
        return $tpl->get();
    }

    /**
     * @inheritdoc
     */
    protected function getComponentInterfaceName(): array
    {
        return [
            Component\Input\Container\ViewControl\Standard::class
        ];
    }
}
