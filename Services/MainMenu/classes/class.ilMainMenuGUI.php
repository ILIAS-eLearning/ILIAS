<?php

/* Copyright (c) 1998-2012 ILIAS open source, Extended GPL, see docs/LICENSE */

use ILIAS\BackgroundTasks\BucketMeta;
use ILIAS\BackgroundTasks\Implementation\Bucket\State;
use ILIAS\UI\Implementation\Component\Popover\ReplaceContentSignal;

/**
 * Handles display of the main menu
 *
 * @author  Alex Killing
 * @version $Id$
 */
class ilMainMenuGUI
{

    /**
     * @var ilRbacSystem
     */
    protected $rbacsystem;
    /**
     * @var ilObjUser
     */
    protected $user;
    /**
     * @var ilLanguage
     */
    protected $lng;
    /**
     * @var ilPluginAdmin
     */
    protected $plugin_admin;
    /**
     * @var ilTree
     */
    protected $tree;
    /**
     * @var ilAccessHandler
     */
    protected $access;
    /**
     * @var ilNavigationHistory
     */
    protected $nav_history;
    /**
     * @var ilSetting
     */
    protected $settings;
    /**
     * @var ilCtrl
     */
    protected $ctrl;
    /**
     * @var ilHelpGUI
     */
    protected $help;
    /**
     * @var ilTemplate
     */
    var $tpl;
    var $target;
    var $start_template;
    var $mail; // [bool]
    /**
     * @var ilTemplate
     */
    protected $main_tpl;
    protected $mode; // [int]
    protected $topbar_back_url; // [stringt]
    protected $topbar_back_caption; // [string]
    const MODE_FULL = 1;
    const MODE_TOPBAR_ONLY = 2;
    const MODE_TOPBAR_REDUCED = 3;
    const MODE_TOPBAR_MEMBERVIEW = 4;


    /**
     * @param string  $a_target                       target frame
     * @param boolean $a_use_start_template           true means: target scripts should
     *                                                be called through start template
     */
    public function __construct($a_target = "_top", $a_use_start_template = false, ilTemplate $a_main_tpl = null)
    {
        global $DIC;

        if ($a_main_tpl != null) {
            $this->main_tpl = $a_main_tpl;
        } else {
            $this->main_tpl = $DIC["tpl"];
        }

        $this->rbacsystem = $DIC->rbac()->system();
        $this->user = $DIC->user();
        $this->lng = $DIC->language();
        $this->plugin_admin = $DIC["ilPluginAdmin"];
        $this->tree = $DIC->repositoryTree();
        $this->access = $DIC->access();
        $this->nav_history = $DIC["ilNavigationHistory"];
        $this->settings = $DIC->settings();
        $this->ctrl = $DIC->ctrl();
        $this->help = $DIC["ilHelp"];
        $this->ui = $DIC->ui();
        $rbacsystem = $DIC->rbac()->system();
        $ilUser = $DIC->user();

        $this->tpl = new ilTemplate(
            "tpl.main_menu.html", true, true,
            "Services/MainMenu"
        );
        $this->target = $a_target;
        $this->start_template = $a_use_start_template;

        $this->mail = false;
        if ($ilUser->getId() != ANONYMOUS_USER_ID) {
            if ($rbacsystem->checkAccess('internal_mail', ilMailGlobalServices::getMailObjectRefId())) {
                $this->mail = true;
            }
        }

        $this->setMode(self::MODE_FULL);

        // member view
        $set = ilMemberViewSettings::getInstance();
        if ($set->isActive()) {
            $this->initMemberView();
        }
    }


    /**
     * @param int $a_value        accepts values:
     *                            self::MODE_TOPBAR_ONLY:
     *                            self::MODE_TOPBAR_REDUCED:
     *                            self::MODE_TOPBAR_MEMBERVIEW:
     *                            case self::MODE_FULL:
     */
    public function setMode(int $a_value)
    {
        $this->mode = (int) $a_value;
    }


    /**
     * @return int
     */
    public function getMode() : int
    {
        return $this->mode;
    }


    /**
     * @param      $a_url
     * @param null $a_caption
     */
    public function setTopBarBack($a_url, $a_caption = null)
    {
        $this->topbar_back_url = $a_url;
        $this->topbar_back_caption = trim($a_caption);
    }


    /**
     * @return string
     */
    public function getSpacerClass()
    {
        switch ($this->getMode()) {
            case self::MODE_TOPBAR_ONLY:
            case self::MODE_TOPBAR_REDUCED:
            case self::MODE_TOPBAR_MEMBERVIEW:
                return "ilFixedTopSpacerBarOnly";

            case self::MODE_FULL:
                return "ilFixedTopSpacer";
        }
    }


    /**
     * @param string $a_active "desktop"|"repository"|"search"|"mail"|"chat_invitation"|"administration"
     *
     * @deprecated
     *
     */
    public function setActive($a_active)
    {
        $this->active = $a_active;
    }


    /**
     * @deprecated
     * Set target parameter for login (public sector).
     * This is used by the main menu
     */
    public function setLoginTargetPar($a_val)
    {
        $this->login_target_par = $a_val;
    }


    /**
     * @deprecated
     * Get target parameter for login
     */
    public function getLoginTargetPar()
    {
        return $this->login_target_par;
    }


    /**
     * @param bool $a_in_topbar
     *
     * @return string
     */
    public static function getLanguageSelection($a_in_topbar = false) : string
    {
        global $DIC;

        $lng = $DIC->language();

        $gr_list = new ilGroupedListGUI();
        $gr_list->setAsDropDown(true);

        $languages = $lng->getInstalledLanguages();
        if (sizeof($languages) > 1) // #11237
        {
            foreach ($languages as $lang_key) {
                $base = substr($_SERVER["REQUEST_URI"], strrpos($_SERVER["REQUEST_URI"], "/") + 1);
                $base = preg_replace("/&*lang=[a-z]{2}&*/", "", $base);
                $link = ilUtil::appendUrlParameterString(
                    $base,
                    "lang=" . $lang_key
                );
                $link = str_replace("?&", "?", $link);

                $gr_list->addEntry($lng->_lookupEntry($lang_key, "meta", "meta_l_" . $lang_key), $link);
            }

            return $gr_list->getHTML();
        }

        return "";
    }


    /**
     * Set all template variables (images, scripts, target frames, ...)
     */
    private function setTemplateVars()
    {
        $rbacsystem = $this->rbacsystem;
        $lng = $this->lng;
        $ilUser = $this->user;
        $ilPluginAdmin = $this->plugin_admin;
        $main_tpl = $this->main_tpl;

        if ($this->logo_only) {
            $this->tpl->setVariable("HEADER_URL", $this->getHeaderURL());
            $this->tpl->setVariable("HEADER_ICON", ilUtil::getImagePath("HeaderIcon.svg"));
            $this->tpl->setVariable("HEADER_ICON_RESPONSIVE", ilUtil::getImagePath("HeaderIconResponsive.svg"));

            // #15759
            $header_top_title = ilObjSystemFolder::_getHeaderTitle();
            if (trim($header_top_title) != "" && $this->tpl->blockExists("header_top_title")) {
                $this->tpl->setCurrentBlock("header_top_title");
                $this->tpl->setVariable("TXT_HEADER_TITLE", $header_top_title);
                $this->tpl->parseCurrentBlock();
            }

            return;
        }

        // get user interface plugins

        if ($this->getMode() != self::MODE_TOPBAR_REDUCED
            && $this->getMode() != self::MODE_TOPBAR_MEMBERVIEW
        ) {
            // search
            if ($rbacsystem->checkAccess('search', ilSearchSettings::_getSearchSettingRefId())) {
                $main_search = new ilMainMenuSearchGUI();
                $html = "";

                // user interface plugin slot + default rendering
                $uip = new ilUIHookProcessor(
                    "Services/MainMenu", "main_menu_search",
                    array("main_menu_gui" => $this, "main_menu_search_gui" => $main_search)
                );
                if (!$uip->replaced()) {
                    $html = $main_search->getHTML();
                }
                $html = $uip->getHTML($html);

                if (strlen($html)) {
                    $this->tpl->setVariable('SEARCHBOX', $html);
                    $this->addToolbarTooltip("ilMMSearch", "mm_tb_search");
                }
            }

            $this->renderStatusBox($this->tpl);

            // online help
            $this->renderHelpButtons();

            $this->renderOnScreenChatMenu();
            $this->populateWithBuddySystem();
            $this->populateWithOnScreenChat();
            $this->renderBackgroundTasks();
            $this->renderAwareness();
        }

        if ($this->getMode() == self::MODE_FULL) {
            $renderer = new ilMMEntryRendererGUI();
            $new_renderer = $renderer->getHTML();
            $this->tpl->setVariable("MAIN_MENU_LIST_ENTRIES", $new_renderer);
        }

        if ($this->getMode() != self::MODE_TOPBAR_MEMBERVIEW) {
            $link_dir = (defined("ILIAS_MODULE"))
                ? "../"
                : "";

            // login stuff
            if ($GLOBALS['DIC']['ilUser']->getId() == ANONYMOUS_USER_ID) {
                if (ilRegistrationSettings::_lookupRegistrationType() != IL_REG_DISABLED) {
                    $this->tpl->setCurrentBlock("registration_link");
                    $this->tpl->setVariable("TXT_REGISTER", $lng->txt("register"));
                    $this->tpl->setVariable("LINK_REGISTER", $link_dir . "register.php?client_id=" . rawurlencode(CLIENT_ID) . "&lang=" . $ilUser->getCurrentLanguage());
                    $this->tpl->parseCurrentBlock();
                }

                // language selection
                $selection = self::getLanguageSelection();
                if ($selection) {
                    $this->tpl->setVariable("TXT_LANGSELECT", $lng->txt("language"));
                    $this->tpl->setVariable("LANG_SELECT", $selection);
                }

                $this->tpl->setCurrentBlock("userisanonymous");
                $this->tpl->setVariable("TXT_NOT_LOGGED_IN", $lng->txt("not_logged_in"));
                $this->tpl->setVariable("TXT_LOGIN", $lng->txt("log_in"));

                // #13058
                $target_str = ($this->getLoginTargetPar() != "")
                    ? $this->getLoginTargetPar()
                    : ilTemplate::buildLoginTarget();
                $this->tpl->setVariable(
                    "LINK_LOGIN",
                    $link_dir . "login.php?target=" . $target_str . "&client_id=" . rawurlencode(CLIENT_ID) . "&cmd=force_login&lang=" . $ilUser->getCurrentLanguage()
                );
                $this->tpl->parseCurrentBlock();
            } else {
                $this->renderOnScreenNotifications($ilUser, $main_tpl, $lng);

                $this->tpl->setCurrentBlock("userisloggedin");
                $this->tpl->setVariable("TXT_LOGIN_AS", $lng->txt("login_as"));
                $user_img_src = $ilUser->getPersonalPicturePath("small", true);
                $user_img_alt = $ilUser->getFullname();
                $this->tpl->setVariable("USER_IMG", ilUtil::img($user_img_src, $user_img_alt));
                $this->tpl->setVariable("USR_LINK_PROFILE", "ilias.php?baseClass=ilPersonalDesktopGUI&cmd=jumpToProfile");
                $this->tpl->setVariable("USR_TXT_PROFILE", $lng->txt("personal_profile"));
                $this->tpl->setVariable("USR_LINK_SETTINGS", "ilias.php?baseClass=ilPersonalDesktopGUI&cmd=jumpToSettings");
                $this->tpl->setVariable("USR_TXT_SETTINGS", $lng->txt("personal_settings"));
                $this->tpl->setVariable("TXT_LOGOUT2", $lng->txt("logout"));
                $this->tpl->setVariable("LINK_LOGOUT2", $link_dir . "logout.php?lang=" . $ilUser->getCurrentLanguage());
                $this->tpl->setVariable("USERNAME", $ilUser->getFullname());
                $this->tpl->setVariable("LOGIN", $ilUser->getLogin());
                $this->tpl->setVariable("MATRICULATION", $ilUser->getMatriculation());
                $this->tpl->setVariable("EMAIL", $ilUser->getEmail());
                $this->tpl->parseCurrentBlock();

                $this->addToolbarTooltip("userlog", "mm_tb_user");
            }
        } else {
            // member view info
            $this->tpl->setVariable("TOPBAR_CLASS", " ilMemberViewMainHeader");
            $this->tpl->setVariable("MEMBER_VIEW_INFO", $lng->txt("mem_view_long"));
        }

        if (!$this->topbar_back_url) {
            $header_top_title = ilObjSystemFolder::_getHeaderTitle();
            if (trim($header_top_title) != "" && $this->tpl->blockExists("header_top_title")) {
                $this->tpl->setCurrentBlock("header_top_title");
                // php7-workaround alex: added phpversion() to help during development of php7 compatibility
                $this->tpl->setVariable("TXT_HEADER_TITLE", $header_top_title);
                $this->tpl->parseCurrentBlock();
            }
        } else {
            $this->tpl->setCurrentBlock("header_back_bl");
            $this->tpl->setVariable("URL_HEADER_BACK", $this->topbar_back_url);
            $this->tpl->setVariable(
                "TXT_HEADER_BACK", $this->topbar_back_caption
                ? $this->topbar_back_caption
                : $lng->txt("back")
            );
            $this->tpl->parseCurrentBlock();
        }

        $this->tpl->setVariable("LOCATION_STYLESHEET", ilUtil::getStyleSheetLocation());

        if ($this->getMode() == self::MODE_FULL) {
            // $this->tpl->setVariable("TXT_LOGOUT", $lng->txt("logout"));
            $this->tpl->setVariable("HEADER_URL", $this->getHeaderURL());
            $this->tpl->setVariable("HEADER_ICON", ilUtil::getImagePath("HeaderIcon.svg"));
            $this->tpl->setVariable("HEADER_ICON_RESPONSIVE", ilUtil::getImagePath("HeaderIconResponsive.svg"));
        }

        $this->tpl->setVariable("TXT_MAIN_MENU", $lng->txt("main_menu"));

        $this->tpl->parseCurrentBlock();
    }


    /**
     * @param ilTemplate $a_tpl
     */
    private function renderStatusBox(ilTemplate $a_tpl)
    {
        $ilUser = $this->user;
        $ui_factory = $this->ui->factory();
        $ui_renderer = $this->ui->renderer();

        if ($this->mail) {
            $new_mails = ilMailGlobalServices::getNumberOfNewMailsByUserId($ilUser->getId());

            $a_tpl->setCurrentBlock('status_box');

            $glyph = $ui_factory->glyph()->mail("ilias.php?baseClass=ilMailGUI");

            if ($new_mails > 0) {
                $glyph = $glyph->withCounter($ui_factory->counter()->novelty($new_mails));
            }

            $a_tpl->setVariable('GLYPH', $ui_renderer->render($glyph));
            $a_tpl->setVariable('STATUS_ID', "sb_mail");
            $this->addToolbarTooltip("sb_mail", "mm_tb_mail");
            $a_tpl->parseCurrentBlock();
        }
    }


    /**
     * @return bool
     * @deprecated Please use RBAC directly
     */
    public static function _checkAdministrationPermission()
    {
        global $DIC;

        $rbacsystem = $DIC->rbac()->system();

        //if($rbacsystem->checkAccess("visible,read", SYSTEM_FOLDER_ID))
        if ($rbacsystem->checkAccess("visible", SYSTEM_FOLDER_ID)) {
            return true;
        }

        return false;
    }


    /**
     * @return string
     * @throws ilTemplateException
     * @deprecated
     */
    public function getHTML()
    {
        // this is a workaround for bugs like 14016
        // the main menu does not need the YUI connection, but many other
        // features since they rely on il.Util.sendAjaxGetRequestToUrl (see Services/Javascript)
        // which still uses YUI. This should be migrated to jQuery with a future major release
        ilYuiUtil::initConnection();

        $this->setTemplateVars();

        return $this->tpl->get();
    }


    /**
     * @return bool
     */
    protected function initMemberView() : bool
    {
        $lng = $this->lng;

        $ref_id = ilMemberViewSettings::getInstance()->getCurrentRefId();

        if (!$ref_id) {
            return false;
        }

        $url = ilLink::_getLink(
            $ref_id,
            ilObject::_lookupType(ilObject::_lookupObjId($ref_id)),
            array('mv' => 0)
        );

        $this->setMode(self::MODE_TOPBAR_MEMBERVIEW);
        $this->setTopBarBack($url, $lng->txt('mem_view_close'));

        return true;
    }


    private function renderHelpButtons()
    {
        $ilHelp = $this->help;
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;
        $ilSetting = $this->settings;
        $ilUser = $this->user;
        $main_tpl = $this->main_tpl;

        // screen id
        if ((defined("OH_REF_ID") && OH_REF_ID > 0) || DEVMODE == 1) {
            if ($ilHelp->getScreenId() != "") {
                if ($this->getMode() == self::MODE_FULL) {
                    $this->tpl->setCurrentBlock("screen_id");
                    $this->tpl->setVariable("SCREEN_ID", $ilHelp->getScreenId());
                    $this->tpl->parseCurrentBlock();
                }
            }
        }

        $help_active = false;

        $helpl = new ilGroupedListGUI();
        $helpl->setAsDropDown(true, true);

        if ($ilHelp->hasSections()) {
            $help_active = true;

            $lng->loadLanguageModule("help");
            //$this->tpl->setCurrentBlock("help_icon");

            // add javascript needed by help (to do: move to help class)
            $main_tpl->addJavascript("./Services/Help/js/ilHelp.js");
            $acc = new ilAccordionGUI();
            $acc->addJavascript($main_tpl);
            $acc->addCss();

            ilTooltipGUI::addTooltip(
                "help_tr", $lng->txt("help_open_online_help"), "",
                "bottom center", "top center", false
            );
            $helpl->addEntry("<span>&nbsp;</span> " . $lng->txt("help_topcis"), "#", "", "il.Help.listHelp(event, false);");
        }

        $module_id = (int) $ilSetting->get("help_module");
        if ((OH_REF_ID > 0 || $module_id > 0) && $ilUser->getLanguage() == "de"
            && $ilSetting->get("help_mode") != "1"
        ) {
            $help_active = true;

            $lng->loadLanguageModule("help");
            $main_tpl->addJavascript("./Services/Help/js/ilHelp.js");

            ilTooltipGUI::addTooltip(
                "help_tt", $lng->txt("help_toggle_tooltips"), "",
                "bottom center", "top center", false
            );
            $helpl->addEntry('<span id="help_tt_switch_on" class="glyphicon glyphicon-ok"></span> ' . $lng->txt("help_tooltips"), "#", "", "return il.Help.switchTooltips(event);");
        }

        if ($help_active && $ilHelp->hasSections()) {
            $this->tpl->setCurrentBlock("help");
            $this->tpl->setVariable("TXT_HELP", $lng->txt("help"));
            $this->tpl->setVariable("HELP_CLICK", "il.Help.listHelp(event, false);");
            $this->tpl->parseCurrentBlock();

            $this->addToolbarTooltip("mm_help", "mm_tb_help");

            // always set ajax url
            $ilHelp->setCtrlPar();
            $this->main_tpl->addOnLoadCode(
                "il.Help.setAjaxUrl('" .
                $ilCtrl->getLinkTargetByClass("ilhelpgui", "", "", true)
                . "');"
            );
        }
    }


    /**
     * Includes all buddy system/user connections related javascript code
     */
    private function populateWithBuddySystem()
    {
        if (ilBuddySystem::getInstance()->isEnabled()) {
            ilBuddySystemGUI::initializeFrontend();
        }
    }


    private function populateWithOnScreenChat()
    {
        ilOnScreenChatGUI::initializeFrontend();
    }


    private function renderOnScreenChatMenu()
    {
        $menu = new ilOnScreenChatMenuGUI();
        $this->tpl->setVariable('ONSCREENCHAT', $menu->getMainMenuHTML());
        $this->addToolbarTooltip("onscreenchatmenu-dropdown", "mm_tb_oschat");
    }


    /**
     * Render awareness tool
     */
    function renderAwareness()
    {
        include_once("./Services/Awareness/classes/class.ilAwarenessGUI.php");
        $aw = ilAwarenessGUI::getInstance();

        $this->tpl->setVariable("AWARENESS", $aw->getMainMenuHTML());
        $this->addToolbarTooltip("awareness_trigger", "mm_tb_aware");
    }


    /**
     * @param \ilObjUser  $user
     * @param \ilTemplate $mainTpl
     * @param \ilLanguage $lng
     */
    private function renderOnScreenNotifications(\ilObjUser $user, \ilTemplate $mainTpl, \ilLanguage $lng)
    {
        if ($this->getMode() != self::MODE_TOPBAR_REDUCED && !$user->isAnonymous()) {
            $this->tpl->touchBlock('osd_container');

            $osdGui = new ilNotificationOSDGUI($user, $mainTpl, $lng);
            $osdGui->render();
        }
    }


    /**
     * Toggle rendering of main menu, search, user info
     *
     * @param bool $a_value
     *
     * @deprecated do not use in other contextx
     *
     * @see        ilImprintGUI
     */
    public function showLogoOnly(bool $a_value)
    {
        $this->logo_only = (bool) $a_value;
    }


    /**
     * @return string
     */
    private function getHeaderURL() : string
    {
        $url = ilUserUtil::getStartingPointAsUrl();

        if (!$url) {
            $url = "./goto.php?target=root_1";
        }

        return $url;
    }


    private function renderBackgroundTasks()
    {
        global $DIC;

        $main_tpl = $this->main_tpl;

        if ($DIC->user()->isAnonymous() || (int) $DIC->user()->getId() === 0) {
            // bugfix mantis 25348
            $this->tpl->setVariable('BGT_HIDDEN', 'true');
            return;
        }

        $DIC->language()->loadLanguageModule("background_tasks");
        $factory = $DIC->ui()->factory();
        $persistence = $DIC->backgroundTasks()->persistence();
        $metas = $persistence->getBucketMetaOfUser($DIC->user()->getId());
        if (!count($metas)) {
            // bugfix mantis 25348
            $this->tpl->setVariable('BGT_HIDDEN', 'true');
            return;
        }

        $numberOfUserInteractions = count(
            array_filter(
                $metas, function (BucketMeta $meta) {
                return $meta->getState() == State::USER_INTERACTION;
            }
            )
        );
        $numberOfNotUserInteractions = count($metas) - $numberOfUserInteractions;

        $popover = $factory->popover()
            ->listing(array())
            ->withFixedPosition()
            ->withTitle($DIC->language()->txt("background_tasks_running")); // needs to have empty content
        $DIC->ctrl()->clearParametersByClass(ilBTControllerGUI::class);
        $DIC->ctrl()->setParameterByClass(
            ilBTControllerGUI::class,
            ilBTControllerGUI::FROM_URL,
            ilBTControllerGUI::hash("//{$_SERVER['HTTP_HOST']}{$_SERVER['REQUEST_URI']}")
        );
        $DIC->ctrl()->setParameterByClass(
            ilBTControllerGUI::class,
            ilBTControllerGUI::REPLACE_SIGNAL,
            $popover->getReplaceContentSignal()->getId()
        );

        $url = $DIC->ctrl()->getLinkTargetByClass([ilBTControllerGUI::class], ilBTControllerGUI::CMD_GET_POPOVER_CONTENT, "", true);
        $popover = $popover->withAsyncContentUrl($url);

        $glyph = $factory->glyph()
            ->briefcase()
            ->withOnClick($popover->getShowSignal())
            ->withCounter($factory->counter()->novelty($numberOfUserInteractions))
            ->withCounter($factory->counter()->status($numberOfNotUserInteractions));

        $main_tpl->addJavascript('./Services/BackgroundTasks/js/background_task_refresh.js');

        $this->tpl->setVariable(
            'BACKGROUNDTASKS',
            $DIC->ui()->renderer()->render([$glyph, $popover])
        );

        // bugfix mantis 25348
        $this->tpl->setVariable('BGT_HIDDEN', 'false');

        $this->tpl->setVariable('BACKGROUNDTASKS_REFRESH_URI', $url);

        $this->addToolbarTooltip("mm_tb_background_tasks", "mm_tb_bgtasks");
    }


    /**
     * Add toolbar tooltip
     *
     * @param string $element_id
     * @param string $help_id
     */
    protected function addToolbarTooltip(string $element_id, string $help_id)
    {
        if (ilHelp::getMainMenuTooltip($help_id) != "") {
            ilTooltipGUI::addTooltip($element_id, ilHelp::getMainMenuTooltip($help_id),
                "", "top center", "bottom center", false);
        }
    }
}

