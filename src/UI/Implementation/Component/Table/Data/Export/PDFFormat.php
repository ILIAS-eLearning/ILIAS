<?php

namespace ILIAS\UI\Implementation\Component\Table\Data\Export;

use ilHtmlToPdfTransformerFactory;
use ILIAS\UI\Component\Table\Data\Table;
use ILIAS\UI\Renderer;

/**
 * Class PDFFormat
 *
 * @package ILIAS\UI\Implementation\Component\Table\Data\Export
 *
 * @author  studer + raimann ag - Team Custom 1 <support-custom1@studer-raimann.ch>
 */
class PDFFormat extends HTMLFormat {

	/**
	 * @inheritDoc
	 */
	public function getFormatId(): string {
		return self::FORMAT_PDF;
	}


	/**
	 * @inheritDoc
	 */
	public function getDisplayTitle(): string {
		return $this->dic->language()->txt(Table::LANG_MODULE . "_export_pdf");
	}


	/**
	 * @inheritDoc
	 */
	public function getFileExtension(): string {
		return "pdf";
	}


	/**
	 * @inheritDoc
	 */
	public function render(array $columns, array $rows, string $title, string $table_id, Renderer $renderer): string {
		$html = parent::render($columns, $rows, $title, $table_id, $renderer);

		$pdf = new ilHtmlToPdfTransformerFactory();

		$tmp_file = $pdf->deliverPDFFromHTMLString($html, "", ilHtmlToPdfTransformerFactory::PDF_OUTPUT_FILE, self::class, $table_id);

		$data = file_get_contents($tmp_file);
		unlink($tmp_file);

		return $data;
	}
}
