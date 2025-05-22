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

namespace ILIAS\UI\Implementation\Component\Progress;

use ILIAS\UI\Implementation\Render\AbstractComponentRenderer;
use ILIAS\UI\Implementation\Render\Template;
use ILIAS\UI\Implementation\Component\Progress\State as I;
use ILIAS\UI\Component\Component;
use ILIAS\UI\Renderer as RendererInterface;
use ILIAS\UI\Implementation\Render\ResourceRegistry;

/**
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
class Renderer extends AbstractComponentRenderer
{
    public function registerResources(ResourceRegistry $registry): void
    {
        $registry->register('./assets/js/progress.min.js');
        parent::registerResources($registry);
    }

    public function render(Component $component, RendererInterface $default_renderer): string
    {
        if ($component instanceof Bar) {
            return $this->renderProgressBar($component, $default_renderer);
        }

        $this->cannotHandleComponent($component);
    }

    protected function renderProgressBar(Bar $component, RendererInterface $default_renderer): string
    {
        $template = $this->getTemplate('tpl.progress_bar.html', true, true);

        $template->setVariable('LABEL', $component->getLabel());

        $template->setVariable('SUCCESS_GLYPH', $default_renderer->render(
            $this->getUIFactory()->symbol()->glyph()->apply(),
        ));

        $template->setVariable('FAILURE_GLYPH', $default_renderer->render(
            $this->getUIFactory()->symbol()->glyph()->close(),
        ));

        $async_url = $component->getAsyncUrl();
        if (null !== $async_url) {
            $template->setVariable('ASYNC_URL', (string) $async_url);
        }

        if (null !== $component->getAsyncUrl()) {
            $enriched_component = $component->withAdditionalOnLoadCode(
                static fn(string $id): string => "
                    const progressbar = il.UI.Progress.Bar.createAsync(
                        document.getElementById('$id'),
                        '{$component->getUpdateSignal()->getId()}',
                        {$component->getAsyncRefreshInterval()->getRefreshIntervalInMs()},
                    );
                    
                    $(document).on('{$component->getResetSignal()}', () => progressBar.reset());
                ",
            );
        } else {
            $enriched_component = $component->withAdditionalOnLoadCode(
                static fn(string $id): string => "
                    const progressBar = il.UI.Progress.Bar.create(
                        document.getElementById('$id'),
                        '{$component->getUpdateSignal()->getId()}',
                    );

                    $(document).on('{$component->getResetSignal()}', () => progressBar.reset());
                ",
            );
        }

        $this->maybeApplyProgressBarValue($template, 0);
        $this->applyProgressBarMaxValue($template);

        $id = $this->bindJavaScript($enriched_component) ?? $this->createId();
        $template->setVariable('ID', $id);

        return $template->get();
    }

    protected function applyProgressBarMaxValue(Template $template, int $max = Bar::MAX_VALUE): void
    {
        $template->setVariable('MAX_VALUE', (string) $max);
    }

    protected function maybeApplyProgressBarValue(Template $template, ?int $value = null): void
    {
        if (null !== $value) {
            $template->setVariable('VALUE', (string) $value);
        }
    }
}
