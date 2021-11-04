<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once("./Services/UICore/lib/html-it/IT.php");
include_once("./Services/UICore/lib/html-it/ITX.php");

/**
* special template class to simplify handling of ITX/PEAR
* @author	Stefan Kesseler <skesseler@databay.de>
* @author	Sascha Hofmann <shofmann@databay.de>
* @version	$Id$
*/
class ilCOPageGlobalTemplate implements ilGlobalTemplateInterface
{
    protected $tree_flat_link = "";
    protected $page_form_action = "";
    protected $permanent_link = false;

    protected $lightbox = array();
    protected $standard_template_loaded = false;

    /**
     * @var	\ilTemplate
     */
    protected $template;

    /**
    * constructor
    * @param	string	$file 		templatefile (mit oder ohne pfad)
    * @param	boolean	$flag1 		remove unknown variables
    * @param	boolean	$flag2 		remove empty blocks
    * @param	boolean	$in_module	should be set to true, if template file is in module subdirectory
    * @param	array	$vars 		variables to replace
    * @access	public
    */
    public function __construct(
        $file,
        $flag1,
        $flag2,
        $in_module = false,
        $vars = "DEFAULT",
        $plugin = false,
        $a_use_cache = true
    ) {
        $this->setBodyClass("std");
        $this->template = new ilTemplate($file, $flag1, $flag2, $in_module, $vars, $plugin, $a_use_cache);
    }

    /**
     * @inheritDoc
     */
    public function printToString() : string
    {
        throw new ilException('not implemented');
    }


    //***********************************
    //
    // FOOTER
    //
    // Used in:
    //  * ilStartUPGUI
    //  * ilTestSubmissionReviewGUI
    //  * ilTestPlayerAbstractGUI
    //  * ilAssQuestionHintRequestGUI
    //
    //***********************************

    private $show_footer = true;

    /**
     * Make the template hide the footer.
     */
    public function hideFooter()
    {
        $this->show_footer = false;
    }

    /**
     * Fill the footer area.
     */
    private function fillFooter()
    {
        global $DIC;

        $ilSetting = $DIC->settings();

        $lng = $DIC->language();

        $ilCtrl = $DIC->ctrl();
        $ilDB = $DIC->database();

        if (!$this->show_footer) {
            return;
        }

        $ftpl = new ilTemplate("tpl.footer.html", true, true, "Services/UICore");

        $php = "";
        if (DEVMODE) {
            $php = ", PHP " . phpversion();
        }
        $ftpl->setVariable("ILIAS_VERSION", $ilSetting->get("ilias_version") . $php);

        $link_items = array();

        // imprint
        include_once "Services/Imprint/classes/class.ilImprint.php";
        if ($_REQUEST["baseClass"] != "ilImprintGUI" && ilImprint::isActive()) {
            include_once "Services/Link/classes/class.ilLink.php";
            $link_items[ilLink::_getStaticLink(0, "impr")] = array($lng->txt("imprint"), true);
        }

        // system support contacts
        include_once("./Modules/SystemFolder/classes/class.ilSystemSupportContactsGUI.php");
        if (($l = ilSystemSupportContactsGUI::getFooterLink()) != "") {
            $link_items[$l] = array(ilSystemSupportContactsGUI::getFooterText(), false);
        }

        if (DEVMODE) {
            if (function_exists("tidy_parse_string")) {
                $link_items[ilUtil::appendUrlParameterString($_SERVER["REQUEST_URI"], "do_dev_validate=xhtml")] = array("Validate", true);
                $link_items[ilUtil::appendUrlParameterString($_SERVER["REQUEST_URI"], "do_dev_validate=accessibility")] = array("Accessibility", true);
            }
        }

        // output translation link
        include_once("Services/Language/classes/class.ilObjLanguageAccess.php");
        if (ilObjLanguageAccess::_checkTranslate() and !ilObjLanguageAccess::_isPageTranslation()) {
            $link_items[ilObjLanguageAccess::_getTranslationLink()] = array($lng->txt('translation'), true);
        }

        $cnt = 0;
        foreach ($link_items as $url => $caption) {
            $cnt++;
            if ($caption[1]) {
                $ftpl->touchBlock("blank");
            }
            if ($cnt < sizeof($link_items)) {
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

            $mem_usage = array();
            if (function_exists("memory_get_usage")) {
                $mem_usage[] =
                    "Memory Usage: " . memory_get_usage() . " Bytes";
            }
            if (function_exists("xdebug_peak_memory_usage")) {
                $mem_usage[] =
                    "XDebug Peak Memory Usage: " . xdebug_peak_memory_usage() . " Bytes";
            }
            $mem_usage[] = round($diff, 4) . " Seconds";

            if (sizeof($mem_usage)) {
                $ftpl->setVariable("MEMORY_USAGE", "<br>" . implode(" | ", $mem_usage));
            }

            if (!empty($_GET["do_dev_validate"]) && $ftpl->blockExists("xhtml_validation")) {
                require_once("Services/XHTMLValidator/classes/class.ilValidatorAdapter.php");
                $template2 = clone($this);
                $ftpl->setCurrentBlock("xhtml_validation");
                $ftpl->setVariable(
                    "VALIDATION",
                    ilValidatorAdapter::validate($template2->get(
                        "DEFAULT",
                        false,
                        false,
                        false,
                        true
                    ), $_GET["do_dev_validate"])
                );
                $ftpl->parseCurrentBlock();
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
                        if ($file != "") {
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
                $ifiles = array();
                $total = 0;
                foreach ($fs as $f) {
                    $ifiles[] = array("file" => $f, "size" => filesize($f));
                    $total += filesize($f);
                }
                $ifiles = ilUtil::sortArray($ifiles, "size", "desc", true);
                foreach ($ifiles as $f) {
                    $ftpl->setCurrentBlock("i_entry");
                    $ftpl->setVariable("I_ENTRY", $f["file"] . " (" . $f["size"] . " Bytes, " . round(100 / $total * $f["size"], 2) . "%)");
                    $ftpl->parseCurrentBlock();
                }
                $ftpl->setCurrentBlock("i_entry");
                $ftpl->setVariable("I_ENTRY", "Total (" . $total . " Bytes, 100%)");
                $ftpl->parseCurrentBlock();
                $ftpl->setCurrentBlock("included_files");
                $ftpl->parseCurrentBlock();
            }
        }

        // BEGIN Usability: Non-Delos Skins can display the elapsed time in the footer
        // The corresponding $ilBench->start invocation is in inc.header.php
        $ilBench = $DIC["ilBench"];
        $ilBench->stop("Core", "ElapsedTimeUntilFooter");
        $ftpl->setVariable(
            "ELAPSED_TIME",
            ", " . number_format($ilBench->getMeasuredTime("Core", "ElapsedTimeUntilFooter"), 1) . ' seconds'
        );
        // END Usability: Non-Delos Skins can display the elapsed time in the footer

        $this->setVariable("FOOTER", $ftpl->get());
    }


    //***********************************
    //
    // MAIN MENU
    //
    //***********************************

    /**
     * @var string
     */
    protected $main_menu;

    /**
     * @var string
     */
    protected $main_menu_spacer;

    private function getMainMenu()
    {
        global $DIC;

        $ilMainMenu = $DIC["ilMainMenu"];

        if ($this->variableExists('MAINMENU')) {
            $ilMainMenu->setLoginTargetPar($this->getLoginTargetPar());
            $this->main_menu = $ilMainMenu->getHTML();
            $this->main_menu_spacer = $ilMainMenu->getSpacerClass();
        }
    }

    private function fillMainMenu()
    {
        global $DIC;
        $tpl = $DIC["tpl"];
        if ($this->variableExists('MAINMENU')) {
            $tpl->setVariable("MAINMENU", $this->main_menu);
            $tpl->setVariable("MAINMENU_SPACER", $this->main_menu_spacer);
        }
    }


    //***********************************
    //
    // HELP
    //
    //***********************************

    /**
     * Init help
     */
    private function initHelp()
    {
        include_once("./Services/Help/classes/class.ilHelpGUI.php");
        //ilHelpGUI::initHelp($this);
    }


    //***********************************
    //
    // MESSAGES
    //
    // setMessage is only used in ilUtil
    //
    //***********************************

    const MESSAGE_TYPE_FAILURE = 'failure';
    const MESSAGE_TYPE_INFO = "info";
    const MESSAGE_TYPE_SUCCESS = "success";
    const MESSAGE_TYPE_QUESTION = "question";

    /**
     * @var array  available Types for Messages
     */
    protected static $message_types = array(
        self::MESSAGE_TYPE_FAILURE,
        self::MESSAGE_TYPE_INFO,
        self::MESSAGE_TYPE_SUCCESS,
        self::MESSAGE_TYPE_QUESTION,
    );

    protected $message = array();

    /**
     * Set a message to be displayed to the user. Please use ilUtil::sendInfo(),
     * ilUtil::sendSuccess() and ilUtil::sendFailure()
     *
     * @param  string  $a_type \ilTemplate::MESSAGE_TYPE_SUCCESS,
     *                         \ilTemplate::MESSAGE_TYPE_FAILURE,,
     *                         \ilTemplate::MESSAGE_TYPE_QUESTION,
     *                         \ilTemplate::MESSAGE_TYPE_INFO
     * @param   string $a_txt  The message to be sent
     * @param bool     $a_keep Keep this message over one redirect
     */
    public function setOnScreenMessage($a_type, $a_txt, $a_keep = false)
    {
        if (!in_array($a_type, self::$message_types) || $a_txt == "") {
            return;
        }
        if (!$a_keep) {
            $this->message[$a_type] = $a_txt;
        } else {
            $_SESSION[$a_type] = $a_txt;
        }
    }

    /**
     * Fill message area.
     */
    private function fillMessage()
    {
        global $DIC;

        $out = "";

        foreach (self::$message_types as $m) {
            $txt = $this->getMessageTextForType($m);

            if ($txt != "") {
                $out .= ilUtil::getSystemMessageHTML($txt, $m);
            }

            $request = $DIC->http()->request();
            $accept_header = $request->getHeaderLine('Accept');
            if (isset($_SESSION[$m]) && $_SESSION[$m] && ($accept_header !== 'application/json')) {
                unset($_SESSION[$m]);
            }
        }

        if ($out != "") {
            $this->setVariable("MESSAGE", $out);
        }
    }


    /**
     * @param $m
     *
     * @return mixed|string
     */
    private function getMessageTextForType($m)
    {
        $txt = "";
        if (isset($_SESSION[$m]) && $_SESSION[$m] != "") {
            $txt = $_SESSION[$m];
        } else {
            if (isset($this->message[$m])) {
                $txt = $this->message[$m];
            }
        }

        return $txt;
    }

    //***********************************
    //
    // JAVASCRIPT files and code
    //
    //***********************************

    /**
     * List of JS-Files that should be included.
     * @var array<int,string>
     */
    protected $js_files = array(0 => "./Services/JavaScript/js/Basic.js");

    /**
     * Stores if a version parameter should be appended to the js-file to force reloading.
     * @var array<string,bool>
     */
    protected $js_files_vp = array("./Services/JavaScript/js/Basic.js" => true);

    /**
     * Stores the order in which js-files should be included.
     * @var array<string,int>
     */
    protected $js_files_batch = array("./Services/JavaScript/js/Basic.js" => 1);

    /**
     * Add a javascript file that should be included in the header.
     */
    public function addJavaScript($a_js_file, $a_add_version_parameter = true, $a_batch = 2)
    {
        // three batches currently
        if ($a_batch < 1 || $a_batch > 3) {
            $a_batch = 2;
        }

        // ensure jquery files being loaded first
        if (is_int(strpos($a_js_file, "Services/jQuery")) ||
            is_int(strpos($a_js_file, "/jquery.js")) ||
            is_int(strpos($a_js_file, "/jquery-min.js"))) {
            $a_batch = 0;
        }

        if (!in_array($a_js_file, $this->js_files)) {
            $this->js_files[] = $a_js_file;
            $this->js_files_vp[$a_js_file] = $a_add_version_parameter;
            $this->js_files_batch[$a_js_file] = $a_batch;
        }
    }

    /**
     * Add on load code
     */
    public function addOnLoadCode($a_code, $a_batch = 2)
    {
        // three batches currently
        if ($a_batch < 1 || $a_batch > 3) {
            $a_batch = 2;
        }
        $this->on_load_code[$a_batch][] = $a_code;
    }

    /**
     * Get js onload code for ajax calls
     *
     * @return string
     */
    public function getOnLoadCodeForAsynch()
    {
        $js = "";
        for ($i = 1; $i <= 3; $i++) {
            if (is_array($this->on_load_code[$i])) {
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
    }

    // REMOVAL CANDIDATE
    // Usage locations:
    //    - latex.php
    /**
     * Reset javascript files
     */
    public function resetJavascript()
    {
        $this->js_files = array();
        $this->js_files_vp = array();
        $this->js_files_batch = array();
    }

    // PRIVATE CANDIDATE
    // Usage locations:
    //    - ilPageObjectGUI
    //    - ilStartUpGUI
    //    - ilObjPortfolioGUI
    //    - latex.php
    public function fillJavaScriptFiles($a_force = false)
    {
        global $DIC;

        $ilSetting = $DIC->settings();

        if (is_object($ilSetting)) {		// maybe this one can be removed
            $vers = "vers=" . str_replace(array(".", " "), "-", $ilSetting->get("ilias_version"));

            if (DEVMODE) {
                $vers .= '-' . time();
            }
        }
        if ($this->blockExists("js_file")) {
            // three batches
            for ($i = 0; $i <= 3; $i++) {
                reset($this->js_files);
                foreach ($this->js_files as $file) {
                    if ($this->js_files_batch[$file] == $i) {
                        if (is_file($file) || substr($file, 0, 4) == "http" || substr($file, 0, 2) == "//" || $a_force) {
                            $this->fillJavascriptFile($file, $vers);
                        } elseif (substr($file, 0, 2) == './') { // #13962
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

    /**
     * Fill add on load code
     */
    private function fillOnLoadCode()
    {
        for ($i = 1; $i <= 3; $i++) {
            if (is_array($this->on_load_code[$i])) {
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

    /**
     * @param string $file
     * @param string $vers
     */
    protected function fillJavascriptFile($file, $vers)
    {
        $this->setCurrentBlock("js_file");
        if ($this->js_files_vp[$file]) {
            $this->setVariable("JS_FILE", ilUtil::appendUrlParameterString($file, $vers));
        } else {
            $this->setVariable("JS_FILE", $file);
        }
        $this->parseCurrentBlock();
    }


    //***********************************
    //
    // CSS files and code
    //
    //***********************************

    /**
     * Stores CSS-files to be included.
     * @var array
     */
    protected $css_files = array();

    /**
     * Stores CSS to be included directly.
     * array
     */
    protected $inline_css = array();

    /**
     * Add a css file that should be included in the header.
     */
    public function addCss($a_css_file, $media = "screen")
    {
        if (!array_key_exists($a_css_file . $media, $this->css_files)) {
            $this->css_files[$a_css_file . $media] = array("file" => $a_css_file, "media" => $media);
        }
    }

    // REMOVAL CANDIDATE
    // Usage locations:
    //    - ilDclRecordEditGUI
    //    - ilObjStyleSheetGUI
    /**
     * Add a css file that should be included in the header.
     */
    public function addInlineCss($a_css, $media = "screen")
    {
        $this->inline_css[] = array("css" => $a_css, "media" => $media);
    }

    // PRIVATE CANDIDATE
    // Usage locations:
    //    - ilPageObjectGUI
    //	  - ilDclDetailedViewGUI
    //    - ilStartUpGUI
    /**
     * Fill in the css file tags
     *
     * @param boolean $a_force
     */
    public function fillCssFiles($a_force = false)
    {
        if (!$this->blockExists("css_file")) {
            return;
        }
        foreach ($this->css_files as $css) {
            $filename = $css["file"];
            if (strpos($filename, "?") > 0) {
                $filename = substr($filename, 0, strpos($filename, "?"));
            }
            if (is_file($filename) || $a_force) {
                $this->setCurrentBlock("css_file");
                $this->setVariable("CSS_FILE", $css["file"]);
                $this->setVariable("CSS_MEDIA", $css["media"]);
                $this->parseCurrentBlock();
            }
        }
    }

    // REMOVAL CANDIDATE:
    // Usage locations:
    //    - ilObjMediaPoolGUI
    //    - ilAttendanceList
    //    - ilObjPortfolioGUI
    //    - ilSCORM2004ScoGUI
    //    - ilTestSubmissionReviewGUI
    //    - ilTestPlayerAbstractGUI
    //    - ilAssQuestionHintRequestGUI
    //    - ilWorkspaceFolderExplorer
    public function setBodyClass($a_class = "")
    {
        $this->body_class = $a_class;
    }

    private function fillBodyClass()
    {
        if ($this->body_class != "" && $this->blockExists("body_class")) {
            $this->setCurrentBlock("body_class");
            $this->setVariable("BODY_CLASS", $this->body_class);
            $this->parseCurrentBlock();
        }
    }

    /**
     * Reset css files
     */
    private function resetCss()
    {
        $this->css_files = array();
    }

    /**
     * Fill in the inline css
     *
     * @param boolean $a_force
     */
    private function fillInlineCss()
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

    /**
     * Fill Content Style
     */
    private function fillNewContentStyle()
    {
        $this->setVariable(
            "LOCATION_NEWCONTENT_STYLESHEET_TAG",
            '<link rel="stylesheet" type="text/css" href="' .
            ilUtil::getNewContentStyleSheetLocation()
            . '" />'
        );
    }


    //***********************************
    //
    // ILIAS STANDARD TEMPLATE
    // which is responsible for the look
    // i.e. a title, tabs, ...
    //
    //***********************************

    /**
     * This loads the standard template "tpl.adm_content.html" and
     * "tpl.statusline.html" the CONTENT and STATUSLINE placeholders
     * if they are not already loaded.
     */
    public function loadStandardTemplate()
    {
        if ($this->standard_template_loaded) {
            return;
        }

        // always load jQuery
        include_once("./Services/jQuery/classes/class.iljQueryUtil.php");
        iljQueryUtil::initjQuery();
        iljQueryUtil::initjQueryUI();

        // always load ui framework
        include_once("./Services/UICore/classes/class.ilUIFramework.php");
        ilUIFramework::init();

        $this->addBlockFile("CONTENT", "content", "tpl.adm_content.html");
        $this->addBlockFile("STATUSLINE", "statusline", "tpl.statusline.html");

        $this->standard_template_loaded = true;
    }


    //***********************************
    //
    // HEADER in standard template
    //
    //***********************************

    protected $header_page_title = "";
    protected $title = "";
    protected $title_desc = "";
    protected $title_alerts = array();
    protected $header_action;

    /**
     * Sets title in standard template.
     *
     * Will override the header_page_title.
     */
    public function setTitle($a_title, $hidden = false)
    {
        $this->title = $a_title;
        $this->header_page_title = $a_title;
    }

    /**
     * Sets descripton below title in standard template.
     */
    public function setDescription($a_descr)
    {
        $this->title_desc = $a_descr;
    }

    /**
     * set title icon
     */
    public function setTitleIcon($a_icon_path, $a_icon_desc = "")
    {
        $this->icon_desc = $a_icon_desc;
        $this->icon_path = $a_icon_path;
    }

    /**
     * Set alert properties
     * @param array $a_props
     * @return void
     */
    public function setAlertProperties(array $a_props)
    {
        $this->title_alerts = $a_props;
    }

    /**
     * Clear header
     */
    public function clearHeader()
    {
        $this->setTitle("");
        $this->setTitleIcon("");
        $this->setDescription("");
        $this->setAlertProperties(array());
    }

    // REMOVAL CANDIDATE
    // Usage locations:
    //    - ilCalendarPresentationGUI
    //    - ilContainerGUI
    //    - ilObjDataCollectionGUI
    //    - ilDashboardGUI
    //    - ilObjPortfolioTemplateGUI
    //    - ilWikiPageGUI
    //    - ilObjWikiGUI
    /**
     * Set header action menu
     *
     * @param string $a_gui $a_header
     */
    public function setHeaderActionMenu($a_header)
    {
        $this->header_action = $a_header;
    }

    // REMOVAL CANDIDATE
    // Usage locations:
    //    - ilObjLanguageExtGUI
    //    - ilTestServiceGUI
    //    - ilWikiPageGUI
    /**
     * Sets the title of the page (for browser window).
     */
    public function setHeaderPageTitle($a_title)
    {
        $this->header_page_title = $a_title;
    }

    /**
    * Fill header
    */
    private function fillHeader()
    {
        global $DIC;

        $lng = $DIC->language();

        $icon = false;
        if ($this->icon_path != "") {
            $icon = true;
            $this->setCurrentBlock("header_image");
            if ($this->icon_desc != "") {
                $this->setVariable("IMAGE_DESC", $lng->txt("icon") . " " . $this->icon_desc);
                $this->setVariable("IMAGE_ALT", $lng->txt("icon") . " " . $this->icon_desc);
            }

            $this->setVariable("IMG_HEADER", $this->icon_path);
            $this->parseCurrentBlock();
            $header = true;
        }

        if ($this->title != "") {
            $title = ilUtil::stripScriptHTML($this->title);
            $this->setVariable("HEADER", $title);

            $header = true;
        }

        if ($header) {
            $this->setCurrentBlock("header_image");
            $this->parseCurrentBlock();
        }

        if ($this->title_desc != "") {
            $this->setCurrentBlock("header_desc");
            $this->setVariable("H_DESCRIPTION", $this->title_desc);
            $this->parseCurrentBlock();
        }

        $header = $this->getHeaderActionMenu();
        if ($header) {
            $this->setCurrentBlock("head_action_inner");
            $this->setVariable("HEAD_ACTION", $header);
            $this->parseCurrentBlock();
        }

        if (count((array) $this->title_alerts)) {
            foreach ($this->title_alerts as $alert) {
                $this->setCurrentBlock('header_alert');
                if (!($alert['propertyNameVisible'] === false)) {
                    $this->setVariable('H_PROP', $alert['property'] . ':');
                }
                $this->setVariable('H_VALUE', $alert['value']);
                $this->parseCurrentBlock();
            }
        }

        // add file upload drop zone in header
        if ($this->enable_fileupload != null) {
            $ref_id = $this->enable_fileupload;
            $upload_id = "dropzone_" . $ref_id;

            include_once("./Services/FileUpload/classes/class.ilFileUploadGUI.php");
            $upload = new ilFileUploadGUI($upload_id, $ref_id, true);

            $this->setVariable("FILEUPLOAD_DROPZONE_ID", " id=\"$upload_id\"");

            $this->setCurrentBlock("header_fileupload");
            $this->setVariable("HEADER_FILEUPLOAD_SCRIPT", $upload->getHTML());
            $this->parseCurrentBlock();
        }
    }

    /**
     * Get header action menu
     *
     * @return int ref id
     */
    private function getHeaderActionMenu()
    {
        return $this->header_action;
    }


    //***********************************
    //
    // LOCATOR in standard template
    //
    //***********************************

    /**
    * Insert locator.
    */
    public function setLocator()
    {
        global $DIC;

        $ilMainMenu = $DIC["ilMainMenu"];
        $ilLocator = $DIC["ilLocator"];

        $ilPluginAdmin = $DIC["ilPluginAdmin"];

        // blog/portfolio
        if ($ilMainMenu->getMode() == ilMainMenuGUI::MODE_TOPBAR_REDUCED ||
            $ilMainMenu->getMode() == ilMainMenuGUI::MODE_TOPBAR_ONLY) {
            $this->setVariable("LOCATOR", "");
            return;
        }

        $html = "";
        if (is_object($ilPluginAdmin)) {
            include_once("./Services/UIComponent/classes/class.ilUIHookProcessor.php");
            $uip = new ilUIHookProcessor(
                "Services/Locator",
                "main_locator",
                array("locator_gui" => $ilLocator)
            );
            if (!$uip->replaced()) {
                $html = $ilLocator->getHTML();
            }
            $html = $uip->getHTML($html);
        } else {
            $html = $ilLocator->getHTML();
        }

        $this->setVariable("LOCATOR", $html);
    }


    //***********************************
    //
    // TABS in standard template
    //
    //***********************************

    /**
     * @var	string
     */
    protected $tabs_html = "";

    /**
     * @var string
     */
    protected $sub_tabs_html = "";

    /**
    * sets tabs in standard template
    */
    public function setTabs($a_tabs_html)
    {
        if ($a_tabs_html != "" && $this->blockExists("tabs_outer_start")) {
            $this->touchBlock("tabs_outer_start");
            $this->touchBlock("tabs_outer_end");
            $this->touchBlock("tabs_inner_start");
            $this->touchBlock("tabs_inner_end");
            $this->setVariable("TABS", $a_tabs_html);
        }
    }

    /**
     * sets subtabs in standard template
     */
    public function setSubTabs($a_tabs_html)
    {
        $this->setVariable("SUB_TABS", $a_tabs_html);
    }

    private function fillTabs()
    {
        if ($this->blockExists("tabs_outer_start")) {
            $this->touchBlock("tabs_outer_start");
            $this->touchBlock("tabs_outer_end");
            $this->touchBlock("tabs_inner_start");
            $this->touchBlock("tabs_inner_end");

            if ($this->tabs_html != "") {
                $this->setVariable("TABS", $this->tabs_html);
            }
            $this->setVariable("SUB_TABS", $this->sub_tabs_html);
        }
    }

    private function getTabsHTML()
    {
        global $DIC;

        $ilTabs = $DIC["ilTabs"];

        if ($this->blockExists("tabs_outer_start")) {
            $this->sub_tabs_html = $ilTabs->getSubTabHTML();
            $this->tabs_html = $ilTabs->getHTML(true);
        }
    }


    //***********************************
    //
    // COLUMN LAYOUT in standard template
    //
    //***********************************

    /**
     * Sets content for standard template.
     */
    public function setContent($a_html)
    {
        if ($a_html != "") {
            $this->main_content = $a_html;
        }
    }

    /**
     * Sets content of left column.
     */
    public function setLeftContent($a_html)
    {
        $this->left_content = $a_html;
    }

    /**
     * Sets content of left navigation column.
     */
    public function setLeftNavContent($a_content)
    {
        $this->left_nav_content = $a_content;
    }

    /**
     * Fill left navigation frame
     */
    private function fillLeftNav()
    {
        if (trim($this->left_nav_content) != "") {
            $this->setCurrentBlock("left_nav");
            $this->setVariable("LEFT_NAV_CONTENT", $this->left_nav_content);
            $this->parseCurrentBlock();
            $this->touchBlock("left_nav_space");
        }
    }

    /**
     * Sets content of right column.
     */
    public function setRightContent($a_html)
    {
        $this->right_content = $a_html;
    }

    private function setCenterColumnClass()
    {
        if (!$this->blockExists("center_col_width")) {
            return;
        }
        $center_column_class = "";
        if (trim($this->right_content) != "" && trim($this->left_content) != "") {
            $center_column_class = "two_side_col";
        } elseif (trim($this->right_content) != "" || trim($this->left_content) != "") {
            $center_column_class = "one_side_col";
        }

        switch ($center_column_class) {
            case "one_side_col": $center_column_class = "col-sm-9"; break;
            case "two_side_col": $center_column_class = "col-sm-6"; break;
            default: $center_column_class = "col-sm-12"; break;
        }
        if (trim($this->left_content) != "") {
            $center_column_class .= " col-sm-push-3";
        }

        $this->setCurrentBlock("center_col_width");
        $this->setVariable("CENTER_COL", $center_column_class);
        $this->parseCurrentBlock();
    }

    private function fillMainContent()
    {
        if (trim($this->main_content) != "") {
            $this->setVariable("ADM_CONTENT", $this->main_content);
        }
    }

    private function fillLeftContent()
    {
        if (trim($this->left_content) != "") {
            $this->setCurrentBlock("left_column");
            $this->setVariable("LEFT_CONTENT", $this->left_content);
            $left_col_class = (trim($this->right_content) == "")
                ? "col-sm-3 col-sm-pull-9"
                : "col-sm-3 col-sm-pull-6";
            $this->setVariable("LEFT_COL_CLASS", $left_col_class);
            $this->parseCurrentBlock();
        }
    }

    private function fillRightContent()
    {
        if (trim($this->right_content) != "") {
            $this->setCurrentBlock("right_column");
            $this->setVariable("RIGHT_CONTENT", $this->right_content);
            $this->parseCurrentBlock();
        }
    }


    //***********************************
    //
    // TOOLBAR in standard template
    //
    //***********************************

    private function fillToolbar()
    {
        global $DIC;

        $ilToolbar = $DIC["ilToolbar"];
        ;

        $thtml = $ilToolbar->getHTML();
        if ($thtml != "") {
            $this->setCurrentBlock("toolbar_buttons");
            $this->setVariable("BUTTONS", $thtml);
            $this->parseCurrentBlock();
        }
    }

    // SPECIAL REQUIREMENTS
    //
    // Stuff that is only used by a little other classes.

    /**
     * Add current user language to meta tags
     */
    private function fillContentLanguage()
    {
        global $DIC;
        $lng = $DIC->language();

        if (is_object($lng)) {
            $this->setVariable('META_CONTENT_LANGUAGE', $lng->getContentLanguage());
            $this->setVariable('LANGUAGE_DIRECTION', $lng->getTextDirection());
        }
    }

    private function fillWindowTitle()
    {
        global $DIC;

        $ilSetting = $DIC->settings();

        if ($this->header_page_title != "") {
            $title = ilUtil::stripScriptHTML($this->header_page_title);
            $this->setVariable("PAGETITLE", "- " . $title);
        }

        if ($ilSetting->get('short_inst_name') != "") {
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

    // REMOVAL CANDIDATE
    // Usage locations:
    //    - ilLuceneAdvancedSearchGUI
    //    - ilLuceneSearchGUI
    //    - ilContainerGUI
    public function setPageFormAction($a_action)
    {
        $this->page_form_action = $a_action;
    }

    private function fillPageFormAction()
    {
        if ($this->page_form_action != "") {
            $this->setCurrentBlock("page_form_start");
            $this->setVariable("PAGE_FORM_ACTION", $this->page_form_action);
            $this->parseCurrentBlock();
            $this->touchBlock("page_form_end");
        }
    }


    // REMOVAL CANDIDATE
    // Usage locations:
    //    - ilObjForumGUI
    //    - ilObjPortfolioBaseGUI
    //    - ilWikiPageGUI
    /**
     * Set target parameter for login (public sector).
     * This is used by the main menu
     */
    public function setLoginTargetPar($a_val)
    {
        $this->login_target_par = $a_val;
    }

    /**
     * Get target parameter for login
     */
    private function getLoginTargetPar()
    {
        return $this->login_target_par;
    }


    // REMOVAL CANDIDATE:
    // Usage locations:
    //    - ilLPListOfObjectsGUI
    //	  - ilExport
    //    - ilLMEditorGUI
    //    - ilObjPortfolioGUI
    //    - ilPortfolioHTMLExport
    //    - ilForumExportGUI
    //    - ilObjWikiGUI.php
    //    - ilWikiHTMLExport
    //    - ilScormSpecialPagesTableGUI
    //
    // Also this seems to be somehow similar to the stuff going on in printToStdout.
    // Maybe we could unify them.
    /**
     * @param	string
     * @return	string
     */
    public function getSpecial(
        $part = "DEFAULT",
        $add_error_mess = false,
        $handle_referer = false,
        $add_ilias_footer = false,
        $add_standard_elements = false,
        $a_main_menu = true,
        $a_tabs = true
    ) {
        global $DIC;

        if ($add_error_mess) {
            $this->fillMessage();
        }

        if ($add_ilias_footer) {
            $this->fillFooter();
        }

        // set standard parts (tabs and title icon)
        if ($add_standard_elements) {
            if ($this->blockExists("content") && $a_tabs) {
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
            $this->setCurrentBlock("DEFAULT");
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

        if ($handle_referer) {
            $this->handleReferer();
        }

        if ($part == "DEFAULT") {
            $html = $this->template->get();
        } else {
            $html = $this->template->get($part);
        }

        // save language usages as late as possible
        ilObjLanguageAccess::_saveUsages();

        return $html;
    }

    /**
     * @param string|bool $part
     * @param bool   $a_fill_tabs fill template variable {TABS} with content of ilTabs
     * @param bool   $a_skip_main_menu
     */
    public function printToStdout($part = "DEFAULT", $a_fill_tabs = true, $a_skip_main_menu = false)
    {
        global $DIC;

        $http = $DIC->http();
        switch ($http->request()->getHeaderLine('Accept')) {
            case 'application/json':
                $string = json_encode([
                    self::MESSAGE_TYPE_SUCCESS => is_null($this->message[self::MESSAGE_TYPE_FAILURE]),
                    'message' => '',
                ]);
                $stream = \ILIAS\Filesystem\Stream\Streams::ofString($string);
                $http->saveResponse($http->response()->withBody($stream));
                $http->sendResponse();
                exit;
            default:
                // include yahoo dom per default
                include_once("./Services/YUI/classes/class.ilYuiUtil.php");
                ilYuiUtil::initDom();

                header('P3P: CP="CURa ADMa DEVa TAIa PSAa PSDa IVAa IVDa OUR BUS IND UNI COM NAV INT CNT STA PRE"');
                header("Content-type: text/html; charset=UTF-8");

                $this->fillMessage();

                // display ILIAS footer
                if ($part !== false) {
                    $this->fillFooter();
                }

                // set standard parts (tabs and title icon)
                $this->fillBodyClass();
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
                    $this->setCurrentBlock("DEFAULT");
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
                        $this->fillTabs();
                        $this->fillMainContent();
                        $this->fillMainMenu();
                        $this->fillLightbox();
                        $this->parseCurrentBlock();
                    }
                }

                if ($part == "DEFAULT" or is_bool($part)) {
                    $html = $this->template->getUnmodified();
                } else {
                    $html = $this->template->getUnmodified($part);
                }

                // Modification of html is done inline here and can't be done
                // by ilTemplate, because the "phase" is template_show in this
                // case here.
                $ilPluginAdmin = $DIC["ilPluginAdmin"];
                $pl_names = $ilPluginAdmin->getActivePluginsForSlot(IL_COMP_SERVICE, "UIComponent", "uihk");
                foreach ($pl_names as $pl) {
                    $ui_plugin = ilPluginAdmin::getPluginObject(IL_COMP_SERVICE, "UIComponent", "uihk", $pl);
                    $gui_class = $ui_plugin->getUIClassInstance();

                    $resp = $gui_class->getHTML(
                        "",
                        "template_show",
                        array("tpl_id" => $this->tplIdentifier, "tpl_obj" => $this, "html" => $html)
                    );

                    if ($resp["mode"] != ilUIHookPluginGUI::KEEP) {
                        $html = $gui_class->modifyHTML($html, $resp);
                    }
                }

                // save language usages as late as possible
                ilObjLanguageAccess::_saveUsages();

                print $html;

                $this->handleReferer();
                break;
        }
    }


    /**
    * TODO: this is nice, but shouldn't be done here
    * (-> maybe at the end of ilias.php!?, alex)
    */
    private function handleReferer()
    {
        if (((substr(strrchr($_SERVER["PHP_SELF"], "/"), 1) != "error.php")
            && (substr(strrchr($_SERVER["PHP_SELF"], "/"), 1) != "adm_menu.php")
            && (substr(strrchr($_SERVER["PHP_SELF"], "/"), 1) != "chat.php"))) {
            // referer is modified if query string contains cmd=gateway and $_POST is not empty.
            // this is a workaround to display formular again in case of error and if the referer points to another page
            $url_parts = @parse_url($_SERVER["REQUEST_URI"]);
            if (!$url_parts) {
                $protocol = (isset($_SERVER['HTTPS']) ? 'https' : 'http') . '://';
                $host = $_SERVER['HTTP_HOST'];
                $path = $_SERVER['REQUEST_URI'];
                $url_parts = @parse_url($protocol . $host . $path);
            }

            if (isset($url_parts["query"]) && preg_match("/cmd=gateway/", $url_parts["query"]) && (isset($_POST["cmd"]["create"]))) {
                foreach ($_POST as $key => $val) {
                    if (is_array($val)) {
                        $val = key($val);
                    }

                    $str .= "&" . $key . "=" . $val;
                }

                $_SESSION["referer"] = preg_replace("/cmd=gateway/", substr($str, 1), $_SERVER["REQUEST_URI"]);
                $_SESSION['referer_ref_id'] = (int) $_GET['ref_id'];
            } elseif (isset($url_parts["query"]) && preg_match("/cmd=post/", $url_parts["query"]) && (isset($_POST["cmd"]["create"]))) {
                foreach ($_POST as $key => $val) {
                    if (is_array($val)) {
                        $val = key($val);
                    }

                    $str .= "&" . $key . "=" . $val;
                }

                $_SESSION["referer"] = preg_replace("/cmd=post/", substr($str, 1), $_SERVER["REQUEST_URI"]);
                if (isset($_GET['ref_id'])) {
                    $_SESSION['referer_ref_id'] = (int) $_GET['ref_id'];
                } else {
                    $_SESSION['referer_ref_id'] = 0;
                }
            } else {
                $_SESSION["referer"] = $_SERVER["REQUEST_URI"];
                if (isset($_GET['ref_id'])) {
                    $_SESSION['referer_ref_id'] = (int) $_GET['ref_id'];
                } else {
                    $_SESSION['referer_ref_id'] = 0;
                }
            }

            unset($_SESSION["error_post_vars"]);
        }
    }


    /**
    * Accessibility focus for screen readers
    */
    private function fillScreenReaderFocus()
    {
        global $DIC;

        $ilUser = $DIC->user();

        if (is_object($ilUser) && $ilUser->getPref("screen_reader_optimization") && $this->blockExists("sr_focus")) {
            $this->touchBlock("sr_focus");
        }
    }

    /**
    * Fill side icons (upper icon, tree icon, webfolder icon)
    */
    private function fillSideIcons()
    {
        global $DIC;

        $ilSetting = $DIC->settings();

        $lng = $DIC->language();

        // tree/flat icon
        if ($this->tree_flat_link != "") {
            if ($this->left_nav_content != "") {
                $this->touchBlock("tree_lns");
            }

            $this->setCurrentBlock("tree_mode");
            $this->setVariable("LINK_MODE", $this->tree_flat_link);
            if ($ilSetting->get("tree_frame") == "right") {
                if ($this->tree_flat_mode == "tree") {
                    $this->setVariable("IMG_TREE", ilUtil::getImagePath("icon_sidebar_on.svg"));
                    $this->setVariable("RIGHT", "Right");
                } else {
                    $this->setVariable("IMG_TREE", ilUtil::getImagePath("icon_sidebar_on.svg"));
                    $this->setVariable("RIGHT", "Right");
                }
            } else {
                if ($this->tree_flat_mode == "tree") {
                    $this->setVariable("IMG_TREE", ilUtil::getImagePath("icon_sidebar_on.svg"));
                } else {
                    $this->setVariable("IMG_TREE", ilUtil::getImagePath("icon_sidebar_on.svg"));
                }
            }
            $this->setVariable("ALT_TREE", $lng->txt($this->tree_flat_mode . "view"));
            $this->setVariable("TARGET_TREE", ilFrameTargetInfo::_getFrame("MainContent"));
            include_once("./Services/Accessibility/classes/class.ilAccessKeyGUI.php");
            $this->setVariable(
                "TREE_ACC_KEY",
                ilAccessKeyGUI::getAttribute(($this->tree_flat_mode == "tree")
                    ? ilAccessKey::TREE_ON
                    : ilAccessKey::TREE_OFF)
            );
            $this->parseCurrentBlock();
        }

        $this->setCurrentBlock("tree_icons");
        $this->parseCurrentBlock();
    }

    /**
    * set tree/flat icon
    * @param	string		link target
    * @param	strong		mode ("tree" | "flat")
    */
    public function setTreeFlatIcon($a_link, $a_mode)
    {
        $this->tree_flat_link = $a_link;
        $this->tree_flat_mode = $a_mode;
    }


    /**
     * Add lightbox html
     */
    public function addLightbox($a_html, $a_id)
    {
        $this->lightbox[$a_id] = $a_html;
    }

    /**
     * Fill lightbox content
     *
     * @param
     * @return
     */
    private function fillLightbox()
    {
        $html = "";

        foreach ($this->lightbox as $lb) {
            $html .= $lb;
        }
        $this->setVariable("LIGHTBOX", $html);
    }

    // ADMIN PANEL
    //
    // Only used in ilContainerGUI
    //
    // An "Admin Panel" is that toolbar thingy that could be found on top and bottom
    // of a repository listing when editing objects in a container gui.

    protected $admin_panel_commands_toolbar = null;
    protected $admin_panel_arrow = null;
    protected $admin_panel_bottom = null;

    /**
     * Add admin panel commands as toolbar
     *
     * @param ilToolbarGUI $toolb
     * @param bool $a_top_only
     */
    public function addAdminPanelToolbar(ilToolbarGUI $toolb, $a_bottom_panel = true, $a_arrow = false)
    {
        $this->admin_panel_commands_toolbar = $toolb;
        $this->admin_panel_arrow = $a_arrow;
        $this->admin_panel_bottom = $a_bottom_panel;
    }

    /**
    * Put admin panel into template:
    * - creation selector
    * - admin view on/off button
    */
    private function fillAdminPanel()
    {
        global $DIC;
        $lng = $DIC->language();

        if ($this->admin_panel_commands_toolbar === null) {
            return;
        }

        $toolb = $this->admin_panel_commands_toolbar;
        assert($toolbar instanceof \ilToolbarGUI);

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

    public function setPermanentLink($a_type, $a_id, $a_append = "", $a_target = "", $a_title = "")
    {
        $this->permanent_link = array(
            "type" => $a_type,
            "id" => $a_id,
            "append" => $a_append,
            "target" => $a_target,
            "title" => $a_title);
    }

    /**
    * Fill in permanent link
    */
    private function fillPermanentLink()
    {
        if (is_array($this->permanent_link)) {
            include_once("./Services/PermanentLink/classes/class.ilPermanentLinkGUI.php");
            $plinkgui = new ilPermanentLinkGUI(
                $this->permanent_link["type"],
                $this->permanent_link["id"],
                $this->permanent_link["append"],
                $this->permanent_link["target"]
            );
            if ($this->permanent_link["title"] != "") {
                $plinkgui->setTitle($this->permanent_link["title"]);
            }
            $this->setVariable("PRMLINK", $plinkgui->getHTML());
        }
    }

    /**
     * Reset all header properties: title, icon, description, alerts, action menu
     */
    public function resetHeaderBlock($a_reset_header_action = true)
    {
        $this->setTitle(null);
        $this->setTitleIcon(null);
        $this->setDescription(null);
        $this->setAlertProperties(array());
        $this->enableDragDropFileUpload(null);

        // see setFullscreenHeader()
        if ($a_reset_header_action) {
            $this->setHeaderActionMenu(null);
        }
    }

    /**
     * Enables the file upload into this object by dropping a file.
     */
    public function enableDragDropFileUpload($a_ref_id)
    {
        $this->enable_fileupload = $a_ref_id;
    }


    // TEMPLATING AND GLOBAL RENDERING
    //
    // Forwards to ilTemplate-member.

    /**
     * @param	string
     * @return	string
     */
    public function get($part = "DEFAULT")
    {
        return $this->template->get($part);
    }

    public function setVariable($variable, $value = '')
    {
        return $this->template->setVariable($variable, $value);
    }

    private function variableExists($a_variablename)
    {
        return $this->template->variableExists($a_variablename);
    }

    /**
    * @access	public
    * @param	string
    * @return	???
    */
    public function setCurrentBlock($part = "DEFAULT")
    {
        return $this->template->setCurrentBlock($part);
    }

    /**
    * overwrites ITX::touchBlock.
    * @access	public
    * @param	string
    * @return	???
    */
    public function touchBlock($block)
    {
        return $this->template->touchBlock($block);
    }

    /**
    * Überladene Funktion, die auf den aktuelle Block vorher noch ein replace ausführt
    * @access	public
    * @param	string
    * @return	string
    */
    public function parseCurrentBlock($part = "DEFAULT")
    {
        return $this->template->parseCurrentBlock($part);
    }

    /**
    * overwrites ITX::addBlockFile
    * @access	public
    * @param	string
    * @param	string
    * @param	string		$tplname		template name
    * @param	boolean		$in_module		should be set to true, if template file is in module subdirectory
    * @return	boolean/string
    */
    public function addBlockFile($var, $block, $tplname, $in_module = false)
    {
        return $this->template->addBlockFile($var, $block, $tplname, $in_module);
    }

    /**
    * check if block exists in actual template
    * @access	private
    * @param string blockname
    * @return	boolean
    */
    public function blockExists($a_blockname)
    {
        return $this->template->blockExists($a_blockname);
    }
}
