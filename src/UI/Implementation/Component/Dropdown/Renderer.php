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

namespace ILIAS\UI\Implementation\Component\Dropdown;

use ILIAS\UI\Component\JavaScriptBindable;
use ILIAS\UI\Implementation\Render\AbstractComponentRenderer;
use ILIAS\UI\Renderer as RendererInterface;
use ILIAS\UI\Component;
use ILIAS\UI\Implementation\Render\ResourceRegistry;
use ILIAS\UI\Implementation\Render\Template;

class Renderer extends AbstractComponentRenderer
{
    /**
     * @inheritdoc
     */
    public function render(Component\Component $component, RendererInterface $default_renderer): string
    {
        $this->checkComponent($component);

        /**
         * @var $component Dropdown
         */
        return $this->renderDropdown($component, $default_renderer);
    }

    protected function renderDropdown(Dropdown $component, RendererInterface $default_renderer): string
    {

        // get template
        $tpl_name = "tpl.standard.html";
        $tpl = $this->getTemplate($tpl_name, true, true);

        // render items
        $items = $component->getItems();
        if (is_array($items) && count($items) == 0) {
            return "";
        }
        $this->renderItems($items, $tpl, $default_renderer);

        // render trigger button
        $label = $component->getLabel();
        if ($label !== null) {
            $tpl->setVariable("LABEL", $component->getLabel());
        } else {
            $tpl->setVariable("LABEL", "");
        }

        // ensure that a) a separate aria label may be provided and
        // b) that an empty label and empty aria-label will use the "actions" fallback
        if ($component->getLabel() == "" || $component->getAriaLabel() != "") {
            $aria_label = ($component->getAriaLabel() != "")
                ? $component->getAriaLabel()
                : $this->txt("actions");
            $tpl->setCurrentBlock("aria_label");
            $tpl->setVariable("ARIA_LABEL", $aria_label);
            $tpl->parseCurrentBlock();
        }

        $this->renderId($component, $tpl);

        return $tpl->get();
    }

    protected function renderItems(array $items, Template $tpl, RendererInterface $default_renderer): void
    {
        foreach ($items as $item) {
            $tpl->setCurrentBlock("item");
            $tpl->setVariable("ITEM", $default_renderer->render($item));
            $tpl->parseCurrentBlock();
        }
    }


    protected function renderId(
        JavaScriptBindable $component,
        Template $tpl
    ): void {
        $id = $this->bindJavaScript($component);
        if ($id === null) {
            $id = $this->createId();
        }
        $tpl->setVariable("ID", $id);
        $tpl->setVariable("ID_MENU", $id."_menu");

    }

    /**
     * @inheritdoc
     */
    public function registerResources(ResourceRegistry $registry): void
    {
        parent::registerResources($registry);
        $registry->register('./src/UI/templates/js/Dropdown/dropdown.js');
    }

    /**
     * @inheritdoc
     */
    protected function getComponentInterfaceName(): array
    {
        return array(Component\Dropdown\Standard::class);
    }
}
