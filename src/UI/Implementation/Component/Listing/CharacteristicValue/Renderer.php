<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\Listing\CharacteristicValue;

use ILIAS\UI\Implementation\Render\AbstractComponentRenderer;
use ILIAS\UI\Renderer as RendererInterface;
use ILIAS\UI\Component\Component;
use ILIAS\UI\Implementation\Render\Template;
use ILIAS\UI\Component\Listing\CharacteristicValue\Text;

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
        return [Text::class];
    }

    /**
     * @inheritdocs
     */
    public function render(Component $component, RendererInterface $default_renderer)
    {
        $this->checkComponent($component);

        if( $component instanceof Text )
        {
            return $this->render_text($component);
        }

        return '';
    }

    /**
     * @param Text          $component
     *
     * @return string
     */
    private function render_text(Text $component)
    {
        $tpl = $this->getReportTemplate();

        foreach($component->getItems() as $label => $item)
        {
            $this->renderItem($tpl, 'text_value', $label, $item);
            $this->renderRow($tpl);
        }

        return $tpl->get();
    }

    /**
     * @param Template $tpl
     * @param string $tpl_block
     * @param string $label
     * @param string $item
     */
    private function renderItem(Template $tpl, $tpl_block, $label, $item)
    {
        $tpl->setCurrentBlock($tpl_block);
        $tpl->setVariable('LABEL', $label);
        $tpl->setVariable('ITEM', $item);
        $tpl->parseCurrentBlock();
    }

    /**
     * @param Template $tpl
     */
    private function renderRow(Template $tpl)
    {
        $tpl->setCurrentBlock('value_row');
        $tpl->parseCurrentBlock();
    }

    /**
     * @return Template
     */
    private function getReportTemplate()
    {
        return $this->getTemplate('tpl.characteristic_value.html', true, true);
    }
}
