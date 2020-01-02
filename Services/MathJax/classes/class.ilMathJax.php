<?php
/* Copyright (c) 1998-2016 ILIAS open source, Extended GPL, see docs/LICENSE */

// fau: mathJaxServer - new class for mathjax rendering.

/**
 * Class for Server-side generation of latex formulas
 */
class ilMathJax
{
    const PURPOSE_BROWSER = 'browser';					// direct display of page in the browser
    const PURPOSE_EXPORT = 'export';					// html export of contents
    const PURPOSE_PDF = 'pdf';							// server-side PDF generation
    const PURPOSE_DEFERRED_PDF = 'deferred_pdf';		// defer rendering for server-side pdf generation (e.g. for XSL-FO)
                                                        // 		this needs a second call with PURPOSE_PDF at the end

    const ENGINE_SERVER = 'server';						// code is treated by one of the rendering modes below
    const ENGINE_CLIENT = 'client';						// code delimiters are
    const ENGINE_MIMETEX = 'mimetex';					// fallback to old mimetex cgi (if configured in ilias.ini.php)
    const ENGINE_DEFERRED = 'deferred';					// protect code for a deferred rendering
    const ENGINE_NONE = 'none';							// don't render the code, just show it

    const RENDER_SVG_AS_XML_EMBED = 'svg_as_xml_embed';	// embed svg code directly in html (default for browser view)
    const RENDER_SVG_AS_IMG_EMBED = 'svg_as_img_embed'; // embed svg base64 encoded in an img tag (default for HTML export)
    const RENDER_SVG_AS_IMG_FILE = 'svg_as_img_file';	// refer to an svg file from an img tag (if called with output dir)

    const RENDER_PNG_AS_IMG_EMBED = 'png_as_img_embed'; // embed png base64 encoded in an img tag (default for PDF generation)
    const RENDER_PNG_AS_IMG_FILE = 'png_as_img_file';	// refer to a png file from an img tag (if called with output dir)
    const RENDER_PNG_AS_FO_FILE = 'png_as_fo_file';		// refer to a png file from an fo tag (for PDF generation with XSL-FO)

    /**
     * @var ilMathJax	Singleton instance
     */
    protected static $_instance = null;

    /**
     * @var ilSetting	MathJax settings
     */
    protected $settings = null;

    /**
     * @var string		Rendering engine
     */
    protected $engine = null;

    /**
     * @var string		URL of the MathJax script for client side rendering
     */
    protected $mathjax_url = '';

    /**
     * @var string		Start limiter for client side rendering
     */
    protected $start_limiter = '';

    /**
     * @var string		End limiter for client side rendering
     */
    protected $end_limiter = '';

    /**
     * @var string		URL of the MathJax server
     */
    protected $server_address = '';

    /**
     * @var float		Server connection timeout in seconds
     */
    protected $server_timeout = 5;

    /**
     * @var string		Rendering mode
     */
    protected $rendering = self::RENDER_SVG_AS_XML_EMBED;

    /**
     * @var string		Output format of the server side rendering
     */
    protected $output = 'svg';

    /**
     * @var int		DPI of rasterized image
     */
    protected $dpi = 150;

    /**
     * @var float	Zoom factor
     */
    protected $zoom_factor = 1.0;


    /**
     * @var string		URL of the mimetex cgi, taken from ilias.ini.php [tools] latex = ...
     * ILIAS 5.2: 		URL_TO_LATEX is still set in ilInitialisation::initIliasIniFile
     * 					The setup formula doesn't include this setting anymore
     */
    protected $mimetex_url = URL_TO_LATEX;

    /**
     * @var string		counter for images generated with mimetex
     */
    protected $mimetex_count = 0;

    /**
     * @var bool		Use cURL extension for the server call
     *					this is automatically set if the extension is loaded
     * 					otherwise allow_url_fopen must be set in php.ini
     */
    protected $use_curl = true;

    /**
     * @var string 	Directory with cached graphics
     */
    protected $cache_dir = '';

    /**
     * @var array	Default options for calling the MathJax server
     */
    protected $default_options = array(
        "format" => "TeX",
        "math" => '',		// TeX code
        "svg" => true,
        "mml" => false,
        "png" => false,
        "speakText" => false,
        "speakRuleset" => "mathspeak",
        "speakStyle"=> "default",
        "ex"=> 6,
        "width"=> 1000000,
        "linebreaks"=> false,
    );


    /**
     * Singleton: protected constructor
     */
    protected function __construct()
    {
        // initiate the settings for browser as default
        include_once "./Services/Administration/classes/class.ilSetting.php";
        $this->settings = new ilSetting("MathJax");
        $this->init(self::PURPOSE_BROWSER);

        // set the connection method
        $this->use_curl = extension_loaded('cURL');

        // set the cache directory
        $this->cache_dir = ilUtil::getWebspaceDir() . '/temp/tex';
    }

    /**
     * Singleton: get instance
     * @return ilMathJax|null
     */
    public static function getInstance()
    {
        if (self::$_instance === null) {
            self::$_instance = new self;
        }
        return self::$_instance;
    }

    /**
     * Initialize the usage
     * This must be done before any rendering call
     * @param	string  $a_purpose 	purpose of the request
     * @return	ilMathJax
     */
    public function init($a_purpose = self::PURPOSE_BROWSER)
    {
        // reset the choice of a former initialisation
        unset($this->engine);

        // try server-side rendering first, set this engine, if possible
        if ($this->settings->get('enable_server')) {
            $this->server_address = $this->settings->get('server_address');
            $this->server_timeout = $this->settings->get('server_timeout');

            if ($a_purpose == self::PURPOSE_BROWSER && $this->settings->get('server_for_browser')) {
                $this->engine = self::ENGINE_SERVER;
                // delivering svg directly in page may be faster than loading image files
                $this->setRendering(self::RENDER_SVG_AS_XML_EMBED);
            } elseif ($a_purpose == self::PURPOSE_EXPORT && $this->settings->get('server_for_export')) {
                $this->engine = self::ENGINE_SERVER;
                // offline pages must always embed the svg as image tags
                // otherwise the html base tag may conflict with references in svg
                $this->setRendering(self::RENDER_SVG_AS_IMG_EMBED);
            } elseif ($a_purpose == self::PURPOSE_PDF && $this->settings->get('server_for_pdf')) {
                $this->engine = self::ENGINE_SERVER;
                // embedded png should work in most pdf engines
                // details can be set by the rendering engine
                $this->setRendering(self::RENDER_PNG_AS_IMG_EMBED);
            } elseif ($a_purpose == self::PURPOSE_DEFERRED_PDF && $this->settings->get('server_for_pdf')) {
                $this->engine = self::ENGINE_DEFERRED;
            }
        }

        // if server is not generally enabled or not activated for the intended purpose
        // then set engine for client-side rendering, if possible
        if (!isset($this->engine) && $this->settings->get('enable')) {
            $this->engine = self::ENGINE_CLIENT;
            $this->mathjax_url = $this->settings->get('path_to_mathjax');
            $this->includeMathJax();

            switch ((int) $this->settings->get("limiter")) {
                case 1:
                    $this->start_limiter = "[tex]";
                    $this->end_limiter = "[/tex]";
                    break;

                case 2:
                    $this->start_limiter = '<span class="math">';
                    $this->end_limiter = '</span>';
                    break;

                default:
                    $this->start_limiter = "\(";
                    $this->end_limiter = "\)";
                    break;
            }
        }

        // neither server nor client side rendering is enabled
        // the use the older mimetex as fallback, if configured in ilias.ini.php
        if (!isset($this->engine) && !empty($this->mimetex_url)) {
            $this->engine = self::ENGINE_MIMETEX;
        }

        // no engine available or configured
        if (!isset($this->engine)) {
            $this->engine = self::ENGINE_NONE;
        }

        return $this;
    }


    /**
     * Set the image type rendered by the server
     * @param string $a_rendering
     * @return	ilMathJax
     */
    public function setRendering($a_rendering)
    {
        switch ($a_rendering) {
            case self::RENDER_SVG_AS_XML_EMBED:
            case self::RENDER_SVG_AS_IMG_EMBED:
            case self::RENDER_SVG_AS_IMG_FILE:
                $this->rendering = $a_rendering;
                $this->output = 'svg';
                break;

            case self::RENDER_PNG_AS_IMG_EMBED:
            case self::RENDER_PNG_AS_IMG_FILE:
            case self::RENDER_PNG_AS_FO_FILE:
                $this->rendering = $a_rendering;
                $this->output = 'png';
                break;
        }
        return $this;
    }

    /**
     * Set the dpi of the rendered images
     * @param int $a_dpi
     * @return ilMathJax
     */
    public function setDpi($a_dpi)
    {
        $this->dpi = (float) $a_dpi;
        return $this;
    }

    /**
     * Set the zoom factor for images
     * @param float $a_factor
     * @return ilMathJax
     */
    public function setZoomFactor($a_factor)
    {
        $this->zoom_factor = (float) $a_factor;
        return $this;
    }

    /**
     * Include Mathjax javascript in a template
     *
     * @param	ilTemplate	$a_tpl
     */
    public function includeMathJax($a_tpl = null)
    {
        global $tpl;

        if ($a_tpl == null) {
            $a_tpl = $tpl;
        }

        if ($this->engine == self::ENGINE_CLIENT) {
            $a_tpl->addJavaScript($this->mathjax_url);
        }
    }


    /**
     * Replace tex tags with formula image code
     * New version of ilUtil::insertLatexImages
     *
     * @param	string	$a_text		text to be converted
     * @param	string  $a_start	start tag to be searched for
     * @param	string	$a_end		end tag to be converted
     * @param	string	$a_dir		output directory for rendered offline image files
     * @param	string	$a_path		path to be used in the src of offline images
     *
     * @return string 	replaced text
     */
    public function insertLatexImages($a_text, $a_start = '[tex]', $a_end = '[/tex]', $a_dir = null, $a_path = null)
    {
        // is this replacement still needed?
        // it was defined in the old ilUtil::insertLatexImages function
        // perhaps it was related to jsmath
        if ($this->engine != self::ENGINE_MIMETEX) {
            $a_text = preg_replace("/\\\\([RZN])([^a-zA-Z]|<\/span>)/", "\\mathbb{" . "$1" . "}" . "$2", $a_text);
        }

        // this is a fix for bug5362
        $a_start = str_replace("\\", "", $a_start);
        $a_end = str_replace("\\", "", $a_end);

        $cpos = 0;
        while (is_int($spos = stripos($a_text, $a_start, $cpos))) {	// find next start
            if (is_int($epos = stripos($a_text, $a_end, $spos + strlen($a_start)))) {
                // extract the tex code inside the delimiters
                $tex = substr($a_text, $spos + strlen($a_start), $epos - $spos - strlen($a_start));

                // undo a code protection done by the deferred engine before
                if (substr($tex, 0, 7) == 'base64:') {
                    $tex = base64_decode(substr($tex, 7));
                }

                // omit the html newlines added by the ILIAS page editor
                // handle custom newlines in JSMath (still needed?)
                $tex = str_replace('<br>', '', $tex);
                $tex = str_replace('<br/>', '', $tex);
                $tex = str_replace('<br />', '', $tex);
                $tex = str_replace('\\\\', '\\cr', $tex);

                // replace, if tags do not go across div borders
                if (!is_int(strpos($tex, '</div>'))) {
                    switch ($this->engine) {
                        case self::ENGINE_CLIENT:
                            // prepare code for processing in the browser
                            // add necessary html encodings
                            // use the configured mathjax delimiters
                            $tex = str_replace('<', '&lt;', $tex);
                            $replacement = $this->start_limiter . $tex . $this->end_limiter;
                            break;

                        case self::ENGINE_SERVER:
                            // apply server-side processing
                            // mathjax-node expects pure tex code
                            // so revert any applied html encoding
                            $tex = html_entity_decode($tex, ENT_QUOTES, 'UTF-8');
                            $replacement = $this->renderMathJax($tex, $a_dir, $a_path);
                            break;

                        case self::ENGINE_MIMETEX:
                            // use mimetex
                            $replacement = $this->renderMimetex($tex, $a_dir, $a_path);
                            break;

                        case self::ENGINE_DEFERRED:
                            // protect code to save it for post production
                            $replacement = '[tex]' . 'base64:' . base64_encode($tex) . '[/tex]';
                            break;

                        case self::ENGINE_NONE:
                            // show only the pure tex code
                            $replacement = htmlspecialchars($tex);
                            break;
                    }

                    // replace tex code with prepared code or generated image
                    $a_text = substr($a_text, 0, $spos) . $replacement . substr($a_text, $epos + strlen($a_end));
                }
            }
            $cpos = $spos + 1;
        }
        return $a_text;
    }


    /**
     * Render image from tex code using the MathJax server
     *
     * @param 	string	$a_tex				tex code
     * @param	string	$a_output_dir		directory to save an image file (without trailing slash)
     * @param	string	$a_image_path		image path to be used in the src attribute (without trailing slash)
     * @return 	string						html code of the rendered image
     */
    protected function renderMathJax($a_tex, $a_output_dir = null, $a_image_path = null)
    {
        $options = $this->default_options;
        $options['math'] = $a_tex;
        $options['dpi'] = $this->dpi;

        switch ($this->output) {
            case 'png':
                $options['svg'] = false;
                $options['png'] = true;
                $suffix = ".png";
                break;

            case 'svg':
            default:
                $options['svg'] = true;
                $options['png'] = false;
                $suffix = ".svg";
                break;
        }

        // store cached rendered image in cascading sub directories
        $hash = md5($a_tex . '#' . $this->dpi);
        $file = $this->cache_dir . '/' . substr($hash, 0, 4) . '/' . substr($hash, 4, 4) . '/' . $hash . $suffix;

        try {
            if (!is_file($file)) {
                // file has to be rendered
                if ($this->use_curl) {
                    $curl = curl_init($this->server_address);
                    curl_setopt($curl, CURLOPT_HEADER, false);
                    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
                    curl_setopt($curl, CURLOPT_HTTPHEADER, array("Content-type: application/json"));
                    curl_setopt($curl, CURLOPT_POST, true);
                    curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($options));
                    curl_setopt($curl, CURLOPT_TIMEOUT, $this->server_timeout);

                    $response = curl_exec($curl);
                    $status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
                    curl_close($curl);

                    if ($status != 200) {
                        $lines = explode("\n", $response);
                        return "[TeX rendering failed: " . $lines[1] . " " . htmlspecialchars($a_tex) . "]";
                    }
                } else {
                    $context = stream_context_create(
                        array(
                            'http' => array(
                                'method' => 'POST',
                                'content' => json_encode($options),
                                'header' => "Content-Type: application/json\r\n",
                                'timeout' => $this->server_timeout,
                                'ignore_errors' => true
                            )
                        )
                    );
                    $response = @file_get_contents($this->server_address, false, $context);
                    if (empty($response)) {
                        return "[TeX rendering failed: " . htmlspecialchars($a_tex) . "]";
                    }
                }

                // create the parent directories recursively
                @mkdir(dirname($file), 0777, true);

                // save a rendered image to the temp folder
                file_put_contents($file, $response);
            }

            // handle output of images for offline usage without embedding
            if (isset($a_output_dir) && is_dir($a_output_dir)) {
                @copy($file, $a_output_dir . '/' . $hash . $suffix);
                $src = $a_image_path . '/' . $hash . $suffix;
            } else {
                $src = ILIAS_HTTP_PATH . '/' . $file;
            }

            // generate the image tag
            switch ($this->output) {
                case 'png':
                    list($width, $height) = getimagesize($file);
                    $width = round($width * $this->zoom_factor);
                    $height = round($height * $this->zoom_factor);
                    $mime = 'image/png';
                    break;

                case 'svg':
                default:
                    $svg = simplexml_load_file($file);
                    $width = round($svg['width'] * $this->zoom_factor);
                    $height = round($svg['height'] * $this->zoom_factor);
                    $mime = 'image/svg+xml';
                    break;
            }


            // generate the image tag
            switch ($this->rendering) {
                case self::RENDER_SVG_AS_XML_EMBED:
                    $html = empty($response) ? file_get_contents($file) : $response;
                    break;

                case self::RENDER_SVG_AS_IMG_EMBED:
                case self::RENDER_PNG_AS_IMG_EMBED:
                    $html = '<img src="data:' . $mime . ';base64,'
                        . base64_encode(empty($response) ? file_get_contents($file) : $response)
                        . '" style="width:' . $width . '; height:' . $height . ';" />';
                    break;

                case self::RENDER_SVG_AS_IMG_FILE:
                case self::RENDER_PNG_AS_IMG_FILE:
                    $html = '<img src="' . $src . '" style="width:' . $width . '; height:' . $height . ';" />';
                    break;

                case self::RENDER_PNG_AS_FO_FILE:
                    $html = '<fo:external-graphic src="url(' . realpath($file) . ')"'
                        . ' content-height="' . $height . 'px" content-width="' . $width . 'px"></fo:external-graphic>';
                    break;

                default:
                    $html = htmlspecialchars($a_tex);
                    break;
            }

            return $html;
        } catch (Exception $e) {
            return "[TeX rendering failed: " . $e->getMessage() . "]";
        }
    }


    /**
     * Render image from tex code using mimetex
     *
     * @param 	string	$a_tex				tex code
     * @param	string	$a_output_dir		directory to save an image file (without trailing slash)
     * @param	string	$a_image_path		image path to be used in the src attribute (without trailing slash)
     * @return 	string						html code of rendered image
     */
    protected function renderMimetex($a_tex, $a_output_dir = null, $a_image_path = null)
    {
        $call = $this->mimetex_url . '?'
            . rawurlencode(str_replace('&amp;', '&', str_replace('&gt;', '>', str_replace('&lt;', '<', $a_tex))));

        if (empty($a_output_dir)) {
            $html = '<img alt="' . htmlentities($a_tex) . '" src="' . $call . '" />';
        } else {
            $cnt = $this->mimetex_count++;

            // get image from cgi and write it to file
            $fpr = @fopen($call, "r");
            $lcnt = 0;
            if ($fpr) {
                while (!feof($fpr)) {
                    $buf = fread($fpr, 1024);
                    if ($lcnt == 0) {
                        if (is_int(strpos(strtoupper(substr($buf, 0, 5)), "GIF"))) {
                            $suffix = "gif";
                        } else {
                            $suffix = "png";
                        }
                        $fpw = fopen($a_output_dir . "/img" . $cnt . "." . $suffix, "w");
                    }
                    $lcnt++;
                    fwrite($fpw, $buf);
                }
                fclose($fpw);
                fclose($fpr);
            }

            $html = '<img alt="' . htmlentities($a_tex) . '" src=' . $a_image_path . '/img"' . $cnt . '.' . $suffix . '/' . '" />';
        }

        return $html;
    }

    /**
     * Get the size of the image cache
     * @return string
     */
    public function getCacheSize()
    {
        $cache_dir = realpath($this->cache_dir);

        if (!is_dir($cache_dir)) {
            $size = 0;
        } else {
            $size = ilUtil::dirsize($cache_dir);
        }

        $type = array("k", "M", "G", "T");
        $size = $size / 1024;
        $counter = 0;
        while ($size >= 1024) {
            $size = $size / 1024;
            $counter++;
        }

        return(round($size, 1) . " " . $type[$counter] . "B");
    }

    /**
     * Clear the cache of rendered graphics
     */
    public function clearCache()
    {
        ilUtil::delDir($this->cache_dir);
    }
}
