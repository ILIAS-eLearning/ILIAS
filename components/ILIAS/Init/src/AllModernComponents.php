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

namespace ILIAS\Init;

use ILIAS\WebDAV\Environment;
use ILIAS\HTTP\GlobalHttpState;
use ILIAS\FileDelivery\FileDeliveryServices;
use ILIAS\FileDelivery\Token\DataSigning;
use ILIAS\Filesystem\Security\Sanitizing\FilenameSanitizer;
use ILIAS\Filesystem\Configuration\FilesystemConfig;
use ILIAS\Filesystem\Filesystems;
use ILIAS\Filesystem\FileSystems\FilesystemWeb;
use ILIAS\Filesystem\FileSystems\FilesystemStorage;
use ILIAS\Filesystem\FileSystems\FilesystemTemp;
use ILIAS\Filesystem\FileSystems\FilesystemCustomizing;
use ILIAS\Filesystem\FileSystems\FilesystemLibs;
use ILIAS\Filesystem\FileSystems\FilesystemNodeModules;
use ILIAS\ResourceStorage\IRSSServices;
use ILIAS\FileUpload\FileUpload as FileUploadInterface;
use ILIAS\Environment\Configuration\Instance\IliasIni;
use ILIAS\Environment\Configuration\Instance\ClientIni;
use ILIAS\StaticURL\StaticURLServices;
use ILIAS\AccessControl\PublicInterface\Access;
use ILIAS\AccessControl\PublicInterface\RBAC;
use ILIAS\AccessControl\PublicInterface\DefaultRBAC;
use ILIAS\DI\RBACServices;
use ILIAS\Logging;

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
        protected \ILIAS\UI\Implementation\Component\Counter\Factory $ui_factory_counter,
        protected \ILIAS\UI\Implementation\Component\Button\Factory $ui_factory_button,
        protected \ILIAS\UI\Implementation\Component\Listing\Factory $ui_factory_listing,
        protected \ILIAS\UI\Implementation\Component\Listing\Workflow\Factory $ui_factory_listing_workflow,
        protected \ILIAS\UI\Implementation\Component\Listing\CharacteristicValue\Factory $ui_factory_listing_characteristic_value,
        protected \ILIAS\UI\Implementation\Component\Listing\Entity\Factory $ui_factory_listing_entity,
        protected \ILIAS\UI\Implementation\Component\Image\Factory $ui_factory_image,
        protected \ILIAS\UI\Implementation\Component\Player\Factory $ui_factory_player,
        protected \ILIAS\UI\Implementation\Component\Panel\Factory $ui_factory_panel,
        protected \ILIAS\UI\Implementation\Component\Modal\Factory $ui_factory_modal,
        protected \ILIAS\UI\Implementation\Component\Dropzone\Factory $ui_factory_dropzone,
        protected \ILIAS\UI\Implementation\Component\Popover\Factory $ui_factory_popover,
        protected \ILIAS\UI\Implementation\Component\Divider\Factory $ui_factory_divider,
        protected \ILIAS\UI\Implementation\Component\Link\Factory $ui_factory_link,
        protected \ILIAS\UI\Implementation\Component\Dropdown\Factory $ui_factory_dropdown,
        protected \ILIAS\UI\Implementation\Component\Item\Factory $ui_factory_item,
        protected \ILIAS\UI\Implementation\Component\Viewcontrol\Factory $ui_factory_viewcontrol,
        protected \ILIAS\UI\Implementation\Component\Chart\Factory $ui_factory_chart,
        protected \ILIAS\UI\Implementation\Component\Input\Factory $ui_factory_input,
        protected \ILIAS\UI\Implementation\Component\Table\Factory $ui_factory_table,
        protected \ILIAS\UI\Implementation\Component\MessageBox\Factory $ui_factory_messagebox,
        protected \ILIAS\UI\Implementation\Component\Card\Factory $ui_factory_card,
        protected \ILIAS\UI\Implementation\Component\Layout\Factory $ui_factory_layout,
        protected \ILIAS\UI\Implementation\Component\Layout\Page\Factory $ui_factory_layout_page,
        protected \ILIAS\UI\Implementation\Component\Layout\Alignment\Factory $ui_factory_layout_alignment,
        protected \ILIAS\UI\Implementation\Component\Maincontrols\Factory $ui_factory_maincontrols,
        protected \ILIAS\UI\Implementation\Component\Tree\Factory $ui_factory_tree,
        protected \ILIAS\UI\Implementation\Component\Tree\Node\Factory $ui_factory_tree_node,
        protected \ILIAS\UI\Implementation\Component\Menu\Factory $ui_factory_menu,
        protected \ILIAS\UI\Implementation\Component\Symbol\Factory $ui_factory_symbol,
        protected \ILIAS\UI\Implementation\Component\Toast\Factory $ui_factory_toast,
        protected \ILIAS\UI\Implementation\Component\Legacy\Factory $ui_factory_legacy,
        protected \ILIAS\UI\Implementation\Component\Launcher\Factory $ui_factory_launcher,
        protected \ILIAS\UI\Implementation\Component\Entity\Factory $ui_factory_entity,
        protected \ILIAS\UI\Implementation\Component\Panel\Listing\Factory $ui_factory_panel_listing,
        protected \ILIAS\UI\Implementation\Component\Panel\Secondary\Factory $ui_factory_panel_secondary,
        protected \ILIAS\UI\Implementation\Component\Modal\InterruptiveItem\Factory $ui_factory_interruptive_item,
        protected \ILIAS\UI\Implementation\Component\Chart\ProgressMeter\Factory $ui_factory_progressmeter,
        protected \ILIAS\UI\Implementation\Component\Chart\Bar\Factory $ui_factory_bar,
        protected \ILIAS\UI\Implementation\Component\Input\Viewcontrol\Factory $ui_factory_input_viewcontrol,
        protected \ILIAS\UI\Implementation\Component\Input\Container\ViewControl\Factory $ui_factory_input_container_viewcontrol,
        protected \ILIAS\UI\Implementation\Component\Table\Column\Factory $ui_factory_table_column,
        protected \ILIAS\UI\Implementation\Component\Table\Factory $ui_factory_table_action,
        protected \ILIAS\UI\Implementation\Component\Maincontrols\Slate\Factory $ui_factory_maincontrols_slate,
        protected \ILIAS\UI\Implementation\Component\Symbol\icon\Factory $ui_factory_symbol_icon,
        protected \ILIAS\UI\Implementation\Component\Symbol\Glyph\Factory $ui_factory_symbol_glyph,
        protected \ILIAS\UI\Implementation\Component\Symbol\avatar\Factory $ui_factory_symbol_avatar,
        protected \ILIAS\UI\Implementation\Component\Input\Container\Form\Factory $ui_factory_input_container_form,
        protected \ILIAS\UI\Implementation\Component\Input\Container\Filter\Factory $ui_factory_input_container_filter,
        protected \ILIAS\UI\Implementation\Component\Input\Field\Factory $ui_factory_input_field,
        protected \ILIAS\UI\Implementation\Component\Prompt\Factory $ui_prompt_factory,
        protected \ILIAS\UI\Implementation\Component\Prompt\State\Factory $ui_prompt_state_factory,
        protected \ILIAS\UI\Implementation\Component\Progress\Factory $ui_progress_factory,
        protected \ILIAS\UI\Implementation\Component\Progress\State\Factory $ui_progress_state_factory,
        protected \ILIAS\UI\Implementation\Component\Progress\State\Bar\Factory $ui_progress_state_bar_factory,
        protected \ILIAS\UI\Implementation\Component\Input\UploadLimitResolver $ui_upload_limit_resolver,
        protected \ILIAS\Setup\AgentFinder $setup_agent_finder,
        protected \ILIAS\UI\Implementation\Component\Navigation\Factory $ui_factory_navigation,
        protected Environment $webdav_environment,
        protected \ILIAS\UI\Implementation\Render\JavaScriptBinding $ui_java_script_binding,
        protected \ILIAS\UI\Implementation\Component\SignalGeneratorInterface $ui_signal_generator,
        protected \ILIAS\UI\Implementation\Render\TemplateFactory $ui_template_factory,
        protected GlobalHttpState $http_services,
        protected FileDeliveryServices $file_delivery,
        protected DataSigning $data_signer,
        protected FilenameSanitizer $filename_sanitizer,
        protected FilesystemConfig $filesystem_config,
        protected Filesystems $filesystems,
        protected FilesystemWeb $filesystem_web,
        protected FilesystemStorage $filesystem_storage,
        protected FilesystemTemp $filesystem_temp,
        protected FilesystemCustomizing $filesystem_customizing,
        protected FilesystemLibs $filesystem_libs,
        protected FilesystemNodeModules $filesystem_node_modules,
        protected IRSSServices $irss_services,
        protected FileUploadInterface $file_upload,
        protected IliasIni $ilias_ini,
        protected ClientIni $client_ini,
        protected StaticURLServices $static_url,
        protected RBAC $rbac,
        protected Access $access_control,
        protected Logging\Logger\LoggerFactoryInterface $logger_factory,
        protected Logging\Logger\DefaultConfigLoggerFactoryInterface $default_config_logger_factory,
        protected Logging\Config\ConfigInterface $logging_config
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
        $DIC[\ILIAS\Data\Factory::class] = fn(): \ILIAS\Data\Factory => $this->data_factory;

        $DIC['refinery'] = fn(): \ILIAS\Refinery\Factory => $this->refinery_factory;
        $DIC['ui.factory.counter'] = fn(): \ILIAS\UI\Implementation\Component\Counter\Factory => $this->ui_factory_counter;
        $DIC['ui.factory.button'] = fn(): \ILIAS\UI\Implementation\Component\Button\Factory => $this->ui_factory_button;
        $DIC['ui.factory.listing'] = fn(): \ILIAS\UI\Implementation\Component\Listing\Factory => $this->ui_factory_listing;
        $DIC['ui.factory.listing.workflow'] = fn(): \ILIAS\UI\Implementation\Component\Listing\Workflow\Factory => $this->ui_factory_listing_workflow;
        $DIC['ui.factory.listing.characteristic_value'] = fn(): \ILIAS\UI\Implementation\Component\Listing\CharacteristicValue\Factory => $this->ui_factory_listing_characteristic_value;
        $DIC['ui.factory.listing.entity'] = fn(): \ILIAS\UI\Implementation\Component\Listing\Entity\Factory => $this->ui_factory_listing_entity;
        $DIC['ui.factory.image'] = fn(): \ILIAS\UI\Implementation\Component\Image\Factory => $this->ui_factory_image;
        $DIC['ui.factory.player'] = fn(): \ILIAS\UI\Implementation\Component\Player\Factory => $this->ui_factory_player;
        $DIC['ui.factory.panel'] = fn(): \ILIAS\UI\Implementation\Component\Panel\Factory => $this->ui_factory_panel;
        $DIC['ui.factory.modal'] = fn(): \ILIAS\UI\Implementation\Component\Modal\Factory => $this->ui_factory_modal;
        $DIC['ui.factory.progress'] = fn(): \ILIAS\UI\Implementation\Component\Progress\Factory => $this->ui_progress_factory;
        $DIC['ui.factory.progress.state'] = fn(): \ILIAS\UI\Implementation\Component\Progress\State\Factory => $this->ui_progress_state_factory;
        $DIC['ui.factory.progress.state.bar'] = fn(): \ILIAS\UI\Implementation\Component\Progress\State\Bar\Factory => $this->ui_progress_state_bar_factory;
        $DIC['ui.factory.dropzone'] = fn(): \ILIAS\UI\Implementation\Component\Dropzone\Factory => $this->ui_factory_dropzone;
        $DIC['ui.factory.popover'] = fn(): \ILIAS\UI\Implementation\Component\Popover\Factory => $this->ui_factory_popover;
        $DIC['ui.factory.divider'] = fn(): \ILIAS\UI\Implementation\Component\Divider\Factory => $this->ui_factory_divider;
        $DIC['ui.factory.link'] = fn(): \ILIAS\UI\Implementation\Component\Link\Factory => $this->ui_factory_link;
        $DIC['ui.factory.dropdown'] = fn(): \ILIAS\UI\Implementation\Component\Dropdown\Factory => $this->ui_factory_dropdown;
        $DIC['ui.factory.item'] = fn(): \ILIAS\UI\Implementation\Component\Item\Factory => $this->ui_factory_item;
        $DIC['ui.factory.viewcontrol'] = fn(): \ILIAS\UI\Implementation\Component\Viewcontrol\Factory => $this->ui_factory_viewcontrol;
        $DIC['ui.factory.chart'] = fn(): \ILIAS\UI\Implementation\Component\Chart\Factory => $this->ui_factory_chart;
        $DIC['ui.factory.input'] = fn(): \ILIAS\UI\Implementation\Component\Input\Factory => $this->ui_factory_input;
        $DIC['ui.factory.table'] = fn(): \ILIAS\UI\Implementation\Component\Table\Factory => $this->ui_factory_table;
        $DIC['ui.factory.messagebox'] = fn(): \ILIAS\UI\Implementation\Component\MessageBox\Factory => $this->ui_factory_messagebox;
        $DIC['ui.factory.card'] = fn(): \ILIAS\UI\Implementation\Component\Card\Factory => $this->ui_factory_card;
        $DIC['ui.factory.layout'] = fn(): \ILIAS\UI\Implementation\Component\Layout\Factory => $this->ui_factory_layout;
        $DIC['ui.factory.layout.page'] = fn(): \ILIAS\UI\Implementation\Component\Layout\Page\Factory => $this->ui_factory_layout_page;
        $DIC['ui.factory.layout.alignment'] = fn(): \ILIAS\UI\Implementation\Component\Layout\Alignment\Factory => $this->ui_factory_layout_alignment;
        $DIC['ui.factory.maincontrols'] = fn(): \ILIAS\UI\Implementation\Component\Maincontrols\Factory => $this->ui_factory_maincontrols;
        $DIC['ui.factory.tree'] = fn(): \ILIAS\UI\Implementation\Component\Tree\Factory => $this->ui_factory_tree;
        $DIC['ui.factory.tree.node'] = fn(): \ILIAS\UI\Implementation\Component\Tree\Node\Factory => $this->ui_factory_tree_node;
        $DIC['ui.factory.menu'] = fn(): \ILIAS\UI\Implementation\Component\Menu\Factory => $this->ui_factory_menu;
        $DIC['ui.factory.symbol'] = fn(): \ILIAS\UI\Implementation\Component\Symbol\Factory => $this->ui_factory_symbol;
        $DIC['ui.factory.toast'] = fn(): \ILIAS\UI\Implementation\Component\Toast\Factory => $this->ui_factory_toast;
        $DIC['ui.factory.legacy'] = fn(): \ILIAS\UI\Implementation\Component\Legacy\Factory => $this->ui_factory_legacy;
        $DIC['ui.factory.launcher'] = fn(): \ILIAS\UI\Implementation\Component\Launcher\Factory => $this->ui_factory_launcher;
        $DIC['ui.factory.entity'] = fn(): \ILIAS\UI\Implementation\Component\Entity\Factory => $this->ui_factory_entity;
        $DIC['ui.factory.prompt'] = fn(): \ILIAS\UI\Implementation\Component\Prompt\Factory => $this->ui_prompt_factory;
        $DIC['ui.factory.prompt.state'] = fn(): \ILIAS\UI\Implementation\Component\Prompt\State\Factory => $this->ui_prompt_state_factory;
        $DIC['ui.factory.panel.listing'] = fn(): \ILIAS\UI\Implementation\Component\Panel\Listing\Factory => $this->ui_factory_panel_listing;
        $DIC['ui.factory.panel.secondary'] = fn(): \ILIAS\UI\Implementation\Component\Panel\Secondary\Factory => $this->ui_factory_panel_secondary;
        $DIC['ui.factory.interruptive_item'] = fn(): \ILIAS\UI\Implementation\Component\Modal\InterruptiveItem\Factory => $this->ui_factory_interruptive_item;
        $DIC['ui.factory.progressmeter'] = fn(): \ILIAS\UI\Implementation\Component\Chart\ProgressMeter\Factory => $this->ui_factory_progressmeter;
        $DIC['ui.factory.bar'] = fn(): \ILIAS\UI\Implementation\Component\Chart\Bar\Factory => $this->ui_factory_bar;
        $DIC['ui.factory.input.viewcontrol'] = fn(): \ILIAS\UI\Implementation\Component\Input\Viewcontrol\Factory => $this->ui_factory_input_viewcontrol;
        $DIC['ui.factory.input.container.viewcontrol'] = fn(): \ILIAS\UI\Implementation\Component\Input\Container\ViewControl\Factory => $this->ui_factory_input_container_viewcontrol;
        $DIC['ui.factory.table.column'] = fn(): \ILIAS\UI\Implementation\Component\Table\Column\Factory => $this->ui_factory_table_column;
        $DIC['ui.factory.table.action'] = fn(): \ILIAS\UI\Implementation\Component\Table\Factory => $this->ui_factory_table_action;
        $DIC['ui.factory.maincontrols.slate'] = fn(): \ILIAS\UI\Implementation\Component\Maincontrols\Slate\Factory => $this->ui_factory_maincontrols_slate;
        $DIC['ui.factory.symbol.icon'] = fn(): \ILIAS\UI\Implementation\Component\Symbol\icon\Factory => $this->ui_factory_symbol_icon;
        $DIC['ui.factory.symbol.glyph'] = fn(): \ILIAS\UI\Implementation\Component\Symbol\Glyph\Factory => $this->ui_factory_symbol_glyph;
        $DIC['ui.factory.symbol.avatar'] = fn(): \ILIAS\UI\Implementation\Component\Symbol\avatar\Factory => $this->ui_factory_symbol_avatar;
        $DIC['ui.factory.input.container.form'] = fn(): \ILIAS\UI\Implementation\Component\Input\Container\Form\Factory => $this->ui_factory_input_container_form;
        $DIC['ui.factory.input.container.filter'] = fn(): \ILIAS\UI\Implementation\Component\Input\Container\Filter\Factory => $this->ui_factory_input_container_filter;
        $DIC['ui.factory.input.field'] = fn(): \ILIAS\UI\Implementation\Component\Input\Field\Factory => $this->ui_factory_input_field;
        $DIC['ui.upload_limit_resolver'] = fn(): \ILIAS\UI\Implementation\Component\Input\UploadLimitResolver => $this->ui_upload_limit_resolver;
        $DIC['ui.factory'] = fn(): \ILIAS\UI\Factory => $this->ui_factory;
        $DIC['ui.renderer'] = fn(): \ILIAS\UI\Renderer => $this->ui_renderer;
        $DIC['setup.agentfinder'] = fn(): \ILIAS\Setup\AgentFinder => $this->setup_agent_finder;
        $DIC['ui.factory.navigation'] = fn(): \ILIAS\UI\Implementation\Component\Input\Field\Factory => $this->ui_factory_input_field;
        $DIC['http'] = fn(): \ILIAS\HTTP\GlobalHttpState => $this->http_services;
        $DIC['file_delivery'] = fn(): \ILIAS\FileDelivery\FileDeliveryServices => $this->file_delivery;
        $DIC['file_delivery.data_signer'] = fn(): \ILIAS\FileDelivery\Token\DataSigning => $this->data_signer;
        $DIC['filesystem.security.sanitizing.filename'] = fn(): \ILIAS\Filesystem\Security\Sanitizing\FilenameSanitizer => $this->filename_sanitizer;
        $DIC[FilesystemConfig::class] = fn(): \ILIAS\Filesystem\Configuration\FilesystemConfig => $this->filesystem_config;
        $DIC['filesystem'] = fn(): Filesystems => $this->filesystems;
        $DIC['filesystem.web'] = fn(): FilesystemWeb => $this->filesystem_web;
        $DIC['filesystem.storage'] = fn(): FilesystemStorage => $this->filesystem_storage;
        $DIC['filesystem.temp'] = fn(): FilesystemTemp => $this->filesystem_temp;
        $DIC['filesystem.customizing'] = fn(): FilesystemCustomizing => $this->filesystem_customizing;
        $DIC['filesystem.libs'] = fn(): FilesystemLibs => $this->filesystem_libs;
        $DIC['filesystem.node_modules'] = fn(): FilesystemNodeModules => $this->filesystem_node_modules;
        $DIC['resource_storage'] = fn(): IRSSServices => $this->irss_services;
        $DIC[Environment::class] = fn(): Environment => $this->webdav_environment;
        $DIC['ui.javascript_binding'] = fn(): \ILIAS\UI\Implementation\Render\JavaScriptBinding => $this->ui_java_script_binding;
        $DIC['ui.signal_generator'] = fn(): \ILIAS\UI\Implementation\Component\SignalGeneratorInterface => $this->ui_signal_generator;
        $DIC['ui.template_factory'] = fn(): \ILIAS\UI\Implementation\Render\TemplateFactory => $this->ui_template_factory;
        $DIC['upload'] = fn(): FileUploadInterface => $this->file_upload;
        $DIC['ilIliasIniFile'] = fn(): \ilIniFile => new \ilIniFile('', $this->ilias_ini);
        $DIC['ilClientIniFile'] = fn(): \ilIniFile => new \ilIniFile('', $this->client_ini);
        $DIC['static_url'] = fn(): StaticURLServices => $this->static_url;
        $DIC['static_url.uri_builder'] = fn(): \ILIAS\StaticURL\Builder\URIBuilder => $this->static_url->builder();
        $rbac = $this->rbac;
        \assert($rbac instanceof DefaultRBAC);

        $DIC[RBACServices::class] = fn(): RBACServices => new RBACServices(
            $rbac->review(),
            $rbac->system(),
            $rbac->admin(),
        );
        $DIC['rbacsystem'] = fn(): \ilRbacSystem => $rbac->system();
        $DIC['rbacreview'] = fn(): \ilRbacReview => $rbac->review();
        $DIC['rbacadmin'] = fn(): \ilRbacAdmin => $rbac->admin();
        $DIC['ilAccess'] = fn(): Access => $this->access_control;
        $DIC['logging.factory'] = fn(): Logging\Logger\LoggerFactoryInterface => $this->logger_factory;
        $DIC['logging.defaultConfigFactory'] = fn(): Logging\Logger\DefaultConfigLoggerFactoryInterface => $this->default_config_logger_factory;
        $DIC['logging.config'] = fn(): Logging\Config\ConfigInterface => $this->logging_config;
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
