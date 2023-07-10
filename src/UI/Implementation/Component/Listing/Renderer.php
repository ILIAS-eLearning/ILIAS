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

namespace ILIAS\UI\Implementation\Component\Listing;

use ILIAS\UI\Implementation\Render\AbstractComponentRenderer;
use ILIAS\UI\Renderer as RendererInterface;
use ILIAS\UI\Component;

/**
 * Class Renderer
 * @package ILIAS\UI\Implementation\Component\Listing\Descriptive
 */
class Renderer extends AbstractComponentRenderer
{
    /**
     * @inheritdocs
     */
    public function render(Component\Component $component, RendererInterface $default_renderer): string
    {
        /**
         * @var Component\Listing\Listing $component
         */
        $this->checkComponent($component);

        if ($component instanceof Component\Listing\Descriptive) {
            return $this->render_descriptive($component, $default_renderer);
        } else {
            return $this->render_simple($component, $default_renderer);
        }
    }

    protected function render_descriptive(
        Component\Listing\Descriptive $component,
        RendererInterface $default_renderer
    ): string {
        $tpl = $this->getTemplate("tpl.descriptive.html", true, true);

        foreach ($component->getItems() as $key => $item) {
            if (is_string($item)) {
                $content = $item;
            } else {
                $content = $default_renderer->render($item);
            }

            if (trim($content) != "") {
                $tpl->setCurrentBlock("item");
                $tpl->setVariable("DESCRIPTION", $key);
                $tpl->setVariable("CONTENT", $content);
                $tpl->parseCurrentBlock();
            }
        }
        return $tpl->get();
    }

    protected function render_simple(Component\Listing\Listing $component, RendererInterface $default_renderer): string
    {
        $tpl_name = "";

        if ($component instanceof Component\Listing\Ordered) {
            $tpl_name = "tpl.ordered.html";
        }
        if ($component instanceof Component\Listing\Unordered) {
            $tpl_name = "tpl.unordered.html";
        }

        $tpl = $this->getTemplate($tpl_name, true, true);

        if (count($component->getItems()) > 0) {
            foreach ($component->getItems() as $item) {
                $tpl->setCurrentBlock("item");
                if (is_string($item)) {
                    $tpl->setVariable("ITEM", $item);
                } else {
                    $tpl->setVariable("ITEM", $default_renderer->render($item));
                }
                $tpl->parseCurrentBlock();
            }
        }
        return $tpl->get();
    }

    /**
     * @inheritdocs
     */
    protected function getComponentInterfaceName(): array
    {
        return [Component\Listing\Listing::class];
    }
}
