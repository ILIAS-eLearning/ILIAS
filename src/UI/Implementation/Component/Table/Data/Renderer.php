<?php

namespace ILIAS\UI\Implementation\Component\Table\Data;

use ILIAS\DI\Container;
use ILIAS\UI\Component\Component;
use ILIAS\UI\Component\Table\Data\Data\Data;
use ILIAS\UI\Component\Table\Data\Format\Format;
use ILIAS\UI\Component\Table\Data\Table;
use ILIAS\UI\Component\Table\Data\UserTableSettings\Settings;
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
class Renderer extends AbstractComponentRenderer {

	/**
	 * @var Container
	 */
	protected $dic;


	/**
	 * @inheritDoc
	 */
	protected function getComponentInterfaceName(): array {
		return [ Table::class ];
	}


	/**
	 * @inheritDoc
	 *
	 * @param Table $component
	 */
	public function render(Component $component, RendererInterface $default_renderer): string {
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
	protected function renderDataTable(Table $component, RendererInterface $renderer): string {
		$user_table_settings = $component->getUserTableSettingsStorage()->read($component->getTableId(), $this->dic->user()->getId());

		$user_table_settings = $component->getBrowserFormat()->handleUserTableSettingsInput($component, $user_table_settings);

		$user_table_settings = $component->getUserTableSettingsStorage()->handleDefaultSettings($user_table_settings, $component);

		$data = $this->handleFetchData($component, $user_table_settings);

		$html = $this->handleFormat($component, $data, $user_table_settings, $renderer);

		$component->getUserTableSettingsStorage()->store($user_table_settings, $component->getTableId(), $this->dic->user()->getId());

		return $html;
	}


	/**
	 * @inheritDoc
	 */
	public function registerResources(ResourceRegistry $registry): void {
		parent::registerResources($registry);

		$registry->register("./src/UI/templates/js/Table/datatable.min.js");
	}


	/**
	 * @param Table    $component
	 * @param Settings $user_table_settings
	 *
	 * @return Data
	 */
	protected function handleFetchData(Table $component, Settings $user_table_settings): Data {
		if (!$component->getDataFetcher()->isFetchDataNeedsFilterFirstSet() || $user_table_settings->isFilterSet()) {
			$data = $component->getDataFetcher()->fetchData($user_table_settings);
		} else {
			$data = $component->getDataFetcher()->data([], 0);
		}

		return $data;
	}


	/**
	 * @param Table             $component
	 * @param Data              $data
	 * @param Settings          $user_table_settings
	 * @param RendererInterface $renderer
	 *
	 * @return string
	 */
	protected function handleFormat(Table $component, Data $data, Settings $user_table_settings, RendererInterface $renderer): string {
		$input_format_id = $component->getBrowserFormat()->getInputFormatId($component);

		/**
		 * @var Format $format
		 */
		$format = current(array_filter($component->getFormats(), function (Format $format) use ($input_format_id): bool {
			return ($format->getFormatId() === $input_format_id);
		}));

		if ($format === false) {
			$format = $component->getBrowserFormat();
		}

		$data = $format->render(function (string $name): Template {
			return $this->getTemplate($name, true, true);
		}, $component, $data, $user_table_settings, $renderer);

		switch ($format->getOutputType()) {
			case Format::OUTPUT_TYPE_DOWNLOAD:
				$format->devliver($data, $component);

				return "";

			case Format::OUTPUT_TYPE_PRINT:
			default:
				return $data;
		}
	}
}
