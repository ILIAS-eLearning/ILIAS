<?php

namespace ILIAS\UI\Component\Table\Data\Format;

use ILIAS\DI\Container;
use ILIAS\UI\Component\Table\Data\Data\Data;
use ILIAS\UI\Component\Table\Data\Filter\Filter;
use ILIAS\UI\Component\Table\Data\Table;
use ILIAS\UI\Implementation\Render\TemplateFactory;
use ILIAS\UI\Renderer;

/**
 * Interface Format
 *
 * @package ILIAS\UI\Component\Table\Data\Format
 *
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 */
interface Format {

	/**
	 * @var string
	 */
	const FORMAT_BROWSER = "browser";
	/**
	 * @var string
	 */
	const FORMAT_CSV = "csv";
	/**
	 * @var string
	 */
	const FORMAT_EXCEL = "excel";
	/**
	 * @var string
	 */
	const FORMAT_PDF = "pdf";
	/**
	 * @var string
	 */
	const FORMAT_HTML = "html";
	/**
	 * @var int
	 */
	const OUTPUT_TYPE_PRINT = 1;
	/**
	 * @var int
	 */
	const OUTPUT_TYPE_DOWNLOAD = 2;


	/**
	 * Format constructor
	 *
	 * @param Container $dic
	 */
	public function __construct(Container $dic);


	/**
	 * @return string
	 */
	public function getFormatId(): string;


	/**
	 * @return string
	 */
	public function getDisplayTitle(): string;


	/**
	 * @return int
	 */
	public function getOutputType(): int;


	/**
	 * @return object
	 */
	public function getTemplate(): object;


	/**
	 * @param TemplateFactory $tpl_factory
	 * @param string          $tpl_path
	 * @param Table           $component
	 * @param Data            $data
	 * @param Filter          $filter
	 * @param Renderer        $renderer
	 *
	 * @return string
	 */
	public function render(TemplateFactory $tpl_factory, string $tpl_path, Table $component, Data $data, Filter $filter, Renderer $renderer): string;


	/**
	 * @param string $data
	 * @param Table  $component
	 */
	public function devliver(string $data, Table $component): void;
}
