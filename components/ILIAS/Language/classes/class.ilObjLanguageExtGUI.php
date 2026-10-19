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
use ILIAS\Language\ComponentTranslation\DuplicateIdentifierMessage;
use ILIAS\Language\ComponentTranslation\InvalidMarkupMessage;
use ILIAS\Language\ComponentTranslation\PluralFormKey;
use ILIAS\FileUpload\DTO\ProcessingStatus;
use ILIAS\FileUpload\Location;
use ILIAS\Language\Activities\AddLanguageEntry;
use ILIAS\Language\Activities\RemoveLocalLanguageChanges;
use ILIAS\Language\Activities\RepositoryProvider;
use ILIAS\Language\Activities\SetLanguageTranslationEnabled;
use ILIAS\Language\RendersActivityErrors;
use ILIAS\UI\URLBuilder;
use ILIAS\UI\URLBuilderToken;
use ILIAS\UI\Component\Input\Container\Filter\Filter as ilFilter;
use ILIAS\UI\Implementation\Component\Table as DataTable;
use ILIAS\UI\Component\Component as UIComponent;
use ILIAS\UI\Component\Modal\RoundTrip;
use ILIAS\Filesystem\Stream\Streams;

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
    private const string EDIT_SELECTION_SESSION_KEY = 'lang_ext_edit_selection';
    private const string EDIT_SELECTION_PARAMETER = 'lang_entries_edit_selection';
    private const int EDIT_SELECTION_MAX_STORED = 10;
    private const int REMARK_MAX_LENGTH = 250;
    private string $langmode;
    private readonly AddLanguageEntry $add_language_entry;
    private readonly SetLanguageTranslationEnabled $set_language_translation_enabled;
    private readonly RemoveLocalLanguageChanges $remove_local_language_changes;
    private URLBuilder $url_builder;
    private URLBuilderToken $action_token;
    private URLBuilderToken $row_id_token;
    private ilLanguageEntriesTable $entries_table;
    private ?array $all_translations = null;

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

        $here_uri = new ILIAS\Data\Factory()->uri($this->request->getUri()->__toString());
        $url_builder = new URLBuilder($here_uri);
        [$url_builder, $action_token, $row_id_token] = $url_builder->acquireParameters(
            ['lang_entries'],
            "table_action", //this is the actions's parameter name
            "entry_names"   //this is the parameter name to be used for row-ids
        );
        $this->url_builder = $url_builder;
        $this->action_token = $action_token;
        $this->row_id_token = $row_id_token;

        /** @var ilObjLanguageExt $obj */
        $obj = $this->object;

        $this->entries_table = new ilLanguageEntriesTable(
            $obj,
            $lng,
            $this->ui_factory,
            $url_builder,
            $action_token,
            $row_id_token,
            (bool) $this->langmode
        );
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
        return ['saveEditedEntries', 'upload', 'maintainExecute', 'saveSettings', 'saveNewEntry'];
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

        // async table action: editSelectedObject() answers with the modal and closes the response
        if ($action = $this->getCommandFromQueryToken($this->action_token->getName())) {
            switch ($action) {
                case 'edit':
                    $names = $this->getIdsFromQueryToken();
                    $this->editSelectedObject($names);
                    $ilHelp->setScreenIdComponent("lng");
                    return;
            }
        }

        $cmd = $this->ctrl->getCmd("view");
        // exact match: ilCtrl compares the unsafe commands case-sensitively, PHP method calls are not
        match ($cmd) {
            'saveEditedEntries',
            'import',
            'upload',
            'export',
            'download',
            'maintain',
            'maintainExecute',
            'settings',
            'saveSettings',
            'statistics',
            'addNewEntry',
            'saveNewEntry' => $this->{$cmd . 'Object'}(),
            default => $this->viewObject(),
        };

        $ilHelp->setScreenIdComponent("lng");
    }

    private function getCommandFromQueryToken(string $param): ?string
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

    /** Only normalises the shape, resolveEntryNames() drops unknown names. */
    private function getIdsFromQueryToken(): array
    {
        if (!$this->request_wrapper->has($this->row_id_token->getName())) {
            return [];
        }

        return $this->request_wrapper->retrieve(
            $this->row_id_token->getName(),
            $this->refinery->byTrying([
                $this->refinery->container()->mapValues(
                    $this->refinery->kindlyTo()->string()
                ),
                $this->refinery->custom()->transformation(
                    static function ($value): array {
                        if (!is_string($value)) {
                            throw new \UnexpectedValueException('Not a string.');
                        }
                        return [$value];
                    }
                ),
                $this->refinery->always([]),
            ])
        );
    }
    /** Also used to resolve ALL_OBJECTS: the same filter id restores the same values from the session. */
    private function initFilter(): ilFilter
    {
        global $DIC;

        $field_factory = $this->ui_factory->input()->field();
        $filter_id = ilLanguageEntriesTable::tableId();
        $fields = [];

        if (!ilObjLanguageAccess::_isPageTranslation()) {
            $fields['pattern'] = $field_factory->text($this->lng->txt('search'));

            $module_options = ['all' => $this->lng->txt('language_all_modules')];
            foreach (ilObjLanguageExt::_getModules($this->lng->getLangKey()) as $module) {
                $module_options[$module] = $module;
            }
            $fields['module'] = $field_factory
                ->select(ucfirst($this->lng->txt('module')), $module_options)
                ->withValue('administration');

            $fields['identifier'] = $field_factory->text(ucfirst($this->lng->txt('identifier')));

            if ($this->langmode) {
                $fields['comment'] = $field_factory->text($this->lng->txt('comment'));
            }

            $mode_options = [
                'all' => $this->lng->txt('language_scope_global'),
                'changed' => $this->lng->txt('language_scope_local'),
            ];
            if ($this->langmode) {
                $mode_options['added'] = $this->lng->txt('language_scope_added');
            }
            $mode_options['unchanged'] = $this->lng->txt('language_scope_unchanged');
            $mode_options['equal'] = $this->lng->txt('language_scope_equal');
            $mode_options['different'] = $this->lng->txt('language_scope_different');
            $mode_options['commented'] = $this->lng->txt('language_scope_commented');
            if ($this->langmode) {
                $mode_options['dbremarks'] = $this->lng->txt('language_scope_dbremarks');
            }
            $mode_options['conflicts'] = $this->lng->txt('language_scope_conflicts');

            $fields['mode'] = $field_factory
                ->select($this->lng->txt('filter'), $mode_options)
                ->withValue('all');
        }

        $compare_options = [];
        foreach ($this->lng->getInstalledLanguages() as $lang_key) {
            $compare_options[$lang_key] = $this->lng->txt('meta_l_' . $lang_key);
        }
        $fields['compare'] = $field_factory
            ->select($this->lng->txt('language_compare'), $compare_options)
            ->withValue($this->lng->getDefaultLanguage());

        return $DIC->uiService()->filter()->standard(
            $filter_id,
            $this->ctrl->getLinkTarget($this, 'view'),
            $fields,
            array_fill(0, count($fields), true),
            true,
            true
        );
    }

    /** Same gating as ilUIFilterService::getData(), but keeps the inputs for ilLanguageEntriesTable::filterValue(). */
    private function resolveFilterData(ilFilter $filter): ?array
    {
        return $filter->isActivated() ? $filter->getInputs() : null;
    }

    /**
     * Show the edit screen
     *
     * @param RoundTrip|null $reopened_modal edit modal of a rejected save, rendered with the table
     * @param list<string> $additional_failure_messages HTML, shown together with the failure messages of this view
     */
    public function viewObject(?RoundTrip $reopened_modal = null, array $additional_failure_messages = []): void
    {
        global $DIC;

        $filter = $this->initFilter();
        $filter_data = $this->resolveFilterData($filter);

        $this->resetEntriesTableRangeIfRequested();

        // one message: setOnScreenMessage() only keeps one per type
        $failure_messages = $additional_failure_messages;
        $notice = $this->entries_table->getConflictsNotice($filter_data);
        if ($notice !== null && $notice['type'] === 'failure') {
            $failure_messages[] = $notice['text'];
        } elseif ($notice !== null) {
            $this->tpl->setOnScreenMessage($notice['type'], $notice['text'], false);
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

        $table = $this->entries_table->getTable($filter_data)
            ->withFilter($filter_data)
            ->withRequest($this->request);

        $missing_entries = [];
        if (ilObjLanguageAccess::_isPageTranslation()) {
            $modules = ilObjLanguageAccess::_getSavedModules();
            $topics = ilObjLanguageAccess::_getSavedTopics();

            $translations = ilObjLanguageExt::_getValues($this->object->key, $modules, $topics);
            $db_found = [];
            foreach ($translations as $name => $translation) {
                $keys = explode($this->lng->separator, $name);
                $db_found[] = $keys[1];
                // the forms of a plural message are its topic (see PluralFormKey)
                $db_found[] = PluralFormKey::parse($keys[1])[0] ?? $keys[1];
            }
            $missing_entries = array_diff($topics, $db_found);
            // only offer what AddLanguageEntry accepts, a topic of another module would be a duplicate
            $missing_entries = RepositoryProvider::filterAddable($DIC->database(), $modules, array_values($missing_entries));
        }

        $this->tpl->setContent(
            $this->ui_renderer->render(array_filter([$filter, $table, $reopened_modal]))
            . $this->buildMissingEntries($missing_entries)
        );
    }

    /**
     * KS Data Table cannot reset only the range for "&reset_offset=true" of
     * ilObjLanguageAccess::_getTranslationLink(), so its whole view-control state is cleared.
     */
    private function resetEntriesTableRangeIfRequested(): void
    {
        if (!ilObjLanguageAccess::_isPageTranslation()) {
            return;
        }

        $reset_offset = $this->request_wrapper->has('reset_offset')
            && $this->request_wrapper->retrieve('reset_offset', $this->refinery->kindlyTo()->bool());

        if ($reset_offset) {
            ilSession::clear(DataTable\Data::STORAGE_ID_PREFIX . ilLanguageEntriesTable::tableId());
        }
    }

    /**
     * Answers the async "edit" action of the entries table with the edit modal, or with a failure message box.
     * ALL_OBJECTS is resolved against the session-persisted filter, like the table itself.
     */
    protected function editSelectedObject(array $names): void
    {
        $names = $this->resolveEntryNames($names);

        if ($names === []) {
            $this->sendAsync($this->ui_factory->messageBox()->failure($this->lng->txt('no_checkbox')));
            return;
        }

        if ($this->exceedsMaxInputVars(count($names))) {
            $this->sendAsync(
                $this->ui_factory->messageBox()->failure($this->lng->txt('language_too_many_entries_selected'))
            );
            return;
        }

        $this->sendAsync($this->buildEditEntriesModal($names, $this->storeEditSelection($names), true));
    }

    /**
     * Expands ALL_OBJECTS and drops names that do not exist (labels are rendered unescaped by KS).
     *
     * @return list<string>
     */
    private function resolveEntryNames(array $names): array
    {
        if (in_array('ALL_OBJECTS', $names, true)) {
            $names = $this->entries_table->getFilteredEntryNames($this->resolveFilterData($this->initFilter()));
        }

        $translations = $this->getAllTranslations();

        return array_values(array_filter(
            $names,
            static fn(string $name): bool => array_key_exists($name, $translations)
        ));
    }

    private function getAllTranslations(): array
    {
        return $this->all_translations ??= $this->object->getAllValues();
    }

    /** Fields per entry (translation, plus comment in langmode) and the trailing "expected_entry_count". */
    private function exceedsMaxInputVars(int $entry_count): bool
    {
        $max_input_vars = ini_get('max_input_vars');
        $limit = ($max_input_vars === false || $max_input_vars === '') ? 1000 : (int) $max_input_vars;

        return ($entry_count * ($this->langmode ? 2 : 1) + 1) > $limit;
    }

    /**
     * The selection stays in the session (a long one does not fit into the post URL); the token keeps
     * modals opened in parallel tabs apart, so no value is ever saved to another tab's entry.
     *
     * @param list<string> $names
     */
    private function storeEditSelection(array $names): string
    {
        $token = bin2hex(random_bytes(8));
        $selections = (array) (ilSession::get(self::EDIT_SELECTION_SESSION_KEY) ?? []);
        $selections[$token] = $names;
        ilSession::set(
            self::EDIT_SELECTION_SESSION_KEY,
            array_slice($selections, -self::EDIT_SELECTION_MAX_STORED, null, true)
        );

        return $token;
    }

    /**
     * @return list<string>
     */
    private function retrieveEditSelection(): array
    {
        if (!$this->request_wrapper->has(self::EDIT_SELECTION_PARAMETER)) {
            return [];
        }
        $token = $this->request_wrapper->retrieve(
            self::EDIT_SELECTION_PARAMETER,
            $this->refinery->byTrying([$this->refinery->kindlyTo()->string(), $this->refinery->always('')])
        );
        $selections = (array) (ilSession::get(self::EDIT_SELECTION_SESSION_KEY) ?? []);

        return array_values(array_map('strval', (array) ($selections[$token] ?? [])));
    }

    /**
     * One textarea per entry, in edit mode with the compare language's value (from the table filter) as byline.
     * "expected_entry_count" is the last field, so a POST truncated by max_input_vars misses it.
     *
     * @param list<string> $names existing entry names, see resolveEntryNames()
     */
    protected function buildEditEntriesModal(array $names, string $selection_token, bool $with_bylines): RoundTrip
    {
        $field_factory = $this->ui_factory->input()->field();
        $translations = $this->getAllTranslations();

        $compare_values = [];
        $compare_title = '';
        if ($with_bylines) {
            $filter_data = $this->resolveFilterData($this->initFilter());
            $compare_values = $this->entries_table->getCompareValues($filter_data);
            $compare_title = $this->entries_table->getCompareTitle($filter_data);
        }

        $inputs = [];
        foreach ($names as $idx => $name) {
            $keys = explode($this->lng->separator, $name);
            $translation = $field_factory
                ->textarea(($keys[0] ?? '') . ' / ' . ($keys[1] ?? ''))
                ->withValue($translations[$name] ?? '');

            $compare_value = str_replace(['<br />', '<br/>', '<br>'], ' ', (string) ($compare_values[$name] ?? ''));
            if ($compare_value !== '') {
                // KS renders bylines unescaped
                $translation = $translation->withByline(
                    htmlspecialchars($compare_title . ': ' . $compare_value, ENT_QUOTES, 'UTF-8')
                );
            }
            $inputs["translation_$idx"] = $translation;

            if ($this->langmode) {
                // no withMaxLength(): KS counts bytes, the remarks column characters; see normalizeRemark()
                $inputs["comment_$idx"] = $field_factory
                    ->text($this->lng->txt('comment'))
                    ->withValue($this->normalizeRemark($this->entries_table->effectiveRemarkOf($name)));
            }
        }
        $inputs['expected_entry_count'] = $field_factory->hidden()->withValue((string) count($names));

        $post_url = $this->ctrl->getFormAction($this, 'saveEditedEntries')
            . '&' . http_build_query([self::EDIT_SELECTION_PARAMETER => $selection_token]);

        return $this->ui_factory->modal()->roundtrip($this->lng->txt('edit'), null, $inputs, $post_url)
            ->withSubmitLabel($this->lng->txt('save'));
    }

    /** Single line, a line break would break the .lang format on export/merge. */
    private function normalizeRemark(string $remark): string
    {
        return mb_substr(trim((string) preg_replace('/\R/u', ' ', $remark)), 0, self::REMARK_MAX_LENGTH);
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

    /** Outside langmode the remarks are written back as shown (see ilObjLanguageExt::getEffectiveRemarks()). */
    public function saveEditedEntriesObject(): void
    {
        $selection = $this->retrieveEditSelection();
        $names = $this->resolveEntryNames($selection);
        if ($names === []) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt('no_checkbox'), true);
            $this->ctrl->redirect($this, 'view');
            return;
        }

        try {
            $data = $this->buildEditEntriesModal($names, '', false)->withRequest($this->request)->getData();
        } catch (InvalidArgumentException) {
            // KS rejects missing (truncated) field values instead of reporting them
            $data = null;
        }

        // an entry deleted meanwhile shifts the indexes, so the whole selection must still exist
        if ($data === null
            || count($names) !== count($selection)
            || (string) ($data['expected_entry_count'] ?? '') !== (string) count($names)
        ) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt('msg_form_save_error'), true);
            $this->ctrl->redirect($this, 'view');
            return;
        }

        $save_array = [];
        $remarks_array = [];
        foreach ($names as $idx => $name) {
            $value = (string) ($data["translation_$idx"] ?? '');
            // avoid line breaks
            $value = preg_replace("/(\015\012)|(\015)|(\012)/", "<br />", $value);
            $value = str_replace("<<", "«", $value);
            // markup is checked below (TranslationMarkupPolicy), not stripped
            $save_array[$name] = $value;

            // an unchanged remark is passed as shown, so saveValues() does not turn a shipped note into a local one
            $current_remark = $this->entries_table->effectiveRemarkOf($name);
            $remarks_array[$name] = $current_remark;
            if ($this->langmode) {
                $posted_remark = $this->normalizeRemark((string) ($data["comment_$idx"] ?? ''));
                if ($posted_remark !== $this->normalizeRemark($current_remark)) {
                    $remarks_array[$name] = $posted_remark;
                }
            }
        }

        // nothing is saved if any changed value has disallowed markup; all affected keys are named
        $invalid_values = ilObjLanguageExt::findInvalidMarkupOfChangedValues($this->object->key, $save_array);
        if ($invalid_values !== []) {
            // the modal is opened again with the input of this save, so nothing typed in is lost
            $modal = $this->buildEditEntriesModal($names, $this->storeEditSelection($names), true)
                ->withRequest($this->request);
            $this->viewObject(
                $modal->withOnLoad($modal->getShowSignal()),
                [$this->invalidMarkupMessage($invalid_values)]
            );
            return;
        }

        $modules_with_unwritten_overlay = $save_array !== []
            ? ilObjLanguageExt::_saveValues($this->object->key, $save_array, $remarks_array)
            : [];

        $this->setSuccessOrOverlayWarning($this->lng->txt('language_variables_saved'), $modules_with_unwritten_overlay);
        $this->ctrl->redirect($this, 'view');
    }

    private function sendAsync(UIComponent $component): void
    {
        $this->http->saveResponse(
            $this->http->response()->withBody(Streams::ofString($this->ui_renderer->renderAsync($component)))
        );
        $this->http->sendResponse();
        $this->http->close();
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


                // code 400: shown escaped by activityErrorMessage()
                if (!$upload->hasUploads()) {
                    throw new ilException($DIC->language()->txt("upload_error_file_not_found"), 400);
                }
                $UploadResult = $upload->getResults()[$_FILES["userfile"]["tmp_name"]];

                $ProcessingStatus = $UploadResult->getStatus();
                if ($ProcessingStatus->getCode() === ProcessingStatus::REJECTED) {
                    throw new ilException($ProcessingStatus->getMessage(), 400);
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
            } catch (ilLanguageDuplicateIdentifierException $e) {
                $this->tpl->setOnScreenMessage('failure', $this->duplicateIdentifierMessage($e->getDuplicates()), true);
                $this->ctrl->redirect($this, 'import');
            } catch (Exception $e) {
                // anything else may carry SQL: logged, a generic message shown
                $this->tpl->setOnScreenMessage('failure', $this->activityErrorMessage($e), true);
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
     * Names each entry with the entries it would duplicate, the full list goes to the log.
     *
     * @param array<string, list<string>> $duplicates key => "<module>: <identifier>"
     */
    private function duplicateIdentifierMessage(array $duplicates): string
    {
        $entries = [];
        foreach ($duplicates as $key => $existing) {
            $entries[] = DuplicateIdentifierMessage::entry((string) $key, $existing);
        }
        $this->activity_error_logger->warning(DuplicateIdentifierMessage::forLog($entries));

        return DuplicateIdentifierMessage::of($this->lng, $this->refinery, $entries);
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
                    // values with disallowed markup (like at installation) and duplicates are left out and named
                    $modules_with_unwritten_overlay = $this->object->importLanguageFile($lang_file, "replace", true);
                    $this->object->setLocal(true);
                    $skipped = [];
                    if ($this->object->getSkippedInvalidMarkupValues() !== []) {
                        $skipped[] = $this->invalidMarkupMessage($this->object->getSkippedInvalidMarkupValues());
                    }
                    if ($this->object->getSkippedDuplicateValues() !== []) {
                        $skipped[] = $this->duplicateIdentifierMessage($this->object->getSkippedDuplicateValues());
                    }
                    if ($skipped !== [] && $modules_with_unwritten_overlay === []) {
                        $this->tpl->setOnScreenMessage('success', $this->lng->txt("language_loaded_local"), true);
                        $this->tpl->setOnScreenMessage('failure', implode('<br />', $skipped), true);
                    } elseif ($skipped !== []) {
                        // one failure box only: overlay warning and left-out entries together
                        $this->tpl->setOnScreenMessage('failure', implode('<br />', [
                            $this->lng->txt("language_loaded_local"),
                            $this->overlayNotWrittenMessage($modules_with_unwritten_overlay),
                            ...$skipped,
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

                // merge local changes into the global language file and the shipped .po (langmode only)
            case "merge":
                $orig_file = $this->object->getLangPath() . "/ilias_" . $this->object->key . ".lang";
                $copy_file = $this->object->getCustLangPath() . "/ilias_" . $this->object->key . ".lang";

                // ShippedPoMerger checks the shipped .po itself and reports the modules it cannot write
                $global_file_writable = is_file($orig_file) && is_writable($orig_file);
                if ($global_file_writable) {
                    @copy($orig_file, $copy_file);
                }
                $result = $this->object->mergeLocalChangesIntoGlobalLanguageFile($global_file_writable);
                $this->mergeMessages(
                    $result,
                    $global_file_writable,
                    // a read-only global file is only an error if there was something to write into it
                    !$global_file_writable && $this->object->hasLocalChangesOutsidePoModules()
                );
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
                case "saveEditedEntries":
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
            $entry = (string) $entry;
            // ilCtrl only escapes "&" of a parameter value
            $ilCtrl->setParameter($this, "eid", $this->refinery->encode()->url()->transform($entry));
            $link = $this->refinery->encode()->htmlAttributeValue()->transform($ilCtrl->getLinkTarget($this, "addNewEntry"));
            $res[] = '<li>' . $this->refinery->encode()->htmlSpecialCharsAsEntities()->transform($entry) .
                ' <a href="' . $link .
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
     * per type), or an info if nothing was written at all; details are in the log.
     *
     * @param array{written: list<string>, skipped: list<string>, invalid_markup: array<string, list<string>>, not_merged: list<string>, unwritten_database: list<string>} $result
     * @param bool $global_file_not_written the global language file had local changes to take but is not writable
     */
    private function mergeMessages(array $result, bool $global_file_written, bool $global_file_not_written): void
    {
        $escape = fn(string $text): string => $this->refinery->encode()->htmlSpecialCharsAsEntities()->transform($text);
        $successes = $global_file_written ? [$this->lng->txt("language_merged_global")] : [];
        if ($result['written'] !== []) {
            $successes[] = sprintf(
                $this->lng->txt("lng_merged_po_modules"),
                $escape(implode(', ', $result['written']))
            );
        }
        if ($successes !== []) {
            $this->tpl->setOnScreenMessage('success', implode('<br />', $successes), true);
        }

        $failures = $global_file_not_written ? [$this->lng->txt("language_error_write_global")] : [];
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
        } elseif ($successes === []) {
            $this->tpl->setOnScreenMessage('info', $this->lng->txt("lng_merge_nothing_to_merge"), true);
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

    private function getSession(): array
    {
        return ilSession::get("lang_ext_maintenance") ?? [];
    }
} // END class.ilObjLanguageExtGUI
