<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once __DIR__ . '/class.ilAbstractHtmlToPdfTransformer.php';
require_once __DIR__ . '/class.ilPDFGeneratorUtils.php';

/**
 * Class ilPhantomJsHtmlToPdfTransformer
 * @author Michael Jansen <mjansen@databay.de>
 */
class ilPhantomJsHtmlToPdfTransformer extends ilAbstractHtmlToPdfTransformer
{

	const SETTING_NAME = 'pdf_transformer_phantom';

	protected $config = array();

	/**
	 * @var ilSetting
	 */
	protected $pdf_phantom_settings;

	/**
	 * @var string
	 */
	protected $path_to_rasterize = './Services/PDFGeneration/js/rasterize.js';

	/**
	 * @var bool
	 */
	protected $phpunit = false;

	/**
	 * @var bool
	 */
	protected $use_default_config;

	/**
	 * @var string
	 */
	protected $page_size;

	/**
	 * @var float
	 */
	protected $zoom;

	/**
	 * @var string
	 */
	protected $orientation;

	/**
	 * @var string
	 */
	protected $margin;

	/**
	 * @var int
	 */
	protected $javascript_delay;

	/**
	 * @var string
	 */
	protected $viewport;

	/**
	 * @var bool
	 */
	protected $print_media_type;

	/**
	 * @var int
	 */
	protected $header_type;

	/**
	 * @var int
	 */
	protected $footer_type;

	/**
	 * @var string
	 */
	protected $header_text;

	/**
	 * @var string
	 */
	protected $header_height;

	/**
	 * @var bool
	 */
	protected $header_show_pages;

	/**
	 * @var string
	 */
	protected $footer_text;

	/**
	 * @var string
	 */
	protected $footer_height;

	/**
	 * @var bool
	 */
	protected $footer_show_pages;

	/**
	 * ilPhantomJsHtmlToPdfTransformer constructor.
	 * @param bool $phpunit_test
	 */
	public function __construct($phpunit_test = false)
	{
		$this->phpunit = $phpunit_test;
		$this->loadDefaultSettings();
	}

	/**
	 *
	 */
	protected function loadDefaultSettings()
	{
		if( ! $this->phpunit)
		{
			$this->pdf_phantom_settings = new ilSetting(self::SETTING_NAME);
			$this->setJavascriptDelay($this->pdf_phantom_settings->get('javascript_delay'));
			$this->setMargin($this->pdf_phantom_settings->get('margin'));
			$this->setOrientation($this->pdf_phantom_settings->get('orientation'));
			$this->setPageSize($this->pdf_phantom_settings->get('page_size'));
			$this->setZoom($this->pdf_phantom_settings->get('zoom'));
			$this->setPrintMediaType($this->pdf_phantom_settings->get('print_media_type'));
			$this->setHeaderType($this->pdf_phantom_settings->get('header_type'));
			$this->setHeaderText($this->pdf_phantom_settings->get('header_text'));
			$this->setHeaderHeight($this->pdf_phantom_settings->get('header_height'));
			$this->setHeaderShowPages($this->pdf_phantom_settings->get('header_show_pages'));
			$this->setFooterType($this->pdf_phantom_settings->get('footer_type'));
			$this->setFooterText($this->pdf_phantom_settings->get('footer_text'));
			$this->setFooterHeight($this->pdf_phantom_settings->get('footer_height'));
			$this->setFooterShowPages($this->pdf_phantom_settings->get('footer_show_pages'));
		}
	}

	/**
	 * @return bool
	 */
	public static function supportMultiSourcesFiles()
	{
		return false;
	}

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
		return 'phantomjs';
	}

	/**
	 * @return string
	 */
	public function isActive()
	{
		return $this->pdf_phantom_settings->get('is_active');
	}

	/**
	 * @param string $a_string
	 * @param string $a_target
	 */
	public function createPDFFileFromHTMLString($a_string, $a_target)
	{
		$html_file	= $this->getHtmlTempName();
		file_put_contents($html_file, $a_string);
		$this->createPDFFileFromHTMLFile($html_file, $a_target);
	}

	/**
	 * @param string $a_path_to_file
	 * @param string $a_target
	 */
	public function createPDFFileFromHTMLFile($a_path_to_file, $a_target)
	{
		global $ilLog;

		if(file_exists($a_path_to_file))
		{
			if( ! $this->isPrintMediaType())
			{
				ilPDFGeneratorUtils::removePrintMediaDefinitionsFromStyleFile(dirname($a_path_to_file) . '/style/');
			}
			$temp_file = $this->getPdfTempName();
			$args = ' ' . $a_path_to_file .' ' . $temp_file . ' ' . $this->getCommandLineConfig() .'';
			$return_value = ilUtil::execQuoted( $this->getPhantomJsPath() . ' ' . $this->path_to_rasterize. ' ', $args);

			$ilLog->write('ilPhantomJsHtmlToPdfTransformer command line config: ' . $args);
			foreach($return_value as $key => $value)
			{
				$ilLog->write('ilPhantomJsHtmlToPdfTransformer return value line ' . $key . ' : ' . $value );
			}

			if(file_exists($temp_file))
			{
				$ilLog->write('ilWebkitHtmlToPdfTransformer file exists: ' . $temp_file . ' file size is :' . filesize($temp_file) . ' bytes, will be renamed to '. $a_target);
				rename($temp_file, $a_target);
			}
			else
			{
				$ilLog->write('ilPhantomJsHtmlToPdfTransformer error: ' . print_r($return_value, true) );
			}
		}
	}
	/**
	 * @return string
	 */
	protected function getCommandLineConfig()
	{
		$this->populateConfig();
		return "'" . json_encode($this->config) ."'";
	}

	/**
	 *
	 */
	protected function populateConfig()
	{
		$this->config['page_size']		= $this->getPageSize();
		$this->config['zoom']			= $this->getZoom();
		$this->config['orientation']	= $this->getOrientation();
		$this->config['margin']			= $this->getMargin();
		$this->config['delay']			= $this->getJavascriptDelay();
		$this->config['viewport']		= $this->getViewPort();
		$this->config['header']			= $this->getHeaderArgs();
		$this->config['footer']			= $this->getFooterArgs();
	}

	/**
	 * @return int
	 */
	public function getHeaderType()
	{
		return $this->header_type;
	}

	/**
	 * @param int $header_type
	 */
	public function setHeaderType($header_type)
	{
		$this->header_type = $header_type;
	}

	/**
	 * @return int
	 */
	public function getFooterType()
	{
		return $this->footer_type;
	}

	/**
	 * @param int $footer_type
	 */
	public function setFooterType($footer_type)
	{
		$this->footer_type = $footer_type;
	}

	/**
	 * @return string
	 */
	public function getPageSize()
	{
		return $this->page_size;
	}

	/**
	 * @param string $page_size
	 */
	public function setPageSize($page_size)
	{
		$this->page_size = $page_size;
	}

	/**
	 * @return float
	 */
	public function getZoom()
	{
		return $this->zoom;
	}

	/**
	 * @param float $zoom
	 */
	public function setZoom($zoom)
	{
		$this->zoom = $zoom;
	}

	/**
	 * @return string
	 */
	public function getOrientation()
	{
		return $this->orientation;
	}

	/**
	 * @param string $orientation
	 */
	public function setOrientation($orientation)
	{
		$this->orientation = $orientation;
	}

	/**
	 * @return string
	 */
	public function getMargin()
	{
		return $this->margin;
	}

	/**
	 * @param string $margin
	 */
	public function setMargin($margin)
	{
		$this->margin = $margin;
	}

	/**
	 * @return int
	 */
	public function getJavascriptDelay()
	{
		return $this->javascript_delay;
	}

	/**
	 * @param int $javascript_delay
	 */
	public function setJavascriptDelay($javascript_delay)
	{
		$this->javascript_delay = $javascript_delay;
	}

	/**
	 * @return string
	 */
	public function getViewPort()
	{
		return $this->viewport;
	}

	/**
	 * @param string $viewport
	 */
	public function setViewport($viewport)
	{
		$this->viewport = $viewport;
	}

	/**
	 * @return string
	 */
	public function getHeaderText()
	{
		return $this->header_text;
	}

	/**
	 * @param string $header_text
	 */
	public function setHeaderText($header_text)
	{
		$this->header_text = $header_text;
	}

	/**
	 * @return string
	 */
	public function getHeaderHeight()
	{
		return $this->header_height;
	}

	/**
	 * @param string $header_height
	 */
	public function setHeaderHeight($header_height)
	{
		$this->header_height = $header_height;
	}

	/**
	 * @return boolean
	 */
	public function isHeaderShowPages()
	{
		if($this->header_show_pages == 1 || $this->header_show_pages == true)
		{
			return true;
		}
		return false;
	}

	/**
	 * @param boolean $header_show_pages
	 */
	public function setHeaderShowPages($header_show_pages)
	{
		$this->header_show_pages = $header_show_pages;
	}

	/**
	 * @return string
	 */
	public function getFooterText()
	{
		return $this->footer_text;
	}

	/**
	 * @param string $footer_text
	 */
	public function setFooterText($footer_text)
	{
		$this->footer_text = $footer_text;
	}

	/**
	 * @return string
	 */
	public function getFooterHeight()
	{
		return $this->footer_height;
	}

	/**
	 * @param string $footer_height
	 */
	public function setFooterHeight($footer_height)
	{
		$this->footer_height = $footer_height;
	}

	/**
	 * @return boolean
	 */
	public function isFooterShowPages()
	{
		if($this->footer_show_pages == 1 || $this->footer_show_pages == true)
		{
			return true;
		}
		return false;
	}

	/**
	 * @param boolean $footer_show_pages
	 */
	public function setFooterShowPages($footer_show_pages)
	{
		$this->footer_show_pages = $footer_show_pages;
	}

	/**
	 * @return array
	 */
	protected function getHeaderArgs()
	{
		if($this->getHeaderType() == ilPDFGenerationConstants::HEADER_TEXT)
		{
			return array('text'			=> $this->getHeaderText(),
						 'height'		=> $this->getHeaderHeight(),
						 'show_pages'	=> $this->isHeaderShowPages());
		}
		else
		{
			return null;
		}
	}

	/**
	 * @return array
	 */
	protected function getFooterArgs()
	{
		if($this->getFooterType() == ilPDFGenerationConstants::FOOTER_TEXT)
		{
			return array('text'			=> $this->getFooterText(),
						 'height'		=> $this->getFooterHeight(),
						 'show_pages'	=> $this->isFooterShowPages());
		}
		else
		{
			return null;
		}
	}

	/**
	 * @return string
	 */
	protected function getPhantomJsPath()
	{
		return $this->pdf_phantom_settings->get('path');
	}


	/**
	 * @return boolean
	 */
	public function isPrintMediaType()
	{
		if( $this->print_media_type == 1 ||  $this->print_media_type == true)
		{
			return true;
		}
		return false;
	}

	/**
	 * @param boolean $print_media_type
	 */
	public function setPrintMediaType($print_media_type)
	{
		$this->print_media_type = $print_media_type;
	}

	/**
	 * @return string
	 */
	public function getPathToTestHTML()
	{
		return 'Services/PDFGeneration/templates/default/test_complex.html';
	}

	/**
	 * @return bool
	 */
	public function hasInfoInterface()
	{
		if($this->isActive())
		{
			return true;
		}
		return false;
	}

	/**
	 * @return string
	 */
	public function showInfo()
	{
		$args			=  ' --version ';
		$return_value	= ilUtil::execQuoted($this->getPhantomJsPath(), $args);
		$log = $this->getPhantomJsPath() . ':';
		foreach($return_value as $key => $value)
		{
			$log .= ' ' . $value;
		}
		$args			= ' version ';
		$return_value	= ilUtil::execQuoted($this->getPhantomJsPath(), ' ' . $this->path_to_rasterize. ' ' . $args);
		foreach($return_value as $key => $value)
		{
			$log .= '<br/> ' . $this->path_to_rasterize .': '. $value;
		}
		return $log;
	}

}