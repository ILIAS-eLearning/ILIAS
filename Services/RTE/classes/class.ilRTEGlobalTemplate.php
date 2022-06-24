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

    public function setOnScreenMessage(string $type, string $a_txt, bool $a_keep = false) : void
    {
    }

    public function addJavaScript(string $a_js_file, bool $a_add_version_parameter = true, int $a_batch = 2) : void
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

    public function addOnLoadCode(string $a_code, int $a_batch = 2) : void
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

    public function fillJavaScriptFiles(bool $a_force = false) : void
    {
        global $DIC;

        $ilSetting = $DIC->settings();

        $vers = '';
        if (is_object($ilSetting)) {
            $vers = 'vers=' . str_replace(['.', ' '], '-', ILIAS_VERSION);

            if (defined('DEVMODE') && DEVMODE) {
                $vers .= '-' . time();
            }
        }

        if ($this->blockExists('js_file')) {
            for ($i = 0; $i <= 3; $i++) {
                reset($this->js_files);
                foreach ($this->js_files as $file) {
                    if ($this->js_files_batch[$file] === $i) {
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

    public function addCss(string $a_css_file, string $media = "screen") : void
    {
    }

    public function addInlineCss(string $a_css, string $media = "screen") : void
    {
    }

    public function setBodyClass(string $a_class = '') : void
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

    public function setTitle(string $a_title, bool $hidden = false) : void
    {
    }

    public function setDescription(string $a_descr) : void
    {
    }

    public function setTitleIcon(string $a_icon_path, string $a_icon_desc = "") : void
    {
    }

    public function setAlertProperties(array $alerts) : void
    {
    }

    public function clearHeader() : void
    {
    }

    public function setHeaderActionMenu(string $a_header) : void
    {
    }

    public function setHeaderPageTitle(string $a_title) : void
    {
    }

    public function setLocator() : void
    {
    }

    public function setTabs(string $a_tabs_html) : void
    {
    }


    public function setSubTabs(string $a_tabs_html) : void
    {
    }

    public function setContent(string $a_html) : void
    {
    }

    public function setLeftContent(string $a_html) : void
    {
    }

    public function setLeftNavContent(string $a_content) : void
    {
    }

    public function setRightContent(string $a_html) : void
    {
    }

    public function setPageFormAction(string $a_action) : void
    {
    }

    public function setLoginTargetPar(string $a_val) : void
    {
    }

    public function getSpecial(
        string $part = self::DEFAULT_BLOCK,
        bool $add_error_mess = false,
        bool $handle_referer = false,
        bool $add_ilias_footer = false,
        bool $add_standard_elements = false,
        bool $a_main_menu = true,
        bool $a_tabs = true
    ) : string {
        return '';
    }

    public function printToStdout(
        string $part = self::DEFAULT_BLOCK,
        bool $has_tabs = true,
        bool $skip_main_menu = false
    ) : void {
        global $DIC;

        $http = $DIC->http();
        switch ($http->request()->getHeaderLine('Accept')) {
            default:
                ilYuiUtil::initDom();

                header('P3P: CP="CURa ADMa DEVa TAIa PSAa PSDa IVAa IVDa OUR BUS IND UNI COM NAV INT CNT STA PRE"');
                header('Content-type: text/html; charset=UTF-8');

                $this->fillBodyClass();
                if ($has_tabs) {
                    $this->setCurrentBlock(self::DEFAULT_BLOCK);
                    $this->fillJavaScriptFiles();
                }

                if ($part === self::DEFAULT_BLOCK) {
                    $html = $this->template->getUnmodified();
                } else {
                    $html = $this->template->getUnmodified($part);
                }

                print $html;

                break;
        }
    }

    public function setTreeFlatIcon(string $a_link, string $a_mode) : void
    {
    }

    public function addLightbox(string $a_html, string $a_id) : void
    {
    }

    public function addAdminPanelToolbar(
        ilToolbarGUI $toolbar,
        bool $is_bottom_panel = true,
        bool $has_arrow = false
    ) : void {
    }

    public function setPermanentLink(
        string $a_type,
        ?int $a_id,
        string $a_append = "",
        string $a_target = "",
        string $a_title = ""
    ) : void {
    }

    public function resetHeaderBlock(bool $a_reset_header_action = true) : void
    {
    }

    public function setFileUploadRefId(int $a_ref_id) : void
    {
    }

    public function get(string $part = self::DEFAULT_BLOCK) : string
    {
        return $this->template->get($part);
    }

    public function setVariable(string $variable, $value = '') : void
    {
        $this->template->setVariable($variable, $value);
    }

    public function setCurrentBlock(string $part = self::DEFAULT_BLOCK) : bool
    {
        return $this->template->setCurrentBlock($part);
    }

    public function touchBlock(string $block) : bool
    {
        return $this->template->touchBlock($block);
    }

    public function parseCurrentBlock(string $block_name = self::DEFAULT_BLOCK) : bool
    {
        return $this->template->parseCurrentBlock($block_name);
    }

    public function addBlockFile(string $var, string $block, string $template_name, string $in_module = null) : bool
    {
        return $this->template->addBlockFile($var, $block, $template_name, $in_module);
    }

    public function blockExists(string $block_name) : bool
    {
        return $this->template->blockExists($block_name);
    }
}
