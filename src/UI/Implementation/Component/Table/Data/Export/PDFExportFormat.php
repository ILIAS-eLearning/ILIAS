<?php

namespace ILIAS\UI\Implementation\Component\Table\Data\Export;

use ilHtmlToPdfTransformerFactory;
use ILIAS\UI\Component\Table\Data\Table;
use ILIAS\UI\Renderer;
use ilTemplate;

/**
 * Class PDFExportFormat
 *
 * @package ILIAS\UI\Implementation\Component\Table\Data\Export
 *
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 */
class PDFExportFormat extends AbstractExportFormat {

	/**
	 * @inheritDoc
	 */
	public function getExportId(): string {
		return self::EXPORT_FORMAT_PDF;
	}


	/**
	 * @inheritDoc
	 */
	public function getTitle(): string {
		return $this->dic->language()->txt(Table::LANG_MODULE . "_export_pdf");
	}


	/**
	 * @inheritDoc
	 */
	public function export(array $columns, array $rows, string $title, string $table_id, Renderer $renderer): void {
		$tpl = new ilTemplate(__DIR__
			. "/../../../../../templates/default/Table/tpl.datatable.html", true, true); // TODO: Somehow access `getTemplate` of renderer

		$tpl->setVariable("TITLE", $title);

		$tpl->setCurrentBlock("header");
		foreach ($columns as $column) {
			$tpl->setVariable("HEADER", $column);

			$tpl->parseCurrentBlock();
		}

		$tpl->setCurrentBlock("body");
		foreach ($rows as $row) {
			$tpl_row = new ilTemplate(__DIR__
				. "/../../../../../templates/default/Table/tpl.datatablerow.html", true, true); // TODO: Somehow access `getTemplate` of renderer

			$tpl_row->setCurrentBlock("row");

			foreach ($row as $column) {
				$tpl_row->setVariable("COLUMN", $column);

				$tpl_row->parseCurrentBlock();
			}

			$tpl->setVariable("ROW", $tpl_row->get());

			$tpl->parseCurrentBlock();
		}

		$html = $tpl->get();

		$filename = $title . ".pdf";

		$pdf = new ilHtmlToPdfTransformerFactory();

		$pdf->deliverPDFFromHTMLString($html, $filename, ilHtmlToPdfTransformerFactory::PDF_OUTPUT_DOWNLOAD, self::class, $table_id);
	}
}
