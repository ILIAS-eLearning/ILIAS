<?php declare(strict_types=1);

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
