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

	/**
	 * @param $html
	 * @return string
	 */
	private static function removeScriptElements($html)
	{
		if(!is_string($html) || !strlen(trim($html)))
		{
			return $html;
		}

		$dom = new DOMDocument();
		if(!@$dom->loadHTML($html))
		{
			return $html;
		}

		$script_elements     = $dom->getElementsByTagName('script');
		$num_script_elements = $script_elements->length;

		for($i = 0; $i < $num_script_elements; $i++)
		{
			$elm = $script_elements->item($i);
			$elm->parentNode->removeChild($elm);
		}

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
		$html = self::removeScriptElements($html);
		$pdf_css_path = self::getTemplatePath('test_pdf.css');
		return '<style>' . file_get_contents($pdf_css_path)	. '</style>' . $html;
	}

	protected static function getTemplatePath($a_filename)
	{
			$module_path = "Modules/Test/";

			// use ilStyleDefinition instead of account to get the current skin
			include_once "Services/Style/classes/class.ilStyleDefinition.php";
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