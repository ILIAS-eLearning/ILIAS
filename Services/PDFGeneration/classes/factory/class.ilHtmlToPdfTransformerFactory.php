<?php
/* Copyright (c) 1998-2015 ILIAS open source, Extended GPL, see docs/LICENSE */
require_once __DIR__ . '/../class.ilAbstractHtmlToPdfTransformer.php';
/**
 * Class ilHtmlToPdfTransformerFactory
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilHtmlToPdfTransformerFactory
{

	const PDF_OUTPUT_DOWNLOAD	= 'D';
	const PDF_OUTPUT_INLINE		= 'I';
	const PDF_OUTPUT_FILE		= 'F';


	/**
	 * @var ilLanguage $lng
	 */
	protected $lng;

	/**
	 * ilHtmlToPdfTransformerFactory constructor.
	 * @param $component
	 */
	public function __construct($component = '')
	{
		global $lng;
		$this->lng	= $lng;
	}

	/**
	 * @param $src
	 * @param $output
	 * @param $delivery_type
	 */
	public function deliverPDFFromHTMLFile($src, $output, $delivery_type)
	{
		$class_name = $this->pdf_transformer_settings->get('selected_transformer');
		$this->transformer = new $class_name;
		if($this->transformer->isActive())
		{
			$this->transformer->createPDFFileFromHTMLFile($src, $output);
			$this->deliverPDF($output, $delivery_type);
		}
	}

	/**
	 * @param $src
	 * @param $output
	 * @param $delivery_type
	 */
	public function deliverPDFFromHTMLString($src, $output, $delivery_type, $service, $purpose)
	{
		$map = ilPDFGeneratorUtils::getRendererMapForPurpose($service, $purpose);
		$class_name = $this->pdf_transformer_settings->get('selected_transformer');
		$this->transformer = new $class_name;
		if($this->transformer->isActive())
		{
			$this->transformer->createPDFFileFromHTMLString($src, $output);
			$this->deliverPDF($output, $delivery_type);
		}
	}

	/**
	 * @param $src
	 * @param $output
	 * @param $delivery_type
	 */
	public function deliverPDFFromFilesArray($src, $output, $delivery_type)
	{
		$class_name = $this->pdf_transformer_settings->get('selected_transformer');
		$this->transformer = new $class_name;
		if($this->transformer->isActive())
		{
			if(is_array($src) && $this->transformer->supportMultiSourcesFiles())
			{
				$this->transformer->createPDFFileFromHTMLFile($src, $output);
			}
			else
			{
				$this->transformer->createPDFFileFromHTMLFile($this->createOneFileFromArray($src), $output);
			}
			self::deliverPDF($output, $delivery_type);
		}
	}

	/**
	 * @param $output
	 */
	public function deliverTestingPDFFromTestingHTMLFile($output)
	{
		$class_name = $this->pdf_transformer_settings->get('selected_transformer');
		$this->transformer = new $class_name;
		if($this->transformer->isActive())
		{
			$this->transformer->createPDFFileFromHTMLFile($this->transformer->getPathToTestHTML(), $output);
			self::deliverPDF($output, self::PDF_OUTPUT_DOWNLOAD);
		}
	}

	/**
	 * @param $file
	 * @param $delivery_type
	 * @return mixed
	 */
	protected function deliverPDF($file, $delivery_type)
	{
		if(file_exists($file))
		{
			if(strtoupper($delivery_type) === self::PDF_OUTPUT_DOWNLOAD)
			{
				ilUtil::deliverFile($file, basename($file), '', false, true);
			}
			else if(strtoupper($delivery_type) === self::PDF_OUTPUT_INLINE)
			{
				ilUtil::deliverFile($file, basename($file), '', true, true);
			}
			else if(strtoupper($delivery_type) === self::PDF_OUTPUT_FILE)
			{
				return $file;
			}
			return $file;
		}
		return false;
	}
	/**
	 * @param array $src
	 * @return string
	 */
	protected function createOneFileFromArray(array $src)
	{
		$tmp_file = dirname(reset($src)) . '/complete_pages_overview.html';
		$html_content	= '';
		foreach($src as $filename)
		{
			if(file_exists($filename))
			{
				$html_content .= file_get_contents($filename);
			}
		}
		file_put_contents($tmp_file, $html_content);
		return $tmp_file;
	}
}