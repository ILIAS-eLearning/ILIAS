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

namespace ILIAS\UI\Implementation\Component\Menu;

use ILIAS\UI\Implementation\Render\AbstractComponentRenderer;
use ILIAS\UI\Renderer as RendererInterface;
use ILIAS\UI\Component;
use ILIAS\UI\Implementation\Component\Menu;
use ILIAS\UI\Implementation\Render\ResourceRegistry;

class Renderer extends AbstractComponentRenderer
{
    /**
     * @inheritdoc
     */
    public function render(Component\Component $component, RendererInterface $default_renderer): string
    {
        $this->checkComponent($component);

        /**
         * @var $component Menu\Menu
         */
        $html = $this->renderMenu($component, $default_renderer);

        if ($component instanceof Menu\Drilldown) {
            $ui_factory = $this->getUIFactory();
            $back_signal = $component->getBacklinkSignal();
            $persistence_id = $component->getPersistenceId();
            $glyph = $ui_factory->symbol()->glyph()->collapsehorizontal();
            $btn = $ui_factory->button()->bulky($glyph, '', '#')->withOnClick($back_signal);
            $back_button_html = $default_renderer->render($btn);

            $component = $component->withAdditionalOnLoadCode(
                function ($id) use ($back_signal, $persistence_id) {
                    $params = "'$id', '$back_signal'";
                    if (is_null($persistence_id)) {
                        $params .= ", null";
                    } else {
                        $params .= ", '$persistence_id'";
                    }
                    return "il.UI.menu.drilldown.init($params);";
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
    ): string {
        $tpl_menu = $this->getTemplate('tpl.menu.html', true, true);

        $label = $component->getLabel();
        if (!is_string($label)) {
            $label = $default_renderer->render($label);
        }
        $tpl_menu->setVariable('LABEL', $label);

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
    public function registerResources(ResourceRegistry $registry): void
    {
        parent::registerResources($registry);
        $registry->register('./src/UI/templates/js/Menu/dist/drilldown.js');
    }

    /**
     * @inheritdoc
     */
    protected function getComponentInterfaceName(): array
    {
        return array(
            Menu\Menu::class
        );
    }
}
