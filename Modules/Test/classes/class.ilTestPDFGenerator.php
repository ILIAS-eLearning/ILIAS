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

	private static function buildHtmlDocument($contentHtml, $styleHtml)
	{
		return "
			<html>
				<head>
					<meta http-equiv=\"Content-Type\" content=\"text/html; charset=utf-8\" />
 					$styleHtml
 				</head>
				<body>$contentHtml</body>
			</html>
		";
	}
	
	/**
	 * @param $html
	 * @return string
	 */
	private static function makeHtmlDocument($contentHtml, $styleHtml)
	{
		if(!is_string($contentHtml) || !strlen(trim($contentHtml)))
		{
			return $contentHtml;
		}
		
		$html = self::buildHtmlDocument($contentHtml, $styleHtml);

		$dom = new DOMDocument("1.0", "utf-8");
		if(!@$dom->loadHTML($html))
		{
			return $html;
		}
		
		$invalid_elements = array();

		$script_elements     = $dom->getElementsByTagName('script');
		foreach($script_elements as $elm)
		{
			$invalid_elements[] = $elm;
		}

		foreach($invalid_elements as $elm)
		{
			$elm->parentNode->removeChild($elm);
		}

		// remove noprint elems as tcpdf will make empty pdf when hidden by css rules
		$domX = new DomXPath($dom);
		foreach($domX->query("//*[contains(@class, 'noprint')]") as $node)
		{
			$node->parentNode->removeChild($node);
		}

		$dom->encoding = 'UTF-8';
		$cleaned_html = $dom->saveHTML();
		if(!$cleaned_html)
		{
			return $html;
		}

		return $cleaned_html;
	}

	public static function generatePDF($pdf_output, $output_mode, $filename=null)
	{
		$pdf_output = self::preprocessHTML($pdf_output);
		
		if (substr($filename, strlen($filename) - 4, 4) != '.pdf')
		{
			$filename .= '.pdf';
		}
		
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
		$pdf_css_path = self::getTemplatePath('test_pdf.css');
		$html = self::makeHtmlDocument($html, '<style>'.file_get_contents($pdf_css_path).'</style>');
		
		return $html;
	}

	protected static function getTemplatePath($a_filename)
	{
			$module_path = "Modules/Test/";

			// use ilStyleDefinition instead of account to get the current skin
			include_once "Services/Style/System/classes/class.ilStyleDefinition.php";
			if (ilStyleDefinition::getCurrentSkin() != "default")
			{
				$fname = "./Customizing/global/skin/".
					ilStyleDefinition::getCurrentSkin()."/".$module_path.basename($a_filename);
			}

			if($fname == "" || !file_exists($fname))
			{
				$fname = "./".$module_path."templates/default/".basename($a_filename);
			}
		return $fname;
	}

}