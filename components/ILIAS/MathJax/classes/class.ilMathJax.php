<?php

declare(strict_types=1);

/**
 * This file is part of ILIAS, a powerful learning management system
 * published by ILIAS open source e-Learning e.V.
 * ILIAS is licensed with the GPL-3.0,
 * see https://www.gnu.org/licenses/gpl-3.0.en.html
 * You should have received a copy of said license along with the
 * source code, too.
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 * https://www.ilias.de
 * https://github.com/ILIAS-eLearning
 */

use ILIAS\DI\Container;

/**
 * Class for processing of latex formulas
 * This class uses a sigleton pattern to store the rendering purpose during a request.
 * The rendering purpose for export or deferred PDF generation must be determined at the beginning of a request.
 * All following calls to convert latex code must use this purpose.
 * Use init() to reset the purpose and all related settings.
 */
class ilMathJax
{
    public const PURPOSE_BROWSER = 'browser';                    // direct display of page in the browser
    public const PURPOSE_EXPORT = 'export';                        // html export of contents
    public const PURPOSE_PDF = 'pdf';                            // server-side PDF generation (only TCPDF and XSL-FO, not PhantomJS!!!)
    public const PURPOSE_DEFERRED_PDF = 'deferred_pdf';            // defer rendering for server-side pdf generation (XSL-FO)
    // this needs a second call with PURPOSE_PDF at the end

    public const ENGINE_SERVER = 'server';                        // code is treated by one of the rendering modes below
    public const ENGINE_CLIENT = 'client';                        // code delimiters are
    public const ENGINE_DEFERRED = 'deferred';                    // protect code for a deferred rendering
    public const ENGINE_NONE = 'none';                            // don't render the code, just show it

    public const RENDER_SVG_AS_XML_EMBED = 'svg_as_xml_embed';    // embed svg code directly in html (default for browser view)
    public const RENDER_SVG_AS_IMG_EMBED = 'svg_as_img_embed';  // embed svg base64 encoded in an img tag (default for HTML export)
    public const RENDER_PNG_AS_IMG_EMBED = 'png_as_img_embed';  // embed png base64 encoded in an img tag (default for PDF generation)
    public const RENDER_PNG_AS_FO_FILE = 'png_as_fo_file';        // refer to a png file from an fo tag (for PDF generation with XSL-FO)

    protected const OUTPUT_SVG = 'svg';                         // svg output format for server side rendering
    protected const OUTPUT_PNG = 'png';                         // png output format for server side rendering

    protected const DEFAULT_DPI = 150;                          // default DIP of rendered images
    protected const DEFAULT_ZOOM = 1.0;                         // default zoom factor of included images

    /**
     * @var ilMathJax Singleton instance
     */
    protected static ?self $_instance;

    /**
     * @var ilMathJaxConfig Stored configuration
     */
    protected ilMathJaxConfig $config;

    /**
     * @var ilMathJaxFactory Factory for image, server or global template
     */
    protected ilMathJaxFactory $factory;

    /**
     * @var string|null Chosen rendering engine
     */
    protected ?string $engine;

    /**
     * @var string    Chosen rendering mode
     */
    protected string $rendering = self::RENDER_SVG_AS_XML_EMBED;

    /**
     * @var string Output format of the server side rendering
     */
    protected string $output = self::OUTPUT_SVG;

    /**
     * @var int DPI of rasterized image
     */
    protected int $dpi;

    /**
     * @var float Zoom factor of included images
     */
    protected float $zoom_factor;

    /**
     * @var array Default options for calling the MathJax server
     */
    protected array $default_server_options = array(
        "format" => "TeX",
        "math" => '',        // TeX code
        "svg" => true,
        "mml" => false,
        "png" => false,
        "speakText" => false,
        "speakRuleset" => "mathspeak",
        "speakStyle" => "default",
        "ex" => 6,
        "width" => 1000000,
        "linebreaks" => false,
    );

    /**
     * Protected constructor to force the use of an initialized instance
     */
    protected function __construct(ilMathJaxConfig $config, ilMathJaxFactory $factory)
    {
        $this->config = $config;
        $this->factory = $factory;
        $this->init(self::PURPOSE_BROWSER);
    }

    /**
     * Singleton: get instance for use in ILIAS
     */
    public static function getInstance(): ilMathJax
    {
        /** @var Container $DIC */
        global $DIC;

        if (!isset(self::$_instance)) {
            $repo = new ilMathJaxConfigSettingsRepository(new ilSettingsFactory($DIC->database()));
            self::$_instance = new self($repo->getConfig(), new ilMathJaxFactory());
        }
        return self::$_instance;
    }

    /**
     * Get an independent instance with a specific config
     * for use in unit tests or on the mathjax settings page
     * Don't use in standard cases!
     */
    public static function getIndependent(ilMathJaxConfig $config, ilMathJaxFactory $factory): ilMathJax
    {
        return new self($config, $factory);
    }

    /**
     * Initialize the usage for a certain purpose
     * This must be done before any rendering call
     */
    public function init(string $a_purpose = self::PURPOSE_BROWSER): ilMathJax
    {
        // reset the class variables
        $this->engine = null;
        $this->rendering = self::RENDER_SVG_AS_XML_EMBED;
        $this->output = self::OUTPUT_SVG;
        $this->dpi = self::DEFAULT_DPI;
        $this->zoom_factor = self::DEFAULT_ZOOM;

        // try the server-side rendering first, set this engine, if possible
        if ($this->config->isServerEnabled()) {
            if ($a_purpose === self::PURPOSE_BROWSER && $this->config->isServerForBrowser()) {
                // delivering svg directly in page may be faster than loading image files
                $this->setEngine(self::ENGINE_SERVER);
                $this->setRendering(self::RENDER_SVG_AS_XML_EMBED);
            } elseif ($a_purpose === self::PURPOSE_EXPORT && $this->config->isServerForExport()) {
                // offline pages must always embed the svg as image tags
                // otherwise the html base tag may conflict with references in svg
                $this->setEngine(self::ENGINE_SERVER);
                $this->setRendering(self::RENDER_SVG_AS_IMG_EMBED);
            } elseif ($a_purpose === self::PURPOSE_PDF && $this->config->isServerForPdf()) {
                // embedded png should work in most pdf engines
                // details can be set by the rendering engine
                $this->setEngine(self::ENGINE_SERVER);
                $this->setRendering(self::RENDER_PNG_AS_IMG_EMBED);
            } elseif ($a_purpose === self::PURPOSE_DEFERRED_PDF && $this->config->isServerForPdf()) {
                // final engine and rendering is set before the pdf is created
                $this->setEngine(self::ENGINE_DEFERRED);
            }
        }

        // support client-side rendering if enabled
        if ($this->config->isClientEnabled()) {
            // included mathjax script may render code which is not found by the server-side rendering
            // see https://docu.ilias.de/goto_docu_wiki_wpage_5614_1357.html
            $this->includeMathJax();

            // set engine for client-side rendering, if server is not used for the purpose
            if (!isset($this->engine)) {
                $this->setEngine(self::ENGINE_CLIENT);
            }
        }

        // no engine available or configured
        if (!isset($this->engine)) {
            $this->engine = self::ENGINE_NONE;
        }

        return $this;
    }

    /**
     * Set the Rendering engine
     */
    protected function setEngine(string $a_engine): ilMathJax
    {
        switch ($a_engine) {
            case self::ENGINE_CLIENT:
            case self::ENGINE_SERVER:
            case self::ENGINE_DEFERRED:
                $this->engine = $a_engine;
                break;
            default:
                $this->engine = self::ENGINE_NONE;
        }

        return $this;
    }

    /**
     * Set the image type rendered by the server
     */
    public function setRendering(string $a_rendering): ilMathJax
    {
        switch ($a_rendering) {
            case self::RENDER_SVG_AS_XML_EMBED:
            case self::RENDER_SVG_AS_IMG_EMBED:
                $this->rendering = $a_rendering;
                $this->output = self::OUTPUT_SVG;
                break;

            case self::RENDER_PNG_AS_IMG_EMBED:
            case self::RENDER_PNG_AS_FO_FILE:
                $this->rendering = $a_rendering;
                $this->output = self::OUTPUT_PNG;
                break;
        }
        return $this;
    }

    /**
     * Set the dpi of the rendered images
     */
    public function setDpi(int $a_dpi): ilMathJax
    {
        $this->dpi = $a_dpi;
        return $this;
    }

    /**
     * Set the zoom factor of the rendered images
     */
    public function setZoomFactor(float $a_factor): ilMathJax
    {
        $this->zoom_factor = $a_factor;
        return $this;
    }

    /**
     * Include the Mathjax javascript(s) in the page template
     */
    public function includeMathJax(ilGlobalTemplateInterface $a_tpl = null): ilMathJax
    {
        if ($this->config->isClientEnabled()) {
            $tpl = $a_tpl ?? $this->factory->template();

            if (!empty($this->config->getClintPolyfillUrl())) {
                $tpl->addJavaScript($this->config->getClintPolyfillUrl());
            }
            if (!empty($this->config->getClientScriptUrl())) {
                $tpl->addJavaScript($this->config->getClientScriptUrl());
            }
        }

        return $this;
    }

    /**
     * Replace all tex code within given start and end delimiters in a text
     * If client-side rendering is enabled, change the start end end delimiters to what Mathjax expects
     * If Server-side rendering is used, replace the whole expression with delimiters by svg or image
     * @param string      $a_text  text to be converted
     * @param string|null $a_start start delimiter to be searched for
     * @param string|null $a_end   end delimiter to be converted
     * @return string    replaced text
     */
    public function insertLatexImages(string $a_text, ?string $a_start = '[tex]', ?string $a_end = '[/tex]'): string
    {
        // don't change anything if mathjax is not configured
        if ($this->engine === self::ENGINE_NONE) {
            return $a_text;
        }

        // this is a fix for bug5362
        $a_start = str_replace("\\", "", $a_start ?? '[tex]');
        $a_end = str_replace("\\", "", $a_end ?? '[/tex]');

        // current position to start the search for delimiters
        $cpos = 0;
        // find position of start delimiter
        while (is_int($spos = ilStr::strIPos($a_text, $a_start, $cpos))) {
            // find position of end delimiter
            if (is_int($epos = ilStr::strIPos($a_text, $a_end, $spos + ilStr::strLen($a_start)))) {
                // extract the tex code inside the delimiters
                $tex = ilStr::subStr($a_text, $spos + ilStr::strLen($a_start), $epos - $spos - ilStr::strLen($a_start));

                // undo a code protection done by the deferred engine before
                if (ilStr::subStr($tex, 0, 7) === 'base64:') {
                    $tex = base64_decode(substr($tex, 7));
                }

                // omit the html newlines added by the ILIAS page editor
                $tex = str_replace(array('<br>', '<br/>', '<br />'), '', $tex);

                // tex specific replacements
                $tex = preg_replace("/\\\\([RZN])([^a-zA-Z])/", "\\mathbb{" . "$1" . "}" . "$2", $tex);

                // check, if tags go across div borders
                if (is_int(ilStr::strIPos($tex, '<div>')) || is_int(ilStr::strIPos($tex, '</div>'))) {
                    // keep the original code including delimiters, continue search behind
                    $cpos = $epos + ilStr::strLen($a_end);
                } else {
                    switch ($this->engine) {
                        case self::ENGINE_CLIENT:
                            // prepare code for processing in the browser
                            // add necessary html encodings
                            // use the configured mathjax delimiters
                            $tex = str_replace('<', '&lt;', $tex);
                            $replacement = $this->config->getClientLimiterStart() . $tex
                                . $this->config->getClientLimiterEnd();
                            break;

                        case self::ENGINE_SERVER:
                            // apply server-side processing
                            // mathjax-node expects pure tex code
                            // so revert any applied html encoding
                            $tex = html_entity_decode($tex, ENT_QUOTES, 'UTF-8');
                            $replacement = $this->renderMathJax($tex);
                            break;

                        case self::ENGINE_DEFERRED:
                            // protect code to save it for post production
                            $replacement = '[tex]' . 'base64:' . base64_encode($tex) . '[/tex]';
                            break;

                        default:
                            // keep the original
                            $replacement = $tex;
                            break;
                    }

                    // replace delimiters and tex code with prepared code or generated image
                    $a_text = ilStr::subStr($a_text, 0, $spos) . $replacement
                        . ilStr::subStr($a_text, $epos + ilStr::strLen($a_end));

                    // continue search behind replacement
                    $cpos = $spos + ilStr::strLen($replacement);
                }
            } else {
                // end delimiter position not found => stop search
                break;
            }

            if ($cpos >= ilStr::strlen($a_text)) {
                // current position at the end => stop search
                break;
            }
        }
        return $a_text;
    }

    /**
     * Render image from tex code using the MathJax server
     */
    protected function renderMathJax(string $a_tex): string
    {
        $options = $this->default_server_options;
        $options['math'] = $a_tex;
        $options['dpi'] = $this->dpi;

        switch ($this->output) {
            case self::OUTPUT_PNG:
                $options['svg'] = false;
                $options['png'] = true;
                $suffix = ".png";
                break;

            case self::OUTPUT_SVG:
            default:
                $options['svg'] = true;
                $options['png'] = false;
                $suffix = ".svg";
                break;
        }

        $image = $this->factory->image($a_tex, $this->output, $this->dpi);

        try {
            if (!$image->exists()) {
                $server = $this->factory->server($this->config);
                $image->write($server->call($options));
            }

            // get the image properties
            switch ($this->output) {
                case self::OUTPUT_PNG:
                    [$width, $height] = getimagesize($image->absolutePath());
                    $width = round($width * $this->zoom_factor);
                    $height = round($height * $this->zoom_factor);
                    $mime = 'image/png';
                    break;

                case self::OUTPUT_SVG:
                default:
                    $svg = simplexml_load_string(file_get_contents($image->absolutePath()));
                    $width = round($svg['width'] * $this->zoom_factor);
                    $height = round($svg['height'] * $this->zoom_factor);
                    $mime = 'image/svg+xml';
                    break;
            }

            // generate the html code
            switch ($this->rendering) {
                case self::RENDER_SVG_AS_XML_EMBED:
                    $html = $image->read();
                    break;

                case self::RENDER_SVG_AS_IMG_EMBED:
                case self::RENDER_PNG_AS_IMG_EMBED:
                    $html = '<img src="data:' . $mime . ';base64,'
                        . base64_encode($image->read())
                        . '" style="width:' . $width . '; height:' . $height . ';" />';
                    break;

                case self::RENDER_PNG_AS_FO_FILE:
                    $html = '<fo:external-graphic src="' . $image->absolutePath() . '"'
                        . ' content-height="' . $height . 'px" content-width="' . $width . 'px"></fo:external-graphic>';
                    break;

                default:
                    $html = htmlspecialchars($a_tex);
                    break;
            }
            return $html;
        } catch (Exception $e) {
            return "[TeX rendering failed: " . $e->getMessage() . htmlentities($a_tex) . "]";
        }
    }

    /**
     * Get the size of the image cache
     */
    public function getCacheSize(): string
    {
        return $this->factory->image('', $this->output, $this->dpi)->getCacheSize();
    }

    /**
     * Clear the cache of rendered graphics
     */
    public function clearCache(): void
    {
        $image = $this->factory->image('', $this->output, $this->dpi);
        $image->clearCache();
    }
}
