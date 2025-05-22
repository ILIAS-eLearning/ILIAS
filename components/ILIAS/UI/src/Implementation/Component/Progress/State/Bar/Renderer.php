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

namespace ILIAS\UI\Implementation\Component\Progress\State\Bar;

use ILIAS\UI\Implementation\Component\Progress\Renderer as ProgressBarRenderer;
use ILIAS\UI\Renderer as RendererInterface;
use ILIAS\UI\Component\Component;

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
class Renderer extends ProgressBarRenderer
{
    public function render(Component $component, RendererInterface $default_renderer): string
    {
        if ($component instanceof State) {
            return $this->renderProgressBarState($component, $default_renderer);
        }

        $this->cannotHandleComponent($component);
    }

    protected function renderProgressBarState(State $component, RendererInterface $default_renderer): string
    {
        $template = $this->getTemplate('tpl.progress_bar_state.html', true, true);

        $this->maybeApplyProgressBarValue($template, $component->getVisualProgressValue());
        $this->applyProgressBarMaxValue($template);

        $message = $component->getMessage();
        if (null !== $message) {
            $template->setVariable('MESSAGE', $message);
        }

        $template->setVariable('STATUS', $component->getStatus()->value);

        return $template->get();
    }
}
