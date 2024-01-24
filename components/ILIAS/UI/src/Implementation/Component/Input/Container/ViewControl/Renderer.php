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
use ILIAS\UI\Implementation\Component\Input\ViewControl\HasInputGroup;
use ILIAS\UI\Implementation\Component\ViewControl\Pagination;
use ILIAS\UI\Implementation\Component\Input\ViewControl\Sortation;

class Renderer extends AbstractComponentRenderer
{
    /**
     * @inheritdoc
     */
    protected function renderComponent(Component\Component $component, RendererInterface $default_renderer): ?string
    {
        if ($component instanceof ViewControl\Standard) {
            if (!$component->getRequest()) {
                throw new LogicException("No request was passed to the container. Please call 'withRequest' on the Container.");
            }
            return $this->renderStandard($component, $default_renderer);
        }

        return null;
    }


    protected function getComponentInternalNames(Component\Input\Group $component, array $names = []): array
    {
        foreach ($component->getInputs() as $input) {
            if ($input instanceof Component\Input\Group) {
                $names = $this->getComponentInternalNames($input, $names);
            }
            if ($input instanceof HasInputGroup) {
                $names = $this->getComponentInternalNames($input->getInputGroup(), $names);
            }
            $names[] = $input->getName();
        }

        return $names;
    }

    protected function renderStandard(ViewControl\Standard $component, RendererInterface $default_renderer): string
    {
        $tpl = $this->getTemplate("tpl.viewcontrol_container.html", true, true);

        $submission_signal = $component->getSubmissionSignal();
        /** @var $component ViewControl\Standard */
        $component = $component->withAdditionalOnLoadCode(
            fn($id) => "$(document).on('{$submission_signal}',
                function(event, signalData) { 
                    document.getElementById('{$id}').submit();
                    return false;
                });"
        );

        $input_names = array_keys($component->getComponentInternalValues());

        $query_params = array_filter(
            $component->getRequest()?->getQueryParams(),
            fn($k) => ! in_array($k, $input_names),
            ARRAY_FILTER_USE_KEY
        );
        // The remaining parameters for the view controls need to be stuffed into
        // hidden fields, so the browser passes them as query parameters once the
        // form is submitted.
        foreach ($query_params as $k => $v) {
            if (is_array($v)) {
                foreach (array_values($v) as $arrv) {
                    $tpl->setCurrentBlock('param');
                    $tpl->setVariable("PARAM_NAME", $k . '[]');
                    $tpl->setVariable("VALUE", $arrv);
                    $tpl->parseCurrentBlock();
                }
            } else {
                $tpl->setCurrentBlock('param');
                $tpl->setVariable("PARAM_NAME", $k);
                $tpl->setVariable("VALUE", $v);
                $tpl->parseCurrentBlock();
            }
        }

        $inputs = array_map(
            fn($input) => $input->withOnChange($submission_signal),
            $component->getInputs()
        );

        $tpl->setVariable("INPUTS", $default_renderer->render($inputs));

        return $this->dehydrateComponent($component, $tpl, $this->getOptionalIdBinder());
    }
}
