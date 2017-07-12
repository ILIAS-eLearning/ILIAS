<?php
require_once __DIR__ . '/class.ilPDFGeneration.php';
require_once __DIR__ . '/class.ilPDFGenerationJob.php';
require_once 'Services/PDFGeneration/classes/class.ilAbstractHtmlToPdfTransformer.php';

class ilTCPDFHtmlToPdfTransformer extends ilAbstractHtmlToPdfTransformer
{
	const SETTING_NAME = 'pdf_transformer_tcpdf';

	/**
	 * @var string
	 */
	protected $margin = '10';

	/**
	 * @return string
	 */
	public function getId()
	{
		return __CLASS__;
	}

	/**
	 * @return string
	 */
	public function getTitle()
	{
		return 'tcpdf';
	}

	/**
	 * @return string
	 */
	public function isActive()
	{
		$tcpdf_set = new ilSetting(self::SETTING_NAME);
		return $tcpdf_set->get('is_active');
	}

	/**
	 * @param string $a_string
	 * @param string $a_target
	 */
	public function createPDFFileFromHTMLString($a_string, $a_target)
	{
		$html_file	= ilUtil::ilTempnam() . '.html';
		file_put_contents($html_file, $a_string);
		self::createPDFFileFromHTMLFile($html_file, $a_target);
	}

	/**
	 * @param string|array $a_path_to_file
	 * @param string $a_target
	 */
	public function createPDFFileFromHTMLFile($a_path_to_file, $a_target)
	{
		if(is_array($a_path_to_file))
		{
			$this->runFileGenerationJob($a_path_to_file, $a_target);
		}
		else
		{
			$this->runFileGenerationJob(array($a_path_to_file), $a_target);
		}
	}

	/**
	 * @param array $paths_to_files
	 * @param $a_target
	 */
	protected function runFileGenerationJob($paths_to_files, $a_target)
	{
		$job = new ilPDFGenerationJob();

		foreach($paths_to_files as $a_path_to_file)
		{
			if(file_exists($a_path_to_file))
			{
				$html = file_get_contents($a_path_to_file);
				$html = preg_replace("/\?dummy\=[0-9]+/", "", $html);
				$html = preg_replace("/\?vers\=[0-9A-Za-z\-]+/", "", $html);
				$job->addPage($html);
			}
		}

		$job->setAutoPageBreak(true)
			->setMarginLeft($this->margin)
			->setMarginRight($this->margin)
			->setMarginTop($this->margin)
			->setMarginBottom($this->margin)
			->setOutputMode("F")
			->setFilename($a_target)
			->setCreator("ILIAS")
			->setImageScale(1);
		ilPDFGeneration::doJob($job);
	}

	public function getPathToTestHTML()
	{
		return 'Services/PDFGeneration/templates/default/test_complex.html';
	}

	public static function supportMultiSourcesFiles()
	{
		return true;
	}

	/**
	 * @return bool
	 */
	public function hasInfoInterface()
	{
		return false;
	}

}