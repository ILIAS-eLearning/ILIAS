<?php declare(strict_types=1);

/* Copyright (c) 2019 Nils Haagen <nils.haagen@concepts-and-training.de> Extended GPL, see docs/LICENSE */

namespace ILIAS\UI\Implementation\Component\Menu;

use ILIAS\UI\Implementation\Render\AbstractComponentRenderer;
use ILIAS\UI\Renderer as RendererInterface;
use ILIAS\UI\Component;
use ILIAS\UI\Implementation\Component\Menu;

class Renderer extends AbstractComponentRenderer
{
    /**
     * @inheritdoc
     */
    public function render(Component\Component $component, RendererInterface $default_renderer)
    {
        $this->checkComponent($component);

        /**
         * @var $component Menu\Menu
         */
        $html = $this->renderMenu($component, $default_renderer);

        if ($component instanceof Menu\Drilldown) {
            $ui_factory = $this->getUIFactory();
            $back_signal = $component->getBacklinkSignal();
            $glyph = $ui_factory->symbol()->glyph()->back();
            $btn = $ui_factory->button()->bulky($glyph, '', '#')->withOnClick($back_signal);
            $back_button_html = $default_renderer->render($btn);

            $component = $component->withAdditionalOnLoadCode(
                function ($id) use ($back_signal) {
                    return "il.UI.menu.drilldown.init('$id', '$back_signal');";
                }
            );
            $id = $this->bindJavaScript($component);

            $tpl_name = "tpl.drilldown.html";
            $tpl = $this->getTemplate($tpl_name, true, true);
            $tpl->setVariable("ID", $id);
            $tpl->setVariable('TITLE', $component->getLabel());
            $tpl->setVariable('BACKNAV', $back_button_html);
            $tpl->setVariable('DRILLDOWN', $html);

            return $tpl->get();
        }

        return $html;
    }

    /**
     * Render a Menu.
     */
    protected function renderMenu(
        Menu\Menu $component,
        RendererInterface $default_renderer
    ) : string {
        $tpl_menu = $this->getTemplate('tpl.menu.html', true, true);

        $label = $component->getLabel();
        if (!is_string($label)) {
            $label = $default_renderer->render($label);
        }
        $tpl_menu->setVariable('LABEL', $label);

        /*
            if ($component->isInitiallyActive()) {
                $tpl->touchBlock('active');
            }
        */
  
        $html = '';
        foreach ($component->getItems() as $item) {
            $tpl_item = $this->getTemplate('tpl.menuitem.html', true, true);
            $tpl_item->setVariable('ITEM', $default_renderer->render($item));
            $html .= $tpl_item->get();
        }
        $tpl_menu->setVariable('ITEMS', $html);
        return $tpl_menu->get();
    }

    /**
     * @inheritdoc
     */
    public function registerResources(\ILIAS\UI\Implementation\Render\ResourceRegistry $registry)
    {
        parent::registerResources($registry);
        $registry->register('./src/UI/templates/js/Menu/drilldown.js');
    }
    /**
     * @inheritdoc
     */
    protected function getComponentInterfaceName()
    {
        return array(
            Menu\Menu::class
        );
    }
}
