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
class ilObjLanguageFolderGUI extends ilObjectGUI
{
    use RendersActivityErrors;

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

        // Resolved once here, per the constructor-injection idiom already
        // used by sibling GUI classes in this component (see
        // ilObjLanguageExtGUI::__construct()) - action methods must not
        // reach into `global $DIC` themselves.
        $this->install_language = $DIC[InstallLanguage::class];
        $this->update_language = $DIC[UpdateLanguage::class];
        $this->uninstall_language = $DIC[UninstallLanguage::class];
        $this->remove_local_language_changes = $DIC[RemoveLocalLanguageChanges::class];
        $this->set_language_detection_enabled = $DIC[SetLanguageDetectionEnabled::class];
        $this->current_user_id = $DIC->user()->getId();
        // Used exclusively by activityErrorMessage() (see RendersActivityErrors) -
        // resolved once here, per the same idiom as the Activities above.
        $this->activity_error_logger = $DIC->logger()->lang();
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

        $this->tpl->setContent($this->ui_renderer->render($table->withRequest($this->request)));
    }

    protected function buildConfirmModal(array $ids, string $title, string $action, string $text, string $add_text = ''): ILIAS\UI\Implementation\Component\Modal\Interruptive
    {
        $f = $this->ui_factory;
        $title = $this->lng->txt($title);
        $items = [];

        if (!empty($ids)) {
            if ($this->abortIfAnyIdIsNotALanguageObject($ids)) {
                // The request has already been aborted above (a failure
                // message plus a redirect to "view") - this method's return
                // type still requires a Modal value, but it is never
                // actually rendered to the user: the redirect() response
                // takes precedence in production. See
                // abortIfAnyIdIsNotALanguageObject() for why.
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
     * Turn a list of language keys into a localized, comma-separated list
     * (e.g. for "German, French installed." style messages).
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
     * Restores, for a batch of ids taken directly from request input (see
     * getIdsFromQueryToken()), the implicit type guarantee
     * `new ilObjLanguage((int) $obj_id)` used to give before every caller
     * below was switched away from it towards `ilObject::_lookupTitle()`: the
     * former threw ilObjectTypeMismatchException for a non-"lng" id, while
     * the latter returns ANY object's title regardless of type (or "" for a
     * non-existing id) without any type check at all. $ids is completely
     * unvalidated request input, so every id must be confirmed to actually be
     * a "lng" object BEFORE its title is trusted as a language key anywhere
     * in this class.
     *
     * Aborts the WHOLE request - a generic, user-visible failure message
     * followed by a redirect back to "view" - the moment a SINGLE id in
     * $ids is not a "lng" object, rather than silently skipping just that
     * one: a caller submitting an id that does not belong on this screen at
     * all must never have any part of their request honoured. The message
     * deliberately does not name the actual mismatch (e.g. which id, or what
     * type it actually is), to avoid confirming to a caller probing this
     * parameter which ids exist and of what type.
     *
     * `ilObject::_lookupType()` is backed by `ilObjDataCache` (see its own
     * implementation), exactly like `ilObject::_lookupTitle()` already is -
     * so checking every id here costs no additional, uncached database
     * round trip compared to before.
     *
     * @param list<string|int> $ids
     * @return bool true if the request was aborted (the caller must return
     *         immediately without acting on $ids at all); false if every id
     *         in $ids is confirmed to be a "lng" object.
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
     * Install languages, or (re-)apply just their customizing/local file -
     * see InstallLanguage::MODE_INSTALL / MODE_INSTALL_LOCAL. $mode is
     * passed straight from the "install"/"install_local" command that was
     * dispatched (see executeCommand()) - both are valid InstallLanguage
     * modes as-is.
     */
    public function installObject(array $ids, string $mode): void
    {
        $this->checkPermission('write');

        // An empty selection must never reach InstallLanguage itself: its
        // own field-level validation would reject it with a technical,
        // untranslated message (e.g. "language_keys: not_min_length") -
        // exactly the same "nothing was selected" case buildConfirmModal()
        // already handles for the confirmation step, so the same
        // established message is shown here too.
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

            // activityErrorMessage() already returns a complete, self-
            // contained message (see uninstallObject()) - a fixed
            // "language_not_installed" prefix does not apply to every
            // possible error here (e.g. a permission failure, or an invalid
            // language file), so it must not be prepended unconditionally.
            $this->tpl->setOnScreenMessage(
                'failure',
                $error_message,
                true
            );

            $this->ctrl->redirect($this, 'view');
            return;
        }
        $value = $result->value();

        // Both of these are "success" messages, but setOnScreenMessage()
        // only keeps one message per type (a second call for the same type
        // would silently overwrite the first) - so if both buckets are
        // non-empty in the same request (e.g. installing several languages
        // at once, some plain, some with a custom file), they are combined
        // into a single message instead of two separate calls.
        $success_messages = [];

        if (($lang_installed = $value['installed_language_keys']) !== []) {
            $success_messages[] = $this->languageKeysToLocalizedList($lang_installed)
                . " " . strtolower($this->lng->txt("installed")) . ".";
        }

        // A language gets a customizing/local file (re-)applied either as
        // part of a fresh installation (mode "install") or as the result of
        // mode "install_local" - either way, something did change for it, so
        // this must be reported explicitly rather than disappearing into
        // "already installed", which would wrongly suggest nothing happened.
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

        // Both of these are "info" messages about requests that changed
        // nothing at all (mode "install" on an already installed language,
        // or mode "install_local" on a language that is not installed) -
        // combined into a single message for the same reason as the success
        // messages above.
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

        if (($invalid_local_language_files = $value['invalid_local_language_files']) !== []) {
            $message = $this->lng->txt('local_language_files') . ': '
                . implode(', ', $invalid_local_language_files) . '. '
                . $this->lng->txt('file_not_valid');
            $this->tpl->setOnScreenMessage(
                'failure',
                $message,
                true
            );
        }

        $this->ctrl->redirect($this, 'view');
    }


    /**
     * Uninstall languages - see UninstallLanguage. A language among $ids
     * that is not installed, the system language, or the language currently
     * in use is left completely untouched.
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

            // activityErrorMessage() already returns a complete, self-
            // contained message - a generic, localized "action_aborted" text
            // for an unexpected internal failure, or the concrete,
            // already-actionable message for a SafeToDisplayActivityError -
            // appending "action_aborted" again would either duplicate it or
            // wrongly imply every rejection is a generic abort.
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

        // These three outcomes are all "nothing changed for this language,
        // and here is why" - combined into a single info message for the
        // same reason as installObject()/refreshSelectedObject() combine
        // theirs: setOnScreenMessage() only keeps one message per type.
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

        $this->ctrl->redirect($this, 'view');
    }


    /**
     * Remove local changes of one or more already installed languages - see
     * RemoveLocalLanguageChanges. A language among $ids that is not
     * installed (or not a known language key at all) is left completely
     * untouched.
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

            // activityErrorMessage() already returns a complete, self-
            // contained message - a generic, localized "action_aborted" text
            // for an unexpected internal failure, or the concrete,
            // already-actionable message for a SafeToDisplayActivityError -
            // appending "action_aborted" again would either duplicate it or
            // wrongly imply every rejection is a generic abort.
            $this->tpl->setOnScreenMessage(
                'failure',
                $error_message,
                true
            );

            $this->ctrl->redirect($this, 'view');
            return;
        }
        $value = $result->value();

        // Plugin language files are refreshed only for the languages whose
        // local changes were actually removed - a requested-but-not-installed
        // or invalid language (see below) has nothing to refresh.
        ilObjLanguage::refreshPlugins($value['removed_local_changes_language_keys']);

        if (($lang_changed = $value['removed_local_changes_language_keys']) !== []) {
            $this->tpl->setOnScreenMessage(
                'success',
                $this->lng->txt("selected_languages_updated") . "<br />"
                    . $this->languageKeysToLocalizedList($lang_changed),
                true
            );
        }

        if (($lang_invalid = $value['invalid_language_file_keys']) !== []) {
            $this->tpl->setOnScreenMessage(
                'failure',
                $this->languageKeysToLocalizedList($lang_invalid) . ": " . $this->lng->txt("file_not_valid"),
                true
            );
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
     * Refresh (already installed) languages, re-seeding their base data
     * (plus a customizing/local file if one exists) from the current
     * language files - see UpdateLanguage. A language among $ids that is
     * not installed is left completely untouched.
     */
    public function refreshSelectedObject(array $ids): void
    {
        $this->checkPermission('write');

        // See installObject() for why an empty selection must never reach
        // the Activity itself.
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

            // activityErrorMessage() already returns a complete, self-
            // contained message (see uninstallObject()) - a fixed
            // "language_not_installed" prefix does not apply to every
            // possible error here (e.g. a permission failure, or an invalid
            // language file), so it must not be prepended unconditionally.
            $this->tpl->setOnScreenMessage(
                'failure',
                $error_message,
                true
            );

            $this->ctrl->redirect($this, 'view');
            return;
        }
        $value = $result->value();

        // Plugin language files are refreshed only for the languages that
        // were actually updated - a requested-but-not-installed language
        // (see not_installed_language_keys below) has nothing to refresh.
        ilObjLanguage::refreshPlugins($value['updated_language_keys']);

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

        $this->ctrl->redirect($this, 'view');
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
                        case 'install_local':
                            $ids = $this->getIdsFromQueryToken();
                            if (current($ids) === 'ALL_OBJECTS') {
                                array_shift($ids);
                                $languages = ilObject::_getObjectsByType("lng");
                                foreach ($languages as $lang) {
                                    $ids[] = (string) $lang["obj_id"];
                                }
                            }
                            // $action is exactly 'install' or 'install_local'
                            // here (the two case labels above) - which are
                            // also InstallLanguage::MODE_INSTALL and
                            // MODE_INSTALL_LOCAL's values, so it can be
                            // passed straight through as the mode.
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

        if ($this->abortIfAnyIdIsNotALanguageObject($ids)) {
            return;
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

        $ids = $this->getIdsFromQueryToken();
        if ($this->abortIfAnyIdIsNotALanguageObject($ids)) {
            return;
        }

        $conf_screen = new ilConfirmationGUI();
        $conf_screen->setFormAction($this->ctrl->getFormAction($this));
        $conf_screen->setHeaderText($this->lng->txt("lang_uninstall_confirm"));
        foreach ($ids as $id) {
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

        $ids = $this->getIdsFromQueryToken();
        if ($this->abortIfAnyIdIsNotALanguageObject($ids)) {
            return;
        }

        $conf_screen = new ilConfirmationGUI();
        $conf_screen->setFormAction($this->ctrl->getFormAction($this));
        $conf_screen->setHeaderText($this->lng->txt("lang_uninstall_changes_confirm"));
        foreach ($ids as $id) {
            $lang_title = ilObject::_lookupTitle($id);
            $conf_screen->addItem("id[]", (string) $id, $this->lng->txt("meta_l_" . $lang_title));
        }
        $conf_screen->setCancel($this->lng->txt("cancel"), "view");
        $conf_screen->setConfirm($this->lng->txt("ok"), "uninstallChanges");
        $this->tpl->setContent($conf_screen->getHTML());
    }

    protected function editFolderObject(array $ids): void
    {
        $this->ctrl->setParameterByClass("ilobjlanguageextgui", "obj_id", current($ids));
        $this->ctrl->redirectByClass("ilobjlanguageextgui");
    }

    /**
     * Disable language detection - see SetLanguageDetectionEnabled.
     */
    protected function disableLanguageDetectionObject(): void
    {
        $this->setLanguageDetectionEnabledObject(false);
    }

    /**
     * Enable language detection - see SetLanguageDetectionEnabled.
     */
    protected function enableLanguageDetectionObject(): void
    {
        $this->setLanguageDetectionEnabledObject(true);
    }

    /**
     * Common implementation of enableLanguageDetectionObject()/
     * disableLanguageDetectionObject() - both flip the very same
     * "lang_detection" system setting to a fixed value, so both are backed
     * by the same SetLanguageDetectionEnabled Activity with a different
     * boolean argument.
     */
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

            // activityErrorMessage() already returns a complete, self-
            // contained message - a generic, localized "action_aborted" text
            // for an unexpected internal failure, or the concrete,
            // already-actionable message for a SafeToDisplayActivityError -
            // appending "action_aborted" again would either duplicate it or
            // wrongly imply every rejection is a generic abort.
            $this->tpl->setOnScreenMessage(
                'failure',
                $error_message,
                true
            );

            $this->ctrl->redirect($this, 'view');
            return;
        }

        // Unlike installObject()/uninstallObject()/refreshSelectedObject(),
        // which redirect after a persisted message, this preserves the
        // extracted code's original behaviour: render the view directly with
        // a non-persisted message, rather than an HTTP redirect.
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
