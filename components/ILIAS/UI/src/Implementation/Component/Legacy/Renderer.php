<?php

declare(strict_types=1);

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

namespace ILIAS\UI\Implementation\Component\Legacy;

use ILIAS\UI\Implementation\Render\AbstractComponentRenderer;
use ILIAS\UI\Renderer as RendererInterface;
use ILIAS\UI\Component;
use ILIAS\UI\Implementation\Render\Template;

/**
 * Class Renderer
 * @package ILIAS\UI\Implementation\Component\Legacy\Html
 */
class Renderer extends AbstractComponentRenderer
{
    /**
     * @inheritdocs
     */
    protected function renderComponent(Component\Component $component, RendererInterface $default_renderer): ?string
    {
        if (!$component instanceof Legacy) {
            return null;
        }

        $component = $this->registerSignals($component);

        $tpl = $this->getTemplate('tpl.legacy.html', true, true);
        $tpl->setVariable('CONTENT', $component->getContent());

        $apply_optional_id = static function (Template $tpl, ?string $id): void {
            if (null !== $id) {
                $tpl->setCurrentBlock('with_id');
                $tpl->setVariable('ID', $id);
                $tpl->parseCurrentBlock();
            }
        };

        return $this->dehydrateComponent($component, $tpl, $this->getOptionalIdBinder());
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
