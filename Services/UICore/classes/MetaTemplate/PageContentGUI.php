<?php namespace ILIAS\Services\UICore\MetaTemplate;

use ilTemplate;

/**
 * Class PageContentGUI
 *
 * @author Fabian Schmid <fs@studer-raimann.ch>
 */
class PageContentGUI
{

    /**
     * @var ilTemplate
     */
    private $template_file;

    /**
     * @var bool
     */
    private $hiddenTitle = false;

    /**
     * @inheritDoc
     */
    public function __construct(string $file, bool $flag1, bool $flag2, bool $in_module = false, $vars = "DEFAULT", bool $plugin = false, bool $a_use_cache = true)
    {
        $this->template_file = new ilTemplate($file, $flag1, $flag2, $in_module, $vars, $plugin, $a_use_cache);
    }


    const MESSAGE_TYPE_FAILURE = 'failure';
    const MESSAGE_TYPE_INFO = "info";
    const MESSAGE_TYPE_SUCCESS = "success";
    const MESSAGE_TYPE_QUESTION = "question";
    /**
     * @var array  available Types for Messages
     */
    protected static $message_types
        = array(
            self::MESSAGE_TYPE_FAILURE,
            self::MESSAGE_TYPE_INFO,
            self::MESSAGE_TYPE_SUCCESS,
            self::MESSAGE_TYPE_QUESTION,
        );
    // Not used (derived from old ilGlobalTemplate)
    private $tree_flat_link;
    private $standard_template_loaded;
    private $show_footer;
    private $main_menu;
    private $main_menu_spacer;
    private $js_files;
    private $js_files_vp;
    private $js_files_batch;
    private $css_files;
    private $inline_css;
    private $tree_flat_mode;
    private $body_class;
    private $on_load_code;
    // needed for content-area
    private $page_form_action;
    private $permanent_link;
    private $main_content;
    private $lightbox = [];
    private $translation_linked;
    private $message;
    private $header_page_title;
    private $title;
    private $title_desc;
    private $title_alerts;
    private $header_action;
    private $tabs_html;
    private $sub_tabs_html;
    private $admin_panel_commands_toolbar;
    private $admin_panel_arrow;
    private $admin_panel_bottom;
    private $right_content;
    private $left_content;
    private $icon_path;
    private $icon_desc;
    private $enable_fileupload;
    private $filter;
    private $banner;
    //
    // Needed Methods for communication from the outside
    //

    /**
     * @param      $var
     * @param      $block
     * @param      $tplname
     * @param bool $in_module
     *
     * @return bool
     */
    public function addBlockFile($var, $block, $tplname, $in_module = false)
    {
        return $this->template_file->addBlockFile($var, $block, $tplname, $in_module);
    }


    /**
     * @param $blockname
     *
     * @return bool
     */
    public function blockExists($blockname)
    {
        return $this->template_file->blockExists($blockname);
    }


    /**
     * @param $block
     */
    public function removeBlockData($block)
    {
        $this->template_file->removeBlockData($block);
    }


    /**
     * @param        $variable
     * @param string $value
     */
    public function setVariable($variable, $value = '')
    {
        $this->template_file->setVariable($variable, $value);
    }


    /**
     * @inheritDoc
     */
    public function setCurrentBlock($part = "DEFAULT")
    {
        $this->template_file->setCurrentBlock($part);
    }


    /**
     * @inheritDoc
     */
    public function touchBlock($block)
    {
        $this->template_file->touchBlock($block);
    }


    /**
     * @inheritDoc
     */
    public function parseCurrentBlock($part = "DEFAULT")
    {
        $this->template_file->parseCurrentBlock($part);
    }









    //
    // BEGIN needed Setters
    //

    /**
     * @param mixed $page_form_action
     */
    public function setPageFormAction($page_form_action)
    {
        $this->page_form_action = $page_form_action;
    }


    /**
     * @param mixed $permanent_link
     */
    public function setPermanentLink($permanent_link)
    {
        $this->permanent_link = $permanent_link;
    }


    /**
     * @param mixed $main_content
     */
    public function setMainContent($main_content)
    {
        $this->main_content = $main_content;
    }


    /**
     * @param $a_html
     * @param $a_id
     */
    public function addLightbox($a_html, $a_id)
    {
        $this->lightbox[$a_id] = $a_html;
    }


    /**
     * @param mixed $header_page_title
     */
    public function setHeaderPageTitle($header_page_title)
    {
        $this->header_page_title = $header_page_title;
    }

    /**
     * Set banner
     *
     * @param string $a_val banner img src
     */
    public function setBanner($a_val)
    {
        $this->banner = $a_val;
    }

    /**
     * Get banner
     *
     * @return string banner img src
     */
    public function getBanner()
    {
        return $this->banner;
    }


    /**
     * @param mixed $title
     * @param bool $hidden
     */
    public function setTitle($title, bool $hidden = false)
    {
        $this->title = $title;
        $this->hiddenTitle = $hidden;
    }


    /**
     * @param mixed $title_desc
     */
    public function setTitleDesc($title_desc)
    {
        $this->title_desc = $title_desc;
    }


    /**
     * @param mixed $title_alerts
     */
    public function setTitleAlerts($title_alerts)
    {
        $this->title_alerts = $title_alerts;
    }


    /**
     * @param mixed $header_action
     */
    public function setHeaderAction($header_action)
    {
        $this->header_action = $header_action;
    }


    /**
     * @param mixed $admin_panel_commands_toolbar
     */
    public function setAdminPanelCommandsToolbar($admin_panel_commands_toolbar)
    {
        $this->admin_panel_commands_toolbar = $admin_panel_commands_toolbar;
    }


    /**
     * @param mixed $admin_panel_arrow
     */
    public function setAdminPanelArrow($admin_panel_arrow)
    {
        $this->admin_panel_arrow = $admin_panel_arrow;
    }


    /**
     * @param mixed $admin_panel_bottom
     */
    public function setAdminPanelBottom($admin_panel_bottom)
    {
        $this->admin_panel_bottom = $admin_panel_bottom;
    }


    /**
     * @param $a_html
     */
    public function setRightContent($a_html)
    {
        $this->right_content = $a_html;
    }


    /**
     * @param mixed $left_content
     */
    public function setLeftContent($left_content)
    {
        $this->left_content = $left_content;
    }


    /**
     * @param string $filter
     */
    public function setFilter(string $filter)
    {
        $this->filter = $filter;
    }


    private function fillFilter()
    {
        if (trim($this->filter) != "") {
            $this->template_file->setCurrentBlock("filter");
            $this->template_file->setVariable("FILTER", $this->filter);
            $this->template_file->parseCurrentBlock();
        }
    }


    /**
     * @param mixed $icon_path
     */
    public function setIconPath($icon_path)
    {
        $this->icon_path = $icon_path;
    }


    /**
     * @param mixed $icon_desc
     */
    public function setIconDesc($icon_desc)
    {
        $this->icon_desc = $icon_desc;
    }


    /**
     * @param mixed $enable_fileupload
     */
    public function setEnableFileupload($enable_fileupload)
    {
        $this->enable_fileupload = $enable_fileupload;
    }


    /**
     * Set a message to be displayed to the user. Please use ilUtil::sendInfo(),
     * ilUtil::sendSuccess() and ilUtil::sendFailure()
     *
     * @param string $a_type   \ilTemplate::MESSAGE_TYPE_SUCCESS,
     *                         \ilTemplate::MESSAGE_TYPE_FAILURE,,
     *                         \ilTemplate::MESSAGE_TYPE_QUESTION,
     *                         \ilTemplate::MESSAGE_TYPE_INFO
     * @param string $a_txt    The message to be sent
     * @param bool   $a_keep   Keep this message over one redirect
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


    //
    // END needed Setters
    //

    public function renderPage($part, $a_fill_tabs, $a_skip_main_menu) : string
    {
        //
        // Copied from old ilGlobalTemplate and modified
        //
        $this->fillMessage();

        // display ILIAS footer
        // if ($part !== false) {
        // 	$this->fillFooter();
        // }

        // set standard parts (tabs and title icon)
        // $this->fillBodyClass();

        // see #22992
        // $this->fillContentLanguage();

        $this->fillPageFormAction();

        if ($a_fill_tabs) {
            if ($this->template_file->blockExists("content")) {
                // determine default screen id
                $this->getTabsHTML();
            }

            // to get also the js files for the main menu
            if (!$a_skip_main_menu) {
                // $this->getMainMenu();
                //$this->initHelp();
            }

            //$this->initHelp();

            // these fill blocks in tpl.main.html
            // $this->fillCssFiles();
            // $this->fillInlineCss();
            //$this->fillJavaScriptFiles();

            // these fill just plain placeholder variables in tpl.main.html
            // $this->setCurrentBlock("DEFAULT");
            // $this->fillNewContentStyle();
            // $this->fillWindowTitle();

            // these fill blocks in tpl.adm_content.html
            $this->fillHeader();
            // $this->fillSideIcons();
            // $this->fillScreenReaderFocus(); // TODO
            $this->fillLeftContent();
            // $this->fillLeftNav();
            $this->fillRightContent();
            $this->fillAdminPanel();
            $this->fillToolbar();
            $this->fillFilter();
            // $this->fillPermanentLink(); //TODO

            $this->setCenterColumnClass();

            // late loading of javascipr files, since operations above may add files
            // $this->fillJavaScriptFiles();
            // $this->fillOnLoadCode();

            // these fill just plain placeholder variables in tpl.adm_content.html
            if ($this->template_file->blockExists("content")) {
                $this->template_file->setCurrentBlock("content");
                $this->fillTabs();
                $this->fillMainContent();
                // $this->fillMainMenu();
                $this->fillLightbox();
                $this->template_file->parseCurrentBlock();
            }
        }

        if ($part == "DEFAULT" or is_bool($part)) {
            $html = $this->template_file->getUnmodified();
        } else {
            $html = $this->template_file->getUnmodified($part);
        }

        global $DIC;

        // Modification of html is done inline here and can't be done
        // by ilTemplate, because the "phase" is template_show in this
        // case here.
        $ilPluginAdmin = $DIC["ilPluginAdmin"];
        $pl_names = $ilPluginAdmin->getActivePluginsForSlot(IL_COMP_SERVICE, "UIComponent", "uihk");
        foreach ($pl_names as $pl) {
            $ui_plugin = \ilPluginAdmin::getPluginObject(IL_COMP_SERVICE, "UIComponent", "uihk", $pl);
            $gui_class = $ui_plugin->getUIClassInstance();

            $resp = $gui_class->getHTML(
                "",
                "template_show",
                array("tpl_id" => $this->tplIdentifier, "tpl_obj" => $this, "html" => $html)
            );

            if ($resp["mode"] != \ilUIHookPluginGUI::KEEP) {
                $html = $gui_class->modifyHTML($html, $resp);
            }
        }

        // save language usages as late as possible
        \ilObjLanguageAccess::_saveUsages();

        return $html;
    }


    private function fillMessage()
    {
        global $DIC;

        $out = "";

        foreach (self::$message_types as $m) {
            $txt = $this->getMessageTextForType($m);

            if ($txt != "") {
                $out .= \ilUtil::getSystemMessageHTML($txt, $m);
            }

            $request = $DIC->http()->request();
            $accept_header = $request->getHeaderLine('Accept');
            if (isset($_SESSION[$m]) && $_SESSION[$m] && ($accept_header !== 'application/json')) {
                unset($_SESSION[$m]);
            }
        }

        if ($out != "") {
            $this->template_file->setVariable("MESSAGE", $out);
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


    private function getTabsHTML()
    {
        global $DIC;

        $ilTabs = $DIC["ilTabs"];

        if ($this->template_file->blockExists("tabs_outer_start")) {
            $this->sub_tabs_html = $ilTabs->getSubTabHTML();
            $this->tabs_html = $ilTabs->getHTML(true);
        }
    }


    /**
     * Init help
     */
    private function initHelp()
    {
        include_once("./Services/Help/classes/class.ilHelpGUI.php");
        //\ilHelpGUI::initHelp($this);
    }


    private function fillHeader()
    {
        global $DIC;

        $lng = $DIC->language();


        if ($this->banner != "" && $this->template_file->blockExists("banner_bl")) {
            $this->template_file->setCurrentBlock("banner_bl");
            $this->template_file->setVariable("BANNER_URL", $this->banner);
            $header = true;
            $this->template_file->parseCurrentBlock();
        }


        $icon = false;
        if ($this->icon_path != "") {
            $icon = true;
            $this->template_file->setCurrentBlock("header_image");
            if ($this->icon_desc != "") {
                $this->template_file->setVariable("IMAGE_DESC", $lng->txt("icon") . " " . $this->icon_desc);
                $this->template_file->setVariable("IMAGE_ALT", $lng->txt("icon") . " " . $this->icon_desc);
            }

            $this->template_file->setVariable("IMG_HEADER", $this->icon_path);
            $this->template_file->parseCurrentBlock();
            $header = true;
        }

        if ($this->title != "") {
            $title = \ilUtil::stripScriptHTML($this->title);
            $this->template_file->setVariable("HEADER", $title);
            if ($this->hiddenTitle) {
                $this->template_file->touchBlock("hidden_title");
            }

            $header = true;
        }

        if ($header && !$this->hiddenTitle) {
            $this->template_file->setCurrentBlock("header_image");
            $this->template_file->parseCurrentBlock();
        }

        if ($this->title_desc != "") {
            $this->template_file->setCurrentBlock("header_desc");
            $this->template_file->setVariable("H_DESCRIPTION", $this->title_desc);
            $this->template_file->parseCurrentBlock();
        }

        $header = $this->header_action;
        if ($header) {
            $this->template_file->setCurrentBlock("head_action_inner");
            $this->template_file->setVariable("HEAD_ACTION", $header);
            $this->template_file->parseCurrentBlock();
        }

        if (count((array) $this->title_alerts)) {
            foreach ($this->title_alerts as $alert) {
                $this->template_file->setCurrentBlock('header_alert');
                if (!($alert['propertyNameVisible'] === false)) {
                    $this->template_file->setVariable('H_PROP', $alert['property'] . ':');
                }
                $this->template_file->setVariable('H_VALUE', $alert['value']);
                $this->template_file->parseCurrentBlock();
            }
        }

        // add file upload drop zone in header
        if ($this->enable_fileupload != null) {
            $ref_id = $this->enable_fileupload;
            $upload_id = "dropzone_" . $ref_id;

            include_once("./Services/FileUpload/classes/class.ilFileUploadGUI.php");
            $upload = new \ilFileUploadGUI($upload_id, $ref_id, true);

            $this->template_file->setVariable("FILEUPLOAD_DROPZONE_ID", " id=\"$upload_id\"");

            $this->template_file->setCurrentBlock("header_fileupload");
            $this->template_file->setVariable("HEADER_FILEUPLOAD_SCRIPT", $upload->getHTML());
            $this->template_file->parseCurrentBlock();
        }
    }


    private function setCenterColumnClass()
    {
        if (!$this->template_file->blockExists("center_col_width")) {
            return;
        }
        $center_column_class = "";
        if (trim($this->right_content) != "" && trim($this->left_content) != "") {
            $center_column_class = "two_side_col";
        } else {
            if (trim($this->right_content) != "" || trim($this->left_content) != "") {
                $center_column_class = "one_side_col";
            }
        }

        switch ($center_column_class) {
            case "one_side_col":
                $center_column_class = "col-sm-9";
                break;
            case "two_side_col":
                $center_column_class = "col-sm-6";
                break;
            default:
                $center_column_class = "col-sm-12";
                break;
        }
        if (trim($this->left_content) != "") {
            $center_column_class .= " col-sm-push-3";
        }

        $this->template_file->setCurrentBlock("center_col_width");
        $this->template_file->setVariable("CENTER_COL", $center_column_class);
        $this->template_file->parseCurrentBlock();
    }


    private function fillMainContent()
    {
        if (trim($this->main_content) != "") {
            $this->template_file->setVariable("ADM_CONTENT", $this->main_content);
        }
    }


    private function fillLeftContent()
    {
        if (trim($this->left_content) != "") {
            $this->template_file->setCurrentBlock("left_column");
            $this->template_file->setVariable("LEFT_CONTENT", $this->left_content);
            $left_col_class = (trim($this->right_content) == "")
                ? "col-sm-3 col-sm-pull-9"
                : "col-sm-3 col-sm-pull-6";
            $this->template_file->setVariable("LEFT_COL_CLASS", $left_col_class);
            $this->template_file->parseCurrentBlock();
        }
    }


    private function fillRightContent()
    {
        if (trim($this->right_content) != "") {
            $this->template_file->setCurrentBlock("right_column");
            $this->template_file->setVariable("RIGHT_CONTENT", $this->right_content);
            $this->template_file->parseCurrentBlock();
        }
    }


    private function fillAdminPanel()
    {
        global $DIC;
        $lng = $DIC->language();

        if ($this->admin_panel_commands_toolbar === null) {
            return;
        }

        $toolb = $this->admin_panel_commands_toolbar;
        assert($toolb instanceof \ilToolbarGUI);

        // Add arrow if desired.
        if ($this->admin_panel_arrow) {
            $toolb->setLeadingImage(\ilUtil::getImagePath("arrow_upright.svg"), $lng->txt("actions"));
        }

        $this->fillPageFormAction();

        // Add top admin bar.
        $this->template_file->setCurrentBlock("adm_view_components");
        $this->template_file->setVariable("ADM_PANEL1", $toolb->getHTML());
        $this->template_file->parseCurrentBlock();

        // Add bottom admin bar if user wants one.
        if ($this->admin_panel_bottom) {
            $this->template_file->setCurrentBlock("adm_view_components2");

            // Replace previously set arrow image.
            if ($this->admin_panel_arrow) {
                $toolb->setLeadingImage(\ilUtil::getImagePath("arrow_downright.svg"), $lng->txt("actions"));
            }

            $this->template_file->setVariable("ADM_PANEL2", $toolb->getHTML());
            $this->template_file->parseCurrentBlock();
        }
    }


    private function fillPageFormAction()
    {
        if ($this->page_form_action != "") {
            $this->template_file->setCurrentBlock("page_form_start");
            $this->template_file->setVariable("PAGE_FORM_ACTION", $this->page_form_action);
            $this->template_file->parseCurrentBlock();
            $this->template_file->touchBlock("page_form_end");
        }
    }


    private function fillToolbar()
    {
        global $DIC;

        $ilToolbar = $DIC["ilToolbar"];
        ;

        $thtml = $ilToolbar->getHTML();
        if ($thtml != "") {
            $this->template_file->setCurrentBlock("toolbar_buttons");
            $this->template_file->setVariable("BUTTONS", $thtml);
            $this->template_file->parseCurrentBlock();
        }
    }


    private function fillTabs()
    {
        if ($this->template_file->blockExists("tabs_outer_start")) {
            $this->template_file->touchBlock("tabs_outer_start");
            $this->template_file->touchBlock("tabs_outer_end");
            $this->template_file->touchBlock("tabs_inner_start");
            $this->template_file->touchBlock("tabs_inner_end");

            if ($this->tabs_html != "") {
                $this->template_file->setVariable("TABS", $this->tabs_html);
            }
            $this->template_file->setVariable("SUB_TABS", $this->sub_tabs_html);
        }
    }


    private function fillLightbox()
    {
        $this->template_file->setVariable('LIGHTBOX', implode('', $this->lightbox));
    }
}
