<?php

class ilHokusPokusExamplePDFConsumer
{
	public function __construct()
	{
	}

	public function generateHokusPokusPDF()
	{
		$filename = ilUtil::getWebspaceDir() . '/hokuspokus/my_cool_nane.pdf';
		ilHokusPokusPDFGenerator::generatePDF($html = '<br/>', ilTestPDFGenerator::PDF_OUTPUT_FILE, $filename);
		//$angerswas->machWasDamit($filename);
	}
}