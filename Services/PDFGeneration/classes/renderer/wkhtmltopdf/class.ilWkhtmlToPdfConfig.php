<?php

class ilWkhtmlToPdfConfig
{
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
     * @var string
     */
    protected $path;
    /**
     * @var string
     */
    protected $overwrite_default_font = '';

    /**
     * ilWkhtmlToPdfConfig constructor.
     * @param null $config
     */
    public function __construct($config = null)
    {
        if ($config != null && !$config instanceof ilWkhtmlToPdfConfig) {
            $this->readConfigFromArray($config);
        } else {
            if ($config instanceof ilWkhtmlToPdfConfig) {
                $this->readConfigFromObject($config);
            } else {
                $this->useDefaultConfig();
            }
        }
    }

    /**
     * @param $config
     */
    protected function readConfigFromArray($config)
    {
        $this->setKeyIfExists('setZoom', 'zoom', $config);
        $this->setKeyIfExists('setEnabledForms', 'enable_forms', $config);
        $this->setKeyIfExists('setExternalLinks', 'external_links', $config);
        $this->setKeyIfExists('setUserStylesheet', 'user_stylesheet', $config);
        $this->setKeyIfExists('setLowQuality', 'low_quality', $config);
        $this->setKeyIfExists('setGreyscale', 'greyscale', $config);
        $this->setKeyIfExists('setOrientation', 'orientation', $config);
        $this->setKeyIfExists('setPageSize', 'page_size', $config);
        $this->setKeyIfExists('setMarginLeft', 'margin_left', $config);
        $this->setKeyIfExists('setMarginRight', 'margin_right', $config);
        $this->setKeyIfExists('setFooterHtmlSpacing', 'footer_html_spacing', $config);
        $this->setKeyIfExists('setFooterHtml', 'footer_html', $config);
        $this->setKeyIfExists('setFooterTextLine', 'footer_text_line', $config);
        $this->setKeyIfExists('setFooterTextCenter', 'footer_text_center', $config);
        $this->setKeyIfExists('setFooterTextCenter', 'footer_text_center', $config);
        $this->setKeyIfExists('setFooterTextSpacing', 'footer_text_spacing', $config);
        $this->setKeyIfExists('setFooterTextRight', 'footer_text_right', $config);
        $this->setKeyIfExists('setFooterTextLeft', 'footer_text_left', $config);
        $this->setKeyIfExists('setFooterType', 'footer_select', $config);
        $this->setKeyIfExists('setHeaderHtmlSpacing', 'head_html_spacing', $config);
        $this->setKeyIfExists('setHeaderHtmlLine', 'head_html_line', $config);
        $this->setKeyIfExists('setHeaderHtml', 'head_html', $config);
        $this->setKeyIfExists('setHeaderTextLine', 'head_text_line', $config);
        $this->setKeyIfExists('setHeaderTextSpacing', 'head_text_spacing', $config);
        $this->setKeyIfExists('setHeaderTextRight', 'head_text_right', $config);
        $this->setKeyIfExists('setHeaderTextCenter', 'head_text_center', $config);
        $this->setKeyIfExists('setHeaderTextLeft', 'head_text_left', $config);
        $this->setKeyIfExists('setHeaderType', 'header_select', $config);
        $this->setKeyIfExists('setRadioButtonCheckedSvg', 'radio_button_checked_svg', $config);
        $this->setKeyIfExists('setRadioButtonSvg', 'radio_button_svg', $config);
        $this->setKeyIfExists('setCheckboxCheckedSvg', 'checkbox_checked_svg', $config);
        $this->setKeyIfExists('setCheckboxSvg', 'checkbox_svg', $config);
        $this->setKeyIfExists('setJavascriptDelay', 'javascript_delay', $config);
        $this->setKeyIfExists('setPrintMediaType', 'print_media_type', $config);
        $this->setKeyIfExists('setMarginTop', 'margin_top', $config);
        $this->setKeyIfExists('setMarginBottom', 'margin_bottom', $config);
        $this->setKeyIfExists('setOverwriteDefaultFont', 'overwrite_font', $config);
    }

    /**
     * @param $function
     * @param $key
     * @param $config
     */
    protected function setKeyIfExists($function, $key, $config)
    {
        if (array_key_exists($key, $config)) {
            $this->{$function}($config[$key]);
        }
    }

    /**
     * @param ilWkhtmlToPdfConfig $config
     */
    protected function readConfigFromObject($config)
    {
        $this->setZoom($config->getZoom());
        $this->setEnabledForms($config->getEnabledForms());
        $this->setExternalLinks($config->getExternalLinks());
        $this->setUserStylesheet($config->getUserStylesheet());
        $this->setLowQuality($config->getLowQuality());
        $this->setGreyscale($config->getGreyscale());
        $this->setOrientation($config->getOrientation());
        $this->setPageSize($config->getPageSize());
        $this->setMarginLeft($config->getMarginLeft());
        $this->setMarginRight($config->getMarginRight());
        $this->setFooterTextLine($config->isFooterTextLine());
        $this->setFooterTextCenter($config->getFooterTextCenter());
        $this->setFooterTextSpacing($config->getFooterTextSpacing());
        $this->setFooterTextRight($config->getFooterTextRight());
        $this->setFooterTextLeft($config->getFooterTextLeft());
        $this->setFooterType($config->getFooterType());

        $this->setFooterHtmlSpacing($config->getFooterHtmlSpacing());
        $this->setFooterHtml($config->getFooterHtml());
        $this->setFooterHtmlLine($config->isFooterHtmlLine());
        $this->setHeaderHtmlSpacing($config->getHeaderHtmlSpacing());
        $this->setHeaderHtml($config->getHeaderHtml());
        $this->setHeaderHtmlLine($config->isHeaderHtmlLine());
        $this->setHeaderTextLine($config->isHeaderTextLine());
        $this->setHeaderTextSpacing($config->getHeaderTextSpacing());
        $this->setHeaderTextRight($config->getHeaderTextRight());
        $this->setHeaderTextCenter($config->getHeaderTextCenter());
        $this->setHeaderTextLeft($config->getHeaderTextLeft());
        $this->setHeaderType($config->getHeaderType());
        $this->setRadioButtonCheckedSvg($config->getRadioButtonCheckedSvg());
        $this->setRadioButtonSvg($config->getRadioButtonSvg());
        $this->setCheckboxCheckedSvg($config->getCheckboxCheckedSvg());
        $this->setCheckboxSvg($config->getCheckboxSvg());
        $this->setJavascriptDelay($config->getJavascriptDelay());
        $this->setPrintMediaType($config->getPrintMediaType());
        $this->setMarginTop($config->getMarginTop());
        $this->setMarginBottom($config->getMarginBottom());
        $this->setOverwriteDefaultFont($config->getOverwriteDefaultFont());
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
     * @param bool $renderStyle
     * @return string
     */
    public function getOverwriteDefaultFont($renderStyle = false)
    {
        if ($renderStyle) {
            if (strlen($this->overwrite_default_font) > 0) {
                return '<style>body{font-family: ' . $this->overwrite_default_font . ';}</style>';
            }
        } else {
            return $this->overwrite_default_font;
        }

        return '';
    }

    /**
     * @param string $overwrite_default_font
     */
    public function setOverwriteDefaultFont($overwrite_default_font)
    {
        $this->overwrite_default_font = $overwrite_default_font;
    }

    protected function useDefaultConfig()
    {
        $this->setExternalLinks(true);
        $this->setEnabledForms(false);
        $this->setJavascriptDelay(500);
        $this->setZoom(1);
        $this->setOrientation('Portrait');
        $this->setPageSize('A4');
        $this->setMarginLeft('0.5cm');
        $this->setMarginRight('2cm');
        $this->setMarginBottom('0.5cm');
        $this->setMarginTop('2cm');
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
    public function getPath()
    {
        return $this->path;
    }

    /**
     * @param string $path
     */
    public function setPath($path)
    {
        $this->path = $path;
    }

    /**
     * @return string
     */
    public function getWKHTMLToPdfDefaultPath()
    {
        $path = $this->getSavedDefaultBinaryPath();
        if($path !== ''){
            return $path;
        }
        return '/usr/local/bin/wkhtmltopdf';
    }

    public function getConfig()
    {
        return $this->config;
    }

    /**
     * @return string
     */
    public function getCommandLineConfig()
    {
        $this->generateCommandLineConfig();
        $settings = ' ';
        foreach ($this->config as $value) {
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
        $this->getSessionObject();
    }

    protected function getZoomArgument()
    {
        if ($this->getZoom() != '') {
            $this->config[] = 'zoom ' . $this->getZoom();
        }
    }

    protected function getExternalLinksArgument()
    {
        if ($this->getExternalLinks()) {
            $this->config[] = 'enable-external-links';
        } else {
            $this->config[] = 'disable-external-links';
        }
    }

    protected function getEnabledFormsArgument()
    {
        if ($this->getEnabledForms()) {
            $this->config[] = 'enable-forms';
        } else {
            $this->config[] = 'disable-forms';
        }
    }

    protected function getUserStylesheetArgument()
    {
        $stylesheet = $this->getUserStylesheet();
        if ($stylesheet != '') {
            $this->config[] = 'user-style-sheet "' . $stylesheet . '"';
        }
    }

    protected function getGreyscaleArgument()
    {
        if ($this->getGreyscale()) {
            $this->config[] = 'grayscale';
        }
    }

    protected function getLowQualityArgument()
    {
        if ($this->getLowQuality() == 1 || $this->getLowQuality() == true) {
            $this->config[] = 'lowquality';
        }
    }

    protected function getOrientationArgument()
    {
        $orientation = $this->getOrientation();
        if ($orientation == '' || $orientation == 'Portrait') {
            $this->config[] = 'orientation Portrait';
        } else {
            $this->config[] = 'orientation Landscape';
        }
    }

    protected function getPrintMediaTypeArgument()
    {
        if ($this->getPrintMediaType() == 1) {
            $this->config[] = 'print-media-type';
        }
    }

    protected function getPageSizeArgument()
    {
        if ($this->getPageSize() != '') {
            $this->config[] = 'page-size ' . $this->getPageSize();
        }
    }

    protected function getJavascriptDelayArgument()
    {
        if ($this->getJavascriptDelay() != '') {
            $this->config[] = 'javascript-delay ' . $this->getJavascriptDelay();
        }
    }

    protected function getCheckboxSvgArgument()
    {
        $checkbox_svg = $this->getCheckboxSvg();
        if ($checkbox_svg != '') {
            $this->config[] = 'checkbox-svg "' . $checkbox_svg . '"';
        }
    }

    protected function getCheckboxCheckedSvgArgument()
    {
        $checkbox_svg = $this->getCheckboxCheckedSvg();
        if ($checkbox_svg != '') {
            $this->config[] = 'checkbox-checked-svg "' . $checkbox_svg . '"';
        }
    }

    protected function getRadioButtonSvgArgument()
    {
        $radio_button_svg = $this->getRadioButtonSvg();
        if ($radio_button_svg != '') {
            $this->config[] = 'radiobutton-svg "' . $radio_button_svg . '"';
        }
    }

    protected function getRadioButtonCheckedSvgArgument()
    {
        $radio_button_svg = $this->getRadioButtonCheckedSvg();
        if ($radio_button_svg != '') {
            $this->config[] = 'radiobutton-checked-svg "' . $radio_button_svg . '"';
        }
    }

    protected function getMarginArgument()
    {
        if ($this->getMarginBottom() != '') {
            $this->config[] = 'margin-bottom ' . $this->getMarginBottom();
        }
        if ($this->getMarginLeft() != '') {
            $this->config[] = 'margin-left ' . $this->getMarginLeft();
        }
        if ($this->getMarginRight() != '') {
            $this->config[] = 'margin-right ' . $this->getMarginRight();
        }
        if ($this->getMarginTop() != '') {
            $this->config[] = 'margin-top ' . $this->getMarginTop();
        }
    }

    protected function getHeaderArgument()
    {
        $header_value = $this->getHeaderType();
        if ($header_value == ilPDFGenerationConstants::HEADER_TEXT) {
            $this->config[] = 'header-left "' . $this->getHeaderTextLeft() . '"';
            $this->config[] = 'header-center "' . $this->getHeaderTextCenter() . '"';
            $this->config[] = 'header-right "' . $this->getHeaderTextRight() . '"';
            if ($this->getHeaderTextSpacing() != '') {
                $this->config[] = 'header-spacing ' . $this->getHeaderTextSpacing();
            }

            if ($this->isHeaderTextLine()) {
                $this->config[] = 'header-line';
            }
        } else {
            if ($header_value == ilPDFGenerationConstants::HEADER_HTML) {
                $this->config[] = 'header-html "' . $this->getHeaderHtml() . '"';

                if ($this->getHeaderHtmlSpacing() != '') {
                    $this->config[] = 'header-spacing ' . $this->getHeaderHtmlSpacing();
                }
                if ($this->isHeaderHtmlLine()) {
                    $this->config[] = 'header-line';
                }
            }
        }
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

    protected function getFooterArgument()
    {
        $footer_value = $this->getFooterType();
        if ($footer_value == ilPDFGenerationConstants::FOOTER_TEXT) {
            $this->config[] = 'footer-left "' . $this->getFooterTextLeft() . '"';
            $this->config[] = 'footer-center "' . $this->getFooterTextCenter() . '"';
            $this->config[] = 'footer-right "' . $this->getFooterTextRight() . '"';
            if ($this->getFooterTextSpacing() != '') {
                $this->config[] = 'footer-spacing ' . $this->getFooterTextSpacing();
            }

            if ($this->isFooterTextLine()) {
                $this->config[] = 'footer-line';
            }
        } else {
            if ($footer_value == ilPDFGenerationConstants::FOOTER_HTML) {
                $this->config[] = 'footer-html "' . $this->getFooterHtml() . '"';

                if ($this->getFooterHtmlSpacing() != '') {
                    $this->config[] = 'footer-spacing ' . $this->getFooterHtmlSpacing();
                }
                if ($this->isFooterHtmlLine()) {
                    $this->config[] = 'footer-line';
                }
            }
        }
    }

    protected function getDebugArgument()
    {
        if (self::ENABLE_QUIET) {
            $this->config[] = 'quiet';
        }
    }

    protected function getSessionObject()
    {
        $this->config[] = 'cookie "PHPSESSID" "' . session_id() . '"';
        $this->config[] = 'cookie "ilClientId" "' . CLIENT_ID . '"';
    }

    /**
     * @return string
     */
    protected function getSavedDefaultBinaryPath(){
        $settings = new ilSetting('wkhtmltopdfrenderer');
        $path = $settings->get('path');
        if( ! is_bool($path) && $path != null && $path != ''){
            return $path;
        }
        return '';
    }
}
