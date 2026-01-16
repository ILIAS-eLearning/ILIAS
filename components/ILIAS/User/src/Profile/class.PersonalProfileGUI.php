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

namespace ILIAS\User\Profile;

use ILIAS\User\LocalDIC;
use ILIAS\User\Context;
use ILIAS\User\Privacy\SettingsGUI as PrivacySettingsGUI;
use ILIAS\User\Profile\ChangeMail\Repository as ChangeMailRepository;
use ILIAS\User\Profile\ChangeMail\DBRepository as ChangeMailDBRepository;
use ILIAS\User\Profile\ChangeMail\Status as ChangeMailStatus;
use ILIAS\User\Profile\ChangeMail\Mail as ChangeMailMail;
use ILIAS\User\Profile\Prompt\Repository as PromptRepository;
use ILIAS\User\Profile\Fields\Field as ProfileField;
use ILIAS\User\Profile\Fields\Standard\FirstName;
use ILIAS\User\Profile\Fields\Standard\LastName;
use ILIAS\User\Profile\Fields\Standard\Alias;
use ILIAS\User\Profile\Fields\Standard\OrganisationalUnits;
use ILIAS\User\Profile\Fields\Standard\Roles;
use ILIAS\User\Profile\Fields\Standard\Email;
use ILIAS\User\Settings\Settings as UserSettings;
use ILIAS\Language\Language;
use ILIAS\FileUpload\FileUpload;
use ILIAS\UI\Factory as UIFactory;
use ILIAS\UI\Renderer as UIRenderer;
use ILIAS\UI\Component\Modal\Interruptive;
use ILIAS\Refinery\Factory as Refinery;
use ILIAS\StaticURL\Services as StaticUrlServices;
use ILIAS\HTTP\Services as HTTP;

/**
 * GUI class for personal profile
 * @author Alexander Killing <killing@leifos.de>
 * @ilCtrl_Calls ILIAS\User\Profile\PersonalProfileGUI: ILIAS\User\Profile\PublicProfileGUI
 * @ilCtrl_Calls ILIAS\User\Profile\PersonalProfileGUI: ILIAS\User\Privacy\SettingsGUI
 * @ilCtrl_Calls ILIAS\User\Profile\PersonalProfileGUI: ilLegalDocumentsAgreementGUI, ilLegalDocumentsWithdrawalGUI
 */
class PersonalProfileGUI
{
    private const PERSONAL_DATA_FORM_ID = 'pd';
    private const PUBLISH_SETTINGS_PREFIX = 'chk_';
    public const CHANGE_EMAIL_CMD = 'changeEmail';

    private \ilGlobalTemplateInterface $tpl;
    private \ilAppEventHandler $event;
    private \ilPropertyFormGUI $form;
    private \ilSetting $settings;
    private \ilObjUser $user;
    private \ilAuthSession $auth_session;
    private StaticUrlServices $static_url;
    private Language $lng;
    private \ilCtrl $ctrl;
    private \ilTabsGUI $tabs;
    private \ilToolbarGUI $toolbar;
    private \ilHelpGUI $help;
    private HTTP $http;
    private \ilErrorHandling $error_handler;
    private ChecklistGUI $checklist;
    private ChecklistStatus $checklist_status;
    private UIFactory $ui_factory;
    private UIRenderer $ui_renderer;
    private Refinery $refinery;

    private ChangeMailRepository $change_mail_token_repo;
    private PromptRepository $prompt_repository;
    private GUIRequest $profile_request;
    private ProfileImplementation $profile;
    private UserSettings $user_settings;

    private \ilLogger $logger;
    private FileUpload $uploads;

    private ?Interruptive $email_change_confirmation_modal = null;

    public function __construct()
    {
        /** @var \ILIAS\DI\Container $DIC */
        global $DIC;

        $this->tabs = $DIC['ilTabs'];
        $this->toolbar = $DIC['ilToolbar'];
        $this->help = $DIC['ilHelp'];
        $this->http = $DIC['http'];
        $this->user = $DIC['ilUser'];
        $this->auth_session = $DIC['ilAuthSession'];
        $this->lng = $DIC['lng'];
        $this->settings = $DIC['ilSetting'];
        $this->tpl = $DIC['tpl'];
        $this->ctrl = $DIC['ilCtrl'];
        $this->error_handler = $DIC['ilErr'];
        $this->event = $DIC['ilAppEventHandler'];
        $this->ui_factory = $DIC['ui.factory'];
        $this->ui_renderer = $DIC['ui.renderer'];
        $this->uploads = $DIC['upload'];
        $this->ui_factory = $DIC['ui.factory'];
        $this->ui_renderer = $DIC['ui.renderer'];
        $this->refinery = $DIC['refinery'];
        $this->auth_session = $DIC['ilAuthSession'];
        $this->static_url = $DIC['static_url'];

        $this->logger = \ilLoggerFactory::getLogger('user');
        $local_dic = LocalDIC::dic();
        $this->profile = $local_dic[Profile::class];
        $this->user_settings = $local_dic[UserSettings::class];
        $this->change_mail_token_repo = new ChangeMailDBRepository(
            $DIC['ilDB'],
            $this->settings
        );
        $this->checklist = new ChecklistGUI();
        $this->checklist_status = new ChecklistStatus(
            $this->lng,
            $this->settings,
            $this->user,
            new Visibility($this->lng, $this->settings, $this->user)
        );
        $this->prompt_repository = new PromptRepository(
            $DIC['ilDB'],
            $this->lng,
            new \ilSetting('user')
        );
        $this->profile_request = new GUIRequest(
            $this->http,
            $DIC['refinery']
        );

        $this->lng->loadLanguageModule('jsmath');
        $this->lng->loadLanguageModule('awrn');
        $this->lng->loadLanguageModule('pd');
        $this->lng->loadLanguageModule('user');
        $this->lng->loadLanguageModule('maps');
        $this->ctrl->saveParameter($this, 'prompted');
    }

    public function executeCommand(): void
    {
        $next_class = $this->ctrl->getNextClass();

        switch ($next_class) {
            case strtolower(PublicProfileGUI::class):
                $pub_profile_gui = new PublicProfileGUI($this->user->getId());
                $pub_profile_gui->setBackUrl($this->ctrl->getLinkTarget($this, 'showPersonalData'));
                $this->ctrl->forwardCommand($pub_profile_gui);
                $this->tpl->printToStdout();
                break;

            case strtolower(PrivacySettingsGUI::class):
                $this->setHeader();
                $this->setTabs();
                $this->tabs->activateTab('visibility_settings');
                $this->showChecklist(ChecklistStatus::STEP_VISIBILITY_OPTIONS);
                $this->ctrl->forwardCommand(
                    new PrivacySettingsGUI(
                        $this->lng,
                        $this->ctrl,
                        $this->event,
                        $this->http->request(),
                        $this->user,
                        $this->settings,
                        $this->tpl,
                        $this->ui_factory,
                        $this->ui_renderer,
                        $this->user_settings,
                        new Visibility(
                            $this->lng,
                            $this->settings,
                            $this->user
                        ),
                        $this->checklist_status,
                        new \ilSetting('chatroom'),
                        new \ilSetting('notifications')
                    )
                );
                break;

            case strtolower(\ilLegalDocumentsAgreementGUI::class):
                $this->ctrl->forwardCommand(new \ilLegalDocumentsAgreementGUI());
                $this->tpl->printToStdout();
                break;

            case strtolower(\ilLegalDocumentsWithdrawalGUI::class):
                $this->ctrl->forwardCommand(new \ilLegalDocumentsWithdrawalGUI());
                $this->tpl->printToStdout();
                break;

            default:
                $this->setTabs();
                $cmd = $this->ctrl->getCmd('showPersonalData');
                $this->$cmd();
                break;
        }
    }

    /**
    * show profile form
    *
    * /OLD IMPLEMENTATION DEPRECATED
    */
    public function showProfile(): void
    {
        $this->showPersonalData();
    }

    // init sub tabs
    public function setTabs(): void
    {
        $this->help->setScreenIdComponent('user');

        // personal data
        $this->tabs->addTab(
            'personal_data',
            $this->lng->txt('user_profile_data'),
            $this->ctrl->getLinkTarget($this, 'showPersonalData')
        );

        // publishing options
        $this->tabs->addTab(
            'public_profile',
            $this->lng->txt('user_publish_options'),
            $this->ctrl->getLinkTarget($this, 'showPublicProfile')
        );

        // visibility settings
        $txt_visibility = $this->checklist_status->anyVisibilitySettings()
            ? $this->lng->txt('user_visibility_settings')
            : $this->lng->txt('preview');
        $this->tabs->addTab(
            'visibility_settings',
            $txt_visibility,
            $this->ctrl->getLinkTargetByClass(PrivacySettingsGUI::class, '')
        );

        // export
        $this->tabs->addTab(
            'export',
            $this->lng->txt('export') . '/' . $this->lng->txt('import'),
            $this->ctrl->getLinkTarget($this, 'showExportImport')
        );
    }

    public function setHeader(): void
    {
        $this->tpl->setTitle($this->lng->txt('personal_profile'));
    }

    public function showPersonalData(
        bool $a_no_init = false
    ): void {
        $this->tabs->activateTab('personal_data');

        $this->setHeader();

        $this->showChecklist(ChecklistStatus::STEP_PROFILE_DATA);

        if (!$a_no_init) {
            $this->initPersonalDataForm();
            // catch feedback message
            if ($this->user->getProfileIncomplete()) {
                $this->tpl->setOnScreenMessage('info', $this->lng->txt('profile_incomplete'));
            }
        }

        $modal = '';
        if ($this->email_change_confirmation_modal !== null) {
            $modal = $this->ui_renderer->render($this->email_change_confirmation_modal);
        }

        $this->tpl->setContent($this->buildInfoText() . $this->form->getHTML() . $modal);

        $this->tpl->printToStdout();
    }

    private function buildInfoText(): string
    {
        $change_mail_info = '';
        if ($this->change_mail_token_repo->hasUserValidEmailConfirmationToken($this->user)) {
            $change_mail_info = $this->lng->txt('change_email_info_message');
        }

        $it = '';
        if ($this->profile_request->getPrompted() === 1) {
            $it = $this->prompt_repository->getSettings()->getPromptText($this->user->getLanguage());
        }
        if ($it === '') {
            $it = $this->prompt_repository->getSettings()->getInfoText($this->user->getLanguage());
        }
        if (trim($it) === '') {
            return $change_mail_info === ''
                ? ''
                : $this->ui_renderer->render($this->ui_factory->messageBox()->info($change_mail_info));
        }

        if ($change_mail_info !== '') {
            $it .= '<br>' . $change_mail_info;
        }

        $pub_prof = in_array($this->user->getPref('public_profile'), ['y', 'n', 'g'])
            ? $this->user->getPref('public_profile')
            : 'n';
        $box = $this->ui_factory->messageBox()->info($it);
        if ($pub_prof === 'n') {
            $box = $box->withLinks(
                [$this->ui_factory->link()->standard(
                    $this->lng->txt('user_make_profile_public'),
                    $this->ctrl->getLinkTarget($this, 'showPublicProfile')
                )]
            );
        }
        return $this->ui_renderer->render($box);
    }

    public function initPersonalDataForm(): void
    {
        $this->form = new \ilPropertyFormGUI();
        $this->form->setFormAction($this->ctrl->getFormAction($this));
        $this->form->setId(self::PERSONAL_DATA_FORM_ID);

        $this->form = $this->profile->addFieldsToForm($this->form, Context::User, true, $this->user);

        $this->form->addCommandButton('savePersonalData', $this->lng->txt('user_save_continue'));
    }

    public function savePersonalData(): void
    {
        $this->initPersonalDataForm();
        $this->uploads->process();

        if (!$this->form->checkInput()
            || !$this->emailCompletionForced()
                && $this->emailChanged()
                && $this->addEmailChangeModal()
            || $this->loginChanged() && !$this->updateLoginOrSetErrorMessages()) {
            $this->form->setValuesByPost();
            $this->profile->tempStorePicture($this->form);
            $this->showPersonalData(true);
            return;
        }

        $this->addDataFromFormToUser();

        $this->user = $this->checklist_status->setStepSucessOnUser(
            ChecklistStatus::STEP_PROFILE_DATA,
            $this->user
        );
        $this->user->update();

        $this->tpl->setOnScreenMessage('success', $this->lng->txt('msg_obj_modified'), true);
        $this->ctrl->redirect($this, 'showPublicProfile');
    }

    private function emailChanged(): bool
    {
        $identifier_email = $this->profile->getFieldByClass(Email::class)->getIdentifier();
        $email_input = $this->form->getItemByPostVar($identifier_email);
        if ($email_input !== null && !$email_input->getDisabled()
            && $this->form->getInput($identifier_email) !== $this->user->getEmail()) {
            return true;
        }

        return false;
    }

    private function emailCompletionForced(): bool
    {
        $current_email = $this->user->getEmail();
        if (
            $this->user->getProfileIncomplete()
            && $this->profile->getFieldByClass(Email::class)->isRequired()
            && ($current_email === null || $current_email === '')
        ) {
            return true;
        }

        return false;
    }

    private function addEmailChangeModal(): bool
    {
        $form_id = 'form_' . self::PERSONAL_DATA_FORM_ID;

        $message = $this->lng->txt('confirm_logout_for_email_change');
        if ((int) $this->settings->get('new_registration_type', '1') === \ilRegistrationSettings::IL_REG_ACTIVATION) {
            $message .= '<br>' . $this->lng->txt('confirm_logout_for_email_change_with_confirmation');
        }

        $modal = $this->ui_factory->modal()->interruptive(
            $this->lng->txt('confirm'),
            $message,
            ''
        )->withActionButtonLabel($this->lng->txt('change'));
        $this->email_change_confirmation_modal = $modal->withOnLoad($modal->getShowSignal())
            ->withAdditionalOnLoadCode(
                static function ($id) use ($form_id) {
                    return "var button = {$id}.querySelector('input[type=\"submit\"]'); "
                    . "button.addEventListener('click', (e) => {e.preventDefault();"
                    . "document.getElementById('{$form_id}').submit();});";
                }
            );

        $this->form->setFormAction($this->ctrl->getFormActionByClass(self::class, 'goToEmailConfirmation'));
        return true;
    }

    private function loginChanged(): bool
    {
        if ($this->profile->userFieldEditableByUser('username')
            && $this->form->getInput('username') !== $this->user->getLogin()) {
            return true;
        }

        return false;
    }

    private function updateLoginOrSetErrorMessages(): bool
    {
        $login = $this->form->getInput('username');
        if ($login === '' || !\ilUtil::isLogin($login)) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt('form_input_not_valid'));
            $this->form->getItemByPostVar('username')->setAlert($this->lng->txt('login_invalid'));
            return false;
        }

        if (\ilObjUser::_loginExists($login, $this->user->getId())) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt('form_input_not_valid'));
            $this->form->getItemByPostVar('username')->setAlert($this->lng->txt('loginname_already_exists'));
            return false;
        }

        try {
            $this->user->updateLogin($login, Context::User);
            return true;
        } catch (\ilUserException $e) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt('form_input_not_valid'));
            $this->form->getItemByPostVar('username')->setAlert($e->getMessage());
            return false;
        }
    }

    public function goToEmailConfirmation(): void
    {
        $this->initPersonalDataForm();
        if (!$this->form->checkInput()
            || $this->loginChanged() && !$this->updateLoginOrSetErrorMessages()) {
            $this->form->setValuesByPost();
            $this->showPersonalData(true);
            return;
        }
        $this->addDataFromFormToUser([Email::class]);
        $this->user->update();

        \ilSession::setClosingContext(\ilSession::SESSION_CLOSE_USER);
        $this->auth_session->logout();
        session_unset();
        $token = $this->change_mail_token_repo->getNewTokenForUser(
            $this->user,
            $this->form->getInput(
                $this->profile->getFieldByClass(Email::class)->getIdentifier()
            ),
            time()
        );
        $this->ctrl->redirectToURL(
            $token->getUriForStatus($this->static_url->builder())->__toString()
        );
    }

    private function addDataFromFormToUser(
        array $skip_fields = []
    ): void {
        $this->user = $this->profile->addFormValuesToUser($this->form, Context::User, $this->user, $skip_fields);
        $this->user->setProfileIncomplete(false);

        $this->user->setTitle($this->user->getFullname());
        $this->user->setDescription($this->user->getEmail());
    }

    public function changeEmail(): void
    {
        $token = $this->change_mail_token_repo->getTokenForTokenString(
            $this->profile_request->getToken(),
            $this->user
        );

        if ($token === null) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt('email_could_not_be_changed'));
            $this->showPublicProfile();
            return;
        }

        if ($token->getStatus() === ChangeMailStatus::Login
            && (int) $this->settings->get('new_registration_type', '1') === \ilRegistrationSettings::IL_REG_ACTIVATION) {
            (new ChangeMailMail(
                $this->user,
                $this->change_mail_token_repo->moveToNextStep($token, time())
                        ->getUriForStatus($this->static_url->builder()),
                $this->lng,
                $this->logger
            ))->send($token->getNewEmail(), ChangeMailStatus::EmailConfirmation->getValidity($this->settings));
            $this->tpl->setOnScreenMessage('info', $this->lng->txt('change_email_email_sent'));
            $this->showPublicProfile();
            return;
        }

        $this->user->setEmail($token->getNewEmail());
        $this->user->update();
        $this->change_mail_token_repo->deleteEntryByToken($token->getToken());
        $this->change_mail_token_repo->deleteExpiredEntries();

        $this->tpl->setOnScreenMessage(
            'success',
            $this->lng->txt('saved_successfully')
        );
        $this->showPublicProfile();
        return;
    }

    public function showPublicProfile(bool $a_no_init = false): void
    {
        $this->tabs->activateTab('public_profile');
        $this->showChecklist(ChecklistStatus::STEP_PUBLISH_OPTIONS);

        $this->setHeader();

        if (!$a_no_init) {
            $this->initPublicProfileForm();
        }

        $this->tpl->setContent($this->form->getHTML());
        $this->tpl->printToStdout();
    }

    private function getProfilePortfolio(): ?int
    {
        if ($this->settings->get('user_portfolios')) {
            return \ilObjPortfolio::getDefaultPortfolio($this->user->getId());
        }
        return null;
    }

    private function initPublicProfileForm(): void
    {
        $this->form = new \ilPropertyFormGUI();

        $this->form->setTitle($this->lng->txt('user_publish_options'));
        $this->form->setDescription($this->lng->txt('user_public_profile_info'));
        $this->form->setFormAction($this->ctrl->getFormAction($this));

        $portfolio_id = $this->getProfilePortfolio();

        if (!$portfolio_id) {
            // Activate public profile
            $radg = new \ilRadioGroupInputGUI($this->lng->txt('user_activate_public_profile'), 'public_profile');
            $info = $this->lng->txt('user_activate_public_profile_info');
            $radg->setValue(
                (new Visibility($this->lng, $this->settings, $this->user))->getMode()
            );
            $op1 = new \ilRadioOption($this->lng->txt('usr_public_profile_disabled'), 'n', $this->lng->txt('usr_public_profile_disabled_info'));
            $radg->addOption($op1);
            $op2 = new \ilRadioOption($this->lng->txt('usr_public_profile_logged_in'), 'y');
            $radg->addOption($op2);
            if ($this->settings->get('enable_global_profiles')) {
                $op3 = new \ilRadioOption($this->lng->txt('usr_public_profile_global'), 'g');
                $radg->addOption($op3);
            }
            $this->form->addItem($radg);

            // #11773
            if ($this->settings->get('user_portfolios')) {
                // #10826
                $href = $this->ctrl->getLinkTargetByClass(\ilDashboardGUI::class, 'jumpToPortfolio');
                $prtf = '<br />' . $this->lng->txt('user_profile_portfolio');
                $prtf .= '<br /><a href="' . $href . '">&raquo; ' .
                    $this->lng->txt('user_portfolios') . '</a>';
                $info .= $prtf;
            }

            $radg->setInfo($info);
        } else {
            $this->ctrl->setParameterByClass(\ilDashboardGUI::class, 'prt_id', $portfolio_id);
            $href = $this->ctrl->getLinkTargetByClass(\ilDashboardGUI::class, 'jumpToPortfolio');
            $this->ctrl->clearParameterByClass(\ilDashboardGUI::class, 'prt_id');
            $prtf = $this->lng->txt('user_profile_portfolio_selected');
            $prtf .= '<br /><a href="' . $href . '">&raquo; ' .
                $this->lng->txt('portfolio') . '</a>';

            $info = new \ilCustomInputGUI($this->lng->txt('user_activate_public_profile'));
            $info->setHtml($prtf);
            $this->form->addItem($info);
            $this->showPublicProfileFields($this->form);
        }

        if (isset($op2)) {
            $this->showPublicProfileFields($this->form, null, $op2, false, '-1');
        }
        if (isset($op3)) {
            $this->showPublicProfileFields($this->form, null, $op3, false, '-2');
        }
        $this->form->setForceTopButtons(true);
        $this->form->addCommandButton('savePublicProfile', $this->lng->txt('user_save_continue'));
    }

    public function showPublicProfileFields(
        \ilPropertyFormGUI $form,
        ?array $prefs = null,
        \ilRadioOption|\ilCheckboxGroupInputGUI|null $parent = null,
        bool $anonymized = false,
        string $key_suffix = ''
    ): void {
        foreach ($this->profile->getVisibleFields(
            Context::User,
            null,
            [],
            [FirstName::class, LastName::class, Alias::class, OrganisationalUnits::class, Roles::class]
        ) as $field) {
            $value = $field->retrieveValueFromUser($this->user);
            if (!$anonymized && ($value === '' || $value === '-' || $value === null)) {
                continue;
            }
            if ($anonymized) {
                $value = null;
            }

            if ($field->isVisibleToUser()) {
                // #18795 - we should use ilUserProfile
                switch ($field->getIdentifier()) {
                    case 'avatar':
                        $caption = $this->lng->txt('personal_picture');
                        $value = $value !== null
                            ? "<img src='{$value}' alt='{$this->lng->txt('user_avatar')}' />"
                            : null;
                        break;

                    default:
                        $caption = $field->getLabel($this->lng);
                }
                $cb = new \ilCheckboxInputGUI($caption, self::PUBLISH_SETTINGS_PREFIX . $field->getIdentifier() . $key_suffix);
                $cb->setChecked(
                    $prefs === null
                        ? $field->isPublishedByUser($this->user)
                        : $prefs["public_{$field->getIdentifier()}"] ?? false
                );

                $cb->setOptionTitle(
                    $this->refinery->byTrying([
                        $this->refinery->kindlyTo()->string(),
                        $this->refinery->custom()->transformation(
                            function (mixed $v): string {
                                return array_reduce(
                                    $this->refinery->kindlyTo()->listOf(
                                        $this->refinery->kindlyTo()->string()
                                    )->transform($v),
                                    static fn(string $c, string $v): string => $c === ''
                                        ? $v : "{$c}, {$v}",
                                    ''
                                );
                            }
                        ),
                        $this->refinery->always('')
                    ])->transform($value)
                );

                if (!$parent) {
                    $form->addItem($cb);
                } else {
                    $parent->addSubItem($cb);
                }
            }
        }

        if (!$anonymized) {
            $handler = \ilBadgeHandler::getInstance();
            if ($handler->isActive()) {
                $badge_options = [];

                foreach (\ilBadgeAssignment::getInstancesByUserId($this->user->getId()) as $ass) {
                    // only active
                    if ($ass->getPosition()) {
                        $badge = new \ilBadge($ass->getBadgeId());
                        $badge_options[] = $badge->getTitle();
                    }
                }

                if (count($badge_options) > 1) {
                    $badge_order = new \ilNonEditableValueGUI($this->lng->txt('obj_bdga'), 'bpos' . $key_suffix);
                    $badge_order->setMultiValues($badge_options);
                    $badge_order->setValue(array_shift($badge_options));
                    $badge_order->setMulti(true, true, false);

                    if (!$parent) {
                        $form->addItem($badge_order);
                    } else {
                        $parent->addSubItem($badge_order);
                    }
                }
            }
        }

        // permalink
        $ne = new \ilNonEditableValueGUI($this->lng->txt('perma_link'), '');
        $ne->setValue(\ilLink::_getLink($this->user->getId(), 'usr'));
        if (!$parent) {
            $form->addItem($ne);
        } else {
            $parent->addSubItem($ne);
        }
    }

    public function savePublicProfile(): void
    {
        $this->initPublicProfileForm();
        if (!$this->form->checkInput()) {
            $this->form->setValuesByPost();
            $this->tpl->showPublicProfile(true);
        }

        if ($this->form->getInput('public_profile') !== '') {
            $this->user->setPref('public_profile', $this->form->getInput('public_profile'));
        }

        $this->user = array_reduce(
            $this->profile->getVisibleFields(Context::User, $this->user),
            fn(\ilObjUser $c, ProfileField $v): \ilObjUser =>
                $v->setPublishedOnUser($c, $this->getPublishedFromPost($v->getIdentifier())),
            $this->user
        );

        if (\ilBadgeHandler::getInstance()->isActive()) {
            $badge_positions = $this->form->getInput('bpos' . $this->buildKeySuffix()) ?? [];
            if (is_array($badge_positions) && $badge_positions !== []) {
                \ilBadgeAssignment::updatePositions($this->user->getId(), $badge_positions);
            }
        }

        $this->user = $this->checklist_status->setStepSucessOnUser(
            ChecklistStatus::STEP_PUBLISH_OPTIONS,
            $this->user
        );
        $this->user->update();

        // update lucene index
        \ilLuceneIndexer::updateLuceneIndex([(int) $this->user->getId()]);
        $this->tpl->setOnScreenMessage('success', $this->lng->txt('msg_obj_modified'), true);

        if (\ilSession::get('orig_request_target')) {
            $target = \ilSession::get('orig_request_target');
            \ilSession::set('orig_request_target', '');
            \ilUtil::redirect($target);
        }

        $this->ctrl->redirectByClass([self::class, PrivacySettingsGUI::class], '');
    }

    public function showExportImport(): void
    {
        $this->tabs->activateTab('export');
        $this->setHeader();

        $button = $this->ui_factory->link()->standard(
            $this->lng->txt('pd_export_profile'),
            $this->ctrl->getLinkTarget($this, 'exportPersonalData')
        );
        $this->toolbar->addStickyItem($button);

        $exp_file = $this->user->getPersonalDataExportFile();
        if ($exp_file != '') {
            $this->toolbar->addSeparator();
            $this->toolbar->addComponent(
                $this->ui_factory->link()->standard(
                    $this->lng->txt("pd_download_last_export_file"),
                    $this->ctrl->getLinkTarget($this, "downloadPersonalData")
                )
            );
        }

        $this->toolbar->addSeparator();
        $this->toolbar->addComponent(
            $this->ui_factory->link()->standard(
                $this->lng->txt("pd_import_personal_data"),
                $this->ctrl->getLinkTarget($this, "importPersonalDataSelection")
            )
        );

        $this->tpl->printToStdout();
    }

    public function exportPersonalData(): void
    {
        $this->user->exportPersonalData();
        $this->user->sendPersonalDataFile();
        $this->ctrl->redirect($this, 'showExportImport');
    }

    /**
     * Download personal data export file
     */
    public function downloadPersonalData(): void
    {
        $this->user->sendPersonalDataFile();
    }

    public function importPersonalDataSelection(): void
    {
        $this->tabs->activateTab('export');
        $this->setHeader();

        $this->initPersonalDataImportForm();

        $this->tpl->setContent($this->form->getHTML());
        $this->tpl->printToStdout();
    }

    public function initPersonalDataImportForm(): void
    {
        $this->form = new \ilPropertyFormGUI();

        // input file
        $fi = new \ilFileInputGUI($this->lng->txt('file'), 'file');
        $fi->setRequired(true);
        $fi->setSuffixes(['zip']);
        $this->form->addItem($fi);

        // profile data
        $cb = new \ilCheckboxInputGUI($this->lng->txt('pd_profile_data'), 'profile_data');
        $this->form->addItem($cb);

        // settings
        $cb = new \ilCheckboxInputGUI($this->lng->txt('settings'), 'settings');
        $this->form->addItem($cb);

        // personal notes
        $cb = new \ilCheckboxInputGUI($this->lng->txt('notes'), 'notes');
        $this->form->addItem($cb);

        // calendar entries
        $cb = new \ilCheckboxInputGUI($this->lng->txt('pd_private_calendars'), 'calendar');
        $this->form->addItem($cb);

        $this->form->addCommandButton('importPersonalData', $this->lng->txt('import'));
        $this->form->addCommandButton('showExportImport', $this->lng->txt('cancel'));

        $this->form->setTitle($this->lng->txt('pd_import_personal_data'));
        $this->form->setFormAction($this->ctrl->getFormAction($this));
    }

    public function importPersonalData(): void
    {
        $this->initPersonalDataImportForm();
        if ($this->form->checkInput()) {
            $this->user->importPersonalData(
                $_FILES['file'],
                (bool) $this->form->getInput('profile_data'),
                (bool) $this->form->getInput('settings'),
                (bool) $this->form->getInput('notes'),
                (bool) $this->form->getInput('calendar')
            );
            $this->tpl->setOnScreenMessage('success', $this->lng->txt('msg_obj_modified'), true);
            $this->ctrl->redirect($this, '');
        } else {
            $this->tabs->activateTab('export');
            $this->setHeader();
            $this->form->setValuesByPost();
            $this->tpl->setContent($this->form->getHTML());
            $this->tpl->printToStdout();
        }
    }

    private function showChecklist(int $active_step): void
    {
        $main_tpl = $this->tpl;
        $main_tpl->setRightContent($this->checklist->render($active_step));
    }

    private function getPublishedFromPost(string $identifier): bool
    {
        $key = self::PUBLISH_SETTINGS_PREFIX . $identifier . $this->buildKeySuffix();

        if (!$this->http->wrapper()->post()->has($key)) {
            return false;
        }

        return $this->http->wrapper()->post()->retrieve(
            $key,
            $this->refinery->kindlyTo()->string()
        ) === '1';
    }

    private function buildKeySuffix(): string
    {
        switch ($this->form->getInput('public_profile')) {
            case 'y':
                return '-1';
            case 'g':
                return '-2';
            default:
                return '';
        }
    }
}
