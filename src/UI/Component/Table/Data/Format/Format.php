<?php

namespace ILIAS\UI\Component\Table\Data\Format;

use ILIAS\DI\Container;
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
	 * @return string
	 */
	public function getFileExtension(): string;


	/**
	 * @param string[] $columns
	 * @param array    $rows
	 * @param string   $title
	 * @param string   $table_id
	 * @param Renderer $renderer
	 */
	public function render(array $columns, array $rows, string $title, string $table_id, Renderer $renderer): string;


	/**
	 * @param string $data
	 * @param string $title
	 * @param string $table_id
	 */
	public function devliver(string $data, string $title, string $table_id): void;
}
