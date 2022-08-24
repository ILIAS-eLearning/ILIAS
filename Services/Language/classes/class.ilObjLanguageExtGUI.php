<?php

declare(strict_types=1);

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
 ********************************************************************
 */

use ILIAS\FileUpload\DTO\ProcessingStatus;
use ILIAS\FileUpload\Location;
use ILIAS\HTTP\Services as HTTPServices;
use ILIAS\Refinery\Factory as Refinery;

require_once("./Services/Object/classes/class.ilObjectGUI.php");
require_once("Services/Language/classes/class.ilObjLanguageAccess.php");


/**
* Class ilObjLanguageExtGUI
*
* This class is a replacement for ilObjLanguageGUI
* which is currently not used in ILIAS.
*
* @author Fred Neumann <fred.neumann@fim.uni-erlangen.de>
* @version $Id: class.ilObjLanguageExtGUI.php $
*
* @ilCtrl_Calls ilObjLanguageExtGUI:
* @ilCtrl_IsCalledBy ilObjLanguageExtGUI: ilDashboardGUI
*
* @ingroup ServicesLanguage
*/
class ilObjLanguageExtGUI extends ilObjectGUI
{
    private const ILIAS_LANGUAGE_MODULE = "Services/Language";
    private string $langmode;
    protected HTTPServices $http;
    protected Refinery $refinery;
    /**
    * Constructor
    *
    * Note:
    * The GET param 'obj_id' is the language object id
    * The GET param 'ref_id' is the language folder (if present)
    *
    * @param    mixed       $a_data (ignored)
    * $a_id         id (ignored)
    * $a_call_by_reference     call-by-reference (ignored)
    */
    public function __construct($a_data, int $a_id = 0, bool $a_call_by_reference = false)
    {
        global $DIC;
        $ilClientIniFile = $DIC->clientIni();
        $ilCtrl = $DIC->ctrl();
        $lng = $DIC->language();
        $this->http = $DIC->http();
        $this->refinery = $DIC->refinery();

        // language maintenance strings are defined in administration
        $lng->loadLanguageModule("administration");
        $lng->loadLanguageModule("meta");

        //  view mode ('translate' or empty) determins available table filters
        $ilCtrl->saveParameter($this, "view_mode");

        // type and id of get the bound object
        $this->type = "lng";
        $obj_id_get = 0;
        if ($this->http->wrapper()->query()->has("obj_id")) {
            $obj_id_get = $this->http->wrapper()->query()->retrieve("obj_id", $this->refinery->kindlyTo()->int());
        }
        if (!$this->id = $obj_id_get) {
            $this->id = ilObjLanguageAccess::_lookupId($lng->getUserLanguage());
        }

        // do all generic GUI initialisations
        parent::__construct($a_data, $this->id, false, true);

        // initialize the array to store session variables for extended language maintenance
        if (!is_array($this->getSession())) {
            ilSession::set("lang_ext_maintenance", array());
        }
        // $this->session = &$_SESSION["lang_ext_maintenance"];// Todo-PHP8-Review This property is not defined, here and in other methods in this class


        // read the lang mode
        $this->langmode = $ilClientIniFile->readVariable("system", "LANGMODE");
    }

    /**
    * Assign the extended language object
    *
    * Overwritten from ilObjectGUI to use the extended language object.
    * (Will be deleted when ilObjLanguageExt is merged with ilObjLanguage)
    */
    protected function assignObject(): void
    {
        require_once("Services/Language/classes/class.ilObjLanguageExt.php");
        $this->object = new ilObjLanguageExt($this->id);
    }

    /**
     * get the language object id (needed for filter serialization)
     * Return language object id
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
    * execute command
    */
    public function executeCommand(): void
    {
        global $DIC;
        $ilHelp = $DIC->help();

        if (!ilObjLanguageAccess::_checkMaintenance()) {
            $this->error->raiseError($this->lng->txt("permission_denied"), $this->error->MESSAGE);
            exit;
        }

        $cmd = $this->ctrl->getCmd("view") . "Object";
        $this->$cmd();

        $ilHelp->setScreenIdComponent("lng");
    }

    /**
    * Cancel the current action
    */
    public function cancelObject(): void
    {
        $this->viewObject();
    }

    /**
     * Get the table to view language entries
     */
    protected function getViewTable(): \ilLanguageExtTableGUI
    {
        // create and configure the table object
        include_once "./Services/Language/classes/class.ilLanguageExtTableGUI.php";
        $table_gui = new ilLanguageExtTableGUI($this, "view", array(
            "langmode" => $this->langmode,
            "lang_key" => $this->object->key,
        ));

        return $table_gui;
    }

    /**
    * Show the edit screen
    */
    public function viewObject(int $changesSuccessBool = 0): void
    {
        global $DIC;
        $tpl = $DIC["tpl"];

        // get the view table
        $table_gui = $this->getViewTable();

        // get the remarks in database
        $comments = $this->object->getAllRemarks();

        $compare_comments = [];
        $missing_entries = [];

        // set the language to compare with
        // get the default values if the compare language is the same
        $compare = $table_gui->getFilterItemByPostVar("compare")->getValue();
        if ($compare == $this->object->key) {
            $compare_object = $this->object->getGlobalLanguageFile();
            $compare_content = $compare_object->getAllValues();
            $compare_comments = $compare_object->getAllComments();
        }

        // page translation mode:
        // - the table is filtered by a list of modules and topics
        if (ilObjLanguageAccess::_isPageTranslation()) {
            // get the selection of modules and topics from request or session
            $modules = ilObjLanguageAccess::_getSavedModules();
            $topics = ilObjLanguageAccess::_getSavedTopics();

            $reset_offset_get = false;
            if ($this->http->wrapper()->query()->has("reset_offset")) {
                $reset_offset_get = $this->http->wrapper()->query()->retrieve(
                    "reset_offset",
                    $this->refinery->kindlyTo()->bool()
                );
            }

            // first call for translation
            if ($reset_offset_get) {
                $table_gui->resetOffset();
            }

            if (!isset($compare_content)) {
                $compare_content = ilObjLanguageExt::_getValues(
                    $compare,
                    $modules,
                    $topics
                );

                $compare_comments = ilObjLanguageExt::_getRemarks($compare);
            }

            $translations = ilObjLanguageExt::_getValues(
                $this->object->key,
                $modules,
                $topics
            );

            // enable adding new entries
            $db_found = array();
            foreach ($translations as $name => $translation) {
                $keys = explode($this->lng->separator, $name);
                $db_found[] = $keys[1];
            }
            $missing_entries = array_diff($topics, $db_found);
        } else { // normal view mode:
            // - the table is filtered manually by module, mode and pattern
            $filter_mode = $table_gui->getFilterItemByPostVar("mode")->getValue();
            $filter_pattern = $table_gui->getFilterItemByPostVar("pattern")->getValue();
            $filter_module = $table_gui->getFilterItemByPostVar("module")->getValue();
            $filter_module = $filter_module === "all" ? "" : $filter_module;
            $filter_modules = $filter_module ? array($filter_module) : array();
            $filter_identifier = $table_gui->getFilterItemByPostVar("identifier")->getValue();
            $filter_topics = $filter_identifier ? array($filter_identifier) : array();

            if (!isset($compare_content)) {
                $compare_content = ilObjLanguageExt::_getValues(
                    $compare,
                    $filter_modules,
                    $filter_topics
                );

                $compare_comments = ilObjLanguageExt::_getRemarks($compare);
            }

            switch ($filter_mode) {
                case "changed":
                    $translations = $this->object->getChangedValues(
                        $filter_modules,
                        $filter_pattern,
                        $filter_topics
                    );
                    break;

                case "added":   //langmode only
                    $translations = $this->object->getAddedValues(
                        $filter_modules,
                        $filter_pattern,
                        $filter_topics
                    );
                    break;

                case "unchanged":
                    $translations = $this->object->getUnchangedValues(
                        $filter_modules,
                        $filter_pattern,
                        $filter_topics
                    );
                    break;

                case "commented":
                    $translations = $this->object->getCommentedValues(
                        $filter_modules,
                        $filter_pattern,
                        $filter_topics
                    );
                    break;

                case "dbremarks":
                    $translations = $this->object->getAllValues(
                        $filter_modules,
                        $filter_pattern,
                        $filter_topics
                    );

                    $translations = array_intersect_key($translations, $comments);
                    break;

                case "equal":
                    $translations = $this->object->getAllValues(
                        $filter_modules,
                        $filter_pattern,
                        $filter_topics
                    );

                    $translations = array_intersect_assoc($translations, $compare_content);
                    break;

                case "different":
                    $translations = $this->object->getAllValues(
                        $filter_modules,
                        $filter_pattern,
                        $filter_topics
                    );

                    $translations = array_diff_assoc($translations, $compare_content);
                    break;

                case "conflicts":
                    $former_file = $this->object->getDataPath() . "/ilias_" . $this->object->key . ".lang";
                    if (!is_readable($former_file)) {
                        $this->tpl->setOnScreenMessage('failure', sprintf($this->lng->txt("language_former_file_missing"), $former_file)
                                        . '<br />' . $this->lng->txt("language_former_file_description"), false);
                        $translations = array();
                        break;
                    }
                    $global_file_obj = $this->object->getGlobalLanguageFile();
                    $former_file_obj = new ilLanguageFile($former_file);
                    $former_file_obj->read();
                    $global_changes = array_diff_assoc(
                        $global_file_obj->getAllValues(),
                        $former_file_obj->getAllValues()
                    );
                    if (!count($global_changes)) {
                        $this->tpl->setOnScreenMessage('info', sprintf($this->lng->txt("language_former_file_equal"), $former_file)
                                        . '<br />' . $this->lng->txt("language_former_file_description"), false);
                        $translations = array();
                        break;
                    }
                    $translations = $this->object->getChangedValues(
                        $filter_modules,
                        $filter_pattern,
                        $filter_topics
                    );

                    $translations = array_intersect_key($translations, $global_changes);
                    break;

                case "all":
                default:
                    $translations = $this->object->getAllValues(
                        $filter_modules,
                        $filter_pattern,
                        $filter_topics
                    );
            }
        }

        // prepare the the data for the table
        $data = array();
        foreach ($translations as $name => $translation) {
            $keys = explode($this->lng->separator, $name);
            $row = array();

            $row["module"] = $keys[0];
            $row["topic"] = $keys[1];
            $row["name"] = $name;
            $row["translation"] = $translation;
            $row["comment"] = $comments[$name] ?? "";
            $row["default"] = $compare_content[$name] ?? "";
            $row["default_comment"] = $compare_comments[$name] ?? "";

            $data[] = $row;
        }

        if ($changesSuccessBool) {
            $tpl->setVariable("MESSAGE", $this->getSuccessMessage());
        }

        // render and show the table
        $table_gui->setData($data);
        $tpl->setContent($table_gui->getHTML() . $this->buildMissingEntries($missing_entries));
    }

    /**
     * Apply filter
     */
    public function applyFilterObject(): void
    {
        $table_gui = $this->getViewTable();
        $table_gui->writeFilterToSession();    // writes filter to session
        $table_gui->resetOffset();             // sets record offest to 0 (first page)
        $this->viewObject();
    }

    /**
     * Reset filter
     */
    public function resetFilterObject(): void
    {
        $table_gui = $this->getViewTable();
        $table_gui->resetOffset();                // sets record offest to 0 (first page)
        $table_gui->resetFilter();                // clears filter
        $this->viewObject();
    }

    /**
    * Save the changed translations
    */
    public function saveObject(): void
    {
        // no changes have been made yet
        $changesSuccessBool = 0;
        // prepare the values to be saved
        $save_array = array();
        $remarks_array = array();
        $post = (array) ($this->http->request()->getParsedBody() ?? []);
        foreach ($post as $key => $value) {
            // mantis #25237
            // @see https://php.net/manual/en/language.variables.external.php
            $key = str_replace(["_POSTDOT_", "_POSTSPACE_"], [".", " "], $key);

            // example key of variable: 'common#:#access'
            // example key of comment: 'common#:#access#:#comment'
            $keys = explode($this->lng->separator, ilUtil::stripSlashes($key));

            if (count($keys) === 2) {
                // avoid line breaks
                $value = preg_replace("/(\015\012)|(\015)|(\012)/", "<br />", $value);
                $value = str_replace("<<", "«", $value);
                $value = ilUtil::stripSlashes($value);
                $save_array[$key] = $value;

                // the comment has the key of the language with the suffix
                $remarks_array[$key] = $post[$key . $this->lng->separator . "comment"];
            }
        }

        // save the translations
        ilObjLanguageExt::_saveValues($this->object->key, $save_array, $remarks_array);

        // set successful changes bool to true;
        $changesSuccessBool = 1;

        // view the list
        $this->viewObject($changesSuccessBool);
    }

    /**
    * Show the screen to import a language file
    */
    public function importObject(): void
    {
        require_once("./Services/Form/classes/class.ilPropertyFormGUI.php");
        $form = new ilPropertyFormGUI();
        $form->setFormAction($this->ctrl->getFormAction($this));
        $form->setTitle($this->lng->txt("language_import_file"));
        $form->addCommandButton("upload", $this->lng->txt("upload"));

        $fu = new ilFileInputGUI($this->lng->txt("file"), "userfile");
        $form->addItem($fu);

        $rg = new ilRadioGroupInputGUI($this->lng->txt("language_mode_existing"), "mode_existing");
        $ro = new ilRadioOption($this->lng->txt("language_mode_existing_keepall"), "keepall");
        $ro->setInfo($this->lng->txt("language_mode_existing_keepall_info"));
        $rg->addOption($ro);
        $ro = new ilRadioOption($this->lng->txt("language_mode_existing_keepnew"), "keepnew");
        $ro->setInfo($this->lng->txt("language_mode_existing_keepnew_info"));
        $rg->addOption($ro);
        $ro = new ilRadioOption($this->lng->txt("language_mode_existing_replace"), "replace");
        $ro->setInfo($this->lng->txt("language_mode_existing_replace_info"));
        $rg->addOption($ro);
        $ro = new ilRadioOption($this->lng->txt("language_mode_existing_delete"), "delete");
        $ro->setInfo($this->lng->txt("language_mode_existing_delete_info"));
        $rg->addOption($ro);
        $rg->setValue($this->getSession()["import"]["mode_existing"] ?? "keepall");
        $form->addItem($rg);

        $this->tpl->setContent($form->getHTML());
    }


    /**
    * Process an uploaded language file
    */
    public function uploadObject(): void
    {
        global $DIC;

        $post_mode_existing = $this->http->request()->getParsedBody()['mode_existing'] ?? "";
        // save form inputs for next display
        $tmp["import"]["mode_existing"] = ilUtil::stripSlashes($post_mode_existing);
        ilSession::set("lang_ext_maintenance", $tmp);

        try {
            $upload = $DIC->upload();
            $upload->process();

            if (!$upload->hasUploads()) {
                throw new ilException($DIC->language()->txt("upload_error_file_not_found"));
            }
            $UploadResult = $upload->getResults()[$_FILES["userfile"]["tmp_name"]];

            $ProcessingStatus = $UploadResult->getStatus();
            if ($ProcessingStatus->getCode() === ProcessingStatus::REJECTED) {
                throw new ilException($ProcessingStatus->getMessage());
            }

            // todo: refactor when importLanguageFile() is able to work with the new Filesystem service
            $tempfile = ilFileUtils::ilTempnam() . ".sec";
            $upload->moveOneFileTo($UploadResult, '', Location::TEMPORARY, basename($tempfile), true);
            $this->object->importLanguageFile($tempfile, $post_mode_existing);

            $tempfs = $DIC->filesystem()->temp();
            $tempfs->delete(basename($tempfile));
        } catch (Exception $e) {
            $this->tpl->setOnScreenMessage('failure', $e->getMessage(), true);
            $this->ctrl->redirect($this, 'import');
        }

        $this->tpl->setOnScreenMessage('success', sprintf($this->lng->txt("language_file_imported"), $_FILES["userfile"]["name"]), true);
        $this->ctrl->redirect($this, "import");
    }


    /**
    * Show the screen to export a language file
    */
    public function exportObject(): void
    {
        require_once("./Services/Form/classes/class.ilPropertyFormGUI.php");
        $form = new ilPropertyFormGUI();
        $form->setFormAction($this->ctrl->getFormAction($this));
        $form->setTitle($this->lng->txt("language_export_file"));
        $form->setPreventDoubleSubmission(false);
        $form->addCommandButton("download", $this->lng->txt("download"));

        $rg = new ilRadioGroupInputGUI($this->lng->txt("language_file_scope"), "scope");
        $ro = new ilRadioOption($this->lng->txt("language_scope_global"), "global");
        $ro->setInfo($this->lng->txt("language_scope_global_info"));
        $rg->addOption($ro);
        $ro = new ilRadioOption($this->lng->txt("language_scope_local"), "local");
        $ro->setInfo($this->lng->txt("language_scope_local_info"));
        $rg->addOption($ro);
        if ($this->langmode) {
            $ro = new ilRadioOption($this->lng->txt("language_scope_added"), "added");
            $ro->setInfo($this->lng->txt("language_scope_added_info"));
            $rg->addOption($ro);
        }
        $ro = new ilRadioOption($this->lng->txt("language_scope_unchanged"), "unchanged");
        $ro->setInfo($this->lng->txt("language_scope_unchanged_info"));
        $rg->addOption($ro);
        if ($this->langmode) {
            $ro = new ilRadioOption($this->lng->txt("language_scope_merged"), "merged");
            $ro->setInfo($this->lng->txt("language_scope_merged_info"));
            $rg->addOption($ro);
        }

        $rg->setValue($this->getSession()["export"]["scope"] ?? "global");
        $form->addItem($rg);

        $this->tpl->setContent($form->getHTML());
    }

    /**
    * Download a language file
    */
    public function downloadObject(): void
    {
        $post_scope = $this->http->request()->getParsedBody()['scope'] ?? "";
        // save the selected scope
        $tmp["export"]["scope"] = ilUtil::stripSlashes($post_scope);
        ilSession::set("lang_ext_maintenance", $tmp);

        $filename = "ilias_" . $this->object->key . '_'
        . str_replace(".", "_", substr(ILIAS_VERSION, 0, strpos(ILIAS_VERSION, " ")))
        . "-" . date("Y-m-d")
        . ".lang." . $this->getSession()["export"]["scope"];

        $global_file_obj = $this->object->getGlobalLanguageFile();
        $local_file_obj = new ilLanguageFile($filename, $this->object->key, $post_scope);

        if ($post_scope === "global") {
            $local_file_obj->setParam("author", $global_file_obj->getParam("author"));
            $local_file_obj->setParam("version", $global_file_obj->getParam("version"));
            $local_file_obj->setAllValues($this->object->getAllValues());
            if ($this->langmode) {
                $local_file_obj->setAllComments($this->object->getAllRemarks());
            }
        } elseif ($post_scope === "local") {
            $local_file_obj->setParam("based_on", $global_file_obj->getParam("version"));
            $local_file_obj->setAllValues($this->object->getChangedValues());
            if ($this->langmode) {
                $local_file_obj->setAllComments($this->object->getAllRemarks());
            }
        } elseif ($post_scope === "added") { // langmode only
            $local_file_obj->setParam("author", $global_file_obj->getParam("author"));
            $local_file_obj->setParam("version", $global_file_obj->getParam("version"));
            $local_file_obj->setAllValues($this->object->getAddedValues());
            $local_file_obj->setAllComments($this->object->getAllRemarks());
        } elseif ($post_scope === "unchanged") {
            $local_file_obj->setParam("author", $global_file_obj->getParam("author"));
            $local_file_obj->setParam("version", $global_file_obj->getParam("version"));
            $local_file_obj->setAllValues($this->object->getUnchangedValues());
            if ($this->langmode) {
                $local_file_obj->setAllComments($this->object->getAllRemarks());
            }
        } elseif ($post_scope === "merged") { // langmode only
            $local_file_obj->setParam("author", $global_file_obj->getParam("author"));
            $local_file_obj->setParam("version", $global_file_obj->getParam("version"));
            $local_file_obj->setAllValues($this->object->getMergedValues());
            $local_file_obj->setAllComments($this->object->getMergedRemarks());
        }

        ilUtil::deliverData($local_file_obj->build(), $filename);
    }


    /**
    * Process the language maintenance
    */
    public function maintainObject(): void
    {
        require_once("./Services/Form/classes/class.ilPropertyFormGUI.php");
        $form = new ilPropertyFormGUI();
        $form->setFormAction($this->ctrl->getFormAction($this));
        $form->setTitle($this->lng->txt("language_maintenance"));
        $form->setPreventDoubleSubmission(false);
        $form->addCommandButton("maintainExecute", $this->lng->txt("language_process_maintenance"));

        $rg = new ilRadioGroupInputGUI($this->lng->txt("language_maintain_local_changes"), "maintain");
        $ro = new ilRadioOption($this->lng->txt("language_load_local_changes"), "load");
        $ro->setInfo(sprintf($this->lng->txt("language_load_local_changes_info"), $this->object->key));
        $rg->addOption($ro);
        $ro = new ilRadioOption($this->lng->txt("language_clear_local_changes"), "clear");
        $ro->setInfo(sprintf($this->lng->txt("language_clear_local_changes_info"), $this->object->key));
        $rg->addOption($ro);
        if ($this->langmode) {
            $ro = new ilRadioOption($this->lng->txt("language_delete_local_additions"), "delete_added");
            $ro->setInfo(sprintf($this->lng->txt("language_delete_local_additions_info"), $this->object->key));
            $rg->addOption($ro);
            $ro = new ilRadioOption($this->lng->txt("language_remove_local_file"), "remove_local_file");
            $ro->setInfo(sprintf($this->lng->txt("language_remove_local_file_info"), $this->object->key));
            $rg->addOption($ro);
            $ro = new ilRadioOption($this->lng->txt("language_merge_local_changes"), "merge");
            $ro->setInfo(sprintf($this->lng->txt("language_merge_local_changes_info"), $this->object->key));
            $rg->addOption($ro);
        }
        $ro = new ilRadioOption($this->lng->txt("language_save_dist"), "save_dist");
        $ro->setInfo(sprintf($this->lng->txt("language_save_dist_info"), $this->object->key));
        $rg->addOption($ro);
        $rg->setValue($this->getSession()["maintain"] ?? "");
        $form->addItem($rg);

        $this->tpl->setContent($form->getHTML());
    }

    public function maintainExecuteObject(): void
    {
        $post_maintain = $this->http->request()->getParsedBody()['maintain'] ?? "";
        if (isset($post_maintain)) {
            $tmp["maintain"] = ilUtil::stripSlashes($post_maintain);
            ilSession::set("lang_ext_maintenance", $tmp);
        }

        switch ($post_maintain) {
            // save the global language file for merge after
            case "save_dist":
                // save a copy of the distributed language file
                $orig_file = $this->object->getLangPath() . "/ilias_" . $this->object->key . ".lang";
                $copy_file = $this->object->getDataPath() . "/ilias_" . $this->object->key . ".lang";
                if (@copy($orig_file, $copy_file)) {
                    $this->tpl->setOnScreenMessage('success', $this->lng->txt("language_saved_dist"), true);
                } else {
                    $this->tpl->setOnScreenMessage('failure', $this->lng->txt("language_save_dist_failed"), true);
                }
                break;

            // load the content of the local language file
            case "load":
                $lang_file = $this->object->getCustLangPath() . "/ilias_" . $this->object->key . ".lang.local";
                if (is_file($lang_file) and is_readable($lang_file)) {
                    $this->object->importLanguageFile($lang_file, "replace");
                    $this->object->setLocal(true);
                    $this->tpl->setOnScreenMessage('success', $this->lng->txt("language_loaded_local"), true);
                } else {
                    $this->tpl->setOnScreenMessage('failure', $this->lng->txt("language_error_read_local"), true);
                }
                break;

            // revert the database to the default language file
            case "clear":
                $lang_file = $this->object->getLangPath() . "/ilias_" . $this->object->key . ".lang";
                if (is_file($lang_file) and is_readable($lang_file)) {
                    $this->object->importLanguageFile($lang_file, "replace");
                    $this->object->setLocal(false);
                    $this->tpl->setOnScreenMessage('success', $this->lng->txt("language_cleared_local"), true);
                } else {
                    $this->tpl->setOnScreenMessage('failure', $this->lng->txt("language_error_clear_local"), true);
                }
                break;

            // delete local additions in the datavase (langmode only)
            case "delete_added":
                ilObjLanguageExt::_deleteValues($this->object->key, $this->object->getAddedValues());
                break;

            // merge local changes back to the global language file (langmode only)
            case "merge":
                $orig_file = $this->object->getLangPath() . "/ilias_" . $this->object->key . ".lang";
                $copy_file = $this->object->getCustLangPath() . "/ilias_" . $this->object->key . ".lang";

                if (is_file($orig_file) and is_writable($orig_file)) {
                    // save a copy of the global language file
                    @copy($orig_file, $copy_file);

                    // modify and write the new global file
                    $global_file_obj = $this->object->getGlobalLanguageFile();
                    $global_file_obj->setAllValues($this->object->getMergedValues());
                    $global_file_obj->setAllComments($this->object->getMergedRemarks());
                    $global_file_obj->write();
                    $this->tpl->setOnScreenMessage('success', $this->lng->txt("language_merged_global"), true);
                } else {
                    $this->tpl->setOnScreenMessage('failure', $this->lng->txt("language_error_write_global"), true);
                }
                break;

            // remove the local language file (langmode only)
            case "remove_local_file":
                $lang_file = $this->object->getCustLangPath() . "/ilias_" . $this->object->key . ".lang.local";

                if (!is_file($lang_file)) {
                    $this->object->setLocal(false);
                    $this->tpl->setOnScreenMessage('failure', $this->lng->txt("language_error_local_missed"), true);
                } elseif (@unlink($lang_file)) {
                    $this->object->setLocal(false);
                    $this->tpl->setOnScreenMessage('success', $this->lng->txt("language_local_file_deleted"), true);
                } else {
                    $this->tpl->setOnScreenMessage('failure', $this->lng->txt("language_error_delete_local"), true);
                }
                break;
        }

        $this->ctrl->redirect($this, "maintain");
    }

    /**
    * Set the language settings
    */
    public function settingsObject(): void
    {
        global $DIC;
        $ilSetting = $DIC->settings();

        $translate_key = "lang_translate_" . $this->object->key;

        $post_translation = $this->http->request()->getParsedBody()['translation'] ?? "";
        // save and get the page translation setting
        if (!empty($post_translation)) {
            $ilSetting->set($translate_key, $post_translation);
            $this->tpl->setOnScreenMessage('success', $this->lng->txt("settings_saved"));
        }
        $translate = (bool) $ilSetting->get($translate_key, '0');

        require_once("./Services/Form/classes/class.ilPropertyFormGUI.php");
        $form = new ilPropertyFormGUI();
        $form->setFormAction($this->ctrl->getFormAction($this));
        $form->setTitle($this->lng->txt("language_settings"));
        $form->setPreventDoubleSubmission(false);
        $form->addCommandButton('settings', $this->lng->txt("language_change_settings"));

        $ci = new ilCheckboxInputGUI($this->lng->txt("language_translation_enabled"), "translation");
        $ci->setChecked($translate);
        $ci->setInfo($this->lng->txt("language_note_translation"));
        $form->addItem($ci);

        $this->tpl->setContent($form->getHTML());
    }

    /**
    * Print out statistics about the language
    */
    public function statisticsObject(): void
    {
        $modules = ilObjLanguageExt::_getModules($this->object->key);

        $data = [];
        $total = [];
        foreach ($modules as $module) {
            $row = [];
            $row["module"] = $module;
            $row["all"] = count($this->object->getAllValues(array($module)));
            $row["changed"] = count($this->object->getChangedValues(array($module)));
            $row["unchanged"] = $row["all"] - $row["changed"];
            isset($total["all"]) ? $total["all"] += $row["all"] : $total["all"] = $row["all"];
            isset($total["changed"]) ? $total["changed"] += $row["changed"] : $total["changed"] = $row["changed"];
            isset($total["unchanged"]) ? $total["unchanged"] += $row["unchanged"] : $total["unchanged"] = $row["unchanged"];
            $data[] = $row;
        }
        $total["module"] = "<b>" . $this->lng->txt("language_all_modules") . "</b>";
        $total["all"] = "<b>" . $total["all"] . "</b>";
        $total["changed"] = "<b>" . $total["changed"] . "</b>";
        $total["unchanged"] = "<b>" . $total["unchanged"] . "</b>";
        $data[] = $total;

        // create and configure the table object
        include_once "Services/Table/classes/class.ilTable2GUI.php";
        $table_gui = new ilTable2GUI($this, "statistics");
        $table_gui->setRowTemplate("tpl.lang_statistics_row.html", "Services/Language");
        $table_gui->setEnableTitle(false);
        $table_gui->setEnableNumInfo(false);
        $table_gui->setLimit(count($data));
        $table_gui->setExportFormats(array(ilTable2GUI::EXPORT_EXCEL));

        $table_gui->addColumn(ucfirst($this->lng->txt("module")), "", "25%");
        $table_gui->addColumn($this->lng->txt("language_scope_global"), "", "25%");
        $table_gui->addColumn($this->lng->txt("language_scope_local"), "", "25%");
        $table_gui->addColumn($this->lng->txt("language_scope_unchanged"), "", "25%");

        $table_gui->setData($data);

        $this->tpl->setContent($table_gui->getHTML());
    }

    /**
     * Get tabs for admin mode
     *(Overwritten from ilObjectGUI, called by prepareOutput)
     */
    public function getAdminTabs(): void
    {
        global $DIC;
        $ilCtrl = $DIC->ctrl();
        $cmd = $ilCtrl->getCmd();

        if (!ilObjLanguageAccess::_isPageTranslation()) {
            $this->tabs_gui->setBackTarget(
                $this->lng->txt("back"),
                $this->ctrl->getLinkTargetByClass("ilObjLanguageFolderGUI")
            );

            $this->tabs_gui->addTab(
                "edit",
                $this->lng->txt("edit"),
                $this->ctrl->getLinkTarget($this, "view")
            );

            $this->tabs_gui->addTab(
                "export",
                $this->lng->txt('export'),
                $this->ctrl->getLinkTarget($this, "export")
            );

            $this->tabs_gui->addTab(
                "import",
                $this->lng->txt("import"),
                $this->ctrl->getLinkTarget($this, "import")
            );

            $this->tabs_gui->addTab(
                "maintain",
                $this->lng->txt("language_maintain"),
                $this->ctrl->getLinkTarget($this, "maintain")
            );

            $this->tabs_gui->addTab(
                "settings",
                $this->lng->txt("settings"),
                $this->ctrl->getLinkTarget($this, "settings")
            );

            $this->tabs_gui->addTab(
                "statistics",
                $this->lng->txt("language_statistics"),
                $this->ctrl->getLinkTarget($this, "statistics")
            );

            switch ($cmd) {
                case "":
                case "view":
                case "applyFilter":
                case "resetFilter":
                case "save":
                    $this->tabs_gui->activateTab("edit");
                    break;
                default:
                    $this->tabs_gui->activateTab($cmd);
            }
        }
    }

    /**
     * Set the locator for admin mode
     *(Overwritten from ilObjectGUI, called by prepareOutput)
     */
    protected function addAdminLocatorItems(bool $do_not_add_object = false): void
    {
        global $DIC;
        $ilLocator = $DIC["ilLocator"];

        if (!ilObjLanguageAccess::_isPageTranslation()) {
            parent::addAdminLocatorItems(true); // #13881

            $ilLocator->addItem(
                $this->lng->txt("languages"),
                $this->ctrl->getLinkTargetByClass("ilobjlanguagefoldergui", "")
            );

            $ilLocator->addItem(
                $this->lng->txt("meta_l_" . $this->object->getTitle()),
                $this->ctrl->getLinkTarget($this, "view")
            );
        }
    }

    /**
     * Set the Title and the description
     * (Overwritten from ilObjectGUI, called by prepareOutput)
     */
    protected function setTitleAndDescription(): void
    {
        if (ilObjLanguageAccess::_isPageTranslation()) {
            $this->tpl->setHeaderPageTitle($this->lng->txt("translation"));
            $this->tpl->setTitle($this->lng->txt("translation") . " " . $this->lng->txt("meta_l_" . $this->object->key));
        } else {
            $this->tpl->setTitle($this->lng->txt("meta_l_" . $this->object->key));
        }
        $this->tpl->setTitleIcon(ilUtil::getImagePath("icon_lngf.svg"), $this->lng->txt("obj_" . $this->object->getType()));
    }


    //
    // new entries
    //

    protected function buildMissingEntries(array $a_missing = null): string
    {
        global $DIC;
        $ilCtrl = $DIC->ctrl();

        if (!count($a_missing)) {
            return '';
        }

        $res = array("<h3>" . $this->lng->txt("adm_missing_entries") . "</h3>", "<ul>");

        foreach ($a_missing as $entry) {
            $ilCtrl->setParameter($this, "eid", $entry);
            $res[] = '<li>' . $entry .
                ' <a href="' . $ilCtrl->getLinkTarget($this, "addNewEntry") .
                '">' . $this->lng->txt("adm_missing_entry_add_action") . '</a></li>';
            $ilCtrl->setParameter($this, "eid", "");
        }

        $res[] = "</ul>";

        return implode("\n", $res);
    }

    public function addNewEntryObject(ilPropertyFormGUI $a_form = null): void
    {
        global $DIC;
        $tpl = $DIC["tpl"];

        $id = "";
        if ($this->http->wrapper()->query()->has("eid")) {
            $id = trim($this->http->wrapper()->query()->retrieve("eid", $this->refinery->kindlyTo()->string()));
        }
        if (!$a_form) {
            $a_form = $this->initAddNewEntryForm($id);
        }

        $tpl->setContent($a_form->getHTML());
    }

    protected function initAddNewEntryForm(string $a_id = null): ilPropertyFormGUI
    {
        global $DIC;
        $ilCtrl = $DIC->ctrl();

        if (!$a_id) {
            $a_id = $this->http->request()->getParsedBody()['id'] ?? "";
        }

        if (!$a_id ||
            !in_array($a_id, ilObjLanguageAccess::_getSavedTopics())) {
            $ilCtrl->redirect($this, "view");
        }

        include_once "Services/Form/classes/class.ilPropertyFormGUI.php";
        $form = new ilPropertyFormGUI();
        $form->setFormAction($ilCtrl->getFormAction($this, "saveNewEntry"));
        $form->setTitle($this->lng->txt("adm_missing_entry_add"));

        $mods = ilObjLanguageAccess::_getSavedModules();
        $options = array_combine($mods, $mods);

        $mod = new ilSelectInputGUI(ucfirst($this->lng->txt("module")), "mod");
        $mod->setOptions(array("" => $this->lng->txt("please_select")) + $options);
        $mod->setRequired(true);
        $form->addItem($mod);

        $id = new ilTextInputGUI(ucfirst($this->lng->txt("identifier")), "id");
        $id->setValue($a_id);
        $id->setDisabled(true);
        $form->addItem($id);

        foreach ($this->lng->getInstalledLanguages() as $lang_key) {
            $trans = new ilTextInputGUI($this->lng->txt("meta_l_" . $lang_key), "trans_" . $lang_key);
            if (in_array($lang_key, array("de", "en"))) {
                $trans->setRequired(true);
            }
            $form->addItem($trans);
        }

        $form->addCommandButton("saveNewEntry", $this->lng->txt("save"));
        $form->addCommandButton("view", $this->lng->txt("cancel"));

        return $form;
    }

    public function saveNewEntryObject(): void
    {
        global $DIC;
        $ilDB = $DIC->database();
        $ilCtrl = $DIC->ctrl();
        $ilUser = $DIC->user();

        $form = $this->initAddNewEntryForm();
        if ($form->checkInput()) {
            $mod = $form->getInput("mod");
            $id = $form->getInput("id");

            $lang = array();
            foreach ($this->lng->getInstalledLanguages() as $lang_key) {
                $trans = trim($form->getInput("trans_" . $lang_key));
                if ($trans) {
                    // add single entry
                    ilObjLanguage::replaceLangEntry(
                        $mod,
                        $id,
                        $lang_key,
                        $trans,
                        date("Y-m-d H:i:s"),
                        $ilUser->getLogin()
                    );

                    // add to serialized module
                    $set = $ilDB->query("SELECT lang_array FROM lng_modules" .
                        " WHERE lang_key = " . $ilDB->quote($lang_key, "text") .
                        " AND module = " . $ilDB->quote($mod, "text"));
                    $row = $ilDB->fetchAssoc($set);
                    $entries = unserialize($row["lang_array"], ["allowed_classes" => false]);
                    if (is_array($entries)) {
                        $entries[$id] = $trans;
                        ilObjLanguage::replaceLangModule($lang_key, $mod, $entries);
                    }
                }
            }

            $this->tpl->setOnScreenMessage('success', $this->lng->txt("settings_saved"), true);
            $ilCtrl->redirect($this, "view");
        }

        $form->setValuesByPost();
        $this->addNewEntryObject($form);
    }

    /**
     * Get success message after variables were saved
     */
    protected function getSuccessMessage(): string
    {
        global $DIC;
        $f = $DIC->ui()->factory();
        $renderer = $DIC->ui()->renderer();

        return $renderer->render($f->messageBox()->success($this->lng->txt("language_variables_saved")));
    }

    private function getSession(): array
    {
        return ilSession::get("lang_ext_maintenance") ?? [];
    }
} // END class.ilObjLanguageExtGUI
