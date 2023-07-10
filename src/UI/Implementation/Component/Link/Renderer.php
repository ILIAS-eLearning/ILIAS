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

namespace ILIAS\UI\Implementation\Component\Link;

use ILIAS\UI\Implementation\Render\AbstractComponentRenderer;
use ILIAS\UI\Implementation\Render\TooltipRenderer;
use ILIAS\UI\Implementation\Render\Template;
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

        if ($component instanceof Component\Link\Standard) {
            return $this->renderStandard($component);
        }
        if ($component instanceof Component\Link\Bulky) {
            return $this->renderBulky($component, $default_renderer);
        }
        throw new LogicException("Cannot render: " . get_class($component));
    }

    protected function setStandardVars(
        string $tpl_name,
        Component\Link\Link $component
    ): Template {
        $tpl = $this->getTemplate($tpl_name, true, true);
        $action = $component->getAction();
        $label = $component->getLabel();
        if ($component->getOpenInNewViewport()) {
            $tpl->touchBlock("open_in_new_viewport");
        }
        if (null !== $component->getContentLanguage()) {
            $tpl->setVariable("CONTENT_LANGUAGE", $component->getContentLanguage());
        }
        if (null !== $component->getLanguageOfReferencedResource()) {
            $tpl->setVariable("HREF_LANGUAGE", $component->getLanguageOfReferencedResource());
        }

        $tpl->setVariable("LABEL", $label);
        $tpl->setVariable("HREF", $action);
        return $tpl;
    }

    protected function maybeRenderWithTooltip(Component\Link\Link $component, Template $tpl): string
    {
        $tooltip_embedding = $this->getTooltipRenderer()->maybeGetTooltipEmbedding(...$component->getHelpTopics());
        if (! $tooltip_embedding) {
            $id = $this->bindJavaScript($component);
            $tpl->setVariable("ID", $id);
            return $tpl->get();
        }
        $component = $component->withAdditionalOnLoadCode($tooltip_embedding[1]);
        $tooltip_id = $this->createId();
        $tpl->setCurrentBlock("with_aria_describedby");
        $tpl->setVariable("ARIA_DESCRIBED_BY", $tooltip_id);
        $tpl->parseCurrentBlock();
        $id = $this->bindJavaScript($component);
        $tpl->setVariable("ID", $id);
        return $tooltip_embedding[0]($tooltip_id, $tpl->get());
    }

    protected function renderStandard(
        Component\Link\Standard $component
    ): string {
        $tpl_name = "tpl.standard.html";
        $tpl = $this->setStandardVars($tpl_name, $component);
        return $this->maybeRenderWithTooltip($component, $tpl);
    }

    protected function renderBulky(
        Component\Link\Bulky $component,
        RendererInterface $default_renderer
    ): string {
        $tpl_name = "tpl.bulky.html";
        $tpl = $this->setStandardVars($tpl_name, $component);
        $renderer = $default_renderer->withAdditionalContext($component);
        $tpl->setVariable("SYMBOL", $renderer->render($component->getSymbol()));

        $aria_role = $component->getAriaRole();
        if ($aria_role != null) {
            $tpl->setCurrentBlock("with_aria_role");
            $tpl->setVariable("ARIA_ROLE", $aria_role);
            $tpl->parseCurrentBlock();
        }
        return $this->maybeRenderWithTooltip($component, $tpl);
    }

    /**
     * @inheritdoc
     */
    protected function getComponentInterfaceName(): array
    {
        return [
            Component\Link\Standard::class,
            Component\Link\Bulky::class
        ];
    }
}
