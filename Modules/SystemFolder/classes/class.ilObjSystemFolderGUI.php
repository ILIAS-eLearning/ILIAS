<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

require_once "./Services/Object/classes/class.ilObjectGUI.php";
require_once('./Services/Repository/classes/class.ilObjectPlugin.php');

use ILIAS\Setup\Metrics;
use ILIAS\Setup\ImplementationOfInterfaceFinder;
use ILIAS\Setup\ImplementationOfAgentFinder;
use ILIAS\Data\Factory;
use ILIAS\Setup\CLI\StatusCommand;

/**
 * Class ilObjSystemFolderGUI
 *
 * @author Stefan Meyer <meyer@leifos.com>
 * $Id$
 *
 * @ilCtrl_Calls ilObjSystemFolderGUI: ilPermissionGUI, ilImprintGUI
 * @ilCtrl_Calls ilObjSystemFolderGUI: ilObjectOwnershipManagementGUI, ilCronManagerGUI
 *
 * @extends ilObjectGUI
 */
class ilObjSystemFolderGUI extends ilObjectGUI
{
    /**
     * @var ilTabsGUI
     */
    protected $tabs;

    /**
     * @var ilRbacSystem
     */
    protected $rbacsystem;

    /**
     * @var ilObjectDefinition
     */
    protected $obj_definition;

    /**
     * @var ilErrorHandling
     */
    protected $error;

    /**
     * @var ilDB
     */
    protected $db;

    /**
     * @var ilStyleDefinition
     */
    protected $style_definition;

    /**
     * @var ilHelpGUI
     */
    protected $help;

    /**
     * @var ilIniFile
     */
    protected $client_ini;

    /**
     * @var ilBenchmark
     */
    protected $bench;

    /**
    * ILIAS3 object type abbreviation
    * @var		string
    * @access	public
    */
    public $type;

    /**
    * Constructor
    * @access public
    */
    public function __construct($a_data, $a_id, $a_call_by_reference)
    {
        global $DIC;

        $this->tabs = $DIC->tabs();
        $this->access = $DIC->access();
        $this->ctrl = $DIC->ctrl();
        $this->rbacsystem = $DIC->rbac()->system();
        $this->user = $DIC->user();
        $this->obj_definition = $DIC["objDefinition"];
        $this->settings = $DIC->settings();
        $this->error = $DIC["ilErr"];
        $this->db = $DIC->database();
        $this->style_definition = $DIC["styleDefinition"];
        $this->lng = $DIC->language();
        $this->tpl = $DIC["tpl"];
        $this->help = $DIC["ilHelp"];
        $this->toolbar = $DIC->toolbar();
        $this->client_ini = $DIC["ilClientIniFile"];
        $this->type = "adm";
        $this->bench = $DIC["ilBench"];
        parent::__construct($a_data, $a_id, $a_call_by_reference, false);

        $this->lng->loadLanguageModule("administration");
        $this->lng->loadLanguageModule("adm");
    }

    public function executeCommand()
    {
        $ilTabs = $this->tabs;

        $next_class = $this->ctrl->getNextClass($this);
        $this->prepareOutput();
        
        switch ($next_class) {
            case 'ilpermissiongui':
                include_once("Services/AccessControl/classes/class.ilPermissionGUI.php");
                $perm_gui = new ilPermissionGUI($this);
                $ret = &$this->ctrl->forwardCommand($perm_gui);
                break;
            
            case 'ilimprintgui':
                // page editor will set its own tabs
                $ilTabs->clearTargets();
                $ilTabs->setBackTarget(
                    $this->lng->txt("back"),
                    $this->ctrl->getLinkTarget($this, "")
                );
            
                include_once("./Services/Imprint/classes/class.ilImprintGUI.php");
                $igui = new ilImprintGUI();
                                
                // needed for editor
                $igui->setStyleId(ilObjStyleSheet::getEffectiveContentStyleId(0, "impr"));
                
                if (!$this->checkPermissionBool("write")) {
                    $igui->setEnableEditing(false);
                }
                
                $ret = $this->ctrl->forwardCommand($igui);
                if ($ret != "") {
                    $this->tpl->setContent($ret);
                }
                break;
                
            case "ilobjectownershipmanagementgui":
                $this->setSystemCheckSubTabs("no_owner");
                include_once("Services/Object/classes/class.ilObjectOwnershipManagementGUI.php");
                $gui = new ilObjectOwnershipManagementGUI(0);
                $this->ctrl->forwardCommand($gui);
                break;
            
            case "ilcronmanagergui":
                $ilTabs->activateTab("cron_jobs");
                include_once("Services/Cron/classes/class.ilCronManagerGUI.php");
                $gui = new ilCronManagerGUI();
                $this->ctrl->forwardCommand($gui);
                break;
            
            default:
//var_dump($_POST);
                $cmd = $this->ctrl->getCmd("view");

                $cmd .= "Object";
                $this->$cmd();

                break;
        }

        return true;
    }

    /**
    * show admin subpanels and basic settings form
    *
    * @access	public
    */
    public function viewObject()
    {
        $ilAccess = $this->access;

        if ($ilAccess->checkAccess("write", "", $this->object->getRefId())) {
            return $this->showBasicSettingsObject();
        }
        return $this->showServerInfoObject();
    }

    public function viewScanLogObject()
    {
        return $this->viewScanLog();
    }
    
    /**
    * Set sub tabs for general settings
    */
    public function setSystemCheckSubTabs($a_activate)
    {
        $ilTabs = $this->tabs;
        $ilCtrl = $this->ctrl;
        
        $ilTabs->addSubTab(
            "system_check_sub",
            $this->lng->txt("system_check"),
            $ilCtrl->getLinkTarget($this, "check")
        );
        $ilTabs->addSubTab(
            "no_owner",
            $this->lng->txt("system_check_no_owner"),
            $ilCtrl->getLinkTargetByClass("ilObjectOwnershipManagementGUI")
        );
        
        $ilTabs->setSubTabActive($a_activate);
        $ilTabs->setTabActive("system_check");
    }

    /**
    * displays system check menu
    *
    * @access	public
    */
    public function checkObject()
    {
        $rbacsystem = $this->rbacsystem;
        $ilUser = $this->user;
        $objDefinition = $this->obj_definition;
        $ilSetting = $this->settings;
        $ilErr = $this->error;
        
        $this->setSystemCheckSubTabs("system_check_sub");

        if (!$rbacsystem->checkAccess("visible,read", $this->object->getRefId())) {
            $ilErr->raiseError($this->lng->txt("permission_denied"), $ilErr->MESSAGE);
        }
        //echo "1";

        if ($_POST['count_limit'] !== null || $_POST['age_limit'] !== null || $_POST['type_limit'] !== null) {
            $ilUser->writePref(
                'systemcheck_count_limit',
                (is_numeric($_POST['count_limit']) && $_POST['count_limit'] > 0) ? $_POST['count_limit'] : ''
            );
            $ilUser->writePref(
                'systemcheck_age_limit',
                (is_numeric($_POST['age_limit']) && $_POST['age_limit'] > 0) ? $_POST['age_limit'] : ''
            );
            $ilUser->writePref('systemcheck_type_limit', trim($_POST['type_limit']));
        }

        if ($_POST["mode"]) {
            //echo "3";
            $this->writeCheckParams();
            $this->startValidator($_POST["mode"], $_POST["log_scan"]);
        } else {
            //echo "4";
            include_once "./Services/Repository/classes/class.ilValidator.php";
            $validator = new ilValidator();
            $hasScanLog = $validator->hasScanLog();

            $this->tpl->addBlockFile(
                "ADM_CONTENT",
                "adm_content",
                "tpl.adm_check.html",
                "Modules/SystemFolder"
            );
            
            if ($hasScanLog) {
                $this->tpl->setVariable("TXT_VIEW_LOG", $this->lng->txt("view_last_log"));
            }

            $this->tpl->setVariable("FORMACTION", $this->ctrl->getFormAction($this));
            $this->tpl->setVariable("TXT_TITLE", $this->lng->txt("systemcheck"));
            $this->tpl->setVariable("COLSPAN", 3);
            $this->tpl->setVariable("TXT_ANALYZE_TITLE", $this->lng->txt("analyze_data"));
            $this->tpl->setVariable("TXT_ANALYSIS_OPTIONS", $this->lng->txt("analysis_options"));
            $this->tpl->setVariable("TXT_REPAIR_OPTIONS", $this->lng->txt("repair_options"));
            $this->tpl->setVariable("TXT_OUTPUT_OPTIONS", $this->lng->txt("output_options"));
            $this->tpl->setVariable("TXT_SCAN", $this->lng->txt("scan"));
            $this->tpl->setVariable("TXT_SCAN_DESC", $this->lng->txt("scan_desc"));
            $this->tpl->setVariable("TXT_DUMP_TREE", $this->lng->txt("dump_tree"));
            $this->tpl->setVariable("TXT_DUMP_TREE_DESC", $this->lng->txt("dump_tree_desc"));
            $this->tpl->setVariable("TXT_CLEAN", $this->lng->txt("clean"));
            $this->tpl->setVariable("TXT_CLEAN_DESC", $this->lng->txt("clean_desc"));
            $this->tpl->setVariable("TXT_RESTORE", $this->lng->txt("restore_missing"));
            $this->tpl->setVariable("TXT_RESTORE_DESC", $this->lng->txt("restore_missing_desc"));
            $this->tpl->setVariable("TXT_PURGE", $this->lng->txt("purge_missing"));
            $this->tpl->setVariable("TXT_PURGE_DESC", $this->lng->txt("purge_missing_desc"));
            $this->tpl->setVariable("TXT_RESTORE_TRASH", $this->lng->txt("restore_trash"));
            $this->tpl->setVariable("TXT_RESTORE_TRASH_DESC", $this->lng->txt("restore_trash_desc"));
            $this->tpl->setVariable("TXT_PURGE_TRASH", $this->lng->txt("purge_trash"));
            $this->tpl->setVariable("TXT_PURGE_TRASH_DESC", $this->lng->txt("purge_trash_desc"));
            $this->tpl->setVariable("TXT_COUNT_LIMIT", $this->lng->txt("purge_count_limit"));
            $this->tpl->setVariable("TXT_COUNT_LIMIT_DESC", $this->lng->txt("purge_count_limit_desc"));
            $this->tpl->setVariable("COUNT_LIMIT_VALUE", $ilUser->getPref("systemcheck_count_limit"));
            $this->tpl->setVariable("TXT_AGE_LIMIT", $this->lng->txt("purge_age_limit"));
            $this->tpl->setVariable("TXT_AGE_LIMIT_DESC", $this->lng->txt("purge_age_limit_desc"));
            $this->tpl->setVariable("AGE_LIMIT_VALUE", $ilUser->getPref("systemcheck_age_limit"));
            $this->tpl->setVariable("TXT_TYPE_LIMIT", $this->lng->txt("purge_type_limit"));
            $this->tpl->setVariable("TXT_TYPE_LIMIT_DESC", $this->lng->txt("purge_type_limit_desc"));

            if ($ilUser->getPref('systemcheck_mode_scan')) {
                $this->tpl->touchBlock('mode_scan_checked');
            }
            if ($ilUser->getPref('systemcheck_mode_dump_tree')) {
                $this->tpl->touchBlock('mode_dump_tree_checked');
            }
            if ($ilUser->getPref('systemcheck_mode_clean')) {
                $this->tpl->touchBlock('mode_clean_checked');
            }
            if ($ilUser->getPref('systemcheck_mode_restore')) {
                $this->tpl->touchBlock('mode_restore_checked');
                $this->tpl->touchBlock('mode_purge_disabled');
            } elseif ($ilUser->getPref('systemcheck_mode_purge')) {
                $this->tpl->touchBlock('mode_purge_checked');
                $this->tpl->touchBlock('mode_restore_disabled');
            }
            if ($ilUser->getPref('systemcheck_mode_restore_trash')) {
                $this->tpl->touchBlock('mode_restore_trash_checked');
                $this->tpl->touchBlock('mode_purge_trash_disabled');
            } elseif ($ilUser->getPref('systemcheck_mode_purge_trash')) {
                $this->tpl->touchBlock('mode_purge_trash_checked');
                $this->tpl->touchBlock('mode_restore_trash_disabled');
            }
            if ($ilUser->getPref('systemcheck_log_scan')) {
                $this->tpl->touchBlock('log_scan_checked');
            }
            
            
            // #9520 - restrict to types which can be found in tree
            
            $obj_types_in_tree = array();
            
            $ilDB = $this->db;
            $set = $ilDB->query('SELECT type FROM object_data od' .
                ' JOIN object_reference ref ON (od.obj_id = ref.obj_id)' .
                ' JOIN tree ON (tree.child = ref.ref_id)' .
                ' WHERE tree.tree < 1' .
                ' GROUP BY type');
            while ($row = $ilDB->fetchAssoc($set)) {
                $obj_types_in_tree[] = $row['type'];
            }
            
            $types = $objDefinition->getAllObjects();
            $ts = array("" => "");
            foreach ($types as $t) {
                if ($t != "" && !$objDefinition->isSystemObject($t) && $t != "root" &&
                    in_array($t, $obj_types_in_tree)) {
                    if ($objDefinition->isPlugin($t)) {
                        $pl = ilObjectPlugin::getPluginObjectByType($t);
                        $ts[$t] = $pl->txt("obj_" . $t);
                    } else {
                        $ts[$t] = $this->lng->txt("obj_" . $t);
                    }
                }
            }
            asort($ts);
            $this->tpl->setVariable(
                "TYPE_LIMIT_CHOICE",
                ilUtil::formSelect(
                    $ilUser->getPref("systemcheck_type_limit"),
                    'type_limit',
                    $ts,
                    false,
                    true
                )
            );
            $this->tpl->setVariable("TXT_LOG_SCAN", $this->lng->txt("log_scan"));
            $this->tpl->setVariable("TXT_LOG_SCAN_DESC", $this->lng->txt("log_scan_desc"));
            $this->tpl->setVariable("TXT_SUBMIT", $this->lng->txt("start_scan"));

            $this->tpl->setVariable("TXT_SAVE", $this->lng->txt("save_params_for_cron"));
            
            include_once("./Services/Form/classes/class.ilPropertyFormGUI.php");
            
            $cron_form = new ilPropertyFormGUI();
            $cron_form->setFormAction($this->ctrl->getFormAction($this));
            $cron_form->setTitle($this->lng->txt('systemcheck_cronform'));
            
            $radio_group = new ilRadioGroupInputGUI($this->lng->txt('systemcheck_cron'), 'cronjob');
            $radio_group->setValue($ilSetting->get('systemcheck_cron'));
    
            $radio_opt = new ilRadioOption($this->lng->txt('disabled'), 0);
            $radio_group->addOption($radio_opt);
    
            $radio_opt = new ilRadioOption($this->lng->txt('enabled'), 1);
            $radio_group->addOption($radio_opt);
                
            $cron_form->addItem($radio_group);
            
            $cron_form->addCommandButton('saveCheckCron', $this->lng->txt('save'));
            
            $this->tpl->setVariable('CRON_FORM', $cron_form->getHTML());
        }
    }
    
    private function saveCheckParamsObject()
    {
        $this->writeCheckParams();
        unset($_POST['mode']);
        return $this->checkObject();
    }
    
    private function writeCheckParams()
    {
        include_once "./Services/Repository/classes/class.ilValidator.php";
        $validator = new ilValidator();
        $modes = $validator->getPossibleModes();
        
        $prefs = array();
        foreach ($modes as $mode) {
            if (isset($_POST['mode'][$mode])) {
                $value = (int) $_POST['mode'][$mode];
            } else {
                $value = 0;
            }
            $prefs[ 'systemcheck_mode_' . $mode ] = $value;
        }
        
        if (isset($_POST['log_scan'])) {
            $value = (int) $_POST['log_scan'];
        } else {
            $value = 0;
        }
        $prefs['systemcheck_log_scan'] = $value;
        
        $ilUser = $this->user;
        foreach ($prefs as $key => $val) {
            $ilUser->writePref($key, $val);
        }
    }
    
    private function saveCheckCronObject()
    {
        $ilSetting = $this->settings;
        
        $systemcheck_cron = ($_POST['cronjob'] ? 1 : 0);
        $ilSetting->set('systemcheck_cron', $systemcheck_cron);
        
        unset($_POST['mode']);
        return $this->checkObject();
    }

    /**
    * edit header title form
    *
    * @access	private
    */
    public function changeHeaderTitleObject()
    {
        $rbacsystem = $this->rbacsystem;
        $styleDefinition = $this->style_definition;

        $this->tpl->addBlockFile(
            "ADM_CONTENT",
            "adm_content",
            "tpl.header_title_edit.html",
            "Modules/SystemFolder"
        );

        $array_push = true;

        if ($_SESSION["error_post_vars"]) {
            $_SESSION["translation_post"] = $_SESSION["error_post_vars"];
            $_GET["mode"] = "session";
            $array_push = false;
        }

        // load from db if edit category is called the first time
        if (($_GET["mode"] != "session")) {
            $data = $this->object->getHeaderTitleTranslations();
            $_SESSION["translation_post"] = $data;
            $array_push = false;
        }	// remove a translation from session
        elseif ($_GET["entry"] != 0) {
            array_splice($_SESSION["translation_post"]["Fobject"], $_GET["entry"], 1, array());

            if ($_GET["entry"] == $_SESSION["translation_post"]["default_language"]) {
                $_SESSION["translation_post"]["default_language"] = "";
            }
        }

        $data = $_SESSION["translation_post"];

        // add additional translation form
        if (!$_GET["entry"] and $array_push) {
            $count = array_push($data["Fobject"], array("title" => "","desc" => ""));
        } else {
            $count = count($data["Fobject"]);
        }

        // stripslashes in form?
        $strip = isset($_SESSION["translation_post"]) ? true : false;

        foreach ($data["Fobject"] as $key => $val) {
            // add translation button
            if ($key == $count - 1) {
                $this->tpl->setCurrentBlock("addTranslation");
                $this->tpl->setVariable("TXT_ADD_TRANSLATION", $this->lng->txt("add_translation") . " >>");
                $this->tpl->parseCurrentBlock();
            }

            // remove translation button
            if ($key != 0) {
                $this->tpl->setCurrentBlock("removeTranslation");
                $this->tpl->setVariable("TXT_REMOVE_TRANSLATION", $this->lng->txt("remove_translation"));
                $this->ctrl->setParameter($this, "entry", $key);
                $this->ctrl->setParameter($this, "mode", "edit");
                $this->tpl->setVariable(
                    "LINK_REMOVE_TRANSLATION",
                    $this->ctrl->getLinkTarget($this, "removeTranslation")
                );
                $this->tpl->parseCurrentBlock();
            }

            // lang selection
            $this->tpl->addBlockFile(
                "SEL_LANGUAGE",
                "sel_language",
                "tpl.lang_selection.html",
                "Services/MetaData"
            );
            $this->tpl->setVariable("SEL_NAME", "Fobject[" . $key . "][lang]");

            include_once('Services/MetaData/classes/class.ilMDLanguageItem.php');

            $languages = ilMDLanguageItem::_getLanguages();

            foreach ($languages as $code => $language) {
                $this->tpl->setCurrentBlock("lg_option");
                $this->tpl->setVariable("VAL_LG", $code);
                $this->tpl->setVariable("TXT_LG", $language);

                if ($code == $val["lang"]) {
                    $this->tpl->setVariable("SELECTED", "selected=\"selected\"");
                }

                $this->tpl->parseCurrentBlock();
            }

            // object data
            $this->tpl->setCurrentBlock("obj_form");

            if ($key == 0) {
                $this->tpl->setVariable("TXT_HEADER", $this->lng->txt("change_header_title"));
            } else {
                $this->tpl->setVariable("TXT_HEADER", $this->lng->txt("translation") . " " . $key);
            }

            if ($key == $data["default_language"]) {
                $this->tpl->setVariable("CHECKED", "checked=\"checked\"");
            }

            $this->tpl->setVariable("TXT_TITLE", $this->lng->txt("title"));
            $this->tpl->setVariable("TXT_DESC", $this->lng->txt("desc"));
            $this->tpl->setVariable("TXT_DEFAULT", $this->lng->txt("default"));
            $this->tpl->setVariable("TXT_LANGUAGE", $this->lng->txt("language"));
            $this->tpl->setVariable("TITLE", ilUtil::prepareFormOutput($val["title"], $strip));
            $this->tpl->setVariable("DESC", ilUtil::stripSlashes($val["desc"]));
            $this->tpl->setVariable("NUM", $key);
            $this->tpl->parseCurrentBlock();
        }

        // global
        $this->tpl->setCurrentBlock("adm_content");

        $this->tpl->setVariable("FORMACTION", $this->ctrl->getFormAction($this));
        $this->tpl->setVariable("TXT_CANCEL", $this->lng->txt("cancel"));
        $this->tpl->setVariable("TXT_SUBMIT", $this->lng->txt("save"));
        $this->tpl->setVariable("CMD_SUBMIT", "saveHeaderTitle");
        $this->tpl->setVariable("TXT_REQUIRED_FLD", $this->lng->txt("required_field"));
    }

    /**
    * save header title
    */
    public function saveHeaderTitleObject()
    {
        $ilErr = $this->error;

        $data = $_POST;

        // default language set?
        if (!isset($data["default_language"])) {
            $ilErr->raiseError($this->lng->txt("msg_no_default_language"), $ilErr->MESSAGE);
        }

        // prepare array fro further checks
        foreach ($data["Fobject"] as $key => $val) {
            $langs[$key] = $val["lang"];
        }

        $langs = array_count_values($langs);

        // all languages set?
        if (array_key_exists("", $langs)) {
            $ilErr->raiseError($this->lng->txt("msg_no_language_selected"), $ilErr->MESSAGE);
        }

        // no single language is selected more than once?
        if (array_sum($langs) > count($langs)) {
            $ilErr->raiseError($this->lng->txt("msg_multi_language_selected"), $ilErr->MESSAGE);
        }

        // copy default translation to variable for object data entry
        $_POST["Fobject"]["title"] = $_POST["Fobject"][$_POST["default_language"]]["title"];
        $_POST["Fobject"]["desc"] = $_POST["Fobject"][$_POST["default_language"]]["desc"];

        // first delete all translation entries...
        $this->object->removeHeaderTitleTranslations();

        // ...and write new translations to object_translation
        foreach ($data["Fobject"] as $key => $val) {
            if ($key == $data["default_language"]) {
                $default = 1;
            } else {
                $default = 0;
            }

            $this->object->addHeaderTitleTranslation(ilUtil::stripSlashes($val["title"]), ilUtil::stripSlashes($val["desc"]), $val["lang"], $default);
        }

        ilUtil::sendSuccess($this->lng->txt("msg_obj_modified"), true);

        $this->ctrl->redirect($this);
    }

    public function cancelObject()
    {
        $this->ctrl->redirect($this, "view");
    }

    /**
    * adds a translation form & save post vars to session
    *
    * @access	public
    */
    public function addHeaderTitleTranslationObject()
    {
        $_SESSION["translation_post"] = $_POST;

        $this->ctrl->setParameter($this, "mode", "session");
        $this->ctrl->setParameter($this, "entry", "0");
        $this->ctrl->redirect($this, "changeHeaderTitle");
    }

    /**
    * removes a translation form & save post vars to session
    *
    * @access	public
    */
    public function removeTranslationObject()
    {
        $this->ctrl->setParameter($this, "entry", $_GET["entry"]);
        $this->ctrl->setParameter($this, "mode", "session");
        $this->ctrl->redirect($this, "changeHeaderTitle");
    }


    public function startValidator($a_mode, $a_log)
    {
        $rbacsystem = $this->rbacsystem;
        $ilErr = $this->error;

        if (!$rbacsystem->checkAccess("visible,read", $this->object->getRefId())) {
            $ilErr->raiseError($this->lng->txt("permission_denied"), $ilErr->MESSAGE);
        }

        $logging = ($a_log) ? true : false;
        include_once "./Services/Repository/classes/class.ilValidator.php";
        $validator = new ilValidator($logging);
        $validator->setMode("all", false);

        $modes = array();
        foreach ($a_mode as $mode => $value) {
            $validator->setMode($mode, (bool) $value);
            $modes[] = $mode . '=' . $value;
        }

        $scan_log = $validator->validate();

        $mode = $this->lng->txt("scan_modes") . ": " . implode(', ', $modes);

        // output
        $this->tpl->addBlockFile(
            "ADM_CONTENT",
            "adm_content",
            "tpl.adm_scan.html",
            "Modules/SystemFolder"
        );

        $this->tpl->setVariable("FORMACTION", $this->ctrl->getFormAction($this));
        $this->tpl->setVariable("TXT_TITLE", $this->lng->txt("scanning_system"));
        $this->tpl->setVariable("COLSPAN", 3);
        $this->tpl->setVariable("TXT_SCAN_LOG", $scan_log);
        $this->tpl->setVariable("TXT_MODE", $mode);

        if ($logging === true) {
            $this->tpl->setVariable("TXT_VIEW_LOG", $this->lng->txt("view_log"));
        }

        $this->tpl->setVariable("TXT_DONE", $this->lng->txt("done"));

        $validator->writeScanLogLine($mode);
    }

    public function viewScanLog()
    {
        include_once "./Services/Repository/classes/class.ilValidator.php";
        $validator = new IlValidator();
        $scan_log = &$validator->readScanLog();

        if (is_array($scan_log)) {
            $scan_log = '<pre>' . implode("", $scan_log) . '</pre>';
            $this->tpl->setVariable("ADM_CONTENT", $scan_log);
        } else {
            $scan_log = "no scanlog found.";
        }

        // output
        $this->tpl->addBlockFile(
            "ADM_CONTENT",
            "adm_content",
            "tpl.adm_scan.html",
            "Modules/SystemFolder"
        );
        $this->tpl->setVariable("TXT_TITLE", $this->lng->txt("scan_details"));
        $this->tpl->setVariable("COLSPAN", 3);
        $this->tpl->setVariable("TXT_SCAN_LOG", $scan_log);
        $this->tpl->setVariable("TXT_DONE", $this->lng->txt("done"));
    }


    /**
     * Benchmark settings
     */
    public function benchmarkObject()
    {
        $rbacsystem = $this->rbacsystem;
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;
        $ilSetting = $this->settings;
        $tpl = $this->tpl;
        $ilErr = $this->error;

        if (!$rbacsystem->checkAccess("visible,read", $this->object->getRefId())) {
            $ilErr->raiseError($this->lng->txt("permission_denied"), $ilErr->MESSAGE);
        }

        $this->benchmarkSubTabs("settings");

        include_once("Services/Form/classes/class.ilPropertyFormGUI.php");
        $this->form = new ilPropertyFormGUI();

        // Activate DB Benchmark
        $cb = new ilCheckboxInputGUI($lng->txt("adm_activate_db_benchmark"), "enable_db_bench");
        $cb->setChecked($ilSetting->get("enable_db_bench"));
        $cb->setInfo($lng->txt("adm_activate_db_benchmark_desc"));
        $this->form->addItem($cb);

        // DB Benchmark User
        $ti = new ilTextInputGUI($lng->txt("adm_db_benchmark_user"), "db_bench_user");
        $ti->setValue($ilSetting->get("db_bench_user"));
        $ti->setInfo($lng->txt("adm_db_benchmark_user_desc"));
        $this->form->addItem($ti);

        $this->form->addCommandButton("saveBenchSettings", $lng->txt("save"));

        $this->form->setTitle($lng->txt("adm_db_benchmark"));
        $this->form->setFormAction($ilCtrl->getFormAction($this));

        $tpl->setContent($this->form->getHTML());
    }

    /**
     * Show db benchmark results
     */
    public function showDbBenchChronologicalObject()
    {
        $this->benchmarkSubTabs("chronological");
        $this->showDbBenchResults("chronological");
    }

    /**
     * Show db benchmark results
     */
    public function showDbBenchSlowestFirstObject()
    {
        $this->benchmarkSubTabs("slowest_first");
        $this->showDbBenchResults("slowest_first");
    }

    /**
     * Show db benchmark results
     */
    public function showDbBenchSortedBySqlObject()
    {
        $this->benchmarkSubTabs("sorted_by_sql");
        $this->showDbBenchResults("sorted_by_sql");
    }

    /**
     * Show db benchmark results
     */
    public function showDbBenchByFirstTableObject()
    {
        $this->benchmarkSubTabs("by_first_table");
        $this->showDbBenchResults("by_first_table");
    }

    /**
     * Show Db Benchmark Results
     *
     * @param	string		mode
     */
    public function showDbBenchResults($a_mode)
    {
        $tpl = $this->tpl;

        $ilBench = $this->bench;
        $rec = $ilBench->getDbBenchRecords();

        include_once("./Modules/SystemFolder/classes/class.ilBenchmarkTableGUI.php");
        $table = new ilBenchmarkTableGUI($this, "benchmark", $rec, $a_mode);
        $tpl->setContent($table->getHTML());
    }

    /**
     * Benchmark sub tabs
     *
     * @param
     * @return
     */
    public function benchmarkSubTabs($a_current)
    {
        $ilTabs = $this->tabs;
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;

        $ilBench = $this->bench;
        $ilTabs->activateTab("benchmarks"); // #18083

        $ilTabs->addSubtab(
            "settings",
            $lng->txt("settings"),
            $ilCtrl->getLinkTarget($this, "benchmark")
        );

        $rec = $ilBench->getDbBenchRecords();
        if (count($rec) > 0) {
            $ilTabs->addSubtab(
                "chronological",
                $lng->txt("adm_db_bench_chronological"),
                $ilCtrl->getLinkTarget($this, "showDbBenchChronological")
            );
            $ilTabs->addSubtab(
                "slowest_first",
                $lng->txt("adm_db_bench_slowest_first"),
                $ilCtrl->getLinkTarget($this, "showDbBenchSlowestFirst")
            );
            $ilTabs->addSubtab(
                "sorted_by_sql",
                $lng->txt("adm_db_bench_sorted_by_sql"),
                $ilCtrl->getLinkTarget($this, "showDbBenchSortedBySql")
            );
            $ilTabs->addSubtab(
                "by_first_table",
                $lng->txt("adm_db_bench_by_first_table"),
                $ilCtrl->getLinkTarget($this, "showDbBenchByFirstTable")
            );
        }

        $ilTabs->activateSubTab($a_current);
    }


    /**
     * Save benchmark settings
     */
    public function saveBenchSettingsObject()
    {
        $ilBench = $this->bench;
        if ($_POST["enable_db_bench"]) {
            $ilBench->enableDbBench(true, ilUtil::stripSlashes($_POST["db_bench_user"]));
        } else {
            $ilBench->enableDbBench(false);
        }

        ilUtil::sendSuccess($this->lng->txt("msg_obj_modified"), true);

        $this->ctrl->redirect($this, "benchmark");
    }


    /**
    * save benchmark settings
    */
    public function switchBenchModuleObject()
    {
        $this->ctrl->setParameter($this, 'cur_mod', $_POST['module']);
        $this->ctrl->redirect($this, "benchmark");
    }


    /**
    * delete all benchmark records
    */
    public function clearBenchObject()
    {
        $ilBench = $this->bench;
        $ilBench->clearData();
        $this->saveBenchSettingsObject();
    }

    // get tabs
    public function getAdminTabs()
    {
        $rbacsystem = $this->rbacsystem;
        $ilHelp = $this->help;
        
        //		$ilHelp->setScreenIdComponent($this->object->getType());

        $this->ctrl->setParameter($this, "ref_id", $this->object->getRefId());

        // general settings
        if ($rbacsystem->checkAccess("write", $this->object->getRefId())) {
            $this->tabs_gui->addTarget(
                "general_settings",
                $this->ctrl->getLinkTarget($this, "showBasicSettings"),
                array("showBasicSettings", "saveBasicSettings"),
                get_class($this)
            );
        }

        // server info
        if ($rbacsystem->checkAccess("visible,read", $this->object->getRefId())) {
            $this->tabs_gui->addTarget(
                "server",
                $this->ctrl->getLinkTarget($this, "showServerInfo"),
                array("showServerInfo", "view"),
                get_class($this)
            );
        }

        if ($rbacsystem->checkAccess("write", $this->object->getRefId())) {
            $this->tabs_gui->addTarget(
                "cron_jobs",
                $this->ctrl->getLinkTargetByClass("ilCronManagerGUI", ""),
                "",
                get_class($this)
            );

            //			$tabs_gui->addTarget("system_check",
            //				$this->ctrl->getLinkTarget($this, "check"), array("check","viewScanLog","saveCheckParams","saveCheckCron"), get_class($this));

            $this->tabs_gui->addTarget(
                "benchmarks",
                $this->ctrl->getLinkTarget($this, "benchmark"),
                "benchmark",
                get_class($this)
            );
        }

        if ($rbacsystem->checkAccess('edit_permission', $this->object->getRefId())) {
            $this->tabs_gui->addTarget(
                "perm_settings",
                $this->ctrl->getLinkTargetByClass(array(get_class($this),'ilpermissiongui'), "perm"),
                array("perm","info","owner"),
                'ilpermissiongui'
            );
        }
    }
    
    /**
    * Show PHP Information
    */
    public function showPHPInfoObject()
    {
        phpinfo();
        exit;
    }

    //
    //
    // Server Info
    //
    //

    // TODO: remove this subtabs
    /**
    * Set sub tabs for server info
    */
    public function setServerInfoSubTabs($a_activate)
    {
        $ilTabs = $this->tabs;
        $ilCtrl = $this->ctrl;
        $rbacsystem = $this->rbacsystem;

        $ilTabs->addSubTabTarget("installation_status", $ilCtrl->getLinkTarget($this, "showServerInstallationStatus"));

        $ilTabs->addSubTabTarget("server_data", $ilCtrl->getLinkTarget($this, "showServerInfo"));

        if ($rbacsystem->checkAccess("write", $this->object->getRefId())) {
            $ilTabs->addSubTabTarget("java_server", $ilCtrl->getLinkTarget($this, "showJavaServer"));
        }
        
        $ilTabs->setSubTabActive($a_activate);
        $ilTabs->setTabActive("server");
    }

    /**
    * Show server info
    */
    public function showServerInfoObject()
    {
        /**
         * @var $ilToolbar ilToolbarGUI
         * @var $lng       ilLanguage
         * @var $ilCtrl    ilCtrl
         * @var $tpl       ilTemplate
         */
        $tpl = $this->tpl;
        $ilCtrl = $this->ctrl;
        $ilToolbar = $this->toolbar;
        $lng = $this->lng;

        require_once 'Services/UIComponent/Button/classes/class.ilLinkButton.php';
        $button = ilLinkButton::getInstance();
        $button->setCaption('vc_information');
        $button->setUrl($this->ctrl->getLinkTarget($this, 'showVcsInformation'));
        $ilToolbar->addButtonInstance($button);

        $this->initServerInfoForm();
        // TODO: remove sub tabs
//        $this->tabs->setTabActive("server");
        $this->setServerInfoSubTabs("server_data");
        
        $btpl = new ilTemplate("tpl.server_data.html", true, true, "Modules/SystemFolder");
        $btpl->setVariable("FORM", $this->form->getHTML());
        $btpl->setVariable("PHP_INFO_TARGET", $ilCtrl->getLinkTarget($this, "showPHPInfo"));
        $tpl->setContent($btpl->get());
    }
    
    /**
    * Init server info form.
    *
    * @param        int        $a_mode        Edit Mode
    */
    public function initServerInfoForm()
    {
        $lng = $this->lng;
        $ilClientIniFile = $this->client_ini;
        $ilSetting = $this->settings;
        
        include_once("Services/Form/classes/class.ilPropertyFormGUI.php");
        $this->form = new ilPropertyFormGUI();
        
        // installation name
        $ne = new ilNonEditableValueGUI($lng->txt("inst_name"), "");
        $ne->setValue($ilClientIniFile->readVariable("client", "name"));
        $ne->setInfo($ilClientIniFile->readVariable("client", "description"));
        $this->form->addItem($ne);

        // client id
        $ne = new ilNonEditableValueGUI($lng->txt("client_id"), "");
        $ne->setValue(CLIENT_ID);
        $this->form->addItem($ne);
        
        // installation id
        $ne = new ilNonEditableValueGUI($lng->txt("inst_id"), "");
        $ne->setValue($ilSetting->get("inst_id"));
        $this->form->addItem($ne);
        
        // database version
        $ne = new ilNonEditableValueGUI($lng->txt("db_version"), "");
        $ne->setValue($ilSetting->get("db_version"));
        
        $this->form->addItem($ne);
        
        // ilias version
        $ne = new ilNonEditableValueGUI($lng->txt("ilias_version"), "");
        $ne->setValue($ilSetting->get("ilias_version"));
        $this->form->addItem($ne);

        // host
        $ne = new ilNonEditableValueGUI($lng->txt("host"), "");
        $ne->setValue($_SERVER["SERVER_NAME"]);
        $this->form->addItem($ne);
        
        // ip & port
        $ne = new ilNonEditableValueGUI($lng->txt("ip_address") . " & " . $this->lng->txt("port"), "");
        $ne->setValue($_SERVER["SERVER_ADDR"] . ":" . $_SERVER["SERVER_PORT"]);
        $this->form->addItem($ne);
        
        // server
        $ne = new ilNonEditableValueGUI($lng->txt("server_software"), "");
        $ne->setValue($_SERVER["SERVER_SOFTWARE"]);
        $this->form->addItem($ne);
        
        // http path
        $ne = new ilNonEditableValueGUI($lng->txt("http_path"), "");
        $ne->setValue(ILIAS_HTTP_PATH);
        $this->form->addItem($ne);
        
        // absolute path
        $ne = new ilNonEditableValueGUI($lng->txt("absolute_path"), "");
        $ne->setValue(ILIAS_ABSOLUTE_PATH);
        $this->form->addItem($ne);
        
        $not_set = $lng->txt("path_not_set");
        
        // convert
        $ne = new ilNonEditableValueGUI($lng->txt("path_to_convert"), "");
        $ne->setValue((PATH_TO_CONVERT) ? PATH_TO_CONVERT : $not_set);
        $this->form->addItem($ne);
        
        // zip
        $ne = new ilNonEditableValueGUI($lng->txt("path_to_zip"), "");
        $ne->setValue((PATH_TO_ZIP) ? PATH_TO_ZIP : $not_set);
        $this->form->addItem($ne);

        // unzip
        $ne = new ilNonEditableValueGUI($lng->txt("path_to_unzip"), "");
        $ne->setValue((PATH_TO_UNZIP) ? PATH_TO_UNZIP : $not_set);
        $this->form->addItem($ne);

        // java
        $ne = new ilNonEditableValueGUI($lng->txt("path_to_java"), "");
        $ne->setValue((PATH_TO_JAVA) ? PATH_TO_JAVA : $not_set);
        $this->form->addItem($ne);
        
        // mkisofs
        $ne = new ilNonEditableValueGUI($lng->txt("path_to_mkisofs"), "");
        $ne->setValue((PATH_TO_MKISOFS) ? PATH_TO_MKISOFS : $not_set);
        $this->form->addItem($ne);

        // latex
        $ne = new ilNonEditableValueGUI($lng->txt("url_to_latex"), "");
        $ne->setValue((URL_TO_LATEX) ? URL_TO_LATEX : $not_set);
        $this->form->addItem($ne);


        $this->form->setTitle($lng->txt("server_data"));
        $this->form->setFormAction($this->ctrl->getFormAction($this));
    }

    protected function showServerInstallationStatusObject() : void
    {
        $this->setServerInfoSubTabs("installation_status");
        $this->renderServerStatus();
    }

    protected function renderServerStatus() : void
    {
        global $DIC;
        $f = $DIC->ui()->factory();
        $r = $DIC->ui()->renderer();
        $refinery = $DIC->refinery();

        $metric = $this->getServerStatusInfo($refinery);
        $report = $metric->toUIReport($f, $this->lng->txt("installation_status"));

        $this->tpl->setContent($r->render($report));
    }

    protected function getServerStatusInfo(ILIAS\Refinery\Factory $refinery) : ILIAS\Setup\Metrics\Metric
    {
        $data = new Factory();
        $lng = new ilSetupLanguage('en');
        $interface_finder = new ImplementationOfInterfaceFinder();
        $plugin_raw_reader = new ilPluginRawReader();

        $agent_finder = new ImplementationOfAgentFinder(
            $refinery,
            $data,
            $lng,
            $interface_finder,
            $plugin_raw_reader,
            []
        );

        $st = new StatusCommand($agent_finder);

        return $st->getMetrics($agent_finder->getAgents());
    }
    
    //
    //
    // General Settings
    //
    //
    
    /**
    * Set sub tabs for general settings
    */
    public function setGeneralSettingsSubTabs($a_activate)
    {
        $ilTabs = $this->tabs;
        $ilCtrl = $this->ctrl;
        
        $ilTabs->addSubTabTarget("basic_settings", $ilCtrl->getLinkTarget($this, "showBasicSettings"));
        $ilTabs->addSubTabTarget("header_title", $ilCtrl->getLinkTarget($this, "showHeaderTitle"));
        $ilTabs->addSubTabTarget("contact_data", $ilCtrl->getLinkTarget($this, "showContactInformation"));
        $ilTabs->addSubTabTarget("adm_imprint", $ilCtrl->getLinkTargetByClass("ilimprintgui", "preview"));
    
        $ilTabs->setSubTabActive($a_activate);
        $ilTabs->setTabActive("general_settings");
    }

    //
    //
    // Basic Settings
    //
    //
    
    /**
    * Show basic settings
    */
    public function showBasicSettingsObject()
    {
        $tpl = $this->tpl;

        $this->initBasicSettingsForm();
        $this->setGeneralSettingsSubTabs("basic_settings");
        
        $tpl->setContent($this->form->getHTML());
    }

    
    /**
    * Init basic settings form.
    */
    public function initBasicSettingsForm()
    {
        /**
         * @var $lng ilLanguage
         * @var $ilSetting ilSetting
         */
        $lng = $this->lng;
        $ilSetting = $this->settings;

        include_once("Services/Form/classes/class.ilPropertyFormGUI.php");
        $this->form = new ilPropertyFormGUI();
        $lng->loadLanguageModule("pd");
        
        // installation short title
        $ti = new ilTextInputGUI($this->lng->txt("short_inst_name"), "short_inst_name");
        $ti->setMaxLength(200);
        $ti->setSize(40);
        $ti->setValue($ilSetting->get("short_inst_name"));
        $ti->setInfo($this->lng->txt("short_inst_name_info"));
        $this->form->addItem($ti);

        
        $cb = new ilCheckboxInputGUI($this->lng->txt("pub_section"), "pub_section");
        $cb->setInfo($lng->txt("pub_section_info"));
        if (ilPublicSectionSettings::getInstance()->isEnabled()) {
            $cb->setChecked(true);
        }
        $this->form->addItem($cb);
        
        $this->lng->loadLanguageModule('administration');
        $domains = new ilTextInputGUI($this->lng->txt('adm_pub_section_domain_filter'), 'public_section_domains');
        $domains->setInfo($this->lng->txt('adm_pub_section_domain_filter_info'));
        $domains->setMulti(true);
        $domains->setValue(current(ilPublicSectionSettings::getInstance()->getDomains()));
        $domains->setMultiValues(ilPublicSectionSettings::getInstance()->getDomains());
        
        $cb->addSubItem($domains);
        
                
        // Enable Global Profiles
        $cb_prop = new ilCheckboxInputGUI($lng->txt('pd_enable_user_publish'), 'enable_global_profiles');
        $cb_prop->setInfo($lng->txt('pd_enable_user_publish_info'));
        $cb_prop->setChecked($ilSetting->get('enable_global_profiles'));
        $cb->addSubItem($cb_prop);

        // search engine
        include_once('Services/PrivacySecurity/classes/class.ilRobotSettings.php');
        $robot_settings = ilRobotSettings::_getInstance();
        $cb2 = new ilCheckboxInputGUI($this->lng->txt("search_engine"), "open_google");
        $cb2->setInfo($this->lng->txt("enable_search_engine"));
        $this->form->addItem($cb2);

        if (!$robot_settings->checkRewrite()) {
            $cb2->setAlert($lng->txt("allow_override_alert"));
            $cb2->setChecked(false);
            $cb2->setDisabled(true);
        } else {
            if ($ilSetting->get("open_google")) {
                $cb2->setChecked(true);
            }
        }
        
        // locale
        $ti = new ilTextInputGUI($this->lng->txt("adm_locale"), "locale");
        $ti->setMaxLength(80);
        $ti->setSize(40);
        $ti->setInfo($this->lng->txt("adm_locale_info"));
        $ti->setValue($ilSetting->get("locale"));
        $this->form->addItem($ti);
        
        // save and cancel commands
        $this->form->addCommandButton("saveBasicSettings", $lng->txt("save"));
                    
        $this->form->setTitle($lng->txt("basic_settings"));
        $this->form->setFormAction($this->ctrl->getFormAction($this));
    }
    
    /**
    * Save basic settings form
    *
    */
    public function saveBasicSettingsObject()
    {
        $tpl = $this->tpl;
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;
        $ilSetting = $this->settings;
        $rbacsystem = $this->rbacsystem;
        $ilErr = $this->error;
    
        if (!$rbacsystem->checkAccess("write", $this->object->getRefId())) {
            $ilErr->raiseError($this->lng->txt("permission_denied"), $ilErr->MESSAGE);
        }

        $this->initBasicSettingsForm();
        if ($this->form->checkInput()) {
            $ilSetting->set("short_inst_name", $_POST["short_inst_name"]);
            
            $public_section = ilPublicSectionSettings::getInstance();
            $public_section->setEnabled($this->form->getInput('pub_section'));
            
            $domains = array();
            foreach ((array) $this->form->getInput('public_section_domains') as $domain) {
                if (strlen(trim($domain))) {
                    $domains[] = $domain;
                }
            }
            $public_section->setDomains($domains);
            $public_section->save();
            
            $global_profiles = ($_POST["pub_section"])
                ? (int) $_POST['enable_global_profiles']
                : 0;
            $ilSetting->set('enable_global_profiles', $global_profiles);
                                
            $ilSetting->set("open_google", $_POST["open_google"]);
            $ilSetting->set("locale", $_POST["locale"]);

            ilUtil::sendSuccess($lng->txt("msg_obj_modified"), true);
            $ilCtrl->redirect($this, "showBasicSettings");
        }
        $this->setGeneralSettingsSubTabs("basic_settings");
        $this->form->setValuesByPost();
        $tpl->setContent($this->form->getHtml());
    }
    
    //
    //
    // Header title
    //
    //

    /**
    * Show header title
    */
    public function showHeaderTitleObject($a_get_post_values = false)
    {
        $tpl = $this->tpl;
        
        $this->setGeneralSettingsSubTabs("header_title");
        include_once("./Services/Object/classes/class.ilObjectTranslationTableGUI.php");
        $table = new ilObjectTranslationTableGUI($this, "showHeaderTitle", false);
        if ($a_get_post_values) {
            $vals = array();
            foreach ($_POST["title"] as $k => $v) {
                $vals[] = array("title" => $v,
                    "desc" => $_POST["desc"][$k],
                    "lang" => $_POST["lang"][$k],
                    "default" => ($_POST["default"] == $k));
            }
            $table->setData($vals);
        } else {
            $data = $this->object->getHeaderTitleTranslations();
            if (is_array($data["Fobject"])) {
                foreach ($data["Fobject"] as $k => $v) {
                    if ($k == $data["default_language"]) {
                        $data["Fobject"][$k]["default"] = true;
                    } else {
                        $data["Fobject"][$k]["default"] = false;
                    }
                }
            } else {
                $data["Fobject"] = array();
            }
            $table->setData($data["Fobject"]);
        }
        $tpl->setContent($table->getHTML());
    }

    /**
    * Save header titles
    */
    public function saveHeaderTitlesObject()
    {
        $ilCtrl = $this->ctrl;
        $lng = $this->lng;
        $rbacsystem = $this->rbacsystem;
        $ilErr = $this->error;

        if (!$rbacsystem->checkAccess("write", $this->object->getRefId())) {
            $ilErr->raiseError($this->lng->txt("permission_denied"), $ilErr->MESSAGE);
        }
        
        //		var_dump($_POST);
        
        // default language set?
        if (!isset($_POST["default"]) && count($_POST["lang"]) > 0) {
            ilUtil::sendFailure($lng->txt("msg_no_default_language"));
            return $this->showHeaderTitleObject(true);
        }

        // all languages set?
        if (array_key_exists("", $_POST["lang"])) {
            ilUtil::sendFailure($lng->txt("msg_no_language_selected"));
            return $this->showHeaderTitleObject(true);
        }

        // no single language is selected more than once?
        if (count(array_unique($_POST["lang"])) < count($_POST["lang"])) {
            ilUtil::sendFailure($lng->txt("msg_multi_language_selected"));
            return $this->showHeaderTitleObject(true);
        }

        // save the stuff
        $this->object->removeHeaderTitleTranslations();
        foreach ($_POST["title"] as $k => $v) {
            $this->object->addHeaderTitleTranslation(
                ilUtil::stripSlashes($v),
                ilUtil::stripSlashes($_POST["desc"][$k]),
                ilUtil::stripSlashes($_POST["lang"][$k]),
                ($_POST["default"] == $k)
            );
        }
        
        ilUtil::sendSuccess($lng->txt("msg_obj_modified"), true);
        $ilCtrl->redirect($this, "showHeaderTitle");
    }
    
    /**
    * Add a header title
    */
    public function addHeaderTitleObject()
    {
        $ilCtrl = $this->ctrl;
        $lng = $this->lng;
        
        if (is_array($_POST["title"])) {
            foreach ($_POST["title"] as $k => $v) {
            }
        }
        $k++;
        $_POST["title"][$k] = "";
        $this->showHeaderTitleObject(true);
    }
    
    /**
    * Remove header titles
    */
    public function deleteHeaderTitlesObject()
    {
        $ilCtrl = $this->ctrl;
        $lng = $this->lng;
        //var_dump($_POST);
        foreach ($_POST["title"] as $k => $v) {
            if ($_POST["check"][$k]) {
                unset($_POST["title"][$k]);
                unset($_POST["desc"][$k]);
                unset($_POST["lang"][$k]);
                if ($k == $_POST["default"]) {
                    unset($_POST["default"]);
                }
            }
        }
        $this->saveHeaderTitlesObject();
    }
    
    
    //
    //
    // Cron Jobs
    //
    //
                                
    /*
     * OLD GLOBAL CRON JOB SWITCHES (ilSetting)
     *
     * cron_user_check => obsolete
     * cron_inactive_user_delete => obsolete
     * cron_inactivated_user_delete => obsolete
     * cron_link_check => obsolete
     * cron_web_resource_check => migrated
     * cron_lucene_index => obsolete
     * forum_notification => migrated
     * mail_notification => migrated
     * crsgrp_ntf => migrated
     * cron_upd_adrbook => migrated
     */
    
    public function jumpToCronJobsObject()
    {
        // #13010 - this is used for external settings
        $this->ctrl->redirectByClass("ilCronManagerGUI", "render");
    }
    
    
    //
    //
    // Contact Information
    //
    //
    
    /**
    * Show contact information
    */
    public function showContactInformationObject()
    {
        $tpl = $this->tpl;
        
        $this->initContactInformationForm();
        $this->setGeneralSettingsSubTabs("contact_data");
        $tpl->setContent($this->form->getHTML());
    }
    
    /**
    * Init contact information form.
    */
    public function initContactInformationForm()
    {
        $lng = $this->lng;
        $ilSetting = $this->settings;
        
        include_once("Services/Form/classes/class.ilPropertyFormGUI.php");
        $this->form = new ilPropertyFormGUI();
    
        // first name
        $ti = new ilTextInputGUI($this->lng->txt("firstname"), "admin_firstname");
        $ti->setMaxLength(64);
        $ti->setSize(40);
        $ti->setRequired(true);
        $ti->setValue($ilSetting->get("admin_firstname"));
        $this->form->addItem($ti);
        
        // last name
        $ti = new ilTextInputGUI($this->lng->txt("lastname"), "admin_lastname");
        $ti->setMaxLength(64);
        $ti->setSize(40);
        $ti->setRequired(true);
        $ti->setValue($ilSetting->get("admin_lastname"));
        $this->form->addItem($ti);
        
        // title
        $ti = new ilTextInputGUI($this->lng->txt("title"), "admin_title");
        $ti->setMaxLength(64);
        $ti->setSize(40);
        $ti->setValue($ilSetting->get("admin_title"));
        $this->form->addItem($ti);
        
        // position
        $ti = new ilTextInputGUI($this->lng->txt("position"), "admin_position");
        $ti->setMaxLength(64);
        $ti->setSize(40);
        $ti->setValue($ilSetting->get("admin_position"));
        $this->form->addItem($ti);
        
        // institution
        $ti = new ilTextInputGUI($this->lng->txt("institution"), "admin_institution");
        $ti->setMaxLength(200);
        $ti->setSize(40);
        $ti->setValue($ilSetting->get("admin_institution"));
        $this->form->addItem($ti);
        
        // street
        $ti = new ilTextInputGUI($this->lng->txt("street"), "admin_street");
        $ti->setMaxLength(64);
        $ti->setSize(40);
        //$ti->setRequired(true);
        $ti->setValue($ilSetting->get("admin_street"));
        $this->form->addItem($ti);
        
        // zip code
        $ti = new ilTextInputGUI($this->lng->txt("zipcode"), "admin_zipcode");
        $ti->setMaxLength(10);
        $ti->setSize(5);
        //$ti->setRequired(true);
        $ti->setValue($ilSetting->get("admin_zipcode"));
        $this->form->addItem($ti);
        
        // city
        $ti = new ilTextInputGUI($this->lng->txt("city"), "admin_city");
        $ti->setMaxLength(64);
        $ti->setSize(40);
        //$ti->setRequired(true);
        $ti->setValue($ilSetting->get("admin_city"));
        $this->form->addItem($ti);
        
        // country
        $ti = new ilTextInputGUI($this->lng->txt("country"), "admin_country");
        $ti->setMaxLength(64);
        $ti->setSize(40);
        //$ti->setRequired(true);
        $ti->setValue($ilSetting->get("admin_country"));
        $this->form->addItem($ti);
        
        // phone
        $ti = new ilTextInputGUI($this->lng->txt("phone"), "admin_phone");
        $ti->setMaxLength(64);
        $ti->setSize(40);
        //$ti->setRequired(true);
        $ti->setValue($ilSetting->get("admin_phone"));
        $this->form->addItem($ti);
        
        // email
        $ti = new ilEmailInputGUI($this->lng->txt("email"), "admin_email");
        $ti->setMaxLength(64);
        $ti->setSize(40);
        $ti->setRequired(true);
        $ti->allowRFC822(true);
        $ti->setValue($ilSetting->get("admin_email"));
        $this->form->addItem($ti);
        
        // feedback recipient
        /* currently used in:
        - footer
        - terms of service: no document found message
        */
        /*$ti = new ilEmailInputGUI($this->lng->txt("feedback_recipient"), "feedback_recipient");
        $ti->setInfo(sprintf($this->lng->txt("feedback_recipient_info"), $this->lng->txt("contact_sysadmin")));
        $ti->setMaxLength(64);
        $ti->setSize(40);
        $ti->setRequired(true);
        $ti->allowRFC822(true);
        $ti->setValue($ilSetting->get("feedback_recipient"));
        $this->form->addItem($ti);*/

        // System support contacts
        include_once("./Modules/SystemFolder/classes/class.ilSystemSupportContacts.php");
        $ti = new ilTextInputGUI($this->lng->txt("adm_support_contacts"), "adm_support_contacts");
        $ti->setMaxLength(500);
        $ti->setValue(ilSystemSupportContacts::getList());
        //$ti->setSize();
        $ti->setInfo($this->lng->txt("adm_support_contacts_info"));
        $this->form->addItem($ti);

        // Accessibility support contacts
        $ti = new ilTextInputGUI($this->lng->txt("adm_accessibility_contacts"), "accessibility_support_contacts");
        $ti->setMaxLength(500);
        $ti->setValue(ilAccessibilitySupportContacts::getList());
        //$ti->setSize();
        $ti->setInfo($this->lng->txt("adm_accessibility_contacts_info"));
        $this->form->addItem($ti);

        
        // error recipient
        /*$ti = new ilEmailInputGUI($this->lng->txt("error_recipient"), "error_recipient");
        $ti->setMaxLength(64);
        $ti->setSize(40);
        $ti->allowRFC822(true);
        $ti->setValue($ilSetting->get("error_recipient"));
        $this->form->addItem($ti);*/
        
        $this->form->addCommandButton("saveContactInformation", $lng->txt("save"));
                    
        $this->form->setTitle($lng->txt("contact_data"));
        $this->form->setFormAction($this->ctrl->getFormAction($this));
    }
    
    /**
    * Save contact information form
    *
    */
    public function saveContactInformationObject()
    {
        $tpl = $this->tpl;
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;
        $ilSetting = $this->settings;
        $rbacsystem = $this->rbacsystem;
        $ilErr = $this->error;
    
        if (!$rbacsystem->checkAccess("write", $this->object->getRefId())) {
            $ilErr->raiseError($this->lng->txt("permission_denied"), $ilErr->MESSAGE);
        }

        $this->initContactInformationForm();
        if ($this->form->checkInput()) {
            $fs = array("admin_firstname", "admin_lastname", "admin_title", "admin_position",
                "admin_institution", "admin_street", "admin_zipcode", "admin_city",
                "admin_country", "admin_phone", "admin_email");
            foreach ($fs as $f) {
                $ilSetting->set($f, $_POST[$f]);
            }

            // System support contacts
            include_once("./Modules/SystemFolder/classes/class.ilSystemSupportContacts.php");
            ilSystemSupportContacts::setList($_POST["adm_support_contacts"]);

            // Accessibility support contacts
            ilAccessibilitySupportContacts::setList($_POST["accessibility_support_contacts"]);

            ilUtil::sendSuccess($lng->txt("msg_obj_modified"), true);
            $ilCtrl->redirect($this, "showContactInformation");
        } else {
            $this->setGeneralSettingsSubTabs("contact_data");
            $this->form->setValuesByPost();
            $tpl->setContent($this->form->getHtml());
        }
    }
    
    //
    //
    // Java Server
    //
    //

    /**
    * Show Java Server Settings
    */
    public function showJavaServerObject()
    {
        $tpl = $this->tpl;
        
        $tpl->addBlockFile('ADM_CONTENT', 'adm_content', 'tpl.java_settings.html', 'Modules/SystemFolder');
        
        $GLOBALS['lng']->loadLanguageModule('search');
        
        $this->initJavaServerForm();
        $this->setServerInfoSubTabs("java_server");
        $tpl->setVariable('SETTINGS_TABLE', $this->form->getHTML());
    }
    
    /**
     * Create a server ini file
     * @return
     */
    public function createJavaServerIniObject()
    {
        $this->setGeneralSettingsSubTabs('java_server');
        $this->tpl->setContent($this->form->getHTML());
    }

    /**
    * Init java server form.
    */
    public function initJavaServerForm()
    {
        $lng = $this->lng;
        $ilSetting = $this->settings;
        
        include_once("Services/Form/classes/class.ilPropertyFormGUI.php");
        $this->form = new ilPropertyFormGUI();
        $this->form->setFormAction($this->ctrl->getFormAction($this, 'saveJavaServer'));

        // pdf fonts
        $pdf = new ilFormSectionHeaderGUI();
        $pdf->setTitle($this->lng->txt('rpc_pdf_generation'));
        $this->form->addItem($pdf);
        
        $pdf_font = new ilTextInputGUI($this->lng->txt('rpc_pdf_font'), 'rpc_pdf_font');
        $pdf_font->setInfo($this->lng->txt('rpc_pdf_font_info'));
        $pdf_font->setSize(64);
        $pdf_font->setMaxLength(1024);
        $pdf_font->setRequired(true);
        $pdf_font->setValue(
            $ilSetting->get('rpc_pdf_font', 'Helvetica, unifont')
        );
        $this->form->addItem($pdf_font);
    
        // save and cancel commands
        $this->form->addCommandButton("saveJavaServer", $lng->txt("save"));
    }
    
    /**
    * Save java server form
    *
    */
    public function saveJavaServerObject()
    {
        $tpl = $this->tpl;
        $lng = $this->lng;
        $ilCtrl = $this->ctrl;
        $ilSetting = $this->settings;
        $rbacsystem = $this->rbacsystem;
        $ilErr = $this->error;
    
        if (!$rbacsystem->checkAccess("write", $this->object->getRefId())) {
            $ilErr->raiseError($this->lng->txt("permission_denied"), $ilErr->MESSAGE);
        }

        $this->initJavaServerForm();
        if ($this->form->checkInput()) {
            $ilSetting->set('rpc_pdf_font', ilUtil::stripSlashes($_POST['rpc_pdf_font']));
            ilUtil::sendSuccess($lng->txt("msg_obj_modified"), true);
            $ilCtrl->redirect($this, "showJavaServer");
            
        // TODO check settings, ping server
        } else {
            $this->setGeneralSettingsSubTabs("java_server");
            $this->form->setValuesByPost();
            $tpl->setContent($this->form->getHtml());
        }
    }
    
    
    /**
     * goto target group
     */
    public static function _goto()
    {
        global $DIC;

        $ilAccess = $DIC->access();
        $ilErr = $DIC["ilErr"];
        $lng = $DIC->language();

        $a_target = SYSTEM_FOLDER_ID;

        if ($ilAccess->checkAccess("read", "", $a_target)) {
            ilUtil::redirect("ilias.php?baseClass=ilAdministrationGUI");
            exit;
        } else {
            if ($ilAccess->checkAccess("read", "", ROOT_FOLDER_ID)) {
                ilUtil::sendFailure(sprintf(
                    $lng->txt("msg_no_perm_read_item"),
                    ilObject::_lookupTitle(ilObject::_lookupObjId($a_target))
                ), true);
                ilObjectGUI::_gotoRepositoryRoot();
            }
        }
        $ilErr->raiseError($lng->txt("msg_no_perm_read"), $ilErr->FATAL);
    }

    /**
     *
     */
    protected function showVcsInformationObject() : void
    {
        $vcInfo = [];

        foreach ([new ilGitInformation()] as $vc) {
            $html = $vc->getInformationAsHtml();
            if ($html) {
                $vcInfo[] = $html;
            }
        }

        if ($vcInfo) {
            ilUtil::sendInfo(implode("<br />", $vcInfo));
        } else {
            ilUtil::sendInfo($this->lng->txt('vc_information_not_determined'));
        }

        $this->showServerInfoObject();
    }
}
