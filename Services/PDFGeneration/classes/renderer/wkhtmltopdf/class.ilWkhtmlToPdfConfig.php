<?php declare(strict_types=1);

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 *
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 *
 *********************************************************************/

class ilWkhtmlToPdfConfig
{
    private const ENABLE_QUIET = true;

    protected bool $phpunit = false;
    protected array $config = [];
    protected float $zoom = 1.0;
    protected bool $external_links = false;
    protected bool $enabled_forms = false;
    protected string $user_stylesheet = '';
    protected bool $greyscale = false;
    protected bool $low_quality = false;
    protected string $orientation = 'Portrait';
    protected bool $print_media_type = false;
    protected string $page_size = 'A4';
    protected int $javascript_delay = 100;
    protected string $margin_left = '';
    protected string $margin_right = '';
    protected string $margin_top = '';
    protected string $margin_bottom = '';
    protected int $header_type = 0;
    protected string $header_text_left = '';
    protected string $header_text_center = '';
    protected string $header_text_right = '';
    protected int $header_text_spacing = 0;
    protected bool $header_text_line = false;
    protected string $header_html = '';
    protected int $header_html_spacing = 0;
    protected bool $header_html_line = false;
    protected int $footer_type = 0;
    protected string $footer_text_left = '';
    protected string $footer_text_center = '';
    protected string $footer_text_right = '';
    protected int $footer_text_spacing = 0;
    protected bool $footer_text_line = false;
    protected string $footer_html = '';
    protected int $footer_html_spacing = 0;
    protected bool $footer_html_line = false;
    protected string $checkbox_svg = '';
    protected string $checkbox_checked_svg = '';
    protected string $radio_button_svg = '';
    protected string $radio_button_checked_svg = '';
    protected string $path = '';
    protected string $overwrite_default_font = 'arial';

    /**
     * @param array|null|self $config
     */
    public function __construct($config = null)
    {
        if (is_array($config)) {
            $this->readConfigFromArray($config);
        } elseif ($config instanceof self) {
            $this->readConfigFromObject($config);
        } else {
            $this->useDefaultConfig();
        }
    }

    protected function readConfigFromArray(array $config) : void
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
     * @param string $function
     * @param string $key
     * @param array<string, mixed> $config
     * @return void
     */
    protected function setKeyIfExists(string $function, string $key, array $config) : void
    {
        if (array_key_exists($key, $config)) {
            $value = $config[$key];

            if (is_scalar($value)) {
                $reflMethod = new ReflectionMethod($this, $function);
                $type = $reflMethod->getParameters()[0]->getType();
                if ($type instanceof ReflectionNamedType && $type->isBuiltin()) {
                    settype($value, $type->getName());
                }
            }
            if($value != null) {
                $this->{$function}($value);
            }
        }
    }

    protected function readConfigFromObject(ilWkhtmlToPdfConfig $config) : void
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

    public function getZoom() : float
    {
        return $this->zoom;
    }

    public function setZoom(float $zoom) : void
    {
        $this->zoom = $zoom;
    }

    public function getEnabledForms() : bool
    {
        return $this->enabled_forms;
    }

    public function setEnabledForms(?bool $enabled_forms) : void
    {
        $this->enabled_forms = $enabled_forms;
    }

    public function getExternalLinks() : bool
    {
        return $this->external_links;
    }

    public function setExternalLinks(bool $external_links) : void
    {
        $this->external_links = $external_links;
    }

    public function getUserStylesheet() : string
    {
        return $this->user_stylesheet;
    }

    public function setUserStylesheet(string $user_stylesheet) : void
    {
        $this->user_stylesheet = $user_stylesheet;
    }

    public function getLowQuality() : bool
    {
        return $this->low_quality;
    }

    public function setLowQuality(bool $low_quality) : void
    {
        $this->low_quality = $low_quality;
    }

    public function getGreyscale() : bool
    {
        return $this->greyscale;
    }

    public function setGreyscale(bool $greyscale) : void
    {
        $this->greyscale = $greyscale;
    }

    public function getOrientation() : string
    {
        return $this->orientation;
    }

    public function setOrientation(string $orientation) : void
    {
        $this->orientation = $orientation;
    }

    public function getPageSize() : string
    {
        return $this->page_size;
    }

    public function setPageSize(string $page_size) : void
    {
        $this->page_size = $page_size;
    }

    public function getMarginLeft() : string
    {
        return $this->margin_left;
    }

    public function setMarginLeft(string $margin_left) : void
    {
        $this->margin_left = $margin_left;
    }

    public function getMarginRight() : string
    {
        return $this->margin_right;
    }

    public function setMarginRight(string $margin_right) : void
    {
        $this->margin_right = $margin_right;
    }

    public function getFooterHtmlSpacing() : int
    {
        return $this->footer_html_spacing;
    }

    public function setFooterHtmlSpacing(int $footer_html_spacing) : void
    {
        $this->footer_html_spacing = $footer_html_spacing;
    }

    public function getFooterHtml() : string
    {
        return $this->footer_html;
    }

    public function setFooterHtml(string $footer_html) : void
    {
        $this->footer_html = $footer_html;
    }

    public function isFooterTextLine() : bool
    {
        return $this->footer_text_line;
    }

    public function setFooterTextLine(bool $footer_text_line) : void
    {
        $this->footer_text_line = $footer_text_line;
    }

    public function getFooterTextCenter() : string
    {
        return $this->footer_text_center;
    }

    public function setFooterTextCenter(string $footer_text_center) : void
    {
        $this->footer_text_center = $footer_text_center;
    }

    public function getFooterTextSpacing() : int
    {
        return $this->footer_text_spacing;
    }

    public function setFooterTextSpacing(int $footer_text_spacing) : void
    {
        $this->footer_text_spacing = $footer_text_spacing;
    }

    public function getFooterTextRight() : string
    {
        return $this->footer_text_right;
    }

    public function setFooterTextRight(string $footer_text_right) : void
    {
        $this->footer_text_right = $footer_text_right;
    }

    public function getFooterTextLeft() : string
    {
        return $this->footer_text_left;
    }

    public function setFooterTextLeft(string $footer_text_left) : void
    {
        $this->footer_text_left = $footer_text_left;
    }

    public function getFooterType() : int
    {
        return $this->footer_type;
    }

    public function setFooterType(int $footer_type) : void
    {
        $this->footer_type = $footer_type;
    }

    public function isFooterHtmlLine() : bool
    {
        return $this->footer_html_line;
    }

    public function setFooterHtmlLine(bool $footer_html_line) : void
    {
        $this->footer_html_line = $footer_html_line;
    }

    public function isHeaderTextLine() : bool
    {
        return $this->header_text_line;
    }

    public function setHeaderTextLine(bool $header_text_line) : void
    {
        $this->header_text_line = $header_text_line;
    }

    public function getHeaderTextSpacing() : int
    {
        return $this->header_text_spacing;
    }

    public function setHeaderTextSpacing(int $header_text_spacing) : void
    {
        $this->header_text_spacing = $header_text_spacing;
    }

    public function getHeaderTextRight() : string
    {
        return $this->header_text_right;
    }

    public function setHeaderTextRight(string $header_text_right) : void
    {
        $this->header_text_right = $header_text_right;
    }

    public function getHeaderTextCenter() : string
    {
        return $this->header_text_center;
    }

    public function setHeaderTextCenter(string $header_text_center) : void
    {
        $this->header_text_center = $header_text_center;
    }

    public function getHeaderTextLeft() : string
    {
        return $this->header_text_left;
    }

    public function setHeaderTextLeft(string $header_text_left) : void
    {
        $this->header_text_left = $header_text_left;
    }

    public function getHeaderType() : int
    {
        return $this->header_type;
    }

    public function setHeaderType(int $header_type) : void
    {
        $this->header_type = $header_type;
    }

    public function getRadioButtonCheckedSvg() : string
    {
        return $this->radio_button_checked_svg;
    }

    public function setRadioButtonCheckedSvg(string $radio_button_checked_svg) : void
    {
        $this->radio_button_checked_svg = $radio_button_checked_svg;
    }

    public function getRadioButtonSvg() : string
    {
        return $this->radio_button_svg;
    }

    public function setRadioButtonSvg(string $radio_button_svg) : void
    {
        $this->radio_button_svg = $radio_button_svg;
    }

    public function getCheckboxCheckedSvg() : string
    {
        return $this->checkbox_checked_svg;
    }

    public function setCheckboxCheckedSvg(string $checkbox_checked_svg) : void
    {
        $this->checkbox_checked_svg = $checkbox_checked_svg;
    }

    public function getCheckboxSvg() : string
    {
        return $this->checkbox_svg;
    }

    public function setCheckboxSvg(string $checkbox_svg) : void
    {
        $this->checkbox_svg = $checkbox_svg;
    }

    public function getJavascriptDelay() : int
    {
        return $this->javascript_delay;
    }

    public function setJavascriptDelay(int $javascript_delay) : void
    {
        $this->javascript_delay = $javascript_delay;
    }

    public function getPrintMediaType() : bool
    {
        return $this->print_media_type;
    }

    public function setPrintMediaType(bool $print_media_type) : void
    {
        $this->print_media_type = $print_media_type;
    }

    public function getMarginTop() : string
    {
        return $this->margin_top;
    }

    public function setMarginTop(string $margin_top) : void
    {
        $this->margin_top = $margin_top;
    }

    public function getMarginBottom() : string
    {
        return $this->margin_bottom;
    }

    public function setMarginBottom(string $margin_bottom) : void
    {
        $this->margin_bottom = $margin_bottom;
    }

    public function getOverwriteDefaultFont(bool $renderStyle = false) : string
    {
        if ($renderStyle) {
            if ($this->overwrite_default_font !== '') {
                return '<style>body{font-family: ' . $this->overwrite_default_font . ';}</style>';
            }
            
            return '';
        }
        if($this->overwrite_default_font === '') {
            return 'arial';
        }

        return $this->overwrite_default_font;
    }

    public function setOverwriteDefaultFont(string $overwrite_default_font) : void
    {
        $this->overwrite_default_font = $overwrite_default_font;
    }

    protected function useDefaultConfig() : void
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

    public static function supportMultiSourcesFiles() : bool
    {
        return true;
    }

    public function getPath() : string
    {
        return $this->path;
    }

    public function setPath(string $path) : void
    {
        $this->path = $path;
    }

    public function getWKHTMLToPdfDefaultPath() : string
    {
        $path = $this->getSavedDefaultBinaryPath();
        if ($path !== '') {
            return $path;
        }

        return '/usr/local/bin/wkhtmltopdf';
    }
    
    public function getConfig() : array
    {
        return $this->config;
    }

    public function getCommandLineConfig() : string
    {
        $this->generateCommandLineConfig();

        $settings = ' ';
        foreach ($this->config as $value) {
            $settings .= '--' . $value . ' ';
        }

        return $settings;
    }

    protected function generateCommandLineConfig() : void
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

    protected function getZoomArgument() : void
    {
        if ($this->getZoom()) {
            $this->config[] = 'zoom ' . $this->getZoom();
        }
    }

    protected function getExternalLinksArgument() : void
    {
        if ($this->getExternalLinks()) {
            $this->config[] = 'enable-external-links';
        } else {
            $this->config[] = 'disable-external-links';
        }
    }

    protected function getEnabledFormsArgument() : void
    {
        if ($this->getEnabledForms()) {
            $this->config[] = 'enable-forms';
        } else {
            $this->config[] = 'disable-forms';
        }
    }

    protected function getUserStylesheetArgument() : void
    {
        $stylesheet = $this->getUserStylesheet();
        if ($stylesheet !== '') {
            $this->config[] = 'user-style-sheet "' . $stylesheet . '"';
        }
    }

    protected function getGreyscaleArgument() : void
    {
        if ($this->getGreyscale()) {
            $this->config[] = 'grayscale';
        }
    }

    protected function getLowQualityArgument() : void
    {
        if ($this->getLowQuality()) {
            $this->config[] = 'lowquality';
        }
    }

    protected function getOrientationArgument() : void
    {
        $orientation = $this->getOrientation();
        if ($orientation === '' || $orientation === 'Portrait') {
            $this->config[] = 'orientation Portrait';
        } else {
            $this->config[] = 'orientation Landscape';
        }
    }

    protected function getPrintMediaTypeArgument() : void
    {
        if ($this->getPrintMediaType()) {
            $this->config[] = 'print-media-type';
        }
    }

    protected function getPageSizeArgument() : void
    {
        if ($this->getPageSize() !== '') {
            $this->config[] = 'page-size ' . $this->getPageSize();
        }
    }

    protected function getJavascriptDelayArgument() : void
    {
        if ($this->getJavascriptDelay() > 0) {
            $this->config[] = 'javascript-delay ' . $this->getJavascriptDelay();
        }
    }

    protected function getCheckboxSvgArgument() : void
    {
        $checkbox_svg = $this->getCheckboxSvg();
        if ($checkbox_svg !== '') {
            $this->config[] = 'checkbox-svg "' . $checkbox_svg . '"';
        }
    }

    protected function getCheckboxCheckedSvgArgument() : void
    {
        $checkbox_svg = $this->getCheckboxCheckedSvg();
        if ($checkbox_svg !== '') {
            $this->config[] = 'checkbox-checked-svg "' . $checkbox_svg . '"';
        }
    }

    protected function getRadioButtonSvgArgument() : void
    {
        $radio_button_svg = $this->getRadioButtonSvg();
        if ($radio_button_svg !== '') {
            $this->config[] = 'radiobutton-svg "' . $radio_button_svg . '"';
        }
    }

    protected function getRadioButtonCheckedSvgArgument() : void
    {
        $radio_button_svg = $this->getRadioButtonCheckedSvg();
        if ($radio_button_svg !== '') {
            $this->config[] = 'radiobutton-checked-svg "' . $radio_button_svg . '"';
        }
    }

    protected function getMarginArgument() : void
    {
        if ($this->getMarginBottom() !== '') {
            $this->config[] = 'margin-bottom ' . $this->getMarginBottom();
        }
        if ($this->getMarginLeft() !== '') {
            $this->config[] = 'margin-left ' . $this->getMarginLeft();
        }
        if ($this->getMarginRight() !== '') {
            $this->config[] = 'margin-right ' . $this->getMarginRight();
        }
        if ($this->getMarginTop() !== '') {
            $this->config[] = 'margin-top ' . $this->getMarginTop();
        }
    }

    protected function getHeaderArgument() : void
    {
        $header_value = $this->getHeaderType();
        if ($header_value === ilPDFGenerationConstants::HEADER_TEXT) {
            $this->config[] = 'header-left "' . $this->getHeaderTextLeft() . '"';
            $this->config[] = 'header-center "' . $this->getHeaderTextCenter() . '"';
            $this->config[] = 'header-right "' . $this->getHeaderTextRight() . '"';
            if ($this->getHeaderTextSpacing() > 0) {
                $this->config[] = 'header-spacing ' . $this->getHeaderTextSpacing();
            }

            if ($this->isHeaderTextLine()) {
                $this->config[] = 'header-line';
            }
        } elseif ($header_value === ilPDFGenerationConstants::HEADER_HTML) {
            $this->config[] = 'header-html "' . $this->getHeaderHtml() . '"';

            if ($this->getHeaderHtmlSpacing() > 0) {
                $this->config[] = 'header-spacing ' . $this->getHeaderHtmlSpacing();
            }
            if ($this->isHeaderHtmlLine()) {
                $this->config[] = 'header-line';
            }
        }
    }

    public function getHeaderHtml() : string
    {
        return $this->header_html;
    }

    public function setHeaderHtml(string $header_html) : void
    {
        $this->header_html = $header_html;
    }

    public function getHeaderHtmlSpacing() : int
    {
        return $this->header_html_spacing;
    }

    public function setHeaderHtmlSpacing(int $header_html_spacing) : void
    {
        $this->header_html_spacing = $header_html_spacing;
    }

    public function isHeaderHtmlLine() : bool
    {
        return $this->header_html_line;
    }

    public function setHeaderHtmlLine(bool $header_html_line) : void
    {
        $this->header_html_line = $header_html_line;
    }

    protected function getFooterArgument() : void
    {
        $footer_value = $this->getFooterType();
        if ($footer_value === ilPDFGenerationConstants::FOOTER_TEXT) {
            $this->config[] = 'footer-left "' . $this->getFooterTextLeft() . '"';
            $this->config[] = 'footer-center "' . $this->getFooterTextCenter() . '"';
            $this->config[] = 'footer-right "' . $this->getFooterTextRight() . '"';
            if ($this->getFooterTextSpacing() > 0) {
                $this->config[] = 'footer-spacing ' . $this->getFooterTextSpacing();
            }

            if ($this->isFooterTextLine()) {
                $this->config[] = 'footer-line';
            }
        } elseif ($footer_value === ilPDFGenerationConstants::FOOTER_HTML) {
            $this->config[] = 'footer-html "' . $this->getFooterHtml() . '"';

            if ($this->getFooterHtmlSpacing() > 0) {
                $this->config[] = 'footer-spacing ' . $this->getFooterHtmlSpacing();
            }
            if ($this->isFooterHtmlLine()) {
                $this->config[] = 'footer-line';
            }
        }
    }

    protected function getDebugArgument() : void
    {
        if (self::ENABLE_QUIET) {
            $this->config[] = 'quiet';
        }
    }

    protected function getSessionObject() : void
    {
        $this->config[] = 'cookie "PHPSESSID" "' . session_id() . '"';
        if (defined('CLIENT_ID')) {
            $this->config[] = 'cookie "ilClientId" "' . CLIENT_ID . '"';
        }
    }

    protected function getSavedDefaultBinaryPath() : string
    {
        $settings = new ilSetting('wkhtmltopdfrenderer');
        $path = $settings->get('path');
        if ($path !== null && $path !== '') {
            return $path;
        }

        return '';
    }
}
