<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */


/**
 * Class ilObjLanguageFolderGUI
 *
 * @author	Stefan Meyer <meyer@leifos.com>
 * @version	$Id$
 *
 * @ilCtrl_Calls ilObjLanguageFolderGUI: ilPermissionGUI
 *
 * @extends	ilObject

 */

require_once "./Services/Language/classes/class.ilObjLanguage.php";
require_once "./Services/Object/classes/class.ilObjectGUI.php";

class ilObjLanguageFolderGUI extends ilObjectGUI
{
    /**
     * Constructor
     *
     * @access public
     */
    public function __construct($a_data, $a_id, $a_call_by_reference)
    {
        $this->type = "lngf";
        parent::__construct($a_data, $a_id, $a_call_by_reference, false);
        $_GET["sort_by"] = "language";
        $this->lng->loadLanguageModule('lng');
    }

    /**
     * show installed languages
     *
     * @access	public
     */
    public function viewObject()
    {
        global $DIC;

        if ($this->checkPermissionBool('write')) {
            // refresh
            $refresh = ilLinkButton::getInstance();
            $refresh->setUrl($this->ctrl->getLinkTarget($this, "confirmRefresh"));
            $refresh->setCaption("refresh_languages");
            $this->toolbar->addButtonInstance($refresh);

            // check languages
            $check = ilLinkButton::getInstance();
            $check->setUrl($this->ctrl->getLinkTarget($this, "checkLanguage"));
            $check->setCaption("check_languages");
            $this->toolbar->addButtonInstance($check);


            if (!$this->settings->get('lang_detection')) {
                $detect = ilLinkButton::getInstance();
                $detect->setUrl($this->ctrl->getLinkTarget($this, "enableLanguageDetection"));
                $detect->setCaption("lng_enable_language_detection");
                $this->toolbar->addButtonInstance($detect);
            } else {
                $detect = ilLinkButton::getInstance();
                $detect->setUrl($this->ctrl->getLinkTarget($this, "disableLanguageDetection"));
                $detect->setCaption("lng_disable_language_detection");
                $this->toolbar->addButtonInstance($detect);
            }
        }

        $ilClientIniFile = $DIC['ilClientIniFile'];
        if ($ilClientIniFile->variableExists('system', 'LANGUAGE_LOG')) {
            $download = ilLinkButton::getInstance();
            $download->setUrl($this->ctrl->getLinkTarget($this, "listDeprecated"));
            $download->setCaption("lng_download_deprecated");
            $this->toolbar->addButtonInstance($download);
        }

        $ltab = new ilLanguageTableGUI($this, "view", $this->object);
        $this->tpl->setContent($ltab->getHTML());
    }

    /**
     * install languages
     */
    public function installObject()
    {
        $this->checkPermission('write');
        $this->lng->loadLanguageModule("meta");

        if (!isset($_POST["id"])) {
            $this->ilias->raiseError($this->lng->txt("no_checkbox"), $this->ilias->error_obj->MESSAGE);
        }

        foreach ($_POST["id"] as $obj_id) {
            $langObj = new ilObjLanguage($obj_id);
            $key = $langObj->install();

            if ($key != "") {
                $lang_installed[] = $key;
            }

            unset($langObj);
        }

        if (isset($lang_installed)) {
            if (count($lang_installed) == 1) {
                $this->data = $this->lng->txt("meta_l_" . $lang_installed[0]) . " " . strtolower($this->lng->txt("installed")) . ".";
            } else {
                foreach ($lang_installed as $lang_key) {
                    $langnames[] = $this->lng->txt("meta_l_" . $lang_key);
                }
                $this->data = implode(", ", $langnames) . " " . strtolower($this->lng->txt("installed")) . ".";
            }
        } else {
            $this->data = $this->lng->txt("languages_already_installed");
        }

        $this->out();
    }


    /**
     * Install local language modifications.
     */
    public function installLocalObject()
    {
        $this->checkPermission('write');
        $this->lng->loadLanguageModule("meta");

        if (!isset($_POST["id"])) {
            $this->ilias->raiseError($this->lng->txt("no_checkbox"), $this->ilias->error_obj->MESSAGE);
        }

        foreach ($_POST["id"] as $obj_id) {
            $langObj = new ilObjLanguage($obj_id);
            $key = $langObj->install();

            if ($key != "") {
                $lang_installed[] = $key;
            }

            unset($langObj);

            $langObj = new ilObjLanguage($obj_id);
            $key = $langObj->install('local');

            if ($key != "") {
                $local_installed[] = $key;
            }

            unset($langObj);
        }

        if (isset($lang_installed)) {
            if (count($lang_installed) == 1) {
                $this->data = $this->lng->txt("meta_l_" . $lang_installed[0]) . " " . strtolower($this->lng->txt("installed")) . ".";
            } else {
                foreach ($lang_installed as $lang_key) {
                    $langnames[] = $this->lng->txt("meta_l_" . $lang_key);
                }
                $this->data = implode(", ", $langnames) . " " . strtolower($this->lng->txt("installed")) . ".";
            }
        }

        if (isset($local_installed)) {
            if (count($local_installed) == 1) {
                $this->data .= " " . $this->lng->txt("meta_l_" . $local_installed[0]) . " " . $this->lng->txt("local_language_file") . " " . strtolower($this->lng->txt("installed")) . ".";
            } else {
                foreach ($local_installed as $lang_key) {
                    $langnames[] = $this->lng->txt("meta_l_" . $lang_key);
                }
                $this->data .= " " . implode(", ", $langnames) . " " . $this->lng->txt("local_language_files") . " " . strtolower($this->lng->txt("installed")) . ".";
            }
        } else {
            $this->data .= " " . $this->lng->txt("local_languages_already_installed");
        }

        $this->out();
    }


    /**
     * uninstall language
     */
    public function uninstallObject()
    {
        $this->checkPermission('write');
        $this->lng->loadLanguageModule("meta");

        if (!isset($_POST["id"])) {
            $this->ilias->raiseError($this->lng->txt("no_checkbox"), $this->ilias->error_obj->MESSAGE);
        }

        // uninstall all selected languages
        foreach ($_POST["id"] as $obj_id) {
            $langObj = new ilObjLanguage($obj_id);
            if (!($sys_lang = $langObj->isSystemLanguage())) {
                if (!($usr_lang = $langObj->isUserLanguage())) {
                    $key = $langObj->uninstall();
                    if ($key != "") {
                        $lang_uninstalled[] = $key;
                    }
                }
            }
            unset($langObj);
        }

        // generate output message
        if (isset($lang_uninstalled)) {
            if (count($lang_uninstalled) == 1) {
                $this->data = $this->lng->txt("meta_l_" . $lang_uninstalled[0]) . " " . $this->lng->txt("uninstalled");
            } else {
                foreach ($lang_uninstalled as $lang_key) {
                    $langnames[] = $this->lng->txt("meta_l_" . $lang_key);
                }

                $this->data = implode(", ", $langnames) . " " . $this->lng->txt("uninstalled");
            }
        } elseif ($sys_lang) {
            $this->data = $this->lng->txt("cannot_uninstall_systemlanguage");
        } elseif ($usr_lang) {
            $this->data = $this->lng->txt("cannot_uninstall_language_in_use");
        } else {
            $this->data = $this->lng->txt("languages_already_uninstalled");
        }

        $this->out();
    }


    /**
     * Uninstall local changes in the database
     */
    public function uninstallChangesObject()
    {
        $this->checkPermission('write');

        $this->data = $this->lng->txt("selected_languages_updated");
        $this->lng->loadLanguageModule("meta");

        foreach ($_POST["id"] as $id) {
            $langObj = new ilObjLanguage($id, false);

            if ($langObj->isInstalled() == true) {
                if ($langObj->check()) {
                    $langObj->flush('all');
                    $langObj->insert();
                    $langObj->setTitle($langObj->getKey());
                    $langObj->setDescription('installed');
                    $langObj->update();
                }
                $this->data .= "<br />" . $this->lng->txt("meta_l_" . $langObj->getKey());
            }

            unset($langObj);
        }

        $this->out();
    }


    /**
     * update all installed languages
     */
    public function refreshObject()
    {
        $this->checkPermission('write');

        ilObjLanguage::refreshAll();
        $this->data = $this->lng->txt("languages_updated");
        $this->out();
    }


    /**
     * update selected languages
     */
    public function refreshSelectedObject()
    {
        $this->checkPermission('write');
        $this->data = $this->lng->txt("selected_languages_updated");
        $this->lng->loadLanguageModule("meta");

        $refreshed = array();
        foreach ($_POST["id"] as $id) {
            $langObj = new ilObjLanguage($id, false);
            if ($langObj->refresh()) {
                $refreshed[] = $langObj->getKey();
                $this->data .= "<br />" . $this->lng->txt("meta_l_" . $langObj->getKey());
            }
            unset($langObj);
        }

        ilObjLanguage::refreshPlugins($refreshed);
        $this->out();
    }


    /**
     * set user language
     */
    public function setUserLanguageObject()
    {
        global $DIC;
        $ilUser = $DIC->user();

        $this->checkPermission('write');
        $this->lng->loadLanguageModule("meta");

        require_once './Services/User/classes/class.ilObjUser.php';

        if (!isset($_POST["id"])) {
            $this->ilias->raiseError($this->lng->txt("no_checkbox"), $this->ilias->error_obj->MESSAGE);
        }

        if (count($_POST["id"]) != 1) {
            $this->ilias->raiseError($this->lng->txt("choose_only_one_language") . "<br/>" . $this->lng->txt("action_aborted"), $this->ilias->error_obj->MESSAGE);
        }

        $obj_id = $_POST["id"][0];

        $newUserLangObj = new ilObjLanguage($obj_id);

        if ($newUserLangObj->isUserLanguage()) {
            $this->ilias->raiseError($this->lng->txt("meta_l_" . $newUserLangObj->getKey()) . " " . $this->lng->txt("is_already_your") . " " . $this->lng->txt("user_language") . "<br/>" . $this->lng->txt("action_aborted"), $this->ilias->error_obj->MESSAGE);
        }

        if ($newUserLangObj->isInstalled() == false) {
            $this->ilias->raiseError($this->lng->txt("meta_l_" . $newUserLangObj->getKey()) . " " . $this->lng->txt("language_not_installed") . "<br/>" . $this->lng->txt("action_aborted"), $this->ilias->error_obj->MESSAGE);
        }


        $curUser = new ilObjUser($ilUser->getId());
        $curUser->setLanguage($newUserLangObj->getKey());
        $curUser->update();

        $this->data = $this->lng->txt("user_language") . " " . $this->lng->txt("changed_to") . " " . $this->lng->txt("meta_l_" . $newUserLangObj->getKey()) . ".";

        $this->out();
    }


    /**
     * set the system language
     */
    public function setSystemLanguageObject()
    {
        $this->checkPermission('write');
        $this->lng->loadLanguageModule("meta");

        if (!isset($_POST["id"])) {
            $this->ilias->raiseError($this->lng->txt("no_checkbox"), $this->ilias->error_obj->MESSAGE);
        }

        if (count($_POST["id"]) != 1) {
            $this->ilias->raiseError($this->lng->txt("choose_only_one_language") . "<br/>" . $this->lng->txt("action_aborted"), $this->ilias->error_obj->MESSAGE);
        }

        $obj_id = $_POST["id"][0];

        $newSysLangObj = new ilObjLanguage($obj_id);

        if ($newSysLangObj->isSystemLanguage()) {
            $this->ilias->raiseError($this->lng->txt("meta_l_" . $newSysLangObj->getKey()) . " is already the system language!<br>Action aborted!", $this->ilias->error_obj->MESSAGE);
        }

        if ($newSysLangObj->isInstalled() == false) {
            $this->ilias->raiseError($this->lng->txt("meta_l_" . $newSysLangObj->getKey()) . " is not installed. Please install that language first.<br>Action aborted!", $this->ilias->error_obj->MESSAGE);
        }

        $this->ilias->setSetting("language", $newSysLangObj->getKey());

        // update ini-file
        $this->ilias->ini->setVariable("language", "default", $newSysLangObj->getKey());
        $this->ilias->ini->write();

        $this->data = $this->lng->txt("system_language") . " " . $this->lng->txt("changed_to") . " " . $this->lng->txt("meta_l_" . $newSysLangObj->getKey()) . ".";

        $this->out();
    }


    /**
     * check all languages
     */
    public function checkLanguageObject()
    {
        $this->checkPermission('write');
        $this->data = $this->object->checkAllLanguages();
        $this->out();
    }


    public function out()
    {
        ilUtil::sendInfo($this->data, true);
        $this->ctrl->redirect($this, "view");
    }
    
    public function getAdminTabs()
    {
        $this->getTabs();
    }
    
    /**
    * get tabs
    * @access	public
    * @param	object	tabs gui object
    */
    public function getTabs()
    {
        if ($this->checkPermissionBool("read")) {
            $this->tabs_gui->addTab('settings', $this->lng->txt('settings'), $this->ctrl->getLinkTarget($this, "view"));
        }

        if ($this->checkPermissionBool('edit_permission')) {
            $this->tabs_gui->addTab('perm_settings', $this->lng->txt('perm_settings'), $this->ctrl->getLinkTargetByClass(array(get_class($this),'ilpermissiongui'), "perm"));
        }
    }
    
    public function executeCommand()
    {
        // always check read permission, needed write permissions are checked in the *Object functions
        $this->checkPermission('read', '', $this->type, $this->ref_id);

        $next_class = $this->ctrl->getNextClass($this);
        $cmd = $this->ctrl->getCmd();
        $this->prepareOutput();

        switch ($next_class) {
            case 'ilpermissiongui':
                include_once("Services/AccessControl/classes/class.ilPermissionGUI.php");
                $perm_gui = new ilPermissionGUI($this);
                $this->tabs_gui->activateTab('perm_settings');
                $this->ctrl->forwardCommand($perm_gui);
                break;

            default:
                $this->tabs_gui->activateTab('settings');

                if (!$cmd) {
                    $cmd = "view";
                }

                $cmd .= "Object";
                $this->$cmd();

                break;
        }
        return true;
    }

    public function confirmRefreshObject()
    {
        $this->checkPermission('write');

        $languages = ilObject::_getObjectsByType("lng");

        $ids = array();
        foreach ($languages as $lang) {
            $langObj = new ilObjLanguage($lang["obj_id"], false);
            if ($langObj->isInstalled() == true) {
                $ids[] = $lang["obj_id"];
            }
        }
        $this->confirmRefreshSelectedObject($ids);
    }

    public function confirmRefreshSelectedObject($a_ids = array())
    {
        $this->checkPermission('write');
        $this->lng->loadLanguageModule("meta");

        if (!empty($a_ids)) {
            $ids = $a_ids;
            $header = $this->lng->txt("lang_refresh_confirm");
        } elseif (!empty($_POST["id"])) {
            $ids = $_POST["id"];
            $header =  $this->lng->txt("lang_refresh_confirm_selected");
        } else {
            $this->ilias->raiseError($this->lng->txt("no_checkbox"), $this->ilias->error_obj->MESSAGE);
        }


        $conf_screen = new ilConfirmationGUI();
        $some_changed = false;
        foreach ($ids as $id) {
            $lang_key = ilObject::_lookupTitle($id);
            $lang_title = $this->lng->txt('meta_l_' . $lang_key);
            $last_change = ilObjLanguage::_getLastLocalChange($lang_key);
            if (!empty($last_change)) {
                $some_changed = true;
                $lang_title .= ' (' . $this->lng->txt("last_change") . ' '
                    . ilDatePresentation::formatDate(new ilDateTime($last_change, IL_CAL_DATETIME)) . ')';
            }
            $conf_screen->addItem("id[]", $id, $lang_title);
        }

        $conf_screen->setFormAction($this->ctrl->getFormAction($this));
        if ($some_changed) {
            $header .= '<br />' . $this->lng->txt("lang_refresh_confirm_info");
        }
        $conf_screen->setHeaderText($header);
        $conf_screen->setCancel($this->lng->txt("cancel"), "view");
        $conf_screen->setConfirm($this->lng->txt("ok"), "refreshSelected");
        $this->tpl->setContent($conf_screen->getHTML());
    }

    public function confirmUninstallObject()
    {
        $this->checkPermission('write');

        if (!isset($_POST["id"])) {
            $this->ilias->raiseError($this->lng->txt("no_checkbox"), $this->ilias->error_obj->MESSAGE);
        }

        $this->lng->loadLanguageModule("meta");
        include_once("./Services/Utilities/classes/class.ilConfirmationGUI.php");
        $conf_screen = new ilConfirmationGUI();
        $conf_screen->setFormAction($this->ctrl->getFormAction($this));
        $conf_screen->setHeaderText($this->lng->txt("lang_uninstall_confirm"));
        foreach ($_POST["id"] as $id) {
            $lang_title = ilObject::_lookupTitle($id);
            $conf_screen->addItem("id[]", $id, $this->lng->txt("meta_l_" . $lang_title));
        }
        $conf_screen->setCancel($this->lng->txt("cancel"), "view");
        $conf_screen->setConfirm($this->lng->txt("ok"), "uninstall");
        $this->tpl->setContent($conf_screen->getHTML());
    }


    public function confirmUninstallChangesObject()
    {
        $this->checkPermission('write');

        if (!isset($_POST["id"])) {
            $this->ilias->raiseError($this->lng->txt("no_checkbox"), $this->ilias->error_obj->MESSAGE);
        }

        $this->lng->loadLanguageModule("meta");
        include_once("./Services/Utilities/classes/class.ilConfirmationGUI.php");
        $conf_screen = new ilConfirmationGUI();
        $conf_screen->setFormAction($this->ctrl->getFormAction($this));
        $conf_screen->setHeaderText($this->lng->txt("lang_uninstall_changes_confirm"));
        foreach ($_POST["id"] as $id) {
            $lang_title = ilObject::_lookupTitle($id);
            $conf_screen->addItem("id[]", $id, $this->lng->txt("meta_l_" . $lang_title));
        }
        $conf_screen->setCancel($this->lng->txt("cancel"), "view");
        $conf_screen->setConfirm($this->lng->txt("ok"), "uninstallChanges");
        $this->tpl->setContent($conf_screen->getHTML());
    }

    /**
     * Get Actions
     * @deprecated	seems to be not needed anymore
    */
    public function getActions()
    {
        // standard actions for container
        return array(
            "install" => array("name" => "install", "lng" => "install"),
            "installLocal" => array("name" => "installLocal", "lng" => "install_local"),
            "uninstall" => array("name" => "uninstall", "lng" => "uninstall"),
            "refresh" => array("name" => "confirmRefreshSelected", "lng" => "refresh"),
            "setSystemLanguage" => array("name" => "setSystemLanguage", "lng" => "setSystemLanguage"),
            "setUserLanguage" => array("name" => "setUserLanguage", "lng" => "setUserLanguage")
        );
    }

    /**
     * Disable language detection
     */
    protected function disableLanguageDetectionObject()
    {
        $this->settings->set('lang_detection', 0);
        ilUtil::sendSuccess($this->lng->txt('saved_successfully'));
        $this->viewObject();
    }

    /**
     * Enable language detection
     */
    protected function enableLanguageDetectionObject()
    {
        $this->settings->set('lang_detection', 1);
        ilUtil::sendSuccess($this->lng->txt('saved_successfully'));
        $this->viewObject();
    }

    /**
     * Download deprecated lang entries
     */
    public function listDeprecatedObject()
    {
        $button = ilLinkButton::getInstance();
        $button->setCaption("download");
        $button->setUrl($this->ctrl->getLinkTarget($this, "downloadDeprecated"));
        $this->toolbar->addButtonInstance($button);

        include_once("./Services/Language/classes/class.ilLangDeprecated.php");

        $d = new ilLangDeprecated();
        $res = "";
        foreach ($d->getDeprecatedLangVars() as $key => $mod) {
            $res.= $mod . "," . $key . "\n";
        }

        $this->tpl->setContent("<pre>" . $res . "</pre>");
    }

    /**
     * Download deprecated lang entries
     */
    public function downloadDeprecatedObject()
    {
        include_once("./Services/Language/classes/class.ilLangDeprecated.php");
        $d = new ilLangDeprecated();
        $res = "";
        foreach ($d->getDeprecatedLangVars() as $key => $mod) {
            $res.= $mod . "," . $key . "\n";
        }

        ilUtil::deliverData($res, "lang_deprecated.csv");
    }
} // END class.ilObjLanguageFolderGUI
