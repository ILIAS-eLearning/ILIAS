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

namespace ILIAS\UI\Implementation\Component\Panel;

use ILIAS\UI\Implementation\Render\AbstractComponentRenderer;
use ILIAS\UI\Renderer as RendererInterface;
use ILIAS\UI\Component;

/**
 * Class Renderer
 * @package ILIAS\UI\Implementation\Component\Panel
 */
class Renderer extends AbstractComponentRenderer
{
    /**
     * @inheritdocs
     */
    public function render(Component\Component $component, RendererInterface $default_renderer): string
    {
        /**
         * @var Component\Panel\Panel $component
         */
        $this->checkComponent($component);

        if ($component instanceof Component\Panel\Standard) {
            /**
             * @var Component\Panel\Standard $component
             */
            return $this->renderStandard($component, $default_renderer);
        } elseif ($component instanceof Component\Panel\Sub) {
            /**
             * @var Component\Panel\Sub $component
             */
            return $this->renderSub($component, $default_renderer);
        }
        /**
         * @var Component\Panel\Report $component
         */
        return $this->renderReport($component, $default_renderer);
    }

    protected function getContentAsString(Component\Component $component, RendererInterface $default_renderer): string
    {
        $content = "";
        foreach ($component->getContent() as $item) {
            $content .= $default_renderer->render($item);
        }
        return $content;
    }

    protected function renderStandard(Component\Panel\Standard $component, RendererInterface $default_renderer): string
    {
        $tpl = $this->getTemplate("tpl.standard.html", true, true);

        $view_controls = $component->getViewControls();
        if ($view_controls) {
            foreach ($view_controls as $view_control) {
                $tpl->setCurrentBlock("view_controls");
                $tpl->setVariable("VIEW_CONTROL", $default_renderer->render($view_control));
                $tpl->parseCurrentBlock();
            }
        }

        // actions
        $actions = $component->getActions();
        if ($actions !== null) {
            $tpl->setVariable("ACTIONS", $default_renderer->render($actions));
        }

        $tpl->setVariable("TITLE", $component->getTitle());
        $tpl->setVariable("BODY", $this->getContentAsString($component, $default_renderer));
        return $tpl->get();
    }

    protected function renderSub(Component\Panel\Sub $component, RendererInterface $default_renderer): string
    {
        $tpl = $this->getTemplate("tpl.sub.html", true, true);

        $actions = $component->getActions();

        if ($component->getTitle() != "" || $actions !== null) {
            $tpl->setCurrentBlock("title");

            // actions
            if ($actions !== null) {
                $tpl->setVariable("ACTIONS", $default_renderer->render($actions));
            }

            // title
            $tpl->setVariable("TITLE", $component->getTitle());
            $tpl->parseCurrentBlock();
        }

        if ($component->getFurtherInformation()) {
            $tpl->setCurrentBlock("with_further_information");
            $tpl->setVariable("BODY", $this->getContentAsString($component, $default_renderer));
            $tpl->setVariable("INFO", $default_renderer->render($component->getFurtherInformation()));
            $tpl->parseCurrentBlock();
        } else {
            $tpl->setCurrentBlock("no_further_information");
            $tpl->setVariable("BODY", $this->getContentAsString($component, $default_renderer));
            $tpl->parseCurrentBlock();
        }

        return $tpl->get();
    }

    protected function renderReport(Component\Panel\Report $component, RendererInterface $default_renderer): string
    {
        $tpl = $this->getTemplate("tpl.report.html", true, true);
        $tpl->setVariable("TITLE", $component->getTitle());
        $tpl->setVariable("BODY", $this->getContentAsString($component, $default_renderer));
        return $tpl->get();
    }

    /**
     * @inheritdocs
     */
    protected function getComponentInterfaceName(): array
    {
        return [Component\Panel\Panel::class];
    }
}
