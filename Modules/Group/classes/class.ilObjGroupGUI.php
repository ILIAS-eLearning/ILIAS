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
 *********************************************************************/

use ILIAS\HTTP\GlobalHttpState;
use ILIAS\Refinery\Factory;

/**
 * Class ilObjGroupGUI
 *
 * @author    Stefan Meyer <smeyer.ilias@gmx.de>
 * @author    Sascha Hofmann <saschahofmann@gmx.de>
 *
 * @ilCtrl_Calls ilObjGroupGUI: ilGroupRegistrationGUI, ilPermissionGUI, ilInfoScreenGUI, ilLearningProgressGUI
 * @ilCtrl_Calls ilObjGroupGUI: ilPublicUserProfileGUI, ilObjCourseGroupingGUI, ilObjectContentStyleSettingsGUI
 * @ilCtrl_Calls ilObjGroupGUI: ilCourseContentGUI, ilColumnGUI, ilContainerPageGUI, ilObjectCopyGUI
 * @ilCtrl_Calls ilObjGroupGUI: ilObjectCustomUserFieldsGUI, ilMemberAgreementGUI, ilExportGUI, ilMemberExportGUI
 * @ilCtrl_Calls ilObjGroupGUI: ilCommonActionDispatcherGUI, ilObjectServiceSettingsGUI, ilSessionOverviewGUI
 * @ilCtrl_Calls ilObjGroupGUI: ilGroupMembershipGUI, ilBadgeManagementGUI, ilMailMemberSearchGUI, ilNewsTimelineGUI, ilContainerNewsSettingsGUI
 * @ilCtrl_Calls ilObjGroupGUI: ilContainerSkillGUI, ilCalendarPresentationGUI
 * @ilCtrl_Calls ilObjGroupGUI: ilLTIProviderObjectSettingGUI
 * @ilCtrl_Calls ilObjGroupGUI: ilObjectMetaDataGUI, ilObjectTranslationGUI, ilPropertyFormGUI
 *
 *
 *
 * @extends ilObjectGUI
 */
class ilObjGroupGUI extends ilContainerGUI
{
    protected bool $show_tracking = false;

    private GlobalHttpState $http;
    protected Factory $refinery;
    protected ilRbacSystem $rbacsystem;

    /**
     * @inheritDoc
    */
    public function __construct($a_data, int $a_id, bool $a_call_by_reference, bool $a_prepare_output = false)
    {
        global $DIC;

        $this->type = "grp";
        parent::__construct($a_data, $a_id, $a_call_by_reference, $a_prepare_output);

        $this->lng->loadLanguageModule('grp');
        $this->lng->loadLanguageModule('obj');
        $this->http = $DIC->http();
        $this->refinery = $DIC->refinery();
        $this->rbacsystem = $DIC->rbac()->system();
    }

    protected function initRefIdFromQuery(): int
    {
        if ($this->http->wrapper()->query()->has('ref_id')) {
            return $this->http->wrapper()->query()->retrieve(
                'ref_id',
                $this->refinery->kindlyTo()->int()
            );
        }
        return 0;
    }

    /**
     * @inheritDoc
     */
    public function executeCommand(): void
    {
        global $DIC;

        $ilNavigationHistory = $DIC['ilNavigationHistory'];

        $ref_id = $this->initRefIdFromQuery();

        $next_class = $this->ctrl->getNextClass($this);
        $cmd = $this->ctrl->getCmd();
        $this->prepareOutput();

        // add entry to navigation history
        if (!$this->getCreationMode() && $this->access->checkAccess("read", "", $ref_id)) {
            $ilNavigationHistory->addItem(
                $ref_id,
                ilLink::_getLink($ref_id, "grp"),
                "grp"
            );
        }

        // if news timeline is landing page, redirect if necessary
        if ($next_class == "" && $cmd == "" && $this->object->isNewsTimelineLandingPageEffective()
            && $this->access->checkAccess("read", "", $ref_id)) {
            $this->ctrl->redirectByClass("ilnewstimelinegui");
        }

        $header_action = true;
        switch ($next_class) {
            case strtolower(ilRepositoryTrashGUI::class):
                $ru = new \ilRepositoryTrashGUI($this);
                $this->ctrl->setReturn($this, 'trash');
                $this->ctrl->forwardCommand($ru);
                break;

            case 'illtiproviderobjectsettinggui':
                $this->setSubTabs('settings');
                $this->tabs_gui->activateTab('settings');
                $this->tabs_gui->activateSubTab('lti_provider');
                $lti_gui = new ilLTIProviderObjectSettingGUI($this->object->getRefId());
                $lti_gui->setCustomRolesForSelection($GLOBALS['DIC']->rbac()->review()->getLocalRoles($this->object->getRefId()));
                $lti_gui->offerLTIRolesForSelection(false);
                $this->ctrl->forwardCommand($lti_gui);
                break;


            case 'ilgroupmembershipgui':

                $this->tabs_gui->activateTab('members');

                $mem_gui = new ilGroupMembershipGUI($this, $this->object);
                $this->ctrl->forwardCommand($mem_gui);
                break;


            case 'ilgroupregistrationgui':
                $this->ctrl->setReturn($this, '');
                $this->tabs_gui->setTabActive('join');
                $registration = new ilGroupRegistrationGUI($this->object);
                $this->ctrl->forwardCommand($registration);
                break;

            case 'ilpermissiongui':
                $this->tabs_gui->activateTab('perm_settings');
                $perm_gui = new ilPermissionGUI($this);
                $this->ctrl->forwardCommand($perm_gui);
                break;

            case "ilinfoscreengui":
                $this->infoScreen();
                break;

            case "illearningprogressgui":
                $user_id = $this->user->getId();
                if ($this->http->wrapper()->query()->has('user_id')) {
                    $user_id = $this->http->wrapper()->query()->retrieve(
                        'user_id',
                        $this->refinery->kindlyTo()->int()
                    );
                }
                $new_gui = new ilLearningProgressGUI(
                    ilLearningProgressGUI::LP_CONTEXT_REPOSITORY,
                    $this->object->getRefId(),
                    $user_id
                );
                $this->ctrl->forwardCommand($new_gui);
                $this->tabs_gui->setTabActive('learning_progress');
                break;

            case 'ilobjcoursegroupinggui':
                $this->setSubTabs('settings');
                $this->ctrl->setReturn($this, 'edit');
                $obj_id = 0;
                if ($this->http->wrapper()->query()->has('obj_id')) {
                    $obj_id = $this->http->wrapper()->query()->retrieve(
                        'obj_id',
                        $this->refinery->kindlyTo()->int()
                    );
                }
                $crs_grp_gui = new ilObjCourseGroupingGUI($this->object, $obj_id);
                $this->ctrl->forwardCommand($crs_grp_gui);
                $this->tabs_gui->setTabActive('settings');
                $this->tabs_gui->setSubTabActive('groupings');
                break;

            case 'ilcoursecontentgui':
                $course_content_obj = new ilCourseContentGUI($this);
                $this->ctrl->forwardCommand($course_content_obj);
                break;

            case 'ilpublicuserprofilegui':
                $this->setSubTabs('members');
                $this->tabs_gui->setTabActive('group_members');
                $this->tabs_gui->setSubTabActive('grp_members_gallery');
                $usr_id = 0;
                if ($this->http->wrapper()->query()->has('user')) {
                    $usr_id = $this->http->wrapper()->query()->retrieve(
                        'user',
                        $this->refinery->kindlyTo()->int()
                    );
                }
                $profile_gui = new ilPublicUserProfileGUI($usr_id);
                $back_url = '';
                if ($this->http->wrapper()->query()->has('back_url')) {
                    $back_url = $this->http->wrapper()->query()->retrieve(
                        'back_url',
                        $this->refinery->kindlyTo()->string()
                    );
                }
                if ($back_url == '') {
                    $profile_gui->setBackUrl($this->ctrl->getLinkTargetByClass(["ilGroupMembershipGUI", "ilUsersGalleryGUI"], 'view'));
                }
                $html = $this->ctrl->forwardCommand($profile_gui);
                $this->tpl->setVariable("ADM_CONTENT", $html);
                break;

            case "ilcolumngui":
                $this->tabs_gui->setTabActive('none');
                $this->checkPermission("read");
                $this->tpl->setVariable(
                    "LOCATION_CONTENT_STYLESHEET",
                    ilObjStyleSheet::getContentStylePath($this->object->getStyleSheetId())
                );
                $this->renderObject();
                break;

            // container page editing
            case "ilcontainerpagegui":
                $ret = $this->forwardToPageObject();
                if ($ret != "") {
                    $this->tpl->setContent($ret);
                }
                $header_action = false;
                break;

            case 'ilobjectcopygui':
                $cp = new ilObjectCopyGUI($this);
                $cp->setType('grp');
                $this->ctrl->forwardCommand($cp);
                break;

            case "ilobjectcontentstylesettingsgui":
                global $DIC;

                $this->checkPermission("write");
                $this->setTitleAndDescription();
                $settings_gui = $DIC->contentStyle()->gui()
                    ->objectSettingsGUIForRefId(
                        null,
                        $this->object->getRefId()
                    );
                $this->ctrl->forwardCommand($settings_gui);
                break;

            case 'ilobjectcustomuserfieldsgui':
                $cdf_gui = new ilObjectCustomUserFieldsGUI($this->object->getId());
                $this->setSubTabs('settings');
                $this->tabs_gui->setTabActive('settings');
                $this->tabs_gui->activateSubTab('grp_custom_user_fields');
                $this->ctrl->forwardCommand($cdf_gui);
                break;

            case 'ilmemberagreementgui':
                $this->ctrl->setReturn($this, '');
                $this->tabs_gui->setTabActive('view_content');
                $agreement = new ilMemberAgreementGUI($this->object->getRefId());
                $this->ctrl->forwardCommand($agreement);
                break;

            case 'ilexportgui':
                $this->tabs_gui->setTabActive('export');
                $exp = new ilExportGUI($this);
                $exp->addFormat('xml');
                $this->ctrl->forwardCommand($exp);
                break;

            case "ilcommonactiondispatchergui":
                $gui = ilCommonActionDispatcherGUI::getInstanceFromAjaxCall();
                $this->ctrl->forwardCommand($gui);
                break;

            case 'ilobjectservicesettingsgui':
                $this->ctrl->setReturn($this, 'edit');
                $this->setSubTabs("settings");
                $this->tabs_gui->activateTab('settings');
                $this->tabs_gui->activateSubTab('tool_settings');

                $service = new ilObjectServiceSettingsGUI(
                    $this,
                    $this->object->getId(),
                    array(
                            ilObjectServiceSettingsGUI::CALENDAR_CONFIGURATION
                        )
                );
                $this->ctrl->forwardCommand($service);
                break;

            case 'ilmailmembersearchgui':
                $mail = new ilMail($this->user->getId());

                if (!($this->access->checkAccess('manage_members', '', $this->object->getRefId()) ||
                    $this->object->getMailToMembersType() == ilObjGroup::MAIL_ALLOWED_ALL) &&
                    $this->rbacsystem->checkAccess('internal_mail', $mail->getMailObjectReferenceId())) {
                    $this->error->raiseError($this->lng->txt("msg_no_perm_read"), $this->error->MESSAGE);
                }

                $this->tabs_gui->setTabActive('members');


                $mail_search = new ilMailMemberSearchGUI($this, $this->object->getRefId(), new ilMailMemberGroupRoles());
                $mail_search->setObjParticipants(ilCourseParticipants::_getInstanceByObjId($this->object->getId()));
                $this->ctrl->forwardCommand($mail_search);
                break;

            case 'ilbadgemanagementgui':
                $this->tabs_gui->setTabActive('obj_tool_setting_badges');
                $bgui = new ilBadgeManagementGUI($this->object->getRefId(), $this->object->getId(), 'grp');
                $this->ctrl->forwardCommand($bgui);
                break;

            case "ilcontainernewssettingsgui":
                $this->setSubTabs("settings");
                $this->tabs_gui->setTabActive('settings');
                $this->tabs_gui->activateSubTab('obj_news_settings');
                $news_set_gui = new ilContainerNewsSettingsGUI($this);
                $news_set_gui->setTimeline(true);
                $news_set_gui->setCronNotifications(true);
                $news_set_gui->setHideByDate(true);
                $this->ctrl->forwardCommand($news_set_gui);
                break;

            case "ilnewstimelinegui":
                $this->checkPermission("read");
                $this->tabs_gui->setTabActive('news_timeline');
                $t = ilNewsTimelineGUI::getInstance($this->object->getRefId(), $this->object->getNewsTimelineAutoENtries());
                $t->setUserEditAll($this->access->checkAccess('write', '', $this->object->getRefId(), 'grp'));
                $this->showPermanentLink();
                $this->ctrl->forwardCommand($t);
                ilLearningProgress::_tracProgress(
                    $this->user->getId(),
                    $this->object->getId(),
                    $this->object->getRefId(),
                    'grp'
                );
                break;

            case "ilcontainerskillgui":
                $this->tabs_gui->activateTab('obj_tool_setting_skills');
                $gui = new ilContainerSkillGUI($this);
                $this->ctrl->forwardCommand($gui);
                break;

            case 'ilcalendarpresentationgui':
                $cal = new ilCalendarPresentationGUI($this->object->getRefId());
                $this->ctrl->forwardCommand($cal);
                break;

            case 'ilobjectmetadatagui':
                if (!$this->access->checkAccess('write', '', $this->object->getRefId())) {
                    $this->error->raiseError($this->lng->txt('permission_denied'), $this->error->WARNING);
                }
                $this->tabs_gui->activateTab('meta_data');
                $this->ctrl->forwardCommand(new ilObjectMetaDataGUI($this->object));
                break;


            case 'ilobjecttranslationgui':
                $this->checkPermissionBool("write");
                $this->setSubTabs("settings");
                $this->tabs->activateTab("settings");
                $transgui = new ilObjectTranslationGUI($this);
                $this->ctrl->forwardCommand($transgui);
                break;

            default:

                // check visible permission
                if (!$this->getCreationMode() and
                        !$this->access->checkAccess('visible', '', $this->object->getRefId(), 'grp') and
                        !$this->access->checkAccess('read', '', $this->object->getRefId(), 'grp')) {
                    $this->error->raiseError($this->lng->txt("msg_no_perm_read"), $this->error->MESSAGE);
                }

                // #9401 - see also ilStartupGUI::_checkGoto()
                if ($cmd == 'infoScreenGoto') {
                    if ($this->object->isRegistrationEnabled()) {
                        $cmd = 'join';
                    } else {
                        $cmd = 'infoScreen';
                    }
                }

                // check read permission
                if ((!$this->getCreationMode()
                    && !$this->rbacsystem->checkAccess('read', $this->object->getRefId()) && $cmd != 'infoScreen')
                    || $cmd == 'join') {
                    // no join permission -> redirect to info screen
                    if (!$this->rbacsystem->checkAccess('join', $this->object->getRefId())) {
                        $this->ctrl->redirect($this, "infoScreen");
                    } else {	// no read -> show registration
                        $this->ctrl->redirectByClass("ilGroupRegistrationGUI", "show");
                    }
                }
                if (!$cmd) {
                    $cmd = 'view';
                }
                $cmd .= 'Object';
                $this->$cmd();
                break;
        }

        if ($header_action) {
            $this->addHeaderAction();
        }
    }

    /**
     * @inheritDoc
     */
    public function viewObject(): void
    {
        ilLearningProgress::_tracProgress(
            $this->user->getId(),
            $this->object->getId(),
            $this->object->getRefId(),
            'grp'
        );

        ilMDUtils::_fillHTMLMetaTags(
            $this->object->getId(),
            $this->object->getId(),
            'grp'
        );

        if ($this->getAdminMode() === self::ADMIN_MODE_SETTINGS) {
            parent::viewObject();
            return;
        }

        if (!$this->checkAgreement()) {
            $this->tabs_gui->setTabActive('view_content');
            $this->ctrl->setReturn($this, 'view');
            $agreement = new ilMemberAgreementGUI($this->object->getRefId());
            $this->ctrl->setCmdClass(get_class($agreement));
            $this->ctrl->forwardCommand($agreement);
            return;
        }

        $this->tabs_gui->setTabActive('view_content');
        $this->renderObject();
    }

    public function renderObject(): void
    {
        $this->tabs->activateTab("view_content");
        parent::renderObject();
    }

    /**
     * @inheritDoc
     */
    public function modifyItemGUI(ilObjectListGUI $a_item_list_gui, array $a_item_data): void
    {
        // if folder is in a course, modify item list gui according to course requirements
        if ($course_ref_id = $this->tree->checkForParentType($this->object->getRefId(), 'crs')) {
            $course_obj_id = ilObject::_lookupObjId($course_ref_id);
            ilObjCourseGUI::_modifyItemGUI(
                $a_item_list_gui,
                'ilcoursecontentgui',
                $a_item_data,
                ilObjCourse::_lookupAboStatus($course_obj_id),
                $course_ref_id,
                $course_obj_id,
                $this->object->getRefId()
            );
        }
    }

    /**
     * @inheritDoc
     * @access public
     * @see ilGroupAddToGroupActionGUI
     */
    public function afterSave(ilObject $new_object, bool $redirect = true): void
    {
        $new_object->setRegistrationType(
            ilGroupConstants::GRP_REGISTRATION_DIRECT
        );
        $new_object->update();

        // check for parent group or course => SORT_INHERIT
        $sort_mode = ilContainer::SORT_TITLE;
        if (
                $this->tree->checkForParentType($new_object->getRefId(), 'crs', true) ||
                $this->tree->checkForParentType($new_object->getRefId(), 'grp', true)
        ) {
            $sort_mode = ilContainer::SORT_INHERIT;
        }

        // Save sorting
        $sort = new ilContainerSortingSettings($new_object->getId());
        $sort->setSortMode($sort_mode);
        $sort->update();


        // Add user as admin and enable notification
        $members_obj = ilGroupParticipants::_getInstanceByObjId($new_object->getId());
        $members_obj->add($this->user->getId(), ilParticipants::IL_GRP_ADMIN);
        $members_obj->updateNotification($this->user->getId(), (bool) $this->settings->get('mail_grp_admin_notification', '1'));
        $members_obj->updateContact($this->user->getId(), true);

        $this->tpl->setOnScreenMessage('success', $this->lng->txt("object_added"), true);
        if ($redirect) {
            $this->ctrl->setParameter($this, "ref_id", $new_object->getRefId());
            $this->ctrl->redirect($this, 'edit');
        }
    }

    /**
     * @inheritDoc
     */
    public function editObject(?ilPropertyFormGUI $a_form = null): void
    {
        $this->checkPermission("write");

        $this->setSubTabs('settings');
        $this->tabs_gui->setTabActive('settings');
        $this->tabs_gui->setSubTabActive('grp_settings');

        if (!$a_form) {
            $a_form = $this->initForm('edit');
        }
        $this->tpl->setVariable('ADM_CONTENT', $a_form->getHTML());
    }

    public function updateGroupTypeObject(): void
    {
        ilDidacticTemplateUtils::switchTemplate(
            $this->object->getRefId(),
            (int) $_REQUEST['grp_type']
        );

        $this->tpl->setOnScreenMessage('success', $this->lng->txt('settings_saved'), true);
        $this->ctrl->redirect($this, 'edit');
    }


    public function updateObject(): void
    {
        $obj_service = $this->getObjectService();
        $this->checkPermission('write');

        $form = $this->initForm();
        $new_type = 0;
        if ($form->checkInput()) {
            // handle group type settings
            $old_type = ilDidacticTemplateObjSettings::lookupTemplateId($this->object->getRefId());

            $modified = false;
            $new_type_info = $form->getInput('didactic_type');
            if ($new_type_info) {
                $new_type = explode('_', $form->getInput('didactic_type'));
                $new_type = (int) $new_type[1];

                $modified = ($new_type !== $old_type);
                ilLoggerFactory::getLogger('grp')->info('Switched group type from ' . $old_type . ' to ' . $new_type);
            }

            // Additional checks: both tile and session limitation activated (not supported)
            if (
                $form->getInput('sl') == "1" &&
                $form->getInput('list_presentation') == "tile") {
                $form->setValuesByPost();
                $this->tpl->setOnScreenMessage('failure', $this->lng->txt('crs_tile_and_session_limit_not_supported'));
                $this->editObject($form);
                return;
            }

            $old_autofill = $this->object->hasWaitingListAutoFill();

            $this->object->setTitle($form->getInput('title'));
            $this->object->setDescription($form->getInput('desc'));
            $this->object->setGroupType((int) $form->getInput('grp_type'));
            $this->object->setRegistrationType((int) $form->getInput('registration_type'));
            $this->object->setPassword($form->getInput('password'));
            $this->object->enableUnlimitedRegistration(!$form->getInput('reg_limit_time'));
            $this->object->enableMembershipLimitation((bool) $form->getInput('registration_membership_limited'));
            $this->object->setMinMembers((int) $form->getInput('registration_min_members'));
            $this->object->setMaxMembers((int) $form->getInput('registration_max_members'));
            $this->object->enableRegistrationAccessCode((bool) $form->getInput('reg_code_enabled'));
            $this->object->setRegistrationAccessCode($form->getInput('reg_code'));
            $this->object->setViewMode((int) $form->getInput('view_mode'));
            $this->object->setMailToMembersType((int) $form->getInput('mail_type'));
            $this->object->setShowMembers((bool) $form->getInput('show_members'));
            $this->object->setAutoNotification((bool) $form->getInput('auto_notification'));

            // session limit
            $this->object->enableSessionLimit((bool) $form->getInput('sl'));
            $session_sp = $form->getInput('sp');
            $this->object->setNumberOfPreviousSessions(is_numeric($session_sp) ? (int) $session_sp : -1);
            $session_sn = $form->getInput('sn');
            $this->object->setNumberOfnextSessions(is_numeric($session_sn) ? (int) $session_sn : -1);

            // period
            $grp_period = $form->getItemByPostVar("period");
            $this->object->setPeriod(
                $grp_period->getStart(),
                $grp_period->getEnd()
            );

            $reg = $form->getItemByPostVar("reg");
            if ($reg->getStart() instanceof ilDateTime && $reg->getEnd() instanceof ilDateTime) {
                $this->object->enableUnlimitedRegistration(false);
            } else {
                $this->object->enableUnlimitedRegistration(true);
            }

            $this->object->setRegistrationStart($reg->getStart());
            $this->object->setRegistrationEnd($reg->getEnd());

            $cancel_end = $form->getItemByPostVar("cancel_end");
            $this->object->setCancellationEnd($cancel_end->getDate());

            $waiting_list = 0;
            if ($this->http->wrapper()->post()->has('waiting_list')) {
                $waiting_list = $this->http->wrapper()->post()->retrieve(
                    'waiting_list',
                    $this->refinery->kindlyTo()->int()
                );
            }
            switch ($waiting_list) {
                case 2:
                    $this->object->enableWaitingList(true);
                    $this->object->setWaitingListAutoFill(true);
                    break;

                case 1:
                    $this->object->enableWaitingList(true);
                    $this->object->setWaitingListAutoFill(false);
                    break;

                default:
                    $this->object->enableWaitingList(false);
                    $this->object->setWaitingListAutoFill(false);
                    break;
            }

            // title icon visibility
            $obj_service->commonSettings()->legacyForm($form, $this->object)->saveTitleIconVisibility();

            // top actions visibility
            $obj_service->commonSettings()->legacyForm($form, $this->object)->saveTopActionsVisibility();

            // custom icon
            $obj_service->commonSettings()->legacyForm($form, $this->object)->saveIcon();

            // tile image
            $obj_service->commonSettings()->legacyForm($form, $this->object)->saveTileImage();

            // list presentation
            $this->saveListPresentation($form);

            // update object settings
            $this->object->update();


            ilObjectServiceSettingsGUI::updateServiceSettingsForm(
                $this->object->getId(),
                $form,
                array(
                    ilObjectServiceSettingsGUI::CALENDAR_CONFIGURATION,
                    ilObjectServiceSettingsGUI::USE_NEWS,
                    ilObjectServiceSettingsGUI::CUSTOM_METADATA,
                    ilObjectServiceSettingsGUI::AUTO_RATING_NEW_OBJECTS,
                    ilObjectServiceSettingsGUI::TAG_CLOUD,
                    ilObjectServiceSettingsGUI::BADGES,
                    ilObjectServiceSettingsGUI::SKILLS,
                    ilObjectServiceSettingsGUI::ORGU_POSITION_ACCESS,
                    ilObjectServiceSettingsGUI::EXTERNAL_MAIL_PREFIX
                )
            );

            // Save sorting
            $this->saveSortingSettings($form);
            // if autofill has been activated trigger process
            if (
                !$old_autofill &&
                $this->object->hasWaitingListAutoFill()) {
                $this->object->handleAutoFill();
            }

            // BEGIN ChangeEvents: Record update Object.
            ilChangeEvent::_recordWriteEvent(
                $this->object->getId(),
                $this->user->getId(),
                'update'
            );
            ilChangeEvent::_catchupWriteEvents($this->object->getId(), $this->user->getId());
            // END PATCH ChangeEvents: Record update Object.
            // Update ecs export settings
            $ecs = new ilECSGroupSettings($this->object);
            $ecs->handleSettingsUpdate();
        } else {
            $this->tpl->setOnScreenMessage('failure', $GLOBALS['DIC']->language()->txt('err_check_input')); // #16975

            $form->setValuesByPost();
            $this->editObject($form);
            return;
        }

        // group type modified
        if ($modified) {
            if ($new_type == 0) {
                $new_type_txt = $GLOBALS['DIC']['lng']->txt('il_grp_status_open');
            } else {
                $dtpl = new ilDidacticTemplateSetting($new_type);
                $new_type_txt = $dtpl->getPresentationTitle($GLOBALS['DIC']['lng']->getLangKey());
            }


            $confirm = new ilConfirmationGUI();
            $confirm->setHeaderText($this->lng->txt('grp_warn_grp_type_changed'));
            $confirm->setFormAction($this->ctrl->getFormAction($this));
            $confirm->addItem(
                'grp_type',
                (string) $new_type,
                $this->lng->txt('grp_info_new_grp_type') . ': ' . $new_type_txt
            );
            $confirm->setConfirm($this->lng->txt('grp_change_type'), 'updateGroupType');
            $confirm->setCancel($this->lng->txt('cancel'), 'edit');

            $this->tpl->setContent($confirm->getHTML());
        } else {
            $this->tpl->setOnScreenMessage('success', $this->lng->txt("msg_obj_modified"), true);
            $this->ctrl->redirect($this, 'edit');
        }
    }

    /**
    * Edit Map Settings
    */
    public function editMapSettingsObject(): void
    {
        $this->setSubTabs("settings");
        $this->tabs_gui->setTabActive('settings');
        $this->tabs_gui->setSubTabActive('grp_map_settings');

        if (!ilMapUtil::isActivated() ||
            !$this->access->checkAccess("write", "", $this->object->getRefId())) {
            return;
        }

        $latitude = $this->object->getLatitude();
        $longitude = $this->object->getLongitude();
        $zoom = $this->object->getLocationZoom();

        // Get Default settings, when nothing is set
        if ($latitude == 0 && $longitude == 0 && $zoom == 0) {
            $def = ilMapUtil::getDefaultSettings();
            $latitude = $def["latitude"];
            $longitude = $def["longitude"];
            $zoom = $def["zoom"];
        }


        $form = new ilPropertyFormGUI();
        $form->setFormAction($this->ctrl->getFormAction($this));

        $form->setTitle($this->lng->txt("grp_map_settings"));

        // enable map
        $public = new ilCheckboxInputGUI(
            $this->lng->txt("grp_enable_map"),
            "enable_map"
        );
        $public->setValue("1");
        $public->setChecked($this->object->getEnableGroupMap());
        $form->addItem($public);

        // map location
        $loc_prop = new ilLocationInputGUI(
            $this->lng->txt("grp_map_location"),
            "location"
        );
        $loc_prop->setLatitude((float) $latitude);
        $loc_prop->setLongitude((float) $longitude);
        $loc_prop->setZoom((int) $zoom);
        $form->addItem($loc_prop);

        $form->addCommandButton("saveMapSettings", $this->lng->txt("save"));

        $this->tpl->setVariable("ADM_CONTENT", $form->getHTML());
    }

    /**
     * @todo use $form->getInput()
     */
    public function saveMapSettingsObject(): void
    {
        $location = [];
        if ($this->http->wrapper()->post()->has('location')) {
            $custom_transformer = $this->refinery->custom()->transformation(
                function ($array) {
                    return $array;
                }
            );
            $location = $this->http->wrapper()->post()->retrieve(
                'location',
                $custom_transformer
            );
        }
        $enable_map = false;
        if ($this->http->wrapper()->post()->has('enable_map')) {
            $enable_map = $this->http->wrapper()->post()->retrieve(
                'enable_map',
                $this->refinery->kindlyTo()->bool()
            );
        }

        $this->object->setLatitude((string) $location['latitude']);
        $this->object->setLongitude((string) $location['longitude']);
        $this->object->setLocationZoom((int) $location['zoom']);
        $this->object->setEnableGroupMap($enable_map);
        $this->object->update();
        $this->ctrl->redirect($this, "editMapSettings");
    }



    public function editInfoObject(): void
    {
        $this->checkPermission('write');

        $this->setSubTabs('settings');
        $this->tabs_gui->setTabActive('settings');
        $this->tabs_gui->setSubTabActive('grp_info_settings');

        $form = $this->initInfoEditor();
        $this->tpl->setContent($form->getHTML());
    }

    protected function initInfoEditor(): ilPropertyFormGUI
    {
        $form = new ilPropertyFormGUI();
        $form->setFormAction($this->ctrl->getFormAction($this, 'updateInfo'));
        $form->setTitle($this->lng->txt('grp_general_informations'));
        $form->addCommandButton('updateInfo', $this->lng->txt('save'));
        $form->addCommandButton('cancel', $this->lng->txt('cancel'));

        $area = new ilTextAreaInputGUI($this->lng->txt('grp_information'), 'important');
        $area->setInfo($this->lng->txt('grp_information_info'));
        $area->setValue($this->object->getInformation());
        $area->setRows(8);
        $area->setCols(80);
        $form->addItem($area);

        return $form;
    }

    public function updateInfoObject(): void
    {
        $this->checkPermission('manage_members');

        $important = '';
        if ($this->http->wrapper()->post()->has('important')) {
            $important = $this->http->wrapper()->post()->retrieve(
                'important',
                $this->refinery->kindlyTo()->string()
            );
        }
        $this->object->setInformation($important);
        $this->object->update();
        $this->tpl->setOnScreenMessage('success', $this->lng->txt("settings_saved"));
        $this->editInfoObject();
    }

    public function readMemberData(array $ids, array $selected_columns = null): array
    {
        $privacy = ilPrivacySettings::getInstance();

        $this->show_tracking =
            (
                ilObjUserTracking::_enabledLearningProgress() && ilObjUserTracking::_enabledUserRelatedData()
            );

        $completed = $in_progress = $failed = [];
        if ($this->show_tracking) {
            $olp = ilObjectLP::getInstance($this->object->getId());
            $this->show_tracking = $olp->isActive();
        }

        if ($this->show_tracking) {
            $completed = ilLPStatusWrapper::_lookupCompletedForObject($this->object->getId());
            $in_progress = ilLPStatusWrapper::_lookupInProgressForObject($this->object->getId());
            $failed = ilLPStatusWrapper::_lookupFailedForObject($this->object->getId());
        }

        if ($privacy->enabledGroupAccessTimes()) {
            $progress = ilLearningProgress::_lookupProgressByObjId($this->object->getId());
        }

        $do_prtf = (is_array($selected_columns) &&
            in_array('prtf', $selected_columns) &&
            is_array($ids));
        if ($do_prtf) {
            $all_prtf = ilObjPortfolio::getAvailablePortfolioLinksForUserIds(
                $ids,
                $this->ctrl->getLinkTarget($this, "members")
            );
        }

        $profile_data = ilObjUser::_readUsersProfileData($ids);
        $members = [];
        foreach ($ids as $usr_id) {
            $name = ilObjUser::_lookupName((int) $usr_id);
            $tmp_data['firstname'] = (string) ($name['firstname'] ?? '');
            $tmp_data['lastname'] = (string) ($name['lastname'] ?? '');
            $tmp_data['notification'] = (bool) $this->object->members_obj->isNotificationEnabled((int) $usr_id) ? 1 : 0;
            $tmp_data['contact'] = (bool) $this->object->members_obj->isContact((int) $usr_id) ? 1 : 0;
            $tmp_data['usr_id'] = (int) $usr_id;
            $tmp_data['login'] = ilObjUser::_lookupLogin((int) $usr_id);

            foreach ((array) ($profile_data[$usr_id] ?? []) as $field => $value) {
                $tmp_data[$field] = $value;
            }

            if ($this->show_tracking) {
                $tmp_data['progress'] = '';
                if (in_array($usr_id, $completed)) {
                    $tmp_data['progress'] = ilLPStatus::LP_STATUS_COMPLETED;
                } elseif (in_array($usr_id, $in_progress)) {
                    $tmp_data['progress'] = ilLPStatus::LP_STATUS_IN_PROGRESS;
                } elseif (in_array($usr_id, $failed)) {
                    $tmp_data['progress'] = ilLPStatus::LP_STATUS_FAILED;
                } else {
                    $tmp_data['progress'] = ilLPStatus::LP_STATUS_NOT_ATTEMPTED;
                }
            }

            if ($privacy->enabledGroupAccessTimes()) {
                if (isset($progress[$usr_id]['ts']) && ($progress[$usr_id]['ts'] ?? false)) {
                    $tmp_data['access_time'] = ilDatePresentation::formatDate(
                        $tmp_date = new ilDateTime($progress[$usr_id]['ts'], IL_CAL_UNIX)
                    );
                    $tmp_data['access_time_unix'] = $tmp_date->get(IL_CAL_UNIX);
                } else {
                    $tmp_data['access_time'] = $this->lng->txt('no_date');
                    $tmp_data['access_time_unix'] = 0;
                }
            }
            $tmp_data['prtf'] = [];
            if ($do_prtf) {
                $tmp_data['prtf'] = ($all_prtf[$usr_id] ?? []);
            }
            $members[$usr_id] = $tmp_data;
        }
        return $members;
    }

    public function leaveObject(): void
    {
        $this->checkPermission('leave');

        $part = ilGroupParticipants::_getInstanceByObjId($this->object->getId());
        if ($part->isLastAdmin($this->user->getId())) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt('grp_err_administrator_required'));
            $this->viewObject();
            return;
        }

        $this->tabs_gui->setTabActive('grp_btn_unsubscribe');

        $cgui = new ilConfirmationGUI();
        $cgui->setHeaderText($this->lng->txt('grp_dismiss_myself'));
        $cgui->setFormAction($this->ctrl->getFormAction($this));
        $cgui->setCancel($this->lng->txt("cancel"), "cancel");
        $cgui->setConfirm($this->lng->txt("grp_btn_unsubscribe"), "unsubscribe");
        $this->tpl->setContent($cgui->getHTML());
    }

    public function unsubscribeObject(): void
    {
        $this->checkPermission('leave');

        $this->object->members_obj->delete($this->user->getId());

        $this->object->members_obj->sendNotification(
            ilGroupMembershipMailNotification::TYPE_UNSUBSCRIBE_MEMBER,
            $this->user->getId()
        );
        $this->object->members_obj->sendNotification(
            ilGroupMembershipMailNotification::TYPE_NOTIFICATION_UNSUBSCRIBE,
            $this->user->getId()
        );

        $this->tpl->setOnScreenMessage('success', $this->lng->txt('grp_msg_membership_annulled'), true);
        $this->ctrl->setParameterByClass(
            "ilrepositorygui",
            "ref_id",
            $this->tree->getParentId($this->object->getRefId())
        );
        $this->ctrl->redirectByClass("ilrepositorygui", "");
    }

    public function addContentTab(): void
    {
        $this->tabs_gui->addTab(
            "view_content",
            $this->lng->txt("content"),
            $this->ctrl->getLinkTarget($this, "view")
        );
    }

    /**
     * @inheritDoc
     */
    protected function getTabs(): void
    {
        global $DIC;

        $ilHelp = $DIC['ilHelp'];
        $ilHelp->setScreenIdComponent("grp");

        if ($this->access->checkAccess('read', '', $this->ref_id)) {
            if ($this->object->isNewsTimelineEffective()) {
                if (!$this->object->isNewsTimelineLandingPageEffective()) {
                    $this->addContentTab();
                }
                $this->tabs_gui->addTab(
                    "news_timeline",
                    $this->lng->txt("cont_news_timeline_tab"),
                    $this->ctrl->getLinkTargetByClass("ilnewstimelinegui", "show")
                );
                if ($this->object->isNewsTimelineLandingPageEffective()) {
                    $this->addContentTab();
                }
            } else {
                $this->addContentTab();
            }
        }
        if (
            $this->access->checkAccess('visible', '', $this->ref_id) ||
            $this->access->checkAccess('join', '', $this->ref_id) ||
            $this->access->checkAccess('read', '', $this->ref_id)
        ) {
            $this->tabs_gui->addTarget(
                "info_short",
                $this->ctrl->getLinkTargetByClass(
                    array("ilobjgroupgui", "ilinfoscreengui"),
                    "showSummary"
                ),
                "infoScreen",
                "",
                "",
                false
            );
        }


        if ($this->access->checkAccess('write', '', $this->object->getRefId())) {
            $this->tabs_gui->addTarget(
                "settings",
                $this->ctrl->getLinkTarget($this, "edit"),
                array("edit", "editMapSettings"),
                get_class($this),
                ""
            );
        }

        $is_participant = ilGroupParticipants::_isParticipant($this->ref_id, $this->user->getId());

        // Members
        $membership_gui = new ilGroupMembershipGUI($this, $this->object);
        $membership_gui->addMemberTab($this->tabs_gui, $is_participant);


        // badges
        if ($this->access->checkAccess('write', '', $this->ref_id)) {
            if (ilBadgeHandler::getInstance()->isObjectActive($this->object->getId())) {
                $this->tabs_gui->addTarget(
                    "obj_tool_setting_badges",
                    $this->ctrl->getLinkTargetByClass("ilbadgemanagementgui", ""),
                    "",
                    "ilbadgemanagementgui"
                );
            }
        }

        // skills
        if ($this->access->checkAccess('read', '', $this->ref_id) && ilContainer::_lookupContainerSetting(
            $this->object->getId(),
            ilObjectServiceSettingsGUI::SKILLS,
            ''
        )) {
            $this->tabs_gui->addTarget(
                "obj_tool_setting_skills",
                $this->ctrl->getLinkTargetByClass(array("ilcontainerskillgui", "ilcontskillpresentationgui"), ""),
                "",
                array("ilcontainerskillgui", "ilcontskillpresentationgui", "ilcontskilladmingui")
            );
        }

        // learning progress
        if (ilLearningProgressAccess::checkAccess($this->object->getRefId(), $is_participant)) {
            $this->tabs_gui->addTarget(
                'learning_progress',
                $this->ctrl->getLinkTargetByClass(array('ilobjgroupgui','illearningprogressgui'), ''),
                '',
                array('illplistofobjectsgui','illplistofsettingsgui','illearningprogressgui','illplistofprogressgui')
            );
        }

        // meta data
        if ($this->access->checkAccess('write', '', $this->ref_id)) {
            $md_gui = new ilObjectMetaDataGUI($this->object);
            $tab_link = $md_gui->getTab();
            if ($tab_link !== null) {
                $this->tabs_gui->addTab(
                    'meta_data',
                    $this->lng->txt('meta_data'),
                    $tab_link,
                    ''
                );
            }
        }


        if ($this->access->checkAccess('write', '', $this->object->getRefId())) {
            $this->tabs_gui->addTarget(
                'export',
                $this->ctrl->getLinkTargetByClass('ilexportgui', ''),
                'export',
                'ilexportgui'
            );
        }

        // parent tabs (all container: edit_permission, clipboard, trash
        parent::getTabs();

        if ($this->access->checkAccess('join', '', $this->object->getRefId()) and
            !$this->object->members_obj->isAssigned($this->user->getId())) {
            if (ilGroupWaitingList::_isOnList($this->user->getId(), $this->object->getId())) {
                $this->tabs_gui->addTab(
                    'leave',
                    $this->lng->txt('membership_leave'),
                    $this->ctrl->getLinkTargetByClass('ilgroupregistrationgui', 'show', '')
                );
            } else {
                $this->tabs_gui->addTarget(
                    "join",
                    $this->ctrl->getLinkTargetByClass('ilgroupregistrationgui', "show"),
                    'show',
                    ""
                );
            }
        }
        if ($this->access->checkAccess('leave', '', $this->object->getRefId()) and
            $this->object->members_obj->isMember($this->user->getId())) {
            $this->tabs_gui->addTarget(
                "grp_btn_unsubscribe",
                $this->ctrl->getLinkTarget($this, "leave"),
                '',
                ""
            );
        }
    }

    public function infoScreenObject(): void
    {
        $this->ctrl->setCmd("showSummary");
        $this->ctrl->setCmdClass("ilinfoscreengui");
        $this->infoScreen();
    }

    public function infoScreen(): void
    {
        $this->tabs_gui->setTabActive('info_short');

        if (!$this->checkPermissionBool('read')) {
            $this->checkPermission('visible');
        }

        ilMDUtils::_fillHTMLMetaTags(
            $this->object->getId(),
            $this->object->getId(),
            'grp'
        );

        $info = new ilInfoScreenGUI($this);

        if (strlen($this->object->getInformation())) {
            $info->addSection($this->lng->txt('grp_general_informations'));
            $info->addProperty($this->lng->txt('grp_information'), nl2br(
                ilUtil::makeClickable($this->object->getInformation(), true)
            ));
        }

        $info->enablePrivateNotes();
        $info->enableLearningProgress(true);

        $record_gui = new ilAdvancedMDRecordGUI(ilAdvancedMDRecordGUI::MODE_INFO, 'grp', $this->object->getId());
        $record_gui->setInfoObject($info);
        $record_gui->parse();

        // meta data
        $info->addMetaDataSections($this->object->getId(), 0, $this->object->getType());


        // support contacts
        $parts = ilParticipants::getInstance($this->object->getRefId());
        $contacts = $parts->getContacts();
        if (count($contacts) > 0) {
            $info->addSection($this->lng->txt("grp_mem_contacts"));
            foreach ($contacts as $c) {
                $pgui = new ilPublicUserProfileGUI($c);
                $pgui->setBackUrl($this->ctrl->getLinkTargetByClass("ilinfoscreengui"));
                $pgui->setEmbedded(true);
                $info->addProperty("", $pgui->getHTML());
            }
        }


        $info->addSection($this->lng->txt('group_registration'));
        $info->showLDAPRoleGroupMappingInfo();

        if (!$this->object->isRegistrationEnabled()) {
            $info->addProperty(
                $this->lng->txt('group_registration_mode'),
                $this->lng->txt('grp_reg_deac_info_screen')
            );
        } else {
            switch ($this->object->getRegistrationType()) {
                case ilGroupConstants::GRP_REGISTRATION_DIRECT:
                    $info->addProperty(
                        $this->lng->txt('group_registration_mode'),
                        $this->lng->txt('grp_reg_direct_info_screen')
                    );
                    break;

                case ilGroupConstants::GRP_REGISTRATION_REQUEST:
                    $info->addProperty(
                        $this->lng->txt('group_registration_mode'),
                        $this->lng->txt('grp_reg_req_info_screen')
                    );
                    break;

                case ilGroupConstants::GRP_REGISTRATION_PASSWORD:
                    $info->addProperty(
                        $this->lng->txt('group_registration_mode'),
                        $this->lng->txt('grp_reg_passwd_info_screen')
                    );
                    break;

            }
            /*
            $info->addProperty($this->lng->txt('group_registration_time'),
                ilDatePresentation::formatPeriod(
                    $this->object->getRegistrationStart(),
                    $this->object->getRegistrationEnd()));
            */
            if ($this->object->isRegistrationUnlimited()) {
                $info->addProperty(
                    $this->lng->txt('group_registration_time'),
                    $this->lng->txt('grp_registration_unlimited')
                );
            } elseif ($this->object->getRegistrationStart()->getUnixTime() < time()) {
                $info->addProperty(
                    $this->lng->txt("group_registration_time"),
                    $this->lng->txt('cal_until') . ' ' .
                                   ilDatePresentation::formatDate($this->object->getRegistrationEnd())
                );
            } elseif ($this->object->getRegistrationStart()->getUnixTime() >= time()) {
                $info->addProperty(
                    $this->lng->txt("group_registration_time"),
                    $this->lng->txt('cal_from') . ' ' .
                                   ilDatePresentation::formatDate($this->object->getRegistrationStart())
                );
            }
            if ($this->object->isMembershipLimited()) {
                if ($this->object->getMinMembers()) {
                    $info->addProperty(
                        $this->lng->txt("mem_min_users"),
                        (string) $this->object->getMinMembers()
                    );
                }
                if ($this->object->getMaxMembers()) {
                    $reg_info = ilObjGroupAccess::lookupRegistrationInfo($this->object->getId());

                    $info->addProperty(
                        $this->lng->txt('mem_free_places'),
                        (string) ($reg_info['reg_info_free_places'] ?? '0')
                    );
                }
            }

            if ($this->object->getCancellationEnd()) {
                $info->addProperty(
                    $this->lng->txt('grp_cancellation_end'),
                    ilDatePresentation::formatDate($this->object->getCancellationEnd())
                );
            }
        }

        if ($this->object->getStart() instanceof ilDateTime &&
            !$this->object->getStart()->isNull()
        ) {
            $info->addProperty(
                $this->lng->txt('grp_period'),
                ilDatePresentation::formatPeriod(
                    $this->object->getStart(),
                    $this->object->getEnd()
                )
            );
        }

        // Confirmation
        $privacy = ilPrivacySettings::getInstance();

        if ($privacy->groupConfirmationRequired() or ilCourseDefinedFieldDefinition::_getFields($this->object->getId()) or $privacy->enabledGroupExport()) {
            $field_info = ilExportFieldsInfo::_getInstanceByType($this->object->getType());

            $this->lng->loadLanguageModule('ps');
            $info->addSection($this->lng->txt('grp_user_agreement_info'));
            $info->addProperty($this->lng->txt('ps_export_data'), $field_info->exportableFieldsToInfoString());

            if ($fields = ilCourseDefinedFieldDefinition::_fieldsToInfoString($this->object->getId())) {
                $info->addProperty($this->lng->txt('ps_grp_user_fields'), $fields);
            }
        }


        // forward the command
        $this->ctrl->forwardCommand($info);
    }

    public function saveNotificationObject(): void
    {
        $noti = new ilMembershipNotifications($this->ref_id);

        $grp_notification = false;
        if ($this->http->wrapper()->query()->has('grp_ntf')) {
            $grp_notification = $this->http->wrapper()->query()->retrieve(
                'grp_ntf',
                $this->refinery->kindlyTo()->bool()
            );
        }

        if ($noti->canCurrentUserEdit()) {
            if ($grp_notification) {
                $noti->activateUser();
            } else {
                $noti->deactivateUser();
            }
        }
        $this->tpl->setOnScreenMessage('success', $this->lng->txt("settings_saved"), true);
        $this->ctrl->redirect($this, "");
    }

    protected function membersObject(): void
    {
        $this->ctrl->redirectByClass('ilgroupmembershipgui');
    }


    public static function _goto(int $a_target, string $a_add = ""): void
    {
        global $DIC;
        $main_tpl = $DIC->ui()->mainTemplate();

        $ilUser = $DIC->user();
        $ilAccess = $DIC->access();
        $lng = $DIC->language();
        $ilErr = $DIC['ilErr'];
        $http = $DIC->http();
        $refinery = $DIC->refinery();

        $target = '';
        if ($http->wrapper()->query()->has('target')) {
            $target = $http->wrapper()->query()->retrieve(
                'target',
                $refinery->kindlyTo()->string()
            );
        }
        if (substr($a_add, 0, 5) == 'rcode') {
            if ($ilUser->getId() == ANONYMOUS_USER_ID) {
                // Redirect to login for anonymous
                ilUtil::redirect(
                    "login.php?target=" . $target . "&cmd=force_login&lang=" .
                    $ilUser->getCurrentLanguage()
                );
            }

            // Redirects to target location after assigning user to group
            ilMembershipRegistrationCodeUtils::handleCode(
                $a_target,
                ilObject::_lookupType(ilObject::_lookupObjId($a_target)),
                substr($a_add, 5)
            );
        }

        if ($a_add == "mem" && $ilAccess->checkAccess("manage_members", "", $a_target)) {
            ilObjectGUI::_gotoRepositoryNode($a_target, "members");
        }

        if ($a_add == "comp" && ilContSkillPresentationGUI::isAccessible($a_target)) {
            ilObjectGUI::_gotoRepositoryNode($a_target, "competences");
        }

        if ($ilAccess->checkAccess("read", "", $a_target)) {
            ilObjectGUI::_gotoRepositoryNode($a_target);
        } else {
            // to do: force flat view
            if ($ilAccess->checkAccess("visible", "", $a_target)) {
                ilObjectGUI::_gotoRepositoryNode($a_target, "infoScreenGoto");
            } else {
                if ($ilAccess->checkAccess("read", "", ROOT_FOLDER_ID)) {
                    $main_tpl->setOnScreenMessage('failure', sprintf(
                        $lng->txt("msg_no_perm_read_item"),
                        ilObject::_lookupTitle(ilObject::_lookupObjId($a_target))
                    ), true);
                    ilObjectGUI::_gotoRepositoryRoot();
                }
            }
        }
        $ilErr->raiseError($lng->txt("msg_no_perm_read"), $ilErr->FATAL);
    }


    public function initForm(string $a_mode = 'edit', bool $a_omit_form_action = false): ilPropertyFormGUI
    {
        $obj_service = $this->getObjectService();
        $form = new ilPropertyFormGUI();

        if (!$a_omit_form_action) {
            switch ($a_mode) {
                case 'edit':
                    $form->setFormAction($this->ctrl->getFormAction($this, 'update'));
                    break;

                default:
                    $form->setTableWidth('600px');
                    $form->setFormAction($this->ctrl->getFormAction($this, 'save'));
                    break;
            }
        }

        // title/description
        $this->initFormTitleDescription($form);

        $form = $this->initDidacticTemplate($form);

        if ($a_mode == 'edit') {
            // group period
            $cdur = new ilDateDurationInputGUI($this->lng->txt('grp_period'), 'period');
            $this->lng->loadLanguageModule('mem');
            $cdur->enableToggleFullTime(
                $this->lng->txt('mem_period_without_time'),
                !$this->object->getStartTimeIndication()
            );
            $cdur->setShowTime(true);
            $cdur->setInfo($this->lng->txt('grp_period_info'));
            $cdur->setStart($this->object->getStart());
            $cdur->setEnd($this->object->getEnd());
            $form->addItem($cdur);

            // Group registration ############################################################
            $pres = new ilFormSectionHeaderGUI();
            $pres->setTitle($this->lng->txt('grp_setting_header_registration'));
            $form->addItem($pres);

            // Registration type
            $reg_type = new ilRadioGroupInputGUI($this->lng->txt('group_registration_mode'), 'registration_type');
            $reg_type->setValue((string) $this->object->getRegistrationType());

            $opt_dir = new ilRadioOption(
                $this->lng->txt('grp_reg_direct'),
                (string) ilGroupConstants::GRP_REGISTRATION_DIRECT
            );#$this->lng->txt('grp_reg_direct_info'));
            $reg_type->addOption($opt_dir);

            $opt_pass = new ilRadioOption(
                $this->lng->txt('grp_pass_request'),
                (string) ilGroupConstants::GRP_REGISTRATION_PASSWORD
            );
            $pass = new ilTextInputGUI($this->lng->txt("password"), 'password');
            $pass->setRequired(true);
            $pass->setInfo($this->lng->txt('grp_reg_password_info'));
            $pass->setValue($this->object->getPassword());
            $pass->setSize(32);
            $pass->setMaxLength(32);
            $opt_pass->addSubItem($pass);
            $reg_type->addOption($opt_pass);

            $opt_req = new ilRadioOption($this->lng->txt('grp_reg_request'), (string) ilGroupConstants::GRP_REGISTRATION_REQUEST, $this->lng->txt('grp_reg_request_info'));
            $reg_type->addOption($opt_req);

            $opt_deact = new ilRadioOption($this->lng->txt('grp_reg_no_selfreg'), (string) ilGroupConstants::GRP_REGISTRATION_DEACTIVATED, $this->lng->txt('grp_reg_disabled_info'));
            $reg_type->addOption($opt_deact);

            // Registration codes
            $reg_code = new ilCheckboxInputGUI($this->lng->txt('grp_reg_code'), 'reg_code_enabled');
            $reg_code->setChecked($this->object->isRegistrationAccessCodeEnabled());
            $reg_code->setValue('1');
            $reg_code->setInfo($this->lng->txt('grp_reg_code_enabled_info'));
            $form->addItem($reg_type);

            // Registration codes
            if (!$this->object->getRegistrationAccessCode()) {
                $this->object->setRegistrationAccessCode(ilMembershipRegistrationCodeUtils::generateCode());
            }
            $reg_link = new ilHiddenInputGUI('reg_code');
            $reg_link->setValue($this->object->getRegistrationAccessCode());
            $form->addItem($reg_link);

            $link = new ilCustomInputGUI($this->lng->txt('grp_reg_code_link'));
            $val = ilLink::_getLink($this->object->getRefId(), $this->object->getType(), array(), '_rcode' . $this->object->getRegistrationAccessCode());
            $link->setHTML('<span class="small">' . $val . '</span>');
            $reg_code->addSubItem($link);
            $form->addItem($reg_code);

            // time limit
            $this->lng->loadLanguageModule('dateplaner');
            $dur = new ilDateDurationInputGUI($this->lng->txt('grp_reg_limited'), 'reg');
            $dur->setShowTime(true);
            $dur->setStart($this->object->getRegistrationStart());
            $dur->setEnd($this->object->getRegistrationEnd());
            $form->addItem($dur);

            // cancellation limit
            $cancel = new ilDateTimeInputGUI($this->lng->txt('grp_cancellation_end'), 'cancel_end');
            $cancel->setInfo($this->lng->txt('grp_cancellation_end_info'));
            $cancel->setDate($this->object->getCancellationEnd());
            $form->addItem($cancel);

            // max member
            $lim = new ilCheckboxInputGUI($this->lng->txt('reg_grp_max_members_short'), 'registration_membership_limited');
            $lim->setValue('1');
            //			$lim->setOptionTitle($this->lng->txt('reg_grp_max_members'));
            $lim->setChecked($this->object->isMembershipLimited());

            $min = new ilTextInputGUI($this->lng->txt('reg_grp_min_members'), 'registration_min_members');
            $min->setSize(3);
            $min->setMaxLength(4);
            $min->setValue($this->object->getMinMembers() ?: '');
            $min->setInfo($this->lng->txt('grp_subscription_min_members_info'));
            $lim->addSubItem($min);

            $max = new ilTextInputGUI($this->lng->txt('reg_grp_max_members'), 'registration_max_members');
            $max->setValue($this->object->getMaxMembers() ?: '');
            //$max->setTitle($this->lng->txt('members'));
            $max->setSize(3);
            $max->setMaxLength(4);
            $max->setInfo($this->lng->txt('grp_reg_max_members_info'));
            $lim->addSubItem($max);

            /*
            $wait = new ilCheckboxInputGUI($this->lng->txt('grp_waiting_list'),'waiting_list');
            $wait->setValue(1);
            //$wait->setOptionTitle($this->lng->txt('grp_waiting_list'));
            $wait->setInfo($this->lng->txt('grp_waiting_list_info'));
            $wait->setChecked($this->object->isWaitingListEnabled() ? true : false);
            $lim->addSubItem($wait);
            $form->addItem($lim);
            */

            $wait = new ilRadioGroupInputGUI($this->lng->txt('grp_waiting_list'), 'waiting_list');

            $option = new ilRadioOption($this->lng->txt('none'), '0');
            $wait->addOption($option);

            $option = new ilRadioOption($this->lng->txt('grp_waiting_list_no_autofill'), '1');
            $option->setInfo($this->lng->txt('grp_waiting_list_info'));
            $wait->addOption($option);

            $option = new ilRadioOption($this->lng->txt('grp_waiting_list_autofill'), '2');
            $option->setInfo($this->lng->txt('grp_waiting_list_autofill_info'));
            $wait->addOption($option);

            if ($this->object->hasWaitingListAutoFill()) {
                $wait->setValue('2');
            } elseif ($this->object->isWaitingListEnabled()) {
                $wait->setValue('1');
            }

            $lim->addSubItem($wait);

            $form->addItem($lim);


            // Group presentation
            $parent_membership_ref_id = 0;
            $hasParentMembership =
                (
                    $parent_membership_ref_id = $this->tree->checkForParentType($this->object->getRefId(), 'crs', true)
                );

            $pres = new ilFormSectionHeaderGUI();
            $pres->setTitle($this->lng->txt('grp_setting_header_presentation'));
            $form->addItem($pres);

            // title and icon visibility
            $form = $obj_service->commonSettings()->legacyForm($form, $this->object)->addTitleIconVisibility();

            // top actions visibility
            $form = $obj_service->commonSettings()->legacyForm($form, $this->object)->addTopActionsVisibility();

            // custom icon
            $form = $obj_service->commonSettings()->legacyForm($form, $this->object)->addIcon();

            // tile image
            $form = $obj_service->commonSettings()->legacyForm($form, $this->object)->addTileImage();

            // list presentation
            $form = $this->initListPresentationForm($form);


            // presentation type
            $view_type = new ilRadioGroupInputGUI($this->lng->txt('grp_presentation_type'), 'view_mode');
            if ($hasParentMembership) {
                $parent_view_mode = ilObjCourseAccess::_lookupViewMode(ilObject::_lookupObjId($parent_membership_ref_id));
                $course_view_mode = '';
                switch ($parent_view_mode) {
                    case ilContainer::VIEW_SESSIONS:
                        $course_view_mode = ': ' . $this->lng->txt('cntr_view_sessions');
                        break;

                    case ilContainer::VIEW_SIMPLE:
                        $course_view_mode = ': ' . $this->lng->txt('cntr_view_simple');
                        break;

                    case ilContainer::VIEW_BY_TYPE:
                        $course_view_mode = ': ' . $this->lng->txt('cntr_view_by_type');
                        break;
                }
                if ($course_view_mode) {
                    $opt = new ilRadioOption($this->lng->txt('grp_view_inherit') . $course_view_mode, (string) ilContainer::VIEW_INHERIT);
                    $opt->setInfo($this->lng->txt('grp_view_inherit_info'));
                    $view_type->addOption($opt);
                }
            }

            if ($hasParentMembership && ilObjGroup::lookupViewMode($this->object->getId()) == ilContainer::VIEW_INHERIT) {
                $view_type->setValue((string) ilContainer::VIEW_INHERIT);
            } else {
                $view_type->setValue((string) ilObjGroup::lookupViewMode($this->object->getId()));
            }

            $opt = new ilRadioOption($this->lng->txt('cntr_view_sessions'), (string) ilContainer::VIEW_SESSIONS);
            $opt->setInfo($this->lng->txt('cntr_view_info_sessions'));
            $view_type->addOption($opt);

            // Limited sessions
            $this->lng->loadLanguageModule('crs');
            $sess = new ilCheckboxInputGUI($this->lng->txt('sess_limit'), 'sl');
            $sess->setValue('1');
            $sess->setChecked($this->object->isSessionLimitEnabled());
            $sess->setInfo($this->lng->txt('sess_limit_info'));

            $prev = new ilNumberInputGUI($this->lng->txt('sess_num_prev'), 'sp');
            $prev->setMinValue(0);
            $prev->setValue((string) (
                $this->object->getNumberOfPreviousSessions() == -1 ?
                    '' :
                    $this->object->getNumberOfPreviousSessions()
            ));
            $prev->setSize(2);
            $prev->setMaxLength(3);
            $sess->addSubItem($prev);

            $next = new ilNumberInputGUI($this->lng->txt('sess_num_next'), 'sn');
            $next->setMinValue(0);
            $next->setValue((string) (
                $this->object->getNumberOfNextSessions() == -1 ?
                    '' :
                    $this->object->getNumberOfNextSessions()
            ));
            $next->setSize(2);
            $next->setMaxLength(3);
            $sess->addSubItem($next);
            $opt->addSubItem($sess);

            $opt = new ilRadioOption($this->lng->txt('cntr_view_simple'), (string) ilContainer::VIEW_SIMPLE);
            $opt->setInfo($this->lng->txt('grp_view_info_simple'));
            $view_type->addOption($opt);

            $opt = new ilRadioOption($this->lng->txt('cntr_view_by_type'), (string) ilContainer::VIEW_BY_TYPE);
            $opt->setInfo($this->lng->txt('grp_view_info_by_type'));
            $view_type->addOption($opt);
            $form->addItem($view_type);


            // Sorting
            $sorting_settings = array();
            if ($hasParentMembership) {
                $sorting_settings[] = ilContainer::SORT_INHERIT;
            }
            $sorting_settings[] = ilContainer::SORT_TITLE;
            $sorting_settings[] = ilContainer::SORT_CREATION;
            $sorting_settings[] = ilContainer::SORT_MANUAL;
            $this->initSortingForm($form, $sorting_settings);

            // additional features
            $feat = new ilFormSectionHeaderGUI();
            $feat->setTitle($this->lng->txt('obj_features'));
            $form->addItem($feat);

            ilObjectServiceSettingsGUI::initServiceSettingsForm(
                $this->object->getId(),
                $form,
                array(
                        ilObjectServiceSettingsGUI::CALENDAR_CONFIGURATION,
                        ilObjectServiceSettingsGUI::USE_NEWS,
                        ilObjectServiceSettingsGUI::CUSTOM_METADATA,
                        ilObjectServiceSettingsGUI::AUTO_RATING_NEW_OBJECTS,
                        ilObjectServiceSettingsGUI::TAG_CLOUD,
                        ilObjectServiceSettingsGUI::BADGES,
                        ilObjectServiceSettingsGUI::SKILLS,
                        ilObjectServiceSettingsGUI::ORGU_POSITION_ACCESS,
                        ilObjectServiceSettingsGUI::EXTERNAL_MAIL_PREFIX
                    )
            );


            $mem = new ilCheckboxInputGUI($this->lng->txt('grp_show_members'), 'show_members');
            $mem->setChecked($this->object->getShowMembers());
            $mem->setInfo($this->lng->txt('grp_show_members_info'));
            $form->addItem($mem);

            // Show members type
            $mail_type = new ilRadioGroupInputGUI($this->lng->txt('grp_mail_type'), 'mail_type');
            $mail_type->setValue((string) $this->object->getMailToMembersType());

            $mail_tutors = new ilRadioOption(
                $this->lng->txt('grp_mail_tutors_only'),
                (string) ilObjGroup::MAIL_ALLOWED_TUTORS,
                $this->lng->txt('grp_mail_tutors_only_info')
            );
            $mail_type->addOption($mail_tutors);

            $mail_all = new ilRadioOption(
                $this->lng->txt('grp_mail_all'),
                (string) ilObjGroup::MAIL_ALLOWED_ALL,
                $this->lng->txt('grp_mail_all_info')
            );
            $mail_type->addOption($mail_all);
            $form->addItem($mail_type);

            // Self notification
            $not = new ilCheckboxInputGUI($this->lng->txt('grp_auto_notification'), 'auto_notification');
            $not->setValue('1');
            $not->setInfo($this->lng->txt('grp_auto_notification_info'));
            $not->setChecked($this->object->getAutoNotification());
            $form->addItem($not);
        }

        switch ($a_mode) {
            case 'create':
                $form->setTitle($this->lng->txt('grp_new'));
                $form->setTitleIcon(ilUtil::getImagePath('icon_grp.svg'));

                $form->addCommandButton('save', $this->lng->txt('grp_new'));
                $form->addCommandButton('cancel', $this->lng->txt('cancel'));
                break;

            case 'edit':
                $form->setTitle($this->lng->txt('grp_edit'));
                $form->setTitleIcon(ilUtil::getImagePath('icon_grp.svg'));

                // Edit ecs export settings
                $ecs = new ilECSGroupSettings($this->object);
                $ecs->addSettingsToForm($form, 'grp');

                $form->addCommandButton('update', $this->lng->txt('save'));
                $form->addCommandButton('cancel', $this->lng->txt('cancel'));
                break;
        }
        return $form;
    }

    protected function setSubTabs(string $a_tab): void
    {
        switch ($a_tab) {

            case 'settings':
                $this->tabs_gui->addSubTabTarget(
                    "grp_settings",
                    $this->ctrl->getLinkTarget($this, 'edit'),
                    "edit",
                    get_class($this)
                );

                $this->tabs_gui->addSubTabTarget(
                    "grp_info_settings",
                    $this->ctrl->getLinkTarget($this, 'editInfo'),
                    "editInfo",
                    get_class($this)
                );

                if (ilMapUtil::isActivated()) {
                    $this->tabs_gui->addSubTabTarget(
                        "grp_map_settings",
                        $this->ctrl->getLinkTarget($this, 'editMapSettings'),
                        "editMapSettings",
                        get_class($this)
                    );
                }

                $this->tabs_gui->addSubTabTarget(
                    'groupings',
                    $this->ctrl->getLinkTargetByClass('ilobjcoursegroupinggui', 'listGroupings'),
                    'listGroupings',
                    get_class($this)
                );

                // only show if export permission is granted
                if (ilPrivacySettings::getInstance()->checkExportAccess($this->object->getRefId()) or ilCourseDefinedFieldDefinition::_hasFields($this->object->getId())) {
                    $this->tabs_gui->addSubTabTarget(
                        'grp_custom_user_fields',
                        $this->ctrl->getLinkTargetByClass('ilobjectcustomuserfieldsgui'),
                        '',
                        'ilobjectcustomuserfieldsgui'
                    );
                }

                // news settings
                if ($this->object->getUseNews()) {
                    $this->tabs_gui->addSubTab(
                        'obj_news_settings',
                        $this->lng->txt("cont_news_settings"),
                        $this->ctrl->getLinkTargetByClass('ilcontainernewssettingsgui')
                    );
                }

                $lti_settings = new ilLTIProviderObjectSettingGUI($this->object->getRefId());
                if ($lti_settings->hasSettingsAccess()) {
                    $this->tabs_gui->addSubTabTarget(
                        'lti_provider',
                        $this->ctrl->getLinkTargetByClass(ilLTIProviderObjectSettingGUI::class)
                    );
                }

                $this->tabs_gui->addSubTabTarget(
                    "obj_multilinguality",
                    $this->ctrl->getLinkTargetByClass("ilobjecttranslationgui", ""),
                    "",
                    "ilobjecttranslationgui"
                );


                break;
        }
    }

    private function checkAgreement(): bool
    {
        if ($this->access->checkAccess('write', '', $this->object->getRefId())) {
            return true;
        }

        // Disable aggrement if is not member of group
        if (!$this->object->members_obj->isAssigned($this->user->getId())) {
            return true;
        }

        if (ilMemberViewSettings::getInstance()->isActive()) {
            return true;
        }

        $privacy = ilPrivacySettings::getInstance();

        // Check agreement
        if (($privacy->groupConfirmationRequired() or ilCourseDefinedFieldDefinition::_hasFields($this->object->getId()))
            and !ilMemberAgreement::_hasAccepted($this->user->getId(), $this->object->getId())) {
            return false;
        }
        // Check required fields
        if (!ilCourseUserData::_checkRequired($this->user->getId(), $this->object->getId())) {
            return false;
        }
        return true;
    }

    /**
     * @inheritDoc
     */
    public function prepareOutput(bool $show_subobjects = true): bool
    {
        return parent::prepareOutput($show_subobjects);
    }

    public function createMailSignature(): string
    {
        $link = chr(13) . chr(10) . chr(13) . chr(10);
        $link .= $this->lng->txt('grp_mail_permanent_link');
        $link .= chr(13) . chr(10) . chr(13) . chr(10);
        $link .= ilLink::_getLink($this->object->getRefId());
        return rawurlencode(base64_encode($link));
    }

    /**
     * @inheritDoc
     */
    protected function initHeaderAction(?string $sub_type = null, ?int $sub_id = null): ?ilObjectListGUI
    {
        global $DIC;

        $ilSetting = $DIC['ilSetting'];
        $ilUser = $DIC['ilUser'];

        $lg = parent::initHeaderAction($sub_type, $sub_id);

        if (ilGroupParticipants::_isParticipant($this->ref_id, $ilUser->getId())) {
            if (ilMembershipNotifications::isActiveForRefId($this->ref_id)) {
                $noti = new ilMembershipNotifications($this->ref_id);
                if (!$noti->isCurrentUserActive()) {
                    $lg->addHeaderIcon(
                        "not_icon",
                        ilUtil::getImagePath("notification_off.svg"),
                        $this->lng->txt("grp_notification_deactivated")
                    );

                    $this->ctrl->setParameter($this, "grp_ntf", 1);
                    $caption = "grp_activate_notification";
                } else {
                    $lg->addHeaderIcon(
                        "not_icon",
                        ilUtil::getImagePath("notification_on.svg"),
                        $this->lng->txt("grp_notification_activated")
                    );

                    $this->ctrl->setParameter($this, "grp_ntf", 0);
                    $caption = "grp_deactivate_notification";
                }

                if ($noti->canCurrentUserEdit()) {
                    $lg->addCustomCommand(
                        $this->ctrl->getLinkTarget($this, "saveNotification"),
                        $caption
                    );
                }

                $this->ctrl->setParameter($this, "grp_ntf", "");
            }
        }

        return $lg;
    }


    public function addCustomData(array $a_data): array
    {
        // object defined fields
        $odfs = ilCourseUserData::_getValuesByObjId($this->object->getId());

        $res_data = array();
        foreach ($a_data as $usr_id => $user_data) {
            $res_data[$usr_id] = $user_data;

            // udf
            $udf_data = new ilUserDefinedData($usr_id);
            foreach ($udf_data->getAll() as $field => $value) {
                list($f, $field_id) = explode('_', $field);
                $res_data[$usr_id]['udf_' . $field_id] = (string) $value;
            }

            foreach ((array) ($odfs[$usr_id] ?? []) as $cdf_field => $cdf_value) {
                $res_data[$usr_id]['cdf_' . $cdf_field] = (string) $cdf_value;
            }
        }

        return $res_data;
    }

    public function getLocalRoles(): array
    {
        $local_roles = $this->object->getLocalGroupRoles();
        $grp_member = $this->object->getDefaultMemberRole();
        $grp_roles = array();

        //put the group member role to the top of the crs_roles array
        if (in_array($grp_member, $local_roles)) {
            $grp_roles[$grp_member] = ilObjRole::_getTranslation(array_search($grp_member, $local_roles));
            unset($local_roles[$grp_roles[$grp_member]]);
        }

        foreach ($local_roles as $title => $role_id) {
            $grp_roles[(int) $role_id] = ilObjRole::_getTranslation($title);
        }
        return $grp_roles;
    }

    protected function jump2UsersGalleryObject(): void
    {
        $this->ctrl->redirectByClass('ilUsersGalleryGUI');
    }

    /**
     * @inheritDoc
     */
    public function setSideColumnReturn(): void
    {
        $this->ctrl->setReturn($this, "view");
    }
} // END class.ilObjGroupGUI
