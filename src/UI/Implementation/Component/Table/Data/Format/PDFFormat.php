<?php

namespace ILIAS\UI\Implementation\Component\Table\Data\Format;

use ilHtmlToPdfTransformerFactory;
use ILIAS\UI\Component\Table\Data\Table;

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
	protected function getFileExtension(): string {
		return "pdf";
	}


	/**
	 * @inheritDoc
	 */
	protected function renderTemplate(Table $component): string {
		$html = parent::renderTemplate($component);

		$pdf = new ilHtmlToPdfTransformerFactory();

		$tmp_file = $pdf->deliverPDFFromHTMLString($html, "", ilHtmlToPdfTransformerFactory::PDF_OUTPUT_FILE, self::class, $component->getTableId());

		$data = file_get_contents($tmp_file);

		unlink($tmp_file);

		return $data;
	}
}
