<?php

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

declare(strict_types=1);

use ILIAS\UI\URLBuilder;
use ILIAS\UI\URLBuilderToken;

/**
 * Class ilObjLanguageFolderGUI
 *
 * @author    Stefan Meyer <meyer@leifos.com>
 * @version   $Id$
 *
 * @ilCtrl_Calls ilObjLanguageFolderGUI: ilPermissionGUI
 *
 * @extends ilObject
 */
class ilObjLanguageFolderGUI extends ilObjectGUI
{
    protected ilLanguageFolderTable $languageFolderTable;
    protected ILIAS\Data\Factory $df;
    protected URLBuilder $url_builder;
    protected URLBuilderToken $action_token;
    protected URLBuilderToken $id_token;

    /**
     * Constructor
     */
    public function __construct(?array $a_data, int $a_id, bool $a_call_by_reference)
    {
        $this->type = "lngf";
        parent::__construct($a_data, $a_id, $a_call_by_reference, false);
        $this->lng->loadLanguageModule("lng");
        $this->df = new ILIAS\Data\Factory();

        $here_uri = $this->df->uri($this->request->getUri()->__toString());
        $url_builder = new URLBuilder($here_uri);
        $query_params_namespace = ['language_folder'];
        [$url_builder, $action_parameter_token, $row_id_token] =
            $url_builder->acquireParameters(
                $query_params_namespace,
                "table_action", //this is the actions's parameter name
                "obj_ids"   //this is the parameter name to be used for row-ids
            );

        $this->url_builder = $url_builder;
        $this->action_token = $action_parameter_token;
        $this->id_token = $row_id_token;

        /** @var ilObjLanguageFolder $folder */
        $folder = $this->object;
        $this->languageFolderTable = new ilLanguageFolderTable($folder, $url_builder, $action_parameter_token, $row_id_token);
    }

    /**
     * show installed languages
     */
    public function viewObject(): void
    {
        global $DIC;

        $table = $this->languageFolderTable->getTable();

        if ($this->checkPermissionBool("write")) {
            // refresh
            $refresh = $this->ui_factory->link()->standard(
                $this->lng->txt("refresh_languages"),
                $this->getUrl("confirmRefresh")
            );
            $this->toolbar->addComponent($refresh);

            // check languages
            $check = $this->ui_factory->button()->standard(
                $this->lng->txt("check_languages"),
                $this->getUrl("checkLanguage")
            );
            $this->toolbar->addComponent($check);
        }

        $ilClientIniFile = $DIC["ilClientIniFile"];
        if ($ilClientIniFile->variableExists("system", "LANGUAGE_LOG")) {
            $download = $this->ui_factory->button()->standard(
                $this->lng->txt("lng_download_deprecated"),
                $this->ctrl->getLinkTarget($this, "listDeprecated")
            );
            $this->toolbar->addComponent($download);
        }

        if ($this->checkPermissionBool("write")) {
            $modal_on = $this->ui_factory->modal()->interruptive(
                'ON',
                $this->lng->txt("lng_enable_language_detection"),
                $this->ctrl->getFormActionByClass(self::class, "enableLanguageDetection")
            )
                                         ->withActionButtonLabel($this->lng->txt('ok'));
            $modal_off = $this->ui_factory->modal()->interruptive(
                'OFF',
                $this->lng->txt("lng_disable_language_detection"),
                $this->ctrl->getFormActionByClass(self::class, "disableLanguageDetection")
            )
                                          ->withActionButtonLabel($this->lng->txt('ok'));
            $toggleButton = $this->ui_factory->button()->toggle(
                $this->lng->txt("language_detection"),
                $modal_on->getShowSignal(),
                $modal_off->getShowSignal(),
                (bool) ($this->settings->get("lang_detection"))
            )
                                             ->withAriaLabel($this->lng->txt("lng_switch_language_detection"));
            $this->toolbar->addComponent($modal_on);
            $this->toolbar->addComponent($modal_off);
            $this->toolbar->addComponent($toggleButton);
        }

        $this->tpl->setContent($this->ui_renderer->render($table->withRequest($this->request)));
    }

    protected function buildConfirmModal(array $ids, string $title, string $action, string $text, string $add_text = ''): ILIAS\UI\Implementation\Component\Modal\Interruptive
    {
        $f = $this->ui_factory;
        $title = $this->lng->txt($title);
        $items = [];

        if (!empty($ids)) {
            $message = $this->lng->txt($text);

            $some_changed = false;
            foreach ($ids as $id) {
                $lang_key = ilObject::_lookupTitle((int) $id);
                $lang_title = $this->lng->txt("meta_l_" . $lang_key);
                $last_change = ilObjLanguage::_getLastLocalChange($lang_key);
                if (!empty($last_change)) {
                    $some_changed = true;
                    $lang_title .= " (" . $this->lng->txt("last_change") . " "
                        . ilDatePresentation::formatDate(new ilDateTime(
                            $last_change,
                            IL_CAL_DATETIME
                        )) . ")";
                }
                $items[] = $f->modal()->interruptiveItem()->standard($id, $lang_title);
            }
            $form_action = $this->getUrl($action, $ids);

            if ($some_changed) {
                $message .= "<br />" . $this->lng->txt($add_text);
            }
        } else {
            $message = $this->lng->txt("no_checkbox");
            $form_action = '';
        }
        $modal = $f->modal()->interruptive(
            $title,
            $message,
            $form_action
        );
        if (!empty($items)) {
            $modal = $modal->withAffectedItems($items)
                           ->withActionButtonLabel($title);
        } else {
            $modal = $modal->withActionButtonLabel($this->lng->txt('ok'));
        }
        return $modal;
    }

    /**
     * install languages
     */
    public function installObject(array $ids): void
    {
        $this->checkPermission("write");
        $this->lng->loadLanguageModule("meta");

        foreach ($ids as $obj_id) {
            $langObj = new ilObjLanguage((int) $obj_id);
            $key = $langObj->install();

            if ($key !== "") {
                $lang_installed[] = $key;
            }

            unset($langObj);
        }

        if (isset($lang_installed)) {
            if (count($lang_installed) === 1) {
                $this->data = $this->lng->txt("meta_l_" . $lang_installed[0]) . " " . strtolower($this->lng->txt("installed")) . ".";
            } else {
                $langnames = [];
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
    public function installLocalObject(array $ids): void
    {
        $this->checkPermission("write");
        $this->lng->loadLanguageModule("meta");

        foreach ($ids as $obj_id) {
            $langObj = new ilObjLanguage((int) $obj_id);
            $key = $langObj->install();

            if ($key !== "") {
                $lang_installed[] = $key;
            }

            unset($langObj);

            $langObj = new ilObjLanguage((int) $obj_id);
            $key = $langObj->install("local");

            if ($key !== "") {
                $local_installed[] = $key;
            }

            unset($langObj);
        }

        if (isset($lang_installed)) {
            if (count($lang_installed) === 1) {
                $this->data = $this->lng->txt("meta_l_" . $lang_installed[0]) . " " . strtolower($this->lng->txt("installed")) . ".";
            } else {
                $langnames = [];
                foreach ($lang_installed as $lang_key) {
                    $langnames[] = $this->lng->txt("meta_l_" . $lang_key);
                }
                $this->data = implode(", ", $langnames) . " " . strtolower($this->lng->txt("installed")) . ".";
            }
        }

        if (isset($local_installed)) {
            if (count($local_installed) === 1) {
                $this->data .= " " . $this->lng->txt("meta_l_" . $local_installed[0]) . " " . $this->lng->txt("local_language_file") . " " . strtolower($this->lng->txt("installed")) . ".";
            } else {
                $langnames = [];
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
    public function uninstallObject(array $ids): void
    {
        $this->checkPermission('write');
        $this->lng->loadLanguageModule("meta");

        $sys_lang = false;
        $usr_lang = false;

        // uninstall all selected languages
        foreach ($ids as $obj_id) {
            $langObj = new ilObjLanguage((int) $obj_id);
            if (!($sys_lang = $langObj->isSystemLanguage()) && !($usr_lang = $langObj->isUserLanguage())) {
                $key = $langObj->uninstall();
                if ($key !== "") {
                    $lang_uninstalled[] = $key;
                }
            }
            unset($langObj);
        }

        // generate output message
        if (isset($lang_uninstalled)) {
            if (count($lang_uninstalled) === 1) {
                $this->data = $this->lng->txt("meta_l_" . $lang_uninstalled[0]) . " " . $this->lng->txt("uninstalled");
            } else {
                $langnames = [];
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
    public function uninstallChangesObject(array $ids): void
    {
        $this->checkPermission("write");

        $this->data = $this->lng->txt("selected_languages_updated");
        $this->lng->loadLanguageModule("meta");
        $refreshed = [];

        foreach ($ids as $id) {
            $langObj = new ilObjLanguage((int) $id, false);

            if ($langObj->isInstalled()) {
                if ($langObj->check()) {
                    $langObj->flush("all");
                    $langObj->insert();
                    $langObj->setTitle($langObj->getKey());
                    $langObj->setDescription("installed");
                    $langObj->update();
                    $refreshed[] = $langObj->getKey();
                }
                $this->data .= "<br />" . $this->lng->txt("meta_l_" . $langObj->getKey());
            }

            unset($langObj);
        }
        ilObjLanguage::refreshPlugins($refreshed);
        $this->out();
    }

    /**
     * update all installed languages
     */
    public function refreshObject(): void
    {
        $this->checkPermission("write");

        ilObjLanguage::refreshAll();
        $this->data = $this->lng->txt("languages_updated");
        $this->out();
    }

    /**
     * update selected languages
     */
    public function refreshSelectedObject(array $ids): void
    {
        $this->checkPermission("write");
        $this->data = $this->lng->txt("selected_languages_updated");
        $this->lng->loadLanguageModule("meta");

        $refreshed = [];
        foreach ($ids as $id) {
            $langObj = new ilObjLanguage((int) $id, false);
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
    public function setUserLanguageObject(array $ids): void
    {
        global $DIC;
        $ilUser = $DIC->user();

        $this->checkPermission("write");
        $this->lng->loadLanguageModule("meta");

        #require_once "./Services/User/classes/class.ilObjUser.php";


        if (count($ids) !== 1) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt("choose_only_one_language") . "<br/>" . $this->lng->txt("action_aborted"), true);
            $this->ctrl->redirect($this, "view");
        }

        $obj_id = current($ids);

        $newUserLangObj = new ilObjLanguage((int) $obj_id);

        if ($newUserLangObj->isUserLanguage()) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt("meta_l_" . $newUserLangObj->getKey()) . " " . $this->lng->txt("is_already_your") . " " . $this->lng->txt("user_language") . "<br/>" . $this->lng->txt("action_aborted"), true);
            $this->ctrl->redirect($this, "view");
        }

        if (!$newUserLangObj->isInstalled()) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt("meta_l_" . $newUserLangObj->getKey()) . " " . $this->lng->txt("language_not_installed") . "<br/>" . $this->lng->txt("action_aborted"), true);
            $this->ctrl->redirect($this, "view");
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
    public function setSystemLanguageObject(array $ids): void
    {
        $this->checkPermission("write");
        $this->lng->loadLanguageModule("meta");


        if (count($ids) !== 1) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt("choose_only_one_language") . "<br/>" . $this->lng->txt("action_aborted"), true);
            $this->ctrl->redirect($this, "view");
        }

        $obj_id = current($ids);

        $newSysLangObj = new ilObjLanguage((int) $obj_id);

        if ($newSysLangObj->isSystemLanguage()) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt("meta_l_" . $newSysLangObj->getKey()) . " " . $this->lng->txt("is_already_your") . " " . $this->lng->txt("system_language") . "<br/>" . $this->lng->txt("action_aborted"), true);
            $this->ctrl->redirect($this, "view");
        }

        if (!$newSysLangObj->isInstalled()) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt("meta_l_" . $newSysLangObj->getKey()) . " " . $this->lng->txt("language_not_installed") . "<br/>" . $this->lng->txt("action_aborted"), true);
            $this->ctrl->redirect($this, "view");
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
    public function checkLanguageObject(): void
    {
        $this->checkPermission("write");
        $this->data = $this->object->checkAllLanguages();
        $this->out();
    }

    public function out(): void
    {
        $this->tpl->setOnScreenMessage('info', $this->data, true);
        $this->ctrl->redirect($this, "view");
    }

    public function getAdminTabs(): void
    {
        $this->getTabs();
    }

    /**
     * Retrieves and adds tabs based on user permissions
     */
    protected function getTabs(): void
    {
        if ($this->checkPermissionBool("read")) {
            $this->tabs_gui->addTab("settings", $this->lng->txt("settings"), $this->ctrl->getLinkTarget($this, "view"));
        }

        if ($this->checkPermissionBool("edit_permission")) {
            $this->tabs_gui->addTab("perm_settings", $this->lng->txt("perm_settings"), $this->ctrl->getLinkTargetByClass(array(self::class,"ilpermissiongui"), "perm"));
        }
    }

    public function executeCommand(): void
    {
        // always check read permission, needed write permissions are checked in the *Object functions
        $this->checkPermission("read", "", $this->type, $this->ref_id);

        $next_class = $this->ctrl->getNextClass($this);
        $cmd = $this->ctrl->getCmd();
        $this->prepareOutput();

        switch ($next_class) {
            case "ilpermissiongui":
                include_once "Services/AccessControl/classes/class.ilPermissionGUI.php";
                $perm_gui = new ilPermissionGUI($this);
                $this->tabs_gui->activateTab("perm_settings");
                $this->ctrl->forwardCommand($perm_gui);
                break;

            default:
                $this->tabs_gui->activateTab("settings");

                if ($action = $this->getCommandFromQueryToken($this->action_token->getName())) {
                    $this->checkPermission("write");
                    $this->lng->loadLanguageModule("meta");
                    $f = $this->ui_factory;
                    $r = $this->ui_renderer;
                    switch ($action) {
                        case 'confirmRefresh':
                            $ids = $this->confirmRefreshObject();
                            $this->refreshSelectedObject($ids);
                            break;
                        case 'checkLanguage':
                            $this->checkLanguageObject();
                            break;
                        case 'refresh':
                            $ids = $this->getIdsFromQueryToken();
                            if (current($ids) === 'ALL_OBJECTS') {
                                array_shift($ids);
                                $languages = ilObject::_getObjectsByType("lng");
                                foreach ($languages as $lang) {
                                    $langObj = new ilObjLanguage((int) $lang["obj_id"], false);
                                    if ($langObj->isInstalled()) {
                                        $ids[] = (string) $lang["obj_id"];
                                    }
                                }
                            }
                            $modal = $this->buildConfirmModal(
                                $ids,
                                $action,
                                'refreshConfirmed',
                                'lang_refresh_confirm_selected',
                                "lang_refresh_confirm_info"
                            );
                            echo($r->renderAsync([$modal]));
                            exit();
                        case 'refreshConfirmed':
                            $ids = $this->getIdsFromQueryToken();
                            $this->refreshSelectedObject($ids);
                            break;
                        case 'uninstall':
                            $ids = $this->getIdsFromQueryToken();
                            if (current($ids) === 'ALL_OBJECTS') {
                                array_shift($ids);
                                $languages = ilObject::_getObjectsByType("lng");
                                foreach ($languages as $lang) {
                                    $langObj = new ilObjLanguage((int) $lang["obj_id"], false);
                                    if ($langObj->isInstalled()) {
                                        $ids[] = (string) $lang["obj_id"];
                                    }
                                }
                            }
                            $modal = $this->buildConfirmModal(
                                $ids,
                                $action,
                                'uninstallConfirmed',
                                'lang_uninstall_confirm'
                            );
                            echo($r->renderAsync([$modal]));
                            exit();
                        case 'uninstallConfirmed':
                            $ids = $this->getIdsFromQueryToken();
                            $this->uninstallObject($ids);
                            break;
                        case 'install':
                            $ids = $this->getIdsFromQueryToken();
                            if (current($ids) === 'ALL_OBJECTS') {
                                array_shift($ids);
                                $languages = ilObject::_getObjectsByType("lng");
                                foreach ($languages as $lang) {
                                    $langObj = new ilObjLanguage((int) $lang["obj_id"], false);
                                    if (!$langObj->isInstalled()) {
                                        $ids[] = (string) $lang["obj_id"];
                                    }
                                }
                            }
                            $this->installObject($ids);
                            break;
                        case 'install_local':
                            $ids = $this->getIdsFromQueryToken();
                            if (current($ids) === 'ALL_OBJECTS') {
                                array_shift($ids);
                                $languages = ilObject::_getObjectsByType("lng");
                                foreach ($languages as $lang) {
                                    $ids[] = (string) $lang["obj_id"];
                                }
                            }
                            $this->installLocalObject($ids);
                            break;
                        case 'lang_uninstall_changes':
                            $ids = $this->getIdsFromQueryToken();
                            if (current($ids) === 'ALL_OBJECTS') {
                                array_shift($ids);
                                $languages = ilObject::_getObjectsByType("lng");
                                foreach ($languages as $lang) {
                                    $langObj = new ilObjLanguage((int) $lang["obj_id"], false);
                                    if ($langObj->isInstalled()) {
                                        $ids[] = (string) $lang["obj_id"];
                                    }
                                }
                            }
                            $modal = $this->buildConfirmModal(
                                $ids,
                                $action,
                                'uninstallChanges',
                                'lang_uninstall_changes_confirm'
                            );
                            echo($r->renderAsync([$modal]));
                            exit();
                        case 'uninstallChanges':
                            $ids = $this->getIdsFromQueryToken();
                            $this->uninstallChangesObject($ids);
                            break;
                        case 'setSystemLanguage':
                            $ids = $this->getIdsFromQueryToken();
                            $this->setSystemLanguageObject($ids);
                            break;
                        case 'setUserLanguage':
                            $ids = $this->getIdsFromQueryToken();
                            $this->setUserLanguageObject($ids);
                            break;
                        case 'edit':
                            $ids = $this->getIdsFromQueryToken();
                            $this->editFolderObject($ids);
                            break;
                    }
                }
                if (!$cmd) {
                    $cmd = "view";
                }
                $cmd .= "Object";
                $this->$cmd();

                break;
        }
    }

    protected function getCommandFromQueryToken(string $param): ?string
    {
        if (!$this->request_wrapper->has($param)) {
            return null;
        }
        $trafo = $this->refinery->byTrying([
            $this->refinery->kindlyTo()->null(),
            $this->refinery->kindlyTo()->string()
        ]);
        return $this->request_wrapper->retrieve($param, $trafo);
    }

    public function confirmRefreshObject(): array
    {
        $this->checkPermission("write");

        $languages = ilObject::_getObjectsByType("lng");

        $ids = [];
        foreach ($languages as $lang) {
            $langObj = new ilObjLanguage((int) $lang["obj_id"], false);
            if ($langObj->isInstalled()) {
                $ids[] = (string) $lang["obj_id"];
            }
        }
        return $ids;
    }

    public function confirmRefreshSelectedObject(array $a_ids = []): void
    {
        $this->checkPermission("write");
        $this->lng->loadLanguageModule("meta");

        $header = '';
        $ids = [];
        if (!empty($a_ids)) {
            $ids = $a_ids;
            $header = $this->lng->txt("lang_refresh_confirm");
        } elseif (!empty($post_id = $this->getIdsFromQueryToken())) {
            $ids = $post_id;
            $header = $this->lng->txt("lang_refresh_confirm_selected");
        } else {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt("no_checkbox"), true);
            $this->ctrl->redirect($this, "view");
        }

        $conf_screen = new ilConfirmationGUI();
        $some_changed = false;
        foreach ($ids as $id) {
            $lang_key = ilObject::_lookupTitle((int) $id);
            $lang_title = $this->lng->txt("meta_l_" . $lang_key);
            $last_change = ilObjLanguage::_getLastLocalChange($lang_key);
            if (!empty($last_change)) {
                $some_changed = true;
                $lang_title .= " (" . $this->lng->txt("last_change") . " "
                    . ilDatePresentation::formatDate(new ilDateTime($last_change, IL_CAL_DATETIME)) . ")";
            }
            $conf_screen->addItem("id[]", (string) $id, $lang_title);
        }

        $conf_screen->setFormAction($this->ctrl->getFormAction($this));
        if ($some_changed) {
            $header .= "<br />" . $this->lng->txt("lang_refresh_confirm_info");
        }
        $conf_screen->setHeaderText($header);
        $conf_screen->setCancel($this->lng->txt("cancel"), "view");
        $conf_screen->setConfirm($this->lng->txt("ok"), "refreshSelected");
        $this->tpl->setContent($conf_screen->getHTML());
    }

    public function confirmUninstallObject(): void
    {
        $this->checkPermission("write");

        $this->lng->loadLanguageModule("meta");
        $conf_screen = new ilConfirmationGUI();
        $conf_screen->setFormAction($this->ctrl->getFormAction($this));
        $conf_screen->setHeaderText($this->lng->txt("lang_uninstall_confirm"));
        foreach ($this->getIdsFromQueryToken() as $id) {
            $lang_title = ilObject::_lookupTitle((int) $id);
            $conf_screen->addItem("id[]", $id, $this->lng->txt("meta_l_" . $lang_title));
        }
        $conf_screen->setCancel($this->lng->txt("cancel"), "view");
        $conf_screen->setConfirm($this->lng->txt("ok"), "uninstall");
        $this->tpl->setContent($conf_screen->getHTML());
    }

    public function confirmUninstallChangesObject(): void
    {
        $this->checkPermission('write');

        $this->lng->loadLanguageModule("meta");
        $conf_screen = new ilConfirmationGUI();
        $conf_screen->setFormAction($this->ctrl->getFormAction($this));
        $conf_screen->setHeaderText($this->lng->txt("lang_uninstall_changes_confirm"));
        foreach ($this->getIdsFromQueryToken() as $id) {
            $lang_title = ilObject::_lookupTitle($id);
            $conf_screen->addItem("id[]", (string) $id, $this->lng->txt("meta_l_" . $lang_title));
        }
        $conf_screen->setCancel($this->lng->txt("cancel"), "view");
        $conf_screen->setConfirm($this->lng->txt("ok"), "uninstallChanges");
        $this->tpl->setContent($conf_screen->getHTML());
    }

    /**
     * Get Actions
     */
    public function getActions(): array
    {
        $f = $this->ui_factory;
        return [
            'confirmRefreshSelected' => $f->table()->action()->standard(
                $this->lng->txt("refresh"),
                $this->url_builder->withParameter($this->action_token, "confirmRefreshSelected"),
                $this->id_token
            )->withAsync(),
            'install' => $f->table()->action()->standard(
                $this->lng->txt("install"),
                $this->url_builder->withParameter($this->action_token, "install"),
                $this->id_token
            ),
            'installLocal' => $f->table()->action()->standard(
                $this->lng->txt("install_local"),
                $this->url_builder->withParameter($this->action_token, "installLocal"),
                $this->id_token
            ),
            'confirmUninstall' => $f->table()->action()->standard(
                $this->lng->txt("uninstall"),
                $this->url_builder->withParameter($this->action_token, "confirmUninstall"),
                $this->id_token
            ),
            'confirmUninstallChanges' => $f->table()->action()->standard(
                $this->lng->txt("lang_uninstall_changes"),
                $this->url_builder->withParameter($this->action_token, "confirmUninstallChanges"),
                $this->id_token
            ),
            'setSystemLanguage' => $f->table()->action()->single(
                $this->lng->txt("setSystemLanguage"),
                $this->url_builder->withParameter($this->action_token, "setSystemLanguage"),
                $this->id_token
            ),
            'setUserLanguage' => $f->table()->action()->single(
                $this->lng->txt("setUserLanguage"),
                $this->url_builder->withParameter($this->action_token, "setUserLanguage"),
                $this->id_token
            ),
            'editFolder' => $f->table()->action()->single(
                $this->lng->txt("edit"),
                $this->url_builder->withParameter($this->action_token, "editFolder"),
                $this->id_token
            ),
        ];
    }

    protected function editFolderObject(array $ids): void
    {
        $this->ctrl->setParameterByClass("ilobjlanguageextgui", "obj_id", current($ids));
        $this->ctrl->redirectByClass("ilobjlanguageextgui");
    }

    /**
     * Disable language detection
     */
    protected function disableLanguageDetectionObject(): void
    {
        $this->settings->set("lang_detection", '0');
        $this->tpl->setOnScreenMessage('success', $this->lng->txt("saved_successfully"));
        $this->viewObject();
    }

    /**
     * Enable language detection
     */
    protected function enableLanguageDetectionObject(): void
    {
        $this->settings->set("lang_detection", '1');
        $this->tpl->setOnScreenMessage('success', $this->lng->txt("saved_successfully"));
        $this->viewObject();
    }

    /**
     * Download deprecated lang entries
     */
    public function listDeprecatedObject(): void
    {
        $button = $this->ui_factory->button()->standard(
            $this->lng->txt("download"),
            $this->ctrl->getLinkTarget($this, "downloadDeprecated")
        );
        $this->toolbar->addComponent($button);

        include_once "./Services/Language/classes/class.ilLangDeprecated.php";

        $d = new ilLangDeprecated();
        $res = "";
        foreach ($d->getDeprecatedLangVars() as $key => $mod) {
            $res .= $mod . "," . $key . "\n";
        }

        $this->tpl->setContent("<pre>" . $res . "</pre>");
    }

    /**
     * Download deprecated lang entries
     */
    public function downloadDeprecatedObject(): void
    {
        include_once "./Services/Language/classes/class.ilLangDeprecated.php";
        $d = new ilLangDeprecated();
        $res = "";
        foreach ($d->getDeprecatedLangVars() as $key => $mod) {
            $res .= $mod . "," . $key . "\n";
        }

        ilUtil::deliverData($res, "lang_deprecated.csv");
    }

    protected function getUrl(string $action, ?array $lang_ids = null): string
    {
        $url_builder = $this->url_builder->withParameter($this->action_token, $action);
        if($lang_ids) {
            $url_builder = $url_builder->withParameter($this->id_token, $lang_ids);
        }
        return $url_builder->buildURI()->__toString();
    }

    private function getIdsFromQueryToken(): array
    {
        $ids = [];
        if ($this->request_wrapper->has($this->id_token->getName())) {
            $ids = $this->request_wrapper->retrieve(
                $this->id_token->getName(),
                $this->refinery->custom()->transformation(fn($v) => $v)
            );
        }
        return $ids;
    }
} // END class.ilObjLanguageFolderGUI
