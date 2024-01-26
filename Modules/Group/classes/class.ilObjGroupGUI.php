<?php

/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once "./Services/Container/classes/class.ilContainerGUI.php";
include_once('./Modules/Group/classes/class.ilObjGroup.php');

/**
 * Class ilObjGroupGUI
 *
 * @author    Stefan Meyer <smeyer.ilias@gmx.de>
 * @author    Sascha Hofmann <saschahofmann@gmx.de>
 *
 * @version    $Id$
 *
 * @ilCtrl_Calls ilObjGroupGUI: ilGroupRegistrationGUI, ilPermissionGUI, ilInfoScreenGUI, ilLearningProgressGUI
 * @ilCtrl_Calls ilObjGroupGUI: ilPublicUserProfileGUI, ilObjCourseGroupingGUI, ilObjStyleSheetGUI
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
    /**
     * @var ilNewsService
     */
    protected $news;

    /**
    * Constructor
    * @access	public
    */
    public function __construct($a_data, $a_id, $a_call_by_reference, $a_prepare_output = false)
    {
        global $DIC;

        $ilSetting = $DIC['ilSetting'];

        $this->type = "grp";
        parent::__construct($a_data, $a_id, $a_call_by_reference, $a_prepare_output);

        $this->lng->loadLanguageModule('grp');
        $this->lng->loadLanguageModule('obj');

        $this->setting = $ilSetting;
        $this->news = $DIC->news();
    }

    public function executeCommand()
    {
        global $DIC;

        $ilUser = $DIC['ilUser'];
        $rbacsystem = $DIC['rbacsystem'];
        $ilAccess = $DIC['ilAccess'];
        $ilNavigationHistory = $DIC['ilNavigationHistory'];
        $ilErr = $DIC['ilErr'];
        $ilToolbar = $DIC['ilToolbar'];

        $next_class = $this->ctrl->getNextClass($this);
        $cmd = $this->ctrl->getCmd();
        $this->prepareOutput();

        // add entry to navigation history
        if (!$this->getCreationMode() &&
            $ilAccess->checkAccess("read", "", $_GET["ref_id"])) {
            include_once("./Services/Link/classes/class.ilLink.php");
            $ilNavigationHistory->addItem(
                $_GET["ref_id"],
                ilLink::_getLink($_GET["ref_id"], "grp"),
                "grp"
            );
        }

        // if news timeline is landing page, redirect if necessary
        if ($next_class == "" && $cmd == "" && $this->object->isNewsTimelineLandingPageEffective()
            && $ilAccess->checkAccess("read", "", $_GET["ref_id"])) {
            $this->ctrl->redirectbyclass("ilnewstimelinegui");
        }

        $header_action = true;
        switch ($next_class) {
            case 'ilreputilgui':
                $ru = new \ilRepUtilGUI($this);
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
                
                include_once './Modules/Group/classes/class.ilGroupMembershipGUI.php';
                $mem_gui = new ilGroupMembershipGUI($this, $this->object);
                $this->ctrl->forwardCommand($mem_gui);
                break;
            
            
            case 'ilgroupregistrationgui':
                $this->ctrl->setReturn($this, '');
                $this->tabs_gui->setTabActive('join');
                include_once('./Modules/Group/classes/class.ilGroupRegistrationGUI.php');
                $registration = new ilGroupRegistrationGUI($this->object);
                $this->ctrl->forwardCommand($registration);
                break;

            case 'ilpermissiongui':
                $this->tabs_gui->activateTab('perm_settings');
                include_once("Services/AccessControl/classes/class.ilPermissionGUI.php");
                $perm_gui = new ilPermissionGUI($this);
                $ret = &$this->ctrl->forwardCommand($perm_gui);
                break;

            case "ilinfoscreengui":
                $ret = &$this->infoScreen();
                break;

            case "illearningprogressgui":
                include_once './Services/Tracking/classes/class.ilLearningProgressGUI.php';

                $new_gui = new ilLearningProgressGUI(
                    ilLearningProgressGUI::LP_CONTEXT_REPOSITORY,
                    $this->object->getRefId(),
                    $_GET['user_id'] ? $_GET['user_id'] : $ilUser->getId()
                );
                $this->ctrl->forwardCommand($new_gui);
                $this->tabs_gui->setTabActive('learning_progress');
                break;

            case 'ilobjcoursegroupinggui':
                $this->setSubTabs('settings');
                
                include_once './Modules/Course/classes/class.ilObjCourseGroupingGUI.php';
                $this->ctrl->setReturn($this, 'edit');
                $crs_grp_gui = new ilObjCourseGroupingGUI($this->object, (int) $_GET['obj_id']);
                $this->ctrl->forwardCommand($crs_grp_gui);
                
                $this->tabs_gui->setTabActive('settings');
                $this->tabs_gui->setSubTabActive('groupings');
                break;

            case 'ilcoursecontentgui':

                include_once './Modules/Course/classes/class.ilCourseContentGUI.php';
                $course_content_obj = new ilCourseContentGUI($this);
                $this->ctrl->forwardCommand($course_content_obj);
                break;

            case 'ilpublicuserprofilegui':
                require_once './Services/User/classes/class.ilPublicUserProfileGUI.php';
                $this->setSubTabs('members');
                $this->tabs_gui->setTabActive('group_members');
                $this->tabs_gui->setSubTabActive('grp_members_gallery');
                $profile_gui = new ilPublicUserProfileGUI($_GET["user"]);
                if ($_GET["back_url"] == "") {
                    $profile_gui->setBackUrl($this->ctrl->getLinkTargetByClass(["ilGroupMembershipGUI", "ilUsersGalleryGUI"], 'view'));
                }
                $html = $this->ctrl->forwardCommand($profile_gui);
                $this->tpl->setVariable("ADM_CONTENT", $html);
                break;

            case "ilcolumngui":
                $this->tabs_gui->setTabActive('none');
                $this->checkPermission("read");
                include_once("./Services/Style/Content/classes/class.ilObjStyleSheet.php");
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
                include_once './Services/Object/classes/class.ilObjectCopyGUI.php';
                $cp = new ilObjectCopyGUI($this);
                $cp->setType('grp');
                $this->ctrl->forwardCommand($cp);
                break;

            case "ilobjstylesheetgui":
                $this->forwardToStyleSheet();
                break;
                
            case 'ilobjectcustomuserfieldsgui':
                include_once './Services/Membership/classes/class.ilObjectCustomUserFieldsGUI.php';
                $cdf_gui = new ilObjectCustomUserFieldsGUI($this->object->getId());
                $this->setSubTabs('settings');
                $this->tabs_gui->setTabActive('settings');
                $this->tabs_gui->activateSubTab('grp_custom_user_fields');
                $this->ctrl->forwardCommand($cdf_gui);
                break;
                
            case 'ilmemberagreementgui':
                include_once('Services/Membership/classes/class.ilMemberAgreementGUI.php');
                $this->ctrl->setReturn($this, '');
                $this->tabs_gui->setTabActive('view_content');
                $agreement = new ilMemberAgreementGUI($this->object->getRefId());
                $this->ctrl->forwardCommand($agreement);
                break;

            case 'ilexportgui':
                $this->tabs_gui->setTabActive('export');
                include_once './Services/Export/classes/class.ilExportGUI.php';
                $exp = new ilExportGUI($this);
                $exp->addFormat('xml');
                $this->ctrl->forwardCommand($exp);
                break;
                                
            case "ilcommonactiondispatchergui":
                include_once("Services/Object/classes/class.ilCommonActionDispatcherGUI.php");
                $gui = ilCommonActionDispatcherGUI::getInstanceFromAjaxCall();
                $this->ctrl->forwardCommand($gui);
                break;
            
            case 'ilobjectservicesettingsgui':
                $this->ctrl->setReturn($this, 'edit');
                $this->setSubTabs("settings");
                $this->tabs_gui->activateTab('settings');
                $this->tabs_gui->activateSubTab('tool_settings');
                
                include_once './Services/Object/classes/class.ilObjectServiceSettingsGUI.php';
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
                include_once 'Services/Mail/classes/class.ilMail.php';
                $mail = new ilMail($ilUser->getId());

                if (!($ilAccess->checkAccess('manage_members', '', $this->object->getRefId()) ||
                    $this->object->getMailToMembersType() == ilObjGroup::MAIL_ALLOWED_ALL) &&
                    $rbacsystem->checkAccess('internal_mail', $mail->getMailObjectReferenceId())) {
                    $ilErr->raiseError($this->lng->txt("msg_no_perm_read"), $ilErr->MESSAGE);
                }

                $this->tabs_gui->setTabActive('members');
                
                include_once './Services/Contact/classes/class.ilMailMemberSearchGUI.php';
                include_once './Services/Contact/classes/class.ilMailMemberGroupRoles.php';

                $mail_search = new ilMailMemberSearchGUI($this, $this->object->getRefId(), new ilMailMemberGroupRoles());
                $mail_search->setObjParticipants(ilCourseParticipants::_getInstanceByObjId($this->object->getId()));
                $this->ctrl->forwardCommand($mail_search);
                break;

            case 'ilbadgemanagementgui':
                $this->tabs_gui->setTabActive('obj_tool_setting_badges');
                include_once 'Services/Badge/classes/class.ilBadgeManagementGUI.php';
                $bgui = new ilBadgeManagementGUI($this->object->getRefId(), $this->object->getId(), 'grp');
                $this->ctrl->forwardCommand($bgui);
                break;
                
            case "ilcontainernewssettingsgui":
                $this->setSubTabs("settings");
                $this->tabs_gui->setTabActive('settings');
                $this->tabs_gui->activateSubTab('obj_news_settings');
                include_once("./Services/Container/classes/class.ilContainerNewsSettingsGUI.php");
                $news_set_gui = new ilContainerNewsSettingsGUI($this);
                $news_set_gui->setTimeline(true);
                $news_set_gui->setCronNotifications(true);
                $news_set_gui->setHideByDate(true);
                $this->ctrl->forwardCommand($news_set_gui);
                break;

            case "ilnewstimelinegui":
                $this->checkPermission("read");
                $this->tabs_gui->setTabActive('news_timeline');
                include_once("./Services/News/classes/class.ilNewsTimelineGUI.php");
                $t = ilNewsTimelineGUI::getInstance($this->object->getRefId(), $this->object->getNewsTimelineAutoENtries());
                $t->setUserEditAll($ilAccess->checkAccess('write', '', $this->object->getRefId(), 'grp'));
                $this->showPermanentLink($tpl);
                $this->ctrl->forwardCommand($t);
                include_once 'Services/Tracking/classes/class.ilLearningProgress.php';
                ilLearningProgress::_tracProgress(
                    $ilUser->getId(),
                    $this->object->getId(),
                    $this->object->getRefId(),
                    'grp'
                );
                break;

            case "ilcontainerskillgui":
                $this->tabs_gui->activateTab('obj_tool_setting_skills');
                include_once("./Services/Container/Skills/classes/class.ilContainerSkillGUI.php");
                $gui = new ilContainerSkillGUI($this);
                $this->ctrl->forwardCommand($gui);
                break;

            case 'ilcalendarpresentationgui':
                include_once('./Services/Calendar/classes/class.ilCalendarPresentationGUI.php');
                $cal = new ilCalendarPresentationGUI($this->object->getRefId());
                $ret = $this->ctrl->forwardCommand($cal);
                break;

            case 'ilobjectmetadatagui':
                if (!$ilAccess->checkAccess('write', '', $this->object->getRefId())) {
                    $ilErr->raiseError($this->lng->txt('permission_denied'), $ilErr->WARNING);
                }
                $this->tabs_gui->activateTab('meta_data');
                $this->ctrl->forwardCommand(new ilObjectMetaDataGUI($this->object));
                break;


            case 'ilobjecttranslationgui':
                $this->checkPermissionBool("write");
                $this->setSubTabs("settings");
                $this->tabs->activateTab("settings");
                include_once("./Services/Object/classes/class.ilObjectTranslationGUI.php");
                $transgui = new ilObjectTranslationGUI($this);
                $this->ctrl->forwardCommand($transgui);
                break;

            default:
            
                // check visible permission
                if (!$this->getCreationMode() and
                        !$ilAccess->checkAccess('visible', '', $this->object->getRefId(), 'grp') and
                        !$ilAccess->checkAccess('read', '', $this->object->getRefId(), 'grp')) {
                    $ilErr->raiseError($this->lng->txt("msg_no_perm_read"), $ilErr->MESSAGE);
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
                    && !$rbacsystem->checkAccess('read', $this->object->getRefId()) && $cmd != 'infoScreen')
                    || $cmd == 'join') {
                    // no join permission -> redirect to info screen
                    if (!$rbacsystem->checkAccess('join', $this->object->getRefId())) {
                        $this->ctrl->redirect($this, "infoScreen");
                    } else {	// no read -> show registration
                        include_once('./Modules/Group/classes/class.ilGroupRegistrationGUI.php');
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
    
    public function viewObject()
    {
        global $DIC;

        $tree = $DIC['tree'];
        $rbacsystem = $DIC['rbacsystem'];
        $ilUser = $DIC['ilUser'];

        include_once 'Services/Tracking/classes/class.ilLearningProgress.php';
        ilLearningProgress::_tracProgress(
            $ilUser->getId(),
            $this->object->getId(),
            $this->object->getRefId(),
            'grp'
        );

        ilMDUtils::_fillHTMLMetaTags(
            $this->object->getId(),
            $this->object->getId(),
            'grp'
        );


        if (strtolower($_GET["baseClass"]) == "iladministrationgui") {
            parent::viewObject();
            return true;
        }
        
        if (!$this->checkAgreement()) {
            include_once('Services/Membership/classes/class.ilMemberAgreementGUI.php');
            $this->tabs_gui->setTabActive('view_content');
            $this->ctrl->setReturn($this, 'view');
            $agreement = new ilMemberAgreementGUI($this->object->getRefId());
            $this->ctrl->setCmdClass(get_class($agreement));
            $this->ctrl->forwardCommand($agreement);
            return true;
        }
        
        $this->tabs_gui->setTabActive('view_content');
        $this->renderObject();
    }
    
    /**
    * Render group
    */
    public function renderObject()
    {
        global $DIC;

        $ilTabs = $DIC['ilTabs'];
        
        $ilTabs->activateTab("view_content");
        $ret = parent::renderObject();
        return $ret;
    }

    /**
     * Modify Item ListGUI for presentation in container
     * @global type $tree
     * @param type $a_item_list_gui
     * @param type $a_item_data
     * @param type $a_show_path
     */
    public function modifyItemGUI($a_item_list_gui, $a_item_data, $a_show_path)
    {
        global $DIC;

        $tree = $DIC['tree'];

        // if folder is in a course, modify item list gui according to course requirements
        if ($course_ref_id = $tree->checkForParentType($this->object->getRefId(), 'crs')) {
            include_once("./Modules/Course/classes/class.ilObjCourse.php");
            include_once("./Modules/Course/classes/class.ilObjCourseGUI.php");
            $course_obj_id = ilObject::_lookupObjId($course_ref_id);
            ilObjCourseGUI::_modifyItemGUI(
                $a_item_list_gui,
                'ilcoursecontentgui',
                $a_item_data,
                $a_show_path,
                ilObjCourse::_lookupAboStatus($course_obj_id),
                $course_ref_id,
                $course_obj_id,
                $this->object->getRefId()
            );
        }
    }
    
    /**
     * After object creation
     * @param \ilObject $new_object
     */
    public function afterSave(\ilObject $new_object, $a_redirect = true)
    {
        global $DIC;

        $ilUser = $DIC['ilUser'];
        $ilSetting = $DIC['ilSetting'];
        
        $new_object->setRegistrationType(GRP_REGISTRATION_DIRECT);
        $new_object->update();
        
        // check for parent group or course => SORT_INHERIT
        $sort_mode = ilContainer::SORT_TITLE;
        if (
                $GLOBALS['DIC']['tree']->checkForParentType($new_object->getRefId(), 'crs', true) ||
                $GLOBALS['DIC']['tree']->checkForParentType($new_object->getRefId(), 'grp', true)
        ) {
            $sort_mode = ilContainer::SORT_INHERIT;
        }
        
        // Save sorting
        include_once './Services/Container/classes/class.ilContainerSortingSettings.php';
        $sort = new ilContainerSortingSettings($new_object->getId());
        $sort->setSortMode($sort_mode);
        $sort->update();
        
        
        // Add user as admin and enable notification
        include_once './Modules/Group/classes/class.ilGroupParticipants.php';
        $members_obj = ilGroupParticipants::_getInstanceByObjId($new_object->getId());
        $members_obj->add($ilUser->getId(), IL_GRP_ADMIN);
        $members_obj->updateNotification($ilUser->getId(), $ilSetting->get('mail_grp_admin_notification', true));
        $members_obj->updateContact($ilUser->getId(), true);
        
        ilUtil::sendSuccess($this->lng->txt("object_added"), true);
        if ($a_redirect) {
            $this->ctrl->setParameter($this, "ref_id", $new_object->getRefId());
            $this->ctrl->redirect($this, 'edit');
        }
    }
    
    /**
     * Edit object
     *
     * @access public
     * @param ilPropertyFormGUI
     * @return
     */
    public function editObject(ilPropertyFormGUI $a_form = null)
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
    
    /**
     * change group type
     *
     * @access public
     * @param
     * @return
     */
    public function updateGroupTypeObject()
    {
        include_once './Services/DidacticTemplate/classes/class.ilDidacticTemplateUtils.php';
        ilDidacticTemplateUtils::switchTemplate(
            $this->object->getRefId(),
            (int) $_REQUEST['grp_type']
        );
        
        ilUtil::sendSuccess($this->lng->txt('settings_saved'), true);
        $this->ctrl->redirect($this, 'edit');
    }
    
    
    /**
     * update group settings
     * @param bool update group type
     * @access public
     */
    public function updateObject()
    {
        $obj_service = $this->getObjectService();

        $this->checkPermission('write');
        
        $form = $this->initForm();
        if ($form->checkInput()) {
            // handle group type settings
            include_once './Services/DidacticTemplate/classes/class.ilDidacticTemplateObjSettings.php';
            $old_type = ilDidacticTemplateObjSettings::lookupTemplateId($this->object->getRefId());
            
            $modified = false;
            $new_type_info = $form->getInput('didactic_type');
            if ($new_type_info) {
                $new_type = explode('_', $form->getInput('didactic_type'));
                $new_type = $new_type[1];

                $modified = ($new_type != $old_type);
                ilLoggerFactory::getLogger('grp')->info('Switched group type from ' . $old_type . ' to ' . $new_type);
            }
            
            // Additional checks: both tile and session limitation activated (not supported)
            if (
                $form->getInput('sl') == "1" &&
                $form->getInput('list_presentation') == "tile") {
                $form->setValuesByPost();
                ilUtil::sendFailure($this->lng->txt('crs_tile_and_session_limit_not_supported'));
                return $this->editObject($form);
            }

            $old_autofill = $this->object->hasWaitingListAutoFill();

            $this->object->setTitle(ilUtil::stripSlashes($form->getInput('title')));
            $this->object->setDescription(ilUtil::stripSlashes($form->getInput('desc')));
            $this->object->setGroupType(ilUtil::stripSlashes($form->getInput('grp_type')));
            $this->object->setRegistrationType(ilUtil::stripSlashes($form->getInput('registration_type')));
            $this->object->setPassword(ilUtil::stripSlashes($form->getInput('password')));
            $this->object->enableUnlimitedRegistration((bool) !$form->getInput('reg_limit_time'));
            $this->object->enableMembershipLimitation((bool) $form->getInput('registration_membership_limited'));
            $this->object->setMinMembers((int) $form->getInput('registration_min_members'));
            $this->object->setMaxMembers((int) $form->getInput('registration_max_members'));
            $this->object->enableRegistrationAccessCode((bool) $form->getInput('reg_code_enabled'));
            $this->object->setRegistrationAccessCode($form->getInput('reg_code'));
            $this->object->setViewMode($form->getInput('view_mode'));
            $this->object->setMailToMembersType((int) $form->getInput('mail_type'));
            $this->object->setShowMembers((int) $form->getInput('show_members'));
            $this->object->setAutoNotification((bool) $form->getInput('auto_notification'));

            // session limit
            $this->object->enableSessionLimit((int) $form->getInput('sl'));
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

            switch ((int) $_POST['waiting_list']) {
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


            include_once './Services/Object/classes/class.ilObjectServiceSettingsGUI.php';
            ilObjectServiceSettingsGUI::updateServiceSettingsForm(
                $this->object->getId(),
                $form,
                $this->getSubServices()
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
            require_once('Services/Tracking/classes/class.ilChangeEvent.php');
            global $DIC;

            $ilUser = $DIC['ilUser'];
            ilChangeEvent::_recordWriteEvent(
                $this->object->getId(),
                $ilUser->getId(),
                'update'
            );
            ilChangeEvent::_catchupWriteEvents($this->object->getId(), $ilUser->getId());
            // END PATCH ChangeEvents: Record update Object.
            // Update ecs export settings
            include_once 'Modules/Group/classes/class.ilECSGroupSettings.php';
            $ecs = new ilECSGroupSettings($this->object);
            $ecs->handleSettingsUpdate();
        } else {
            ilUtil::sendFailure($GLOBALS['DIC']->language()->txt('err_check_input')); // #16975
            
            $form->setValuesByPost();
            $this->editObject($form);
            return true;
        }

        // group type modified
        if ($modified) {
            if ($new_type == 0) {
                $new_type_txt = $GLOBALS['DIC']['lng']->txt('il_grp_status_open');
            } else {
                include_once './Services/DidacticTemplate/classes/class.ilDidacticTemplateSetting.php';
                $dtpl = new ilDidacticTemplateSetting($new_type);
                $new_type_txt = $dtpl->getPresentationTitle($GLOBALS['DIC']['lng']->getLangKey());
            }
            
            
            include_once './Services/Utilities/classes/class.ilConfirmationGUI.php';
            ilUtil::sendQuestion($this->lng->txt('grp_warn_grp_type_changed'));
            $confirm = new ilConfirmationGUI();
            $confirm->setFormAction($this->ctrl->getFormAction($this));
            $confirm->addItem(
                'grp_type',
                $new_type,
                $this->lng->txt('grp_info_new_grp_type') . ': ' . $new_type_txt
            );
            $confirm->addButton($this->lng->txt('grp_change_type'), 'updateGroupType');
            $confirm->setCancel($this->lng->txt('cancel'), 'edit');
            
            $this->tpl->setContent($confirm->getHTML());
            return true;
        } else {
            ilUtil::sendSuccess($this->lng->txt("msg_obj_modified"), true);
            $this->ctrl->redirect($this, 'edit');
            return true;
        }
    }

    protected function getSubServices() : array
    {
        $subs = array(
            ilObjectServiceSettingsGUI::CALENDAR_CONFIGURATION,
            ilObjectServiceSettingsGUI::CUSTOM_METADATA,
            ilObjectServiceSettingsGUI::AUTO_RATING_NEW_OBJECTS,
            ilObjectServiceSettingsGUI::TAG_CLOUD,
            ilObjectServiceSettingsGUI::BADGES,
            ilObjectServiceSettingsGUI::SKILLS,
            ilObjectServiceSettingsGUI::ORGU_POSITION_ACCESS,
            ilObjectServiceSettingsGUI::EXTERNAL_MAIL_PREFIX
        );
        if ($this->news->isGloballyActivated()) {
            $subs[] = ilObjectServiceSettingsGUI::USE_NEWS;
        }

        return $subs;
    }

    /**
    * Edit Map Settings
    */
    public function editMapSettingsObject()
    {
        global $DIC;

        $ilUser = $DIC['ilUser'];
        $ilCtrl = $DIC['ilCtrl'];
        $ilUser = $DIC['ilUser'];
        $ilAccess = $DIC['ilAccess'];

        $this->setSubTabs("settings");
        $this->tabs_gui->setTabActive('settings');
        $this->tabs_gui->setSubTabActive('grp_map_settings');
        
        include_once('./Services/Maps/classes/class.ilMapUtil.php');
        if (!ilMapUtil::isActivated() ||
            !$ilAccess->checkAccess("write", "", $this->object->getRefId())) {
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


        include_once("./Services/Form/classes/class.ilPropertyFormGUI.php");
        $form = new ilPropertyFormGUI();
        $form->setFormAction($ilCtrl->getFormAction($this));
        
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
        $loc_prop->setLatitude($latitude);
        $loc_prop->setLongitude($longitude);
        $loc_prop->setZoom($zoom);
        $form->addItem($loc_prop);
        
        $form->addCommandButton("saveMapSettings", $this->lng->txt("save"));
        
        $this->tpl->setVariable("ADM_CONTENT", $form->getHTML());
    }

    public function saveMapSettingsObject()
    {
        global $DIC;

        $ilCtrl = $DIC['ilCtrl'];
        $ilUser = $DIC['ilUser'];

        $this->object->setLatitude(ilUtil::stripSlashes($_POST["location"]["latitude"]));
        $this->object->setLongitude(ilUtil::stripSlashes($_POST["location"]["longitude"]));
        $this->object->setLocationZoom(ilUtil::stripSlashes($_POST["location"]["zoom"]));
        $this->object->setEnableGroupMap(ilUtil::stripSlashes($_POST["enable_map"]));
        $this->object->update();
        
        $ilCtrl->redirect($this, "editMapSettings");
    }
    
    
    /**
     * edit info
     *
     * @access public
     * @return
     */
    public function editInfoObject()
    {
        global $DIC;

        $ilErr = $DIC['ilErr'];
        $ilAccess = $DIC['ilAccess'];

        $this->checkPermission('write');
        
        $this->setSubTabs('settings');
        $this->tabs_gui->setTabActive('settings');
        $this->tabs_gui->setSubTabActive('grp_info_settings');
        
        $form = $this->initInfoEditor();
        $this->tpl->setContent($form->getHTML());
    }
    
    /**
     * init info editor
     *
     * @access protected
     * @return
     */
    protected function initInfoEditor()
    {
        include_once("./Services/Form/classes/class.ilPropertyFormGUI.php");
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
    
    /**
     * update info
     *
     * @access public
     * @return
     */
    public function updateInfoObject()
    {
        $this->checkPermission('manage_members');
        
        $this->object->setInformation(ilUtil::stripSlashes($_POST['important']));
        $this->object->update();
        
        ilUtil::sendSuccess($this->lng->txt("settings_saved"));
        $this->editInfoObject();
        return true;
    }
    
    /////////////////////////////////////////////////////////// Member section /////////////////////
    public function readMemberData($ids, $selected_columns = null)
    {
        include_once('./Services/PrivacySecurity/classes/class.ilPrivacySettings.php');
        $privacy = ilPrivacySettings::_getInstance();
        
        include_once './Services/Tracking/classes/class.ilObjUserTracking.php';
        $this->show_tracking =
            (
                ilObjUserTracking::_enabledLearningProgress() and
            ilObjUserTracking::_enabledUserRelatedData()
            );
        if ($this->show_tracking) {
            include_once('./Services/Object/classes/class.ilObjectLP.php');
            $olp = ilObjectLP::getInstance($this->object->getId());
            $this->show_tracking = $olp->isActive();
        }
        
        if ($this->show_tracking) {
            include_once 'Services/Tracking/classes/class.ilLPStatusWrapper.php';
            $completed = ilLPStatusWrapper::_lookupCompletedForObject($this->object->getId());
            $in_progress = ilLPStatusWrapper::_lookupInProgressForObject($this->object->getId());
            $failed = ilLPStatusWrapper::_lookupFailedForObject($this->object->getId());
        }
        
        if ($privacy->enabledGroupAccessTimes()) {
            include_once('./Services/Tracking/classes/class.ilLearningProgress.php');
            $progress = ilLearningProgress::_lookupProgressByObjId($this->object->getId());
        }
        
        $do_prtf = (is_array($selected_columns) &&
            in_array('prtf', $selected_columns) &&
            is_array($ids));
        if ($do_prtf) {
            include_once "Modules/Portfolio/classes/class.ilObjPortfolio.php";
            $all_prtf = ilObjPortfolio::getAvailablePortfolioLinksForUserIds(
                $ids,
                $this->ctrl->getLinkTarget($this, "members")
            );
        }

        /**
         * This reads out all fields in usr_data, including usr_id, firstname,
         * lastname, and login, so should never be necessary here to call
         * ilObjUser a second time (#31394).
         */
        $profile_data = ilObjUser::_readUsersProfileData($ids);
        foreach ($ids as $usr_id) {
            $tmp_data['notification'] = $this->object->members_obj->isNotificationEnabled($usr_id) ? 1 : 0;
            $tmp_data['contact'] = $this->object->members_obj->isContact($usr_id) ? 1 : 0;

            foreach ((array) $profile_data[$usr_id] as $field => $value) {
                $tmp_data[$field] = $value;
            }

            if ($this->show_tracking) {
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
                if (isset($progress[$usr_id]['ts']) and $progress[$usr_id]['ts']) {
                    $tmp_data['access_time'] = ilDatePresentation::formatDate(
                        $tmp_date = new ilDateTime($progress[$usr_id]['ts'], IL_CAL_UNIX)
                    );
                    $tmp_data['access_time_unix'] = $tmp_date->get(IL_CAL_UNIX);
                } else {
                    $tmp_data['access_time'] = $this->lng->txt('no_date');
                    $tmp_data['access_time_unix'] = 0;
                }
            }
            
            if ($do_prtf) {
                $tmp_data['prtf'] = $all_prtf[$usr_id];
            }

            $members[$usr_id] = $tmp_data;
        }
        return $members ? $members : array();
    }
    
    /**
    * leave Group
    * @access public
    */
    public function leaveObject()
    {
        global $DIC;

        $ilUser = $DIC['ilUser'];
        
        $this->checkPermission('leave');
        
        $part = ilGroupParticipants::_getInstanceByObjId($this->object->getId());
        if ($part->isLastAdmin($ilUser->getId())) {
            ilUtil::sendFailure($this->lng->txt('grp_err_administrator_required'));
            $this->viewObject();
            return false;
        }
        
        $this->tabs_gui->setTabActive('grp_btn_unsubscribe');
        
        include_once "Services/Utilities/classes/class.ilConfirmationGUI.php";
        $cgui = new ilConfirmationGUI();
        $cgui->setHeaderText($this->lng->txt('grp_dismiss_myself'));
        $cgui->setFormAction($this->ctrl->getFormAction($this));
        $cgui->setCancel($this->lng->txt("cancel"), "cancel");
        $cgui->setConfirm($this->lng->txt("grp_btn_unsubscribe"), "unsubscribe");
        $this->tpl->setContent($cgui->getHTML());
    }
    
    /**
     * unsubscribe from group
     *
     * @access public
     * @return
     */
    public function unsubscribeObject()
    {
        global $DIC;

        $ilUser = $DIC['ilUser'];
        $tree = $DIC['tree'];
        $ilCtrl = $DIC['ilCtrl'];
        
        $this->checkPermission('leave');
        
        $this->object->members_obj->delete($ilUser->getId());
        
        include_once './Modules/Group/classes/class.ilGroupMembershipMailNotification.php';
        $this->object->members_obj->sendNotification(
            ilGroupMembershipMailNotification::TYPE_UNSUBSCRIBE_MEMBER,
            $ilUser->getId()
        );
        $this->object->members_obj->sendNotification(
            ilGroupMembershipMailNotification::TYPE_NOTIFICATION_UNSUBSCRIBE,
            $ilUser->getId()
        );
        
        ilUtil::sendSuccess($this->lng->txt('grp_msg_membership_annulled'), true);
        $ilCtrl->setParameterByClass(
            "ilrepositorygui",
            "ref_id",
            $tree->getParentId($this->object->getRefId())
        );
        $ilCtrl->redirectByClass("ilrepositorygui", "");
    }

    /**
     * Add content tab
     *
     * @param
     * @return
     */
    public function addContentTab()
    {
        $this->tabs_gui->addTab(
            "view_content",
            $this->lng->txt("content"),
            $this->ctrl->getLinkTarget($this, "view")
        );
    }
    
    
    // get tabs
    public function getTabs()
    {
        global $DIC;

        $rbacsystem = $DIC['rbacsystem'];
        $ilUser = $DIC['ilUser'];
        $ilAccess = $DIC['ilAccess'];
        $lng = $DIC['lng'];
        $ilHelp = $DIC['ilHelp'];
        
        $ilHelp->setScreenIdComponent("grp");

        if ($ilAccess->checkAccess('read', '', $this->ref_id)) {
            if ($this->object->isNewsTimelineEffective()) {
                if (!$this->object->isNewsTimelineLandingPageEffective()) {
                    $this->addContentTab();
                }
                $this->tabs_gui->addTab(
                    "news_timeline",
                    $lng->txt("cont_news_timeline_tab"),
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
            $ilAccess->checkAccess('visible', '', $this->ref_id) ||
            $ilAccess->checkAccess('join', '', $this->ref_id) ||
            $ilAccess->checkAccess('read', '', $this->ref_id)
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


        if ($ilAccess->checkAccess('write', '', $this->object->getRefId())) {
            $this->tabs_gui->addTarget(
                "settings",
                $this->ctrl->getLinkTarget($this, "edit"),
                array("edit", "editMapSettings"),
                get_class($this),
                ""
            );
        }

        include_once './Modules/Group/classes/class.ilGroupParticipants.php';
        $is_participant = ilGroupParticipants::_isParticipant($this->ref_id, $ilUser->getId());
            
        // Members
        include_once './Modules/Group/classes/class.ilGroupMembershipGUI.php';
        $membership_gui = new ilGroupMembershipGUI($this, $this->object);
        $membership_gui->addMemberTab($this->tabs_gui, $is_participant);
        
        
        // badges
        if ($ilAccess->checkAccess('write', '', $this->ref_id)) {
            include_once 'Services/Badge/classes/class.ilBadgeHandler.php';
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
        include_once("./Services/Object/classes/class.ilObjectServiceSettingsGUI.php");
        if ($ilAccess->checkAccess('read', '', $this->ref_id) && ilContainer::_lookupContainerSetting(
            $this->object->getId(),
            ilObjectServiceSettingsGUI::SKILLS,
            false
        )) {
            $this->tabs_gui->addTarget(
                "obj_tool_setting_skills",
                $this->ctrl->getLinkTargetByClass(array("ilcontainerskillgui", "ilcontskillpresentationgui"), ""),
                "",
                array("ilcontainerskillgui", "ilcontskillpresentationgui", "ilcontskilladmingui")
            );
        }

        // learning progress
        include_once './Services/Tracking/classes/class.ilLearningProgressAccess.php';
        if (ilLearningProgressAccess::checkAccess($this->object->getRefId(), $is_participant)) {
            $this->tabs_gui->addTarget(
                'learning_progress',
                $this->ctrl->getLinkTargetByClass(array('ilobjgroupgui','illearningprogressgui'), ''),
                '',
                array('illplistofobjectsgui','illplistofsettingsgui','illearningprogressgui','illplistofprogressgui')
            );
        }

        // meta data
        if ($ilAccess->checkAccess('write', '', $this->ref_id)) {
            $md_gui = new ilObjectMetaDataGUI($this->object);
            $tab_link = $md_gui->getTab();
            if ($tab_link !== null) {
                $this->tabs_gui->addTab(
                    'meta_data',
                    $this->lng->txt('meta_data'),
                    $tab_link,
                    '',
                    'ilObjectMetaDataGUI'
                );
            }
        }


        if ($ilAccess->checkAccess('write', '', $this->object->getRefId())) {
            $this->tabs_gui->addTarget(
                'export',
                $this->ctrl->getLinkTargetByClass('ilexportgui', ''),
                'export',
                'ilexportgui'
            );
        }

        // parent tabs (all container: edit_permission, clipboard, trash
        parent::getTabs();

        if ($ilAccess->checkAccess('join', '', $this->object->getRefId()) and
            !$this->object->members_obj->isAssigned($ilUser->getId())) {
            include_once './Modules/Group/classes/class.ilGroupWaitingList.php';
            if (ilGroupWaitingList::_isOnList($ilUser->getId(), $this->object->getId())) {
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
        if ($ilAccess->checkAccess('leave', '', $this->object->getRefId()) and
            $this->object->members_obj->isMember($ilUser->getId())) {
            $this->tabs_gui->addTarget(
                "grp_btn_unsubscribe",
                $this->ctrl->getLinkTarget($this, "leave"),
                '',
                ""
            );
        }
    }

    /**
    * this one is called from the info button in the repository
    * not very nice to set cmdClass/Cmd manually, if everything
    * works through ilCtrl in the future this may be changed
    */
    public function infoScreenObject()
    {
        $this->ctrl->setCmd("showSummary");
        $this->ctrl->setCmdClass("ilinfoscreengui");
        $this->infoScreen();
    }
    
    /**
    * show information screen
    */
    public function infoScreen()
    {
        global $DIC;

        $rbacsystem = $DIC['rbacsystem'];
        $ilUser = $DIC['ilUser'];
        $ilSetting = $DIC['ilSetting'];
        
        $this->tabs_gui->setTabActive('info_short');

        if (!$this->checkPermissionBool('read')) {
            $this->checkPermission('visible');
        }

        ilMDUtils::_fillHTMLMetaTags(
            $this->object->getId(),
            $this->object->getId(),
            'grp'
        );

        include_once("./Services/InfoScreen/classes/class.ilInfoScreenGUI.php");
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
                case GRP_REGISTRATION_DIRECT:
                    $info->addProperty(
                        $this->lng->txt('group_registration_mode'),
                        $this->lng->txt('grp_reg_direct_info_screen')
                    );
                    break;
                                                       
                case GRP_REGISTRATION_REQUEST:
                    $info->addProperty(
                        $this->lng->txt('group_registration_mode'),
                        $this->lng->txt('grp_reg_req_info_screen')
                    );
                    break;
    
                case GRP_REGISTRATION_PASSWORD:
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
                        $this->object->getMinMembers()
                    );
                }
                if ($this->object->getMaxMembers()) {
                    include_once './Modules/Group/classes/class.ilObjGroupAccess.php';
                    $reg_info = ilObjGroupAccess::lookupRegistrationInfo($this->object->getId());

                    $info->addProperty(
                        $this->lng->txt('mem_free_places'),
                        $reg_info['reg_info_free_places']
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
        include_once('Services/PrivacySecurity/classes/class.ilPrivacySettings.php');
        $privacy = ilPrivacySettings::_getInstance();
        
        include_once('Modules/Course/classes/Export/class.ilCourseDefinedFieldDefinition.php');
        if ($privacy->groupConfirmationRequired() or ilCourseDefinedFieldDefinition::_getFields($this->object->getId()) or $privacy->enabledGroupExport()) {
            include_once('Services/PrivacySecurity/classes/class.ilExportFieldsInfo.php');
            
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

    /**
     * :TEMP: Save notification setting (from infoscreen)
     */
    public function saveNotificationObject()
    {
        include_once "Services/Membership/classes/class.ilMembershipNotifications.php";
        $noti = new ilMembershipNotifications($this->ref_id);
        if ($noti->canCurrentUserEdit()) {
            if ((bool) $_REQUEST["grp_ntf"]) {
                $noti->activateUser();
            } else {
                $noti->deactivateUser();
            }
        }
        ilUtil::sendSuccess($this->lng->txt("settings_saved"), true);
        $this->ctrl->redirect($this, "");
    }
    
    /**
     * Called from goto?
     */
    protected function membersObject()
    {
        $GLOBALS['DIC']['ilCtrl']->redirectByClass('ilgroupmembershipgui');
    }
    

    /**
     * goto target group
     */
    public static function _goto($a_target, $a_add = "")
    {
        global $DIC;

        $ilAccess = $DIC['ilAccess'];
        $ilErr = $DIC['ilErr'];
        $lng = $DIC['lng'];
        $ilUser = $DIC['ilUser'];

        include_once './Services/Membership/classes/class.ilMembershipRegistrationCodeUtils.php';
        if (substr($a_add, 0, 5) == 'rcode') {
            if ($ilUser->getId() == ANONYMOUS_USER_ID) {
                // Redirect to login for anonymous
                ilUtil::redirect(
                    "login.php?target=" . $_GET["target"] . "&cmd=force_login&lang=" .
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
                    ilUtil::sendFailure(sprintf(
                        $lng->txt("msg_no_perm_read_item"),
                        ilObject::_lookupTitle(ilObject::_lookupObjId($a_target))
                    ), true);
                    ilObjectGUI::_gotoRepositoryRoot();
                }
            }
        }
        $ilErr->raiseError($lng->txt("msg_no_perm_read"), $ilErr->FATAL);
    }

    
    /**
     * init create/edit form
     *
     * @access protected
     * @param string edit or create
     * @return
     */
    public function initForm($a_mode = 'edit', $a_omit_form_action = false)
    {
        global $DIC;

        $obj_service = $this->getObjectService();

        $tree = $DIC['tree'];
        
        include_once("./Services/Form/classes/class.ilPropertyFormGUI.php");
        
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
            $reg_type->setValue($this->object->getRegistrationType());

            $opt_dir = new ilRadioOption($this->lng->txt('grp_reg_direct'), GRP_REGISTRATION_DIRECT);#$this->lng->txt('grp_reg_direct_info'));
            $reg_type->addOption($opt_dir);

            $opt_pass = new ilRadioOption($this->lng->txt('grp_pass_request'), GRP_REGISTRATION_PASSWORD);
            $pass = new ilTextInputGUI($this->lng->txt("password"), 'password');
            $pass->setRequired(true);
            $pass->setInfo($this->lng->txt('grp_reg_password_info'));
            $pass->setValue($this->object->getPassword());
            $pass->setSize(32);
            $pass->setMaxLength(32);
            $opt_pass->addSubItem($pass);
            $reg_type->addOption($opt_pass);

            $opt_req = new ilRadioOption($this->lng->txt('grp_reg_request'), GRP_REGISTRATION_REQUEST, $this->lng->txt('grp_reg_request_info'));
            $reg_type->addOption($opt_req);

            $opt_deact = new ilRadioOption($this->lng->txt('grp_reg_no_selfreg'), GRP_REGISTRATION_DEACTIVATED, $this->lng->txt('grp_reg_disabled_info'));
            $reg_type->addOption($opt_deact);

            // Registration codes
            $reg_code = new ilCheckboxInputGUI($this->lng->txt('grp_reg_code'), 'reg_code_enabled');
            $reg_code->setChecked($this->object->isRegistrationAccessCodeEnabled());
            $reg_code->setValue(1);
            $reg_code->setInfo($this->lng->txt('grp_reg_code_enabled_info'));
            $form->addItem($reg_type);

            // Registration codes
            if (!$this->object->getRegistrationAccessCode()) {
                include_once './Services/Membership/classes/class.ilMembershipRegistrationCodeUtils.php';
                $this->object->setRegistrationAccessCode(ilMembershipRegistrationCodeUtils::generateCode());
            }
            $reg_link = new ilHiddenInputGUI('reg_code');
            $reg_link->setValue($this->object->getRegistrationAccessCode());
            $form->addItem($reg_link);

            $link = new ilCustomInputGUI($this->lng->txt('grp_reg_code_link'));
            include_once './Services/Link/classes/class.ilLink.php';
            $val = ilLink::_getLink($this->object->getRefId(), $this->object->getType(), array(), '_rcode' . $this->object->getRegistrationAccessCode());
            $link->setHTML('<span class="small">' . $val . '</span>');
            $reg_code->addSubItem($link);
            $form->addItem($reg_code);

            // time limit
            $this->lng->loadLanguageModule('dateplaner');
            include_once './Services/Form/classes/class.ilDateDurationInputGUI.php';
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
            $lim->setValue(1);
            //			$lim->setOptionTitle($this->lng->txt('reg_grp_max_members'));
            $lim->setChecked($this->object->isMembershipLimited());

            $min = new ilTextInputGUI($this->lng->txt('reg_grp_min_members'), 'registration_min_members');
            $min->setSize(3);
            $min->setMaxLength(4);
            $min->setValue($this->object->getMinMembers() ? $this->object->getMinMembers() : '');
            $min->setInfo($this->lng->txt('grp_subscription_min_members_info'));
            $lim->addSubItem($min);

            $max = new ilTextInputGUI($this->lng->txt('reg_grp_max_members'), 'registration_max_members');
            $max->setValue($this->object->getMaxMembers() ? $this->object->getMaxMembers() : '');
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
            
            $option = new ilRadioOption($this->lng->txt('none'), 0);
            $wait->addOption($option);
            
            $option = new ilRadioOption($this->lng->txt('grp_waiting_list_no_autofill'), 1);
            $option->setInfo($this->lng->txt('grp_waiting_list_info'));
            $wait->addOption($option);
            
            $option = new ilRadioOption($this->lng->txt('grp_waiting_list_autofill'), 2);
            $option->setInfo($this->lng->txt('grp_waiting_list_autofill_info'));
            $wait->addOption($option);
            
            if ($this->object->hasWaitingListAutoFill()) {
                $wait->setValue(2);
            } elseif ($this->object->isWaitingListEnabled()) {
                $wait->setValue(1);
            }
            
            $lim->addSubItem($wait);
            
            $form->addItem($lim);
            

            // Group presentation
            $parent_membership_ref_id = 0;
            $hasParentMembership =
                (
                    $parent_membership_ref_id = $tree->checkForParentType($this->object->getRefId(), 'crs', true)
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
                    $opt = new ilRadioOption($this->lng->txt('grp_view_inherit') . $course_view_mode, ilContainer::VIEW_INHERIT);
                    $opt->setInfo($this->lng->txt('grp_view_inherit_info'));
                    $view_type->addOption($opt);
                }
            }

            if ($hasParentMembership && ilObjGroup::lookupViewMode($this->object->getId()) == ilContainer::VIEW_INHERIT) {
                $view_type->setValue(ilContainer::VIEW_INHERIT);
            } else {
                $view_type->setValue(ilObjGroup::lookupViewMode($this->object->getId()));
            }

            $opt = new ilRadioOption($this->lng->txt('cntr_view_sessions'), ilContainer::VIEW_SESSIONS);
            $opt->setInfo($this->lng->txt('cntr_view_info_sessions'));
            $view_type->addOption($opt);
            
            // Limited sessions
            $this->lng->loadLanguageModule('crs');
            $sess = new ilCheckboxInputGUI($this->lng->txt('sess_limit'), 'sl');
            $sess->setValue(1);
            $sess->setChecked($this->object->isSessionLimitEnabled());
            $sess->setInfo($this->lng->txt('sess_limit_info'));

            $prev = new ilNumberInputGUI($this->lng->txt('sess_num_prev'), 'sp');
            $prev->setMinValue(0);
            $prev->setValue(
                $this->object->getNumberOfPreviousSessions() == -1 ?
                    '' :
                    $this->object->getNumberOfPreviousSessions()
            );
            $prev->setSize(2);
            $prev->setMaxLength(3);
            $sess->addSubItem($prev);

            $next = new ilNumberInputGUI($this->lng->txt('sess_num_next'), 'sn');
            $next->setMinValue(0);
            $next->setValue(
                $this->object->getNumberOfNextSessions() == -1 ?
                    '' :
                    $this->object->getNumberOfNextSessions()
            );
            $next->setSize(2);
            $next->setMaxLength(3);
            $sess->addSubItem($next);
            $opt->addSubItem($sess);

            $opt = new ilRadioOption($this->lng->txt('cntr_view_simple'), ilContainer::VIEW_SIMPLE);
            $opt->setInfo($this->lng->txt('grp_view_info_simple'));
            $view_type->addOption($opt);
            
            $opt = new ilRadioOption($this->lng->txt('cntr_view_by_type'), ilContainer::VIEW_BY_TYPE);
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

            include_once './Services/Object/classes/class.ilObjectServiceSettingsGUI.php';
            ilObjectServiceSettingsGUI::initServiceSettingsForm(
                $this->object->getId(),
                $form,
                $this->getSubServices()
            );


            $mem = new ilCheckboxInputGUI($this->lng->txt('grp_show_members'), 'show_members');
            $mem->setChecked($this->object->getShowMembers());
            $mem->setInfo($this->lng->txt('grp_show_members_info'));
            $form->addItem($mem);

            // Show members type
            $mail_type = new ilRadioGroupInputGUI($this->lng->txt('grp_mail_type'), 'mail_type');
            $mail_type->setValue($this->object->getMailToMembersType());

            $mail_tutors = new ilRadioOption(
                $this->lng->txt('grp_mail_tutors_only'),
                ilObjGroup::MAIL_ALLOWED_TUTORS,
                $this->lng->txt('grp_mail_tutors_only_info')
            );
            $mail_type->addOption($mail_tutors);

            $mail_all = new ilRadioOption(
                $this->lng->txt('grp_mail_all'),
                ilObjGroup::MAIL_ALLOWED_ALL,
                $this->lng->txt('grp_mail_all_info')
            );
            $mail_type->addOption($mail_all);
            $form->addItem($mail_type);

            // Self notification
            $not = new ilCheckboxInputGUI($this->lng->txt('grp_auto_notification'), 'auto_notification');
            $not->setValue(1);
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
                include_once 'Modules/Group/classes/class.ilECSGroupSettings.php';
                $ecs = new ilECSGroupSettings($this->object);
                $ecs->addSettingsToForm($form, 'grp');
            
                $form->addCommandButton('update', $this->lng->txt('save'));
                $form->addCommandButton('cancel', $this->lng->txt('cancel'));
                break;
        }
        return $form;
    }

    /**
     * set sub tabs
     *
     * @access protected
     * @param
     * @return
     */
    protected function setSubTabs($a_tab)
    {
        global $DIC;

        $rbacsystem = $DIC['rbacsystem'];
        $ilUser = $DIC['ilUser'];
        $ilAccess = $DIC['ilAccess'];
    
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

                include_once("./Services/Maps/classes/class.ilMapUtil.php");
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

                include_once('Services/PrivacySecurity/classes/class.ilPrivacySettings.php');
                include_once('Modules/Course/classes/Export/class.ilCourseDefinedFieldDefinition.php');
                // only show if export permission is granted
                if (ilPrivacySettings::_getInstance()->checkExportAccess($this->object->getRefId()) or ilCourseDefinedFieldDefinition::_hasFields($this->object->getId())) {
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
    
    /**
     * Check agreement and redirect if it is not accepted
     *
     * @access private
     *
     */
    private function checkAgreement()
    {
        global $DIC;

        $ilUser = $DIC['ilUser'];
        $ilAccess = $DIC['ilAccess'];
        
        if ($ilAccess->checkAccess('write', '', $this->object->getRefId())) {
            return true;
        }
        
        // Disable aggrement if is not member of group
        if (!$this->object->members_obj->isAssigned($ilUser->getId())) {
            return true;
        }
        
        include_once './Services/Container/classes/class.ilMemberViewSettings.php';
        if (ilMemberViewSettings::getInstance()->isActive()) {
            return true;
        }
        
        include_once('Services/PrivacySecurity/classes/class.ilPrivacySettings.php');
        include_once('Services/Membership/classes/class.ilMemberAgreement.php');
        $privacy = ilPrivacySettings::_getInstance();
        
        // Check agreement
        if (($privacy->groupConfirmationRequired() or ilCourseDefinedFieldDefinition::_hasFields($this->object->getId()))
            and !ilMemberAgreement::_hasAccepted($ilUser->getId(), $this->object->getId())) {
            return false;
        }
        // Check required fields
        include_once('Modules/Course/classes/Export/class.ilCourseUserData.php');
        if (!ilCourseUserData::_checkRequired($ilUser->getId(), $this->object->getId())) {
            return false;
        }
        return true;
    }
    
    
    /**
     * Handle member view
     * @return
     */
    public function prepareOutput($a_show_subobjects = true)
    {
        global $DIC;

        $rbacsystem = $DIC['rbacsystem'];
        if (!$this->getCreationMode()) {
            /*
            include_once './Services/Container/classes/class.ilMemberViewSettings.php';
            $settings = ilMemberViewSettings::getInstance();
            if($settings->isActive() and $settings->getContainer() != $this->object->getRefId())
            {
                $settings->setContainer($this->object->getRefId());
                $rbacsystem->initMemberView();
            }
            */
        }
        parent::prepareOutput($a_show_subobjects);
    }
    
    /**
     * Create a course mail signature
     * @return string
     */
    public function createMailSignature()
    {
        $link = chr(13) . chr(10) . chr(13) . chr(10);
        $link .= $this->lng->txt('grp_mail_permanent_link');
        $link .= chr(13) . chr(10) . chr(13) . chr(10);
        include_once 'Services/Link/classes/class.ilLink.php';
        $link .= ilLink::_getLink($this->object->getRefId());
        return rawurlencode(base64_encode($link));
    }
    
    protected function initHeaderAction($a_sub_type = null, $a_sub_id = null)
    {
        global $DIC;

        $ilSetting = $DIC['ilSetting'];
        $ilUser = $DIC['ilUser'];
        
        $lg = parent::initHeaderAction($a_sub_type, $a_sub_id);
                
        include_once('./Modules/Group/classes/class.ilGroupParticipants.php');
        if (ilGroupParticipants::_isParticipant($this->ref_id, $ilUser->getId())) {
            include_once "Services/Membership/classes/class.ilMembershipNotifications.php";
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
    
    
    /**
     *
     * @param array $a_data
     */
    public function addCustomData($a_data)
    {
        // object defined fields
        include_once('Modules/Course/classes/Export/class.ilCourseUserData.php');
        $odfs = ilCourseUserData::_getValuesByObjId($this->object->getId());
        
        $res_data = array();
        foreach ($a_data as $usr_id => $user_data) {
            $res_data[$usr_id] = $user_data;
            
            // udf
            include_once './Services/User/classes/class.ilUserDefinedData.php';
            $udf_data = new ilUserDefinedData($usr_id);
            foreach ($udf_data->getAll() as $field => $value) {
                list($f, $field_id) = explode('_', $field);
                $res_data[$usr_id]['udf_' . $field_id] = (string) $value;
            }
                
            foreach ((array) $odfs[$usr_id] as $cdf_field => $cdf_value) {
                $res_data[$usr_id]['cdf_' . $cdf_field] = (string) $cdf_value;
            }
        }
        
        return $res_data;
    }

    /**
     * returns all local roles [role_id] => title
     * @return array
     */
    public function getLocalRoles()
    {
        $local_roles = $this->object->getLocalGroupRoles(false);
        $grp_member = $this->object->getDefaultMemberRole();
        $grp_roles = array();

        //put the group member role to the top of the crs_roles array
        if (in_array($grp_member, $local_roles)) {
            $grp_roles[$grp_member] = ilObjRole::_getTranslation(array_search($grp_member, $local_roles));
            unset($local_roles[$grp_roles[$grp_member]]);
        }

        foreach ($local_roles as $title => $role_id) {
            $grp_roles[$role_id] = ilObjRole::_getTranslation($title);
        }
        return $grp_roles;
    }

    /**
     *
     */
    protected function jump2UsersGalleryObject()
    {
        $this->ctrl->redirectByClass('ilUsersGalleryGUI');
    }

    /**
     * Set return point for side column actions
     */
    public function setSideColumnReturn()
    {
        $this->ctrl->setReturn($this, "view");
    }
} // END class.ilObjGroupGUI
