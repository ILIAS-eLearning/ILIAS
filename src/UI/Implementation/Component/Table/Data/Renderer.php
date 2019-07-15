<?php

namespace ILIAS\UI\Implementation\Component\Table\Data;

use Closure;
use ILIAS\DI\Container;
use ILIAS\UI\Component\Component;
use ILIAS\UI\Component\Table\Data\Data\Data;
use ILIAS\UI\Component\Table\Data\Filter\Filter;
use ILIAS\UI\Component\Table\Data\Format\Format;
use ILIAS\UI\Component\Table\Data\Table;
use ILIAS\UI\Implementation\Render\AbstractComponentRenderer;
use ILIAS\UI\Implementation\Render\ResourceRegistry;
use ILIAS\UI\Implementation\Render\TemplateFactory;
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
		$filter = $component->getFilterStorage()->read($component->getTableId(), $this->dic->user()->getId());

		$filter = $component->getBrowserFormat()->handleFilterInput($component, $filter);

		$filter = $component->getFilterStorage()->handleDefaultFilter($filter, $component);

		$data = $this->handleFetchData($component, $filter);

		$html = $this->handleFormat($component, $data, $filter, $renderer);

		$component->getFilterStorage()->store($filter, $component->getTableId(), $this->dic->user()->getId());

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
	 * @param Table  $component
	 * @param Filter $filter
	 *
	 * @return Data
	 */
	protected function handleFetchData(Table $component, Filter $filter): Data {
		if (!$component->getDataFetcher()->isFetchDataNeedsFilterFirstSet() || $filter->isFilterSet()) {
			$data = $component->getDataFetcher()->fetchData($filter);
		} else {
			$data = $component->getDataFetcher()->data([], 0);
		}

		return $data;
	}


	/**
	 * @param Table             $component
	 * @param Data              $data
	 * @param Filter            $filter
	 * @param RendererInterface $renderer
	 *
	 * @return string
	 */
	protected function handleFormat(Table $component, Data $data, Filter $filter, RendererInterface $renderer): string {
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

		$data = $format->render(Closure::bind(function (): TemplateFactory { return $this->tpl_factory; }, $this, AbstractComponentRenderer::class)(), $this->getTemplatePath(""), $component, $data, $filter, $renderer); // TODO: `$this->tpl_factory` is private!!!

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
