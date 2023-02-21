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

    protected function renderStandard(ViewControl\Standard $component, RendererInterface $default_renderer): string
    {
        $tpl = $this->getTemplate("tpl.vc_container.html", true, true);

        $current_target = $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['SERVER_NAME']
        . ':' . $_SERVER['SERVER_PORT']
        . $_SERVER['SCRIPT_NAME'] . '?';

        $current_query = $_SERVER['QUERY_STRING'];

        $submission_signal = $component->getSubmissionSignal();
        $component = $component->withAdditionalOnLoadCode(
            fn ($id) => "$(document).on('{$submission_signal}', 
                function(event, signalData) { 
                    var form = document.getElementById('{$id}'),
                        values = form.querySelectorAll('input'),
                        target = '{$current_target}',
                        query = '{$current_query}'.split('&'),
                        i, pair, params = [];

                    for(i = 0; i < query.length; i = i +1) {
                        pair = query[i].split('=');
                        params[pair[0]] = pair[1]
                    }
                    for(i = 0; i < values.length; i = i +1) {
                        params[values[i].name] = values[i].value;
                    }
                    target = target + Object.keys(params).map(
                        k => k + '=' + params[k]
                    ).join('&');

                    window.location = target;
                    return false;
                });"
        );
        $id = $this->bindJavaScript($component);

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
