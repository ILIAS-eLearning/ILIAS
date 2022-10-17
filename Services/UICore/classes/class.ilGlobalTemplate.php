<?php

declare(strict_types=1);

/* Copyright (c) 1998-2022 ILIAS open source, GPLv3, see LICENSE */

use ILIAS\DI\Container;

/**
 * special template class to simplify handling of ITX/PEAR
 * @author Stefan Kesseler <skesseler@databay.de>
 * @author Sascha Hofmann <shofmann@databay.de>
 * @author Thibeau Fuhrer <thibeau@sr.solutions>
 */
class ilGlobalTemplate implements ilGlobalTemplateInterface
{
    protected ilTemplate $template;
    protected ?ilToolbarGUI $admin_panel_commands_toolbar = null;

    /**
     * List of JS-Files that should be included.
     * @var string[]
     */
    protected array $js_files = [
        "./Services/JavaScript/js/Basic.js",
    ];

    /**
     * Stores if a version parameter should be appended to the js-file to force reloading.
     * @var array<string, bool>
     */
    protected array $js_files_vp = [
        "./Services/JavaScript/js/Basic.js" => true,
    ];

    /**
     * Stores the order in which js-files should be included.
     * @var array<string, int>
     */
    protected array $js_files_batch = [
        "./Services/JavaScript/js/Basic.js" => 1,
    ];

    /**
     * Stores CSS-files to be included.
     * @var array<string, array>
     */
    protected array $css_files = [];

    /**
     * Stores CSS to be included directly.
     * @var array<string, array>
     */
    protected array $inline_css = [];

    protected string $in_module;
    protected string $template_name;
    protected string $body_class = '';
    protected string $tree_flat_link = "";
    protected string $page_form_action = "";
    protected array $permanent_link = [];
    protected string $main_content = "";
    protected array $lightbox = [];
    protected bool $standard_template_loaded = false;
    protected string $main_menu = '';
    protected string $main_menu_spacer = '';
    protected array $messages = [];
    protected bool $show_footer = true;
    protected array $on_load_code = [];
    protected string $left_nav_content = '';
    protected string $tree_flat_mode = '';
    protected bool $admin_panel_arrow = false;
    protected bool $admin_panel_bottom = false;
    protected ?int $enable_fileupload = null;
    protected string $header_page_title = "";
    protected string $title = "";
    protected string $title_desc = "";
    protected array $title_alerts = [];
    protected string $header_action = '';
    protected string $icon_desc = '';
    protected string $icon_path = '';
    protected string $tabs_html = "";
    protected string $sub_tabs_html = "";
    protected string $left_content = '';
    protected string $right_content = '';
    protected string $login_target_par = '';

    /**
     * @throws ilTemplateException|ilSystemStyleException
     */
    public function __construct(
        string $file,
        bool $flag1,
        bool $flag2,
        string $in_module = '',
        string $vars = self::DEFAULT_BLOCK,
        bool $plugin = false,
        bool $a_use_cache = true
    ) {
        $this->setBodyClass("std");
        $this->template_name = $file;
        $this->in_module = $in_module;
        $this->template = new ilTemplate(
            $file,
            $flag1,
            $flag2,
            $in_module,
            $vars,
            $plugin,
            $a_use_cache
        );
    }

    public function printToString(string $part = self::DEFAULT_BLOCK): string
    {
        global $DIC;
        ilYuiUtil::initDom();
        return $this->renderPage($part, true, false, $DIC);
    }

    public function hideFooter(): void
    {
        $this->show_footer = false;
    }

    /**
     * @throws ilTemplateException
     * @throws ilCtrlException
     */
    protected function fillFooter(): void
    {
        if (!$this->show_footer) {
            return;
        }

        global $DIC;

        $ilSetting = $DIC->settings();
        $lng = $DIC->language();
        $ilCtrl = $DIC->ctrl();
        $ilDB = $DIC->database();

        $ftpl = new ilTemplate("tpl.footer.html", true, true, "Services/UICore");

        $php = "";
        if (DEVMODE) {
            $php = ", PHP " . PHP_VERSION;
        }
        $ftpl->setVariable("ILIAS_VERSION", ILIAS_VERSION . $php);

        $link_items = [];

        // imprint
        $call_history = $ilCtrl->getCallHistory();
        if (isset($call_history[0][ilCtrlInterface::PARAM_CMD_CLASS]) &&
            $call_history[0][ilCtrlInterface::PARAM_CMD_CLASS] !== "ilImprintGUI" &&
            ilImprint::isActive()
        ) {
            $link_items[ilLink::_getStaticLink(0, "impr")] = [$lng->txt("imprint"), true];
        }

        // system support contacts
        if (($l = ilSystemSupportContactsGUI::getFooterLink()) !== "") {
            $link_items[$l] = [ilSystemSupportContactsGUI::getFooterText(), false];
        }

        if (DEVMODE && function_exists("tidy_parse_string")) {
            // I think $_SERVER in dev mode is ok.
            $link_items[ilUtil::appendUrlParameterString(
                $_SERVER["REQUEST_URI"],
                "do_dev_validate=xhtml"
            )] = ["Validate", true];
            $link_items[ilUtil::appendUrlParameterString(
                $_SERVER["REQUEST_URI"],
                "do_dev_validate=accessibility"
            )] = ["Accessibility", true];
        }

        // output translation link
        if (ilObjLanguageAccess::_checkTranslate() && !ilObjLanguageAccess::_isPageTranslation()) {
            $link_items[ilObjLanguageAccess::_getTranslationLink()] = [$lng->txt('translation'), true];
        }

        $cnt = 0;
        foreach ($link_items as $url => $caption) {
            $cnt++;
            if ($caption[1]) {
                $ftpl->touchBlock("blank");
            }
            if ($cnt < count($link_items)) {
                $ftpl->touchBlock("item_separator");
            }

            $ftpl->setCurrentBlock("items");
            $ftpl->setVariable("URL_ITEM", ilUtil::secureUrl($url));
            $ftpl->setVariable("TXT_ITEM", $caption[0]);
            $ftpl->parseCurrentBlock();
        }

        if (DEVMODE) {
            // execution time
            $t1 = explode(" ", $GLOBALS['ilGlobalStartTime']);
            $t2 = explode(" ", microtime());
            $diff = $t2[0] - $t1[0] + $t2[1] - $t1[1];

            $mem_usage = [];
            if (function_exists("memory_get_usage")) {
                $mem_usage[] =
                    "Memory Usage: " . memory_get_usage() . " Bytes";
            }
            if (function_exists("xdebug_peak_memory_usage")) {
                $mem_usage[] =
                    "XDebug Peak Memory Usage: " . xdebug_peak_memory_usage() . " Bytes";
            }
            $mem_usage[] = round($diff, 4) . " Seconds";

            if (count($mem_usage)) {
                $ftpl->setVariable("MEMORY_USAGE", "<br>" . implode(" | ", $mem_usage));
            }

            // controller history
            if (is_object($ilCtrl) && $ftpl->blockExists("c_entry") &&
                $ftpl->blockExists("call_history")) {
                $hist = $ilCtrl->getCallHistory();
                foreach ($hist as $entry) {
                    $ftpl->setCurrentBlock("c_entry");
                    $ftpl->setVariable("C_ENTRY", $entry["class"]);
                    if (is_object($ilDB)) {
                        $file = $ilCtrl->lookupClassPath($entry["class"]);
                        $add = $entry["mode"] . " - " . $entry["cmd"];
                        if ($file !== "") {
                            $add .= " - " . $file;
                        }
                        $ftpl->setVariable("C_FILE", $add);
                    }
                    $ftpl->parseCurrentBlock();
                }
                $ftpl->setCurrentBlock("call_history");
                $ftpl->parseCurrentBlock();
            }

            // included files
            if (is_object($ilCtrl) && $ftpl->blockExists("i_entry") &&
                $ftpl->blockExists("included_files")) {
                $fs = get_included_files();
                $ifiles = [];
                $total = 0;
                foreach ($fs as $f) {
                    $ifiles[] = [
                        "file" => $f,
                        "size" => filesize($f),
                    ];
                    $total += filesize($f);
                }
                $ifiles = ilArrayUtil::sortArray($ifiles, "size", "desc", true);
                foreach ($ifiles as $f) {
                    $ftpl->setCurrentBlock("i_entry");
                    $ftpl->setVariable(
                        "I_ENTRY",
                        $f["file"] . " (" . $f["size"] . " Bytes, " . round(100 / $total * $f["size"], 2) . "%)"
                    );
                    $ftpl->parseCurrentBlock();
                }
                $ftpl->setCurrentBlock("i_entry");
                $ftpl->setVariable("I_ENTRY", "Total (" . $total . " Bytes, 100%)");
                $ftpl->parseCurrentBlock();
                $ftpl->setCurrentBlock("included_files");
                $ftpl->parseCurrentBlock();
            }
        }

        $this->setVariable("FOOTER", $ftpl->get());
    }

    protected function getMainMenu(): void
    {
    }

    protected function fillMainMenu(): void
    {
    }

    protected function initHelp(): void
    {
        //ilHelpGUI::initHelp($this);
    }

    public function setOnScreenMessage(string $a_type, string $a_txt, bool $a_keep = false): void
    {
        if ($a_txt === "" ||
            !in_array($a_type, self::MESSAGE_TYPES, true)
        ) {
            return;
        }

        if (!$a_keep) {
            $this->messages[$a_type] = $a_txt;
        } else {
            ilSession::set($a_type, $a_txt);
        }
    }

    protected function fillMessage(): void
    {
        $out = "";
        foreach (self::MESSAGE_TYPES as $type) {
            $txt = $this->getMessageTextForType($type);
            if (null !== $txt) {
                $out .= ilUtil::getSystemMessageHTML($txt, $type);
            }

            ilSession::clear($type);
        }

        if ($out !== "") {
            $this->setVariable("MESSAGE", $out);
        }
    }

    protected function getMessageTextForType(string $type): ?string
    {
        if (ilSession::has($type)) {
            return (string) ilSession::get($type);
        }

        return $this->messages[$type] ?? null;
    }

    public function addJavaScript(string $a_js_file, bool $a_add_version_parameter = true, int $a_batch = 2): void
    {
        // three batches currently
        if ($a_batch < 1 || $a_batch > 3) {
            $a_batch = 2;
        }

        // ensure jquery files being loaded first
        if (is_int(strpos($a_js_file, "Services/jQuery")) ||
            is_int(strpos($a_js_file, "/jquery.js")) ||
            is_int(strpos($a_js_file, "/jquery/")) ||
            is_int(strpos($a_js_file, "/jquery-ui/")) ||
            is_int(strpos($a_js_file, "/jquery-min.js"))
        ) {
            $a_batch = 0;
        }

        if (!in_array($a_js_file, $this->js_files, true)) {
            $this->js_files[] = $a_js_file;
            $this->js_files_vp[$a_js_file] = $a_add_version_parameter;
            $this->js_files_batch[$a_js_file] = $a_batch;
        }
    }

    public function addOnLoadCode(string $a_code, int $a_batch = 2): void
    {
        // three batches currently
        if ($a_batch < 1 || $a_batch > 3) {
            $a_batch = 2;
        }

        $this->on_load_code[$a_batch][] = $a_code;
    }

    public function getOnLoadCodeForAsynch(): string
    {
        $js = "";
        for ($i = 1; $i <= 3; $i++) {
            if (isset($this->on_load_code[$i])) {
                foreach ($this->on_load_code[$i] as $code) {
                    $js .= $code . "\n";
                }
            }
        }
        if ($js) {
            return '<script type="text/javascript">' . "\n" .
                $js .
                '</script>' . "\n";
        }

        return '';
    }

    public function resetJavascript(): void
    {
        $this->js_files = [];
        $this->js_files_vp = [];
        $this->js_files_batch = [];
    }

    public function fillJavaScriptFiles(bool $a_force = false): void
    {
        global $DIC;

        $ilSetting = $DIC->settings();

        $vers = '';
        if (is_object($ilSetting)) {        // maybe this one can be removed
            $vers = "vers=" . str_replace([".", " "], "-", ILIAS_VERSION);

            if (DEVMODE) {
                $vers .= '-' . time();
            }
        }
        if ($this->blockExists("js_file")) {
            // three batches
            for ($i = 0; $i <= 3; $i++) {
                reset($this->js_files);
                foreach ($this->js_files as $file) {
                    if ($this->js_files_batch[$file] === $i) {
                        if ($a_force ||
                            is_file($file) ||
                            strpos($file, "http") === 0 ||
                            strpos($file, "//") === 0
                        ) {
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

    public function fillOnLoadCode(): void
    {
        for ($i = 1; $i <= 3; $i++) {
            if (isset($this->on_load_code[$i])) {
                $this->setCurrentBlock("on_load_code");
                foreach ($this->on_load_code[$i] as $code) {
                    $this->setCurrentBlock("on_load_code_inner");
                    $this->setVariable("OLCODE", $code);
                    $this->parseCurrentBlock();
                }
                $this->setCurrentBlock("on_load_code");
                $this->parseCurrentBlock();
            }
        }
    }

    protected function fillJavascriptFile(string $file, string $vers): void
    {
        $this->setCurrentBlock("js_file");
        if ($this->js_files_vp[$file]) {
            $this->setVariable("JS_FILE", ilUtil::appendUrlParameterString($file, $vers));
        } else {
            $this->setVariable("JS_FILE", $file);
        }
        $this->parseCurrentBlock();
    }

    public function addCss(string $a_css_file, string $media = "screen"): void
    {
        if (!array_key_exists($a_css_file . $media, $this->css_files)) {
            $this->css_files[$a_css_file . $media] = [
                "file" => $a_css_file,
                "media" => $media,
            ];
        }
    }

    public function addInlineCss(string $a_css, string $media = "screen"): void
    {
        $this->inline_css[] = [
            "css" => $a_css,
            "media" => $media,
        ];
    }

    /**
     * @throws ilTemplateException
     */
    public function fillCssFiles(bool $a_force = false): void
    {
        if (!$this->blockExists("css_file")) {
            return;
        }
        foreach ($this->css_files as $css) {
            $filename = $css["file"];
            if (strpos($filename, "?") > 0) {
                $filename = substr($filename, 0, strpos($filename, "?"));
            }
            if ($a_force || is_file($filename)) {
                $this->setCurrentBlock("css_file");
                $this->setVariable("CSS_FILE", $css["file"]);
                $this->setVariable("CSS_MEDIA", $css["media"]);
                $this->parseCurrentBlock();
            }
        }
    }

    public function setBodyClass(string $a_class = ""): void
    {
        $this->body_class = $a_class;
    }

    /**
     * @throws ilTemplateException
     */
    public function fillBodyClass(): void
    {
        if ($this->body_class !== "" && $this->blockExists("body_class")) {
            $this->setCurrentBlock("body_class");
            $this->setVariable("BODY_CLASS", $this->body_class);
            $this->parseCurrentBlock();
        }
    }

    /**
     * @throws ilTemplateException
     * @throws ilCtrlException
     */
    public function renderPage(
        string $part,
        bool $a_fill_tabs,
        bool $a_skip_main_menu,
        Container $DIC
    ): string {
        $this->fillMessage();

        // display ILIAS footer
        if ($part !== '') {
            $this->fillFooter();
        }

        // set standard parts (tabs and title icon)
        $this->fillBodyClass();

        // see #22992
        $this->fillContentLanguage();

        if ($a_fill_tabs) {
            if ($this->blockExists("content")) {
                // determine default screen id
                $this->getTabsHTML();
            }
            // to get also the js files for the main menu
            if (!$a_skip_main_menu) {
                $this->getMainMenu();
                $this->initHelp();
            }

            // these fill blocks in tpl.main.html
            $this->fillCssFiles();
            $this->fillInlineCss();
            //$this->fillJavaScriptFiles();

            // these fill just plain placeholder variables in tpl.main.html
            $this->setCurrentBlock();
            $this->fillNewContentStyle();
            $this->fillWindowTitle();

            // these fill blocks in tpl.adm_content.html
            $this->fillHeader();
            $this->fillSideIcons();
            $this->fillScreenReaderFocus();
            $this->fillLeftContent();
            $this->fillLeftNav();
            $this->fillRightContent();
            $this->fillAdminPanel();
            $this->fillToolbar();
            $this->fillPermanentLink();

            $this->setCenterColumnClass();

            // late loading of javascipr files, since operations above may add files
            $this->fillJavaScriptFiles();
            $this->fillOnLoadCode();

            // these fill just plain placeholder variables in tpl.adm_content.html
            if ($this->blockExists("content")) {
                $this->setCurrentBlock("content");
                $this->fillTabs();
                $this->fillMainContent();
                $this->fillMainMenu();
                $this->fillLightbox();
                $this->parseCurrentBlock();
            }
        }

        if ($part === self::DEFAULT_BLOCK) {
            $html = $this->template->getUnmodified();
        } else {
            $html = $this->template->getUnmodified($part);
        }

        // Modification of html is done inline here and can't be done
        // by ilTemplate, because the "phase" is template_show in this
        // case here.
        $component_factory = $DIC["component.factory"];

        // not quite sure if that's good.
        $id = $this->template->getTemplateIdentifier(
            $this->template_name,
            $this->in_module
        );

        foreach ($component_factory->getActivePluginsInSlot("uihk") as $ui_plugin) {
            $gui_class = $ui_plugin->getUIClassInstance();
            $resp = $gui_class->getHTML(
                "",
                "template_show",
                [
                    "tpl_id" => $id,
                    "tpl_obj" => $this,
                    "html" => $html
                ]
            );

            if ($resp["mode"] !== ilUIHookPluginGUI::KEEP) {
                $html = $gui_class->modifyHTML($html, $resp);
            }
        }

        // save language usages as late as possible
        \ilObjLanguageAccess::_saveUsages();

        return $html;
    }

    protected function resetCss(): void
    {
        $this->css_files = [];
    }

    /**
     * @throws ilTemplateException
     */
    protected function fillInlineCss(): void
    {
        if (!$this->blockExists("css_inline")) {
            return;
        }
        foreach ($this->inline_css as $css) {
            $this->setCurrentBlock("css_inline");
            $this->setVariable("CSS_INLINE", $css["css"]);
            $this->parseCurrentBlock();
        }
    }

    protected function fillNewContentStyle(): void
    {
        $this->setVariable(
            "LOCATION_NEWCONTENT_STYLESHEET_TAG",
            '<link rel="stylesheet" type="text/css" href="' .
            ilUtil::getNewContentStyleSheetLocation()
            . '" />'
        );
    }

    /**
     * This loads the standard template "tpl.adm_content.html" and
     * "tpl.statusline.html" the CONTENT and STATUSLINE placeholders
     * if they are not already loaded.
     * @throws ilTemplateException
     */
    public function loadStandardTemplate(): void
    {
        if ($this->standard_template_loaded) {
            return;
        }

        // always load jQuery
        iljQueryUtil::initjQuery();
        iljQueryUtil::initjQueryUI();

        // always load ui framework
        ilUIFramework::init();

        $this->addBlockFile("CONTENT", "content", "tpl.adm_content.html");
        $this->addBlockFile("STATUSLINE", "statusline", "tpl.statusline.html");

        $this->standard_template_loaded = true;
    }

    /**
     * Sets title in standard template.
     * Will override the header_page_title.
     */
    public function setTitle(string $a_title, bool $hidden = false): void
    {
        $this->title = $a_title;
        $this->header_page_title = $a_title;
    }

    /**
     * Sets descripton below title in standard template.
     */
    public function setDescription(string $a_descr): void
    {
        $this->title_desc = $a_descr;
    }

    /**
     * set title icon
     */
    public function setTitleIcon(string $a_icon_path, string $a_icon_desc = ""): void
    {
        $this->icon_desc = $a_icon_desc;
        $this->icon_path = $a_icon_path;
    }

    public function setAlertProperties(array $a_props): void
    {
        $this->title_alerts = $a_props;
    }

    public function clearHeader(): void
    {
        $this->setTitle("");
        $this->setTitleIcon("");
        $this->setDescription("");
        $this->setAlertProperties([]);
    }

    public function setHeaderActionMenu(string $a_header): void
    {
        $this->header_action = $a_header;
    }

    public function setHeaderPageTitle(string $a_title): void
    {
        $this->header_page_title = $a_title;
    }

    /**
     * @throws ilTemplateException
     */
    protected function fillHeader(): void
    {
        global $DIC;

        $lng = $DIC->language();
        $header = $this->getHeaderActionMenu();

        $header_tpl = new ilTemplate('tpl.il_header.html', true, true);

        if ($this->icon_path !== "") {
            $header_tpl->setCurrentBlock("header_image");
            if ($this->icon_desc !== "") {
                $header_tpl->setVariable("IMAGE_DESC", $lng->txt("icon") . " " . $this->icon_desc);
            }

            $header_tpl->setVariable("IMG_HEADER", $this->icon_path);
            $header_tpl->parseCurrentBlock();
            $header = true;
        }

        if ($this->title !== "") {
            $title = ilUtil::stripScriptHTML($this->title);
            $header_tpl->setVariable("HEADER", $title);

            $header = true;
        }

        if ($header !== '') {
            $header_tpl->setCurrentBlock("header_image");
            $header_tpl->parseCurrentBlock();
        }

        if ($this->title_desc !== "") {
            $header_tpl->setCurrentBlock("header_desc");
            $header_tpl->setVariable("H_DESCRIPTION", $this->title_desc);
            $header_tpl->parseCurrentBlock();
        }

        if ($header !== '') {
            $header_tpl->setCurrentBlock("head_action_inner");
            $header_tpl->setVariable("HEAD_ACTION", $header);
            $header_tpl->parseCurrentBlock();
        }

        foreach ($this->title_alerts as $alert) {
            $header_tpl->setCurrentBlock('header_alert');
            if (!($alert['propertyNameVisible'] === false)) {
                $header_tpl->setVariable('H_PROP', $alert['property'] . ':');
            }
            $header_tpl->setVariable('H_VALUE', $alert['value']);
            $header_tpl->parseCurrentBlock();
        }

        // add file upload drop zone in header
        if ($this->enable_fileupload !== null) {
            $file_upload = new ilObjFileUploadDropzone(
                $this->enable_fileupload,
                $header_tpl->get()
            );

            $this->setVariable(
                "IL_DROPZONE_HEADER",
                $file_upload->getDropzoneHtml()
            );
        } else {
            $this->setVariable("IL_HEADER", $header_tpl->get());
        }
    }

    protected function getHeaderActionMenu(): string
    {
        return $this->header_action;
    }

    public function setLocator(): void
    {
        global $DIC;

        $ilLocator = $DIC["ilLocator"];
        $html = "";

        $uip = new ilUIHookProcessor(
            "Services/Locator",
            "main_locator",
            ["locator_gui" => $ilLocator]
        );
        if (!$uip->replaced()) {
            $html = $ilLocator->getHTML();
        }
        $html = $uip->getHTML($html);

        $this->setVariable("LOCATOR", $html);
    }

    /**
     * @throws ilTemplateException
     */
    public function setTabs(string $a_tabs_html): void
    {
        if ($a_tabs_html !== "" && $this->blockExists("tabs_outer_start")) {
            $this->touchBlock("tabs_outer_start");
            $this->touchBlock("tabs_outer_end");
            $this->touchBlock("tabs_inner_start");
            $this->touchBlock("tabs_inner_end");
            $this->setVariable("TABS", $a_tabs_html);
        }
    }

    public function setSubTabs(string $a_tabs_html): void
    {
        $this->setVariable("SUB_TABS", $a_tabs_html);
    }

    /**
     * @throws ilTemplateException
     */
    public function fillTabs(): void
    {
        if ($this->blockExists("tabs_outer_start")) {
            $this->touchBlock("tabs_outer_start");
            $this->touchBlock("tabs_outer_end");
            $this->touchBlock("tabs_inner_start");
            $this->touchBlock("tabs_inner_end");

            if ($this->tabs_html !== "") {
                $this->setVariable("TABS", $this->tabs_html);
            }
            $this->setVariable("SUB_TABS", $this->sub_tabs_html);
        }
    }

    protected function getTabsHTML(): void
    {
        global $DIC;

        $ilTabs = $DIC["ilTabs"];

        if ($this->blockExists("tabs_outer_start")) {
            $this->sub_tabs_html = $ilTabs->getSubTabHTML();
            $this->tabs_html = $ilTabs->getHTML(true);
        }
    }

    public function setContent(string $a_html): void
    {
        if ($a_html !== "") {
            $this->main_content = $a_html;
        }
    }

    public function setLeftContent(string $a_html): void
    {
        if ($a_html !== "") {
            $this->left_content = $a_html;
        }
    }

    public function setLeftNavContent(string $a_content): void
    {
        if ($a_content !== "") {
            $this->left_nav_content = $a_content;
        }
    }

    /**
     * @throws ilTemplateException
     */
    protected function fillLeftNav(): void
    {
        if (trim($this->left_nav_content) !== "") {
            $this->setCurrentBlock("left_nav");
            $this->setVariable("LEFT_NAV_CONTENT", trim($this->left_nav_content));
            $this->parseCurrentBlock();
            $this->touchBlock("left_nav_space");
        }
    }

    public function setRightContent(string $a_html): void
    {
        if ($a_html !== '') {
            $this->right_content = $a_html;
        }
    }

    /**
     * @throws ilTemplateException
     */
    protected function setCenterColumnClass(): void
    {
        if (!$this->blockExists("center_col_width")) {
            return;
        }

        $left = trim($this->left_content);
        $right = trim($this->right_content);

        switch (true) {
            case ('' !== $left && '' !== $right):
                $center_column_class = 'col-sm-6';
                break;

            case ('' !== $left || '' !== $right):
                $center_column_class = 'col-sm-9';
                break;

            default:
                $center_column_class = "col-sm-12";
                break;
        }

        if ('' !== $left) {
            $center_column_class .= " col-sm-push-3";
        }

        $this->setCurrentBlock("center_col_width");
        $this->setVariable("CENTER_COL", $center_column_class);
        $this->parseCurrentBlock();
    }

    protected function fillMainContent(): void
    {
        if (trim($this->main_content) !== "") {
            $this->setVariable("ADM_CONTENT", trim($this->main_content));
        }
    }

    /**
     * @throws ilTemplateException
     */
    protected function fillLeftContent(): void
    {
        if (trim($this->left_content) !== "") {
            $this->setCurrentBlock("left_column");
            $this->setVariable("LEFT_CONTENT", trim($this->left_content));
            $left_col_class = (trim($this->right_content) === "")
                ? "col-sm-3 col-sm-pull-9"
                : "col-sm-3 col-sm-pull-6";
            $this->setVariable("LEFT_COL_CLASS", $left_col_class);
            $this->parseCurrentBlock();
        }
    }

    /**
     * @throws ilTemplateException
     */
    protected function fillRightContent(): void
    {
        if (trim($this->right_content) !== "") {
            $this->setCurrentBlock("right_column");
            $this->setVariable("RIGHT_CONTENT", trim($this->right_content));
            $this->parseCurrentBlock();
        }
    }

    /**
     * @throws ilTemplateException
     */
    protected function fillToolbar(): void
    {
        global $DIC;

        $ilToolbar = $DIC["ilToolbar"];
        $thtml = $ilToolbar->getHTML();

        if ($thtml !== "") {
            $this->setCurrentBlock("toolbar_buttons");
            $this->setVariable("BUTTONS", $thtml);
            $this->parseCurrentBlock();
        }
    }

    public function fillContentLanguage(): void
    {
        global $DIC;
        $lng = $DIC->language();

        if (is_object($lng)) {
            $this->setVariable('META_CONTENT_LANGUAGE', $lng->getContentLanguage());
            $this->setVariable('LANGUAGE_DIRECTION', $lng->getTextDirection());
        }
    }

    public function fillWindowTitle(): void
    {
        global $DIC;

        $ilSetting = $DIC->settings();

        if ($this->header_page_title !== "") {
            $title = ilUtil::stripScriptHTML($this->header_page_title);
            $this->setVariable("PAGETITLE", "- " . $title);
        }

        if ($ilSetting->get('short_inst_name') !== "") {
            $this->setVariable(
                "WINDOW_TITLE",
                $ilSetting->get('short_inst_name')
            );
        } else {
            $this->setVariable(
                "WINDOW_TITLE",
                "ILIAS"
            );
        }
    }

    public function setPageFormAction(string $a_action): void
    {
        $this->page_form_action = $a_action;
    }

    /**
     * @throws ilTemplateException
     */
    protected function fillPageFormAction(): void
    {
        if ($this->page_form_action !== "") {
            $this->setCurrentBlock("page_form_start");
            $this->setVariable("PAGE_FORM_ACTION", $this->page_form_action);
            $this->parseCurrentBlock();
            $this->touchBlock("page_form_end");
        }
    }

    /**
     * Set target parameter for login (public sector).
     * This is used by the main menu
     */
    public function setLoginTargetPar(string $a_val): void
    {
        $this->login_target_par = $a_val;
    }

    protected function getLoginTargetPar(): string
    {
        return $this->login_target_par;
    }

    /**
     * @throws ilTemplateException
     * @throws ilCtrlException
     */
    public function getSpecial(
        string $part = self::DEFAULT_BLOCK,
        bool $add_error_mess = false,
        bool $handle_referer = false,
        bool $add_ilias_footer = false,
        bool $add_standard_elements = false,
        bool $a_main_menu = true,
        bool $a_tabs = true
    ): string {
        if ($add_error_mess) {
            $this->fillMessage();
        }

        if ($add_ilias_footer) {
            $this->fillFooter();
        }

        // set standard parts (tabs and title icon)
        if ($add_standard_elements) {
            if ($a_tabs && $this->blockExists("content")) {
                // determine default screen id
                $this->getTabsHTML();
            }

            // to get also the js files for the main menu
            $this->getMainMenu();
            $this->initHelp();

            // these fill blocks in tpl.main.html
            $this->fillCssFiles();
            $this->fillInlineCss();
            $this->fillBodyClass();

            // these fill just plain placeholder variables in tpl.main.html
            $this->setCurrentBlock();
            $this->fillNewContentStyle();
            $this->fillContentLanguage();
            $this->fillWindowTitle();

            // these fill blocks in tpl.adm_content.html
            $this->fillHeader();
            $this->fillSideIcons();
            $this->fillScreenReaderFocus();
            $this->fillLeftContent();
            $this->fillLeftNav();
            $this->fillRightContent();
            $this->fillAdminPanel();
            $this->fillToolbar();
            $this->fillPermanentLink();

            $this->setCenterColumnClass();

            // late loading of javascipr files, since operations above may add files
            $this->fillJavaScriptFiles();
            $this->fillOnLoadCode();

            // these fill just plain placeholder variables in tpl.adm_content.html
            if ($this->blockExists("content")) {
                $this->setCurrentBlock("content");
                if ($a_tabs) {
                    $this->fillTabs();
                }
                $this->fillMainContent();
                if ($a_main_menu) {
                    $this->fillMainMenu();
                }
                $this->fillLightbox();
                $this->parseCurrentBlock();
            }
        }

        if ($part === self::DEFAULT_BLOCK) {
            $html = $this->template->get();
        } else {
            $html = $this->template->get($part);
        }

        // save language usages as late as possible
        \ilObjLanguageAccess::_saveUsages();

        return $html;
    }

    public function printToStdout(
        string $part = self::DEFAULT_BLOCK,
        bool $a_fill_tabs = true,
        bool $a_skip_main_menu = false
    ): void {
        global $DIC;

        // include yahoo dom per default
        ilYuiUtil::initDom();

        header('P3P: CP="CURa ADMa DEVa TAIa PSAa PSDa IVAa IVDa OUR BUS IND UNI COM NAV INT CNT STA PRE"');
        header("Content-type: text/html; charset=UTF-8");

        print $this->renderPage(
            $part,
            $a_fill_tabs,
            $a_skip_main_menu,
            $DIC
        );
    }

    public function fillScreenReaderFocus(): void
    {
        // abandoned
    }

    /**
     * @throws ilTemplateException
     */
    protected function fillSideIcons(): void
    {
        global $DIC;

        $ilSetting = $DIC->settings();
        $lng = $DIC->language();

        if ($this->tree_flat_link !== "") {
            if ($this->left_nav_content !== "") {
                $this->touchBlock("tree_lns");
            }

            $this->setCurrentBlock("tree_mode");
            $this->setVariable("LINK_MODE", $this->tree_flat_link);
            $this->setVariable("IMG_TREE", ilUtil::getImagePath("icon_sidebar_on.svg"));
            if ($ilSetting->get("tree_frame") === "right") {
                $this->setVariable("RIGHT", "Right");
            }
            $this->setVariable("ALT_TREE", $lng->txt($this->tree_flat_mode . "view"));
            $this->setVariable("TARGET_TREE", ilFrameTargetInfo::_getFrame("MainContent"));
            $this->parseCurrentBlock();
        }

        $this->setCurrentBlock("tree_icons");
        $this->parseCurrentBlock();
    }

    public function setTreeFlatIcon(string $a_link, string $a_mode): void
    {
        $this->tree_flat_link = $a_link;
        $this->tree_flat_mode = $a_mode;
    }

    /**
     * Add lightbox html
     */
    public function addLightbox(string $a_html, string $a_id): void
    {
        $this->lightbox[$a_id] = $a_html;
    }

    protected function fillLightbox(): void
    {
        $this->setVariable("LIGHTBOX", implode('', $this->lightbox));
    }

    public function addAdminPanelToolbar(ilToolbarGUI $toolb, bool $a_bottom_panel = true, bool $a_arrow = false): void
    {
        $this->admin_panel_commands_toolbar = $toolb;
        $this->admin_panel_arrow = $a_arrow;
        $this->admin_panel_bottom = $a_bottom_panel;
    }

    /**
     * @throws ilTemplateException
     */
    protected function fillAdminPanel(): void
    {
        global $DIC;
        $lng = $DIC->language();

        if ($this->admin_panel_commands_toolbar === null) {
            return;
        }

        $toolb = $this->admin_panel_commands_toolbar;

        // Add arrow if desired.
        if ($this->admin_panel_arrow) {
            $toolb->setLeadingImage(ilUtil::getImagePath("arrow_upright.svg"), $lng->txt("actions"));
        }

        $this->fillPageFormAction();

        // Add top admin bar.
        $this->setCurrentBlock("adm_view_components");
        $this->setVariable("ADM_PANEL1", $toolb->getHTML());
        $this->parseCurrentBlock();

        // Add bottom admin bar if user wants one.
        if ($this->admin_panel_bottom) {
            $this->setCurrentBlock("adm_view_components2");

            // Replace previously set arrow image.
            if ($this->admin_panel_arrow) {
                $toolb->setLeadingImage(ilUtil::getImagePath("arrow_downright.svg"), $lng->txt("actions"));
            }

            $this->setVariable("ADM_PANEL2", $toolb->getHTML());
            $this->parseCurrentBlock();
        }
    }

    public function setPermanentLink(
        string $a_type,
        ?int $a_id,
        string $a_append = "",
        string $a_target = "",
        string $a_title = ""
    ): void {
        $this->permanent_link = [
            "type" => $a_type,
            "id" => $a_id,
            "append" => $a_append,
            "target" => $a_target,
            "title" => $a_title
        ];
    }

    protected function fillPermanentLink(): void
    {
        if (!empty($this->permanent_link)) {
            $plinkgui = new ilPermanentLinkGUI(
                $this->permanent_link["type"],
                $this->permanent_link["id"],
                $this->permanent_link["append"],
                $this->permanent_link["target"]
            );
            if ($this->permanent_link["title"] !== "") {
                $plinkgui->setTitle($this->permanent_link["title"]);
            }
            $this->setVariable("PRMLINK", $plinkgui->getHTML());
        }
    }

    public function resetHeaderBlock(bool $a_reset_header_action = true): void
    {
        $this->setTitle('');
        $this->setTitleIcon('');
        $this->setDescription('');
        $this->setAlertProperties([]);
        $this->enable_fileupload = null;

        // see setFullscreenHeader()
        if ($a_reset_header_action) {
            $this->setHeaderActionMenu('');
        }
    }

    public function setFileUploadRefId(int $a_ref_id): void
    {
        $this->enable_fileupload = $a_ref_id;
    }

    /**
     * @throws ilTemplateException
     */
    public function get(string $part = self::DEFAULT_BLOCK): string
    {
        return $this->template->get($part);
    }

    public function setVariable(string $variable, $value = ''): void
    {
        $this->template->setVariable($variable, $value);
    }

    protected function variableExists(string $a_variablename): bool
    {
        return $this->template->variableExists($a_variablename);
    }

    /**
     * @throws ilTemplateException
     */
    public function setCurrentBlock(string $part = self::DEFAULT_BLOCK): bool
    {
        return $this->template->setCurrentBlock($part);
    }

    /**
     * @throws ilTemplateException
     */
    public function touchBlock(string $block): bool
    {
        return $this->template->touchBlock($block);
    }

    /**
     * @throws ilTemplateException
     */
    public function parseCurrentBlock(string $part = self::DEFAULT_BLOCK): bool
    {
        return $this->template->parseCurrentBlock($part);
    }

    /**
     * @throws ilTemplateException
     */
    public function addBlockFile(string $var, string $block, string $template_name, string $in_module = null): bool
    {
        return $this->template->addBlockFile($var, $block, $template_name, $in_module);
    }

    public function blockExists(string $a_blockname): bool
    {
        return $this->template->blockExists($a_blockname);
    }

    public function getJSFiles(): array
    {
        return $this->js_files_batch;
    }

    public function getCSSFiles(): array
    {
        return $this->css_files;
    }
}
