<?php declare(strict_types=1);
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Special template class to simplify handling of ITX/PEAR
 * @author Stefan Kesseler <skesseler@databay.de>
 * @author Sascha Hofmann <shofmann@databay.de>
 */
class ilRTEGlobalTemplate implements ilGlobalTemplateInterface
{
    protected string $tree_flat_link = '';
    protected string $page_form_action = '';
    protected bool $permanent_link = false;
    protected array $lightbox = [];
    protected bool $standard_template_loaded = false;
    protected ilTemplate $template;
    protected string $body_class = '';

    public function __construct(
        string $file,
        bool $flag1,
        bool $flag2,
        string $in_module = '',
        string $vars = 'DEFAULT',
        bool $plugin = false,
        bool $a_use_cache = true
    ) {
        $this->setBodyClass('std');
        $this->template = new ilTemplate($file, $flag1, $flag2, $in_module, $vars, $plugin, $a_use_cache);
    }

    public function printToString() : string
    {
        throw new ilException('not implemented');
    }

    public function hideFooter() : void
    {
    }

    public function setOnScreenMessage($a_type, $a_txt, $a_keep = false) : void
    {
    }

    /**
     * List of JS-Files that should be included.
     * @var array<int,string>
     */
    protected array $js_files = [0 => './Services/JavaScript/js/Basic.js'];

    /**
     * Stores if a version parameter should be appended to the js-file to force reloading.
     * @var array<string,bool>
     */
    protected array $js_files_vp = ['./Services/JavaScript/js/Basic.js' => true];

    /**
     * Stores the order in which js-files should be included.
     * @var array<string,int>
     */
    protected array $js_files_batch = ['./Services/JavaScript/js/Basic.js' => 1];

    public function addJavaScript($a_js_file, $a_add_version_parameter = true, $a_batch = 2) : void
    {
        // three batches currently
        if ($a_batch < 1 || $a_batch > 3) {
            $a_batch = 2;
        }

        // ensure jquery files being loaded first
        if (
            is_int(strpos($a_js_file, 'Services/jQuery')) ||
            is_int(strpos($a_js_file, '/jquery.js')) ||
            is_int(strpos($a_js_file, '/jquery-min.js'))
        ) {
            $a_batch = 0;
        }

        if (!in_array($a_js_file, $this->js_files, true)) {
            $this->js_files[] = $a_js_file;
            $this->js_files_vp[$a_js_file] = $a_add_version_parameter;
            $this->js_files_batch[$a_js_file] = $a_batch;
        }
    }

    public function addOnLoadCode($a_code, $a_batch = 2) : void
    {
    }

    public function getOnLoadCodeForAsynch() : string
    {
        return '';
    }

    public function resetJavascript() : void
    {
        $this->js_files = [];
        $this->js_files_vp = [];
        $this->js_files_batch = [];
    }

    public function fillJavaScriptFiles($a_force = false) : void
    {
        global $DIC;

        $ilSetting = $DIC->settings();

        $vers = '';
        if (is_object($ilSetting)) {
            $vers = 'vers=' . str_replace(['.', ' '], '-', $ilSetting->get('ilias_version', ''));

            if (DEVMODE) {
                $vers .= '-' . time();
            }
        }

        if ($this->blockExists('js_file')) {
            for ($i = 0; $i <= 3; $i++) {
                reset($this->js_files);
                foreach ($this->js_files as $file) {
                    if ($this->js_files_batch[$file] == $i) {
                        if ($a_force || is_file($file) || strpos($file, 'http') === 0 || strpos($file, '//') === 0) {
                            $this->fillJavascriptFile($file, $vers);
                        } elseif (strpos($file, './') === 0) { // #13962
                            $url_parts = parse_url($file);
                            if (is_file($url_parts['path'])) {
                                $this->fillJavascriptFile($file, $vers);
                            }
                        }
                    }
                }
            }
        }
    }

    protected function fillJavascriptFile(string $file, string $vers) : void
    {
        $this->setCurrentBlock('js_file');
        if ($this->js_files_vp[$file]) {
            $this->setVariable('JS_FILE', ilUtil::appendUrlParameterString($file, $vers));
        } else {
            $this->setVariable('JS_FILE', $file);
        }
        $this->parseCurrentBlock();
    }

    public function addCss($a_css_file, $media = 'screen') : void
    {
    }

    public function addInlineCss($a_css, $media = 'screen') : void
    {
    }

    public function setBodyClass($a_class = '') : void
    {
        $this->body_class = $a_class;
    }

    private function fillBodyClass() : void
    {
        if ($this->body_class !== '' && $this->blockExists('body_class')) {
            $this->setCurrentBlock('body_class');
            $this->setVariable('BODY_CLASS', $this->body_class);
            $this->parseCurrentBlock();
        }
    }

    public function loadStandardTemplate() : void
    {
        if ($this->standard_template_loaded) {
            return;
        }

        iljQueryUtil::initjQuery();
        iljQueryUtil::initjQueryUI();

        ilUIFramework::init();

        $this->addBlockFile('CONTENT', 'content', 'tpl.adm_content.html');
        $this->addBlockFile('STATUSLINE', 'statusline', 'tpl.statusline.html');

        $this->standard_template_loaded = true;
    }

    public function setTitle($a_title, $hidden = false) : void
    {
    }

    public function setDescription($a_descr) : void
    {
    }

    public function setTitleIcon($a_icon_path, $a_icon_desc = "") : void
    {
    }

    public function setAlertProperties(array $a_props) : void
    {
    }

    public function clearHeader() : void
    {
    }

    public function setHeaderActionMenu($a_header) : void
    {
    }

    public function setHeaderPageTitle($a_title) : void
    {
    }

    public function setLocator() : void
    {
    }

    public function setTabs($a_tabs_html) : void
    {
    }

    public function setSubTabs($a_tabs_html) : void
    {
    }

    public function setContent($a_html) : void
    {
    }

    public function setLeftContent($a_html) : void
    {
    }

    public function setLeftNavContent($a_content) : void
    {
    }

    public function setRightContent($a_html) : void
    {
    }

    public function setPageFormAction($a_action) : void
    {
    }

    public function setLoginTargetPar($a_val) : void
    {
    }

    public function getSpecial(
        $part = 'DEFAULT',
        $add_error_mess = false,
        $handle_referer = false,
        $add_ilias_footer = false,
        $add_standard_elements = false,
        $a_main_menu = true,
        $a_tabs = true
    ) : string {
        return '';
    }

    public function printToStdout($part = 'DEFAULT', $a_fill_tabs = true, $a_skip_main_menu = false) : void
    {
        global $DIC;

        $http = $DIC->http();
        switch ($http->request()->getHeaderLine('Accept')) {
            default:
                ilYuiUtil::initDom();

                header('P3P: CP="CURa ADMa DEVa TAIa PSAa PSDa IVAa IVDa OUR BUS IND UNI COM NAV INT CNT STA PRE"');
                header('Content-type: text/html; charset=UTF-8');

                $this->fillBodyClass();
                if ($a_fill_tabs) {
                    $this->setCurrentBlock('DEFAULT');
                    $this->fillJavaScriptFiles();
                }

                if ($part === 'DEFAULT' || is_bool($part)) {
                    $html = $this->template->getUnmodified();
                } else {
                    $html = $this->template->getUnmodified($part);
                }

                print $html;

                break;
        }
    }

    public function setTreeFlatIcon($a_link, $a_mode) : void
    {
    }

    public function addLightbox($a_html, $a_id) : void
    {
    }

    public function addAdminPanelToolbar(ilToolbarGUI $toolb, $a_bottom_panel = true, $a_arrow = false) : void
    {
    }

    public function setPermanentLink($a_type, $a_id, $a_append = '', $a_target = '', $a_title = '') : void
    {
    }

    public function resetHeaderBlock($a_reset_header_action = true) : void
    {
    }

    public function setFileUploadRefId($a_ref_id) : void
    {
    }

    public function get($part = 'DEFAULT') : string
    {
        return $this->template->get($part);
    }

    public function setVariable(string $variable, $value = '') : void
    {
        $this->template->setVariable($variable, $value);
    }

    private function variableExists($a_variablename) : bool
    {
        return $this->template->variableExists($a_variablename);
    }

    public function setCurrentBlock(string $part = 'DEFAULT') : bool
    {
        return $this->template->setCurrentBlock($part);
    }

    /**
     * @throws ilTemplateException
     */
    public function touchBlock(string $block) : bool
    {
        return $this->template->touchBlock($block);
    }

    public function parseCurrentBlock(string $part = 'DEFAULT') : bool
    {
        return $this->template->parseCurrentBlock($part);
    }

    public function addBlockFile(string $var, string $block, string $template_name, string $in_module = null) : bool
    {
        return $this->template->addBlockFile($var, $block, $template_name, $in_module);
    }

    public function blockExists(string $a_blockname) : bool
    {
        return $this->template->blockExists($a_blockname);
    }
}
