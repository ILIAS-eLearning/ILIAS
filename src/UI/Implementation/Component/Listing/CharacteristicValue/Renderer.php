<?php declare(strict_types=1);

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\Listing\CharacteristicValue;

use ILIAS\UI\Implementation\Render\AbstractComponentRenderer;
use ILIAS\UI\Renderer as RendererInterface;
use ILIAS\UI\Component\Component;
use ILIAS\UI\Implementation\Render\Template;
use ILIAS\UI\Component\Listing\CharacteristicValue\Text;

/**
 * Class Renderer
 * @package     ILIAS\UI\Implementation\Component\Listing\CharacteristicValue
 */
class Renderer extends AbstractComponentRenderer
{
    /**
     * @inheritdocs
     */
    protected function getComponentInterfaceName() : array
    {
        return [Text::class];
    }

    /**
     * @inheritdocs
     */
    public function render(Component $component, RendererInterface $default_renderer) : string
    {
        $this->checkComponent($component);

        if ($component instanceof Text) {
            return $this->render_text($component);
        }

        return '';
    }

    private function render_text(Text $component) : string
    {
        $tpl = $this->getReportTemplate();

        foreach ($component->getItems() as $label => $item) {
            $this->renderItem($tpl, 'text_value', $label, $item);
            $this->renderRow($tpl);
        }

        return $tpl->get();
    }

    private function renderItem(Template $tpl, string $tpl_block, string $label, string $item) : void
    {
        $tpl->setCurrentBlock($tpl_block);
        $tpl->setVariable('LABEL', $label);
        $tpl->setVariable('ITEM', $item);
        $tpl->parseCurrentBlock();
    }

    private function renderRow(Template $tpl) : void
    {
        $tpl->setCurrentBlock('value_row');
        $tpl->parseCurrentBlock();
    }

    private function getReportTemplate() : Template
    {
        return $this->getTemplate('tpl.characteristic_value.html', true, true);
    }
}
