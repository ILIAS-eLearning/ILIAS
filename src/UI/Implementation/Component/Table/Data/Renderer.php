<?php

declare(strict_types=1);

namespace ILIAS\UI\Implementation\Component\Table\Data;

use ILIAS\DI\Container;
use ILIAS\UI\Component\Component;
use ILIAS\UI\Component\Table\Data\Data\Data as DataInterface;
use ILIAS\UI\Component\Table\Data\Format\BrowserFormat;
use ILIAS\UI\Component\Table\Data\Format\Format;
use ILIAS\UI\Component\Table\Data\Table;
use ILIAS\UI\Component\Table\Data\Settings\Settings;
use ILIAS\UI\Implementation\Component\Table\Data\Data\Data;
use ILIAS\UI\Implementation\Component\Table\Data\Format\DefaultBrowserFormat;
use ILIAS\UI\Implementation\Component\Table\Data\Settings\Storage\DefaultSettingsStorage;
use ILIAS\UI\Implementation\Render\AbstractComponentRenderer;
use ILIAS\UI\Implementation\Render\ResourceRegistry;
use ILIAS\UI\Implementation\Render\Template;
use ILIAS\UI\Renderer as RendererInterface;

/**
 * Class Renderer
 *
 * @package ILIAS\UI\Implementation\Component\Table\Data
 *
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 */
class Renderer extends AbstractComponentRenderer
{

    /**
     * @var Container
     */
    protected $dic;


    /**
     * @inheritDoc
     */
    protected function getComponentInterfaceName() : array
    {
        return [Table::class];
    }


    /**
     * @inheritDoc
     *
     * @param Table $component
     */
    public function render(Component $component, RendererInterface $default_renderer) : string
    {
        global $DIC;

        $this->dic = $DIC;

        $this->dic->language()->loadLanguageModule(Table::LANG_MODULE);

        $this->checkComponent($component);

        return $this->renderDataTable($component, $default_renderer);
    }


    /**
     * @param Table             $component
     * @param RendererInterface $renderer
     *
     * @return string
     */
    protected function renderDataTable(Table $component, RendererInterface $renderer) : string
    {
        $browser_format = $component->getCustomBrowserFormat() ?: new DefaultBrowserFormat($this->dic);
        $settings_storage = $component->getCustomSettingsStorage() ?: new DefaultSettingsStorage($this->dic);

        $settings = $settings_storage->read($component->getTableId(), intval($this->dic->user()->getId()));
        $settings = $browser_format->handleSettingsInput($component, $settings);
        $settings = $settings_storage->handleDefaultSettings($settings, $component);

        $data = $this->handleFetchData($component, $settings);

        $html = $this->handleFormat($browser_format, $component, $data, $settings, $renderer);

        $settings_storage->store($settings, $component->getTableId(), intval($this->dic->user()->getId()));

        return $html;
    }


    /**
     * @inheritDoc
     */
    public function registerResources(ResourceRegistry $registry) : void
    {
        parent::registerResources($registry);

        $registry->register("./src/UI/templates/js/Table/datatable.js");
    }


    /**
     * @param Table    $component
     * @param Settings $settings
     *
     * @return DataInterface
     */
    protected function handleFetchData(Table $component, Settings $settings) : DataInterface
    {
        if (!$component->getDataFetcher()->isFetchDataNeedsFilterFirstSet() || $settings->isFilterSet()) {
            $data = $component->getDataFetcher()->fetchData($settings);
        } else {
            $data = new Data([], 0);
        }

        return $data;
    }


    /**
     * @param BrowserFormat     $browser_format ,
     * @param Table             $component
     * @param DataInterface     $data
     * @param Settings          $settings
     * @param RendererInterface $renderer
     *
     * @return string
     */
    protected function handleFormat(BrowserFormat $browser_format, Table $component, DataInterface $data, Settings $settings, RendererInterface $renderer) : string
    {
        $input_format_id = $browser_format->getInputFormatId($component);

        /**
         * @var Format $format
         */
        $format = current(array_filter($component->getFormats(), function (Format $format) use ($input_format_id): bool {
            return ($format->getFormatId() === $input_format_id);
        }));

        if ($format === false) {
            $format = $browser_format;
        }

        $data = $format->render(function (string $name, bool $purge_unfilled_vars = true, bool $purge_unused_blocks = true) : Template {
            return $this->getTemplate($name, $purge_unfilled_vars, $purge_unused_blocks);
        }, $component, $data, $settings, $renderer);

        switch ($format->getOutputType()) {
            case Format::OUTPUT_TYPE_DOWNLOAD:
                $format->deliverDownload($data, $component);

                return "";

            case Format::OUTPUT_TYPE_PRINT:
            default:
                return $data;
        }
    }
}
