<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */


/**
 * Class ilTCPDFGenerator
 * 
 * @author Maximilian Becker <mbecker@databay.de>
 * @version $Id$
 */
class ilTCPDFGenerator
{
	public static function generatePDF(ilPDFGenerationJob $job)
	{
		require_once 'tcpdf/tcpdf.php';

		// create new PDF document
		$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

		// set document information
		$pdf->SetCreator( $job->getCreator() );
		$pdf->SetAuthor( $job->getAuthor() );
		$pdf->SetTitle( $job->getTitle() );
		$pdf->SetSubject( $job->getSubject() );
		$pdf->SetKeywords( $job->getKeywords() );

		//$pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE.' 006', PDF_HEADER_STRING); // TODO
		$pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN)); // TODO
		$pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA)); // TODO
		$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED); // TODO
		$pdf->SetMargins($job->getMarginLeft(), $job->getMarginTop(), $job->getMarginRight());
		$pdf->SetHeaderMargin(PDF_MARGIN_HEADER); // TODO
		$pdf->SetFooterMargin(PDF_MARGIN_FOOTER); // TODO
		$pdf->SetAutoPageBreak($job->getAutoPageBreak(), $job->getMarginBottom());
		$pdf->setImageScale($job->getImageScale());
		$pdf->SetFont('dejavusans', '', 10); // TODO

		/* // TODO
		// set some language-dependent strings (optional)
		if (file_exists(dirname(__FILE__).'/lang/eng.php')) {
			require_once(dirname(__FILE__).'/lang/eng.php');
			$pdf->setLanguageArray($l);
		}
		*/
		// set font

		foreach ($job->getPages() as $page)
		{
			$pdf->AddPage();
			$pdf->writeHTML($page, true, false, true, false, '');			
		}

		$result = $pdf->Output($job->getFilename(), $job->getOutputMode() ); // (I - Inline, D - Download, F - File)
	}
}