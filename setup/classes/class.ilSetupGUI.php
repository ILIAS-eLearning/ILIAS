<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

use ILIAS\BackgroundTasks\Implementation\Bucket\State;
use ILIAS\BackgroundTasks\Implementation\Persistence\BasicPersistence;

require_once "./setup/classes/class.ilSetup.php";

/**
 * Setup GUI class
 *
 * class to setup ILIAS first and maintain the ini-settings and the database
 *
 * @author   Sascha Hofmann <shofmann@databay.de>
 * @version  $Id$
 */
class ilSetupGUI
{
    const UI_PASSWORD_PLACEHOLDER = '********';

    public $tpl;       // template object
    public $lng;       // language objet
    public $log;       // log object

    public $btn_prev_on = false;   // toggle previous button on/off
    public $btn_prev_cmd;          // command processed when previous button was clicked
    public $btn_prev_lng;          // previous button label

    public $btn_next_on = false;   // toggle NEXT button on/off
    public $btn_next_cmd;          // command processed when next button was clicked
    public $btn_next_lng;          // next button label

    public $revision;              // cvs revision of this script
    public $version;               // cvs version of this script
    public $lang;                  // current language (lang_key)

    public $cmd;                       // command variable
    public $display_mode = "view";     // view mode (setup or details)

    /**
     * Constructor
     *
     */
    public function __construct()
    {
        global $tpl, $lng, $DIC;

        $this->tpl = $tpl;
        $this->lng = $lng;

        // note: this is currently only used for subtabs, alex 8.1.2012
        include_once("./Services/UIComponent/Tabs/classes/class.ilTabsGUI.php");
        $this->tabs = new ilTabsGUI();
        $this->tabs->setSetupMode(true);

        include_once("./Services/jQuery/classes/class.iljQueryUtil.php");
        iljQueryUtil::initjQuery($this->tpl);
        include_once("./Services/YUI/classes/class.ilYuiUtil.php");
        ilYuiUtil::initDomEvent();

        $tpl->addJavaScript("./Services/JavaScript/js/Basic.js", 0);

        include_once("./Services/UICore/classes/class.ilUIFramework.php");
        ilUIFramework::init($this->tpl);

        // CVS - REVISION - DO NOT MODIFY
        $this->revision = '$Revision$';
        $this->version = "2 " . substr(substr($this->revision, 1), 0, -2);
        $this->lang = $this->lng->lang_key;

        // init setup
        require_once 'setup/classes/class.ilSetupPasswordManager.php';
        require_once 'setup/classes/class.ilSetupPasswordEncoderFactory.php';
        $this->setup = new ilSetup(
            new \ilSetupPasswordManager([
                'password_encoder' => 'bcryptphp',
                'encoder_factory' => new \ilSetupPasswordEncoderFactory([
                    'default_password_encoder' => 'bcryptphp'
                ])
            ]),
            $_SESSION["auth"],
            $_SESSION["access_mode"]
        );

        // init client object if exists
        $client_id = ($_GET["client_id"]) ? $_GET["client_id"] : $_SESSION["ClientId"];
        if ($_POST["client_id"] != "") {
            $client_id = $_POST["client_id"];
        }

        // for security
        if (!$this->setup->isAdmin() and $client_id != $_SESSION["ClientId"]) {
            $client_id = $_SESSION["ClientId"];
        }

        $this->client_id = $client_id;

        $this->setup->ini_client_exists = $this->setup->newClient($client_id);
        if (is_object($this->setup->getClient())) {
            $this->setup->getClient()->status = $this->setup->getStatus($client_id);
        }

        // determine command
        if (($this->cmd = $_GET["cmd"]) == "gateway") {
            // surpress warning if POST is not set
            @$this->cmd = key($_POST["cmd"]);
        }

        // determine display mode here
        // TODO: depending on previous setting (session)
        // OR switch to 'setup'-mode if someone logs in as client and client's setup wasn't finished (-> entry in settings table does not exist)
        if ($this->setup->isAuthenticated() and !$this->setup->getClient()->status["finish"]["status"] and $this->cmd != "clientlist" and $this->cmd != "") {
            $this->setDisplayMode("setup");
        } else {
            $this->setDisplayMode($_SESSION["display_mode"]);
        }

        // output starts here


        // main cmd handling
        if (!$this->setup->isAuthenticated() or !$this->setup->isInstalled()) {
            // check for first time installation or migrate an old one first
            if (!$this->setup->isInstalled() or !($this->setup->ini->readVariable("clients", "path"))) {
                $this->cmdInstall();
            } else {
                if ($this->cmd == "performLogin" || $this->cmd == "performMLogin") {
                    $cmd = $this->cmd;
                    $this->$cmd();
                } else {
                    $this->displayLogin();
                }
            }
        } else {
            if ($this->setup->isAdmin()) {
                $this->cmdAdmin();
            } else {
                $this->cmdClient();
            }
        }

        // display header
        $this->displayHeader();

        if (DEBUG) {
            echo "cmd: " . $this->cmd . " | access: " . $this->setup->access_mode . " | display: " . $this->display_mode;
            var_dump($this->setup->getClient()->status);
        }

        // display footer
        $this->displayFooter();

        // end output
    }  // end constructor

    // cmd subsets

    /**
     * process valid commands for pre-installation status
     */
    public function cmdInstall()
    {
        $cmd = $this->cmd;
        switch ($this->cmd) {
            case null:
            case "preliminaries":
                $this->setup->checkPreliminaries();
                $this->displayPreliminaries();
                break;

            case "install":
                $this->displayMasterSetup();
                break;

            case "determineToolsPathInstall":
                $this->determineToolsPathInstall();
                break;

            case "saveBasicSettings":
                $this->$cmd();
                break;

            default:
                $this->displayError($this->lng->txt("unknown_command") . ": '" . $this->cmd . "'");
                break;
        }
    }

    /**
     * process valid commands for admins
     */
    public function cmdAdmin()
    {
        $cmd = $this->cmd;
        switch ($this->cmd) {
            case null:
            case "clientlist":
                
                $this->setDisplayMode("view");
                $this->displayClientList();
                $this->active_tab = "clientlist";
                break;

            case "savemasterpassword":
                $this->setDisplayMode("view");
                $this->saveMasterPassword();
                $this->active_tab = "password";
                break;

            case "changemasterpassword":
                $this->setDisplayMode("view");
                $this->changeMasterPassword();
                $this->active_tab = "password";
                break;

            case "mastersettings":
                $this->setDisplayMode("view");
                $this->changeMasterSettings();
                $this->active_tab = "basicsettings";
                break;

            case "determineToolsPath":
                $this->setDisplayMode("view");
                $this->determineToolsPath();
                break;

            case "changedefault":
                $this->changeDefaultClient();
                break;

            case "newclient":
                $this->cmd = "selectdb";
                $this->setDisplayMode("setup");
                $this->setup->ini_client_exists = $this->setup->newClient();
                $this->selectDBType();
                break;

            case "selectdbtype":
            case "displayIni":
                $this->cmd = "ini";
                $this->setDisplayMode("setup");
                //$this->setup->ini_client_exists = $this->setup->newClient($this->client_id);
                $this->displayIni();
                break;

            case "startup":
                $this->setDisplayMode("setup");
                $this->setup->ini_client_exists = $this->setup->newClient();
                $this->displayStartup();
                break;

            case "delete":
                $this->setDisplayMode("view");
                $this->displayDeleteConfirmation();
                break;

            case "togglelist":
                $this->setDisplayMode("view");
                $this->toggleClientList();
                break;

            case "preliminaries":
                $this->setup->checkPreliminaries();
                $this->displayPreliminaries();
                $this->active_tab = "preliminaries";
                break;

            case "updateBasicSettings":
            case "performLogin":
            case "performMLogin":
                $this->$cmd();
                break;

            default:
                $this->cmdClient();
                break;
        }
    }

    /**
     * process valid commands for all clients
     */
    public function cmdClient()
    {
        $cmd = $this->cmd;
        switch ($this->cmd) {
            case null:
            case "view":
                if ($this->setup->getClient()->db_installed) {
                    $this->setDisplayMode("view");
                    $this->displayClientOverview();
                } else {
                    $this->cmd = "db";
                    $this->displayDatabase();
                }
                break;

            case "ini":
                // only allow access to ini if db does not exist yet
                //if ($this->setup->getClient()->db_installed)
                //{
                //	$this->cmd = "db";
                //	$this->displayDatabase();
                //}
                //else
                //{
                    $this->displayIni();
                //}
                break;

            case "db":
                $this->displayDatabase();
                break;

            case "dbslave":
                $this->displayDatabaseSlave();
                break;

            case "sess":
                if (!isset($_GET["lang"]) and !$this->setup->getClient()->status["finish"]["status"] and $_GET["cmd"] == "sess" and $this->setup->error === true) {
                    $this->jumpToFirstUnfinishedSetupStep();
                } else {
                    $this->displaySessions();
                }
                break;

            case "lang":
                if (!isset($_GET["lang"]) and !$this->setup->getClient()->status["finish"]["status"] and $_GET["cmd"] == "lang" and $this->setup->error === true) {
                    $this->jumpToFirstUnfinishedSetupStep();
                } else {
                    $this->displayLanguages();
                }
                break;

            case "contact":
                if (!isset($_GET["lang"]) and !$this->setup->getClient()->status["finish"]["status"] and $_GET["cmd"] == "contact") {
                    $this->jumpToFirstUnfinishedSetupStep();
                } else {
                    $this->displayContactData();
                }
                break;

            case "proxy":
                if (!isset($_GET["lang"]) and !$this->setup->getClient()->status["finish"]["status"] and $_GET["cmd"] == "proxy") {
                    $this->jumpToFirstUnfinishedSetupStep();
                } else {
                    $this->displayProxy();
                }
                break;

            case "cache":
                $this->displayCache();
                break;


            case "nic":
                if (!isset($_GET["lang"]) and !$this->setup->getClient()->status["finish"]["status"] and $_GET["cmd"] == "nic") {
                    $this->jumpToFirstUnfinishedSetupStep();
                } else {
                    $this->displayNIC();
                }
                break;

            case "finish":
                if (!isset($_GET["lang"]) and !$this->setup->getClient()->status["finish"]["status"] and $_GET["cmd"] == "finish") {
                    $this->jumpToFirstUnfinishedSetupStep();
                } else {
                    $this->displayFinishSetup();
                }
                break;

            case "changeaccess":
                $this->changeAccessMode($_GET["back"]);
                break;

            case "logout":
                $this->displayLogout();
                break;

            case "login":
                session_destroy();
                ilUtil::redirect(ILIAS_HTTP_PATH . "/login.php?client_id=" . $this->setup->getClient()->getId());
                break;

            case "login_new":
                if ($this->setup->getClient()->ini->readVariable("client", "access") != "1") {
                    $this->setup->getClient()->ini->setVariable("client", "access", "1");
                    $this->setup->getClient()->ini->write();
                }

                session_destroy();
                ilUtil::redirect(ILIAS_HTTP_PATH . "/login.php?client_id=" . $this->setup->getClient()->getId());
                break;

            case "tools":
                $this->displayTools();
                break;

            case "reloadStructure":
                $this->reloadControlStructure();
                break;

            case 'switchTree':
                $this->switchTree();
                break;

            case "saveClientIni":
            case "installDatabase":
            case "displayDatabase":
            case "updateDatabase":
            case "showUpdateSteps":
            case "saveLanguages":
            case "saveContact":
            case "displayContactData":
            case "displayNIC":
            case "saveRegistration":
            case "applyHotfix":
            case "showHotfixSteps":
            case "applyCustomUpdates":
            case "changeSettingsType":
            case "showLongerSettings":
            case "cloneSelectSource":
            case "cloneSaveSource":
            case "saveProxy":
            case "displayPassword":
            case "savePassword":
            case "saveDbSlave":
            case "saveCache":
            case "addMemcacheServer":
            case "deleteMemcacheServer":
            case "editMemcacheServer":
            case "createMemcacheServer":
            case "updateMemcacheServer":
            case "flushCache":
            case "background_tasks":
            case "edit_background_tasks":
            case "save_background_tasks":
            case "kill_waiting_tasks":
                $this->$cmd();
                break;

            default:
                $this->setDisplayMode("setup");
                $this->displayError($this->lng->txt("unknown_command") . ": '" . $this->cmd . "'");
                break;
        }
    }

    // end cmd subsets

    ////
    //// GENERAL DISPLAY FUNCTIONS
    ////

    /**
     * set display mode to 'view' or 'setup'
     * 'setup' -> show status panel and (prev/next) navigation buttons
     * 'view' -> show overall status and tabs under title bar
     *
     * @param    string      display mode
     * @return   boolean     true if display mode was successfully set
     */
    public function setDisplayMode($a_mode)
    {
        // security
        if ($a_mode != "view" and $a_mode != "setup") {
            return false;
        }

        $this->display_mode = $a_mode;
        $_SESSION["display_mode"] = $this->display_mode;

        return true;
    }

    /**
     * display header with admin links and language flags
     */
    public function displayHeader()
    {
        $languages = $this->lng->getLanguages();

        $count = (int) round(count($languages) / 2);
        $num = 1;
        
        sort($languages); // #16837
        foreach ($languages as $lang_key) {
            /*
            if ($num === $count)
            {
                $this->tpl->touchBlock("lng_new_row");
            }
            */
            $this->tpl->setCurrentBlock("languages");
            $this->tpl->setVariable("LINK_LANG", "./setup.php?cmd=" . $this->cmd . "&amp;lang=" . $lang_key);
            $this->tpl->setVariable("LANG_NAME", $this->lng->txt("meta_l_" . $lang_key));
            $this->tpl->setVariable("LANG_ICON", $lang_key);
            $this->tpl->setVariable("LANG_KEY", $lang_key);
            $this->tpl->setVariable("BORDER", 0);
            $this->tpl->setVariable("VSPACE", 0);
            $this->tpl->parseCurrentBlock();

            $num++;
        }

        if ($this->cmd != "logout" and $this->setup->isInstalled() and $this->setup->isAuthenticated()) {
            // add client link
            if ($this->setup->isAdmin()) {
                if ($this->display_mode == "view" ||
                    $this->cmd == "clientlist" ||
                    $this->cmd == "changemasterpassword" ||
                    $this->cmd == 'savemasterpassword' ||
                    $this->cmd == "mastersettings") {
                    $this->tpl->setCurrentBlock("add_client");
                    $this->tpl->setVariable("TXT_ADD_CLIENT", ucfirst($this->lng->txt("new_client")));
                    $this->tpl->parseCurrentBlock();
                }

                // client list link
                $class = ($this->active_tab == "clientlist")
                    ? "ilSMActive"
                    : "ilSMInactive";
                $this->tpl->setCurrentBlock("display_list");
                $this->tpl->setVariable("TXT_LIST", ucfirst($this->lng->txt("list_clients")));
                $this->tpl->setVariable("TAB_CLASS", $class);
                $this->tpl->parseCurrentBlock();

                // client list link
                $class = ($this->active_tab == "background_tasks")
                    ? "ilSMActive"
                    : "ilSMInactive";
                $this->tpl->setCurrentBlock("display_list");
                $this->tpl->setVariable("TXT_BACKGROUND_TASKS", ucfirst($this->lng->txt("background_tasks")));
                $this->tpl->setVariable("BACKGROUND_TASKS_CLASS", $class);
                $this->tpl->parseCurrentBlock();

                // edit paths link
                $class = ($this->active_tab == "basicsettings")
                    ? "ilSMActive"
                    : "ilSMInactive";
                $this->tpl->setCurrentBlock("edit_pathes");
                $this->tpl->setVariable("TXT_EDIT_PATHES", $this->lng->txt("basic_settings"));
                $this->tpl->setVariable("TAB_CLASS", $class);
                $this->tpl->parseCurrentBlock();

                // preliminaries
                $class = ($this->active_tab == "preliminaries")
                    ? "ilSMActive"
                    : "ilSMInactive";
                $this->tpl->setCurrentBlock("preliminaries");
                $this->tpl->setVariable("TXT_PRELIMINARIES", $this->lng->txt("preliminaries"));
                $this->tpl->setVariable("TAB_CLASS", $class);
                $this->tpl->parseCurrentBlock();

                // change password link
                $class = ($this->active_tab == "password")
                    ? "ilSMActive"
                    : "ilSMInactive";
                $this->tpl->setCurrentBlock("change_password");
                $this->tpl->setVariable("TXT_CHANGE_PASSWORD", ucfirst($this->lng->txt("password")));
                $this->tpl->setVariable("TAB_CLASS", $class);
                $this->tpl->parseCurrentBlock();
            }

            // logout link
            if ($this->setup->isAuthenticated()) {
                $this->tpl->setCurrentBlock("logout");
                $this->tpl->setVariable("TXT_LOGOUT", $this->lng->txt("logout"));
                $this->tpl->parseCurrentBlock();
            }
        }

        $this->tpl->setVariable("VAL_CMD", htmlspecialchars($_GET["cmd"]));
        $this->tpl->setVariable("TXT_CHOOSE_LANGUAGE", $this->lng->txt("choose_language"));
        $this->tpl->setVariable("PAGETITLE", "Setup");
        //$this->tpl->setVariable("LOCATION_STYLESHEET","./templates/blueshadow.css");
        $this->tpl->setVariable("LOCATION_STYLESHEET", "../templates/default/delos.css");
        $this->tpl->setVariable("LOCATION_CONTENT_STYLESHEET", "./css/setup.css");
        $this->tpl->setVariable("TXT_ILIAS_VERSION", "ILIAS " . ILIAS_VERSION);
        $this->tpl->setVariable("TXT_SETUP", $this->lng->txt("setup"));
        $this->tpl->setVariable("VERSION", $this->version);
        $this->tpl->setVariable("TXT_VERSION", $this->lng->txt("version"));
        $this->tpl->setVariable("LANG", $this->lang);
    }

    /**
     * page output and set title
     */
    public function displayFooter()
    {
        // footer (not really)
        if ($this->cmd != "logout") {
            if ($this->setup->ini_ilias_exists and $this->display_mode == "setup" and $this->setup->getClient()->getId() != "") {
                $this->tpl->setVariable("TXT_ACCESS_MODE", "(" . $this->lng->txt("client_id") . ": " . $this->setup->getClient()->getId() . ")");
            } elseif ($this->setup->isAdmin()) {
                $this->tpl->setVariable("TXT_ACCESS_MODE", "(" . $this->lng->txt("root_access") . ")");
            }

            $this->displayNavButtons();
        }

        $this->tpl->show();
    }

    /**
     * display navigation buttons
     *
     * @return   boolean     false if both buttons are deactivated
     */
    public function displayNavButtons()
    {
        if (!$this->btn_prev_on and !$this->btn_next_on) {
            return false;
        }

        $ntpl = new ilTemplate("tpl.navbuttons.html", true, true, "setup");
        //$this->tpl->addBlockFile("NAVBUTTONS","navbuttons","tpl.navbuttons.html", "setup");

        $ntpl->setVariable("FORMACTION_BUTTONS", "setup.php?cmd=gateway");

        if ($this->btn_prev_on) {
            $ntpl->setCurrentBlock("btn_back");
            $ntpl->setVariable("TXT_PREV", $this->btn_prev_lng);
            $ntpl->setVariable("CMD_PREV", $this->btn_prev_cmd);
            $ntpl->parseCurrentBlock();
        }

        if ($this->btn_next_on) {
            $ntpl->setCurrentBlock("btn_forward");
            $ntpl->setVariable("TXT_NEXT", $this->btn_next_lng);
            $ntpl->setVariable("CMD_NEXT", $this->btn_next_cmd);
            $ntpl->parseCurrentBlock();
        }

        $nav_html = $ntpl->get();
        $this->tpl->setVariable("NAVBUTTONS", $nav_html);
        if (!$this->no_second_nav) {
            $this->tpl->setVariable("NAVBUTTONS2", $nav_html);
        }
        return true;
    }

    /**
     * set previous navigation button
     *
     * @param    string      command to process on click
     * @param    string      button label
     */
    public function SetButtonPrev($a_cmd = 0, $a_lng = 0)
    {
        $this->btn_prev_on = true;
        $this->btn_prev_cmd = ($a_cmd) ? $a_cmd : "gateway";
        $this->btn_prev_lng = ($a_lng) ? $this->lng->txt($a_lng) : $this->lng->txt("prev");
    }

    /**
     * set next navigation button
     *
     * @param    string      command to process on click
     * @param    string      button label
     */
    public function SetButtonNext($a_cmd, $a_lng = 0)
    {
        $this->btn_next_on = true;
        $this->btn_next_cmd = ($a_cmd) ? $a_cmd : "gateway";
        $this->btn_next_lng = ($a_lng) ? $this->lng->txt($a_lng) : $this->lng->txt("next");
    }

    ////
    //// CLIENT OVERVIEW
    ////

    /**
     * display client overview panel
     */
    public function displayClientOverview()
    {
        $this->checkDisplayMode();

        // disable/enable button
        $btpl = new ilTemplate("tpl.buttons.html", true, true, "setup");
        $btpl->setCurrentBlock("btn");
        $btpl->setVariable("CMD", "changeaccess");
        $access_button = ($this->setup->getClient()->status["access"]["status"]) ? "disable" : "enable";
        $btpl->setVariable("TXT", $this->lng->txt($access_button));
        $btpl->setVariable("FORMACTION", "setup.php?cmd=gateway");
        $btpl->parseCurrentBlock();
        $this->tpl->setVariable("BUTTONS", $btpl->get());

        $this->initClientOverviewForm();
        $this->tpl->setVariable("SETUP_CONTENT", $this->form->getHTML());

        $this->displayStatusPanel();
    }

    /**
     * Init client overview form.
     */
    public function initClientOverviewForm()
    {
        global $lng, $ilCtrl;

        $settings = $this->setup->getClient()->getAllSettings();

        include_once("Services/Form/classes/class.ilPropertyFormGUI.php");
        $this->form = new ilPropertyFormGUI();

        $this->form->setTitle($lng->txt("client_info"));

        // installation name
        $ne = new ilNonEditableValueGUI($lng->txt("inst_name"), "inst_name");
        $ne->setValue(($this->setup->getClient()->getName())
            ? $this->setup->getClient()->getName()
            : "&lt;" . $this->lng->txt("no_client_name") . "&gt;");
        $ne->setInfo($this->setup->getClient()->getDescription());
        $this->form->addItem($ne);

        // client id
        $ne = new ilNonEditableValueGUI($lng->txt("client_id"), "client_id");
        $ne->setValue($this->setup->getClient()->getId());
        $this->form->addItem($ne);

        // nic id
        $ne = new ilNonEditableValueGUI($lng->txt("ilias_nic_id"), "nic_id");
        $ne->setValue(($this->setup->getClient()->db_installed)
            ? $settings["inst_id"]
            : $txt_no_database);
        $this->form->addItem($ne);

        // database version
        $ne = new ilNonEditableValueGUI($lng->txt("db_version"), "db_vers");
        $ne->setValue(($this->setup->getClient()->db_installed)
            ? $settings["db_version"]
            : $txt_no_database);
        $this->form->addItem($ne);

        // access status
        $ne = new ilNonEditableValueGUI($lng->txt("access_status"), "status");
        //$access_link = "&nbsp;&nbsp;[<a href=\"setup.php?cmd=changeaccess&client_id=".$this->setup->getClient()->getId()."&back=view\">".$this->lng->txt($access_button)."</a>]";
        $access_status = ($this->setup->getClient()->status["access"]["status"]) ? "online" : "disabled";
        $ne->setValue($this->lng->txt($access_status) . $access_link);
        $this->form->addItem($ne);

        // server information
        $sh = new ilFormSectionHeaderGUI();
        $sh->setTitle($this->lng->txt("server_info"));
        $this->form->addItem($sh);

        // ilias version
        $ne = new ilNonEditableValueGUI($lng->txt("ilias_version"), "il_vers");
        $ne->setValue(ILIAS_VERSION);
        $this->form->addItem($ne);

        // host
        $ne = new ilNonEditableValueGUI($lng->txt("host"), "host");
        $ne->setValue($_SERVER["SERVER_NAME"]);
        $this->form->addItem($ne);

        // ip address and port
        $ne = new ilNonEditableValueGUI($lng->txt("ip_address") . " & " .
            $lng->txt("port"));
        $ne->setValue($_SERVER["SERVER_ADDR"] . ":" . $_SERVER["SERVER_PORT"]);
        $this->form->addItem($ne);

        // server software
        $ne = new ilNonEditableValueGUI($lng->txt("server_software"), "server_softw");
        $ne->setValue($_SERVER["SERVER_SOFTWARE"]);
        $this->form->addItem($ne);

        // http path
        $ne = new ilNonEditableValueGUI($lng->txt("http_path"), "http_path");
        $ne->setValue(ILIAS_HTTP_PATH);
        $this->form->addItem($ne);

        // absolute path
        $ne = new ilNonEditableValueGUI($lng->txt("absolute_path"), "absolute_path");
        $ne->setValue(ILIAS_ABSOLUTE_PATH);
        $this->form->addItem($ne);

        // third party tools
        $sh = new ilFormSectionHeaderGUI();
        $sh->setTitle($this->lng->txt("3rd_party_software"));
        $this->form->addItem($sh);

        $tools = array("convert", "zip", "unzip", "ghostscript", "java", "ffmpeg", "phantomjs");

        foreach ($tools as $tool) {
            // tool
            $ne = new ilNonEditableValueGUI($lng->txt($tool . "_path"), $tool . "_path");
            $p = $this->setup->ini->readVariable("tools", $tool);
            $ne->setValue($p ? $p : $this->lng->txt("not_configured"));
            $this->form->addItem($ne);
        }

        // latex
        $ne = new ilNonEditableValueGUI($lng->txt("url_to_latex"), "latex_url");
        $p = $this->setup->ini->readVariable("tools", "latex"); // #13109
        $ne->setValue($p ? $p : $this->lng->txt("not_configured"));
        $this->form->addItem($ne);

        // virus scanner
        $ne = new ilNonEditableValueGUI($lng->txt("virus_scanner"), "vscan");
        $ne->setValue($this->setup->ini->readVariable("tools", "vscantype"));
        $this->form->addItem($ne);

        // scan command
        $ne = new ilNonEditableValueGUI($lng->txt("scan_command"), "scan");
        $p = $this->setup->ini->readVariable("tools", "scancommand");
        $ne->setValue($p ? $p : $this->lng->txt("not_configured"));
        $this->form->addItem($ne);

        // clean command
        $ne = new ilNonEditableValueGUI($lng->txt("clean_command"), "clean");
        $p = $this->setup->ini->readVariable("tools", "cleancommand");
        $ne->setValue($p ? $p : $this->lng->txt("not_configured"));
        $this->form->addItem($ne);


        // system styles
        $sh = new ilFormSectionHeaderGUI();
        $sh->setTitle($this->lng->txt("system_styles"));
        $this->form->addItem($sh);

        // system styles activation
        $ne = new ilNonEditableValueGUI($lng->txt("enable_system_styles_management"), "enable_system_styles_management");
        $p = $this->setup->ini->readVariable("tools", "enable_system_styles_management");
        $ne->setValue($p ? $this->lng->txt("enabled") : $this->lng->txt("not_enabled"));
        $this->form->addItem($ne);

        // lessc command
        $ne = new ilNonEditableValueGUI($lng->txt("lessc"), "lessc");
        $p = $this->setup->ini->readVariable("tools", "lessc");
        $ne->setValue($p ? $p : $this->lng->txt("not_configured"));
        $this->form->addItem($ne);


        $this->form->setFormAction("setup.php?cmd=gateway");
    }

    ////
    //// PRELIMINARIES
    ////

    /**
     * display preliminaries page
     */
    public function displayPreliminaries()
    {
        $OK = "<font color=\"green\"><strong>OK</strong></font>";
        $FAILED = "<strong><font color=\"red\">FAILED</font></strong>";

        $this->tpl->addBlockFile("CONTENT", "content", "tpl.preliminaries.html", "setup");

        $this->tpl->setVariable("TXT_SETUP_TITLE", $this->lng->txt("ilias_setup"));
        $this->tpl->setVariable("TXT_SETUP_WELCOME", $this->lng->txt("setup_welcome"));
        $this->tpl->setVariable("TXT_SETUP_INIFILE_DESC", $this->lng->txt("setup_inifile_desc"));
        $this->tpl->setVariable("TXT_SETUP_DATABASE_DESC", $this->lng->txt("setup_database_desc"));
        $this->tpl->setVariable("TXT_SETUP_LANGUAGES_DESC", $this->lng->txt("setup_languages_desc"));
        $this->tpl->setVariable("TXT_SETUP_PASSWORD_DESC", $this->lng->txt("setup_password_desc"));
        $this->tpl->setVariable("TXT_SETUP_NIC_DESC", $this->lng->txt("setup_nic_desc"));

        $server_os = php_uname();
        $server_web = $_SERVER["SERVER_SOFTWARE"];
        $environment = $this->lng->txt("env_using") . " " . $server_os . " <br/>" . $this->lng->txt("with") . " " . $server_web;

        if ((stristr($server_os, "linux") || stristr($server_os, "windows")) && stristr($server_web, "apache")) {
            $env_comment = $this->lng->txt("env_ok");
        } else {
            $env_comment = "<font color=\"red\">" . $this->lng->txt("env_warning") . "</font>";
        }

        $this->tpl->setVariable("TXT_ENV_TITLE", $this->lng->txt("environment"));
        $this->tpl->setVariable("TXT_ENV_INTRO", $environment);
        $this->tpl->setVariable("TXT_ENV_COMMENT", $env_comment);

        $this->tpl->setVariable("TXT_PRE_TITLE", $this->lng->txt("preliminaries"));
        $this->tpl->setVariable("TXT_PRE_INTRO", $this->lng->txt("pre_intro"));

        $preliminaries = array("php", "root", "folder_create",
            "cookies_enabled", "dom", "xsl", "gd", "memory");

        foreach ($preliminaries as $preliminary) {
            $this->tpl->setCurrentBlock("preliminary");
            $this->tpl->setVariable("TXT_PRE", $this->lng->txt("pre_" . $preliminary));
            if ($this->setup->preliminaries_result[$preliminary]["status"] == true) {
                $this->tpl->setVariable("STATUS_PRE", $OK);
            } else {
                $this->tpl->setVariable("STATUS_PRE", $FAILED);
            }
            $this->tpl->setVariable("COMMENT_PRE", $this->setup->preliminaries_result[$preliminary]["comment"]);
            $this->tpl->parseCurrentBlock();
        }

        // summary
        if ($this->setup->preliminaries === true) {
            if ($this->setup->isInstalled()) {
                $cmd = "mastersettings";
            } else {
                $cmd = "install";
            }
            $btn_text = ($this->cmd == "preliminaries") ? "" : "installation";
            //echo "-".$this->display_mode."-";
            $this->setButtonNext($cmd, $btn_text);
        } else {
            $this->tpl->setCurrentBlock("premessage");
            $this->tpl->setVariable("TXT_PRE_ERR", sprintf(
                $this->lng->txt("pre_error"),
                "http://www.ilias.de/docu/goto.php?target=pg_6531_367&client_id=docu"
            ));
            $this->tpl->parseCurrentBlock();
        }
    }

    ////
    //// BASIC SETTINGS
    ////

    /**
     * display master setup form & process form input
     */
    public function displayMasterSetup($a_omit_init = false)
    {
        $this->tpl->addBlockFile("CONTENT", "content", "tpl.std_layout.html", "setup");
        $this->tpl->setVariable("TXT_HEADER", $this->lng->txt("basic_settings"));
        $this->tpl->setVariable(
            "TXT_INFO",
            $this->lng->txt("info_text_first_install") . "<br/>" . $this->lng->txt("info_text_pathes")
        );

        $this->setButtonPrev("preliminaries");

        if ($this->setup->isInstalled()) {
            $this->setButtonNext("list");
        }

        if (!$a_omit_init) {
            $this->initBasicSettingsForm(true);
        }
        $this->tpl->setVariable("SETUP_CONTENT", "<br>" . $this->form->getHTML() . "<br>");
    }

    /**
     * display master settings and process form input
     */
    public function changeMasterSettings($a_omit_init = false)
    {
        $this->tpl->addBlockFile("CONTENT", "content", "tpl.std_layout.html", "setup");
        $this->tpl->setVariable("TXT_HEADER", $this->lng->txt("basic_settings"));
        $this->tpl->setVariable("TXT_INFO", $this->lng->txt("info_text_pathes"));

        $this->btn_next_on = true;
        $this->btn_next_lng = $this->lng->txt("create_new_client") . "...";
        $this->btn_next_cmd = "newclient";

        if (!$a_omit_init) {
            $this->initBasicSettingsForm();
            $this->getBasicSettingsValues();
        }
        $this->tpl->setVariable("SETUP_CONTENT", "<br>" . $this->form->getHTML() . "<br>");
    }

    /**
     * Init basic settings form.
     */
    public function initBasicSettingsForm($a_install = false)
    {
        global $lng, $ilCtrl;

        include_once("Services/Form/classes/class.ilPropertyFormGUI.php");
        $this->form = new ilPropertyFormGUI();

        // webspace dir
        $ne = new ilNonEditableValueGUI($lng->txt("data_directory_in_ws"), "webspace_dir");
        if ($a_install) {
            $ne->setInfo($this->lng->txt("data_directory_in_ws_info"));
        }
        $cwd = ilUtil::isWindows()
            ? str_replace("\\", "/", getcwd())
            : getcwd();

        $ne->setValue($cwd . "/data");
        $this->form->addItem($ne);

        // data dir
        if ($a_install) {
            $ti = new ilTextInputGUI($lng->txt("data_directory_outside_ws"), "datadir_path");
            $ti->setInfo($lng->txt("data_directory_info"));
            $ti->setRequired(true);
            $this->form->addItem($ti);
        } else {
            $ne = new ilNonEditableValueGUI($lng->txt("data_directory_outside_ws"), "data_dir");
            $this->form->addItem($ne);
        }

        $lvext = (ilUtil::isWindows())
            ? "_win"
            : "";


        // logging
        $sh = new ilFormSectionHeaderGUI();
        $sh->setTitle($lng->txt("logging"));
        $this->form->addItem($sh);

        // path to log file
        $ti = new ilTextInputGUI($lng->txt("log_path"), "log_path");
        $ti->setInfo($lng->txt("log_path_comment" . $lvext));
        $this->form->addItem($ti);

        // disable logging
        $cb = new ilCheckboxInputGUI($lng->txt("disable_logging"), "chk_log_status");
        $this->form->addItem($cb);

        // path to error log dir
        $ti = new ilTextInputGUI($lng->txt("error_log_path"), "error_log_path");
        $ti->setInfo($lng->txt("error_log_path_comment" . $lvext));
        $this->form->addItem($ti);

        // server settings
        $sh = new ilFormSectionHeaderGUI();
        $sh->setTitle($lng->txt("server_settings"));
        $this->form->addItem($sh);

        // time zone
        include_once("./Services/Calendar/classes/class.ilCalendarUtil.php");
        $si = new ilSelectInputGUI($lng->txt("time_zone"), "time_zone");
        $si->setOptions(array_merge(
            array("" => "-- " . $lng->txt("please_select") . " --"),
            ilCalendarUtil::_getShortTimeZoneList()
        ));
        $si->setRequired(true);
        $this->form->addItem($si);

        // https settings
        $sh = new ilFormSectionHeaderGUI();
        $sh->setTitle($lng->txt("https_settings"));
        $this->form->addItem($sh);

        $check = new ilCheckboxInputGUI($lng->txt('ps_auto_https'), 'auto_https_detect_enabled');
        $check->setOptionTitle($lng->txt('ps_auto_https_description'));
        $check->setValue(1);

        $text = new ilTextInputGUI($lng->txt('ps_auto_https_header_name'), 'auto_https_detect_header_name');
        $text->setSize(24);
        $text->setMaxLength(64);
        $text->setRequired(true);
        $check->addSubItem($text);

        $text = new ilTextInputGUI($lng->txt('ps_auto_https_header_value'), 'auto_https_detect_header_value');
        $text->setSize(24);
        $text->setMaxLength(64);
        $text->setRequired(true);
        $check->addSubItem($text);

        $this->form->addItem($check);

        // required 3rd party tools
        $sh = new ilFormSectionHeaderGUI();
        $sh->setTitle($lng->txt("3rd_party_software_req"));
        $this->form->addItem($sh);

        // convert path
        $ti = new ilTextInputGUI($lng->txt("convert_path"), "convert_path");
        $ti->setInfo($lng->txt("convert_path_comment" . $lvext));
        $ti->setRequired(true);
        $this->form->addItem($ti);

        // zip path
        $ti = new ilTextInputGUI($lng->txt("zip_path"), "zip_path");
        $ti->setInfo($lng->txt("zip_path_comment" . $lvext));
        $ti->setRequired(true);
        $this->form->addItem($ti);

        // unzip path
        $ti = new ilTextInputGUI($lng->txt("unzip_path"), "unzip_path");
        $ti->setInfo($lng->txt("unzip_path_comment" . $lvext));
        $ti->setRequired(true);
        $this->form->addItem($ti);

        // optional 3rd party tools
        $sh = new ilFormSectionHeaderGUI();
        $sh->setTitle($lng->txt("3rd_party_software_opt"));
        $this->form->addItem($sh);

        // ghostscript path
        $ti = new ilTextInputGUI($lng->txt("ghostscript_path"), "ghostscript_path");
        $ti->setInfo($lng->txt("ghostscript_path_comment" . $lvext));
        $this->form->addItem($ti);

        // ffmpeg path
        $ti = new ilTextInputGUI($lng->txt("ffmpeg_path"), "ffmpeg_path");
        $ti->setInfo($lng->txt("ffmpeg_path_comment"));
        $this->form->addItem($ti);

        // phantomjs path
        $pj = new ilTextInputGUI($lng->txt("phantomjs_path"), "phantomjs_path");
        $pj->setInfo($lng->txt("phantomjs_path_comment"));
        $this->form->addItem($pj);

        // latex
        $ti = new ilTextInputGUI($lng->txt("url_to_latex"), "latex_url");
        $ti->setInfo($lng->txt("latex_url_comment"));
        $this->form->addItem($ti);

        // virus scanner
        $options = array(
            "none" => $lng->txt("none"),
            "sophos" => $lng->txt("sophos"),
            "antivir" => $lng->txt("antivir"),
            "clamav" => $lng->txt("clamav")
            );
        $si = new ilSelectInputGUI($lng->txt("virus_scanner"), "vscanner_type");
        $si->setOptions($options);
        $this->form->addItem($si);

        // scan command
        $ti = new ilTextInputGUI($lng->txt("scan_command"), "scan_command");
        $this->form->addItem($ti);

        // clean command
        $ti = new ilTextInputGUI($lng->txt("clean_command"), "clean_command");
        $this->form->addItem($ti);

        // system styles
        $sh = new ilFormSectionHeaderGUI();
        $sh->setTitle($this->lng->txt("system_styles"));
        $this->form->addItem($sh);

        // enabled system styles mangesment
        $check = new ilCheckboxInputGUI($lng->txt('enable_system_styles_management'), 'enable_system_styles_management');
        $check->setInfo($lng->txt('enable_system_styles_management_info'));
        $check->setValue(1);

        // lessc command
        $lessc = new ilTextInputGUI($lng->txt("lessc_path"), "lessc_path");
        $lessc->setInfo($lng->txt("lessc_path_comment"));
        $check->addSubItem($lessc);

        $this->form->addItem($check);


        if ($a_install) {
            $sh = new ilFormSectionHeaderGUI();
            $sh->setTitle($lng->txt("master_password"));
            $this->form->addItem($sh);

            // password
            $pi = new ilPasswordInputGUI($lng->txt("password"), "password");
            $pi->setRequired(true);
            $pi->setSkipSyntaxCheck(true);
            $pi->setInfo($lng->txt("password_info"));
            $this->form->addItem($pi);
        }

        if ($a_install) {
            $this->form->addCommandButton("saveBasicSettings", $lng->txt("save"));
        } else {
            $this->form->addCommandButton("updateBasicSettings", $lng->txt("save"));
            $this->form->addCommandButton("determineToolsPath", $lng->txt("determine_tools_paths"));
        }

        $this->form->setTitle($lng->txt("data_directories"));
        $this->form->setFormAction("setup.php?cmd=gateway");

        if ($a_install) {
            $det = $this->determineTools();
            $this->form->setValuesByArray($det);
        }
    }

    /**
     * Get current values for basic settings from
     */
    public function getBasicSettingsValues()
    {
        $values = array();

        $values["webspace_dir"] = getcwd() . "/data";
        $values["data_dir"] = $this->setup->ini->readVariable("clients", "datadir");
        $values["convert_path"] = $this->setup->ini->readVariable("tools", "convert");
        $values["zip_path"] = $this->setup->ini->readVariable("tools", "zip");
        $values["unzip_path"] = $this->setup->ini->readVariable("tools", "unzip");
        $values["ghostscript_path"] = $this->setup->ini->readVariable("tools", "ghostscript");
        //$values["mkisofs_path"] = $this->setup->ini->readVariable("tools","mkisofs");
        $values["ffmpeg_path"] = $this->setup->ini->readVariable("tools", "ffmpeg");
        $values["phantomjs_path"] = $this->setup->ini->readVariable("tools", "phantomjs");
        $values["latex_url"] = $this->setup->ini->readVariable("tools", "latex");
        $values["fop_path"] = $this->setup->ini->readVariable("tools", "fop");
        $values["vscanner_type"] = $this->setup->ini->readVariable("tools", "vscantype");
        $values["scan_command"] = $this->setup->ini->readVariable("tools", "scancommand");
        $values["clean_command"] = $this->setup->ini->readVariable("tools", "cleancommand");
        $values["enable_system_styles_management"] = $this->setup->ini->readVariable("tools", "enable_system_styles_management");
        $values["lessc_path"] = $this->setup->ini->readVariable("tools", "lessc");
        $values["log_path"] = $this->setup->ini->readVariable("log", "path") . "/" .
            $this->setup->ini->readVariable("log", "file");
        $values["chk_log_status"] = !$this->setup->ini->readVariable("log", "enabled");
        $values["error_log_path"] = $this->setup->ini->readVariable("log", "error_path");
        $values["time_zone"] = $this->setup->ini->readVariable("server", "timezone");

        // https settings
        $values["auto_https_detect_enabled"] = $this->setup->ini->readVariable("https", "auto_https_detect_enabled");
        $values["auto_https_detect_header_name"] = $this->setup->ini->readVariable("https", "auto_https_detect_header_name");
        $values["auto_https_detect_header_value"] = $this->setup->ini->readVariable("https", "auto_https_detect_header_value");

        $this->form->setValuesByArray($values);
    }

    /**
     * Save basic settings form
     */
    public function saveBasicSettings()
    {
        global $tpl, $lng, $ilCtrl;

        $this->initBasicSettingsForm(true);

        if ($this->form->checkInput()) {
            // correct paths on windows
            if (ilUtil::isWindows()) {
                $fs = array("datadir_path", "log_path", "convert_path", "zip_path",
                    "unzip_path", "ghostscript_path", "ffmpeg_path","lessc_path", "phantomjs_path");
                foreach ($fs as $f) {
                    $_POST[$f] = str_replace("\\", "/", $_POST[$f]);
                }
            }
            $_POST["setup_pass"] = $_POST["password"];
            $_POST["setup_pass2"] = $_POST["password_retype"];
            if (!$this->setup->checkDataDirSetup($_POST)) {
                $i = $this->form->getItemByPostVar("datadir_path");
                $i->setAlert($this->lng->txt($this->setup->getError()));
                ilUtil::sendFailure($this->lng->txt("form_input_not_valid"), true);
            } elseif (!$this->setup->checkLogSetup($_POST)) {
                $i = $this->form->getItemByPostVar("log_path");
                $i->setAlert($this->lng->txt($this->setup->getError()));
                ilUtil::sendFailure($this->lng->txt("form_input_not_valid"), true);
            } elseif (!$this->setup->checkErrorLogSetup($_POST["error_log_path"])) {
                $i = $this->form->getItemByPostVar("error_log_path");
                $i->setAlert($this->lng->txt($this->setup->getError()));
                ilUtil::sendFailure($this->lng->txt("form_input_not_valid"), true);
            } elseif (!$this->setup->checkPasswordSetup($_POST)) {
                ilUtil::sendFailure($this->lng->txt($this->setup->getError()), true);
            } elseif (!$this->setup->saveMasterSetup($_POST)) {
                ilUtil::sendFailure($this->lng->txt($this->setup->getError()), true);
            } else {
                ilUtil::sendSuccess($this->lng->txt("settings_saved"), true);
                ilUtil::redirect("setup.php?cmd=mastersettings");
            }
        }

        $this->form->setValuesByPost();
        $this->displayMasterSetup(true);
    }

    /**
     * Update basic settings form
     */
    public function updateBasicSettings()
    {
        global $tpl, $lng, $ilCtrl;

        $this->initBasicSettingsForm();

        if ($this->form->checkInput()) {
            if (ilUtil::isWindows()) {
                $fs = array("datadir_path", "log_path", "convert_path", "zip_path",
                    "unzip_path", "ghostscript_path", "ffmpeg_path","lessc_path", "phantomjs_path");
                foreach ($fs as $f) {
                    $_POST[$f] = str_replace("\\", "/", $_POST[$f]);
                }
            }

            if (!$this->setup->checkLogSetup($_POST)) {
                $i = $this->form->getItemByPostVar("log_path");
                $i->setAlert($this->lng->txt($this->setup->getError()));
                ilUtil::sendFailure($this->lng->txt("form_input_not_valid"), true);
            } elseif (!$this->setup->checkErrorLogSetup($_POST["error_log_path"])) {
                $i = $this->form->getItemByPostVar("error_log_path");
                $i->setAlert($this->lng->txt($this->setup->getError()));
                ilUtil::sendFailure($this->lng->txt("form_input_not_valid"), true);
            } elseif (!$this->setup->updateMasterSettings($_POST)) {
                ilUtil::sendFailure($this->lng->txt($this->setup->getError()), true);
            } else {
                ilUtil::sendSuccess($this->lng->txt("settings_saved"), true);
                ilUtil::redirect("setup.php?cmd=mastersettings");
            }
        }

        $this->form->setValuesByPost();
        $this->changeMasterSettings(true);
    }

    ////
    //// LOGIN
    ////

    /**
     * login to a client
     */
    public function loginClient()
    {
        session_destroy();

        ilUtil::redirect(ILIAS_HTTP_PATH . "/login.php?client_id=" . $this->setup->getClient()->getId());
    }

    /**
     * display login form and process form
     */
    public function displayLogin($a_omit_minit = false, $a_omit_cinit = false)
    {
        global $lng;
        $this->tpl->setVariable("SETUP_LOGIN_CLASS", " ilSetupLogin");
        $this->tpl->addBlockFile("CONTENT", "content", "tpl.std_layout.html", "setup");

        if ($a_omit_minit) {
            $m_form = $this->form->getHTML();
        }
        if (!$a_omit_cinit) {
            $this->initClientLoginForm();
        }
        $cl_form = $this->form->getHTML();
        if (!$a_omit_minit) {
            $this->initMasterLoginForm();
            $m_form = $this->form->getHTML();
        }
        $this->tpl->setVariable("SETUP_CONTENT", $cl_form . "<br>" . $m_form);
        $this->tpl->setVariable("TXT_HEADER", $lng->txt("login"));
    }

    /**
    * Master Login
    */
    public function performMLogin()
    {
        $this->initMasterLoginForm();
        if ($this->form->checkInput()) {
            $i = $this->form->getItemByPostVar('mpassword');
            if (!$this->setup->loginAsAdmin($_POST['mpassword'])) {
                $i->setAlert($this->lng->txt('login_invalid'));
            } else {
                // everything ok -> we are authenticated
                ilUtil::redirect('setup.php');
            }
        }

        // something wrong -> display login again
        $this->form->setValuesByPost();
        $this->displayLogin(true);
    }

    /**
     * Login
     */
    public function performLogin()
    {
        $this->initClientLoginForm();
        if ($this->form->checkInput()) {
            $i = $this->form->getItemByPostVar("password");
            if (!$this->setup->loginAsClient(
                array("client_id" => $_POST["client_id"],
                "username" => $_POST["username"], "password" => $_POST["password"])
            )) {
                $i->setAlert($this->setup->getError());
            } else {
                // everything ok -> we are authenticated
                ilUtil::redirect("setup.php");
            }
        }

        // something wrong -> display login again
        $this->form->setValuesByPost();
        $this->displayLogin(false, true);
    }

    /**
    * Init client login form.
    */
    public function initClientLoginForm()
    {
        global $lng, $ilCtrl;

        include_once("Services/Form/classes/class.ilPropertyFormGUI.php");
        $this->form = new ilPropertyFormGUI();
        $this->form->setId("client_login");

        // client id
        $ti = new ilTextInputGUI($lng->txt("client_id"), "client_id");
        $ti->setMaxLength(32);
        $ti->setSize(20);
        $this->form->addItem($ti);

        // username
        $ti = new ilTextInputGUI($lng->txt("username"), "username");
        $ti->setSize(20);
        $this->form->addItem($ti);

        // password
        $pi = new ilPasswordInputGUI($lng->txt("password"), "password");
        $pi->setSize(20);
        $pi->setRetype(false);
        $pi->setSkipSyntaxCheck(true);
        $this->form->addItem($pi);

        $this->form->addCommandButton("performLogin", $lng->txt("login"));

        $this->form->setTitle($lng->txt("client_login"));
        $this->form->setFormAction("setup.php?cmd=gateway");
    }

    /**
    * Init master login form.
    */
    public function initMasterLoginForm()
    {
        global $lng, $ilCtrl;

        include_once("Services/Form/classes/class.ilPropertyFormGUI.php");
        $this->form = new ilPropertyFormGUI();
        $this->form->setId("master_login");
        // password
        $pi = new ilPasswordInputGUI($lng->txt("password"), "mpassword");
        $pi->setSize(20);
        $pi->setRetype(false);
        $pi->setSkipSyntaxCheck(true);
        $this->form->addItem($pi);

        $this->form->addCommandButton("performMLogin", $lng->txt("login"));

        $this->form->setTitle($lng->txt("admin_login"));
        $this->form->setFormAction("setup.php?cmd=gateway");
    }

    ////
    //// CLIENT LIST
    ////

    /**
     * display client list and process form input
     */
    public function displayClientList()
    {
        $_SESSION["ClientId"] = "";

        $this->tpl->addBlockFile("CONTENT", "content", "tpl.clientlist.html", "setup");
        $this->tpl->setVariable("TXT_INFO", $this->lng->txt("info_text_list"));
        ilUtil::sendInfo();

        // common
        $this->tpl->setVariable("TXT_HEADER", $this->lng->txt("list_clients"));
        $this->tpl->setVariable("TXT_LISTSTATUS", ($this->setup->ini->readVariable("clients", "list")) ? $this->lng->txt("display_clientlist") : $this->lng->txt("hide_clientlist"));
        $this->tpl->setVariable("TXT_TOGGLELIST", ($this->setup->ini->readVariable("clients", "list")) ? $this->lng->txt("disable") : $this->lng->txt("enable"));

        include_once("./setup/classes/class.ilClientListTableGUI.php");
        $tab = new ilClientListTableGUI($this->setup);
        $this->tpl->setVariable("CLIENT_LIST", $tab->getHTML());

        // create new client button
        $this->btn_next_on = true;
        $this->btn_next_lng = $this->lng->txt("create_new_client") . "...";
        $this->btn_next_cmd = "newclient";
    }

    /**
    * Determine tools paths
    */
    public function determineToolsPath()
    {
        $_POST = $this->determineTools($_POST);
        $this->updateBasicSettings();
    }

    /**
    * Determine tools paths
    */
    public function determineToolsPathInstall()
    {
        $this->displayMasterSetup(true);
    }

    /**
    * Determine Tools
    */
    public function determineTools($a_tools = array())
    {
        $cwd = ilUtil::isWindows()
            ? str_replace("\\", "/", getcwd())
            : getcwd();
        if (!ilUtil::isWindows()) {
            $tools = array("convert" => "convert",
                "zip" => "zip", "unzip" => "unzip", "ghostscript" => "gs",
                "java" => "java", "ffmpeg" => "ffmpeg", "lessc" => "lessc", "phantomjs" => "phantomjs");
            $dirs = array("/usr/local", "/usr/local/bin", "/usr/bin", "/bin", "/sw/bin", "/usr/bin");
        } else {
            $tools = array("convert" => "convert.exe",
                "zip" => "zip.exe", "unzip" => "unzip.exe");
            $dirs = array();
        }
        foreach ($tools as $k => $tool) {
            // try which command
            unset($ret);
            @exec("which " . $tool, $ret);
            if (substr($ret[0], 0, 3) != "no " && substr($ret[0], 0, 1) == "/") {
                $a_tools[$k . "_path"] = $ret[0];
                continue;
            }

            // try common directories
            foreach ($dirs as $dir) {
                if (@is_file($dir . "/" . $tool)) {
                    $a_tools[$k . "_path"] = $dir . "/" . $tool;
                    continue;
                }
            }
        }
        return $a_tools;
    }


    ////
    //// NEW CLIENT STEP 1: SELECT DB TYPE
    ////

    /**
     * Select database type
     *
     */
    public function selectDBType()
    {
        $this->checkDisplayMode("create_new_client");

        unset($_SESSION["db_type"]);
        $this->initDBSelectionForm();
        $this->tpl->setVariable("SETUP_CONTENT", $this->form->getHTML());

        if ($this->setup->getClient()->status["ini"]["status"]) {
            $this->setButtonNext("db");
        }

        $this->checkPanelMode();
    }

    /**
     * Init db selection form.
     */
    public function initDBSelectionForm()
    {
        global $lng;

        require_once('./Services/Form/classes/class.ilPropertyFormGUI.php');
        $this->form = new ilPropertyFormGUI();

        // db type
        $options = ilDBConstants::getAvailableTypes(true);
        $si = new ilSelectInputGUI($lng->txt("db_type"), "db_type");
        $si->setOptions($options);
        $si->setInfo($lng->txt(""));
        $this->form->addItem($si);

        $this->form->addCommandButton("selectdbtype", $lng->txt("save"));

        $this->form->setTitle($lng->txt("db_selection"));
        $this->form->setFormAction("setup.php?cmd=gateway");
    }

    ////
    //// NEW CLIENT STEP 2: SELECT DB TYPE
    ////

    /**
     * display setup in step
     */
    public function displayIni($a_omit_form_init = false)
    {
        $this->checkDisplayMode("create_new_client");

        if ($_POST["db_type"] != "") {
            $_SESSION["db_type"] = $_POST["db_type"];
        } else {
            $_POST["db_type"] = $_SESSION["db_type"];
        }

        $has_ini = $this->setup->getClient()->status["ini"]["status"];

        // use value from client ini if setup was resumed (no value in session)
        if (!$_SESSION["db_type"] && $has_ini) {
            $_SESSION["db_type"] = $this->setup->getClient()->getDbType();
        }

        $this->tpl->setVariable("TXT_INFO", $this->lng->txt("info_text_ini"));
        if (!$a_omit_form_init) {
            $this->initClientIniForm();
            $this->getClientIniValues();
        }
        $this->tpl->setVariable("SETUP_CONTENT", $this->form->getHTML());

        if ($has_ini) {
            $this->setButtonNext("db");
        }

        $this->checkPanelMode();
    }

    /**
     * Init client ini form.
     */
    protected function initClientIniForm()
    {
        global $lng;

        include_once("Services/Form/classes/class.ilPropertyFormGUI.php");
        $this->form = new ilPropertyFormGUI();

        // client id
        if ($this->setup->ini_client_exists) {
            $hi = new ilHiddenInputGUI("client_id");
            $hi->setValue($this->client_id);
            $this->form->addItem($hi);

            $ne = new ilNonEditableValueGUI($lng->txt("client_id"), "hh");
            $ne->setValue($this->client_id);
            $this->form->addItem($ne);
        } else {
            $ti = new ilTextInputGUI($lng->txt("client_id"), "client_id");
            $ti->setMaxLength(32);
            $ti->setRequired(true);
            $this->form->addItem($ti);
        }

        // database connection
        $sh = new ilFormSectionHeaderGUI();
        $sh->setTitle($lng->txt("db_conn"));
        $this->form->addItem($sh);

        // db type
        $ne = new ilNonEditableValueGUI($lng->txt("db_type"), "dbt");
        $at = ilDBConstants::getAvailableTypes(true);
        $ne->setValue($at[$_SESSION["db_type"]]);
        $this->form->addItem($ne);

        // db host
        $ti = new ilTextInputGUI($lng->txt("db_host"), "db_host");
        $ti->setMaxLength(120);
        $ti->setRequired(true);
        $this->form->addItem($ti);

        // db name
        $ti = new ilTextInputGUI($lng->txt("db_name"), "db_name");
        $ti->setRequired(true);
        $ti->setMaxLength(40);
        $this->form->addItem($ti);

        // db user
        $ti = new ilTextInputGUI($lng->txt("db_user"), "db_user");
        $ti->setMaxLength(40);
        $ti->setRequired(true);
        $this->form->addItem($ti);

        // db port
        $ti = new ilTextInputGUI($lng->txt("db_port"), "db_port");
        $ti->setMaxLength(8);
        $this->form->addItem($ti);

        $sqlPassword = new \ilPasswordInputGUI($lng->txt('db_pass'), 'db_pass');
        $sqlPassword->setDisableHtmlAutoComplete(true);
        $sqlPassword->setValidateAuthPost(false);
        $sqlPassword->setSkipSyntaxCheck(true);
        $sqlPassword->setRequired(false);
        $sqlPassword->setMaxLength(40);
        $sqlPassword->setRetype(false);
        $this->form->addItem($sqlPassword);

        $this->form->addCommandButton("saveClientIni", $lng->txt("save"));

        $this->form->setTitle($lng->txt("inst_identification"));
        $this->form->setFormAction("setup.php?cmd=gateway");
    }

    /**
     * Get current values for client ini from
     */
    public function getClientIniValues()
    {
        $values = array();

        $values["db_host"] = $this->setup->getClient()->getDbHost();
        $values["db_user"] = $this->setup->getClient()->getDbUser();
        $values["db_port"] = $this->setup->getClient()->getDbPort();
        $password = '';
        if (is_string($this->setup->getClient()->getDbPass()) && strlen($this->setup->getClient()->getDbPass())) {
            $password = self::UI_PASSWORD_PLACEHOLDER;
        }
        $values["db_pass"] = $password;
        $values["db_name"] = $this->setup->getClient()->getDbName();
        $values["client_id"] = $this->setup->getClient()->getId();

        $this->form->setValuesByArray($values);
    }
    
    /**
     * Save client ini form
     */
    public function saveClientIni()
    {
        $this->initClientIniForm();
        if ($this->form->checkInput()) {
            if (strlen($_POST["client_id"]) != strlen(urlencode(($_POST["client_id"])))
                || !$this->setup->isValidClientId($_POST["client_id"])
            ) {
                $i = $this->form->getItemByPostVar("client_id");
                $i->setAlert($this->lng->txt("ini_client_id_invalid"));
                ilUtil::sendFailure($this->lng->txt("ini_client_id_invalid"), true);
            } else {
                if (strlen($_POST["client_id"]) < 4) {
                    $i = $this->form->getItemByPostVar("client_id");
                    $i->setAlert($this->lng->txt("ini_client_id_too_short"));
                    ilUtil::sendFailure($this->lng->txt("ini_client_id_too_short"), true);
                } else {
                    if (strlen($_POST["client_id"]) > 32) {
                        $i = $this->form->getItemByPostVar("client_id");
                        $i->setAlert($this->lng->txt("ini_client_id_too_long"));
                        ilUtil::sendFailure($this->lng->txt("ini_client_id_too_long"), true);
                    } else {
                        if (!$this->setup->ini_client_exists && file_exists(ILIAS_ABSOLUTE_PATH . "/" . ILIAS_WEB_DIR . "/" . $_POST["client_id"])) {
                            $i = $this->form->getItemByPostVar("client_id");
                            $i->setAlert($this->lng->txt("ini_client_id_exists"));
                            ilUtil::sendFailure($this->lng->txt("ini_client_id_exists"), true);
                        } else {

                            // save some old values
                            $old_db_name = $this->setup->getClient()->getDbName();
                            $old_db_type = $this->setup->getClient()->getDbType();
                            $old_client_id = $this->setup->getClient()->getId();

                            // create new client object if it does not exist
                            if (!$this->setup->ini_client_exists) {
                                $client_id = $_POST["client_id"];
                                $this->setup->newClient($client_id);
                            }

                            // set client data
                            $this->setup->getClient()->setId($_POST["client_id"]);
                            $this->setup->getClient()->setDbHost($_POST["db_host"]);
                            $this->setup->getClient()->setDbName($_POST["db_name"]);
                            $this->setup->getClient()->setDbUser($_POST["db_user"]);
                            $this->setup->getClient()->setDbPort($_POST["db_port"]);
                            $dbPassword = (string) ($_POST["db_pass"] ?? '');
                            if ('' === $dbPassword || $dbPassword !== self::UI_PASSWORD_PLACEHOLDER) {
                                $this->setup->getClient()->setDbPass($dbPassword);
                            }
                            $this->setup->getClient()->setDbType($_SESSION["db_type"]);
                            $this->setup->getClient()->setDSN();

                            // try to connect to database
                            if (!$this->setup->getClient()->getDBSetup(false)->isConnectable()) {
                                $i = $this->form->getItemByPostVar("db_host");
                                $i->setAlert($this->lng->txt($this->setup->getClient()->getError()));
                                ilUtil::sendFailure($this->setup->getClient()->getError(), true);
                            } else {
                                // check if db exists
                                $db_installed = $this->setup->getClient()->getDBSetup(false)->isDatabaseInstalled();

                                if ($db_installed and (!$this->setup->ini_ilias_exists or ($this->setup->getClient()->getDbName() != $old_db_name))) {
                                    $_POST["db_name"] = $old_db_name;
                                    $message = ucfirst($this->lng->txt("database")) . " \"" . $this->setup->getClient()->getDbName() . "\" "
                                               . $this->lng->txt("ini_db_name_exists");
                                    $i = $this->form->getItemByPostVar("db_name");
                                    $i->setAlert($message);
                                    ilUtil::sendFailure($message, true);
                                } else {
                                    // all ok. create client.ini and save posted data
                                    if (!$this->setup->ini_client_exists) {
                                        if ($this->setup->saveNewClient()) {
                                            ilUtil::sendSuccess($this->lng->txt("settings_saved"), true);
                                            $this->setup->getClient()->status["ini"]["status"] = true;
                                            $_SESSION["ClientId"] = $client_id;
                                            ilUtil::redirect("setup.php?cmd=displayIni&client_id=" . $client_id);
                                        } else {
                                            $err = $this->setup->getError();
                                            ilUtil::sendFailure($this->lng->txt("save_error") . ": " . $err, true);
                                            $this->setup->getClient()->status["ini"]["status"] = false;
                                            $this->setup->getClient()->status["ini"]["comment"] = $err;
                                        }
                                    } else {
                                        if ($this->setup->getClient()->ini->write()) {
                                            ilUtil::sendSuccess($this->lng->txt("settings_changed"));
                                            $this->setup->getClient()->status["ini"]["status"] = true;
                                            ilUtil::redirect("setup.php?cmd=displayIni");
                                        } else {
                                            $err = $this->setup->getClient()->ini->getError();
                                            ilUtil::sendFailure($this->lng->txt("save_error") . ": " . $err, true);
                                            $this->setup->getClient()->status["ini"]["status"] = false;
                                            $this->setup->getClient()->status["ini"]["comment"] = $err;
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        }

        $this->form->setValuesByPost();
        $this->displayIni(true);
    }

    /**
     * display error page
     *
     * @param    string  error message
     */
    public function displayError($a_message)
    {
        $this->setDisplayMode("view");

        $this->tpl->addBlockFile("CONTENT", "content", "tpl.error.html", "setup");

        $this->tpl->setCurrentBlock("content");
        $this->tpl->setVariable("FORMACTION", $_SESSION["referer"]);
        $this->tpl->setVariable("TXT_BACK", $this->lng->txt("back"));
        $this->tpl->setVariable("ERROR_MESSAGE", ($a_message));
        $this->tpl->parseCurrentBlock();

        //		$this->tpl->show();
//		exit();
    }

    /**
     * display logout page
     */
    public function displayLogout()
    {
        $this->tpl->addBlockFile("CONTENT", "content", "tpl.logout.html", "setup");

        session_destroy();

        $this->logged_out = true;
        $this->tpl->setVariable("TXT_HEADER", $this->lng->txt("logged_out"));
        $this->tpl->setCurrentBlock("home_link");
        $this->tpl->setVariable("TXT_INDEX", $this->lng->txt("ilias_homepage"));
        $this->tpl->setVariable("LNK_INDEX", ILIAS_HTTP_PATH . "/index.php");
        $this->tpl->parseCurrentBlock();
    }

    /**
     * display process panel
     */
    public function displayProcessPanel()
    {
        include_once("./Services/UIComponent/Checklist/classes/class.ilChecklistGUI.php");
        $checklist = new ilChecklistGUI();
        $checklist->setHeading($this->lng->txt("setup_process_status"));


        $OK = "<font color=\"green\"><strong>OK</strong></font>";

        $steps = $this->setup->getStatus();
        
        // #16846
        $first = array("selectdb" => array(
            "status" => ((bool) $_SESSION["db_type"] || (bool) $steps["ini"]["status"]),
            "text" => $this->lng->txt("db_selection"),
            "comment" => ""
        ));
        
        $steps = $first + $steps;

        // remove access step
        unset($steps["access"]);

        $steps["ini"]["text"] = $this->lng->txt("setup_process_step_ini");
        $steps["db"]["text"] = $this->lng->txt("setup_process_step_db");
        //$steps["sess"]["text"]      = $this->lng->txt("setup_process_step_sess");
        $steps["lang"]["text"] = $this->lng->txt("setup_process_step_lang");
        $steps["contact"]["text"] = $this->lng->txt("setup_process_step_contact");
        $steps["proxy"]["text"] = $this->lng->txt("setup_process_step_proxy");
        $steps["nic"]["text"] = $this->lng->txt("setup_process_step_nic");
        $steps["finish"]["text"] = $this->lng->txt("setup_process_step_finish");

        $stpl = new ilTemplate("tpl.process_panel.html", true, true, "setup");

        $num = 1;

        foreach ($steps as $key => $val) {
            $stpl->setCurrentBlock("menu_row");
            $stpl->setVariable("TXT_STEP", $this->lng->txt("step") . " " . $num . ": &nbsp;");
            $stpl->setVariable("TXT_ACTION", $val["text"]);
            $stpl->setVariable("IMG_ARROW", "spacer.png");

            if ($this->cmd == $key and isset($this->cmd)) {
                $stpl->setVariable("HIGHLIGHT", " style=\"font-weight:bold;\"");
            }

            $status = ($val["status"]) ? $OK : "";

            $stpl->setVariable("TXT_STATUS", $status);
            $stpl->parseCurrentBlock();

            $checklist->addEntry(
                $num . ". " . $val["text"],
                "",
                ($val["status"]) ?
                    ilChecklistGUI::STATUS_OK : ilChecklistGUI::STATUS_NOT_OK,
                ($this->cmd == $key and isset($this->cmd)),
                ""
            );

            $num++;
        }

        $stpl->setVariable("TXT_SETUP_PROCESS_STATUS", $this->lng->txt("setup_process_status"));

        $this->tpl->setVariable("PROCESS_MENU", $checklist->getHTML());
    }

    /**
     * display status panel
     */
    public function displayStatusPanel()
    {
        include_once("./Services/UIComponent/Checklist/classes/class.ilChecklistGUI.php");
        $checklist = new ilChecklistGUI();
        $checklist->setHeading($this->lng->txt("overall_status"));
        
        $OK = "<font color=\"green\"><strong>OK</strong></font>";

        //$this->tpl->addBlockFile("STATUS_PANEL","status_panel","tpl.status_panel.html", "setup");

        $this->tpl->setVariable("TXT_OVERALL_STATUS", $this->lng->txt("overall_status"));
        // display status
        if ($this->setup->getClient()->status) {
            foreach ($this->setup->getClient()->status as $key => $val) {
                $status = ($val["status"]) ? $OK : "&nbsp;";
                //				$this->tpl->setCurrentBlock("status_row");
                //				$this->tpl->setVariable("TXT_STEP", $this->lng->txt("step_".$key));
                //				$this->tpl->setVariable("TXT_STATUS",$status);


                //				$this->tpl->setVariable("TXT_COMMENT",$val["comment"]);
                //				$this->tpl->parseCurrentBlock();

                $checklist->addEntry(
                    $this->lng->txt("step_" . $key),
                    "",
                    ($val["status"]) ?
                        ilChecklistGUI::STATUS_OK : ilChecklistGUI::STATUS_NO_STATUS,
                    false,
                    $val["comment"]
                );
            }
        }
        $this->tpl->setVariable("STATUS_PANEL", $checklist->getHTML());
    }

    /**
     * determine display mode and load according html layout
     *
     * @param    string  set title for display mode 'setup'
     */
    public function checkDisplayMode($a_title = "")
    {
        switch ($this->display_mode) {
            case "view":
                $this->tpl->addBlockFile("CONTENT", "content", "tpl.clientview.html", "setup");
                // display tabs
                include "./setup/include/inc.client_tabs.php";
                $client_name = ($this->setup->getClient()->getName()) ? $this->setup->getClient()->getName() : $this->lng->txt("no_client_name");
                $this->tpl->setVariable("TXT_HEADER", $client_name . " (" . $this->lng->txt("client_id") . ": " . $this->setup->getClient()->getId() . ")");
                break;

            case "setup":
                $this->tpl->addBlockFile("CONTENT", "content", "tpl.clientsetup.html", "setup");
                $this->tpl->setVariable("TXT_HEADER", $this->lng->txt($a_title));
                break;

            default:
                $this->displayError($this->lng->txt("unknown_display_mode"));
                exit();
                break;
        }
    }

    /**
     * Show subtabs
     *
     * @param
     * @return
     */
    public function displaySubTabs()
    {
        $sub_tab_html = $this->tabs->getSubTabHTML();
        if ($sub_tab_html != "") {
            $this->tpl->setVariable("SUBTABS", $sub_tab_html);
        }
    }


    /**
     * determine display mode and load correct panel
     */
    public function checkPanelMode()
    {
        switch ($this->display_mode) {
            case "view":
                $this->displayStatusPanel();
                break;

            case "setup":
                $this->displayProcessPanel();
                break;
        }
    }

    /**
     * display intro page for the first client installation
     */
    public function displayStartup()
    {
        $this->tpl->addBlockFile("CONTENT", "content", "tpl.clientsetup.html", "setup");

        $this->tpl->setVariable("TXT_INFO", $this->lng->txt("info_text_first_client"));
        $this->tpl->setVariable("TXT_HEADER", $this->lng->txt("setup_first_client"));

        $this->displayProcessPanel();

        $this->setButtonNext("ini");
    }

    ////
    //// DISPLAY DATABASE
    ////

    /**
     * display database form and process form input
     */
    public function displayDatabase()
    {
        global $ilErr,$ilDB,$ilLog;

        $this->checkDisplayMode("setup_database");

        // database is intalled
        if ($this->setup->getClient()->getDBSetup()->isDatabaseInstalled()) {
            $this->setDbSubTabs("db");

            $ilDB = $this->setup->getClient()->getDB();
            $this->lng->setDbHandler($ilDB);
            $dbupdate = new ilDBUpdate($ilDB);
            $db_status = $dbupdate->getDBVersionStatus();
            $hotfix_available = $dbupdate->hotfixAvailable();
            $custom_updates_available = $dbupdate->customUpdatesAvailable();
            $this->initClientDbForm(false, $dbupdate, $db_status, $hotfix_available, $custom_updates_available);
            $this->getClientDbFormValues($dbupdate);
            $this->tpl->setVariable("SETUP_CONTENT", $this->form->getHTML());

            if ($db_status) {
                $this->setButtonNext("lang");
            }
        } else {	// database is not installed
            $this->tpl->setVariable("TXT_INFO", $this->lng->txt("info_text_db") . "<br />" .
                "<p><code>CREATE DATABASE &lt;your_db&gt; CHARACTER SET utf8 COLLATE &lt;your_collation&gt;</code></p>" .
                "<p><b>" . $this->lng->txt("info_text_db2") . "</b></p><br/>");

            $this->initClientDbForm();
            $this->getClientDbFormValues();
            $this->tpl->setVariable("SETUP_CONTENT", $this->form->getHTML());
            $this->setButtonPrev("ini");
        }

        $this->checkPanelMode();

        $this->displaySubTabs();
    }



    protected function bt_tabs($edit = false)
    {
        $tabs = new ilTemplate("tpl.tabs.html", true, true, "Services/UIComponent/Tabs");

        $tabs->setCurrentBlock("tab");
        $tabs->setVariable("TAB_TYPE", !$edit? "active" : "");
        $tabs->setVariable("TAB_TEXT", $this->lng->txt('overview'));
        $tabs->setVariable("TAB_LINK", "setup.php?cmd=background_tasks");
        $tabs->parseCurrentBlock();

        $tabs->setCurrentBlock("tab");
        $tabs->setVariable("TAB_TYPE", $edit? "active" : "");
        $tabs->setVariable("TAB_TEXT", $this->lng->txt('settings'));
        $tabs->setVariable("TAB_LINK", "setup.php?cmd=edit_background_tasks");
        $tabs->parseCurrentBlock();

        return $tabs;
    }

    protected function displayCache()
    {
        require_once('Services/Form/classes/class.ilPropertyFormGUI.php');
        require_once('Services/GlobalCache/classes/class.ilGlobalCache.php');
        require_once('./Services/GlobalCache/classes/Settings/class.ilGlobalCacheSettings.php');
        $this->checkDisplayMode('setup_cache');
        /**
         * @var $ini ilIniFile
         */

        $ilGlobalCacheSettings = new ilGlobalCacheSettings();
        $ilGlobalCacheSettings->readFromIniFile($this->setup->getClient()->ini);

        $cache_form = new ilPropertyFormGUI();
        $cache_form->setTitle($this->lng->txt('global_cache_configuration'));
        $cache_form->addCommandButton('saveCache', $this->lng->txt('save'));
        $cache_form->setFormAction('setup.php?cmd=gateway');

        $some_inactive = false;
        $message = '';
        $service_type = new ilRadioGroupInputGUI($this->lng->txt('global_cache_service_type'), 'global_cache_service_type');

        $option = new ilRadioOption($this->lng->txt('none'), -1);
        $service_type->addOption($option);

        foreach (ilGlobalCache::getAllTypes() as $type) {
            $option = new ilRadioOption($this->lng->txt('global_cache_service_type_' . $type->getServiceType()), $type->getServiceType());
            $option->setInfo($this->lng->txt('global_cache_install_info_' . $type->getServiceType()));
            if (!$type->isCacheServiceInstallable()) {
                $option->setDisabled(true);
                $message .= $this->lng->txt('global_cache_service_type_' . $type->getServiceType()) . ': ' . $type->getInstallationFailureReason()
                    . '; ';
                $some_inactive = true;
            }
            $service_type->addOption($option);
        }

        if ($some_inactive) {
            $service_type->setAlert($message);
            ilUtil::sendInfo($this->lng->txt('global_cache_supported_services'));
        }

        $service_type->setValue($ilGlobalCacheSettings->isActive() ? $ilGlobalCacheSettings->getService() : -1);
        $cache_form->addItem($service_type);
        if ($ilGlobalCacheSettings->isActive()) {
            $h = new ilFormSectionHeaderGUI();
            $h->setTitle($this->lng->txt('cache_activated_components'));
            $cache_form->addItem($h);

            foreach (ilGlobalCache::getAvailableComponents() as $comp) {
                $cc = new ilCheckboxInputGUI($this->lng->txt('cache_activate_' . $comp), 'activate[' . $comp . ']');
                $cc->setChecked($ilGlobalCacheSettings->isComponentActivated($comp));
                $cache_form->addItem($cc);
            }

            $cc = new ilCheckboxInputGUI($this->lng->txt('cache_activate_all'), 'activate[all]');
            $cc->setChecked($ilGlobalCacheSettings->areAllComponentActivated());
            $cache_form->addItem($cc);
        }

        $table_html = '';
        require_once('./Services/UIComponent/Toolbar/classes/class.ilToolbarGUI.php');
        require_once('./Services/UIComponent/Button/classes/class.ilLinkButton.php');
        $ilToolbarGUI = new ilToolbarGUI();
        if ($ilGlobalCacheSettings->isActive()) {
            $b = ilLinkButton::getInstance();
            $b->setCaption('cache_flush');
            $b->setUrl('setup.php?cmd=flushCache');
            $ilToolbarGUI->addButtonInstance($b);
        }

        if ($ilGlobalCacheSettings->getService() == ilGlobalCache::TYPE_MEMCACHED) {
            require_once('./Services/GlobalCache/classes/Memcache/class.ilMemcacheServerTableGUI.php');
            $b = ilLinkButton::getInstance();
            $b->setCaption('memcache_add');
            $b->setUrl('setup.php?cmd=addMemcacheServer');
            $ilToolbarGUI->addButtonInstance($b);
            $ilMemcacheServerTableGUI = new ilMemcacheServerTableGUI(null);
            $table_html = $ilMemcacheServerTableGUI->getHTML();
        }

        $this->tpl->setVariable('SETUP_CONTENT', $ilToolbarGUI->getHTML() . $cache_form->getHTML() . $table_html);
    }


    protected function flushCache()
    {
        require_once('Services/GlobalCache/classes/class.ilGlobalCache.php');
        ilGlobalCache::flushAll();
        ilUtil::redirect('setup.php?cmd=cache');
    }


    protected function addMemcacheServer()
    {
        require_once('./Services/GlobalCache/classes/Memcache/class.ilMemcacheServerFormGUI.php');
        $this->checkDisplayMode('setup_cache');
        $ilMemcacheServerFormGUI = new ilMemcacheServerFormGUI(new ilMemcacheServer());
        $this->tpl->setVariable('SETUP_CONTENT', $ilMemcacheServerFormGUI->getHTML());
    }


    protected function createMemcacheServer()
    {
        require_once('./Services/GlobalCache/classes/Memcache/class.ilMemcacheServerFormGUI.php');
        $this->checkDisplayMode('setup_cache');
        $ilMemcacheServerFormGUI = new ilMemcacheServerFormGUI(new ilMemcacheServer());
        $ilMemcacheServerFormGUI->setValuesByPost();
        if ($ilMemcacheServerFormGUI->saveObject()) {
            ilUtil::redirect('setup.php?cmd=cache');
        }
        $this->tpl->setVariable('SETUP_CONTENT', $ilMemcacheServerFormGUI->getHTML());
    }


    protected function editMemcacheServer()
    {
        require_once('./Services/GlobalCache/classes/Memcache/class.ilMemcacheServerFormGUI.php');
        $this->checkDisplayMode('setup_cache');
        $ilMemcacheServerFormGUI = new ilMemcacheServerFormGUI(ilMemcacheServer::find($_GET['mcsid']));
        $ilMemcacheServerFormGUI->fillForm();
        $this->tpl->setVariable('SETUP_CONTENT', $ilMemcacheServerFormGUI->getHTML());
    }


    protected function updateMemcacheServer()
    {
        require_once('./Services/GlobalCache/classes/Memcache/class.ilMemcacheServerFormGUI.php');
        $this->checkDisplayMode('setup_cache');

        $ilMemcacheServerFormGUI = new ilMemcacheServerFormGUI(ilMemcacheServer::find($_GET['mcsid']));
        $ilMemcacheServerFormGUI->setValuesByPost();
        if ($ilMemcacheServerFormGUI->saveObject()) {
            ilUtil::redirect('setup.php?cmd=cache');
        }
        $this->tpl->setVariable('SETUP_CONTENT', $ilMemcacheServerFormGUI->getHTML());
    }


    protected function deleteMemcacheServer()
    {
        require_once('./Services/GlobalCache/classes/Memcache/class.ilMemcacheServer.php');
        $ilMemcacheServer = ilMemcacheServer::find($_GET['mcsid']);
        $ilMemcacheServer->delete();
        ilUtil::redirect('setup.php?cmd=cache');
    }


    public function saveCache()
    {
        /**
         * @var $ini ilIniFile
         */
        require_once('Services/GlobalCache/classes/class.ilGlobalCache.php');
        require_once('./Services/GlobalCache/classes/Settings/class.ilGlobalCacheSettings.php');
        ilGlobalCache::flushAll();
        $ini = $this->setup->getClient()->ini;

        $ilGlobalCacheSettings = new ilGlobalCacheSettings();
        $ilGlobalCacheSettings->readFromIniFile($ini);
        $service_type = $_POST['global_cache_service_type'];
        $ilGlobalCacheSettings->setActive(($service_type >= 0) ? true : false);
        $ilGlobalCacheSettings->setService($service_type);
        $ilGlobalCacheSettings->resetActivatedComponents();
        if (is_array($_POST['activate']) && count($_POST['activate']) > 0) {
            foreach ($_POST['activate'] as $comp => $a) {
                if ($comp == 'all') {
                    $ilGlobalCacheSettings->activateAll();
                    break;
                }
                $ilGlobalCacheSettings->addActivatedComponent($comp);
            }
        }

        $ilGlobalCacheSettings->writeToIniFile($ini);

        ilUtil::sendSuccess($this->lng->txt('saved_successfully'), true);
        ilUtil::redirect('setup.php?cmd=cache');
    }


    /**
     * Display database slave
     */
    public function displayDatabaseSlave($a_from_save = false)
    {
        global $ilErr,$ilDB,$ilLog;

        $this->checkDisplayMode("setup_database");

        //$this->tpl->addBlockFile("SETUP_CONTENT","setup_content","tpl.clientsetup_db.html", "setup");

        // database is intalled
        if (!$this->setup->getClient()->db_installed) {
            return;
        }

        $this->setDbSubTabs("repl");

        if (!$a_from_save) {
            $ilDB = $this->setup->getClient()->db;
            $this->lng->setDbHandler($ilDB);
        }

        ilUtil::sendInfo($this->lng->txt("mysql_replication_info_alpha"));

        if (!$a_from_save) {
            $this->initDbSlaveForm();
        }

        $this->tpl->setVariable("SETUP_CONTENT", $this->form->getHTML());

        $this->checkPanelMode();

        $this->displaySubTabs();
    }

    /**
     * Init db slave form
     */
    public function initDbSlaveForm()
    {
        global $lng, $ilCtrl, $ilDB;

        $client = $this->setup->getClient();

        include_once("Services/Form/classes/class.ilPropertyFormGUI.php");
        $this->form = new ilPropertyFormGUI();

        // db type
        $ne = new ilNonEditableValueGUI($lng->txt("db_type"), "slave_type");
        $ne->setValue($lng->txt("db_" . $ilDB->getDbType()));
        $this->form->addItem($ne);

        // activate slave
        $act = new ilCheckboxInputGUI($this->lng->txt("db_active"), "slave_active");
        $act->setChecked($client->getDbSlaveActive());
        $this->form->addItem($act);

        // slave host
        $ti = new ilTextInputGUI($lng->txt("db_host"), "slave_host");
        $ti->setValue($client->getDbSlaveHost());
        $ti->setMaxLength(120);
        $ti->setRequired(true);
        $act->addSubItem($ti);

        // slave name
        $ti = new ilTextInputGUI($lng->txt("db_name"), "slave_name");
        $ti->setValue($client->getDbSlaveName());
        $ti->setRequired(true);
        $ti->setMaxLength(40);
        $act->addSubItem($ti);

        // slave user
        $ti = new ilTextInputGUI($lng->txt("db_user"), "slave_user");
        $ti->setValue($client->getDbSlaveUser());
        $ti->setMaxLength(40);
        $ti->setRequired(true);
        $act->addSubItem($ti);

        // slave port
        $ti = new ilTextInputGUI($lng->txt("db_port"), "slave_port");
        $ti->setValue($client->getDbSlavePort());
        $ti->setMaxLength(8);
        $act->addSubItem($ti);

        // set password
        $set_pw = new ilCheckboxInputGUI($this->lng->txt("db_set_password"), "set_slave_password");
        $act->addSubItem($set_pw);

        // slave password
        $ti = new ilTextInputGUI($lng->txt("db_pass"), "slave_pass");
        $ti->setMaxLength(40);
        $set_pw->addSubItem($ti);

        $this->form->addCommandButton("saveDbSlave", $lng->txt("save"));

        $this->form->setTitle($lng->txt("db_slave_settings"));
        $this->form->setFormAction("setup.php?cmd=gateway");
    }

    /**
     * Save db slave form
     */
    public function saveDbSlave()
    {
        global $tpl, $lng, $ilCtrl, $ilDB;

        $client = $this->setup->getClient();

        $ilDB = $this->setup->getClient()->db;
        $this->lng->setDbHandler($ilDB);

        $this->initDbSlaveForm();
        if ($this->form->checkInput()) {
            $client->setDbSlaveActive($this->form->getInput("slave_active"));
            if ($this->form->getInput("slave_active")) {
                $client->setDbSlaveHost($this->form->getInput("slave_host"));
                $client->setDbSlaveUser($this->form->getInput("slave_user"));
                $client->setDbSlavePort($this->form->getInput("slave_port"));
                $client->setDbSlaveName($this->form->getInput("slave_name"));
                if ($this->form->getInput("set_slave_password")) {
                    $client->setDbSlavePass($this->form->getInput("slave_pass"));
                }
            }
            $client->writeIni();

            ilUtil::sendSuccess($lng->txt("saved_successfully"), true);
            ilUtil::redirect("setup.php?cmd=dbslave");
        } else {
            $this->form->setValuesByPost();
            $this->displayDatabaseSlave(true);
        }
    }


    /**
     * Set db subtabs
     *
     * @param
     * @return
     */
    public function setDbSubtabs($a_subtab_id = "db")
    {
        global $ilDB;

        if ($ilDB->getDbType() == "mysql") {
            $this->tabs->addSubTab("db", $this->lng->txt("db_master"), "setup.php?client_id=" . $this->client_id . "&cmd=db");
            $this->tabs->addSubTab("repl", $this->lng->txt("db_slave"), "setup.php?client_id=" . $this->client_id . "&cmd=dbslave");
        }

        $this->tabs->activateSubTab($a_subtab_id);
    }



    /**
    * Init client db form.
    */
    public function initClientDbForm($a_install = true, $dbupdate = null, $db_status = false, $hotfix_available = false, $custom_updates_available = false)
    {
        global $lng, $ilCtrl;

        include_once("Services/Form/classes/class.ilPropertyFormGUI.php");
        $this->form = new ilPropertyFormGUI();

        // type
        $ne = new ilNonEditableValueGUI($lng->txt("db_type"), "db_type");
        $this->form->addItem($ne);

        // version
        if ($this->setup->getClient()->getDBSetup()->isDatabaseInstalled()
            && in_array($this->setup->getClient()->getDbType(), array(
                ilDBConstants::TYPE_MYSQL,
                ilDBConstants::TYPE_INNODB,
            ))
        ) {
            $ne = new ilNonEditableValueGUI($lng->txt("version"), "db_version");
            $ilDB = $this->setup->getClient()->db;
            $ne->setValue($ilDB->getDBVersion());
            $this->form->addItem($ne);
        }

        // host
        $ne = new ilNonEditableValueGUI($lng->txt("host"), "db_host");
        $this->form->addItem($ne);

        // name
        $ne = new ilNonEditableValueGUI($lng->txt("name"), "db_name");
        $this->form->addItem($ne);

        // user
        $ne = new ilNonEditableValueGUI($lng->txt("user"), "db_user");
        $this->form->addItem($ne);

        // port
        $ne = new ilNonEditableValueGUI($lng->txt("port"), "db_port");
        $this->form->addItem($ne);

        // creation / collation for mysql
        if ((in_array($this->setup->getClient()->getDBType(), ilDBConstants::getInstallableTypes()) && $a_install)) {
            // create database
            $cb = new ilCheckboxInputGUI($lng->txt("database_create"), "chk_db_create");

            // collation
            $collations = array(
                    "utf8_unicode_ci",
                    "utf8_general_ci",
                    "utf8_czech_ci",
                    "utf8_danish_ci",
                    "utf8_estonian_ci",
                    "utf8_icelandic_ci",
                    "utf8_latvian_ci",
                    "utf8_lithuanian_ci",
                    "utf8_persian_ci",
                    "utf8_polish_ci",
                    "utf8_roman_ci",
                    "utf8_romanian_ci",
                    "utf8_slovak_ci",
                    "utf8_slovenian_ci",
                    "utf8_spanish2_ci",
                    "utf8_spanish_ci",
                    "utf8_swedish_ci",
                    "utf8_turkish_ci"
                );
            foreach ($collations as $collation) {
                $options[$collation] = $collation;
            }
            $si = new ilSelectInputGUI($lng->txt("collation"), "collation");
            $si->setOptions($options);
            $si->setInfo($this->lng->txt("info_text_db_collation2") . " " .
                    "<a target=\"_new\" href=\"http://dev.mysql.com/doc/mysql/en/charset-unicode-sets.html\">" .
                    " MySQL Reference Manual :: 10.11.1 Unicode Character Sets</a>");
            $cb->addSubItem($si);

            $this->form->addItem($cb);
        }

        if ($a_install) {
            $this->form->addCommandButton("installDatabase", $lng->txt("database_install"));
        } else {
            $ilDB = $this->setup->getClient()->db;
            $this->lng->setDbHandler($ilDB);
            $dbupdate = new ilDBUpdate($ilDB);

            // database version
            $ne = new ilNonEditableValueGUI($lng->txt("database_version"), "curv");
            $ne->setValue($dbupdate->currentVersion);
            $this->form->addItem($ne);

            // file version
            $ne = new ilNonEditableValueGUI($lng->txt("file_version"), "filev");
            $ne->setValue($dbupdate->fileVersion);
            $this->form->addItem($ne);

            if (!$db_status = $dbupdate->getDBVersionStatus()) {
                // next update step
                $options = array();
                for ($i = $dbupdate->currentVersion + 1; $i <= $dbupdate->fileVersion; $i++) {
                    $options[$i] = $i;
                }
                if (count($options) > 1) {
                    $si = new ilSelectInputGUI($lng->txt("next_update_break"), "update_break");
                    $si->setOptions($options);
                    $si->setInfo($lng->txt("next_update_break_info"));
                    $this->form->addItem($si);
                }

                if ($dbupdate->getRunningStatus() > 0) {
                    ilUtil::sendFailure($this->lng->txt("db_update_interrupted") . " (Step " . $dbupdate->getRunningStatus() . ") <br /><br />" .
                        $this->lng->txt("db_update_interrupted_avoid"));
                } else {
                    ilUtil::sendInfo($this->lng->txt("database_needs_update"));
                }
                $this->form->addCommandButton("updateDatabase", $lng->txt("database_update"));
                $this->form->addCommandButton("showUpdateSteps", $lng->txt("show_update_steps"));
            } elseif ($hotfix_available) {
                // hotfix current version
                $ne = new ilNonEditableValueGUI($lng->txt("applied_hotfixes"), "curhf");
                $ne->setValue($dbupdate->getHotfixCurrentVersion());
                $this->form->addItem($ne);

                // hotfix file version
                $ne = new ilNonEditableValueGUI($lng->txt("available_hotfixes"), "filehf");
                $ne->setValue($dbupdate->getHotfixFileVersion());
                $this->form->addItem($ne);

                $this->form->addCommandButton("applyHotfix", $lng->txt("apply_hotfixes"));
                $this->form->addCommandButton("showHotfixSteps", $lng->txt("show_update_steps"));
                ilUtil::sendInfo($this->lng->txt("database_needs_update"));
            } elseif ($custom_updates_available) {
                // custom updates current version
                $ne = new ilNonEditableValueGUI($lng->txt("applied_custom_updates"), "curcu");
                $ne->setValue($dbupdate->getCustomUpdatesCurrentVersion());
                $this->form->addItem($ne);

                // custom updates file version
                $ne = new ilNonEditableValueGUI($lng->txt("available_custom_updates"), "filecu");
                $ne->setValue($dbupdate->getCustomUpdatesFileVersion());
                $this->form->addItem($ne);

                $this->form->addCommandButton("applyCustomUpdates", $lng->txt("apply_custom_updates"));
                ilUtil::sendInfo($this->lng->txt("database_needs_update"));
            } else {
                if ($dbupdate->getHotfixFileVersion() > 0) {
                    // hotfix current version
                    $ne = new ilNonEditableValueGUI($lng->txt("applied_hotfixes"), "curhf");
                    $ne->setValue($dbupdate->getHotfixCurrentVersion());
                    $this->form->addItem($ne);

                    // hotfix file version
                    $ne = new ilNonEditableValueGUI($lng->txt("available_hotfixes"), "filehf");
                    $ne->setValue($dbupdate->getHotfixFileVersion());
                    $this->form->addItem($ne);
                }
                if ($dbupdate->getCustomUpdatesFileVersion() > 0) {
                    // custom updates current version
                    $ne = new ilNonEditableValueGUI($lng->txt("applied_custom_updates"), "curcu");
                    $ne->setValue($dbupdate->getCustomUpdatesCurrentVersion());
                    $this->form->addItem($ne);

                    // custom updates file version
                    $ne = new ilNonEditableValueGUI($lng->txt("available_custom_updates"), "filecu");
                    $ne->setValue($dbupdate->getCustomUpdatesFileVersion());
                    $this->form->addItem($ne);
                }
                ilUtil::sendSuccess($this->lng->txt("database_is_uptodate"));
            }
        }

        $this->form->setTitle($lng->txt("database"));
        $this->form->setFormAction("setup.php?cmd=gateway");
    }

    /**
    * Get current values for client db from
    *
    */
    public function getClientDbFormValues($dbupdate = null)
    {
        $values = array();
        $values["db_host"] = $this->setup->getClient()->getDbHost();
        $values["db_name"] = $this->setup->getClient()->getDbName();
        $values["db_user"] = $this->setup->getClient()->getDbUser();
        $values["db_port"] = $this->setup->getClient()->getDbPort();
        $values["db_type"] = ilDBConstants::describe($this->setup->getClient()->getDbType());
        if (is_object($dbupdate)) {
            $values["update_break"] = $dbupdate->fileVersion;
            if (($dbupdate->fileVersion - $dbupdate->currentVersion) >= 200) {
                $values["update_break"] = $dbupdate->currentVersion + 200 -
                    ($dbupdate->currentVersion % 100);
            }
        }

        $this->form->setValuesByArray($values);
    }

    ////
    //// INSTALL DATABASE
    ////

    /**
     * Install the database
     *
     * @param
     * @return
     */
    public function installDatabase()
    {
        if (!$this->setup->getClient()->getDBSetup()->isDatabaseExisting()) {
            if ($_POST["chk_db_create"]) {
                if (!$this->setup->createDatabase($_POST["collation"])) {
                    echo "installation failed";
                    ilUtil::sendFailure($this->lng->txt($this->setup->getError()), true);
                    ilUtil::redirect("setup.php?cmd=displayDatabase");
                }
            } else {
                ilUtil::sendFailure($this->lng->txt("database_not_exists_create_first"), true);
                ilUtil::redirect("setup.php?cmd=displayDatabase");
            }
        }
        if (!$this->setup->installDatabase()) {
            ilUtil::sendFailure($this->lng->txt($this->setup->getError()), true);
        } else {
            ilUtil::sendSuccess($this->lng->txt("database_installed"), true);
        }
        ilUtil::redirect("setup.php?cmd=displayDatabase");
    }

    ////
    //// UPDATE DATABASE
    ////

    /**
     * Update database
     */
    public function updateDatabase()
    {
        global $ilCtrlStructureReader;

        include_once "./Services/AccessControl/classes/class.ilRbacAdmin.php";
        include_once "./Services/AccessControl/classes/class.ilRbacReview.php";
        include_once "./Services/AccessControl/classes/class.ilRbacSystem.php";
        include_once "./Services/Tree/classes/class.ilTree.php";
        include_once "./Services/Xml/classes/class.ilSaxParser.php";
        include_once "./Services/Object/classes/class.ilObjectDefinition.php";


        // referencing db handler in language class
        $ilDB = $this->setup->getClient()->db;
        $this->lng->setDbHandler($ilDB);

        // run dbupdate
        $dbupdate = new ilDBUpdate($ilDB, $this->setup->getClient()->ini);
        $dbupdate->applyUpdate((int) $_POST["update_break"]);

        if ($dbupdate->updateMsg == "no_changes") {
            $message = $this->lng->txt("no_changes") . ". " . $this->lng->txt("database_is_uptodate");
        } else {
            $sep = "";
            foreach ($dbupdate->updateMsg as $row) {
                if ($row["msg"] == "update_applied") {
                    $a_message .= $sep . $row["nr"];
                    $sep = ", ";
                } else {
                    $e_message .= "<br/>" . $this->lng->txt($row["msg"]) . ": " . $row["nr"];
                }
            }
            if ($a_message != "") {
                $a_message = $this->lng->txt("update_applied") . ": " . $a_message;
            }
        }

        ilUtil::sendInfo($a_message . $e_message, true);
        ilUtil::redirect("setup.php?cmd=displayDatabase");
    }

    ////
    //// UPDATE DATABASE
    ////

    /**
     * Show hotfix steps
     *
     * @param
     * @return
     */
    public function showHotfixSteps()
    {
        $this->showUpdateSteps(true);
    }


    /**
     * Update database
     */
    public function showUpdateSteps($a_hotfix = false)
    {
        global $ilCtrlStructureReader;

        $this->checkDisplayMode("setup_database");

        //$this->tpl->addBlockFile("SETUP_CONTENT","setup_content","tpl.clientsetup_db.html", "setup");

        // database is intalled
        if ($this->setup->getClient()->db_installed) {
            $ilDB = $this->setup->getClient()->db;
            $this->lng->setDbHandler($ilDB);
            $dbupdate = new ilDBUpdate($ilDB);
            $db_status = $dbupdate->getDBVersionStatus();
            $hotfix_available = $dbupdate->hotfixAvailable();
            $custom_updates_available = $dbupdate->customUpdatesAvailable();
            //			$this->initClientDbForm(false, $dbupdate, $db_status, $hotfix_available, $custom_updates_available);
            //			$this->getClientDbFormValues($dbupdate);

            $ntpl = new ilTemplate("tpl.setup_steps.html", true, true, "setup");
            if ($a_hotfix) {
                $ntpl->setVariable("CONTENT", $dbupdate->getHotfixSteps());
            } else {
                $ntpl->setVariable("CONTENT", $dbupdate->getUpdateSteps($_POST["update_break"]));
            }
            $ntpl->setVariable("BACK", $this->lng->txt("back"));
            $ntpl->setVariable("HREF_BACK", "./setup.php?client_id=&cmd=db");
            $this->tpl->setVariable("SETUP_CONTENT", $ntpl->get());
        }
    }


    ////
    //// Apply hotfixes
    ////

    /**
     * Apply hotfixes
     */
    public function applyHotfix()
    {
        global $ilCtrlStructureReader;

        $ilCtrlStructureReader->setIniFile($this->setup->getClient()->ini);

        include_once "./Services/AccessControl/classes/class.ilRbacAdmin.php";
        include_once "./Services/AccessControl/classes/class.ilRbacReview.php";
        include_once "./Services/AccessControl/classes/class.ilRbacSystem.php";
        include_once "./Services/Tree/classes/class.ilTree.php";
        include_once "./Services/Xml/classes/class.ilSaxParser.php";
        include_once "./Services/Object/classes/class.ilObjectDefinition.php";

        // referencing db handler in language class
        $ilDB = $this->setup->getClient()->db;
        $this->lng->setDbHandler($ilDB);

        // run dbupdate
        $dbupdate = new ilDBUpdate($ilDB, $this->setup->getClient()->ini);
        $dbupdate->applyHotfix();

        if ($dbupdate->updateMsg == "no_changes") {
            $message = $this->lng->txt("no_changes") . ". " . $this->lng->txt("database_is_uptodate");
        } else {
            $sep = "";
            foreach ($dbupdate->updateMsg as $row) {
                if ($row["msg"] == "update_applied") {
                    $a_message .= $sep . $row["nr"];
                    $sep = ", ";
                } else {
                    $e_message .= "<br/>" . $this->lng->txt($row["msg"]) . ": " . $row["nr"];
                }
            }
            if ($a_message != "") {
                $a_message = $this->lng->txt("update_applied") . ": " . $a_message;
            }
        }

        ilUtil::sendInfo($a_message . $e_message, true);
        ilUtil::redirect("setup.php?cmd=displayDatabase");
    }

    ////
    //// SESSION
    ////

    /**
     * display sessions form and process form input
     */
    public function displaySessions()
    {
        require_once('Services/Authentication/classes/class.ilSessionControl.php');

        $this->checkDisplayMode("setup_sessions");

        if (!$this->setup->getClient()->db_installed) {
            // program should never come to this place
            $message = "No database found! Please install database first.";
            ilUtil::sendInfo($message);
        }

        $setting_fields = ilSessionControl::getSettingFields();

        $valid = true;
        $settings = array();

        foreach ($setting_fields as $field) {
            if ($field == 'session_allow_client_maintenance') {
                if (isset($_POST[$field])) {
                    $_POST[$field] = '1';
                } else {
                    $_POST[$field] = '0';
                }
            }

            if (isset($_POST[$field]) && $_POST[$field] != '') {
                $settings[$field] = $_POST[$field];
            } else {
                $valid = false;
                break;
            }
        }

        if ($valid) {
            $this->setup->setSessionSettings($settings);
        }

        $settings = $this->setup->getSessionSettings();

        include_once("Services/Form/classes/class.ilPropertyFormGUI.php");
        $form = new ilPropertyFormGUI();

        include_once 'Services/Authentication/classes/class.ilSession.php';

        // BEGIN SESSION SETTINGS
        // create session handling radio group
        $ssettings = new ilRadioGroupInputGUI($this->lng->txt('sess_mode'), 'session_handling_type');
        $ssettings->setValue($settings['session_handling_type'], ilSession::SESSION_HANDLING_FIXED);

        // first option, fixed session duration
        $fixed = new ilRadioOption($this->lng->txt('sess_fixed_duration'), ilSession::SESSION_HANDLING_FIXED);

        // add session handling to radio group
        $ssettings->addOption($fixed);

        // second option, session control
        $ldsh = new ilRadioOption($this->lng->txt('sess_load_dependent_session_handling'), ilSession::SESSION_HANDLING_LOAD_DEPENDENT);

        // this is the max count of active sessions
        // that are getting started simlutanously
        $ti = new ilTextInputGUI($this->lng->txt('sess_max_session_count'), "session_max_count");
        $ti->setInfo($this->lng->txt('sess_max_session_count_info'));
        $ti->setMaxLength(5);
        $ti->setSize(5);
        $ti->setValue($settings['session_max_count']);
        $ldsh->addSubItem($ti);

        // after this (min) idle time the session can be deleted,
        // if there are further requests for new sessions,
        // but max session count is reached yet
        $ti = new ilTextInputGUI($this->lng->txt('sess_min_session_idle'), "session_min_idle");
        $ti->setInfo($this->lng->txt('sess_min_session_idle_info'));
        $ti->setMaxLength(5);
        $ti->setSize(5);
        $ti->setValue($settings['session_min_idle']);
        $ldsh->addSubItem($ti);

        // after this (max) idle timeout the session expires
        // and become invalid, so it is not considered anymore
        // when calculating current count of active sessions
        $ti = new ilTextInputGUI($this->lng->txt('sess_max_session_idle'), "session_max_idle");
        $ti->setInfo($this->lng->txt('sess_max_session_idle_info'));
        $ti->setMaxLength(5);
        $ti->setSize(5);
        $ti->setValue($settings['session_max_idle']);
        $ldsh->addSubItem($ti);

        // this is the max duration that can elapse between the first and the secnd
        // request to the system before the session is immidietly deleted
        $ti = new ilTextInputGUI($this->lng->txt('sess_max_session_idle_after_first_request'), "session_max_idle_after_first_request");
        $ti->setInfo($this->lng->txt('sess_max_session_idle_after_first_request_info'));
        $ti->setMaxLength(5);
        $ti->setSize(5);
        $ti->setValue($settings['session_max_idle_after_first_request']);
        $ldsh->addSubItem($ti);

        // add session control to radio group
        $ssettings->addOption($ldsh);

        $form->addItem($ssettings);

        // controls the ability t maintenance the following
        // settings in client administration
        $chkb = new ilCheckboxInputGUI($this->lng->txt('sess_allow_client_maintenance'), "session_allow_client_maintenance");
        $chkb->setInfo($this->lng->txt('sess_allow_client_maintenance_info'));
        $chkb->setChecked($settings['session_allow_client_maintenance'] ? true : false);
        $form->addItem($chkb);
        // END SESSION SETTINGS

        // save and cancel commands
        $form->addCommandButton("sess", $this->lng->txt('save'));

        $form->setTitle($this->lng->txt("sess_sessions"));
        $form->setFormAction('setup.php?client_id=' . $this->client_id . '&cmd=sess');

        $this->tpl->setVariable("TXT_SETUP_TITLE", ucfirst(trim($this->lng->txt('sess_sessions'))));
        $this->tpl->setVariable("TXT_INFO", '');
        $this->tpl->setVariable("SETUP_CONTENT", $form->getHTML());

        /*$this->setButtonPrev("db");

        if($this->setup->checkClientSessionSettings($this->client,true))
        {
            $this->setButtonNext("lang");
        }*/

        $this->checkPanelMode();
    }

    ////
    //// LANGUAGES
    ////

    /**
     * display language form and process form input
     */
    public function displayLanguages()
    {
        $this->checkDisplayMode("setup_languages");

        if (!$this->setup->getClient()->db_installed) {
            // program should never come to this place
            $message = "No database found! Please install database first.";
            ilUtil::sendFailure($message);
        }

        include_once("./setup/classes/class.ilSetupLanguageTableGUI.php");
        $tab = new ilSetupLanguageTableGUI($this->setup->getClient());
        $this->tpl->setVariable("SETUP_CONTENT", $tab->getHTML());

        $this->tpl->setVariable("TXT_SETUP_TITLE", ucfirst(trim($this->lng->txt("setup_languages"))));
        $this->tpl->setVariable("TXT_INFO", $this->lng->txt("info_text_lang"));

        $installed_langs = $this->lng->getInstalledLanguages();
        $lang_count = count($installed_langs);
        if ($lang_count > 0) {
            $this->setup->getClient()->status["lang"]["status"] = true;
            $this->setup->getClient()->status["lang"]["comment"] = $lang_count . " " . $this->lng->txt("languages_installed");
        } else {
            $this->setup->getClient()->status["lang"]["status"] = false;
            $this->setup->getClient()->status["lang"]["comment"] = $this->lng->txt("lang_none_installed");
        }

        $this->setButtonPrev("lang");

        if ($lang_count > 0) {
            $this->setButtonNext("contact");
        }

        $this->checkPanelMode();
    }

    /**
     * Save languages
     *
     * @param
     * @return
     */
    public function saveLanguages()
    {
        if (empty($_POST["form"]["lang_id"])) {
            ilUtil::sendFailure($this->lng->txt("lang_min_one_language"), true);
            ilUtil::redirect("setup.php?cmd=lang");
        }

        if (!in_array($_POST["form"]["lang_default"], $_POST["form"]["lang_id"])) {
            ilUtil::sendFailure($this->lng->txt("lang_not_installed_default"), true);
            ilUtil::redirect("setup.php?cmd=lang");
        }

        $result = $this->lng->installLanguages($_POST["form"]["lang_id"], $_POST["form"]["lang_local"]);

        if (is_array($result)) {
            $count = count($result);
            $txt = "tet";

            foreach ($result as $key => $lang_key) {
                $list .= $this->lng->txt("lang_" . $lang_key);

                if ($count > $key + 1) {
                    $list .= ", ";
                }
            }
        }

        $this->setup->getClient()->setDefaultLanguage($_POST["form"]["lang_default"]);
        $message = $this->lng->txt("languages_installed");

        if ($result !== true) {
            $message .= "<br/>(" . $this->lng->txt("langs_not_valid_not_installed") . ": " . $list . ")";
        }
        ilUtil::sendInfo($message, true);
        ilUtil::redirect("setup.php?cmd=lang");
    }

    ////
    //// CONTACT DATA
    ////

    /**
     * display contact data form and process form input
     */
    public function displayContactData($a_omit_init = false)
    {
        $this->checkDisplayMode("setup_contact_data");
        $settings = $this->setup->getClient()->getAllSettings();

        if (!$a_omit_init) {
            $this->initContactDataForm();
            $this->getContactValues();
        }
        $this->tpl->setVariable("SETUP_CONTENT", $this->form->getHTML());
        $this->tpl->setVariable("TXT_INFO", $this->lng->txt("info_text_contact"));
        $this->setButtonPrev("lang");

        $check = $this->setup->checkClientContact($this->setup->client);

        $this->setup->getClient()->status["contact"]["status"] = $check["status"];
        $this->setup->getClient()->status["contact"]["comment"] = $check["comment"];

        if ($check["status"]) {
            $this->setButtonNext("proxy");
        }

        $this->checkPanelMode();
    }

    /**
     * Init contact data form.
     *
     * @param        int        $a_mode        Edit Mode
     */
    public function initContactDataForm()
    {
        global $lng, $ilCtrl;

        include_once("Services/Form/classes/class.ilPropertyFormGUI.php");
        $this->form = new ilPropertyFormGUI();

        // name
        $ti = new ilTextInputGUI($lng->txt("name"), "inst_name");
        $ti->setMaxLength(64);
        $ti->setSize(30);
        $ti->setRequired(true);
        $this->form->addItem($ti);

        // description
        $ti = new ilTextInputGUI($lng->txt("client_info"), "inst_info");
        $ti->setMaxLength(64);
        $ti->setSize(30);
        $this->form->addItem($ti);

        // institution
        $ti = new ilTextInputGUI($lng->txt("client_institution"), "inst_institution");
        $ti->setMaxLength(64);
        $ti->setSize(30);
        $this->form->addItem($ti);

        // contact data
        $sh = new ilFormSectionHeaderGUI();
        $sh->setTitle($lng->txt("contact_data"));
        $this->form->addItem($sh);

        // first name
        $ti = new ilTextInputGUI($lng->txt("firstname"), "admin_firstname");
        $ti->setMaxLength(64);
        $ti->setSize(30);
        $ti->setRequired(true);
        $this->form->addItem($ti);

        // last name
        $ti = new ilTextInputGUI($lng->txt("lastname"), "admin_lastname");
        $ti->setMaxLength(64);
        $ti->setSize(30);
        $ti->setRequired(true);
        $this->form->addItem($ti);

        $fs = array(
            "title" => array("max" => 64, "size" => 30),
            "position" => array("max" => 64, "size" => 30),
            "institution" => array("max" => 200, "size" => 30),
            "street" => array("max" => 64, "size" => 30),
            "zipcode" => array("max" => 10, "size" => 5),
            "city" => array("max" => 64, "size" => 30),
            "country" => array("max" => 64, "size" => 30),
            "phone" => array("max" => 64, "size" => 30)
            );
        foreach ($fs as $f => $op) {
            // field
            $ti = new ilTextInputGUI($lng->txt($f), "admin_" . $f);
            $ti->setMaxLength($op["max"]);
            $ti->setSize($op["size"]);
            $ti->setInfo($lng->txt(""));
            $this->form->addItem($ti);
        }

        // email
        $ti = new ilEmailInputGUI($lng->txt("email"), "admin_email");
        $ti->setRequired(true);
        $ti->allowRFC822(true);
        $this->form->addItem($ti);

        // feedback recipient
        /*$ti = new ilEmailInputGUI($lng->txt("feedback_recipient"), "feedback_recipient");
        $ti->setInfo($lng->txt("feedback_recipient_info"));
        $ti->setRequired(true);
        $ti->allowRFC822(true);
        $this->form->addItem($ti);*/

        // error recipient
        /*$ti = new ilEmailInputGUI($lng->txt("error_recipient"), "error_recipient");
        $ti->allowRFC822(true);
        $this->form->addItem($ti);*/

        $this->form->addCommandButton("saveContact", $lng->txt("save"));

        $this->form->setTitle($lng->txt("client_data"));
        $this->form->setFormAction("setup.php?cmd=gateway");
    }

    /**
     * Get current values for contact from
     */
    public function getContactValues()
    {
        $settings = $this->setup->getClient()->getAllSettings();

        $values = $settings;

        $values["inst_name"] = ($this->setup->getClient()->getName())
            ? $this->setup->getClient()->getName()
            : $this->setup->getClient()->getId();
        $values["inst_info"] = $this->setup->getClient()->getDescription();

        $this->form->setValuesByArray($values);
    }

    /**
     * Save contact form
     */
    public function saveContact()
    {
        global $tpl, $lng, $ilCtrl;

        $this->initContactDataForm();
        if ($this->form->checkInput()) {
            $this->setup->getClient()->setSetting("admin_firstname", $_POST["admin_firstname"]);
            $this->setup->getClient()->setSetting("admin_lastname", $_POST["admin_lastname"]);
            $this->setup->getClient()->setSetting("admin_title", $_POST["admin_title"]);
            $this->setup->getClient()->setSetting("admin_position", $_POST["admin_position"]);
            $this->setup->getClient()->setSetting("admin_institution", $_POST["admin_institution"]);
            $this->setup->getClient()->setSetting("admin_street", $_POST["admin_street"]);
            $this->setup->getClient()->setSetting("admin_zipcode", $_POST["admin_zipcode"]);
            $this->setup->getClient()->setSetting("admin_city", $_POST["admin_city"]);
            $this->setup->getClient()->setSetting("admin_country", $_POST["admin_country"]);
            $this->setup->getClient()->setSetting("admin_phone", $_POST["admin_phone"]);
            $this->setup->getClient()->setSetting("admin_email", $_POST["admin_email"]);
            $this->setup->getClient()->setSetting("inst_institution", $_POST["inst_institution"]);
            $this->setup->getClient()->setSetting("inst_name", $_POST["inst_name"]);
            //$this->setup->getClient()->setSetting("feedback_recipient", $_POST["feedback_recipient"]);
            //$this->setup->getClient()->setSetting("error_recipient", $_POST["error_recipient"]);

            // update client.ini
            $this->setup->getClient()->setName($_POST["inst_name"]);
            $this->setup->getClient()->setDescription($_POST["inst_info"]);
            $this->setup->getClient()->ini->write();

            ilUtil::sendSuccess($this->lng->txt("settings_saved"), true);
            ilUtil::redirect("setup.php?cmd=displayContactData");
        }

        $this->form->setValuesByPost();
        $this->displayContactData(true);
    }

    ////
    //// NIC Registration
    ////

    /**
     * display nic registration form and process form input
     */
    public function displayNIC($a_omit_init = false)
    {
        $this->checkDisplayMode("nic_registration");
        $settings = $this->setup->getClient()->getAllSettings();
        $nic_key = $this->setup->getClient()->getNICkey();

        // reload settings
        $settings = $this->setup->getClient()->getAllSettings();
        //var_dump($settings);
        if ($settings["nic_enabled"] == "1" && $settings["inst_id"] > 0) {
            $this->no_second_nav = true;
            $this->tpl->setVariable("TXT_INFO", $this->lng->txt("info_text_nic3") . " " . $settings["inst_id"] . ".");
        } else {
            // reload settings
            $settings = $this->setup->getClient()->getAllSettings();

            $this->tpl->setVariable("TXT_INFO", $this->lng->txt("info_text_nic"));
            if (!$a_omit_init) {
                $this->initRegistrationForm();
                $this->getRegistrationValues();
            }
            $this->tpl->setVariable("SETUP_CONTENT", $this->form->getHTML());

            if (isset($settings["nic_enabled"])) {
                $this->setup->getClient()->status["nic"]["status"] = true;
            }
        }

        $this->setButtonPrev("proxy");

        if ($this->setup->getClient()->status["nic"]["status"]) {
            $this->setButtonNext("finish", "finish");
        }

        $this->checkPanelMode();
    }

    /**
     * Init registration form.
     *
     * @param        int        $a_mode        Edit Mode
     */
    public function initRegistrationForm($a_mode = "edit")
    {
        global $lng, $ilCtrl;

        include_once("Services/Form/classes/class.ilPropertyFormGUI.php");
        $this->form = new ilPropertyFormGUI();

        // registration type
        $radg = new ilRadioGroupInputGUI($lng->txt("nic_registration"), "register");
        $radg->setValue(1);
        $op1 = new ilRadioOption($lng->txt("nic_reg_online"), 1);
        $radg->addOption($op1);
        $op1 = new ilRadioOption($lng->txt("nic_reg_disable"), 0, $lng->txt("nic_reg_disable_info"));
        $radg->addOption($op1);
        $this->form->addItem($radg);

        $this->form->addCommandButton("saveRegistration", $lng->txt("save"));
        $this->form->setFormAction("setup.php?cmd=gateway");
    }

    /**
     * Get current values for registration from
     */
    public function getRegistrationValues()
    {
        $settings = $this->setup->getClient()->getAllSettings();
        $nic_key = $this->setup->getClient()->getNICkey();


        $values = array();

        if (!isset($settings["nic_enabled"]) or $settings["nic_enabled"] == "1") {
            $values["register"] = 1;
        }
        /*elseif ($settings["nic_enabled"] == "2")
        {
            $this->tpl->setVariable("EMAIL",$checked);
        }*/
        else {
            $values["register"] = 0;
        }

        $this->form->setValuesByArray($values);
    }

    /**
     * Save registration form
     */
    public function saveRegistration()
    {
        global $tpl, $lng, $ilCtrl;

        $this->initRegistrationForm();
        if ($this->form->checkInput()) {
            // check register option
            if ($_POST["register"] == 1) {
                // update nic
                $this->setup->getClient()->updateNIC($this->setup->ilias_nic_server);
                //var_dump($this->setup->getClient()->nic_status);
                // online registration failed
                if (empty($this->setup->getClient()->nic_status[2])) {
                    $this->setup->getClient()->setSetting("nic_enabled", "-1");
                    ilUtil::sendFailure($this->lng->txt("nic_reg_failed"), true);
                    ilUtil::redirect("setup.php?cmd=displayNIC");
                } else {
                    $this->setup->getClient()->setSetting("inst_id", $this->setup->getClient()->nic_status[2]);
                    $this->setup->getClient()->setSetting("nic_enabled", "1");
                    $this->setup->getClient()->status["nic"]["status"] = true;
                    ilUtil::sendSuccess($this->lng->txt("nic_reg_enabled"), true);
                    ilUtil::redirect("setup.php?cmd=displayNIC");
                }
            }
            /*elseif ($_POST["form"]["register"] == 2)
            {
                $nic_by_email = (int) $_POST["form"]["nic_id"];

                $checksum = md5($nic_key.$nic_by_email);

                if (!$nic_by_email or $_POST["form"]["nic_checksum"] != $checksum)
                {
                    $message = $this->lng->txt("nic_reg_enter_correct_id");
                }
                else
                {
                    $this->setup->getClient()->setSetting("inst_id",$nic_by_email);
                    $this->setup->getClient()->setSetting("nic_enabled","1");
                    $message = $this->lng->txt("nic_reg_enabled");
                }
            }*/
            else {
                $this->setup->getClient()->setSetting("inst_id", "0");
                $this->setup->getClient()->setSetting("nic_enabled", "0");
                ilUtil::sendSuccess($this->lng->txt("nic_reg_disabled"), true);
                ilUtil::redirect("setup.php?cmd=displayNIC");
            }
        }

        $this->form->setValuesByPost();
        $this->displayNIC(true);
    }

    ////
    //// Tools
    ////

    /**
     * display tools
     */
    public function displayTools()
    {
        $this->checkDisplayMode();

        // output
        ilUtil::sendInfo();

        // use property forms and add the settings type switch
        $ctrl_structure_form = $this->initControlStructureForm();
        $settings_type_form = $this->initSettingsTypeForm();
        $mp_ns_form = $this->initTreeImplementationForm();

        $this->tpl->setVariable(
            "SETUP_CONTENT",
            $ctrl_structure_form->getHTML() . "<br />" .
            $settings_type_form->getHTML() . '<br />' .
            $mp_ns_form->getHTML()
        );
    }

    public function initTreeImplementationForm()
    {
        include_once("Services/Form/classes/class.ilPropertyFormGUI.php");
        $form = new ilPropertyFormGUI();

        $form->setId('tree_impl');
        $form->setTitle($this->lng->txt('tree_implementation'));
        $form->setFormAction('setup.php?cmd=gateway');


        $options = new ilRadioGroupInputGUI('', 'tree_impl_type');
        #$options->setRequired(true);

        $set = new ilSetting('common');
        $type = ($set->get('main_tree_impl', 'ns') == 'ns' ? 'ns' : 'mp');


        $options->setValue($type);

        $ns = new ilRadioOption($this->lng->txt('tree_implementation_ns'), 'ns');
        $options->addOption($ns);

        $mp = new ilRadioOption($this->lng->txt('tree_implementation_mp'), 'mp');
        $options->addOption($mp);

        $form->addItem($options);
        $form->addCommandButton('switchTree', $this->lng->txt('tree_implementation_switch_btn'));
        $form->setShowTopButtons(false);

        return $form;
    }

    public function switchTree()
    {
        $set = new ilSetting('common');
        $type = ($set->get('main_tree_impl', 'ns') == 'ns' ? 'ns' : 'mp');

        if ($type == 'ns' and $_POST['tree_impl_type'] == 'mp') {
            // To mp
            include_once './Services/Tree/classes/class.ilMaterializedPathTree.php';
            ilMaterializedPathTree::createFromParentReleation();

            $GLOBALS['ilDB']->dropIndexByFields('tree', array('lft'));
            $GLOBALS['ilDB']->dropIndexByFields('tree', array('path'));
            $GLOBALS['ilDB']->addIndex('tree', array('path'), 'i4');

            $set->set('main_tree_impl', 'mp');
        } elseif ($type == 'mp' and $_POST['tree_impl_type'] == 'ns') {
            include_once './Services/Tree/classes/class.ilTree.php';
            $GLOBALS['ilSetting'] = $set;
            $GLOBALS["DIC"]["ilSetting"] = function ($c) {
                return $GLOBALS["ilSetting"];
            };
            $tree = new ilTree(1);
            $tree->renumber(1);

            $GLOBALS['ilDB']->dropIndexByFields('tree', array('lft'));
            $GLOBALS['ilDB']->dropIndexByFields('tree', array('path'));
            $GLOBALS['ilDB']->addIndex('tree', array('lft'), 'i4');

            $set->set('main_tree_impl', 'ns');
        }

        ilUtil::sendInfo($this->lng->txt("tree_implementation_switched"), true);
        $this->displayTools();
    }

    /**
    * Init the form to reload the control structure
    *
    * @return   object  property form to reload control structure
    */
    public function initControlStructureForm()
    {
        include_once("Services/Form/classes/class.ilPropertyFormGUI.php");
        $form = new ilPropertyFormGUI();

        $form->setId("control_structure");
        $form->setTitle($this->lng->txt("ctrl_structure"));
        $form->setFormAction("setup.php?cmd=gateway");

        $ilDB = $this->setup->getClient()->db;
        $cset = $ilDB->query("SELECT count(*) as cnt FROM ctrl_calls");
        $crec = $ilDB->fetchAssoc($cset);

        $item = new ilCustomInputGUI($this->lng->txt("ctrl_structure_reload"));
        if ($crec["cnt"] == 0) {
            $item->setInfo($this->lng->txt("ctrl_missing_desc"));
        } else {
            $item->setInfo($this->lng->txt("ctrl_structure_desc"));
        }
        $form->addItem($item);

        $form->addCommandButton("reloadStructure", $this->lng->txt("reload"));
        return $form;
    }


    /**
    * reload control structure
    */
    public function reloadControlStructure()
    {
        global $ilCtrlStructureReader;

        if (!$this->setup->getClient()->db_installed) {
            ilUtil::sendInfo($this->lng->txt("no_db"), true);
            $this->displayTools();
            return;
        }
        
        // referencing does not work in dbupdate-script
        $GLOBALS["ilDB"] = $this->setup->getClient()->getDB();
        $GLOBALS["DIC"]["ilDB"] = function ($c) {
            return $GLOBALS["ilDB"];
        };
        // BEGIN WebDAV
        // read module and service information into db

        $modules = ilModule::getAvailableCoreModules();
        $services = ilService::getAvailableCoreServices();

        $ilCtrlStructureReader->readStructure(true);

        // clear tables
        $mr = new ilModuleReader("", "", "");
        $mr->clearTables();
        foreach ($modules as $module) {
            $mr = new ilModuleReader(
                ILIAS_ABSOLUTE_PATH . "/Modules/" . $module["subdir"] . "/module.xml",
                $module["subdir"],
                "Modules"
            );
            $mr->getModules();
            unset($mr);
        }

        // clear tables
        $sr = new ilServiceReader("", "", "");
        $sr->clearTables();
        foreach ($services as $service) {
            $sr = new ilServiceReader(
                ILIAS_ABSOLUTE_PATH . "/Services/" . $service["subdir"] . "/service.xml",
                $service["subdir"],
                "Services"
            );
            $sr->getServices();
            unset($sr);
        }
        // END WebDAV

        ilUtil::sendInfo($this->lng->txt("ctrl_structure_reloaded"), true);
        $this->displayTools();
    }

    /**
    * Init the form to change the settings value type
    *
    * @return   object  property form to change settings type
    */
    public function initSettingsTypeForm()
    {
        include_once("./Services/Administration/classes/class.ilSetting.php");
        $type = ilSetting::_getValueType();

        include_once("Services/Form/classes/class.ilPropertyFormGUI.php");
        $form = new ilPropertyFormGUI();

        $form->setId("settings_type");
        $form->setTitle($this->lng->txt("settings_type"));
        $form->setFormAction("setup.php?cmd=gateway");

        $item = new ilNonEditableValueGUI($this->lng->txt('settings_type_current'));
        $item->setValue(strtoupper($type));

        if ($type == "clob") {
            $item->setInfo($this->lng->txt('settings_info_clob'));
            $form->addCommandButton("showLongerSettings", $this->lng->txt("settings_show_longer"));
            $form->addCommandButton("changeSettingsType", $this->lng->txt("settings_change_text"));
        } else {
            $item->setInfo($this->lng->txt('settings_info_text'));
            $form->addCommandButton("changeSettingsType", $this->lng->txt("settings_change_clob"));
        }
        $form->addItem($item);

        if (is_array($this->longer_settings)) {
            $item = new ilCustomInputGUI($this->lng->txt('settings_longer_values'));

            if (count($this->longer_settings)) {
                foreach ($this->longer_settings as $row) {
                    $subitem = new ilCustomInputGUI(sprintf($this->lng->txt('settings_key_info'), $row['module'], $row['keyword']));
                    $subitem->setInfo($row['value']);
                    $item->addSubItem($subitem);
                }
            } else {
                $item->setHTML($this->lng->txt('settings_no_longer_values'));
            }
            $form->addItem($item);
        }

        return $form;
    }


    /**
    * change the type of the value field in settings table
    */
    public function changeSettingsType()
    {
        include_once("./Services/Administration/classes/class.ilSetting.php");
        $old_type = ilSetting::_getValueType();

        if ($old_type == "clob") {
            $longer_settings = ilSetting::_getLongerSettings();
            if (count($longer_settings)) {
                $this->longer_settings = $longer_settings;
                ilUtil::sendFailure($this->lng->txt("settings_too_long"));
            } else {
                $changed = ilSetting::_changeValueType('text');
            }
        } else {
            $changed = ilSetting::_changeValueType('clob');
        }

        if ($changed) {
            ilUtil::sendInfo($this->lng->txt("settings_type_changed"));
        }

        $this->displayTools();
    }


    /**
    * show a list of setting values that are loger than 4000 characters
    *
    */
    public function showLongerSettings()
    {
        include_once("./Services/Administration/classes/class.ilSetting.php");
        $this->longer_settings = ilSetting::_getLongerSettings();
        $this->displayTools();
    }
    
    /**
     * return \ilPropertyFormGUI
     */
    protected function getMasterPasswordForm()
    {
        require_once 'Services/Form/classes/class.ilPropertyFormGUI.php';
        $form = new \ilPropertyFormGUI();
        $form->setTitle($this->lng->txt('change_password'));

        $currentPassword = new \ilPasswordInputGUI($this->lng->txt('set_oldpasswd'), 'pass_old');
        $currentPassword->setDisableHtmlAutoComplete(true);
        $currentPassword->setValidateAuthPost(false);
        $currentPassword->setSkipSyntaxCheck(true);
        $currentPassword->setRequired(true);
        $currentPassword->setRetype(false);
        $form->addItem($currentPassword);

        $newPassword = new \ilPasswordInputGUI($this->lng->txt('set_newpasswd'), 'pass');
        $newPassword->setDisableHtmlAutoComplete(true);
        $newPassword->setValidateAuthPost(false);
        $newPassword->setSkipSyntaxCheck(true);
        $newPassword->setRequired(true);
        $newPassword->setRetype(true);
        $form->addItem($newPassword);

        $form->setFormAction('setup.php?cmd=gateway');
        $form->addCommandButton('savemasterpassword', $this->lng->txt('save'));

        return $form;
    }

    /**
     * @throws ilUserException
     */
    protected function saveMasterPassword()
    {
        $form = $this->getMasterPasswordForm();

        $isValid = $form->checkInput();
        $form->setValuesByPost();

        if (!$isValid) {
            return $this->changeMasterPassword($form);
        }

        $cp = $form->getInput('pass_old');
        $np = $form->getInput('pass');

        if (!$this->setup->verifyMasterPassword($cp)) {
            \ilUtil::sendFailure($this->lng->txt('password_old_wrong'));
            return $this->changeMasterPassword($form);
        }

        if (!$this->setup->storeMasterPassword($np)) {
            \ilUtil::sendFailure($this->lng->txt('save_error'));
            return $this->changeMasterPassword($form);
        }

        \ilUtil::sendInfo($this->lng->txt('password_changed'), true);
        \ilUtil::redirect("setup.php");
    }

    /**
     * display change password form and process form input
     * @param ilPropertyFormGUI|null $form
     */
    protected function changeMasterPassword(\ilPropertyFormGUI $form = null)
    {
        if (null === $form) {
            $form = $this->getMasterPasswordForm();
        }

        $this->tpl->addBlockFile('CONTENT', 'content', 'tpl.std_layout.html', 'setup');
        $this->tpl->setVariable('TXT_HEADER', $this->lng->txt('password_new_master'));
        $this->tpl->setVariable('TXT_INFO', $this->lng->txt('info_text_password'));
        $this->tpl->setVariable('SETUP_CONTENT', $form->getHTML());
    }

    /**
     * display finish setup page
     */
    public function displayFinishSetup()
    {
        $this->checkDisplayMode("finish_setup");
        $this->no_second_nav = true;
        //echo "<b>1</b>";
        if ($this->validateSetup()) {
            $txt_info = $this->lng->txt("info_text_finish1") . "<br /><br />" .
                "<p>" . $this->lng->txt("user") . ": <b>root</b><br />" .
                $this->lng->txt("password") . ": <b>homer</b></p>";
            $this->setButtonNext("login_new", "login");
            //echo "<b>2</b>";
            $this->setup->getClient()->reconnect();		// if this is not done, the writing of
                                            // the setup_ok fails (with MDB2 and a larger
                                            // client list), alex 17.1.2008
            $this->setup->getClient()->setSetting("setup_ok", 1);
            //$this->setup->getClient()->setSetting("zzz", "Z");
            //echo "<b>3</b>";
            $this->setup->getClient()->status["finish"]["status"] = true;
        //echo "<b>4</b>";
        } else {
            $txt_info = $this->lng->txt("info_text_finish2");
        }

        //echo "<b>5</b>";
        // output
        $this->tpl->addBlockFile("SETUP_CONTENT", "setup_content", "tpl.clientsetup_finish.html", "setup");
        $this->tpl->setVariable("TXT_INFO", $txt_info);

        $this->setButtonPrev("nic");
        //echo "<b>6</b>";
        $this->checkPanelMode();
        //echo "<b>7</b>";
    }

    /**
     * display delete client confirmation form and process form input
     */
    public function displayDeleteConfirmation()
    {
        $this->checkDisplayMode();

        // formular sent
        if ($_POST["form"]["delete"]) {
            $ini = true;
            $db = false;
            $files = false;

            /* disabled
            switch ($_POST["form"]["delete"])
            {
                case 1:
                    $ini = true;
                    break;

                case 2:
                    $ini = true;
                    $db = true;
                    break;

                case 3:
                    $ini = true;
                    $db = true;
                    $files = true;
                    break;
            }
            */

            $msg = $this->setup->getClient()->delete($ini, $db, $files);

            ilUtil::sendInfo($this->lng->txt("client_deleted"), true);
            ilUtil::redirect("setup.php");
        }

        $this->tpl->setVariable("TXT_INFO", $this->lng->txt("info_text_delete"));

        // output
        $this->tpl->addBlockFile("SETUP_CONTENT", "setup_content", "tpl.form_delete_client.html", "setup");

        // delete panel
        $this->tpl->setVariable("FORMACTION", "setup.php?cmd=gateway");
        $this->tpl->setVariable("TXT_DELETE", $this->lng->txt("delete"));
        $this->tpl->setVariable("TXT_DELETE_CONFIRM", $this->lng->txt("delete_confirm"));
        $this->tpl->setVariable("TXT_DELETE_INFO", $this->lng->txt("delete_info"));

        $this->checkPanelMode();
    }

    /**
     * enable/disable access to a client
     *
     * @param    string  jump back to this script
     */
    public function changeAccessMode($a_back)
    {
        if ($this->setup->getClient()->status["finish"]["status"]) {
            $val = ($this->setup->getClient()->ini->readVariable("client", "access")) ? "0" : true;
            $this->setup->getClient()->ini->setVariable("client", "access", $val);
            $this->setup->getClient()->ini->write();
            $message = "client_access_mode_changed";
        } else {
            $message = "client_setup_not_finished";
        }

        ilUtil::sendInfo($this->lng->txt($message), true);

        ilUtil::redirect("setup.php?cmd=" . $a_back);
    }

    /**
     * set defualt client
     */
    public function changeDefaultClient()
    {
        if ($_POST["form"]) {
            $client = new ilClient($_POST["form"]["default"]);

            if (!$client->init()) {
                \ilUtil::sendFailure($this->lng->txt("no_valid_client_id"), true);
                \ilUtil::redirect("setup.php?cmd=clientlist");
            }

            $status = $this->setup->getStatus($client);

            if ($status["finish"]["status"]) {
                $this->setup->ini->setVariable("clients", "default", $client->getId());
                $this->setup->ini->write();
                $message = "default_client_changed";
            } else {
                $message = "client_setup_not_finished";
            }
        }

        ilUtil::sendInfo($this->lng->txt($message), true);

        ilUtil::redirect("setup.php");
    }

    /**
     * validatesetup status again
     * and set access mode of the first client to online
     */
    public function validateSetup()
    {
        foreach ($this->setup->getClient()->status as $key => $val) {
            if ($key != "finish" and $key != "access") {
                if ($val["status"] != true) {
                    return false;
                }
            }
        }

        //$this->setup->getClient()->setSetting("zzz", "V");
        $clientlist = new ilClientList();
        //$this->setup->getClient()->setSetting("zzz", "W");
        $list = $clientlist->getClients();
        //$this->setup->getClient()->setSetting("zzz", "X");
        if (count($list) == 1) {
            $this->setup->ini->setVariable("clients", "default", $this->setup->getClient()->getId());
            $this->setup->ini->write();

            $this->setup->getClient()->ini->setVariable("client", "access", 1);
            $this->setup->getClient()->ini->write();
        }
        //$this->setup->getClient()->setSetting("zzz", "Y");
        return true;
    }

    /**
     * if setting up a client was not finished, jump back to the first uncompleted setup step
     */
    public function jumpToFirstUnfinishedSetupStep()
    {
        if (!$this->setup->getClient()->status["db"]["status"]) {
            $this->cmd = "db";
            ilUtil::sendInfo($this->lng->txt("finish_initial_setup_first"), true);
            $this->displayDatabase();
        } elseif (!$this->setup->getClient()->status["lang"]["status"]) {
            $this->cmd = "lang";
            ilUtil::sendInfo($this->lng->txt("finish_initial_setup_first"), true);
            $this->displayLanguages();
        } elseif (!$this->setup->getClient()->status["contact"]["status"]) {
            $this->cmd = "contact";
            ilUtil::sendInfo($this->lng->txt("finish_initial_setup_first"), true);
            $this->displayContactData();
        } elseif (!$this->setup->getClient()->status['proxy']['status']) {
            $this->cmd = "proxy";
            ilUtil::sendInfo($this->lng->txt("finish_initial_setup_first"), true);
            $this->displayProxy();
        } elseif (!$this->setup->getClient()->status["nic"]["status"]) {
            $this->cmd = "nic";
            ilUtil::sendInfo($this->lng->txt("finish_initial_setup_first"), true);
            $this->displayNIC();
        } elseif (!$this->setup->getClient()->status["finish"]["status"]) {
            $this->cmd = "finish";
            ilUtil::sendInfo($this->lng->txt("finish_initial_setup_first"), true);
            $this->displayFinishSetup();
        } else {
            return false;
        }
    }

    /**
     * enable/disable client list on index page
     */
    public function toggleClientList()
    {
        if ($this->setup->ini->readVariable("clients", "list")) {
            $this->setup->ini->setVariable("clients", "list", "0");
            $this->setup->ini->write();
            ilUtil::sendInfo($this->lng->txt("list_disabled"), true);
        } else {
            $this->setup->ini->setVariable("clients", "list", "1");
            $this->setup->ini->write();
            ilUtil::sendInfo($this->lng->txt("list_enabled"), true);
        }

        ilUtil::redirect("setup.php");
    }

    ////
    //// APPLY CUSTOM DB UPDATES
    ////

    public function applyCustomUpdates()
    {
        global $ilCtrlStructureReader;

        $ilCtrlStructureReader->setIniFile($this->setup->getClient()->ini);

        include_once "./Services/AccessControl/classes/class.ilRbacAdmin.php";
        include_once "./Services/AccessControl/classes/class.ilRbacReview.php";
        include_once "./Services/AccessControl/classes/class.ilRbacSystem.php";
        include_once "./Services/Tree/classes/class.ilTree.php";
        include_once "./Services/Xml/classes/class.ilSaxParser.php";
        include_once "./Services/Object/classes/class.ilObjectDefinition.php";

        // referencing db handler in language class
        $ilDB = $this->setup->getClient()->db;
        $this->lng->setDbHandler($ilDB);

        // run dbupdate

        $dbupdate = new ilDBUpdate($ilDB, $this->setup->getClient()->ini);
        $dbupdate->applyCustomUpdates();

        if ($dbupdate->updateMsg == "no_changes") {
            $message = $this->lng->txt("no_changes") . ". " . $this->lng->txt("database_is_uptodate");
        } else {
            $sep = "";
            foreach ($dbupdate->updateMsg as $row) {
                if ($row["msg"] == "update_applied") {
                    $a_message .= $sep . $row["nr"];
                    $sep = ", ";
                } else {
                    $e_message .= "<br/>" . $this->lng->txt($row["msg"]) . ": " . $row["nr"];
                }
            }
            if ($a_message != "") {
                $a_message = $this->lng->txt("update_applied") . ": " . $a_message;
            }
        }

        ilUtil::sendInfo($a_message . $e_message, true);
        ilUtil::redirect("setup.php?cmd=displayDatabase");
    }

    /**
     * Initialize clone form
     */
    public function cloneInitForm()
    {
        global $lng, $ilCtrl;

        $this->checkDisplayMode();

        include_once("Services/Form/classes/class.ilPropertyFormGUI.php");
        $this->form = new ilPropertyFormGUI();

        $this->form->setId("clone_form");
        $this->form->setFormAction("setup.php?cmd=gateway");

        if ($this->setup->getClient()->status["access"]["status"] === false and stripos($this->setup->getClient()->getName(), "master") === false and $this->setup->getClient()->getdbType() == "mysql" and $this->setup->getClient()->db_exists) {
            $this->form->setTitle($this->lng->txt("clone_source"));
            $clients = array();
            $clientlist = new ilClientList();
            $list = $clientlist->getClients();
            $clientlistarray = array();

            foreach ($list as $key => $client) {
                if ((strcmp($key, $this->setup->getClient()->getId()) != '0') && ($client->getDbType() == 'mysql')) {  // You cannot clone yourself
                    $clientlistarray[$client->id] = $client->id;
                }
            }

            $si = new ilSelectInputGUI($lng->txt("clone_selectsource"), "source");

            $si->setOptions(array_merge(
                array("" => "-- " . $lng->txt("please_select") . " --"),
                $clientlistarray
            ));
            $si->setRequired(true);
            $this->form->addItem($si);

            $cb = new ilCheckboxInputGUI($lng->txt("clone_areyousure"), "iamsure");
            $cb->setRequired(true);
            $this->form->addItem($cb);

            $this->form->addCommandButton("cloneSaveSource", $lng->txt("cloneit"));
        } else {
            $disabledmessage = "<h1>" . $this->lng->txt("clone_disabledmessage") . "</h1><br>";
            if (!$this->setup->getClient()->status["access"]["status"] === false) {
                $disabledmessage .= $this->lng->txt("clone_clientnotdisabled") . "<br>";
            }
            if (!stripos($this->setup->getClient()->getName(), "aster") === false) {
                $disabledmessage .= $this->lng->txt("clone_clientismaster") . "<br>";
            }
            if ($this->setup->getClient()->getdbType() != "mysql") {
                $disabledmessage .= $this->lng->txt("clone_clientisnotmysql") . "<br>";
            }
            if (!$this->setup->getClient()->db_exists) {
                $disabledmessage .= $this->lng->txt("clone_clientnodatabase") . "<br>";
            }
            $this->form->setTitle($disabledmessage);
        }
    }

    public function cloneSelectSource()
    {
        if (!$this->setup->isAdmin()) {
            return;
        }

        $this->cloneInitForm();
        $this->form->setValuesByPost();
        $this->tpl->setVariable("TXT_INFO", $this->lng->txt("info_text_clone"));
        $this->tpl->setVariable("SETUP_CONTENT", $this->form->getHTML());
    }

    public function cloneSaveSource()
    {
        global $lng, $ilCtrl;

        if (!$this->setup->isAdmin()) {
            return;
        }

        $this->cloneInitForm();

        if ($this->form->checkInput()) {
            $error = '';

            if ($this->form->getInput("iamsure") != "1") {
                $error = $this->lng->txt('clone_youmustcheckiamsure');
            }

            if (!$this->setup->cloneFromSource($this->form->getInput("source"))) {
                $error = $this->lng->txt('clone_error') . ' -> ' . $this->setup->error;
            }

            if (0 === strlen($error)) {
                \ilUtil::sendInfo($this->lng->txt('client_cloned'));
            } else {
                \ilUtil::sendFailure($error);
            }
        }
        $this->form->setValuesByPost();
        $this->tpl->setVariable("TXT_INFO", $this->lng->txt("info_text_clone"));
        $this->tpl->setVariable("SETUP_CONTENT", $this->form->getHTML());
    }

    public function displayProxy($a_omit_init = false)
    {
        $this->checkDisplayMode("proxy");
        $settings = $this->setup->getClient()->getAllSettings();

        if (!$a_omit_init) {
            include_once("./Services/Administration/classes/class.ilSetting.php");
            $this->initProxyForm();
            $this->form->setValuesByArray(array(
                'proxy_status' => (bool) $settings['proxy_status'],
                'proxy_host' => $settings['proxy_host'],
                'proxy_port' => $settings['proxy_port']
            ));
            if ((bool) $settings['proxy_status']) {
                $this->setup->printProxyStatus($this->setup->client);
            }
        }
        $this->tpl->setVariable("SETUP_CONTENT", $this->form->getHTML());
        $this->tpl->setVariable("TXT_INFO", $this->lng->txt("info_text_proxy"));


        $check = $this->setup->checkClientProxySettings($this->setup->client);

        $this->setup->getClient()->status["proxy"]["status"] = $check["status"];
        $this->setup->getClient()->status["proxy"]["comment"] = $check["comment"];
        $this->setup->getClient()->status["proxy"]["text"] = $check["comment"];

        if ($check["status"]) {
            $this->setButtonNext("nic");
        }

        $this->setButtonPrev("contact");
        $this->checkPanelMode();
    }
    private function initProxyForm()
    {
        global $lng;

        include_once('Services/Form/classes/class.ilPropertyFormGUI.php');
        $this->form = new ilPropertyFormGUI();
        $this->form->setFormAction("setup.php?cmd=gateway");

        // Proxy status
        $proxs = new ilCheckboxInputGUI($lng->txt('proxy_status'), 'proxy_status');
        $proxs->setInfo($lng->txt('proxy_status_info'));
        $proxs->setValue(1);
        $this->form->addItem($proxs);

        // Proxy availability
        $proxa = new ilCustomInputGUI('', 'proxy_availability');
        $proxs->addSubItem($proxa);

        // Proxy
        $prox = new ilTextInputGUI($lng->txt('proxy_host'), 'proxy_host');
        $prox->setInfo($lng->txt('proxy_host_info'));
        $proxs->addSubItem($prox);

        // Proxy Port
        $proxp = new ilTextInputGUI($lng->txt('proxy_port'), 'proxy_port');
        $proxp->setInfo($lng->txt('proxy_port_info'));
        $proxp->setSize(10);
        $proxp->setMaxLength(10);
        $proxs->addSubItem($proxp);

        // save and cancel commands
        $this->form->addCommandButton('saveProxy', $lng->txt('save'));
    }

    /**
     *
     * Save proxy settings
     *
     * @access	public
     *
     */
    public function saveProxy()
    {
        global $lng;

        $this->initProxyForm();
        $isFormValid = $this->form->checkInput();

        $new_settings['proxy_status'] = (int) $this->form->getInput('proxy_status');
        $new_settings['proxy_host'] = trim($this->form->getInput('proxy_host'));
        $new_settings['proxy_port'] = trim($this->form->getInput('proxy_port'));

        if ($isFormValid) {
            if ($new_settings['proxy_status'] == true) {
                if (!strlen($new_settings['proxy_host'])) {
                    $isFormValid = false;
                    $this->form->getItemByPostVar('proxy_host')->setAlert($lng->txt('msg_input_is_required'));
                }
                if (!strlen($new_settings['proxy_port'])) {
                    $isFormValid = false;
                    $this->form->getItemByPostVar('proxy_port')->setAlert($lng->txt('msg_input_is_required'));
                }
                if (!preg_match('/[0-9]{1,}/', $new_settings['proxy_port']) ||
                    $new_settings['proxy_port'] < 0 ||
                    $new_settings['proxy_port'] > 65535) {
                    $isFormValid = false;
                    $this->form->getItemByPostVar('proxy_port')->setAlert($lng->txt('proxy_port_numeric'));
                }
            }

            if ($isFormValid) {
                $this->setup->saveProxySettings($new_settings);

                ilUtil::sendSuccess($lng->txt('saved_successfully'));
                $settings = $this->setup->getClient()->getAllSettings();
                if ($settings['proxy_status'] == true) {
                    $this->setup->printProxyStatus($this->setup->client);
                }
            } else {
                ilUtil::sendFailure($lng->txt('form_input_not_valid'));
            }
        }

        $this->form->setValuesByPost();
        $this->tpl->setVariable("SETUP_CONTENT", $this->form->getHTML());


        $this->displayProxy(true);
    }

    protected function background_tasks()
    {
        $this->setDisplayMode("view");
        $this->tpl->addBlockFile("CONTENT", "content", "tpl.std_layout.html", "setup");

        include_once("./setup/classes/class.ilBackgroundTaskTableGUI.php");
        $table = new ilBackgroundTaskTableGUI($this->setup);

        $this->tpl->setVariable("SETUP_CONTENT", $this->bt_tabs()->get() . $table->getHTML());
    }


    /**
     * @return ilPropertyFormGUI
     */
    protected function createBackgroundTasksForm()
    {
        include_once("Services/Form/classes/class.ilPropertyFormGUI.php");

        $form = new ilPropertyFormGUI();
        $form->setTitle($this->lng->txt('background_task_configuration'));
        $form->addCommandButton('save_background_tasks', $this->lng->txt('save'));
        $form->addCommandButton('background_tasks', $this->lng->txt('cancel'));
        $form->setFormAction('setup.php?cmd=gateway');

        $rgroup = new ilRadioGroupInputGUI($this->lng->txt("type"), "concurrency");

        $cc = new ilRadioOption($this->lng->txt('sync'), 'sync');
        $rgroup->addOption($cc);

        $cc = new ilRadioOption($this->lng->txt('async'), 'async');
        $rgroup->addOption($cc);

        $form->addItem($rgroup);

        $i = new ilNumberInputGUI($this->lng->txt('max_number_of_concurrent_tasks'), 'number_of_concurrent_tasks');
        $form->addItem($i);

        return $form;
    }

    protected function edit_background_tasks()
    {
        require_once('Services/Form/classes/class.ilPropertyFormGUI.php');
        $this->setDisplayMode("view");
        $this->tpl->addBlockFile("CONTENT", "content", "tpl.std_layout.html", "setup");

        $form = $this->createBackgroundTasksForm();
        $this->fillBackgroundTasksForm($form);

        $this->tpl->setVariable("SETUP_CONTENT", $this->bt_tabs(true)->get() . $form->getHTML());
    }

    /**
     * @param $form ilPropertyFormGUI
     */
    private function fillBackgroundTasksForm(&$form)
    {
        $n_of_tasks = $this->setup->ini->readVariable("background_tasks", "number_of_concurrent_tasks");
        $sync = $this->setup->ini->readVariable("background_tasks", "concurrency");

        $n_of_tasks = $n_of_tasks ? $n_of_tasks : 5;
        $sync = $sync ? $sync : 'sync'; // The default value is sync.

        $form->setValuesByArray([
            'concurrency' => $sync,
            'number_of_concurrent_tasks' => $n_of_tasks
        ]);
    }

    public function save_background_tasks()
    {
        $form = $this->createBackgroundTasksForm();
        $form->setValuesByPost();

        // If something goes wrong we display the content with warnings again.
        if (!$form->checkInput()) {
            $this->tpl->setVariable('CONTENT', $this->bt_tabs(true)->get() . $form->getHTML());
            return;
        }

        if ($this->saveBTFormToIni($form)) {
            ilUtil::sendSuccess($this->lng->txt('saved_successfully'), true);
        } else {
            ilUtil::sendFailure($this->lng->txt('permission_denied'), true);
        }
        
        ilUtil::redirect("setup.php?cmd=edit_background_tasks");
    }


    /**
     * @param $form ilPropertyFormGUI
     */
    private function saveBTFormToIni(&$form)
    {
        if (!$this->setup->ini->groupExists('background_tasks')) {
            $this->setup->ini->addGroup('background_tasks');
            $this->setup->ini->write();
        }

        $this->setup->ini->setVariable("background_tasks", "concurrency", $form->getInput('concurrency'));
        $this->setup->ini->setVariable("background_tasks", "number_of_concurrent_tasks", $form->getInput('number_of_concurrent_tasks'));

        return $this->setup->ini->write();
    }

    public function kill_waiting_tasks()
    {
        $client_id = $_GET['client_id'];
        $this->setup->newClient($client_id);
        $client = $this->setup->getClient();
        $client->provideGlobalDB();

        $persistence = BasicPersistence::instance();
        $bucket_ids = $persistence->getBucketIdsByState(State::SCHEDULED);
        foreach ($bucket_ids as $bucket_id) {
            $persistence->deleteBucketById($bucket_id);
        }

        ilUtil::sendSuccess($this->lng->txt('terminated_waiting_tasks'), true);
        ilUtil::redirect("setup.php?cmd=background_tasks");
    }
} // END class.ilSetupGUI
