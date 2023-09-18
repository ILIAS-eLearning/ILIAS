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

namespace ILIAS\UI\Implementation\Component\Chart;

use ILIAS\UI\Implementation\Render\AbstractComponentRenderer;
use ILIAS\UI\Renderer as RendererInterface;
use ILIAS\UI\Component;

class Renderer extends AbstractComponentRenderer
{
    /**
     * @inheritdocs
     */
    public function render(Component\Component $component, RendererInterface $default_renderer): string
    {
        $this->checkComponent($component);

        $tpl = $this->getTemplate("tpl.scale_bar.html", true, true);

        $items = $component->getItems();
        if (count($items) == 0) {
            return "";
        }

        $width = str_replace(",", ".", (string) round(100 / count($items), 5));

        foreach ($component->getItems() as $txt => $active) {
            if ($active) {
                $tpl->setCurrentBlock("active");
                $tpl->setVariable("TXT_ACTIVE", $this->txt("active"));
                $tpl->parseCurrentBlock();
                $tpl->touchBlock("active_class");
            }

            $tpl->setCurrentBlock("item");
            $tpl->setVariable("ITEM_TEXT", $txt);
            $tpl->setVariable("ITEM_WIDTH", $width);
            $tpl->parseCurrentBlock();
        }

        return $tpl->get();
    }

    /**
     * @inheritdocs
     */
    protected function getComponentInterfaceName(): array
    {
        return array(Component\Chart\ScaleBar::class);
    }
}
