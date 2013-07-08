<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilTestPDFGenerator
 * 
 * Class that handles PDF generation for test and assessment.
 * 
 * @author Maximilian Becker <mbecker@databay.de>
 * @version $Id$
 * 
 */
class ilTestPDFGenerator 
{
	const PDF_OUTPUT_DOWNLOAD = 'D';
	const PDF_OUTPUT_INLINE = 'I';
	const PDF_OUTPUT_FILE = 'F';
	
	public static function generatePDF($pdf_output, $output_mode, $filename=null)
	{
		$pdf_output = self::preprocessHTML($pdf_output);
		
		require_once './Services/PDFGeneration/classes/class.ilPDFGeneration.php';
		
		$job = new ilPDFGenerationJob();
		$job->setAutoPageBreak(true)
			->setCreator('ILIAS Test')
			->setFilename($filename)
			->setMarginLeft('20')
			->setMarginRight('20')
			->setMarginTop('20')
			->setMarginBottom('20')
			->setOutputMode($output_mode)
			->addPage($pdf_output);
		
		ilPDFGeneration::doJob($job);
	}
	
	public static function preprocessHTML($html)
	{
		global $tpl;
		
		$pdf_css_path = $tpl->getTemplatePath('test_pdf.css', true);

		return '<style>' . file_get_contents($pdf_css_path)	. '</style>' . $html;
	}
}