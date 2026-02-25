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

use ILIAS\User\LocalDIC;
use ILIAS\User\UserGUIRequest;
use ILIAS\User\Presentation\AdminTabs;
use ILIAS\User\Settings\Administration\SettingsGUI as AdminSettingsGUI;
use ILIAS\User\Settings\ConfigurationGUI as UserSettingsConfigurationGUI;
use ILIAS\User\Settings\NewAccountMail\SettingsGUI as NewAccountMailSettingsGUI;
use ILIAS\User\Settings\NewAccountMail\Repository as NewAccountMailRepository;
use ILIAS\User\Settings\ConfigurationRepository as UserSettingsConfigurationRepository;
use ILIAS\User\Settings\StartingPoint\SettingsGUI as StartingPointSettingsGUI;
use ILIAS\User\Profile\Fields\ConfigurationRepository as ProfileConfigurationRepository;
use ILIAS\User\Profile\Fields\ConfigurationGUI as ProfileFieldsConfigurationGUI;
use ILIAS\User\Profile\Fields\CustomFieldsGUI;
use ILIAS\User\Profile\Prompt\SettingsGUI as ProfileSettingsGUI;
use ILIAS\User\Profile\Prompt\Repository as PromptRepository;
use ILIAS\Filesystem\Util\Archive\LegacyArchives;
use ILIAS\Filesystem\Filesystem;
use ILIAS\FileUpload\FileUpload;
use ILIAS\ResourceStorage\Services as ResourceStorage;
use ILIAS\Mail\TemplateEngine\TemplateEngineFactoryInterface;

;

/**
 * @author       Stefan Meyer <meyer@leifos.com>
 * @author       Sascha Hofmann <saschahofmann@gmx.de>
 * @author       Helmut Schottmüller <helmut.schottmueller@mac.com>
 * @ilCtrl_Calls ilObjUserFolderGUI: ilPermissionGUI, ilUserTableGUI, ilRepositorySearchGUI, ilExportGUI
 * @ilCtrl_Calls ilObjUserFolderGUI: ILIAS\User\Settings\Administration\SettingsGUI
 * @ilCtrl_Calls ilObjUserFolderGUI: ILIAS\User\Settings\ConfigurationGUI
 * @ilCtrl_Calls ilObjUserFolderGUI: ILIAS\User\Settings\NewAccountMail\SettingsGUI
 * @ilCtrl_Calls ilObjUserFolderGUI: ILIAS\User\Settings\StartingPoint\SettingsGUI
 * @ilCtrl_Calls ilObjUserFolderGUI: ILIAS\User\Profile\Fields\ConfigurationGUI
 * @ilCtrl_Calls ilObjUserFolderGUI: ILIAS\User\Profile\Fields\CustomFieldsGUI
 * @ilCtrl_Calls ilObjUserFolderGUI: ILIAS\User\Profile\Prompt\SettingsGUI
 * @ilCtrl_Calls ilObjUserFolderGUI: ILIAS\User\Search\EndpointGUI
 */
class ilObjUserFolderGUI extends ilObjectGUI
{
    use ilTableCommandHelper;

    public const USER_FIELD_TRANSLATION_MAPPING = [
        'visible' => 'user_visible_in_profile',
        'changeable' => 'changeable',
        'searchable' => 'header_searchable',
        'required' => 'required_field',
        'export' => 'export',
        'course_export' => 'course_export',
        'group_export' => 'group_export',
        'prg_export' => 'prg_export',
        'visib_reg' => 'header_visible_registration',
        'visib_lua' => 'usr_settings_visib_lua',
        'changeable_lua' => 'usr_settings_changeable_lua'
    ];

    private ilPropertyFormGUI $form;
    private array $requested_ids; // Missing array type.
    private string $selected_action;
    private UserGUIRequest $user_request;
    private AdminTabs $admin_tabs;
    private int $user_owner_id = 0;
    private ilDBInterface $db;
    private TemplateEngineFactoryInterface $mail_template_engine_factory;
    private ilLogger $log;
    private ilAppEventHandler $event;
    private Filesystem $filesystem;
    private FileUpload $upload;
    private LegacyArchives $archives;
    private ResourceStorage $irss;
    private NewAccountMailRepository $account_mail_repo;
    private UserSettingsConfigurationRepository $user_settings_repo;
    private ProfileConfigurationRepository $profile_configuration_repo;
    private array $profile_field_change_listeners;

    public function __construct(
        $a_data,
        int $a_id,
        bool $a_call_by_reference
    ) {
        /** @var ILIAS\DI\Container $DIC */
        global $DIC;

        $this->event = $DIC['ilAppEventHandler'];
        $this->filesystem = $DIC->filesystem()->storage();
        $this->upload = $DIC['upload'];
        $this->db = $DIC['ilDB'];
        $this->mail_template_engine_factory = $DIC->mail()->templateEngineFactory();
        $this->archives = $DIC->legacyArchives();
        $this->irss = $DIC['resource_storage'];

        $local_dic = LocalDIC::dic();
        $this->account_mail_repo = $local_dic[NewAccountMailRepository::class];
        $this->user_settings_repo = $local_dic[UserSettingsConfigurationRepository::class];
        $this->profile_configuration_repo = $local_dic[ProfileConfigurationRepository::class];
        $this->profile_field_change_listeners = $local_dic['profile.fields.changelisteners'];

        $this->type = 'usrf';
        parent::__construct(
            $a_data,
            $a_id,
            $a_call_by_reference,
            false
        );

        $this->lng->loadLanguageModule('search');
        $this->lng->loadLanguageModule('user');
        $this->lng->loadLanguageModule('tos');
        $this->lng->loadLanguageModule('ps');
        $this->lng->loadLanguageModule('registration');
        $this->lng->loadLanguageModule('tos');
        $this->lng->loadLanguageModule('dpro');
        $this->lng->loadLanguageModule('ui');
        $this->lng->loadLanguageModule('mail');
        $this->lng->loadLanguageModule('meta');
        $this->lng->loadLanguageModule('chatroom');
        $this->lng->loadLanguageModule('administration');
        $this->lng->loadLanguageModule('dateplaner');
        $this->lng->loadLanguageModule('style');
        $this->lng->loadLanguageModule('awrn');
        $this->lng->loadLanguageModule('buddysystem');

        $this->ctrl->saveParameter(
            $this,
            'letter'
        );

        $this->admin_tabs = new AdminTabs(
            $this->tabs_gui,
            $this->lng,
            $this->ctrl,
            $this->access,
            $this->getRefId()
        );

        $this->user_request = new UserGUIRequest(
            $DIC->http(),
            $DIC->refinery()
        );

        $this->selected_action = $this->user_request->getSelectedAction();

        $this->log = ilLoggerFactory::getLogger('user');
        $this->requested_ids = $this->user_request->getIds();
    }

    public function setUserOwnerId(int $a_id): void
    {
        $this->user_owner_id = $a_id;
    }

    public function getUserOwnerId(): int
    {
        return $this->user_owner_id ?: USER_FOLDER_ID;
    }

    public function executeCommand(): void
    {
        $this->checkPermission('read');

        $next_class = $this->ctrl->getNextClass($this);
        $cmd = $this->ctrl->getCmd();
        $this->prepareOutput();

        switch ($next_class) {
            case strtolower(ilUserTableGUI::class):
                $u_table = new ilUserTableGUI(
                    $this,
                    'view'
                );
                $u_table->initFilter();
                $this->ctrl->setReturn(
                    $this,
                    'view'
                );
                $this->ctrl->forwardCommand($u_table);
                break;
            case strtolower(ilRepositorySearchGUI::class):
                if (!$this->access->checkRbacOrPositionPermissionAccess(
                    \ilObjUserFolder::PERM_READ_ALL,
                    \ilObjUserFolder::ORG_OP_EDIT_USER_ACCOUNTS,
                    USER_FOLDER_ID
                )) {
                    $this->ilias->raiseError(
                        $this->lng->txt('permission_denied'),
                        $this->ilias->error_obj->MESSAGE
                    );
                }

                $user_search = new ilRepositorySearchGUI();
                $user_search->setTitle($this->lng->txt('search_user_extended')); // #17502
                $user_search->enableSearchableCheck(false);
                $user_search->setUserLimitations(false);
                $user_search->setCallback(
                    $this,
                    'searchResultHandler',
                    $this->getUserMultiCommands(true)
                );
                $user_search->addUserAccessFilterCallable([$this, 'searchUserAccessFilterCallable']);
                $this->ctrl->setReturn(
                    $this,
                    'view'
                );
                $this->ctrl->forwardCommand($user_search);
                break;
            case strtolower(AdminSettingsGUI::class):
                $this->raiseErrorOnMissingWrite();
                $this->ctrl->forwardCommand(
                    new AdminSettingsGUI(
                        $this->lng,
                        $this->ctrl,
                        $this->access,
                        $this->settings,
                        $this->tpl,
                        $this->ui_factory,
                        $this->ui_renderer,
                        $this->refinery,
                        $this->request,
                        $this->profile_configuration_repo
                    )
                );
                break;
            case strtolower(UserSettingsConfigurationGUI::class):
                $this->raiseErrorOnMissingWrite();
                $this->ctrl->forwardCommand(
                    new UserSettingsConfigurationGUI(
                        $this->lng,
                        $this->ctrl,
                        $this->access,
                        $this->tpl,
                        $this->ui_factory,
                        $this->ui_renderer,
                        $this->refinery,
                        $this->request,
                        $this->request_wrapper,
                        $this->http,
                        $this->user_settings_repo
                    )
                );
                break;
            case strtolower(NewAccountMailSettingsGUI::class):
                $this->raiseErrorOnMissingWrite();
                $this->ctrl->forwardCommand(
                    new NewAccountMailSettingsGUI(
                        $this->lng,
                        $this->ctrl,
                        $this->access,
                        $this->tpl,
                        $this->mail_template_engine_factory,
                        $this->ui_factory,
                        $this->ui_renderer,
                        $this->refinery,
                        $this->request,
                        $this->irss,
                        $this->account_mail_repo
                    )
                );
                break;
            case strtolower(StartingPointSettingsGUI::class):
                $this->raiseErrorOnMissingWrite();
                $this->ctrl->forwardCommand(
                    new StartingPointSettingsGUI($this->ref_id)
                );
                break;
            case strtolower(ProfileFieldsConfigurationGUI::class):
                $this->raiseErrorOnMissingWrite();
                $this->ctrl->forwardCommand(
                    new ProfileFieldsConfigurationGUI(
                        $this->lng,
                        $this->ctrl,
                        $this->event,
                        $this->access,
                        $this->toolbar,
                        $this->tpl,
                        $this->ui_factory,
                        $this->ui_renderer,
                        $this->refinery,
                        $this->request,
                        $this->request_wrapper,
                        $this->post_wrapper,
                        $this->http,
                        $this->profile_field_change_listeners,
                        $this->profile_configuration_repo
                    )
                );
                break;
            case strtolower(CustomFieldsGUI::class):
                $this->raiseErrorOnMissingWrite();
                $this->ctrl->forwardCommand(
                    new CustomFieldsGUI(
                        $this->requested_ref_id,
                        $this->user_request->getFieldId()
                    )
                );
                break;
            case strtolower(ProfileSettingsGUI::class):
                $this->raiseErrorOnMissingWrite();
                $this->ctrl->forwardCommand(
                    new ProfileSettingsGUI(
                        $this->ctrl,
                        $this->lng,
                        $this->ui_factory,
                        $this->ui_renderer,
                        $this->tpl,
                        $this->request,
                        $this->refinery,
                        new PromptRepository(
                            $this->db,
                            $this->lng,
                            new ilSetting('user')
                        )
                    )
                );
                break;
            case strtolower(ilPermissionGUI::class):
                $perm_gui = new ilPermissionGUI($this);
                $this->ctrl->forwardCommand($perm_gui);
                break;
            default:
                if (!$cmd) {
                    $cmd = 'view';
                }
                $cmd .= 'Object';
                $this->$cmd();
                break;
        }
    }

    public function resetFilterObject(): void
    {
        $utab = new ilUserTableGUI(
            $this,
            'view'
        );
        $utab->resetOffset();
        $utab->resetFilter();
        $this->viewObject();
    }

    /**
     * Add new user
     */
    public function addUserObject(): void
    {
        $this->ctrl->setParameterByClass(
            'ilobjusergui',
            'new_type',
            'usr'
        );
        $this->ctrl->redirectByClass(
            ['iladministrationgui', 'ilobjusergui'],
            'create'
        );
    }

    public function applyFilterObject(): void
    {
        $utab = new ilUserTableGUI(
            $this,
            'view'
        );
        $utab->resetOffset();
        $utab->writeFilterToSession();
        $this->viewObject();
    }

    /**
     * list users
     */
    public function viewObject(
    ): void {
        if ($this->rbac_system->checkAccess('create_usr', $this->object->getRefId())
            || $this->rbac_system->checkAccess('cat_administrate_users', $this->object->getRefId())) {
            $this->toolbar->addComponent(
                $this->ui_factory->link()->standard(
                    $this->lng->txt('usr_add'),
                    $this->ctrl->getLinkTarget($this, 'addUser')
                )
            );

            $this->toolbar->addComponent(
                $this->ui_factory->link()->standard(
                    $this->lng->txt('import_users'),
                    $this->ctrl->getLinkTarget($this, 'importUserForm')
                )
            );
        }

        $utab = new ilUserTableGUI(
            $this,
            'view',
            ilUserTableGUI::MODE_USER_FOLDER,
            false
        );
        $utab->addFilterItemValue(
            'user_ids',
            $this->retrieveUserList()
        );
        $utab->getItems();

        $this->tpl->setContent($utab->getHTML());
    }

    protected function addUserAutoCompleteObject(): void
    {
        $auto = new ilUserAutoComplete();
        $auto->addUserAccessFilterCallable(\Closure::fromCallable([$this, 'filterUserIdsByRbacOrPositionOfCurrentUser']));
        $auto->setSearchFields(['login', 'firstname', 'lastname', 'email', 'second_email']);
        $auto->enableFieldSearchableCheck(false);
        $auto->setMoreLinkAvailable(true);

        if ($this->user_request->getFetchAll()) {
            $auto->setLimit(ilUserAutoComplete::MAX_ENTRIES);
        }

        echo $auto->getList($this->user_request->getTerm());
        exit();
    }

    /**
     * @param array<int> $user_ids
     */
    public function filterUserIdsByRbacOrPositionOfCurrentUser(array $user_ids): array
    {
        return $this->access->filterUserIdsByRbacOrPositionOfCurrentUser(
            \ilObjUserFolder::PERM_READ_ALL,
            \ilObjUserFolder::ORG_OP_EDIT_USER_ACCOUNTS,
            USER_FOLDER_ID,
            $user_ids
        );
    }

    public function chooseLetterObject(): void
    {
        $this->ctrl->redirect(
            $this,
            'view'
        );
    }

    /**
     * show possible subobjects (pulldown menu)
     * overwritten to prevent displaying of role templates in local role folders
     */
    protected function showPossibleSubObjects(): void
    {
        $subobj = null;

        $d = $this->obj_definition->getCreatableSubObjects($this->object->getType());

        if (!$this->rbac_system->checkAccess(
            'create_usr',
            $this->object->getRefId()
        )) {
            unset($d['usr']);
        }

        if (count($d) > 0) {
            foreach ($d as $row) {
                $count = 0;
                if ($row['max'] > 0) {
                    //how many elements are present?
                    for ($i = 0, $iMax = count($this->data['ctrl']); $i < $iMax; $i++) {
                        if ($this->data['ctrl'][$i]['type'] == $row['name']) {
                            $count++;
                        }
                    }
                }
                if ($row['max'] == '' || $count < $row['max']) {
                    $subobj[] = $row['name'];
                }
            }
        }

        if (is_array($subobj)) {
            //build form
            $opts = ilLegacyFormElementsUtil::formSelect(
                12,
                'new_type',
                $subobj
            );
            $this->tpl->setCurrentBlock('add_object');
            $this->tpl->setVariable(
                'SELECT_OBJTYPE',
                $opts
            );
            $this->tpl->setVariable(
                'BTN_NAME',
                'create'
            );
            $this->tpl->setVariable(
                'TXT_ADD',
                $this->lng->txt('add')
            );
            $this->tpl->parseCurrentBlock();
        }
    }

    public function cancelUserFolderActionObject(): void
    {
        $this->ctrl->redirect(
            $this,
            'view'
        );
    }

    public function cancelSearchActionObject(): void
    {
        $this->ctrl->redirectByClass(
            'ilrepositorysearchgui',
            'showSearchResults'
        );
    }

    public function confirmactivateObject(): void
    {
        if (!$this->checkUserManipulationAccessBool()) {
            $this->ilias->raiseError(
                $this->lng->txt('msg_no_perm_write'),
                $this->ilias->error_obj->WARNING
            );
        }

        // FOR ALL SELECTED OBJECTS
        foreach ($this->getActionUserIds() as $id) {
            $obj = \ilObjectFactory::getInstanceByObjId(
                $id,
                false
            );
            if ($obj instanceof \ilObjUser) {
                if (!$obj->getActive()) {
                    $obj->setLoginAttempts(0);
                }
                $obj->setActive(
                    true,
                    $this->user->getId()
                );
                $obj->update();
            }
        }

        $this->tpl->setOnScreenMessage('success', $this->lng->txt('user_activated'), true);

        if ($this->user_request->getFrSearch()) {
            $this->ctrl->redirectByClass(
                'ilRepositorySearchGUI',
                'show'
            );
        } else {
            $this->ctrl->redirect(
                $this,
                'view'
            );
        }
    }

    public function confirmdeactivateObject(): void
    {
        if (!$this->checkUserManipulationAccessBool()) {
            $this->ilias->raiseError(
                $this->lng->txt('msg_no_perm_write'),
                $this->ilias->error_obj->WARNING
            );
        }
        // FOR ALL SELECTED OBJECTS
        foreach ($this->getActionUserIds() as $id) {
            $obj = \ilObjectFactory::getInstanceByObjId(
                $id,
                false
            );
            if ($obj instanceof \ilObjUser) {
                $obj->setActive(
                    false,
                    $this->user->getId()
                );
                $obj->update();
            }
        }

        // Feedback
        $this->tpl->setOnScreenMessage('success', $this->lng->txt('user_deactivated'), true);

        if ($this->user_request->getFrSearch()) {
            $this->ctrl->redirectByClass(
                'ilRepositorySearchGUI',
                'show'
            );
        } else {
            $this->ctrl->redirect(
                $this,
                'view'
            );
        }
    }

    protected function confirmaccessFreeObject(): void
    {
        if (!$this->checkUserManipulationAccessBool()) {
            $this->ilias->raiseError(
                $this->lng->txt('msg_no_perm_write'),
                $this->ilias->error_obj->WARNING
            );
        }

        foreach ($this->getActionUserIds() as $id) {
            $obj = \ilObjectFactory::getInstanceByObjId(
                $id,
                false
            );
            if ($obj instanceof \ilObjUser) {
                $obj->setTimeLimitUnlimited(true);
                $obj->setTimeLimitFrom(null);
                $obj->setTimeLimitUntil(null);
                $obj->update();
            }
        }

        // Feedback
        $this->tpl->setOnScreenMessage('success', $this->lng->txt('access_free_granted'), true);

        if ($this->user_request->getFrSearch()) {
            $this->ctrl->redirectByClass(
                'ilRepositorySearchGUI',
                'show'
            );
        } else {
            $this->ctrl->redirect(
                $this,
                'view'
            );
        }
    }

    public function setAccessRestrictionObject(
        ?ilPropertyFormGUI $a_form = null,
        bool $a_from_search = false
    ): bool {
        if (!$a_form) {
            $a_form = $this->initAccessRestrictionForm($a_from_search);
        }
        $this->tpl->setContent($a_form->getHTML());

        // #10963
        return true;
    }

    protected function initAccessRestrictionForm(
        bool $a_from_search = false
    ): ?ilPropertyFormGUI {
        $user_ids = $this->getActionUserIds();
        if (!$user_ids) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt('select_one'));
            $this->viewObject();
            return null;
        }

        $form = new ilPropertyFormGUI();
        $form->setTitle($this->lng->txt('time_limit_add_time_limit_for_selected'));
        $form->setFormAction(
            $this->ctrl->getFormAction(
                $this,
                'confirmaccessRestrict'
            )
        );

        $from = new ilDateTimeInputGUI(
            $this->lng->txt('access_from'),
            'from'
        );
        $from->setShowTime(true);
        $from->setRequired(true);
        $form->addItem($from);

        $to = new ilDateTimeInputGUI(
            $this->lng->txt('access_until'),
            'to'
        );
        $to->setRequired(true);
        $to->setShowTime(true);
        $form->addItem($to);

        $form->addCommandButton(
            'confirmaccessRestrict',
            $this->lng->txt('confirm')
        );
        $form->addCommandButton(
            'view',
            $this->lng->txt('cancel')
        );

        foreach ($user_ids as $user_id) {
            $ufield = new ilHiddenInputGUI('id[]');
            $ufield->setValue((string) $user_id);
            $form->addItem($ufield);
        }

        // return to search?
        if ($a_from_search || $this->user_request->getFrSearch()) {
            $field = new ilHiddenInputGUI('frsrch');
            $field->setValue('1');
            $form->addItem($field);
        }

        return $form;
    }

    /**
     * @throws ilCtrlException
     * @throws ilDatabaseException
     * @throws ilObjectNotFoundException
     */
    protected function confirmaccessRestrictObject(): bool
    {
        $form = $this->initAccessRestrictionForm();
        if (!$form->checkInput()) {
            return $this->setAccessRestrictionObject($form);
        }

        $timefrom = $form->getItemByPostVar('from')->getDate()->get(IL_CAL_UNIX);
        $timeuntil = $form->getItemByPostVar('to')->getDate()->get(IL_CAL_UNIX);
        if ($timeuntil <= $timefrom) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt('time_limit_not_valid'));
            return $this->setAccessRestrictionObject($form);
        }

        if (!$this->checkUserManipulationAccessBool()) {
            $this->ilias->raiseError(
                $this->lng->txt('msg_no_perm_write'),
                $this->ilias->error_obj->WARNING
            );
        }
        foreach ($this->getActionUserIds() as $id) {
            $obj = \ilObjectFactory::getInstanceByObjId(
                $id,
                false
            );
            if ($obj instanceof \ilObjUser) {
                $obj->setTimeLimitUnlimited(false);
                $obj->setTimeLimitFrom((int) $timefrom);
                $obj->setTimeLimitUntil((int) $timeuntil);
                $obj->update();
            }
        }
        $this->tpl->setOnScreenMessage('success', $this->lng->txt('access_restricted'), true);

        if ($this->user_request->getFrSearch()) {
            $this->ctrl->redirectByClass(
                'ilRepositorySearchGUI',
                'show'
            );
        } else {
            $this->ctrl->redirect(
                $this,
                'view'
            );
        }
        return false;
    }

    public function confirmdeleteObject(): void
    {
        if (!$this->rbac_system->checkAccess(
            'delete',
            $this->object->getRefId()
        )) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt('msg_no_perm_delete'), true);
            $this->ctrl->redirect(
                $this,
                'view'
            );
        }

        $ids = $this->user_request->getIds();
        if (in_array(
            $this->user->getId(),
            $ids
        )) {
            $this->ilias->raiseError(
                $this->lng->txt('msg_no_delete_yourself'),
                $this->ilias->error_obj->WARNING
            );
        }

        // FOR ALL SELECTED OBJECTS
        foreach ($ids as $id) {
            // instatiate correct object class (usr)
            $obj = ilObjectFactory::getInstanceByObjId($id);
            $obj->delete();
        }

        // Feedback
        $this->tpl->setOnScreenMessage('success', $this->lng->txt('user_deleted'), true);

        if ($this->user_request->getFrSearch()) {
            $this->ctrl->redirectByClass(
                'ilRepositorySearchGUI',
                'show'
            );
        } else {
            $this->ctrl->redirect(
                $this,
                'view'
            );
        }
    }

    /**
     * Get selected items for table action
     * @return array<int>
     */
    protected function getActionUserIds(): array
    {
        if ($this->getSelectAllPostArray()['select_cmd_all']) {
            $utab = new ilUserTableGUI(
                $this,
                'view',
                ilUserTableGUI::MODE_USER_FOLDER,
                false
            );

            if (!$this->access->checkAccess(
                \ilObjUserFolder::PERM_READ_ALL,
                '',
                USER_FOLDER_ID
            ) &&
                $this->access->checkRbacOrPositionPermissionAccess(
                    \ilObjUserFolder::PERM_READ_ALL,
                    \ilObjUserFolder::ORG_OP_EDIT_USER_ACCOUNTS,
                    USER_FOLDER_ID
                )) {
                $users = \ilLocalUser::_getAllUserIds(\ilLocalUser::_getUserFolderId());
                $filtered_users = $this->access->filterUserIdsByRbacOrPositionOfCurrentUser(
                    \ilObjUserFolder::PERM_READ_ALL,
                    \ilObjUserFolder::ORG_OP_EDIT_USER_ACCOUNTS,
                    USER_FOLDER_ID,
                    $users
                );

                $utab->addFilterItemValue(
                    'user_ids',
                    $filtered_users
                );
            }

            return $utab->getUserIdsForFilter();
        } else {
            return $this->access->filterUserIdsByRbacOrPositionOfCurrentUser(
                \ilObjUserFolder::PERM_READ_ALL,
                ilObjUserFolder::ORG_OP_EDIT_USER_ACCOUNTS,
                USER_FOLDER_ID,
                $this->requested_ids
            );
        }
    }

    private function checkUserManipulationAccessBool(): bool
    {
        return $this->access->checkRbacOrPositionPermissionAccess(
            'write',
            \ilObjUserFolder::ORG_OP_EDIT_USER_ACCOUNTS,
            USER_FOLDER_ID
        );
    }

    public function showActionConfirmation(
        string $action,
        bool $a_from_search = false
    ): bool {
        $user_ids = $this->getActionUserIds();
        if (!$user_ids) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt('no_checkbox'));
            $this->viewObject();
            return false;
        }

        if (strcmp(
            $action,
            'accessRestrict'
        ) == 0) {
            return $this->setAccessRestrictionObject(
                null,
                $a_from_search
            );
        }
        if (strcmp(
            $action,
            'mail'
        ) == 0) {
            $this->mailObject();
            return false;
        }
        if (strcmp($action, 'addToClipboard') === 0) {
            $this->addToClipboardObject();
            return false;
        }

        unset($this->data);

        if (!$a_from_search) {
            $cancel = 'cancelUserFolderAction';
        } else {
            $cancel = 'cancelSearchAction';
        }

        // display confirmation message
        $cgui = new ilConfirmationGUI();
        $cgui->setFormAction($this->ctrl->getFormAction($this));
        $cgui->setHeaderText($this->lng->txt('info_' . $action . '_sure'));
        $cgui->setCancel(
            $this->lng->txt('cancel'),
            $cancel
        );
        $cgui->setConfirm(
            $this->lng->txt('confirm'),
            'confirm' . $action
        );

        if ($a_from_search) {
            $cgui->addHiddenItem(
                'frsrch',
                '1'
            );
        }

        foreach ($user_ids as $id) {
            $user = new ilObjUser((int) $id);

            $login = $user->getLastLogin();
            if (!$login) {
                $login = $this->lng->txt('never');
            } else {
                $login = ilDatePresentation::formatDate(
                    new ilDateTime(
                        $login,
                        IL_CAL_DATETIME
                    )
                );
            }

            $caption = $user->getFullname() . ' (' . $user->getLogin() . ')' . ', ' .
                $user->getEmail() . ' -  ' . $this->lng->txt('last_login') . ': ' . $login;

            $cgui->addItem(
                'id[]',
                (string) $id,
                $caption
            );
        }

        $this->tpl->setContent($cgui->getHTML());

        return true;
    }

    public function deleteUsersObject(): void
    {
        if (!$this->access->checkRbacOrPositionPermissionAccess(
            'delete',
            \ilObjUserFolder::ORG_OP_EDIT_USER_ACCOUNTS,
            USER_FOLDER_ID
        )) {
            $this->ilias->raiseError(
                $this->lng->txt('permission_denied'),
                $this->ilias->error_obj->MESSAGE
            );
        }

        if (in_array($this->user->getId(), $this->getActionUserIds())) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt('msg_no_delete_yourself'));
            $this->viewObject();
            return;
        }
        $this->showActionConfirmation('delete');
    }

    public function activateUsersObject(): void
    {
        $this->raiseErrorOnMissingWrite();
        $this->showActionConfirmation('activate');
    }

    public function deactivateUsersObject(): void
    {
        $this->raiseErrorOnMissingWrite();
        if (in_array($this->user->getId(), $this->getActionUserIds())) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt('no_deactivate_yourself'));
            $this->viewObject();
            return;
        }
        $this->showActionConfirmation('deactivate');
    }

    public function restrictAccessObject(): void
    {
        $this->raiseErrorOnMissingWrite();
        $this->showActionConfirmation('accessRestrict');
    }

    public function freeAccessObject(): void
    {
        $this->raiseErrorOnMissingWrite();
        $this->showActionConfirmation('accessFree');
    }

    public function userActionObject(): void
    {
        $this->raiseErrorOnMissingWrite();
        $this->showActionConfirmation($this->user_request->getSelectedAction());
    }

    public function importUserFormObject(): void
    {
        $this->tabs_gui->clearTargets();
        $this->tabs_gui->setBackTarget(
            $this->lng->txt('usrf'),
            $this->ctrl->getLinkTarget(
                $this,
                'view'
            )
        );
        if (
            !$this->rbac_system->checkAccess('create_usr', $this->object->getRefId())
            && !$this->access->checkAccess('cat_administrate_users', '', $this->object->getRefId())
        ) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt('permission_denied'));
            return;
        }
        $this->initUserImportForm();
        $this->tpl->setContent($this->form->getHTML());
    }

    public function initUserImportForm(): void
    {
        $this->form = new ilPropertyFormGUI();

        // Import File
        $fi = new ilFileInputGUI(
            $this->lng->txt('import_file'),
            'importFile'
        );
        $fi->setSuffixes(['xml']);
        $fi->setRequired(true);
        $this->form->addItem($fi);

        $this->form->addCommandButton(
            'importUserRoleAssignment',
            $this->lng->txt('import')
        );
        $this->form->addCommandButton(
            'importCancelled',
            $this->lng->txt('cancel')
        );

        $this->form->setTitle($this->lng->txt('import_users'));
        $this->form->setFormAction($this->ctrl->getFormAction($this));
    }

    protected function inAdministration(): bool
    {
        return (strtolower($this->user_request->getBaseClass()) === 'iladministrationgui');
    }

    public function importCancelledObject(): void
    {
        $import_dir = $this->getImportDir();
        if ($this->filesystem->hasDir($import_dir)) {
            $this->filesystem->deleteDir($import_dir);
        }

        if ($this->inAdministration()) {
            $this->ctrl->redirect(
                $this,
                'view'
            );
        } else {
            $this->ctrl->redirectByClass(
                'ilobjcategorygui',
                'listUsers'
            );
        }
    }

    public function getImportDir(): string
    {
        // For each user session a different directory must be used to prevent
        // that one user session overwrites the import data that another session
        // is currently importing.

        $importDir = 'user_import/usr_' . $this->user->getId() . '_' . mb_substr(session_id(), 0, 8);

        return $importDir;
    }

    /**
     * display form for user import with new FileSystem implementation
     */
    public function importUserRoleAssignmentObject(): void
    {
        $this->tabs_gui->clearTargets();
        $this->tabs_gui->setBackTarget(
            $this->lng->txt('usrf'),
            $this->ctrl->getLinkTarget(
                $this,
                'view'
            )
        );

        $this->initUserImportForm();
        if ($this->form->checkInput()) {
            $xml_file = $this->handleUploadedFiles();
            $xml_file_full_path = ilFileUtils::getDataDir() . '/' . $xml_file;

            list($form, $message) = $this->initUserRoleAssignmentForm($xml_file_full_path);

            $this->tpl->setContent($message . $this->ui_renderer->render($form));
        } else {
            $this->form->setValuesByPost();
            $this->tpl->setContent($this->form->getHTML());
        }
    }

    /**
     * @throws ilCtrlException
     * @return array<\ILIAS\UI\Component\Input\Container\Form\Standard, string>
     */
    private function initUserRoleAssignmentForm(string $xml_file_full_path): array
    {
        $global_roles_assignment_info = null;
        $local_roles_assignment_info = null;

        $import_parser = new ilUserImportParser(
            $xml_file_full_path,
            ilUserImportParser::IL_VERIFY
        );
        $import_parser->startParsing();

        $message = $this->verifyXmlData($import_parser);

        $xml_file_name = explode(
            '/',
            $xml_file_full_path
        );
        $roles_import_filename = $this->ui_factory->input()->field()
            ->text($this->lng->txt('import_file'))
            ->withDisabled(true)
            ->withValue(end($xml_file_name));

        $roles_import_count = $this->ui_factory->input()->field()
            ->numeric($this->lng->txt('num_users'))
            ->withDisabled(true)
            ->withValue($import_parser->getUserCount());

        $import_parser = new ilUserImportParser(
            $xml_file_full_path,
            ilUserImportParser::IL_EXTRACT_ROLES
        );
        $import_parser->startParsing();

        $roles = $import_parser->getCollectedRoles();
        $all_gl_roles = $this->rbac_review->getRoleListByObject(ROLE_FOLDER_ID);
        $gl_roles = [];
        $roles_of_user = $this->rbac_review->assignedRoles($this->user->getId());
        foreach ($all_gl_roles as $obj_data) {
            // check assignment permission if called from local admin
            if ($this->object->getRefId() != USER_FOLDER_ID
                && !in_array(SYSTEM_ROLE_ID, $roles_of_user)
                && !ilObjRole::_getAssignUsersStatus($obj_data['obj_id'])
            ) {
                continue;
            }
            // exclude anonymous role from list
            if ($obj_data['obj_id'] != ANONYMOUS_ROLE_ID
                && ($obj_data['obj_id'] != SYSTEM_ROLE_ID
                    || in_array(SYSTEM_ROLE_ID, $roles_of_user))
            ) {
                $gl_roles[$obj_data['obj_id']] = $obj_data['title'];
            }
        }

        // global roles
        $got_globals = false;
        $global_selects = [];
        foreach ($roles as $role_id => $role) {
            if ($role['type'] === 'Global') {
                $select_options = [];
                if (!$got_globals) {
                    $global_roles_assignment_info = $this->ui_factory->input()->field()
                        ->text($this->lng->txt('roles_of_import_global'))
                        ->withDisabled(true)
                        ->withValue($this->lng->txt('assign_global_role'));
                } else {
                    $select_options[] = $this->lng->txt('usrimport_ignore_role');
                }

                foreach ($gl_roles as $key => $value) {
                    $select_options[$role_id . '-' . $key] = $value;
                }

                // pre selection for role
                $pre_select = array_search(
                    $role['name'],
                    $select_options
                );
                if (!$pre_select) {
                    switch ($role['name']) {
                        case 'Administrator':    // ILIAS 2/3 Administrator
                            $pre_select = array_search(
                                'Administrator',
                                $select_options
                            );
                            break;

                        case 'Autor':            // ILIAS 2 Author
                            $pre_select = array_search(
                                'User',
                                $select_options
                            );
                            break;

                        case 'Lerner':            // ILIAS 2 Learner
                            $pre_select = array_search(
                                'User',
                                $select_options
                            );
                            break;

                        case 'Gast':            // ILIAS 2 Guest
                            $pre_select = array_search(
                                'Guest',
                                $select_options
                            );
                            break;

                        case 'User':
                            $pre_select = array_search(
                                'User',
                                $select_options
                            );
                            break;
                    }
                }

                $select = $this->ui_factory->input()->field()
                    ->select(
                        $role['name'],
                        $select_options
                    )
                    ->withValue($pre_select);

                if (!$got_globals) {
                    $got_globals = true;
                    $global_selects[] = $select->withRequired(true);
                } else {
                    $global_selects[] = $select;
                }
            }
        }

        // Check if local roles need to be assigned
        $got_locals = false;
        foreach ($roles as $role_id => $role) {
            if ($role['type'] == 'Local') {
                $got_locals = true;
                break;
            }
        }

        if ($got_locals) {
            $local_roles_assignment_info = $this->ui_factory->input()->field()
                ->text($this->lng->txt('roles_of_import_local'))
                ->withDisabled(true)
                ->withValue($this->lng->txt('assign_local_role'));

            // get local roles
            if ($this->object->getRefId() == USER_FOLDER_ID) {
                // The import function has been invoked from the user folder
                // object. In this case, we show only matching roles,
                // because the user folder object is considered the parent of all
                // local roles and may contains thousands of roles on large ILIAS
                // installations.
                $loc_roles = [];

                $roleMailboxSearch = new ilRoleMailboxSearch(new ilMailRfc822AddressParserFactory());
                foreach ($roles as $role_id => $role) {
                    if ($role['type'] == 'Local') {
                        $searchName = (strpos($role['name'], '#') === 0) ? $role['name'] : '#' . $role['name'];
                        $matching_role_ids = $roleMailboxSearch->searchRoleIdsByAddressString($searchName);
                        foreach ($matching_role_ids as $mid) {
                            if (!in_array(
                                $mid,
                                $loc_roles
                            )) {
                                $loc_roles[] = $mid;
                            }
                        }
                    }
                }
            } else {
                // The import function has been invoked from a locally
                // administrated category. In this case, we show all roles
                // contained in the subtree of the category.
                $loc_roles = $this->rbac_review->getAssignableRolesInSubtree($this->object->getRefId());
            }
            $l_roles = [];

            // create a search array with  .
            foreach ($loc_roles as $key => $loc_role) {
                // fetch context path of role
                $rolf = $this->rbac_review->getFoldersAssignedToRole(
                    $loc_role,
                    true
                );

                // only process role folders that are not set to status 'deleted'
                // and for which the user has write permissions.
                // We also don't show the roles which are in the ROLE_FOLDER_ID folder.
                // (The ROLE_FOLDER_ID folder contains the global roles).
                if (
                    !$this->rbac_review->isDeleted($rolf[0]) &&
                    $this->rbac_system->checkAccess(
                        'write',
                        $rolf[0]
                    ) &&
                    $rolf[0] != ROLE_FOLDER_ID
                ) {
                    // A local role is only displayed, if it is contained in the subtree of
                    // the localy administrated category. If the import function has been
                    // invoked from the user folder object, we show all local roles, because
                    // the user folder object is considered the parent of all local roles.
                    // Thus, if we start from the user folder object, we initialize the
                    // isInSubtree variable with true. In all other cases it is initialized
                    // with false, and only set to true if we find the object id of the
                    // locally administrated category in the tree path to the local role.
                    $is_in_subtree = $this->object->getRefId() == USER_FOLDER_ID;

                    $path_array = [];
                    if ($this->tree->isInTree($rolf[0])) {
                        // Create path. Paths which have more than 4 segments
                        // are truncated in the middle.
                        $tmpPath = $this->tree->getPathFull($rolf[0]);
                        $tmpPath[] = $rolf[0];//adds target item to list

                        for ($i = 1, $n = count($tmpPath) - 1; $i < $n; $i++) {
                            if ($i < 3 || $i > $n - 3) {
                                $path_array[] = $tmpPath[$i]['title'];
                            } elseif ($i == 3 || $i == $n - 3) {
                                $path_array[] = '...';
                            }

                            $is_in_subtree |= $tmpPath[$i]['obj_id'] == $this->object->getId();
                        }
                        //revert this path for a better readability in dropdowns #18306
                        $path = implode(
                            ' < ',
                            array_reverse($path_array)
                        );
                    } else {
                        $path = '<b>Rolefolder ' . $rolf[0] . ' not found in tree! (Role ' . $loc_role . ')</b>';
                    }
                    $roleMailboxAddress = (new \ilRoleMailboxAddress($loc_role))->value();
                    $l_roles[$loc_role] = $roleMailboxAddress . ', ' . $path;
                }
            }

            natcasesort($l_roles);
            $l_roles['ignore'] = $this->lng->txt('usrimport_ignore_role');

            $roleMailboxSearch = new ilRoleMailboxSearch(new ilMailRfc822AddressParserFactory());
            $local_selects = [];
            foreach ($roles as $role_id => $role) {
                if ($role['type'] == 'Local') {
                    $searchName = (strpos($role['name'], '#') === 0) ? $role['name'] : '#' . $role['name'];
                    $matching_role_ids = $roleMailboxSearch->searchRoleIdsByAddressString($searchName);
                    $pre_select = count($matching_role_ids) == 1 ? $role_id . '-' . $matching_role_ids[0] : 'ignore';

                    $selectable_roles = [];
                    if ($this->object->getRefId() == USER_FOLDER_ID) {
                        // There are too many roles in a large ILIAS installation
                        // that's why whe show only a choice with the the option 'ignore',
                        // and the matching roles.
                        $selectable_roles['ignore'] = $this->lng->txt('usrimport_ignore_role');
                        foreach ($matching_role_ids as $id) {
                            $selectable_roles[$role_id . '-' . $id] = $l_roles[$id];
                        }
                    } else {
                        foreach ($l_roles as $local_role_id => $value) {
                            if ($local_role_id !== 'ignore') {
                                $selectable_roles[$role_id . '-' . $local_role_id] = $value;
                            }
                        }
                    }

                    if (count($selectable_roles) > 0) {
                        $select = $this->ui_factory->input()->field()
                            ->select($role['name'], $selectable_roles)
                            ->withRequired(true);
                        if (array_key_exists($pre_select, $selectable_roles)) {
                            $select = $select->withValue($pre_select);
                        }
                        $local_selects[] = $select;
                    }
                }
            }
        }

        $handlers = [
            ilUserImportParser::IL_IGNORE_ON_CONFLICT => $this->lng->txt('ignore_on_conflict'),
            ilUserImportParser::IL_UPDATE_ON_CONFLICT => $this->lng->txt('update_on_conflict')
        ];

        $conflict_action_select = $this->ui_factory->input()->field()
            ->select(
                $this->lng->txt('conflict_handling'),
                $handlers,
                str_replace(
                    '\n',
                    '<br>',
                    $this->lng->txt('usrimport_conflict_handling_info')
                )
            )
            ->withValue(ilUserImportParser::IL_IGNORE_ON_CONFLICT)
            ->withRequired(true);

        // new account mail
        $this->lng->loadLanguageModule('mail');
        $amail = $this->account_mail_repo->getFor($this->lng->getDefaultLanguage());
        $mail_section = null;
        if ($amail->getSubject() !== '' && $amail->getBody() !== '') {
            $send_checkbox = $this->ui_factory->input()->field()->checkbox($this->lng->txt('user_send_new_account_mail'))
                                ->withValue(true);

            $mail_section = $this->ui_factory->input()->field()->section(
                [$send_checkbox],
                $this->lng->txt('mail_account_mail')
            );
        }

        $file_info_section = $this->ui_factory->input()->field()->section(
            [
                'filename' => $roles_import_filename,
                'import_count' => $roles_import_count,
            ],
            $this->lng->txt('file_info')
        );

        $form_action = $this->ctrl->getFormActionByClass(self::class, 'importUsers');

        $form_elements = [
            'file_info' => $file_info_section
        ];

        if (!empty($global_selects)) {
            $global_role_info_section = $this->ui_factory->input()
                ->field()
                ->section([$global_roles_assignment_info], $this->lng->txt('global_role_assignment'));
            $global_role_selection_section = $this->ui_factory->input()->field()->section($global_selects, '');
            $form_elements['global_role_info'] = $global_role_info_section;
            $form_elements['global_role_selection'] = $global_role_selection_section;
        }

        if (!empty($local_selects)) {
            $local_role_info_section = $this->ui_factory->input()->field()->section(
                [$local_roles_assignment_info],
                $this->lng->txt('local_role_assignment')
            );
            $local_role_selection_section = $this->ui_factory->input()->field()->section(
                $local_selects,
                ''
            );

            $form_elements['local_role_info'] = $local_role_info_section;
            $form_elements['local_role_selection'] = $local_role_selection_section;
        }

        $form_elements['conflict_action'] = $this->ui_factory->input()->field()->section([$conflict_action_select], '');

        if ($mail_section !== null) {
            $form_elements['send_mail'] = $mail_section;
        }

        return [$this->ui_factory->input()->container()->form()->standard(
            $form_action,
            $form_elements
        ), $message];
    }

    private function handleUploadedFiles(): string
    {
        $subdir = '';
        $xml_file = '';

        $import_dir = $this->getImportDir();

        if (!$this->upload->hasBeenProcessed()) {
            $this->upload->process();
        }

        // recreate user import directory
        if ($this->filesystem->hasDir($import_dir)) {
            $this->filesystem->deleteDir($import_dir);
        }
        $this->filesystem->createDir($import_dir);

        foreach ($this->upload->getResults() as $single_file_upload) {
            $file_name = $single_file_upload->getName();
            $parts = pathinfo($file_name);

            //check if upload status is ok
            if (!$single_file_upload->isOK()) {
                $this->filesystem->deleteDir($import_dir);
                $this->ilias->raiseError(
                    $this->lng->txt('no_import_file_found'),
                    $this->ilias->error_obj->MESSAGE
                );
            }

            // move uploaded file to user import directory
            $this->upload->moveFilesTo(
                $import_dir,
                \ILIAS\FileUpload\Location::STORAGE
            );

            // handle zip file
            if ($single_file_upload->getMimeType() == 'application/zip') {
                // Workaround: unzip function needs full path to file. Should be replaced once Filesystem has own unzip implementation
                $full_path = ilFileUtils::getDataDir() . '/user_import/usr_'
                    . $this->user->getId() . '_' . session_id() . '/' . $file_name;
                $this->archives->unzip($full_path);

                $xml_file = null;
                $file_list = $this->filesystem->listContents($import_dir);

                foreach ($file_list as $key => $a_file) {
                    if (substr(
                        $a_file->getPath(),
                        -4
                    ) == '.xml') {
                        unset($file_list[$key]);
                        $xml_file = $a_file->getPath();
                        break;
                    }
                }

                //Removing all files except the one to be imported, to make sure to get the right one in import-function
                foreach ($file_list as $a_file) {
                    $this->filesystem->delete($a_file->getPath());
                }

                if (is_null($xml_file)) {
                    $subdir = basename(
                        $parts['basename'],
                        '.' . $parts['extension']
                    );
                    $xml_file = $import_dir . '/' . $subdir . '/' . $subdir . '.xml';
                }
            } // handle xml file
            else {
                $a = $this->filesystem->listContents($import_dir);
                $file = end($a);
                $xml_file = $file->getPath();
            }

            // check xml file
            if (!$this->filesystem->has($xml_file)) {
                $this->filesystem->deleteDir($import_dir);
                $this->ilias->raiseError(
                    $this->lng->txt('no_xml_file_found_in_zip')
                    . ' ' . $subdir . '/' . $subdir . '.xml',
                    $this->ilias->error_obj->MESSAGE
                );
            }
        }

        return $xml_file;
    }

    public function verifyXmlData(ilUserImportParser $import_parser): string
    {
        $import_dir = $this->getImportDir();
        switch ($import_parser->getErrorLevel()) {
            case ilUserImportParser::IL_IMPORT_SUCCESS:
                return '';
            case ilUserImportParser::IL_IMPORT_WARNING:
                return $import_parser->getProtocolAsHTML($this->lng->txt("verification_warning_log"));
            case ilUserImportParser::IL_IMPORT_FAILURE:
                $this->filesystem->deleteDir($import_dir);
                $this->tpl->setOnScreenMessage(
                    'failure',
                    $this->lng->txt('verification_failed') . $import_parser->getProtocolAsHTML(
                        $this->lng->txt('verification_failure_log')
                    ),
                    true
                );
                $this->ctrl->redirectByClass(self::class, 'importUserForm');
        }
    }

    /**
     * Import Users with new form implementation
     */
    public function importUsersObject(): void
    {
        $result = [];
        $xml_file = '';
        $import_dir = $this->getImportDir();

        $file_list = $this->filesystem->listContents($import_dir);

        if (count($file_list) > 1) {
            $this->filesystem->deleteDir($import_dir);
            $this->tpl->setOnScreenMessage($this->lng->txt('usrimport_wrong_file_count'), true);
            $this->redirectAfterImport();
        }
        $xml_file = $file_list[0]->getPath();

        //Need full path to xml file to initialise form
        $xml_path = ilFileUtils::getDataDir() . '/' . $xml_file;

        if (!$this->user_request->isPost()) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt('usrimport_form_not_evaluabe'), true);
            $this->redirectAfterImport();
        }

        $form = $this->initUserRoleAssignmentForm($xml_path)[0]->withRequest($this->user_request->getRequest());
        $result = $form->getData();

        if ($result === null) {
            $this->tpl->setContent($this->ui_renderer->render($form));
            return;
        }

        $rule = $result['conflict_action'][0] ?? 1;

        //If local roles exist, merge the roles that are to be assigned, otherwise just take the array that has global roles
        $local_role_selection = (array) ($result['local_role_selection'] ?? []);
        $global_role_selection = (array) ($result['global_role_selection'] ?? []);
        $roles = array_merge(
            $local_role_selection,
            $global_role_selection
        );

        $role_assignment = [];
        foreach ($roles as $value) {
            $keys = explode(
                '-',
                $value
            );
            if (count($keys) === 2) {
                $role_assignment[$keys[0]] = $keys[1];
            }
        }

        $import_parser = new ilUserImportParser(
            $xml_path,
            ilUserImportParser::IL_USER_IMPORT,
            (int) $rule
        );
        $import_parser->setFolderId($this->getUserOwnerId());

        // Catch hack attempts
        // We check here again, if the role folders are in the tree, and if the
        // user has permission on the roles.
        if (!empty($role_assignment)) {
            $global_roles = $this->rbac_review->getGlobalRoles();
            $roles_of_user = $this->rbac_review->assignedRoles($this->user->getId());
            foreach ($role_assignment as $role_id_string) {
                $role_id = $this->refinery->byTrying([
                    $this->refinery->kindlyTo()->int(),
                    $this->refinery->always(null)
                ])->transform($role_id_string);
                if ($role_id === null) {
                    continue;
                }
                $this->redirectOnRoleWithMissingWrite(
                    $role_id,
                    $roles_of_user,
                    $global_roles,
                    $xml_path
                );
            }
        }

        if (isset($result['send_mail'])) {
            $import_parser->setSendMail($result['send_mail'][0]);
        }

        $import_parser->setRoleAssignment($role_assignment);
        $import_parser->startParsing();

        // purge user import directory
        $this->filesystem->deleteDir($import_dir);

        switch ($import_parser->getErrorLevel()) {
            case ilUserImportParser::IL_IMPORT_SUCCESS:
                $this->tpl->setOnScreenMessage(
                    'success',
                    $this->lng->txt('user_imported'),
                    true
                );
                break;
            case ilUserImportParser::IL_IMPORT_WARNING:
                $this->tpl->setOnScreenMessage(
                    'success',
                    $this->lng->txt('user_imported_with_warnings')
                    . $import_parser->getProtocolAsHTML(
                        $this->lng->txt('import_warning_log')
                    ),
                    true
                );
                break;
            case ilUserImportParser::IL_IMPORT_FAILURE:
                $this->tpl->setOnScreenMessage('failure', $this->lng->txt('user_import_failed'), true);
                $this->redirectAfterImport();
                break;
        }

        if ($this->inAdministration()) {
            $this->ctrl->redirect(
                $this,
                'view'
            );
        } else {
            $this->ctrl->redirectByClass(
                'ilobjcategorygui',
                'listUsers'
            );
        }
    }

    private function redirectOnRoleWithMissingWrite(
        int $role_id,
        array $roles_of_user,
        array $global_roles,
        string $import_dir
    ): void {
        if (in_array(
            $role_id,
            $global_roles
        )) {
            if (in_array(
                SYSTEM_ROLE_ID,
                $roles_of_user
            )) {
                return;
            }

            if ($role_id === SYSTEM_ROLE_ID
                || $this->object->getRefId() !== USER_FOLDER_ID
                    && !ilObjRole::_getAssignUsersStatus($role_id)
            ) {
                $this->filesystem->deleteDir($import_dir);
                $this->tpl->setOnScreenMessage(
                    'failure',
                    $this->lng->txt('usrimport_with_specified_role_not_permitted'),
                    true
                );
                $this->redirectAfterImport();
            }
            return;
        }

        $rolf = $this->rbac_review->getFoldersAssignedToRole(
            $role_id,
            true
        );
        if ($this->rbac_review->isDeleted($rolf[0])
            || !$this->rbac_system->checkAccess(
                'write',
                $rolf[0]
            )
        ) {
            $this->filesystem->deleteDir($import_dir);
            $this->tpl->setOnScreenMessage(
                'failure',
                $this->lng->txt('usrimport_with_specified_role_not_permitted'),
                true
            );
            $this->redirectAfterImport();
        }
    }

    /**
     * Download selected export files
     * Sends a selected export file for download
     */
    public function downloadExportFileObject(): void
    {
        $files = $this->user_request->getFiles();
        if (count($files) == 0) {
            $this->ilias->raiseError(
                $this->lng->txt('no_checkbox'),
                $this->ilias->error_obj->MESSAGE
            );
        }

        if (count($files) > 1) {
            $this->ilias->raiseError(
                $this->lng->txt('select_max_one_item'),
                $this->ilias->error_obj->MESSAGE
            );
        }

        $file = basename($files[0]);

        $export_dir = $this->object->getExportDirectory();
        ilFileDelivery::deliverFileLegacy(
            $export_dir . '/' . $file,
            $file
        );
    }

    public function confirmDeleteExportFileObject(): void
    {
        $files = $this->user_request->getFiles();
        if (count($files) == 0) {
            $this->ilias->raiseError(
                $this->lng->txt('no_checkbox'),
                $this->ilias->error_obj->MESSAGE
            );
        }

        $cgui = new ilConfirmationGUI();
        $cgui->setFormAction($this->ctrl->getFormAction($this));
        $cgui->setHeaderText($this->lng->txt('info_delete_sure'));
        $cgui->setCancel(
            $this->lng->txt('cancel'),
            'cancelDeleteExportFile'
        );
        $cgui->setConfirm(
            $this->lng->txt('confirm'),
            'deleteExportFile'
        );

        // BEGIN TABLE DATA
        foreach ($files as $file) {
            $cgui->addItem(
                'file[]',
                $file,
                $file,
                ilObject::_getIcon($this->object->getId()),
                $this->lng->txt('obj_usrf')
            );
        }

        $this->tpl->setContent($cgui->getHTML());
    }

    public function cancelDeleteExportFileObject(): void
    {
        $this->ctrl->redirectByClass(
            [self::class, ilExportGUI::class],
            'export'
        );
    }

    public function deleteExportFileObject(): void
    {
        $this->raiseErrorOnMissingWrite();
        $files = $this->user_request->getFiles();
        $export_dir = $this->object->getExportDirectory();
        foreach ($files as $file) {
            $file = basename($file);

            $exp_file = $export_dir . '/' . $file;
            if (is_file($exp_file)) {
                unlink($exp_file);
            }
        }
        $this->ctrl->redirectByClass(
            [self::class, ilExportGUI::class],
            'export'
        );
    }

    /**
     * @throws ilCtrlException
     * @throws ilObjectException
     */
    protected function performExportObject(): void
    {
        $this->checkPermission(\ilObjUserFolder::PERM_READ_ALL_AND_WRITE);

        $this->object->buildExportFile($this->user_request->getExportType());
        $this->ctrl->redirectByClass(
            [self::class, ilExportGUI::class],
            'export'
        );
    }

    public function exportObject(): void
    {
        $this->checkPermission(\ilObjUserFolder::PERM_READ_ALL_AND_WRITE);

        $export_types = [
            'userfolder_export_excel_x86',
            'userfolder_export_xml'
        ];
        $options = [];
        foreach ($export_types as $type) {
            $this->ctrl->setParameterByClass(self::class, 'export_type', $type);
            $options[] = $this->ui_factory->button()->shy(
                $this->lng->txt($type),
                $this->ctrl->getLinkTargetByClass(self::class, 'performExport')
            );
        }
        $type_selection = $this->ui_factory->dropdown()->standard($options)
            ->withLabel($this->lng->txt('create_export_file'));

        $this->toolbar->addComponent(
            $type_selection,
            true
        );

        $table = new \ilUserExportFileTableGUI(
            $this,
            'export'
        );
        $table->init();
        $table->parse($this->object->getExportFiles());

        $this->tpl->setContent($table->getHTML());
    }

    public function getAdminTabs(): void
    {
        $this->getTabs();
    }

    protected function getTabs(): void
    {
        $this->admin_tabs->initializeTabs();
    }

    public static function _goto(string $a_user): void
    {
        global $DIC;

        $a_user = (int) $a_user;
        $main_tpl = $DIC->ui()->mainTemplate();

        $ilAccess = $DIC['ilAccess'];
        $ilErr = $DIC['ilErr'];
        $lng = $DIC['lng'];
        $ctrl = $DIC['ilCtrl'];

        $a_target = USER_FOLDER_ID;

        if ($ilAccess->checkAccess(
            'read',
            '',
            $a_target
        )) {
            $ctrl->redirectToURL('ilias.php?baseClass=ilAdministrationGUI&ref_id=' . $a_target . '&jmpToUser=' . $a_user);
            exit;
        } else {
            if ($ilAccess->checkAccess(
                'read',
                '',
                ROOT_FOLDER_ID
            )) {
                $main_tpl->setOnScreenMessage('failure', sprintf(
                    $lng->txt('msg_no_perm_read_item'),
                    ilObject::_lookupTitle(ilObject::_lookupObjId($a_target))
                ), true);
                ilObjectGUI::_gotoRepositoryRoot();
            }
        }
        $ilErr->raiseError(
            $lng->txt('msg_no_perm_read'),
            $ilErr->FATAL
        );
    }

    /**
     * Jump to edit screen for user
     */
    public function jumpToUserObject(): void
    {
        $jump_to_user = $this->user_request->getJumpToUser();
        if (ilObject::_lookupType($jump_to_user) == 'usr') {
            $this->ctrl->setParameterByClass(
                'ilobjusergui',
                'obj_id',
                $jump_to_user
            );
            $this->ctrl->redirectByClass(
                'ilobjusergui',
                'view'
            );
        }
    }

    public function searchUserAccessFilterCallable(array $a_user_ids): array // Missing array type.
    {
        if ($this->checkPermissionBool(\ilObjUserFolder::PERM_READ_ALL, '', '', USER_FOLDER_ID)
            || $this->checkPermissionBool('read_users')) {
            return $a_user_ids;
        }

        return $this->access->filterUserIdsByPositionOfCurrentUser(
            \ilObjUserFolder::ORG_OP_EDIT_USER_ACCOUNTS,
            USER_FOLDER_ID,
            $a_user_ids
        );
    }

    /**
     * Handles multi command from repository search gui
     */
    public function searchResultHandler(
        array $a_usr_ids,
        string $a_cmd
    ): bool {
        if (!count($a_usr_ids)) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt('select_one'));
            return false;
        }

        $this->requested_ids = $a_usr_ids;

        // no real confirmation here
        if (stripos($a_cmd, 'export') !== false) {
            $cmd = $a_cmd . 'Object';
            return $this->$cmd();
        }

        return $this->showActionConfirmation(
            $a_cmd,
            true
        );
    }

    public function getUserMultiCommands(bool $a_search_form = false): array // Missing array type.
    {
        $cmds = [];
        // see searchResultHandler()
        if ($a_search_form) {
            if ($this->rbac_system->checkAccess('write', $this->object->getRefId())) {
                $cmds = [
                    'activate' => $this->lng->txt('activate'),
                    'deactivate' => $this->lng->txt('deactivate'),
                    'accessRestrict' => $this->lng->txt('accessRestrict'),
                    'accessFree' => $this->lng->txt('accessFree')
                ];
            }

            if ($this->rbac_system->checkAccess('delete', $this->object->getRefId())) {
                $cmds['delete'] = $this->lng->txt('delete');
            }
        } else {
            if ($this->rbac_system->checkAccess('write', $this->object->getRefId())) {
                $cmds = [
                    'activateUsers' => $this->lng->txt('activate'),
                    'deactivateUsers' => $this->lng->txt('deactivate'),
                    'restrictAccess' => $this->lng->txt('accessRestrict'),
                    'freeAccess' => $this->lng->txt('accessFree')
                ];
            }

            if ($this->rbac_system->checkAccess('delete', $this->object->getRefId())) {
                $cmds['deleteUsers'] = $this->lng->txt('delete');
            }
        }

        if ($this->rbac_system->checkAccess('write', $this->object->getRefId())) {
            $export_types = [
                'userfolder_export_excel_x86',
                'userfolder_export_xml'
            ];
            foreach ($export_types as $type) {
                $cmd = explode(
                    '_',
                    $type
                );
                $cmd = array_pop($cmd);
                $cmds['usrExport' . ucfirst($cmd)] = $this->lng->txt('export') . ' - ' .
                    $this->lng->txt($type);
            }
        }

        // check if current user may send mails
        $mail = new ilMail($this->user->getId());
        if ($this->rbac_system->checkAccess(
            'internal_mail',
            $mail->getMailObjectReferenceId()
        )) {
            $cmds['mail'] = $this->lng->txt('send_mail');
        }

        $cmds['addToClipboard'] = $this->lng->txt('clipboard_add_btn');

        return $cmds;
    }

    protected function usrExportX86Object(): void
    {
        $user_ids = $this->getActionUserIds();
        if (!$user_ids) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt('select_one'), true);
            $this->ctrl->redirectByClass(
                self::class,
                'view'
            );
        }

        if ($this->checkPermissionBool(\ilObjUserFolder::PERM_READ_ALL_AND_WRITE)) {
            $this->object->buildExportFile(
                ilObjUserFolder::FILE_TYPE_EXCEL,
                $user_ids
            );
            $this->ctrl->redirectByClass(
                [self::class, ilExportGUI::class],
                'export'
            );
        } elseif ($this->checkUserManipulationAccessBool()) {
            $fullname = $this->object->buildExportFile(
                ilObjUserFolder::FILE_TYPE_EXCEL,
                $user_ids,
                true
            );
            ilFileDelivery::deliverFileLegacy(
                $fullname . '.xlsx',
                $this->object->getExportFilename(ilObjUserFolder::FILE_TYPE_EXCEL) . '.xlsx',
                '',
                false,
                true
            );
        }
    }

    protected function usrExportCsvObject(): void
    {
        $user_ids = $this->getActionUserIds();
        if (!$user_ids) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt('select_one'), true);
            $this->ctrl->redirect(
                $this,
                'view'
            );
        }

        if ($this->checkPermissionBool(\ilObjUserFolder::PERM_READ_ALL_AND_WRITE)) {
            $this->object->buildExportFile(
                ilObjUserFolder::FILE_TYPE_CSV,
                $user_ids
            );
            $this->ctrl->redirectByClass(
                'ilobjuserfoldergui',
                'export'
            );
        } elseif ($this->checkUserManipulationAccessBool()) {
            $fullname = $this->object->buildExportFile(
                ilObjUserFolder::FILE_TYPE_CSV,
                $user_ids,
                true
            );
            ilFileDelivery::deliverFileLegacy(
                $fullname,
                $this->object->getExportFilename(ilObjUserFolder::FILE_TYPE_CSV),
                '',
                false,
                true
            );
        }
    }

    protected function usrExportXmlObject(): void
    {
        $user_ids = $this->getActionUserIds();
        if (!$user_ids) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt('select_one'), true);
            $this->ctrl->redirectByClass(
                self::class,
                'view'
            );
        }
        if ($this->checkPermissionBool(\ilObjUserFolder::PERM_READ_ALL_AND_WRITE)) {
            $this->object->buildExportFile(
                ilObjUserFolder::FILE_TYPE_XML,
                $user_ids
            );
            $this->ctrl->redirectByClass(
                [self::class, ilExportGUI::class],
                'export'
            );
        } elseif ($this->checkUserManipulationAccessBool()) {
            $fullname = $this->object->buildExportFile(
                ilObjUserFolder::FILE_TYPE_XML,
                $user_ids,
                true
            );
            ilFileDelivery::deliverFileLegacy(
                $fullname,
                $this->object->getExportFilename(ilObjUserFolder::FILE_TYPE_XML),
                '',
                false,
                true
            );
        }
    }

    protected function mailObject(): void
    {
        $user_ids = $this->getActionUserIds();
        if (!$user_ids) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt('select_one'), true);
            $this->ctrl->redirect(
                $this,
                'view'
            );
            return;
        }

        // remove existing (temporary) lists
        $old_lists = new ilMailingLists($this->user);
        $old_lists->deleteTemporaryLists();

        // create (temporary) mailing list
        $list = new ilMailingList($this->user);
        $list->setMode(ilMailingList::MODE_TEMPORARY);
        $list->setTitle('-TEMPORARY SYSTEM LIST-');
        $list->setDescription('-USER ACCOUNTS MAIL-');
        $list->setCreatedate(date('Y-m-d H:i:s'));
        $list->insert();
        $list_id = $list->getId();

        // after list has been saved...
        foreach ($user_ids as $user_id) {
            $list->assignUser((int) $user_id);
        }

        $umail = new ilFormatMail($this->user->getId());
        $mail_data = $umail->retrieveFromStage();

        $umail->persistToStage(
            $mail_data['user_id'],
            '#il_ml_' . $list_id,
            $mail_data['rcp_cc'],
            $mail_data['rcp_bcc'],
            $mail_data['m_subject'],
            $mail_data['m_message'],
            $mail_data['attachments'],
            $mail_data['use_placeholders'],
            $mail_data['tpl_ctx_id'],
            $mail_data['tpl_ctx_params']
        );

        $this->ctrl->redirectToURL(
            ilMailFormCall::getRedirectTarget(
                $this,
                '',
                [],
                ['type' => 'search_res']
            )
        );
    }

    public function addToExternalSettingsForm(int $a_form_id): array // Missing array type.
    {
        switch ($a_form_id) {
            case ilAdministrationSettingsFormHandler::FORM_SECURITY:
                $security = ilSecuritySettings::_getInstance();

                $fields = [];

                $subitems = [
                    'ps_password_change_on_first_login_enabled' => [
                        $security->isPasswordChangeOnFirstLoginEnabled(),
                        ilAdministrationSettingsFormHandler::VALUE_BOOL
                    ],
                    'ps_password_must_not_contain_loginame' => [
                        $security->getPasswordMustNotContainLoginnameStatus(),
                        ilAdministrationSettingsFormHandler::VALUE_BOOL
                    ],
                    'ps_password_chars_and_numbers_enabled' => [
                        $security->isPasswordCharsAndNumbersEnabled(),
                        ilAdministrationSettingsFormHandler::VALUE_BOOL
                    ],
                    'ps_password_special_chars_enabled' => [
                        $security->isPasswordSpecialCharsEnabled(),
                        ilAdministrationSettingsFormHandler::VALUE_BOOL
                    ],
                    'ps_password_min_length' => $security->getPasswordMinLength(),
                    'ps_password_max_length' => $security->getPasswordMaxLength(),
                    'ps_password_uppercase_chars_num' => $security->getPasswordNumberOfUppercaseChars(),
                    'ps_password_lowercase_chars_num' => $security->getPasswordNumberOfLowercaseChars(),
                    'ps_password_max_age' => $security->getPasswordMaxAge()
                ];
                $fields['ps_password_settings'] = [null, null, $subitems];

                $subitems = [
                    'ps_login_max_attempts' => $security->getLoginMaxAttempts(),
                    'ps_prevent_simultaneous_logins' => [
                        $security->isPreventionOfSimultaneousLoginsEnabled(),
                        ilAdministrationSettingsFormHandler::VALUE_BOOL
                    ]
                ];
                $fields['ps_security_protection'] = [null, null, $subitems];

                return [['generalSettings', $fields]];
        }
        return [];
    }

    private function redirectAfterImport(): void
    {
        if ($this->inAdministration()) {
            $this->ctrl->redirect(
                $this,
                'view'
            );
        }

        $this->ctrl->redirectByClass(
            'ilobjcategorygui',
            'listUsers'
        );
    }

    protected function addToClipboardObject(): void
    {
        $users = $this->getActionUserIds();
        if (!count($users)) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt('select_one'), true);
            $this->ctrl->redirect(
                $this,
                'view'
            );
        }
        $clip = ilUserClipboard::getInstance($GLOBALS['DIC']['ilUser']->getId());
        $clip->add($users);
        $clip->save();

        $this->tpl->setOnScreenMessage('success', $this->lng->txt('clipboard_user_added'), true);
        $this->ctrl->redirect(
            $this,
            'view'
        );
    }

    private function retrieveUserList(): ?array
    {
        if ($this->access->checkAccess(\ilObjUserFolder::PERM_READ_ALL, '', USER_FOLDER_ID)) {
            return null;
        }

        if ($this->access->checkPositionAccess(
            \ilObjUserFolder::ORG_OP_EDIT_USER_ACCOUNTS,
            USER_FOLDER_ID
        )) {
            return $this->access->filterUserIdsByPositionOfCurrentUser(
                \ilObjUserFolder::ORG_OP_EDIT_USER_ACCOUNTS,
                USER_FOLDER_ID,
                \ilLocalUser::_getAllUserIds(\ilLocalUser::_getUserFolderId())
            );
        }

        return [];
    }

    private function checkbox(string $name): ilCheckboxInputGUI
    {
        $checkbox = new ilCheckboxInputGUI($this->lng->txt($name), $name);
        $checkbox->setInfo($this->lng->txt($name . '_desc'));
        $checkbox->setValue('1');

        return $checkbox;
    }

    private function raiseErrorOnMissingWrite(): void
    {
        if (!$this->access->checkRbacOrPositionPermissionAccess(
            'write',
            \ilObjUserFolder::ORG_OP_EDIT_USER_ACCOUNTS,
            USER_FOLDER_ID
        )) {
            $this->ilias->raiseError(
                $this->lng->txt('permission_denied'),
                $this->ilias->error_obj->MESSAGE
            );
        }
    }
}
