<?php

namespace ILIAS\UI\Implementation\Component\Table\Data\Format;

use ilHtmlToPdfTransformerFactory;
use ILIAS\UI\Renderer;

/**
 * Class PDFFormat
 *
 * @package ILIAS\UI\Implementation\Component\Table\Data\Format
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
