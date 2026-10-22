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

use ILIAS\Language\ComponentTranslation\MigratedTranslations;
use ILIAS\Language\ComponentTranslation\NativeGettext;
use ILIAS\Language\ComponentTranslation\DuplicateIdentifierMessage;
use ILIAS\Language\ComponentTranslation\InvalidMarkupMessage;
use ILIAS\UI\URLBuilder;
use ILIAS\UI\URLBuilderToken;
use ILIAS\Language\Activities\InstallLanguage;
use ILIAS\Language\Activities\UpdateLanguage;
use ILIAS\Language\Activities\UninstallLanguage;
use ILIAS\Language\Activities\RemoveLocalLanguageChanges;
use ILIAS\Language\Activities\SetLanguageDetectionEnabled;
use ILIAS\Language\RendersActivityErrors;

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
class ilObjLanguageFolderGUI extends ilObjectGUI implements ilCtrlSecurityInterface
{
    use RendersActivityErrors;

    private const string CMD_TABLE_ACTION = 'executeTableAction';

    protected ilLanguageFolderTable $languageFolderTable;
    protected ILIAS\Data\Factory $df;
    protected URLBuilder $url_builder;
    protected URLBuilderToken $action_token;
    protected URLBuilderToken $id_token;
    private readonly InstallLanguage $install_language;
    private readonly UpdateLanguage $update_language;
    private readonly UninstallLanguage $uninstall_language;
    private readonly RemoveLocalLanguageChanges $remove_local_language_changes;
    private readonly SetLanguageDetectionEnabled $set_language_detection_enabled;
    private readonly int $current_user_id;

    /**
     * Constructor
     */
    public function __construct(?array $a_data, int $a_id, bool $a_call_by_reference)
    {
        global $DIC;

        $this->type = "lngf";
        parent::__construct($a_data, $a_id, $a_call_by_reference, false);
        $this->lng->loadLanguageModule("lng");

        $this->install_language = $DIC[InstallLanguage::class];
        $this->update_language = $DIC[UpdateLanguage::class];
        $this->uninstall_language = $DIC[UninstallLanguage::class];
        $this->remove_local_language_changes = $DIC[RemoveLocalLanguageChanges::class];
        $this->set_language_detection_enabled = $DIC[SetLanguageDetectionEnabled::class];
        $this->current_user_id = $DIC->user()->getId();
        // used by activityErrorMessage() (see RendersActivityErrors)
        $this->activity_error_logger = $DIC->logger()->lang();
        $this->df = new ILIAS\Data\Factory();

        // via ilCtrl, so that the rtoken of the unsafe table action command is appended
        $url_builder = new URLBuilder($this->df->uri(
            ilUtil::_getHttpPath() . '/'
            . $this->ctrl->getLinkTargetByClass([ilAdministrationGUI::class, self::class], self::CMD_TABLE_ACTION)
        ));
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

    public function getUnsafeGetCommands(): array
    {
        return [
            self::CMD_TABLE_ACTION,
            'enableLanguageDetection',
            'disableLanguageDetection',
        ];
    }

    public function getSafePostCommands(): array
    {
        return [];
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
            $ids = $this->confirmRefreshObject();
            $modal = $this->buildConfirmModal(
                $ids,
                'refresh_languages',
                'confirmRefresh',
                'lang_refresh_confirm_selected',
                "lang_refresh_confirm_info"
            );
            $refresh = $this->ui_factory->button()->standard(
                $this->lng->txt("refresh_languages"),
                ''
            )->withOnClick($modal->getShowSignal());

            $this->toolbar->addComponent($modal);
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

        $this->tpl->setContent($this->ui_renderer->render([
            ...$this->nativeGettextMessageBoxes(),
            $table->withRequest($this->request)
        ]));
    }

    /**
     * Whether the migrated modules can be served through native gettext (see TranslationGettextRepository): a failure box
     * for a problem concerning every migrated module (no gettext, no build), an info box for one concerning a single
     * module, language or overlay, and one in a thread-safe PHP (the locale settings apply to every thread there).
     * Paths are shown relative, the log has the full ones.
     *
     * @return list<\ILIAS\UI\Component\MessageBox\MessageBox>
     */
    private function nativeGettextMessageBoxes(): array
    {
        $boxes = [];
        $problem = MigratedTranslations::getProblem(defined('CLIENT_DATA_DIR') ? (string) CLIENT_DATA_DIR : null);
        if ($problem !== null && $problem['fatal']) {
            $boxes[] = $this->ui_factory->messageBox()->failure(htmlspecialchars(str_replace(
                '%s',
                $problem['message'],
                $this->textOrDefault(
                    'lng_native_gettext_unavailable',
                    'The modules maintained in PO files cannot be served, their texts are shown as "-identifier-": %s'
                )
            )));
        } elseif ($problem !== null) {
            $boxes[] = $this->ui_factory->messageBox()->info(htmlspecialchars(str_replace(
                '%s',
                $problem['message'],
                $this->textOrDefault(
                    'lng_native_gettext_problem',
                    'A problem with the language files of the modules maintained in PO files: %s'
                )
            )));
        }
        if (NativeGettext::isThreadSafe()) {
            $boxes[] = $this->ui_factory->messageBox()->info(htmlspecialchars($this->textOrDefault(
                'lng_native_gettext_thread_safe',
                'PHP runs thread-safe (ZTS): the locale settings for the language files of the modules maintained in PO files apply to every thread of the web server process.'
            )));
        }

        return $boxes;
    }

    private function textOrDefault(string $identifier, string $default): string
    {
        return $this->lng->exists($identifier) ? $this->lng->txt($identifier) : $default;
    }

    protected function buildConfirmModal(array $ids, string $title, string $action, string $text, string $add_text = ''): ILIAS\UI\Implementation\Component\Modal\Interruptive
    {
        $f = $this->ui_factory;
        $title = $this->lng->txt($title);
        $items = [];

        if (!empty($ids)) {
            if ($this->abortIfAnyIdIsNotALanguageObject($ids)) {
                // already aborted (failure message and redirect); the Modal is only needed for the return type
                return $f->modal()->interruptive($title, '', '')->withActionButtonLabel($this->lng->txt('ok'));
            }

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
                            IL_CAL_DATETIME,
                            'UTC'
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
     * Localized, comma-separated list of language names.
     *
     * @param list<string> $lang_keys
     */
    private function languageKeysToLocalizedList(array $lang_keys): string
    {
        return implode(', ', array_map(
            fn(string $key): string => $this->lng->txt('meta_l_' . $key),
            $lang_keys
        ));
    }

    /**
     * $ids is unvalidated request input, and ilObject::_lookupTitle() returns the title of ANY object type, so every
     * id must be confirmed to be a "lng" object before its title is trusted as a language key.
     *
     * Aborts the WHOLE request (generic failure message, redirect to "view") if a single id is not one. The message
     * deliberately does not name the mismatch, so probing the parameter reveals nothing about existing ids.
     *
     * @param list<string|int> $ids
     * @return bool true if the request was aborted (the caller must return immediately)
     */
    private function abortIfAnyIdIsNotALanguageObject(array $ids): bool
    {
        foreach ($ids as $obj_id) {
            if (ilObject::_lookupType((int) $obj_id) !== 'lng') {
                $this->tpl->setOnScreenMessage(
                    'failure',
                    $this->lng->txt('obj_not_found') . '<br/>' . $this->lng->txt('action_aborted'),
                    true
                );
                $this->ctrl->redirect($this, 'view');
                return true;
            }
        }
        return false;
    }

    /**
     * $mode is the dispatched "install"/"install_local" command, both are InstallLanguage modes (MODE_INSTALL/MODE_INSTALL_LOCAL).
     */
    public function installObject(array $ids, string $mode): void
    {
        $this->checkPermission('write');

        // an empty selection must not reach the Activity, its validation message is technical and untranslated
        if ($ids === []) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt('no_checkbox'), true);
            $this->ctrl->redirect($this, 'view');
            return;
        }

        if ($this->abortIfAnyIdIsNotALanguageObject($ids)) {
            return;
        }

        $language_keys = [];
        foreach ($ids as $obj_id) {
            $language_keys[] = ilObject::_lookupTitle((int) $obj_id);
        }

        $result = $this->install_language->maybePerformAs(
            $this->ui_factory->input(),
            $this->current_user_id,
            [
                'language_keys' => $language_keys,
                'mode' => $mode,
            ]
        );

        if ($result->isError()) {
            $error = $result->error();
            $error_message = $this->activityErrorMessage($error);

            // activityErrorMessage() is complete: no prefix ("language_not_installed", "action_aborted") applies to every error
            $this->tpl->setOnScreenMessage(
                'failure',
                $error_message,
                true
            );

            $this->ctrl->redirect($this, 'view');
            return;
        }
        $value = $result->value();

        // setOnScreenMessage() keeps one message per type, so messages of one type are combined
        $success_messages = [];

        if (($lang_installed = $value['installed_language_keys']) !== []) {
            $success_messages[] = $this->languageKeysToLocalizedList($lang_installed)
                . " " . strtolower($this->lng->txt("installed")) . ".";
        }

        // reported explicitly, else it would vanish into "already installed" and suggest nothing happened
        if (($lang_installed_with_local_file = $value['installed_with_local_language_keys']) !== []) {
            $success_messages[] = $this->languageKeysToLocalizedList($lang_installed_with_local_file)
                . ": " . $this->lng->txt("installed_local") . ".";
        }

        if ($success_messages !== []) {
            $this->tpl->setOnScreenMessage(
                'success',
                implode('<br />', $success_messages),
                true
            );
        }

        $info_messages = [];

        if (($lang_already_installed = $value['already_installed_language_keys']) !== []) {
            $info_messages[] = $this->lng->txt("languages_already_installed") . ': '
                . $this->languageKeysToLocalizedList($lang_already_installed);
        }

        if (($lang_not_installed = $value['not_installed_language_keys']) !== []) {
            $info_messages[] = $this->languageKeysToLocalizedList($lang_not_installed)
                . " " . $this->lng->txt("language_not_installed");
        }

        if ($info_messages !== []) {
            $this->tpl->setOnScreenMessage(
                'info',
                implode('<br />', $info_messages),
                true
            );
        }

        $failure_messages = [];
        if (($invalid_local_language_files = $value['invalid_local_language_files']) !== []) {
            $failure_messages[] = $this->lng->txt('local_language_files') . ': '
                . implode(', ', $invalid_local_language_files) . '. '
                . $this->lng->txt('file_not_valid');
        }
        if (($overlay_write_failed = $value['overlay_write_failed_language_keys'] ?? []) !== []) {
            $failure_messages[] = $this->overlayWriteFailedMessage($overlay_write_failed);
        }
        if (($invalid_markup = $value['invalid_markup_customizing_entries'] ?? []) !== []) {
            $failure_messages[] = $this->invalidMarkupCustomizingMessage($invalid_markup);
        }
        if (($duplicates = $value['duplicate_customizing_entries'] ?? []) !== []) {
            $failure_messages[] = $this->duplicateCustomizingMessage($duplicates);
        }
        if ($failure_messages !== []) {
            $this->tpl->setOnScreenMessage(
                'failure',
                implode('<br />', $failure_messages),
                true
            );
        }

        $this->ctrl->redirect($this, 'view');
    }


    /**
     * Languages that are not installed, the system language and the language in use are left untouched.
     */
    public function uninstallObject(array $ids): void
    {
        $this->checkPermission('write');
        $this->lng->loadLanguageModule("meta");

        if ($this->abortIfAnyIdIsNotALanguageObject($ids)) {
            return;
        }

        $language_keys = [];
        foreach ($ids as $obj_id) {
            $language_keys[] = ilObject::_lookupTitle((int) $obj_id);
        }

        $result = $this->uninstall_language->maybePerformAs(
            $this->ui_factory->input(),
            $this->current_user_id,
            ['language_keys' => $language_keys]
        );

        if ($result->isError()) {
            $error = $result->error();
            $error_message = $this->activityErrorMessage($error);

            $this->tpl->setOnScreenMessage(
                'failure',
                $error_message,
                true
            );

            $this->ctrl->redirect($this, 'view');
            return;
        }
        $value = $result->value();

        if (($lang_uninstalled = $value['uninstalled_language_keys']) !== []) {
            $this->tpl->setOnScreenMessage(
                'success',
                $this->languageKeysToLocalizedList($lang_uninstalled) . " " . $this->lng->txt("uninstalled"),
                true
            );
        }

        $info_messages = [];

        if (($lang_system = $value['system_language_keys']) !== []) {
            $info_messages[] = $this->lng->txt("cannot_uninstall_systemlanguage") . ': '
                . $this->languageKeysToLocalizedList($lang_system);
        }

        if (($lang_in_use = $value['user_language_keys']) !== []) {
            $info_messages[] = $this->lng->txt("cannot_uninstall_language_in_use") . ': '
                . $this->languageKeysToLocalizedList($lang_in_use);
        }

        if (($lang_not_installed = $value['not_installed_language_keys']) !== []) {
            $info_messages[] = $this->lng->txt("languages_already_uninstalled") . ': '
                . $this->languageKeysToLocalizedList($lang_not_installed);
        }

        if ($info_messages !== []) {
            $this->tpl->setOnScreenMessage('info', implode('<br />', $info_messages), true);
        }

        if (($overlay_write_failed = $value['overlay_write_failed_language_keys'] ?? []) !== []) {
            $this->tpl->setOnScreenMessage('failure', $this->overlayWriteFailedMessage($overlay_write_failed), true);
        }

        $this->ctrl->redirect($this, 'view');
    }


    /**
     * Languages that are not installed (or no language key) are left untouched.
     */
    public function uninstallChangesObject(array $ids): void
    {
        $this->checkPermission("write");
        $this->lng->loadLanguageModule("meta");

        if ($this->abortIfAnyIdIsNotALanguageObject($ids)) {
            return;
        }

        $language_keys = [];
        foreach ($ids as $obj_id) {
            $language_keys[] = ilObject::_lookupTitle((int) $obj_id);
        }

        $result = $this->remove_local_language_changes->maybePerformAs(
            $this->ui_factory->input(),
            $this->current_user_id,
            ['language_keys' => $language_keys]
        );

        if ($result->isError()) {
            $error = $result->error();
            $error_message = $this->activityErrorMessage($error);

            $this->tpl->setOnScreenMessage(
                'failure',
                $error_message,
                true
            );

            $this->ctrl->redirect($this, 'view');
            return;
        }
        $value = $result->value();

        if (($lang_changed = $value['removed_local_changes_language_keys']) !== []) {
            $this->tpl->setOnScreenMessage(
                'success',
                $this->lng->txt("selected_languages_updated") . "<br />"
                    . $this->languageKeysToLocalizedList($lang_changed),
                true
            );
        }

        $failure_messages = [];
        if (($lang_invalid = $value['invalid_language_file_keys']) !== []) {
            $failure_messages[] = $this->languageKeysToLocalizedList($lang_invalid) . ": " . $this->lng->txt("file_not_valid");
        }
        if (($overlay_write_failed = $value['overlay_write_failed_language_keys'] ?? []) !== []) {
            $failure_messages[] = $this->overlayWriteFailedMessage($overlay_write_failed);
        }
        if ($failure_messages !== []) {
            $this->tpl->setOnScreenMessage('failure', implode('<br />', $failure_messages), true);
        }

        if (($lang_not_installed = $value['not_installed_language_keys']) !== []) {
            $this->tpl->setOnScreenMessage(
                'info',
                $this->languageKeysToLocalizedList($lang_not_installed)
                    . " " . $this->lng->txt("language_not_installed"),
                true
            );
        }

        $this->ctrl->redirect($this, 'view');
    }

    /**
     * Languages that are not installed are left untouched.
     */
    public function refreshSelectedObject(array $ids): void
    {
        $this->checkPermission('write');

        // see installObject()
        if ($ids === []) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt('no_checkbox'), true);
            $this->ctrl->redirect($this, 'view');
            return;
        }

        if ($this->abortIfAnyIdIsNotALanguageObject($ids)) {
            return;
        }

        $language_keys = [];
        foreach ($ids as $obj_id) {
            $language_keys[] = ilObject::_lookupTitle((int) $obj_id);
        }

        $result = $this->update_language->maybePerformAs(
            $this->ui_factory->input(),
            $this->current_user_id,
            ['language_keys' => $language_keys]
        );

        if ($result->isError()) {
            $error = $result->error();
            $error_message = $this->activityErrorMessage($error);

            $this->tpl->setOnScreenMessage(
                'failure',
                $error_message,
                true
            );

            $this->ctrl->redirect($this, 'view');
            return;
        }
        $value = $result->value();

        if (($lang_updated = $value['updated_language_keys']) !== []) {
            $this->tpl->setOnScreenMessage(
                'success',
                $this->lng->txt('selected_languages_updated') . ' '
                    . $this->languageKeysToLocalizedList($lang_updated),
                true
            );
        }

        if (($lang_not_installed = $value['not_installed_language_keys']) !== []) {
            $this->tpl->setOnScreenMessage(
                'info',
                $this->languageKeysToLocalizedList($lang_not_installed)
                    . ' ' . $this->lng->txt('language_not_installed'),
                true
            );
        }

        $failure_messages = [];
        if (($overlay_write_failed = $value['overlay_write_failed_language_keys'] ?? []) !== []) {
            $failure_messages[] = $this->overlayWriteFailedMessage($overlay_write_failed);
        }
        if (($invalid_markup = $value['invalid_markup_customizing_entries'] ?? []) !== []) {
            $failure_messages[] = $this->invalidMarkupCustomizingMessage($invalid_markup);
        }
        if (($duplicates = $value['duplicate_customizing_entries'] ?? []) !== []) {
            $failure_messages[] = $this->duplicateCustomizingMessage($duplicates);
        }
        if ($failure_messages !== []) {
            $this->tpl->setOnScreenMessage('failure', implode('<br />', $failure_messages), true);
        }

        $this->ctrl->redirect($this, 'view');
    }

    /**
     * Customizing entries not applied because of disallowed markup, see InvalidMarkupMessage. The Activity output
     * already names each entry with its markup ("<language>: <module>#:#<identifier>: <script>"), so they are listed as is.
     *
     * @param list<string> $entries
     */
    private function invalidMarkupCustomizingMessage(array $entries): string
    {
        return InvalidMarkupMessage::of($this->lng, $this->refinery, array_fill_keys($entries, []));
    }

    /**
     * Customizing entries not applied as duplicates of another module: "lng_identifier_not_unique" with the escaped,
     * shortened list of the Activity output.
     *
     * @param list<string> $entries
     */
    private function duplicateCustomizingMessage(array $entries): string
    {
        return DuplicateIdentifierMessage::of($this->lng, $this->refinery, $entries);
    }

    /**
     * The database was written, but the PO/MO files of a PO module could not be (see LanguageActivity::overlayWriteFailedOutputField()).
     *
     * @param list<string> $lang_keys
     */
    private function overlayWriteFailedMessage(array $lang_keys): string
    {
        return sprintf(
            $this->lng->txt('lng_po_overlay_not_written_languages'),
            $this->languageKeysToLocalizedList($lang_keys)
        );
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
                $perm_gui = new ilPermissionGUI($this);
                $this->tabs_gui->activateTab("perm_settings");
                $this->ctrl->forwardCommand($perm_gui);
                break;

            default:
                $this->tabs_gui->activateTab("settings");

                // exact match: ilCtrl compares the unsafe commands case-sensitively, PHP method calls are not
                match ($cmd) {
                    self::CMD_TABLE_ACTION,
                    'enableLanguageDetection',
                    'disableLanguageDetection',
                    'listDeprecated',
                    'downloadDeprecated' => $this->{$cmd . 'Object'}(),
                    default => $this->viewObject(),
                };

                break;
        }
    }

    protected function executeTableActionObject(): void
    {
        $this->checkPermission("write");
        $this->lng->loadLanguageModule("meta");
        $r = $this->ui_renderer;
        switch ($action = $this->getCommandFromQueryToken($this->action_token->getName())) {
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
            case 'install_local':
                $ids = $this->getIdsFromQueryToken();
                if (current($ids) === 'ALL_OBJECTS') {
                    array_shift($ids);
                    $languages = ilObject::_getObjectsByType("lng");
                    foreach ($languages as $lang) {
                        $ids[] = (string) $lang["obj_id"];
                    }
                }
                // 'install'/'install_local' are the InstallLanguage mode values
                $this->installObject($ids, $action);
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
        }
        $this->viewObject();
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

    protected function disableLanguageDetectionObject(): void
    {
        $this->setLanguageDetectionEnabledObject(false);
    }

    protected function enableLanguageDetectionObject(): void
    {
        $this->setLanguageDetectionEnabledObject(true);
    }

    private function setLanguageDetectionEnabledObject(bool $enabled): void
    {
        $result = $this->set_language_detection_enabled->maybePerformAs(
            $this->ui_factory->input(),
            $this->current_user_id,
            ['enabled' => $enabled]
        );

        if ($result->isError()) {
            $error = $result->error();
            $error_message = $this->activityErrorMessage($error);

            $this->tpl->setOnScreenMessage(
                'failure',
                $error_message,
                true
            );

            $this->ctrl->redirect($this, 'view');
            return;
        }

        // rendered directly with a non-persisted message, no redirect (unlike installObject() & Co.)
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
        if ($lang_ids) {
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
