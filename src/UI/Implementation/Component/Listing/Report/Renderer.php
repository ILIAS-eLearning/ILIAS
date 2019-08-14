<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\Listing\Report;

use ILIAS\UI\Implementation\Render\AbstractComponentRenderer;
use ILIAS\UI\Renderer as RendererInterface;
use ILIAS\UI\Component\Component;
use ILIAS\UI\Implementation\Render\Template;
use ILIAS\UI\Component\Listing\Report\Report;
use ILIAS\UI\Component\Listing\Report\Standard;
use ILIAS\UI\Component\Listing\Report\Mini;

/**
 * Class Renderer
 *
 * @author      Björn Heyser <info@bjoernheyser.de>
 *
 * @package     Services/AssessmentQuestion
 */
class Renderer extends AbstractComponentRenderer
{
    /**
	 * @inheritdocs
	 */
    protected function getComponentInterfaceName()
    {
        return [Report::class];
    }

	/**
	 * @inheritdocs
	 */
    public function render(Component $component, RendererInterface $default_renderer)
    {
        $this->checkComponent($component);

        if($component instanceof Standard)
        {
            return $this->render_standard($component, $default_renderer);
        }

        if($component instanceof Mini)
        {
            return $this->render_mini($component, $default_renderer);
        }

        return '';
    }


    /**
     * @param Standard          $component
     * @param RendererInterface $default_renderer
     *
     * @return string
     */
    private function render_standard(Standard $component, RendererInterface $default_renderer)
    {
        $tpl = $this->getReportTemplate();

        $first = true;

        foreach ($component->getItems() as $label => $item)
        {
            if( $first )
            {
                $first = false;
            }
            elseif( $component->hasDivider() )
            {
                $this->renderDivider(
                    $tpl, $default_renderer->render($component->getDivider())
                );
            }

            $this->renderItem($tpl, 'item_standard', $label,
                $this->ensureRenderedItemString($default_renderer, $item)
            );

            $this->renderRow($tpl);
        }

        return $tpl->get();
    }

    /**
     * @param Mini              $component
     * @param RendererInterface $default_renderer
     *
     * @return string
     */
    private function render_mini(Mini $component, RendererInterface $default_renderer)
    {
        $tpl = $this->getReportTemplate();

        foreach($component->getItems() as $label => $item)
        {
            $this->renderItem($tpl, 'item_mini', $label,
                $this->ensureRenderedItemString($default_renderer, $item)
            );

            $this->renderRow($tpl);
        }

        return $tpl->get();
    }

    /**
     * @param Template $tpl
     * @param RendererInterface $default_renderer
     */
    private function renderDivider(Template $tpl, $renderedDivider)
    {
        $tpl->setCurrentBlock('divider');
        $tpl->setVariable('DIVIDER', $renderedDivider);
        $tpl->parseCurrentBlock();
    }

    /**
     * @param Template $tpl
     * @param string $label
     * @param string $item
     */
    private function renderItem(Template $tpl, $block, $label, $item)
    {
        $tpl->setCurrentBlock($block);
        $tpl->setVariable('LABEL', $label);
        $tpl->setVariable('ITEM', $item);
        $tpl->parseCurrentBlock();
    }

    /**
     * @param Template $tpl
     */
    private function renderRow(Template $tpl)
    {
        $tpl->setCurrentBlock('row');
        $tpl->parseCurrentBlock();
    }

    /**
     * @return Template
     */
    private function getReportTemplate()
    {
        return $this->getTemplate('tpl.report.html', true, true);
    }

    /**
     * @param RendererInterface $default_renderer
     * @param Component|string $item
     *
     * @return string
     */
    private function ensureRenderedItemString(RendererInterface $default_renderer, $item)
    {
        if($item instanceof Component)
        {
            return $default_renderer->render($item);
        }

        return $item;
    }
}
