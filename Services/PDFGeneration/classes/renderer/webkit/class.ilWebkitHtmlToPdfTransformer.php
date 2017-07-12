<?php
/* Copyright (c) 1998-2013 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once __DIR__ . '/class.ilAbstractHtmlToPdfTransformer.php';
require_once __DIR__ . '/class.ilPDFGenerationConstants.php';

/**
 * Class ilWebkitHtmlToPdfTransformer
 * @author Michael Jansen <mjansen@databay.de>
 * @author Guido Vollbach <gvollbach@databay.de>
 */
class ilWebkitHtmlToPdfTransformer extends ilAbstractHtmlToPdfTransformer
{

	const SETTING_NAME = 'pdf_transformer_webkit';

	const ENABLE_QUIET = true;

	/**
	 * @var bool
	 */
	protected $phpunit = false;
	/**
	 * @var array
	 */
	protected $config = array();
	/**
	 * @var ilSetting
	 */
	protected $pdf_webkit_settings;
	/**
	 * @var float
	 */
	protected $zoom;
	/**
	 * @var bool
	 */
	protected $external_links;
	/**
	 * @var bool
	 */
	protected $enabled_forms;
	/**
	 * @var string
	 */
	protected $user_stylesheet;
	/**
	 * @var bool
	 */
	protected $greyscale;
	/**
	 * @var bool
	 */
	protected $low_quality;
	/**
	 * @var string
	 */
	protected $orientation;
	/**
	 * @var bool
	 */
	protected $print_media_type;
	/**
	 * @var string
	 */
	protected $page_size;
	/**
	 * @var int
	 */
	protected $javascript_delay;
	/**
	 * @var string
	 */
	protected $margin_left;
	/**
	 * @var string
	 */
	protected $margin_right;
	/**
	 * @var string
	 */
	protected $margin_top;
	/**
	 * @var string
	 */
	protected $margin_bottom;
	/**
	 * @var int
	 */
	protected $header_type;
	/**
	 * @var string
	 */
	protected $header_text_left;
	/**
	 * @var string
	 */
	protected $header_text_center;
	/**
	 * @var string
	 */
	protected $header_text_right;
	/**
	 * @var int
	 */
	protected $header_text_spacing;
	/**
	 * @var bool
	 */
	protected $header_text_line;
	/**
	 * @var string
	 */
	protected $header_html;
	/**
	 * @var int
	 */
	protected $header_html_spacing;
	/**
	 * @var bool
	 */
	protected $header_html_line;
	/**
	 * @var int
	 */
	protected $footer_type;
	/**
	 * @var string
	 */
	protected $footer_text_left;
	/**
	 * @var string
	 */
	protected $footer_text_center;
	/**
	 * @var string
	 */
	protected $footer_text_right;
	/**
	 * @var int
	 */
	protected $footer_text_spacing;
	/**
	 * @var bool
	 */
	protected $footer_text_line;
	/**
	 * @var string
	 */
	protected $footer_html;
	/**
	 * @var int
	 */
	protected $footer_html_spacing;
	/**
	 * @var bool
	 */
	protected $footer_html_line;
	/**
	 * @var string
	 */
	protected $checkbox_svg;
	/**
	 * @var string
	 */
	protected $checkbox_checked_svg;
	/**
	 * @var string
	 */
	protected $radio_button_svg;
	/**
	 * @var string
	 */
	protected $radio_button_checked_svg;

	/**
	 * ilWebkitHtmlToPdfTransformer constructor.
	 * @param bool $phpunit_test
	 */
	public function __construct($phpunit_test = false)
	{
		$this->phpunit = $phpunit_test;
		$this->loadDefaultSettings();
	}

	protected function loadDefaultSettings()
	{
		if( ! $this->phpunit)
		{
			$this->pdf_webkit_settings = new ilSetting(self::SETTING_NAME);

			$this->setPageSize($this->pdf_webkit_settings->get('page_size'));
			$this->setGreyscale((bool)$this->pdf_webkit_settings->get('greyscale'));
			$this->setPrintMediaType((bool)$this->pdf_webkit_settings->get('print_media_type'));
			$this->setLowQuality((bool)$this->pdf_webkit_settings->get('low_quality'));
			$this->setOrientation($this->pdf_webkit_settings->get('orientation'));
			$this->setZoom($this->pdf_webkit_settings->get('zoom'));
			$this->setExternalLinks((bool)$this->pdf_webkit_settings->get('external_links'));
			$this->setEnabledForms((bool)$this->pdf_webkit_settings->get('enable_forms'));
			$this->setUserStylesheet($this->pdf_webkit_settings->get('user_stylesheet'));
			$this->setCheckboxSvg($this->pdf_webkit_settings->get('checkbox_svg'));
			$this->setCheckboxCheckedSvg($this->pdf_webkit_settings->get('checkbox_checked_svg'));
			$this->setRadioButtonSvg($this->pdf_webkit_settings->get('radio_button_svg'));
			$this->setRadioButtonCheckedSvg($this->pdf_webkit_settings->get('radio_button_checked_svg'));
			$this->setJavascriptDelay($this->pdf_webkit_settings->get('javascript_delay'));

			$this->setMarginLeft($this->pdf_webkit_settings->get('margin_left'));
			$this->setMarginRight($this->pdf_webkit_settings->get('margin_right'));
			$this->setMarginTop($this->pdf_webkit_settings->get('margin_top'));
			$this->setMarginBottom($this->pdf_webkit_settings->get('margin_bottom'));

			$this->setHeaderType($this->pdf_webkit_settings->get('header_select'));
			$this->setHeaderTextLine((bool)$this->pdf_webkit_settings->get('head_text_line'));
			$this->setHeaderTextSpacing($this->pdf_webkit_settings->get('head_text_spacing'));
			$this->setHeaderTextRight($this->pdf_webkit_settings->get('head_text_right'));
			$this->setHeaderTextLeft($this->pdf_webkit_settings->get('head_text_left'));
			$this->setHeaderTextCenter($this->pdf_webkit_settings->get('head_text_center'));
			$this->setHeaderHtml($this->pdf_webkit_settings->get('head_html'));
			$this->setHeaderHtmlSpacing($this->pdf_webkit_settings->get('head_html_spacing'));
			$this->setHeaderHtmlLine((bool)$this->pdf_webkit_settings->get('head_html_line'));

			$this->setFooterType($this->pdf_webkit_settings->get('footer_select'));
			$this->setFooterTextLeft($this->pdf_webkit_settings->get('footer_text_left'));
			$this->setFooterTextCenter($this->pdf_webkit_settings->get('footer_text_center'));
			$this->setFooterTextRight($this->pdf_webkit_settings->get('footer_text_right'));
			$this->setFooterTextSpacing($this->pdf_webkit_settings->get('footer_text_spacing'));
			$this->setFooterTextLine((bool)$this->pdf_webkit_settings->get('footer_text_line'));
			$this->setFooterHtml($this->pdf_webkit_settings->get('footer_html'));
			$this->setFooterHtmlSpacing($this->pdf_webkit_settings->get('footer_html_spacing'));
			$this->setFooterHtmlLine((bool)$this->pdf_webkit_settings->get('footer_html_line'));
		}
	}

	/**
	 * @return bool
	 */
	public static function supportMultiSourcesFiles()
	{
		return true;
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
		return 'webkit';
	}

	/**
	 * @return string
	 */
	public function isActive()
	{
		return $this->pdf_webkit_settings->get('is_active');
	}

	/**
	 * @param string $a_string
	 * @param string $a_target
	 */
	public function createPDFFileFromHTMLString($a_string, $a_target)
	{
		$html_file = $this->getHtmlTempName();
		file_put_contents($html_file, $a_string);
		$this->createPDFFileFromHTMLFile($html_file, $a_target);
	}

	/**
	 * @param string|array $a_path_to_file
	 * @param string $a_target
	 */
	public function createPDFFileFromHTMLFile($a_path_to_file, $a_target)
	{
		if(is_array($a_path_to_file))
		{
			$files_list_as_string = ' ';
			foreach($a_path_to_file as $file)
			{
				if(file_exists($file))
				{
					$files_list_as_string .= ' '.$files_list_as_string;
				}
			}
			$this->runCommandLine($files_list_as_string, $a_target);
		}
		else if(file_exists($a_path_to_file))
		{
			$this->runCommandLine($a_path_to_file, $a_target);
		}
	}

	/**
	 * @param $a_path_to_file
	 * @param $a_target
	 */
	protected function runCommandLine($a_path_to_file, $a_target)
	{
		global $ilLog;
		$temp_file		= $this->getPdfTempName();
		$args			= $this->getCommandLineConfig() . ' ' . $a_path_to_file . ' ' . $temp_file . $this->redirectLog();
		$return_value	= ilUtil::execQuoted($this->getWKHTMLToPdfPath(), $args);

		$ilLog->write('ilWebkitHtmlToPdfTransformer command line config: ' . $args);
		foreach($return_value as $key => $value)
		{
			$ilLog->write('ilWebkitHtmlToPdfTransformer return value line ' . $key . ' : ' . $value );
		}
		if(file_exists($temp_file))
		{
			$ilLog->write('ilWebkitHtmlToPdfTransformer file exists: ' . $temp_file . ' file size is :' . filesize($temp_file) . ' bytes, will be renamed to '. $a_target);
			rename($temp_file, $a_target);
		}
		else
		{
			$ilLog->write('ilWebkitHtmlToPdfTransformer error: ' . print_r($return_value, true) );
		}
	}

	/**
	 * @return string
	 */
	protected function getCommandLineConfig()
	{
		$this->generateCommandLineConfig();
		$settings = ' ';
		foreach($this->config as $value)
		{
			$settings .= '--' . $value . ' ';
		}
		return $settings;
	}

	protected function generateCommandLineConfig()
	{
		$this->getZoomArgument();
		$this->getExternalLinksArgument();
		$this->getEnabledFormsArgument();
		$this->getUserStylesheetArgument();
		$this->getGreyscaleArgument();
		$this->getLowQualityArgument();
		$this->getOrientationArgument();
		$this->getPrintMediaTypeArgument();
		$this->getPageSizeArgument();
		$this->getJavascriptDelayArgument();
		$this->getCheckboxSvgArgument();
		$this->getCheckboxCheckedSvgArgument();
		$this->getRadioButtonSvgArgument();
		$this->getRadioButtonCheckedSvgArgument();
		$this->getMarginArgument();
		$this->getHeaderArgument();
		$this->getFooterArgument();
		$this->getDebugArgument();
	}

	/**
	 * @return string
	 */
	protected function getZoomArgument()
	{
		$this->config[] = 'zoom ' . $this->getZoom();
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
	protected function getExternalLinksArgument()
	{
		if($this->getExternalLinks())
		{
			$this->config[] = 'enable-external-links';
		}
		else
		{
			$this->config[] = 'disable-external-links';
		}
	}

	/**
	 * @return bool
	 */
	public function getExternalLinks()
	{
		return $this->external_links;
	}

	/**
	 * @param boolean $external_links
	 */
	public function setExternalLinks($external_links)
	{
		$this->external_links = $external_links;
	}

	/**
	 * @return string
	 */
	protected function getEnabledFormsArgument()
	{
		if($this->getEnabledForms())
		{
			$this->config[] = 'enable-forms';
		}
		else
		{
			$this->config[] = 'disable-forms';
		}
	}

	/**
	 * @return bool
	 */
	public function getEnabledForms()
	{
		return $this->enabled_forms;
	}

	/**
	 * @param boolean $enabled_forms
	 */
	public function setEnabledForms($enabled_forms)
	{
		$this->enabled_forms = $enabled_forms;
	}

	/**
	 * @return string
	 */
	protected function getUserStylesheetArgument()
	{
		$stylesheet = $this->getUserStylesheet();
		if($stylesheet != '')
		{
			$this->config[] = 'user-style-sheet "' . $stylesheet . '"';
		}
	}

	/**
	 * @return string
	 */
	public function getUserStylesheet()
	{
		return $this->user_stylesheet;
	}

	/**
	 * @param string $user_stylesheet
	 */
	public function setUserStylesheet($user_stylesheet)
	{
		$this->user_stylesheet = $user_stylesheet;
	}

	/**
	 * @return string
	 */
	protected function getGreyscaleArgument()
	{
		if($this->getGreyscale())
		{
			$this->config[] = 'grayscale';
		}
	}

	/**
	 * @return bool
	 */
	public function getGreyscale()
	{
		return $this->greyscale;
	}

	/**
	 * @param boolean $greyscale
	 */
	public function setGreyscale($greyscale)
	{
		$this->greyscale = $greyscale;
	}

	/**
	 * @return string
	 */
	protected function getLowQualityArgument()
	{
		if($this->getLowQuality() == 1 || $this->getLowQuality() == true)
		{
			$this->config[] = 'lowquality';
		}
	}

	/**
	 * @return bool
	 */
	public function getLowQuality()
	{
		return $this->low_quality;
	}

	/**
	 * @param boolean $low_quality
	 */
	public function setLowQuality($low_quality)
	{
		$this->low_quality = $low_quality;
	}

	/**
	 * @return string
	 */
	protected function getOrientationArgument()
	{
		$orientation = $this->getOrientation();
		if($orientation == '' || $orientation == 'Portrait')
		{
			$this->config[] = 'orientation Portrait';
		}
		else
		{
			$this->config[] = 'orientation Landscape';
		}
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
	protected function getPrintMediaTypeArgument()
	{
		if($this->getPrintMediaType() == 1)
		{
			$this->config[] = 'print-media-type';
		}
	}

	/**
	 * @return bool
	 */
	public function getPrintMediaType()
	{
		return $this->print_media_type;
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
	protected function getPageSizeArgument()
	{
		$this->config[] = 'page-size ' . $this->getPageSize();
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
	 * @return string
	 */
	protected function getJavascriptDelayArgument()
	{
		$javascript_delay = $this->getJavascriptDelay();
		$this->config[]   = 'javascript-delay ' . $javascript_delay;
	}

	/**
	 * @return string
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
	protected function getMarginArgument()
	{
		$this->config[] = 'margin-bottom '	.	$this->getMarginBottom();
		$this->config[] = 'margin-left '	.	$this->getMarginLeft();
		$this->config[] = 'margin-right '	.	$this->getMarginRight();
		$this->config[] = 'margin-top '		.	$this->getMarginTop();
	}

	/**
	 * @return string
	 */
	public function getMarginBottom()
	{
		return $this->margin_bottom;
	}

	/**
	 * @param string $margin_bottom
	 */
	public function setMarginBottom($margin_bottom)
	{
		$this->margin_bottom = $margin_bottom;
	}

	/**
	 * @return string
	 */
	public function getMarginLeft()
	{
		return $this->margin_left;
	}

	/**
	 * @param string $margin_left
	 */
	public function setMarginLeft($margin_left)
	{
		$this->margin_left = $margin_left;
	}

	/**
	 * @return string
	 */
	public function getMarginRight()
	{
		return $this->margin_right;
	}

	/**
	 * @param string $margin_right
	 */
	public function setMarginRight($margin_right)
	{
		$this->margin_right = $margin_right;
	}

	/**
	 * @return string
	 */
	public function getMarginTop()
	{
		return $this->margin_top;
	}

	/**
	 * @param string $margin_top
	 */
	public function setMarginTop($margin_top)
	{
		$this->margin_top = $margin_top;
	}

	/**
	 * @return string
	 */
	protected function getHeaderArgument()
	{
		$header_value  = $this->getHeaderType();
		$header_string = '';
		if($header_value == ilPDFGenerationConstants::HEADER_TEXT)
		{
			$this->config[] = 'header-left "'	.	$this->getHeaderTextLeft()		. '"';
			$this->config[] = 'header-center "'	.	$this->getHeaderTextCenter()	. '"';
			$this->config[] = 'header-right "'	.	$this->getHeaderTextRight()		. '"';
			$this->config[] = 'header-spacing '	.	$this->getHeaderTextSpacing();
			if($this->isHeaderTextLine())
			{
				$this->config[] = 'header-line';
			}
		}
		else if($header_value == ilPDFGenerationConstants::HEADER_HTML)
		{
			$this->config[] = 'header-html "'	.	$this->getHeaderHtml(). '"';
			$this->config[] = 'header-spacing '	.	$this->getHeaderHtmlSpacing();
			if($this->isHeaderHtmlLine())
			{
				$this->config[] = 'header-line';
			}
		}
		return $header_string;
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
	 * @return string
	 */
	public function getHeaderTextLeft()
	{
		return $this->header_text_left;
	}

	/**
	 * @param string $header_text_left
	 */
	public function setHeaderTextLeft($header_text_left)
	{
		$this->header_text_left = $header_text_left;
	}

	/**
	 * @return string
	 */
	public function getHeaderTextCenter()
	{
		return $this->header_text_center;
	}

	/**
	 * @param string $header_text_center
	 */
	public function setHeaderTextCenter($header_text_center)
	{
		$this->header_text_center = $header_text_center;
	}

	/**
	 * @return string
	 */
	public function getHeaderTextRight()
	{
		return $this->header_text_right;
	}

	/**
	 * @param string $header_text_right
	 */
	public function setHeaderTextRight($header_text_right)
	{
		$this->header_text_right = $header_text_right;
	}

	/**
	 * @return int
	 */
	public function getHeaderTextSpacing()
	{
		return $this->header_text_spacing;
	}

	/**
	 * @param int $header_text_spacing
	 */
	public function setHeaderTextSpacing($header_text_spacing)
	{
		$this->header_text_spacing = $header_text_spacing;
	}

	/**
	 * @return boolean
	 */
	public function isHeaderTextLine()
	{
		return $this->header_text_line;
	}

	/**
	 * @param boolean $header_text_line
	 */
	public function setHeaderTextLine($header_text_line)
	{
		$this->header_text_line = $header_text_line;
	}

	/**
	 * @return string
	 */
	public function getHeaderHtml()
	{
		return $this->header_html;
	}

	/**
	 * @param string $header_html
	 */
	public function setHeaderHtml($header_html)
	{
		$this->header_html = $header_html;
	}

	/**
	 * @return int
	 */
	public function getHeaderHtmlSpacing()
	{
		return $this->header_html_spacing;
	}

	/**
	 * @param int $header_html_spacing
	 */
	public function setHeaderHtmlSpacing($header_html_spacing)
	{
		$this->header_html_spacing = $header_html_spacing;
	}

	/**
	 * @return boolean
	 */
	public function isHeaderHtmlLine()
	{
		return $this->header_html_line;
	}

	/**
	 * @param boolean $header_html_line
	 */
	public function setHeaderHtmlLine($header_html_line)
	{
		$this->header_html_line = $header_html_line;
	}

	/**
	 * @return string
	 */
	protected function getFooterArgument()
	{
		$header_value  = $this->getFooterType();
		$header_string = '';
		if($header_value == ilPDFGenerationConstants::HEADER_TEXT)
		{
			$this->config[] = 'footer-left "'	.	$this->getFooterTextLeft()		. '"';
			$this->config[] = 'footer-center "'	.	$this->getFooterTextCenter()	. '"';
			$this->config[] = 'footer-right "'	.	$this->getFooterTextRight()		. '"';
			$this->config[] = 'footer-spacing '	.	$this->getFooterTextSpacing();
			if($this->isFooterTextLine())
			{
				$this->config[] = 'footer-line';
			}
		}
		else if($header_value == ilPDFGenerationConstants::HEADER_HTML)
		{
			$this->config[] = 'footer-html "'	.	$this->getFooterHtml() . '"';
			$this->config[] = 'footer-spacing '	.	$this->getFooterHtmlSpacing();
			if($this->isFooterHtmlLine())
			{
				$this->config[] = 'footer-line';
			}
		}
		return $header_string;
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
	public function getFooterTextLeft()
	{
		return $this->footer_text_left;
	}

	/**
	 * @param string $footer_text_left
	 */
	public function setFooterTextLeft($footer_text_left)
	{
		$this->footer_text_left = $footer_text_left;
	}

	/**
	 * @return string
	 */
	public function getFooterTextCenter()
	{
		return $this->footer_text_center;
	}

	/**
	 * @param string $footer_text_center
	 */
	public function setFooterTextCenter($footer_text_center)
	{
		$this->footer_text_center = $footer_text_center;
	}

	/**
	 * @return string
	 */
	public function getFooterTextRight()
	{
		return $this->footer_text_right;
	}

	/**
	 * @param string $footer_text_right
	 */
	public function setFooterTextRight($footer_text_right)
	{
		$this->footer_text_right = $footer_text_right;
	}

	/**
	 * @return int
	 */
	public function getFooterTextSpacing()
	{
		return $this->footer_text_spacing;
	}

	/**
	 * @param int $footer_text_spacing
	 */
	public function setFooterTextSpacing($footer_text_spacing)
	{
		$this->footer_text_spacing = $footer_text_spacing;
	}

	/**
	 * @return boolean
	 */
	public function isFooterTextLine()
	{
		return $this->footer_text_line;
	}

	/**
	 * @param boolean $footer_text_line
	 */
	public function setFooterTextLine($footer_text_line)
	{
		$this->footer_text_line = $footer_text_line;
	}

	/**
	 * @return string
	 */
	public function getFooterHtml()
	{
		return $this->footer_html;
	}

	/**
	 * @param string $footer_html
	 */
	public function setFooterHtml($footer_html)
	{
		$this->footer_html = $footer_html;
	}

	/**
	 * @return int
	 */
	public function getFooterHtmlSpacing()
	{
		return $this->footer_html_spacing;
	}

	/**
	 * @param int $footer_html_spacing
	 */
	public function setFooterHtmlSpacing($footer_html_spacing)
	{
		$this->footer_html_spacing = $footer_html_spacing;
	}

	/**
	 * @return boolean
	 */
	public function isFooterHtmlLine()
	{
		return $this->footer_html_line;
	}

	/**
	 * @param boolean $footer_html_line
	 */
	public function setFooterHtmlLine($footer_html_line)
	{
		$this->footer_html_line = $footer_html_line;
	}

	protected function getDebugArgument()
	{
		if(self::ENABLE_QUIET)
		{
			$this->config[] = 'quiet';
		}
	}

	protected function getCheckboxSvgArgument()
	{
		$checkbox_svg = $this->getCheckboxSvg();
		if($checkbox_svg != '')
		{
			$this->config[] = 'checkbox-svg "' . $checkbox_svg .'"';
		}
	}

	protected function getCheckboxCheckedSvgArgument()
	{
		$checkbox_svg = $this->getCheckboxCheckedSvg();
		if($checkbox_svg != '')
		{
			$this->config[] = 'checkbox-checked-svg "' . $checkbox_svg.'"';
		}
	}

	protected function getRadioButtonSvgArgument()
	{
		$radio_button_svg = $this->getRadioButtonSvg();
		if($radio_button_svg != '')
		{
			$this->config[] = 'radiobutton-svg "' . $radio_button_svg.'"';
		}
	}

	protected function getRadioButtonCheckedSvgArgument()
	{
		$radio_button_svg = $this->getRadioButtonCheckedSvg();
		if($radio_button_svg != '')
		{
			$this->config[] = 'radiobutton-checked-svg "' . $radio_button_svg.'"';
		}
	}

	/**
	 * @return string
	 */
	public function getCheckboxSvg()
	{
		return $this->checkbox_svg;
	}

	/**
	 * @param string $checkbox_svg
	 */
	public function setCheckboxSvg($checkbox_svg)
	{
		$this->checkbox_svg = $checkbox_svg;
	}


	/**
	 * @return string
	 */
	public function getRadioButtonSvg()
	{
		return $this->radio_button_svg;
	}

	/**
	 * @param string $radio_button_svg
	 */
	public function setRadioButtonSvg($radio_button_svg)
	{
		$this->radio_button_svg = $radio_button_svg;
	}

	/**
	 * @return string
	 */
	public function getRadioButtonCheckedSvg()
	{
		return $this->radio_button_checked_svg;
	}

	/**
	 * @param string $radio_button_checked_svg
	 */
	public function setRadioButtonCheckedSvg($radio_button_checked_svg)
	{
		$this->radio_button_checked_svg = $radio_button_checked_svg;
	}
	/**
	 * @return string
	 */
	public function getCheckboxCheckedSvg()
	{
		return $this->checkbox_checked_svg;
	}

	/**
	 * @param string $checkbox_checked_svg
	 */
	public function setCheckboxCheckedSvg($checkbox_checked_svg)
	{
		$this->checkbox_checked_svg = $checkbox_checked_svg;
	}

	/**
	 * @return string
	 */
	protected function getWKHTMLToPdfPath()
	{
		return $this->pdf_webkit_settings->get('path');
	}

	/**
	 * @return string
	 */
	public function getPathToTestHTML()
	{
		return 'Services/PDFGeneration/templates/default/test_complex.html';
	}

	/**
	 * @return string
	 */
	protected function redirectLog()
	{
		return	$redirect_log = ' 2>&1 ';
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
		$args			=  ' --version ' . $this->redirectLog();
		$return_value	= ilUtil::execQuoted($this->getWKHTMLToPdfPath(), $args);
		$log = $this->getWKHTMLToPdfPath() . ':';
		foreach($return_value as $key => $value)
		{
			$log .= ' ' . $value;
		}
		return $log;
	}
}