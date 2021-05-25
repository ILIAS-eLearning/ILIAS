<?php
declare(strict_types=1);

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
            $tpl_name = "tpl.drilldown.html";
            $tpl = $this->getTemplate($tpl_name, true, true);
            $tpl->setVariable('DRILLDOWN', $html);

            $component = $component->withAdditionalOnLoadCode(function ($id) {
                return "il.UI.menu.drilldown.init('$id');";
            });
            $id = $this->bindJavaScript($component);
            $tpl->setVariable("ID", $id);

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
        $tpl_name = "tpl.menuitem.html";
        $tpl = $this->getTemplate($tpl_name, true, true);

        /**
         * @var $label Component\Component
         */
        $label = $this->maybeConvertLabelToShy($component->getLabel());
        $tpl->setVariable('LABEL', $default_renderer->render($label));

        if ($component instanceof Menu\Sub) {
            if ($component->isInitiallyActive()) {
                $tpl->touchBlock('active');
            }
        }
        /**
         * @var $component Menu\Menu
         */
        $component = $component->withAdditionalOnLoadCode(function ($id) {
            return '';
        });
        $id = $this->bindJavaScript($component);
        $tpl->setVariable("ID", $id);

        foreach ($component->getItems() as $subitem) {
            if ($subitem instanceof Menu\Menu) {
                $html = $default_renderer->render($subitem);
            } else {
                $html = $this->wrapMenuEntry($subitem, $default_renderer);
            }
            $tpl->setCurrentBlock('subitems');
            $tpl->setVariable('SUBITEMS', $html);
            $tpl->parseCurrentBlock();
        }

        return $tpl->get();
    }

    /**
     * Wrap an entry like Clickable or Divider to fit the menu-structure.
     */
    protected function wrapMenuEntry(
        Component\Component $component,
        RendererInterface $default_renderer
    ) : string {
        $tpl_name = "tpl.menuitem.html";
        $tpl = $this->getTemplate($tpl_name, true, true);

        $label = $default_renderer->render($component);
        $tpl->setVariable('LABEL', $label);
        return $tpl->get();
    }


    /**
     * A string will be converted to a Shy Button, any Clickables
     * will be returned as they are.
     */
    protected function maybeConvertLabelToShy($label) : Component\Clickable
    {
        if (is_string($label)) {
            $label = $this->getUIFactory()->button()->shy($label, '');
        }
        return $label;
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
