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

namespace ILIAS\UI\Implementation\Component\Panel;

use ILIAS\UI\Implementation\Render\AbstractComponentRenderer;
use ILIAS\UI\Renderer as RendererInterface;
use ILIAS\UI\Component\Component;
use ILIAS\UI\Component\Dropdown\Standard as DropdownStandard;
use ILIAS\UI\Component\Panel\Panel;
use ILIAS\UI\Component\Panel\Standard;
use ILIAS\UI\Component\Panel\Sub;
use ILIAS\UI\Component\Panel\Report;
use ILIAS\UI\Implementation\Render\Template;

class Renderer extends AbstractComponentRenderer
{
    use HasExpandableRenderer;

    /**
     * @inheritdocs
     */
    public function render(Component $component, RendererInterface $default_renderer): string
    {
        return match (true) {
            $component instanceof Standard => $this->renderStandard($component, $default_renderer),
            $component instanceof Sub => $this->renderSub($component, $default_renderer),
            $component instanceof Report => $this->renderReport($component, $default_renderer),
            default => $this->cannotHandleComponent($component),
        };
    }

    protected function getContentAsString(Panel $component, RendererInterface $default_renderer): string
    {
        return implode(
            '',
            array_map(
                static fn(Component $item): string => $default_renderer->render($item),
                $component->getContent()
            )
        );
    }

    protected function renderStandard(Standard $component, RendererInterface $default_renderer): string
    {
        $tpl = $this->getTemplate('tpl.standard.html', true, true);

        if ($component->isExpandable()) {
            $component = $this->parseActions($component);
        }

        $id = $this->bindJavaScript($component) ?? $this->createId();
        $tpl->setVariable('ID', $id);

        $tpl->setVariable(
            'HEADING',
            $this->parseHeading(
                $component,
                $id,
                $default_renderer,
                $this->getUIFactory()
            )->get()
        );

        if ($component->isExpandable()) {
            $tpl = $this->declareExpandable($component, $id, $tpl);
        }

        $tpl->setVariable(
            'BODY',
            $this->getContentAsString($component, $default_renderer)
        );

        return $tpl->get();
    }

    protected function renderSub(Sub $component, RendererInterface $default_renderer): string
    {
        $tpl = $this->getTemplate('tpl.sub.html', true, true);

        $actions = $component->getActions();

        if ($component->getTitle() !== '' || $actions instanceof DropdownStandard) {
            $tpl->setCurrentBlock('title');

            if ($actions instanceof DropdownStandard) {
                $tpl->setVariable('ACTIONS', $default_renderer->render($actions));
            }

            $tpl->setVariable('TITLE', $component->getTitle());
            $tpl->parseCurrentBlock();
        }

        if ($component->getFurtherInformation()) {
            $tpl->setCurrentBlock('with_further_information');
            $tpl->setVariable('BODY', $this->getContentAsString($component, $default_renderer));
            $tpl->setVariable('INFO', $default_renderer->render($component->getFurtherInformation()));
            $tpl->parseCurrentBlock();
            return $tpl->get();
        }

        $tpl->setCurrentBlock('no_further_information');
        $tpl->setVariable('BODY', $this->getContentAsString($component, $default_renderer));
        $tpl->parseCurrentBlock();
        return $tpl->get();
    }

    protected function renderReport(Report $component, RendererInterface $default_renderer): string
    {
        $tpl = $this->parseHeader(
            $component,
            $default_renderer,
            $this->getTemplate('tpl.report.html', true, true)
        );
        $tpl->setVariable('BODY', $this->getContentAsString($component, $default_renderer));
        return $tpl->get();
    }

    protected function parseHeader(
        Standard|Report $component,
        RendererInterface $default_renderer,
        Template $tpl
    ): Template {
        foreach ($component->getViewControls() ?? [] as $view_control) {
            $tpl->setCurrentBlock('view_controls');
            $tpl->setVariable('VIEW_CONTROL', $default_renderer->render($view_control));
            $tpl->parseCurrentBlock();
        }

        $actions = $component->getActions();
        if ($actions instanceof DropdownStandard) {
            $tpl->setVariable('ACTIONS', $default_renderer->render($actions));
        }

        $tpl->setVariable('TITLE', $component->getTitle());

        return $tpl;
    }
}
