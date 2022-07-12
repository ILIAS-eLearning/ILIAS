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
 
namespace ILIAS\UI\Implementation\Component\Divider;

use ILIAS\UI\Implementation\Render\AbstractComponentRenderer;
use ILIAS\UI\Renderer as RendererInterface;
use ILIAS\UI\Component;

class Renderer extends AbstractComponentRenderer
{
    /**
     * @inheritdoc
     */
    public function render(Component\Component $component, RendererInterface $default_renderer) : string
    {
        $this->checkComponent($component);

        if ($component instanceof Component\Divider\Horizontal) {
            return $this->renderDividerHorizontal($component);
        }
        if ($component instanceof Component\Divider\Vertical) {
            return $this->renderDividerVertical();
        }
        return "";
    }

    protected function renderDividerHorizontal(Component\Divider\Horizontal $component) : string
    {
        $tpl = $this->getTemplate("tpl.horizontal.html", true, true);

        $label = $component->getLabel();

        if ($label !== null) {
            $tpl->setCurrentBlock("label");
            $tpl->setVariable("LABEL", $label);
            $tpl->parseCurrentBlock();
            $tpl->touchBlock("with_label");
        } else {
            $tpl->touchBlock("divider");
        }

        return $tpl->get();
    }

    protected function renderDividerVertical() : string
    {
        $tpl = $this->getTemplate("tpl.vertical.html", true, true);

        $tpl->touchBlock("divider");

        return $tpl->get();
    }

    /**
     * @inheritdoc
     */
    protected function getComponentInterfaceName() : array
    {
        return [
            Component\Divider\Horizontal::class,
            Component\Divider\Vertical::class
        ];
    }
}
