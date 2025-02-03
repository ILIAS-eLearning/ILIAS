<?php

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

declare(strict_types=1);

namespace ILIAS\UI\Implementation\Component\Menu;

use ILIAS\UI\Implementation\Render\AbstractComponentRenderer;
use ILIAS\UI\Renderer as RendererInterface;
use ILIAS\UI\Component;
use ILIAS\UI\Implementation\Component\Menu;
use ILIAS\UI\Implementation\Render\ResourceRegistry;

class Renderer extends AbstractComponentRenderer
{
    private const PARENT_CLASS = 'c-drilldown__branch';
    private const LEAF_CLASS = 'c-drilldown__leaf';
    private const NO_ITEMS_LABEL = 'drilldown_no_items';

    /**
     * @inheritdoc
     */
    public function render(Component\Component $component, RendererInterface $default_renderer): string
    {
        if ($component instanceof Menu\Drilldown) {
            return $this->renderDrilldownMenu($component, $default_renderer);
        }
        if ($component instanceof Menu\Menu) {
            return $this->renderStandardMenu($component, $default_renderer);
        }

        $this->cannotHandleComponent($component);
    }

    /**
     * Render a Menu.
     */
    protected function renderStandardMenu(
        Menu\Menu $component,
        RendererInterface $default_renderer
    ): string {
        $tpl_menu = $this->getTemplate('tpl.menu.html', true, true);

        $label = $component->getLabel();
        if (!is_string($label)) {
            $label = $default_renderer->render($label);
        }
        $tpl_menu->setVariable('LABEL', $label);
        $tpl_menu->setVariable('ITEMS', $this->renderMenuItems($component, $default_renderer));
        return $tpl_menu->get();
    }

    protected function renderDrilldownMenu(
        Menu\Drilldown $component,
        RendererInterface $default_renderer
    ): string {
        $items_html = $this->renderMenuItems($component, $default_renderer);
        $ui_factory = $this->getUIFactory();
        $back_signal = $component->getBacklinkSignal();
        $persistence_id = $component->getPersistenceId();
        $glyph = $ui_factory->symbol()->glyph()->collapsehorizontal();
        $btn = $ui_factory->button()->bulky($glyph, '', '#')
                                    ->withOnClick($back_signal)
                                    ->withAriaLabel($this->txt('back'));
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
        $id_drilldown = $this->bindJavaScript($component);
        $id_filter = $this->createId();

        $tpl_name = "tpl.drilldown.html";
        $tpl = $this->getTemplate($tpl_name, true, true);
        $tpl->setVariable("ID", $id_drilldown);
        $tpl->setVariable('LABEL', sprintf($this->txt('filter_nodes_in'), $component->getLabel()));
        $tpl->setVariable('ARIA_LABEL', $component->getLabel());
        $tpl->setVariable('ID_FILTER', $id_filter);
        $tpl->setVariable('BACKNAV', $back_button_html);
        $tpl->setVariable('DRILLDOWN', $items_html);
        $tpl->setVariable('NO_ITEMS_TEXT', $this->txt(self::NO_ITEMS_LABEL));

        return $tpl->get();
    }

    protected function renderMenuItems(
        Menu\Menu $component,
        RendererInterface $default_renderer
    ): string {
        $html = '';
        foreach ($component->getItems() as $item) {
            $tpl_item = $this->getTemplate('tpl.menuitem.html', true, true);
            if ($item instanceof Menu\Sub) {
                $tpl_item->setVariable('CLASS', self::PARENT_CLASS);
            } else {
                $tpl_item->setVariable('CLASS', self::LEAF_CLASS);
            }
            $tpl_item->setVariable('ITEM', $default_renderer->render($item));
            $html .= $tpl_item->get();
        }
        return $html;
    }

    /**
     * @inheritdoc
     */
    public function registerResources(ResourceRegistry $registry): void
    {
        parent::registerResources($registry);
        $registry->register('assets/js/drilldown.js');
    }
}
