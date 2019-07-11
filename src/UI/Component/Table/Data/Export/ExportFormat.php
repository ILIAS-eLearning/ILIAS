<?php

namespace ILIAS\UI\Component\Table\Data\Export;

use ILIAS\DI\Container;
use ILIAS\UI\Renderer;

/**
 * Interface ExportFormat
 *
 * @package ILIAS\UI\Component\Table\Data\Export
 *
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 */
interface ExportFormat {

	/**
	 * @var string
	 */
	const EXPORT_FORMAT_CSV = "csv";
	/**
	 * @var string
	 */
	const EXPORT_FORMAT_EXCEL = "excel";
	/**
	 * @var string
	 */
	const EXPORT_FORMAT_PDF = "pdf";


	/**
	 * ExportFormat constructor
	 *
	 * @param Container $dic
	 */
	public function __construct(Container $dic);


	/**
	 * @return string
	 */
	public function getExportId(): string;


	/**
	 * @return string
	 */
	public function getTitle(): string;


	/**
	 * @param string[] $columns
	 * @param array    $rows
	 * @param string   $title
	 * @param string   $table_id
	 * @param Renderer $renderer
	 */
	public function export(array $columns, array $rows, string $title, string $table_id, Renderer $renderer): void;
}
