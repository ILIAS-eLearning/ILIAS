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
 */

declare(strict_types=1);

namespace ILIAS\Init;

/**
 * This entry point can be thought of as a list of all modern components.
 * Modern components are those initialised using the new component bootstrap
 * mechanism. This class serves as an adapter to the legacy ILIAS
 * initialisation and populates modern components inside the legacy service
 * locator $DIC, so they will available when calling
 * @see ilInitialisation::initILIAS()
 *
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
class AllModernComponents implements \ILIAS\Component\EntryPoint
{
    public function __construct(
        protected \ILIAS\Refinery\Factory $refinery_factory,
        protected \ILIAS\Data\Factory $data_factory,
        protected \ILIAS\UI\Factory $ui_factory,
        protected \ILIAS\UI\Renderer $ui_renderer,
        protected \ILIAS\UI\Component\Counter\Factory $ui_factory_counter,
        protected \ILIAS\UI\Component\Button\Factory $ui_factory_button,
        protected \ILIAS\UI\Component\Listing\Factory $ui_factory_listing,
        protected \ILIAS\UI\Component\Image\Factory $ui_factory_image,
        protected \ILIAS\UI\Component\Panel\Factory $ui_factory_panel,
        protected \ILIAS\UI\Component\Modal\Factory $ui_factory_modal,
        protected \ILIAS\UI\Component\Dropzone\Factory $ui_factory_dropzone,
        protected \ILIAS\UI\Component\Popover\Factory $ui_factory_popover,
        protected \ILIAS\UI\Component\Divider\Factory $ui_factory_divider,
        protected \ILIAS\UI\Component\Link\Factory $ui_factory_link,
        protected \ILIAS\UI\Component\Dropdown\Factory $ui_factory_dropdown,
        protected \ILIAS\UI\Component\Item\Factory $ui_factory_item,
        protected \ILIAS\UI\Component\Viewcontrol\Factory $ui_factory_viewcontrol,
        protected \ILIAS\UI\Component\Chart\Factory $ui_factory_chart,
        protected \ILIAS\UI\Component\Input\Factory $ui_factory_input,
        protected \ILIAS\UI\Component\Table\Factory $ui_factory_table,
        protected \ILIAS\UI\Component\MessageBox\Factory $ui_factory_messagebox,
        protected \ILIAS\UI\Component\Card\Factory $ui_factory_card,
        protected \ILIAS\UI\Component\Layout\Factory $ui_factory_layout,
        protected \ILIAS\UI\Component\Maincontrols\Factory $ui_factory_maincontrols,
        protected \ILIAS\UI\Component\Tree\Factory $ui_factory_tree,
        protected \ILIAS\UI\Component\Menu\Factory $ui_factory_menu,
        protected \ILIAS\UI\Component\Symbol\Factory $ui_factory_symbol,
        protected \ILIAS\UI\Component\Toast\Factory $ui_factory_toast,
        protected \ILIAS\UI\Component\Legacy\Factory $ui_factory_legacy,
        protected \ILIAS\UI\Component\Launcher\Factory $ui_factory_launcher,
        protected \ILIAS\UI\Component\Entity\Factory $ui_factory_entity,
        protected \ILIAS\UI\Component\Panel\Listing\Factory $ui_factory_panel_listing,
        protected \ILIAS\UI\Component\Modal\InterruptiveItem\Factory $ui_factory_interruptive_item,
        protected \ILIAS\UI\Component\Chart\ProgressMeter\Factory $ui_factory_progressmeter,
        protected \ILIAS\UI\Component\Chart\Bar\Factory $ui_factory_bar,
        protected \ILIAS\UI\Component\Input\Viewcontrol\Factory $ui_factory_input_viewcontrol,
        protected \ILIAS\UI\Component\Input\Container\ViewControl\Factory $ui_factory_input_container_viewcontrol,
        protected \ILIAS\UI\Component\Table\Column\Factory $ui_factory_table_column,
        protected \ILIAS\UI\Component\Table\Factory $ui_factory_table_action,
        protected \ILIAS\UI\Component\Maincontrols\Slate\Factory $ui_factory_maincontrols_slate,
        protected \ILIAS\UI\Component\Symbol\icon\Factory $ui_factory_symbol_icon,
        protected \ILIAS\UI\Component\Symbol\Glyph\Factory $ui_factory_symbol_glyph,
        protected \ILIAS\UI\Component\Symbol\avatar\Factory $ui_factory_symbol_avatar,
        protected \ILIAS\UI\Component\Input\Container\Form\Factory $ui_factory_input_container_form,
        protected \ILIAS\UI\Component\Input\Container\Filter\Factory $ui_factory_input_container_filter,
        protected \ILIAS\UI\Component\Input\Field\Factory $ui_factory_input_field,
    ) {
    }

    /**
     * Populates already bootstrapped components in the legacy service locator $DIC.
     * Components which are not contained in the service locator are populated using their
     * fully qualified namespace. E.g. to zse the data factory, access it the service like
     * $DIC[\ILIAS\Refinery\Factory::class];
     * Components which have been populated in the past at some point, should be populated
     * using their legacy offset, since it cannot be service-located by legacy components
     * otherwise.
     */
    protected function populateComponentsInLegacyEnvironment(\Pimple\Container $DIC): void
    {
        $DIC[\ILIAS\Data\Factory::class] = fn() => $this->data_factory;

        $DIC['refinery'] = fn() => $this->refinery_factory;
        $DIC['ui.factory.counter'] = fn() => $this->ui_factory_counter;
        $DIC['ui.factory.button'] = fn() => $this->ui_factory_button;
        $DIC['ui.factory.listing'] = fn() => $this->ui_factory_listing;
        $DIC['ui.factory.image'] = fn() => $this->ui_factory_image;
        $DIC['ui.factory.panel'] = fn() => $this->ui_factory_panel;
        $DIC['ui.factory.modal'] = fn() => $this->ui_factory_modal;
        $DIC['ui.factory.dropzone'] = fn() => $this->ui_factory_dropzone;
        $DIC['ui.factory.popover'] = fn() => $this->ui_factory_popover;
        $DIC['ui.factory.divider'] = fn() => $this->ui_factory_divider;
        $DIC['ui.factory.link'] = fn() => $this->ui_factory_link;
        $DIC['ui.factory.dropdown'] = fn() => $this->ui_factory_dropdown;
        $DIC['ui.factory.item'] = fn() => $this->ui_factory_item;
        $DIC['ui.factory.viewcontrol'] = fn() => $this->ui_factory_viewcontrol;
        $DIC['ui.factory.chart'] = fn() => $this->ui_factory_chart;
        $DIC['ui.factory.input'] = fn() => $this->ui_factory_input;
        $DIC['ui.factory.table'] = fn() => $this->ui_factory_table;
        $DIC['ui.factory.messagebox'] = fn() => $this->ui_factory_messagebox;
        $DIC['ui.factory.card'] = fn() => $this->ui_factory_card;
        $DIC['ui.factory.layout'] = fn() => $this->ui_factory_layout;
        $DIC['ui.factory.maincontrols'] = fn() => $this->ui_factory_maincontrols;
        $DIC['ui.factory.tree'] = fn() => $this->ui_factory_tree;
        $DIC['ui.factory.menu'] = fn() => $this->ui_factory_menu;
        $DIC['ui.factory.symbol'] = fn() => $this->ui_factory_symbol;
        $DIC['ui.factory.toast'] = fn() => $this->ui_factory_toast;
        $DIC['ui.factory.legacy'] = fn() => $this->ui_factory_legacy;
        $DIC['ui.factory.launcher'] = fn() => $this->ui_factory_launcher;
        $DIC['ui.factory.entity'] = fn() => $this->ui_factory_entity;
        $DIC['ui.factory.panel.listing'] = fn() => $this->ui_factory_panel_listing;
        $DIC['ui.factory.interruptive_item'] = fn() => $this->ui_factory_interruptive_item;
        $DIC['ui.factory.progressmeter'] = fn() => $this->ui_factory_progressmeter;
        $DIC['ui.factory.bar'] = fn() => $this->ui_factory_bar;
        $DIC['ui.factory.input.viewcontrol'] = fn() => $this->ui_factory_input_viewcontrol;
        $DIC['ui.factory.input.container.viewcontrol'] = fn() => $this->ui_factory_input_container_viewcontrol;
        $DIC['ui.factory.table.column'] = fn() => $this->ui_factory_table_column;
        $DIC['ui.factory.table.action'] = fn() => $this->ui_factory_table_action;
        $DIC['ui.factory.maincontrols.slate'] = fn() => $this->ui_factory_maincontrols_slate;
        $DIC['ui.factory.symbol.icon'] = fn() => $this->ui_factory_symbol_icon;
        $DIC['ui.factory.symbol.glyph'] = fn() => $this->ui_factory_symbol_glyph;
        $DIC['ui.factory.symbol.avatar'] = fn() => $this->ui_factory_symbol_avatar;
        $DIC['ui.factory.input.container.form'] = fn() => $this->ui_factory_input_container_form;
        $DIC['ui.factory.input.container.filter'] = fn() => $this->ui_factory_input_container_filter;
        $DIC['ui.factory.input.field'] = fn() => $this->ui_factory_input_field;
        $DIC['ui.factory'] = fn() => $this->ui_factory;
        $DIC['ui.renderer'] = fn() => $this->ui_renderer;
    }

    public function getName(): string
    {
        return 'ILIAS Legacy Initialisation Adapter';
    }

    public function enter(): int
    {
        global $DIC;

        $DIC = new \ILIAS\DI\Container();
        $GLOBALS['DIC'] = $DIC;

        $this->populateComponentsInLegacyEnvironment($DIC);

        \ilInitialisation::initILIAS();

        return 0;
    }
}
