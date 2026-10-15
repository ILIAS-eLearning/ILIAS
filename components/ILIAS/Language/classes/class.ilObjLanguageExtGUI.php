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

use ILIAS\Language\ComponentTranslation\PlainLogText;
use ILIAS\Language\ComponentTranslation\InvalidMarkupMessage;
use ILIAS\Language\ComponentTranslation\PluralFormKey;
use ILIAS\FileUpload\DTO\ProcessingStatus;
use ILIAS\FileUpload\Location;
use ILIAS\HTTP\Services as HTTPServices;
use ILIAS\Refinery\Factory as Refinery;
use ILIAS\Language\Activities\AddLanguageEntry;
use ILIAS\Language\Activities\RemoveLocalLanguageChanges;
use ILIAS\Language\Activities\SetLanguageTranslationEnabled;
use ILIAS\Language\RendersActivityErrors;

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
class ilObjLanguageExtGUI extends ilObjectGUI implements ilCtrlSecurityInterface
{
    use RendersActivityErrors;

    private const string ILIAS_LANGUAGE_MODULE = "components/ILIAS/Language";
    private string $langmode;
    private readonly AddLanguageEntry $add_language_entry;
    private readonly SetLanguageTranslationEnabled $set_language_translation_enabled;
    private readonly RemoveLocalLanguageChanges $remove_local_language_changes;

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
        $this->http = $DIC['http'];
        $this->refinery = $DIC['refinery'];
        $this->add_language_entry = $DIC[AddLanguageEntry::class];
        $this->set_language_translation_enabled = $DIC[SetLanguageTranslationEnabled::class];
        $this->remove_local_language_changes = $DIC[RemoveLocalLanguageChanges::class];
        // used by activityErrorMessage() (see RendersActivityErrors)
        $this->activity_error_logger = $DIC->logger()->lang();

        // language maintenance strings are defined in administration
        $lng->loadLanguageModule("administration");
        $lng->loadLanguageModule("meta");
        $lng->loadLanguageModule("lng");

        //  view mode ('translate' or empty) determins available table filters
        $ilCtrl->saveParameter($this, "view_mode");

        // type and id of get the bound object
        $this->type = "lng";
        $obj_id_get = 0;

        if ($this->http->wrapper()->query()->has("obj_id")) {
            $obj_id_get = $this->http->wrapper()->query()->retrieve("obj_id", $this->refinery->kindlyTo()->int());
        } elseif ($this->http->wrapper()->query()->has("language_folder_obj_ids")) {
            $obj_id_get = $this->http->wrapper()->query()->retrieve("language_folder_obj_ids", $this->refinery->kindlyTo()->int());
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
     * Sent by GET (e.g. a crafted link) these commands need the CSRF token as well; ilCtrl appends it to their link targets.
     *
     * @return list<string>
     */
    public function getUnsafeGetCommands(): array
    {
        return ['save', 'upload', 'maintainExecute', 'saveSettings', 'saveNewEntry'];
    }

    /**
     * @return list<string>
     */
    public function getSafePostCommands(): array
    {
        return [];
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
    protected function getViewTable(): ilLanguageExtTableGUI
    {
        // create and configure the table object
        $table_gui = new ilLanguageExtTableGUI($this, "view", array(
            "langmode" => $this->langmode,
            "lang_key" => $this->object->key,
        ));

        return $table_gui;
    }

    /**
    * Show the edit screen
    */
    /**
     * @param list<string> $modules_with_unwritten_overlay after saving: modules whose PO/MO files could not be written
     *        (see ilObjLanguageExt::_saveValues()); a warning replaces the success message
     * @param list<string> $additional_failure_messages HTML, shown together with the failure messages of this view
     * @param array<string, string> $posted_values module.separator.topic => value shown instead of the stored one (rejected save)
     * @param array<string, string> $posted_remarks module.separator.topic => remark, like $posted_values
     */
    public function viewObject(
        int $changesSuccessBool = 0,
        array $modules_with_unwritten_overlay = [],
        array $additional_failure_messages = [],
        array $posted_values = [],
        array $posted_remarks = []
    ): void {
        global $DIC;
        $tpl = $DIC["tpl"];

        // get the view table
        $table_gui = $this->getViewTable();

        // see ilObjLanguageExt::getEffectiveRemarks()
        $comments = $this->object->getEffectiveRemarks();

        $compare_comments = [];
        $missing_entries = [];

        // set the language to compare with
        // get the default values if the compare language is the same
        $compare = $table_gui->getFilterItemByPostVar("compare")->getValue();
        if ($compare == $this->object->key) {
            $compare_content = $this->object->getShippedValues();
            $compare_comments = $this->object->getShippedCommentsForDisplay();
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

                $compare_comments = $this->compareComments($compare, $modules);
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
                // the forms of a plural message are its topic (see PluralFormKey)
                $db_found[] = PluralFormKey::parse($keys[1])[0] ?? $keys[1];
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

                $compare_comments = $this->compareComments($compare, $filter_modules);
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

                    // only local remarks, no shipped notes
                    $local_remarks = $this->object->getAllRemarks();
                    $translations = array_filter(
                        $translations,
                        fn(int|string $name): bool => $this->remarkOf($local_remarks, (string) $name) !== '',
                        ARRAY_FILTER_USE_KEY
                    );
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
                    $former_file_obj = new ilLanguageFile($former_file);
                    $former_file_obj->read();
                    // PO modules are compared with the backup of their shipped .po (written by "save_dist")
                    $global_changes = $this->object->getShippedChangesSinceBackup(
                        $former_file_obj,
                        $this->object->getDataPath()
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
            $row["translation"] = $posted_values[$name] ?? $translation;
            $row["comment"] = $posted_remarks[$name] ?? $this->remarkOf($comments, (string) $name);
            $row["default"] = $compare_content[$name] ?? "";
            $row["default_comment"] = $this->remarkOf($compare_comments, (string) $name);

            $data[] = $row;
        }

        // one message: setOnScreenMessage() only keeps one per type
        $failure_messages = $additional_failure_messages;
        if ($changesSuccessBool && $modules_with_unwritten_overlay !== []) {
            $failure_messages[] = $this->overlayNotWrittenMessage($modules_with_unwritten_overlay);
        } elseif ($changesSuccessBool) {
            $tpl->setVariable("MESSAGE", $this->getSuccessMessage());
        }
        if (($unreadable_modules = $this->object->getUnreadableShippedPoModules()) !== []) {
            $failure_messages[] = sprintf(
                $this->lng->txt("lng_shipped_po_unreadable_fallback"),
                implode(', ', $unreadable_modules)
            );
        }
        if ($failure_messages !== []) {
            $this->tpl->setOnScreenMessage('failure', implode('<br />', $failure_messages));
        }

        // render and show the table
        $table_gui->setData($data);
        $tpl->setContent($table_gui->getHTML() . $this->buildMissingEntries($missing_entries));
    }

    /**
     * Remarks (lng_data and overlay) plus, for PO modules without a remark, the shipped notes and "not translated yet"
     * marker of the shipped .po (see ilObjLanguageExt::_getShippedMigratedComments()).
     *
     * @param list<string> $modules the displayed modules, all if empty
     * @return array<string, string> module.separator.topic => comment
     */
    private function compareComments(string $compare, array $modules = []): array
    {
        return array_merge(
            ilObjLanguageExt::_getShippedMigratedComments($compare, $modules),
            array_filter(
                ilObjLanguageExt::_getRemarks($compare),
                static fn(?string $remark): bool => $remark !== null && $remark !== ''
            )
        );
    }

    /**
     * A form of a plural message without a comment of its own gets the one of its identifier (see PluralFormKey).

     *
     * @param array<string, string|null> $comments
     */
    private function remarkOf(array $comments, string $name): string
    {
        if (($comments[$name] ?? '') !== '') {
            return (string) $comments[$name];
        }
        $keys = explode($this->lng->separator, $name, 2);
        $identifier = PluralFormKey::parse($keys[1] ?? '')[0] ?? null;

        return $identifier === null ? '' : (string) ($comments[$keys[0] . $this->lng->separator . $identifier] ?? '');
    }

    /**
     * Maintenance action "clear": the same RemoveLocalLanguageChanges as "remove local changes" in the language list.
     */
    private function clearLocalChanges(): void
    {
        $result = $this->remove_local_language_changes->maybePerformAs(
            $this->ui_factory->input(),
            $this->user->getId(),
            ['language_keys' => [$this->object->key]]
        );
        if ($result->isError()) {
            $this->tpl->setOnScreenMessage(
                'failure',
                $this->lng->txt("language_error_clear_local") . '<br />' . $this->activityErrorMessage($result->error()),
                true
            );
            return;
        }
        $value = $result->value();

        if ($value['removed_local_changes_language_keys'] === []) {
            // not installed or an invalid language file/shipped .po: nothing was changed
            $unreadable_modules = $this->object->getUnreadableShippedPoModules();
            $this->tpl->setOnScreenMessage(
                'failure',
                $unreadable_modules !== []
                    ? sprintf($this->lng->txt("lng_error_clear_shipped_po_unreadable"), implode(', ', $unreadable_modules))
                    : $this->lng->txt("language_error_clear_local"),
                true
            );
            return;
        }
        if (($value['overlay_write_failed_language_keys'] ?? []) !== []) {
            $this->tpl->setOnScreenMessage(
                'failure',
                $this->lng->txt("language_cleared_local") . '<br />' . sprintf(
                    $this->lng->txt("lng_po_overlay_not_written_languages"),
                    $this->lng->txt("meta_l_" . $this->object->key)
                ),
                true
            );
            return;
        }
        $this->tpl->setOnScreenMessage('success', $this->lng->txt("language_cleared_local"), true);
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
            $orginal_key = $key;
            // mantis #25237
            // @see https://php.net/manual/en/language.variables.external.php
            $key = str_replace(
                ["_POSTDOT_", "_POSTSPACE_", "_POSTLBRACKET_", "_POSTRBRACKET_"],
                [".", " ", "[", "]"],
                $key
            );

            // example key of variable: 'common#:#access'
            // example key of comment: 'common#:#access#:#comment'
            $keys = explode($this->lng->separator, ilUtil::stripSlashes($key));

            // only strings: a parameter sent as array (e.g. "common#:#access[]") is no value
            if (count($keys) === 2 && is_string($value)) {
                // avoid line breaks
                $value = preg_replace("/(\015\012)|(\015)|(\012)/", "<br />", $value);
                $value = str_replace("<<", "«", $value);
                // markup is checked below (TranslationMarkupPolicy), not stripped
                $save_array[$key] = $value;

                // the comment has the key of the language with the suffix
                $remark = $post[$orginal_key . $this->lng->separator . "comment"] ?? null;
                $remarks_array[$key] = is_string($remark) ? $remark : null;
            }
        }

        // nothing is saved if any changed value has disallowed markup; all affected keys are named
        $invalid_values = ilObjLanguageExt::findInvalidMarkupOfChangedValues($this->object->key, $save_array);
        if ($invalid_values !== []) {
            // shown again with the input of this save, so nothing typed in is lost
            $this->viewObject(
                0,
                [],
                [$this->invalidMarkupMessage($invalid_values)],
                $save_array,
                array_map(
                    static fn(mixed $remark): string => (string) $remark,
                    array_filter($remarks_array, static fn(mixed $remark): bool => $remark !== null)
                )
            );
            return;
        }

        // save the translations
        $modules_with_unwritten_overlay = ilObjLanguageExt::_saveValues($this->object->key, $save_array, $remarks_array);

        // set successful changes bool to true;
        $changesSuccessBool = 1;

        // view the list
        $this->viewObject($changesSuccessBool, $modules_with_unwritten_overlay);
    }

    /**
    * Show the screen to import a language file
    */
    public function importObject(): void
    {
        $form = $this->initNewImportForm();
        $this->tpl->setContent($form->getHTML());
    }

    protected function initNewImportForm(): ilPropertyFormGUI
    {
        $form = new ilPropertyFormGUI();
        $form->setFormAction($this->ctrl->getFormAction($this));
        $form->setTitle($this->lng->txt("language_import_file"));
        $form->addCommandButton("upload", $this->lng->txt("upload"));

        $fu = new ilFileInputGUI($this->lng->txt("file"), "userfile");
        $fu->setRequired(true);
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

        return $form;
    }

    /**
    * Process an uploaded language file
    */
    public function uploadObject(): void
    {
        global $DIC;

        $form = $this->initNewImportForm();
        if ($form->checkInput()) {
            $post_mode_existing = $this->http->request()->getParsedBody()['mode_existing'] ?? "";
            // save form inputs for next display
            $tmp["import"]["mode_existing"] = ilUtil::stripSlashes($post_mode_existing);
            ilSession::set("lang_ext_maintenance", $tmp);
            try {
                $upload = $DIC->upload();
                //mantis #41617
                if (!$upload->hasBeenProcessed()) {
                    $upload->process();
                }


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
                try {
                    $modules_with_unwritten_overlay = $this->object->importLanguageFile($tempfile, $post_mode_existing);
                } finally {
                    $tempfs = $DIC->filesystem()->temp();
                    $tempfs->delete(basename($tempfile));
                }
            } catch (ilLanguageInvalidMarkupException $e) {
                $this->tpl->setOnScreenMessage('failure', $this->invalidMarkupMessage($e->getInvalidValues()), true);
                $this->ctrl->redirect($this, 'import');
            } catch (Exception $e) {
                $this->tpl->setOnScreenMessage('failure', $e->getMessage(), true);
                $this->ctrl->redirect($this, 'import');
            }

            $this->setSuccessOrOverlayWarning(
                $this->importedMessage((string) ($_FILES["userfile"]["name"] ?? '')),
                $modules_with_unwritten_overlay
            );
            $this->ctrl->redirect($this, "import");
        }

        $form->setValuesByPost();
        $this->tpl->setContent($form->getHTML());
    }

    /**
     * The English violations go to the log, see InvalidMarkupMessage.
     *
     * @param array<string, list<string>> $invalid_values key => violations
     */
    private function invalidMarkupMessage(array $invalid_values): string
    {
        global $DIC;

        $logged = [];
        foreach ($invalid_values as $key => $violations) {
            $logged[] = $violations === [] ? (string) $key : sprintf('%s (%s)', $key, implode('; ', $violations));
        }
        $DIC->logger()->forComponent('lang')->warning(PlainLogText::of(sprintf(
            'Language entries rejected or not applied, their value contains markup that is not allowed: %s',
            implode(', ', $logged)
        )));

        return InvalidMarkupMessage::of($this->lng, $this->refinery, $invalid_values);
    }

    /**
     * The client-supplied file name ends up in HTML, so it is escaped.
     */
    private function importedMessage(string $file_name): string
    {
        return sprintf(
            $this->lng->txt("language_file_imported"),
            $this->refinery->encode()->htmlSpecialCharsAsEntities()->transform($file_name)
        );
    }

    /**
    * Show the screen to export a language file
    */
    public function exportObject(): void
    {
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

        $pos = strpos(ILIAS_VERSION, " ");
        $pos = $pos === false ? null : $pos;
        $filename = "ilias_" . $this->object->key . '_'
        . str_replace(".", "_", substr(ILIAS_VERSION, 0, $pos))
        . "-" . gmdate("Y-m-d")
        . ".lang." . $this->getSession()["export"]["scope"];

        $global_file_obj = $this->object->getGlobalLanguageFile();
        $local_file_obj = new ilLanguageFile($filename, $this->object->key, $post_scope);

        if ($post_scope === "global") {
            $local_file_obj->setParam("author", $global_file_obj->getParam("author"));
            $local_file_obj->setParam("version", $global_file_obj->getParam("version"));
            $local_file_obj->setAllValues($this->object->getAllValues());
            if ($this->langmode) {
                $local_file_obj->setAllComments($this->object->getEffectiveRemarks());
            }
        } elseif ($post_scope === "local") {
            $local_file_obj->setParam("based_on", $global_file_obj->getParam("version"));
            $local_file_obj->setAllValues($this->object->getChangedValues());
            if ($this->langmode) {
                $local_file_obj->setAllComments($this->object->getEffectiveRemarks());
            }
        } elseif ($post_scope === "added") { // langmode only
            $local_file_obj->setParam("author", $global_file_obj->getParam("author"));
            $local_file_obj->setParam("version", $global_file_obj->getParam("version"));
            $local_file_obj->setAllValues($this->object->getAddedValues());
            $local_file_obj->setAllComments($this->object->getEffectiveRemarks());
        } elseif ($post_scope === "unchanged") {
            $local_file_obj->setParam("author", $global_file_obj->getParam("author"));
            $local_file_obj->setParam("version", $global_file_obj->getParam("version"));
            $local_file_obj->setAllValues($this->object->getUnchangedValues());
            if ($this->langmode) {
                $local_file_obj->setAllComments($this->object->getEffectiveRemarks());
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
        $rg->setValue($this->getSession()["maintain"] ?? "load");
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

        // developer-mode actions are also refused when sent directly
        if (in_array($post_maintain, ['delete_added', 'merge', 'remove_local_file'], true) && !$this->langmode) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt("permission_denied"), true);
            $this->ctrl->redirect($this, "maintain");
            return;
        }

        switch ($post_maintain) {
            // save the global language file for merge after
            case "save_dist":
                // save a copy of the distributed language file
                $orig_file = $this->object->getLangPath() . "/ilias_" . $this->object->key . ".lang";
                $copy_file = $this->object->getDataPath() . "/ilias_" . $this->object->key . ".lang";
                // the shipped .po of PO modules too, for the filter "conflicts"
                if (@copy($orig_file, $copy_file) && $this->object->backupShippedPoFiles($this->object->getDataPath()) === []) {
                    $this->tpl->setOnScreenMessage('success', $this->lng->txt("language_saved_dist"), true);
                } else {
                    $this->tpl->setOnScreenMessage('failure', $this->lng->txt("language_save_dist_failed"), true);
                }
                break;

                // load the content of the local language file
            case "load":
                $lang_file = $this->object->getCustLangPath() . "/ilias_" . $this->object->key . ".lang.local";
                if (is_file($lang_file) and is_readable($lang_file)) {
                    // like at installation: values with disallowed markup are left out (and named)
                    $modules_with_unwritten_overlay = $this->object->importLanguageFile($lang_file, "replace", true);
                    $this->object->setLocal(true);
                    $skipped = $this->object->getSkippedInvalidMarkupValues();
                    if ($skipped !== [] && $modules_with_unwritten_overlay === []) {
                        $this->tpl->setOnScreenMessage('success', $this->lng->txt("language_loaded_local"), true);
                        $this->tpl->setOnScreenMessage('failure', $this->invalidMarkupMessage($skipped), true);
                    } elseif ($skipped !== []) {
                        // one failure box only: overlay warning and left-out entries together
                        $this->tpl->setOnScreenMessage('failure', implode('<br />', [
                            $this->lng->txt("language_loaded_local"),
                            $this->overlayNotWrittenMessage($modules_with_unwritten_overlay),
                            $this->invalidMarkupMessage($skipped),
                        ]), true);
                    } else {
                        $this->setSuccessOrOverlayWarning($this->lng->txt("language_loaded_local"), $modules_with_unwritten_overlay);
                    }
                } else {
                    $this->tpl->setOnScreenMessage('failure', $this->lng->txt("language_error_read_local"), true);
                }
                break;

                // same Activity as ilObjLanguageFolderGUI::uninstallChangesObject()
            case "clear":
                $this->clearLocalChanges();
                break;

                // delete local additions in the datavase (langmode only)
            case "delete_added":
                $modules_with_unwritten_overlay = ilObjLanguageExt::_deleteValues(
                    $this->object->key,
                    $this->object->getAddedValues()
                );
                if ($modules_with_unwritten_overlay !== []) {
                    $this->tpl->setOnScreenMessage(
                        'failure',
                        $this->overlayNotWrittenMessage($modules_with_unwritten_overlay),
                        true
                    );
                }
                break;

                // merge local changes back to the global language file (langmode only)
            case "merge":
                $orig_file = $this->object->getLangPath() . "/ilias_" . $this->object->key . ".lang";
                $copy_file = $this->object->getCustLangPath() . "/ilias_" . $this->object->key . ".lang";

                if (is_file($orig_file) and is_writable($orig_file)) {
                    // save a copy of the global language file
                    @copy($orig_file, $copy_file);

                    // the shipped .po of PO modules is merged only on a single-client installation: it is shared by every client
                    $this->mergeMessages($this->object->mergeLocalChangesIntoGlobalLanguageFile());
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
     * View the language settings
     */
    public function settingsObject(): void
    {
        $form = $this->initNewSettingsForm();
        $this->tpl->setContent($form->getHTML());
    }

    /**
    * Set the language settings - see SetLanguageTranslationEnabled.
    *
    * An unchecked checkbox is not submitted, so a missing/empty "translation" means "disabled".
    */
    public function saveSettingsObject(): void
    {
        $ilUser = $this->user;

        $post_translation = $this->http->request()->getParsedBody()['translation'] ?? null;
        $enabled = $post_translation !== null && $post_translation !== '';

        $result = $this->set_language_translation_enabled->maybePerformAs(
            $this->ui_factory->input(),
            $ilUser->getId(),
            [
                'language_key' => $this->object->key,
                'enabled' => $enabled,
            ]
        );

        if ($result->isError()) {
            $error = $result->error();
            $error_message = $this->activityErrorMessage($error);

            $this->tpl->setOnScreenMessage('failure', $error_message);
        } elseif ($result->value()['changed']) {
            // only shown if the value changed
            $this->tpl->setOnScreenMessage('success', $this->lng->txt("settings_saved"));
        }

        $form = $this->initNewSettingsForm();

        // rendered directly with a non-persisted message, no redirect (unlike installObject() & Co.)
        $this->tpl->setContent($form->getHTML());
    }

    protected function initNewSettingsForm(): ilPropertyFormGUI
    {
        global $DIC;
        $ilSetting = $DIC->settings();
        $translate_key = "lang_translate_" . $this->object->key;
        $translate = (bool) $ilSetting->get($translate_key, '0');

        $form = new ilPropertyFormGUI();
        $form->setFormAction($this->ctrl->getFormAction($this));
        $form->setTitle($this->lng->txt("language_settings"));
        $form->setPreventDoubleSubmission(false);
        $form->addCommandButton('saveSettings', $this->lng->txt("language_change_settings"));

        $ci = new ilCheckboxInputGUI($this->lng->txt("language_translation_enabled"), "translation");
        $ci->setChecked($translate);
        $ci->setInfo($this->lng->txt("language_note_translation"));
        $form->addItem($ci);

        return $form;
    }

    /**
    * Print out statistics about the language
    */
    public function statisticsObject(): void
    {
        $statisticsTable = (new ilLanguageStatisticsTable(
            $this->object,
            $this->ui_factory,
            $this->lng
        ))->getTable();

        $this->tpl->setContent($this->ui_renderer->render($statisticsTable->withRequest($this->request)));
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

            $this->ctrl->setParameter($this, "obj_id", $this->id);
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
        $this->tpl->setTitleIcon(ilUtil::getImagePath("standard/icon_lngf.svg"), $this->lng->txt("obj_" . $this->object->getType()));
    }


    //
    // new entries
    //

    protected function buildMissingEntries(?array $a_missing = null): string
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

    public function addNewEntryObject(?ilPropertyFormGUI $a_form = null): void
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

    protected function initAddNewEntryForm(?string $a_id = null): ilPropertyFormGUI
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
        $ilCtrl = $this->ctrl;
        $ilUser = $this->user;

        $form = $this->initAddNewEntryForm();
        if ($form->checkInput()) {
            $mod = $form->getInput("mod");
            $id = $form->getInput("id");

            // a language left blank is skipped by AddLanguageEntry
            $translations = [];
            foreach ($this->lng->getInstalledLanguages() as $lang_key) {
                $translations[$lang_key] = trim((string) $form->getInput("trans_" . $lang_key));
            }

            $result = $this->add_language_entry->maybePerformAs(
                $this->ui_factory->input(),
                $ilUser->getId(),
                [
                    'module' => $mod,
                    'identifier' => $id,
                    'translations' => $translations,
                ]
            );

            if ($result->isError()) {
                $error = $result->error();
                $error_message = $this->activityErrorMessage($error);

                if ($this->isActivityInputError($error)) {
                    // input rejection (e.g. blank "de"/"en"): re-render the form, keeping the entered values
                    $this->tpl->setOnScreenMessage('failure', $error_message);
                    $form->setValuesByPost();
                    $this->addNewEntryObject($form);
                    return;
                }

                // unexpected failure (logged by activityErrorMessage()): redirect, like every other Activity error path
                $this->tpl->setOnScreenMessage('failure', $error_message, true);
                $ilCtrl->redirect($this, "view");
                return;
            }

            $overlay_write_failed_language_keys = $result->value()['overlay_write_failed_language_keys'] ?? [];
            if ($overlay_write_failed_language_keys !== []) {
                $this->tpl->setOnScreenMessage(
                    'failure',
                    sprintf(
                        $this->lng->txt("lng_po_overlay_not_written_languages"),
                        implode(', ', $overlay_write_failed_language_keys)
                    ),
                    true
                );
            } else {
                $this->tpl->setOnScreenMessage('success', $this->lng->txt("settings_saved"), true);
            }
            $ilCtrl->redirect($this, "view");
            return;
        }

        $form->setValuesByPost();
        $this->addNewEntryObject($form);
    }

    /**
     * A success message, or a warning if PO/MO files could not be written (the database was written, the web server
     * keeps serving the previous content).
     *
     * @param list<string> $modules_with_unwritten_overlay
     */
    private function setSuccessOrOverlayWarning(string $success_message, array $modules_with_unwritten_overlay): void
    {
        if ($modules_with_unwritten_overlay === []) {
            $this->tpl->setOnScreenMessage('success', $success_message, true);
            return;
        }
        $this->tpl->setOnScreenMessage(
            'failure',
            $success_message . '<br />' . $this->overlayNotWrittenMessage($modules_with_unwritten_overlay),
            true
        );
    }

    /**
     * Success, plus everything that failed or was left out in one failure message (setOnScreenMessage() keeps one
     * per type); details are in the log.
     *
     * @param array{written: list<string>, skipped: list<string>, invalid_markup: array<string, list<string>>, not_merged: list<string>, unwritten_database: list<string>} $result
     */
    private function mergeMessages(array $result): void
    {
        $escape = fn(string $text): string => $this->refinery->encode()->htmlSpecialCharsAsEntities()->transform($text);
        $success = $this->lng->txt("language_merged_global");
        if ($result['written'] !== []) {
            $success .= '<br />' . sprintf(
                $this->lng->txt("lng_merged_po_modules"),
                $escape(implode(', ', $result['written']))
            );
        }
        $this->tpl->setOnScreenMessage('success', $success, true);

        $failures = [];
        if ($result['skipped'] !== []) {
            // not written at all (e.g. no write permission): an error, not an intended skip
            $failures[] = sprintf(
                $this->lng->txt("lng_merge_po_not_written"),
                $escape(implode(', ', $result['skipped']))
            );
        }
        if ($result['invalid_markup'] !== []) {
            $failures[] = $this->invalidMarkupMessage($result['invalid_markup']);
        }
        if ($result['not_merged'] !== []) {
            $failures[] = $this->lng->txt("form_input_not_valid") . ' ' . $escape(PlainLogText::keyList($result['not_merged']));
        }
        if ($result['unwritten_database'] !== []) {
            $failures[] = sprintf(
                $this->lng->txt("lng_merge_database_not_updated"),
                $escape(implode(', ', $result['unwritten_database']))
            );
        }
        if ($failures !== []) {
            $this->tpl->setOnScreenMessage('failure', implode('<br />', $failures), true);
        }
    }

    /**
     * @param list<string> $modules_with_unwritten_overlay
     */
    private function overlayNotWrittenMessage(array $modules_with_unwritten_overlay): string
    {
        return sprintf(
            $this->lng->txt("lng_po_overlay_not_written"),
            implode(', ', $modules_with_unwritten_overlay)
        );
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
