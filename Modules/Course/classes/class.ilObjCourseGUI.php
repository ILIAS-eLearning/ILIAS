<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */


require_once "./Services/Container/classes/class.ilContainerGUI.php";

/**
 * Class ilObjCourseGUI
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 * $Id$
 *
 * @ilCtrl_Calls ilObjCourseGUI: ilCourseRegistrationGUI, ilCourseObjectivesGUI
 * @ilCtrl_Calls ilObjCourseGUI: ilObjCourseGroupingGUI, ilInfoScreenGUI, ilLearningProgressGUI, ilPermissionGUI
 * @ilCtrl_Calls ilObjCourseGUI: ilRepositorySearchGUI, ilConditionHandlerGUI
 * @ilCtrl_Calls ilObjCourseGUI: ilCourseContentGUI, ilPublicUserProfileGUI, ilMemberExportGUI
 * @ilCtrl_Calls ilObjCourseGUI: ilObjectCustomUserFieldsGUI, ilMemberAgreementGUI, ilSessionOverviewGUI
 * @ilCtrl_Calls ilObjCourseGUI: ilColumnGUI, ilContainerPageGUI
 * @ilCtrl_Calls ilObjCourseGUI: ilObjectCopyGUI, ilObjStyleSheetGUI
 * @ilCtrl_Calls ilObjCourseGUI: ilCourseParticipantsGroupsGUI, ilExportGUI, ilCommonActionDispatcherGUI
 * @ilCtrl_Calls ilObjCourseGUI: ilDidacticTemplateGUI, ilCertificateGUI, ilObjectServiceSettingsGUI
 * @ilCtrl_Calls ilObjCourseGUI: ilContainerStartObjectsGUI, ilContainerStartObjectsPageGUI
 * @ilCtrl_Calls ilObjCourseGUI: ilMailMemberSearchGUI, ilBadgeManagementGUI
 * @ilCtrl_Calls ilObjCourseGUI: ilLOPageGUI, ilObjectMetaDataGUI, ilNewsTimelineGUI, ilContainerNewsSettingsGUI
 * @ilCtrl_Calls ilObjCourseGUI: ilCourseMembershipGUI, ilPropertyFormGUI, ilContainerSkillGUI, ilCalendarPresentationGUI
 * @ilCtrl_Calls ilObjCourseGUI: ilMemberExportSettingsGUI
 * @ilCtrl_Calls ilObjCourseGUI: ilLTIProviderObjectSettingGUI, ilObjectTranslationGUI, ilBookingGatewayGUI, ilRepUtilGUI
 *
 * @extends ilContainerGUI
 */
class ilObjCourseGUI extends ilContainerGUI
{
    const BREADCRUMB_DEFAULT = 0;
    const BREADCRUMB_CRS_ONLY = 1;
    const BREADCRUMB_FULL_PATH = 2;
    /**
     * @var ilNewsService
     */
    protected $news;

    /**
     * Constructor
     * @access public
     */
    public function __construct()
    {
        global $DIC;

        $ilCtrl = $DIC['ilCtrl'];
        $ilHelp = $DIC['ilHelp'];

        // CONTROL OPTIONS
        $this->ctrl = $ilCtrl;
        $this->ctrl->saveParameter($this, array("ref_id","cmdClass"));

        $this->type = "crs";
        parent::__construct('', (int) $_GET['ref_id'], true, false);

        $this->lng->loadLanguageModule('crs');
        $this->lng->loadLanguageModule('cert');
        $this->lng->loadLanguageModule('obj');

        $this->SEARCH_USER = 1;
        $this->SEARCH_GROUP = 2;
        $this->SEARCH_COURSE = 3;
        $this->news = $DIC->news();
    }

    public function gatewayObject()
    {
        switch ($_POST["action"]) {

            case "deleteSubscribers":
                $this->deleteSubscribers();
                break;

            case "addSubscribers":
                $this->addSubscribers();
                break;

            case "addFromWaitingList":
                $this->addFromWaitingList();
                break;

            case "removeFromWaitingList":
                $this->removeFromWaitingList();
                break;

            default:
                $this->viewObject();
                break;
        }
        return true;
    }

    /**
     * add course admin after import file
     * @return
     */
    protected function afterImport(ilObject $a_new_object)
    {
        global $DIC;

        $ilUser = $DIC['ilUser'];
        $ilSetting = $DIC['ilSetting'];

        // #11895
        include_once './Modules/Course/classes/class.ilCourseParticipants.php';
        $part = ilCourseParticipants::_getInstanceByObjId($a_new_object->getId());
        $part->add($ilUser->getId(), ilCourseConstants::CRS_ADMIN);
        $part->updateNotification($ilUser->getId(), $ilSetting->get('mail_crs_admin_notification', true));

        parent::afterImport($a_new_object);
    }

    public function renderObject()
    {
        $this->ctrl->setCmd("view");
        $this->viewObject();
    }

    public function viewObject()
    {
        global $DIC;

        $rbacsystem = $DIC['rbacsystem'];
        $ilUser = $DIC['ilUser'];
        $ilCtrl = $DIC['ilCtrl'];

        $this->tabs_gui->setTabActive('view_content');

        // CHECK ACCESS
        $this->checkPermission('read', 'view');
        /*
        if(!$rbacsystem->checkAccess("read",$this->object->getRefId()))
        {
            $this->ilias->raiseError($this->lng->txt("msg_no_perm_read"),$this->ilias->error_obj->MESSAGE);
        }
        */
        if (strtolower($_GET["baseClass"]) == "iladministrationgui") {
            parent::viewObject();
            return true;
        }

        // Fill meta header tags
        include_once('Services/MetaData/classes/class.ilMDUtils.php');
        ilMDUtils::_fillHTMLMetaTags($this->object->getId(), $this->object->getId(), 'crs');

        // Trac access
        if ($ilCtrl->getNextClass() != "ilcolumngui") {
            include_once 'Services/Tracking/classes/class.ilLearningProgress.php';
            ilLearningProgress::_tracProgress(
                $ilUser->getId(),
                $this->object->getId(),
                $this->object->getRefId(),
                'crs'
            );
        }

        if (!$this->checkAgreement()) {
            include_once('Services/Membership/classes/class.ilMemberAgreementGUI.php');
            $this->tabs_gui->clearTargets();
            $this->ctrl->setReturn($this, 'view_content');
            $agreement = new ilMemberAgreementGUI($this->object->getRefId());
            $this->ctrl->setCmdClass(get_class($agreement));
            $this->ctrl->forwardCommand($agreement);
            return true;
        }

        if (!$this->__checkStartObjects()) {
            include_once "Services/Container/classes/class.ilContainerStartObjectsContentGUI.php";
            $stgui = new ilContainerStartObjectsContentGUI($this, $this->object);
            $stgui->enableDesktop($this->object->getAboStatus(), $this);
            return $stgui->getHTML();
        }

        // views handled by general container logic
        if ($this->object->getViewMode() == ilContainer::VIEW_SIMPLE ||
            $this->object->getViewMode() == ilContainer::VIEW_BY_TYPE ||
            $this->object->getViewMode() == ilContainer::VIEW_SESSIONS ||
            $this->object->getViewMode() == ilContainer::VIEW_TIMING ||
            $this->object->getViewMode() == ilContainer::VIEW_OBJECTIVE
            ) {
            $ret = parent::renderObject();
            return $ret;
        } else {
            include_once './Modules/Course/classes/class.ilCourseContentGUI.php';
            $course_content_obj = new ilCourseContentGUI($this);

            $this->ctrl->setCmdClass(get_class($course_content_obj));
            $this->ctrl->forwardCommand($course_content_obj);
        }

        return true;
    }

    public function renderContainer()
    {
        return parent::renderObject();
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
     * Show info screen
     *
     * @throws \ilDateTimeException
     * @throws \ilObjectException
     * @throws \ilTemplateException
    */
    public function infoScreen()
    {
        global $DIC;

        $ilErr = $DIC['ilErr'];
        $ilAccess = $DIC['ilAccess'];
        $ilUser = $DIC['ilUser'];
        $ilSetting = $DIC['ilSetting'];

        if (!$this->checkPermissionBool('read')) {
            $this->checkPermission('visible');
        }

        // Fill meta header tags
        include_once('Services/MetaData/classes/class.ilMDUtils.php');
        ilMDUtils::_fillHTMLMetaTags($this->object->getId(), $this->object->getId(), 'crs');

        $this->tabs_gui->setTabActive('info_short');

        include_once("./Services/InfoScreen/classes/class.ilInfoScreenGUI.php");
        include_once 'Modules/Course/classes/class.ilCourseFile.php';

        $files = ilCourseFile::_readFilesByCourse($this->object->getId());

        $info = new ilInfoScreenGUI($this);
        $info->enablePrivateNotes();
        $info->enableFeedback();
        $info->enableNews();
        $info->enableBookingInfo(true);
        if ($ilAccess->checkAccess("write", "", $_GET["ref_id"])) {
            $info->enableNewsEditing();
        }

        if (
            strlen($this->object->getImportantInformation()) ||
            strlen($this->object->getSyllabus()) ||
            strlen($this->object->getTargetGroup()) ||
           count($files)) {
            $info->addSection($this->lng->txt('crs_general_informations'));
        }

        if (strlen($this->object->getImportantInformation())) {
            $info->addProperty(
                $this->lng->txt('crs_important_info'),
                "<strong>" . nl2br(
                    ilUtil::makeClickable($this->object->getImportantInformation(), true) . "</strong>"
                )
            );
        }
        if (strlen($this->object->getSyllabus())) {
            $info->addProperty($this->lng->txt('crs_syllabus'), nl2br(
                ilUtil::makeClickable($this->object->getSyllabus(), true)
            ));
        }
        if (strlen($this->object->getTargetGroup())) {
            $info->addProperty(
                $this->lng->txt('crs_target_group'),
                nl2br(
                    \ilUtil::makeClickable($this->object->getTargetGroup(), true)
                )
            );
        }
        // files
        if (count($files)) {
            $tpl = new ilTemplate('tpl.event_info_file.html', true, true, 'Modules/Course');

            foreach ($files as $file) {
                $tpl->setCurrentBlock("files");
                $this->ctrl->setParameter($this, 'file_id', $file->getFileId());
                $tpl->setVariable("DOWN_LINK", $this->ctrl->getLinkTarget($this, 'sendfile'));
                $tpl->setVariable("DOWN_NAME", $file->getFileName());
                $tpl->setVariable("DOWN_INFO_TXT", $this->lng->txt('crs_file_size_info'));
                $tpl->setVariable("DOWN_SIZE", $file->getFileSize());
                $tpl->setVariable("TXT_BYTES", $this->lng->txt('bytes'));
                $tpl->parseCurrentBlock();
            }
            $info->addProperty(
                $this->lng->txt('crs_file_download'),
                $tpl->get()
            );
        }

        include_once('Services/AdvancedMetaData/classes/class.ilAdvancedMDRecordGUI.php');
        $record_gui = new ilAdvancedMDRecordGUI(ilAdvancedMDRecordGUI::MODE_INFO, 'crs', $this->object->getId());
        $record_gui->setInfoObject($info);
        $record_gui->parse();

        // meta data
        $info->addMetaDataSections($this->object->getId(), 0, $this->object->getType());

        // contact
        if ($this->object->hasContactData()) {
            $info->addSection($this->lng->txt("crs_contact"));
        }
        if (strlen($this->object->getContactName())) {
            $info->addProperty(
                $this->lng->txt("crs_contact_name"),
                $this->object->getContactName()
            );
        }
        if (strlen($this->object->getContactResponsibility())) {
            $info->addProperty(
                $this->lng->txt("crs_contact_responsibility"),
                $this->object->getContactResponsibility()
            );
        }
        if (strlen($this->object->getContactPhone())) {
            $info->addProperty(
                $this->lng->txt("crs_contact_phone"),
                $this->object->getContactPhone()
            );
        }
        if ($this->object->getContactEmail()) {
            include_once './Modules/Course/classes/class.ilCourseMailTemplateMemberContext.php';
            require_once 'Services/Mail/classes/class.ilMailFormCall.php';

            $emails = explode(",", $this->object->getContactEmail());
            foreach ($emails as $email) {
                $email = trim($email);
                $etpl = new ilTemplate("tpl.crs_contact_email.html", true, true, 'Modules/Course');
                $etpl->setVariable(
                    "EMAIL_LINK",
                    ilMailFormCall::getLinkTarget(
                        $info,
                        'showSummary',
                        array(),
                        array(
                            'type' => 'new',
                            'rcp_to' => $email,
                            'sig' => $this->createMailSignature()
                        ),
                        array(
                            ilMailFormCall::CONTEXT_KEY => ilCourseMailTemplateMemberContext::ID,
                            'ref_id' => $this->object->getRefId(),
                            'ts' => time()
                        )
                    )
                );
                $etpl->setVariable("CONTACT_EMAIL", $email);
                $mailString .= $etpl->get() . "<br />";
            }
            $info->addProperty($this->lng->txt("crs_contact_email"), $mailString);
        }
        if (strlen($this->object->getContactConsultation())) {
            $info->addProperty(
                $this->lng->txt("crs_contact_consultation"),
                nl2br($this->object->getContactConsultation())
            );
        }


        // support contacts
        $parts = ilParticipants::getInstanceByObjId($this->object->getId());
        $conts = $parts->getContacts();
        if (count($conts) > 0) {
            $info->addSection($this->lng->txt("crs_mem_contacts"));
            foreach ($conts as $c) {
                include_once("./Services/User/classes/class.ilPublicUserProfileGUI.php");
                $pgui = new ilPublicUserProfileGUI($c);
                $pgui->setBackUrl($this->ctrl->getLinkTargetByClass("ilinfoscreengui"));
                $pgui->setEmbedded(true);
                $info->addProperty("", $pgui->getHTML());
            }
        }



        //
        // access
        //

        // #10360
        $info->enableAvailability(false);
        $this->lng->loadLanguageModule("rep");
        $info->addSection($this->lng->txt("rep_activation_availability"));
        $info->showLDAPRoleGroupMappingInfo();

        // activation
        $info->addAccessPeriodProperty();

        switch ($this->object->getSubscriptionLimitationType()) {
            case IL_CRS_SUBSCRIPTION_DEACTIVATED:
                $txt = $this->lng->txt("crs_info_reg_deactivated");
                break;

            default:
                switch ($this->object->getSubscriptionType()) {
                    case IL_CRS_SUBSCRIPTION_CONFIRMATION:
                        $txt = $this->lng->txt("crs_info_reg_confirmation");
                        break;
                    case IL_CRS_SUBSCRIPTION_DIRECT:
                        $txt = $this->lng->txt("crs_info_reg_direct");
                        break;
                    case IL_CRS_SUBSCRIPTION_PASSWORD:
                        $txt = $this->lng->txt("crs_info_reg_password");
                        break;
                }
        }

        // subscription
        $info->addProperty($this->lng->txt("crs_info_reg"), $txt);


        if ($this->object->getSubscriptionLimitationType() != IL_CRS_SUBSCRIPTION_DEACTIVATED) {
            if ($this->object->getSubscriptionUnlimitedStatus()) {
                $info->addProperty(
                    $this->lng->txt("crs_reg_until"),
                    $this->lng->txt('crs_unlimited')
                );
            } elseif ($this->object->getSubscriptionStart() < time()) {
                $info->addProperty(
                    $this->lng->txt("crs_reg_until"),
                    $this->lng->txt('crs_to') . ' ' .
                                   ilDatePresentation::formatDate(new ilDateTime($this->object->getSubscriptionEnd(), IL_CAL_UNIX))
                );
            } elseif ($this->object->getSubscriptionStart() > time()) {
                $info->addProperty(
                    $this->lng->txt("crs_reg_until"),
                    $this->lng->txt('crs_from') . ' ' .
                                   ilDatePresentation::formatDate(new ilDateTime($this->object->getSubscriptionStart(), IL_CAL_UNIX))
                );
            }
            if ($this->object->isSubscriptionMembershipLimited()) {
                if ($this->object->getSubscriptionMinMembers()) {
                    $info->addProperty(
                        $this->lng->txt("mem_min_users"),
                        $this->object->getSubscriptionMinMembers()
                    );
                }
                if ($this->object->getSubscriptionMaxMembers()) {
                    include_once './Modules/Course/classes/class.ilObjCourseAccess.php';
                    $reg_info = ilObjCourseAccess::lookupRegistrationInfo($this->object->getId());

                    $info->addProperty(
                        $this->lng->txt('mem_free_places'),
                        $reg_info['reg_info_free_places']
                    );
                }
            }
        }

        if ($this->object->getCancellationEnd()) {
            $info->addProperty(
                $this->lng->txt('crs_cancellation_end'),
                ilDatePresentation::formatDate($this->object->getCancellationEnd())
            );
        }

        if (
            $this->object->getCourseStart() instanceof ilDateTime &&
            !$this->object->getCourseStart()->isNull()
        ) {
            $info->addProperty(
                $this->lng->txt('crs_period'),
                ilDatePresentation::formatPeriod(
                    $this->object->getCourseStart(),
                    $this->object->getCourseEnd()
                )
            );
        }

        // Confirmation
        include_once('Services/PrivacySecurity/classes/class.ilPrivacySettings.php');
        $privacy = ilPrivacySettings::_getInstance();

        include_once('Modules/Course/classes/Export/class.ilCourseDefinedFieldDefinition.php');
        if ($privacy->courseConfirmationRequired() or ilCourseDefinedFieldDefinition::_getFields($this->object->getId()) or $privacy->enabledCourseExport()) {
            include_once('Services/PrivacySecurity/classes/class.ilExportFieldsInfo.php');

            $field_info = ilExportFieldsInfo::_getInstanceByType($this->object->getType());

            $this->lng->loadLanguageModule('ps');
            $info->addSection($this->lng->txt('crs_user_agreement_info'));
            $info->addProperty($this->lng->txt('ps_export_data'), $field_info->exportableFieldsToInfoString());

            if ($fields = ilCourseDefinedFieldDefinition::_fieldsToInfoString($this->object->getId())) {
                $info->addProperty($this->lng->txt('ps_crs_user_fields'), $fields);
            }
        }

        $info->enableLearningProgress(true);

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
            if ((bool) $_REQUEST["crs_ntf"]) {
                $noti->activateUser();
            } else {
                $noti->deactivateUser();
            }
        }
        ilUtil::sendSuccess($this->lng->txt("settings_saved"), true);
        $this->ctrl->redirect($this, "");
    }

    /**
     * Edit info page informations
     *
     * @access public
     *
     */
    public function editInfoObject(ilPropertyFormGUI $a_form = null)
    {
        include_once 'Modules/Course/classes/class.ilCourseFile.php';

        global $DIC;

        $ilErr = $DIC['ilErr'];
        $ilAccess = $DIC['ilAccess'];

        $this->checkPermission('write');
        /*
        if(!$ilAccess->checkAccess('write','',$this->object->getRefId()))
        {
            $ilErr->raiseError($this->lng->txt('msg_no_perm_read'),$ilErr->MESSAGE);
        }
        */
        $this->setSubTabs('properties');
        $this->tabs_gui->setTabActive('settings');
        $this->tabs_gui->setSubTabActive('crs_info_settings');

        if (!$a_form) {
            $a_form = $this->initInfoEditor();
        }
        $this->tpl->addBlockFile('ADM_CONTENT', 'adm_content', 'tpl.edit_info.html', 'Modules/Course');
        $this->tpl->setVariable('INFO_TABLE', $a_form->getHTML());

        if (!count($files = ilCourseFile::_readFilesByCourse($this->object->getId()))) {
            return true;
        }
        $rows = array();
        foreach ($files as $file) {
            $table_data['id'] = $file->getFileId();
            $table_data['filename'] = $file->getFileName();
            $table_data['filetype'] = $file->getFileType();
            $table_data['filesize'] = $file->getFileSize();

            $rows[] = $table_data;
        }

        include_once("./Modules/Course/classes/class.ilCourseInfoFileTableGUI.php");
        $table_gui = new ilCourseInfoFileTableGUI($this, 'editInfo');
        $table_gui->setTitle($this->lng->txt("crs_info_download"));
        $table_gui->setData($rows);
        $table_gui->addCommandButton("cancel", $this->lng->txt("cancel"));
        $table_gui->addMultiCommand("confirmDeleteInfoFiles", $this->lng->txt("delete"));
        $table_gui->setSelectAllCheckbox("file_id");
        $this->tpl->setVariable('INFO_FILE_TABLE', $table_gui->getHTML());

        return true;
    }

    /**
     * show info file donfimation table
     *
     * @access public
     * @param
     *
     */
    public function confirmDeleteInfoFilesObject()
    {
        if (!count($_POST['file_id'])) {
            ilUtil::sendFailure($this->lng->txt('select_one'));
            $this->editInfoObject();
            return false;
        }

        $this->setSubTabs('properties');
        $this->tabs_gui->setTabActive('settings');
        $this->tabs_gui->setSubTabActive('crs_info_settings');

        include_once("Services/Utilities/classes/class.ilConfirmationGUI.php");
        $c_gui = new ilConfirmationGUI();

        // set confirm/cancel commands
        $c_gui->setFormAction($this->ctrl->getFormAction($this, "deleteInfoFiles"));
        $c_gui->setHeaderText($this->lng->txt("info_delete_sure"));
        $c_gui->setCancel($this->lng->txt("cancel"), "editInfo");
        $c_gui->setConfirm($this->lng->txt("confirm"), "deleteInfoFiles");

        // add items to delete
        include_once('Modules/Course/classes/class.ilCourseFile.php');
        foreach ($_POST["file_id"] as $file_id) {
            $file = new ilCourseFile($file_id);
            $c_gui->addItem("file_id[]", $file_id, $file->getFileName());
        }

        $this->tpl->setContent($c_gui->getHTML());
    }

    /**
     * Delete info files
     *
     * @access public
     *
     */
    public function deleteInfoFilesObject()
    {
        if (!count($_POST['file_id'])) {
            ilUtil::sendFailure($this->lng->txt('select_one'));
            $this->editInfoObject();
            return false;
        }
        include_once('Modules/Course/classes/class.ilCourseFile.php');

        foreach ($_POST['file_id'] as $file_id) {
            $file = new ilCourseFile($file_id);
            if ($this->object->getId() == $file->getCourseId()) {
                $file->delete();
            }
        }
        ilUtil::sendSuccess($this->lng->txt('settings_saved'));
        $this->editInfoObject();
        return true;
    }

    /**
     * init info editor
     *
     * @access public
     * @param
     *
     */
    public function initInfoEditor()
    {
        include_once("./Services/Form/classes/class.ilPropertyFormGUI.php");
        $form = new ilPropertyFormGUI();
        $form->setFormAction($this->ctrl->getFormAction($this, 'updateInfo'));
        $form->setMultipart(true);
        $form->setTitle($this->lng->txt('crs_general_info'));
        $form->addCommandButton('updateInfo', $this->lng->txt('save'));
        $form->addCommandButton('cancel', $this->lng->txt('cancel'));

        $area = new ilTextAreaInputGUI($this->lng->txt('crs_important_info'), 'important');
        $area->setValue($this->object->getImportantInformation());
        $area->setRows(6);
        $area->setCols(80);
        $form->addItem($area);

        $area = new ilTextAreaInputGUI($this->lng->txt('crs_syllabus'), 'syllabus');
        $area->setValue($this->object->getSyllabus());
        $area->setRows(6);
        $area->setCols(80);
        $form->addItem($area);

        $tg = new \ilTextAreaInputGUI($this->lng->txt('crs_target_group'), 'target_group');
        $tg->setValue($this->object->getTargetGroup());
        $tg->setRows(6);
        $form->addItem($tg);

        $section = new ilFormSectionHeaderGUI();
        $section->setTitle($this->lng->txt('crs_info_download'));
        $form->addItem($section);

        $file = new ilFileInputGUI($this->lng->txt('crs_file'), 'file');
        $file->enableFileNameSelection('file_name');
        $form->addItem($file);

        $section = new ilFormSectionHeaderGUI();
        $section->setTitle($this->lng->txt('crs_contact'));
        $form->addItem($section);

        $text = new ilTextInputGUI($this->lng->txt('crs_contact_name'), 'contact_name');
        $text->setValue($this->object->getContactName());
        $text->setSize(40);
        $text->setMaxLength(70);
        $form->addItem($text);

        $text = new ilTextInputGUI($this->lng->txt('crs_contact_responsibility'), 'contact_responsibility');
        $text->setValue($this->object->getContactResponsibility());
        $text->setSize(40);
        $text->setMaxLength(70);
        $form->addItem($text);

        $text = new ilTextInputGUI($this->lng->txt('crs_contact_phone'), 'contact_phone');
        $text->setValue($this->object->getContactPhone());
        $text->setSize(40);
        $text->setMaxLength(40);
        $form->addItem($text);

        $text = new ilTextInputGUI($this->lng->txt('crs_contact_email'), 'contact_email');
        $text->setValue($this->object->getContactEmail());
        $text->setInfo($this->lng->txt('crs_contact_email_info'));
        $text->setSize(40);
        $text->setMaxLength(255);
        $form->addItem($text);

        $area = new ilTextAreaInputGUI($this->lng->txt('crs_contact_consultation'), 'contact_consultation');
        $area->setValue($this->object->getContactConsultation());
        $area->setRows(6);
        $area->setCols(80);
        $form->addItem($area);

        include_once('Services/AdvancedMetaData/classes/class.ilAdvancedMDRecordGUI.php');
        $this->record_gui = new ilAdvancedMDRecordGUI(ilAdvancedMDRecordGUI::MODE_EDITOR, 'crs', $this->object->getId());
        $this->record_gui->setPropertyForm($form);
        $this->record_gui->parse();

        return $form;
    }

    /**
     * @return bool
     * @throws \ilObjectException
     * @todo switch to form
     */
    public function updateInfoObject()
    {
        global $DIC;

        $ilErr = $DIC['ilErr'];
        $ilAccess = $DIC['ilAccess'];

        $this->checkPermission('write');

        include_once 'Modules/Course/classes/class.ilCourseFile.php';
        $file_obj = new ilCourseFile();
        $file_obj->setCourseId($this->object->getId());
        $name = (strlen($_POST['file_name']) ?
                               ilUtil::stripSlashes($_POST['file_name']) :
                               $_FILES['file']['name']);
        $file_obj->setFileName(ilFileUtils::getValidFilename($name));
        $file_obj->setFileSize($_FILES['file']['size']);
        $file_obj->setFileType($_FILES['file']['type']);
        $file_obj->setTemporaryName($_FILES['file']['tmp_name']);
        $file_obj->setErrorCode($_FILES['file']['error']);

        $this->object->setImportantInformation(ilUtil::stripSlashes($_POST['important']));
        $this->object->setSyllabus(ilUtil::stripSlashes($_POST['syllabus']));
        $this->object->setTargetGroup(\ilUtil::stripSlashes($_POST['target_group']));
        $this->object->setContactName(ilUtil::stripSlashes($_POST['contact_name']));
        $this->object->setContactResponsibility(ilUtil::stripSlashes($_POST['contact_responsibility']));
        $this->object->setContactPhone(ilUtil::stripSlashes($_POST['contact_phone']));
        $this->object->setContactEmail(ilUtil::stripSlashes($_POST['contact_email']));
        $this->object->setContactConsultation(ilUtil::stripSlashes($_POST['contact_consultation']));


        // validate

        $error = false;
        $ilErr->setMessage('');

        $file_obj->validate();
        $this->object->validateInfoSettings();
        if (strlen($ilErr->getMessage())) {
            $error = $ilErr->getMessage();
        }

        // needed for proper advanced MD validation
        $form = $this->initInfoEditor();
        $form->checkInput();
        if (!$this->record_gui->importEditFormPostValues()) {
            $error = true;
        }

        if ($error) {
            if ($error !== true) {
                ilUtil::sendFailure($ilErr->getMessage());
            }
            $this->editInfoObject($form);
            return false;
        }

        $this->object->update();
        $file_obj->create();
        $this->record_gui->writeEditForm();


        // Update ecs content
        include_once 'Modules/Course/classes/class.ilECSCourseSettings.php';
        $ecs = new ilECSCourseSettings($this->object);
        $ecs->handleContentUpdate();

        ilUtil::sendSuccess($this->lng->txt("crs_settings_saved"));
        $this->editInfoObject();
        return true;
    }


    /**
     * Update course settings
     * @global type $ilUser
     * @return boolean
     */
    public function updateObject()
    {
        $obj_service = $this->getObjectService();
        $setting = $this->settings;

        $form = $this->initEditForm();

        if (!$form->checkInput()) {
            $form->setValuesByPost();
            ilUtil::sendFailure($GLOBALS['DIC']->language()->txt('err_check_input'));
            return $this->editObject($form);
        }

        // Additional checks: subsription min/max
        if (
            $form->getInput('subscription_max') &&
            $form->getInput('subscription_min') &&
            ($form->getInput('subscription_max') < $form->getInput('subscription_min'))
        ) {
            $min = $form->getItemByPostVar('subscription_min');
            $min->setAlert($this->lng->txt('crs_subscription_min_members_err'));
            ilUtil::sendFailure($GLOBALS['DIC']->language()->txt('err_check_input'));
            return $this->editObject($form);
        }

        // Additional checks: both tile and objective view activated (not supported)
        if (
            $form->getInput('list_presentation') == "tile" &&
            $form->getInput('view_mode') == IL_CRS_VIEW_OBJECTIVE) {
            ilUtil::sendFailure($GLOBALS['DIC']->language()->txt('crs_tile_and_objective_view_not_supported'));
            return $this->editObject($form);
        }

        // Additional checks: both tile and session limitation activated (not supported)
        if ($form->getInput('sl') == "1" &&
            $form->getInput('list_presentation') == "tile") {
            ilUtil::sendFailure($GLOBALS['DIC']->language()->txt('crs_tile_and_session_limit_not_supported'));
            return $this->editObject($form);
        }

        // check successful

        // title/desc
        $this->object->setTitle($form->getInput('title'));
        $this->object->setDescription($form->getInput('desc'));

        // period
        $crs_period = $form->getItemByPostVar("period");


        $this->object->setCoursePeriod(
            $crs_period->getStart(),
            $crs_period->getEnd()
        );

        // activation/online
        $this->object->setOfflineStatus((bool) !$form->getInput('activation_online'));

        // activation period
        $period = $form->getItemByPostVar("access_period");
        if ($period->getStart() && $period->getEnd()) {
            $this->object->setActivationStart($period->getStart()->get(IL_CAL_UNIX));
            $this->object->setActivationEnd($period->getEnd()->get(IL_CAL_UNIX));
            $this->object->setActivationVisibility((int) $form->getInput('activation_visibility'));
        } else {
            $this->object->setActivationStart(null);
            $this->object->setActivationEnd(null);
        }

        // subscription settings
        $this->object->setSubscriptionPassword($form->getInput('subscription_password'));
        $this->object->setSubscriptionStart(null);
        $this->object->setSubscriptionEnd(null);

        $sub_type = $form->getInput('subscription_type');
        $sub_period = $form->getItemByPostVar('subscription_period');

        $this->object->setSubscriptionType($sub_type);
        if ($sub_type != IL_CRS_SUBSCRIPTION_DEACTIVATED) {
            if ($sub_period->getStart() && $sub_period->getEnd()) {
                $this->object->setSubscriptionLimitationType(IL_CRS_SUBSCRIPTION_LIMITED);
                $this->object->setSubscriptionStart($sub_period->getStart()->get(IL_CAL_UNIX));
                $this->object->setSubscriptionEnd($sub_period->getEnd()->get(IL_CAL_UNIX));
            } else {
                $this->object->setSubscriptionLimitationType(IL_CRS_SUBSCRIPTION_UNLIMITED);
            }
        } else {
            $this->object->setSubscriptionType(IL_CRS_SUBSCRIPTION_DIRECT);
            $this->object->setSubscriptionLimitationType(IL_CRS_SUBSCRIPTION_DEACTIVATED);
        }

        // registration code
        $this->object->enableRegistrationAccessCode((int) $form->getInput('reg_code_enabled'));
        $this->object->setRegistrationAccessCode($form->getInput('reg_code'));

        // cancellation end
        $this->object->setCancellationEnd($form->getItemByPostVar("cancel_end")->getDate());

        // waiting list
        $this->object->enableSubscriptionMembershipLimitation((int) $form->getInput('subscription_membership_limitation'));
        $this->object->setSubscriptionMaxMembers((int) $form->getInput('subscription_max'));
        $this->object->setSubscriptionMinMembers((int) $form->getInput('subscription_min'));
        $old_autofill = $this->object->hasWaitingListAutoFill();
        switch ((int) $form->getInput('waiting_list')) {
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

        ilContainer::_writeContainerSetting($this->object->getId(), "rep_breacrumb", $form->getInput('rep_breacrumb'));

        // custom icon
        $obj_service->commonSettings()->legacyForm($form, $this->object)->saveIcon();

        // tile image
        $obj_service->commonSettings()->legacyForm($form, $this->object)->saveTileImage();

        // list presentation
        $this->saveListPresentation($form);


        // view mode settings
        $this->object->setViewMode((int) $form->getInput('view_mode'));
        if ($this->object->getViewMode() == IL_CRS_VIEW_TIMING) {
            $this->object->setOrderType(ilContainer::SORT_ACTIVATION);
            $this->object->setTimingMode((int) $form->getInput('timing_mode'));
        }
        $this->object->setTimingMode($form->getInput('timing_mode'));
        $this->object->setOrderType($form->getInput('sorting'));
        $this->saveSortingSettings($form);

        $this->object->setAboStatus((int) $form->getInput('abo'));
        $this->object->setShowMembers((int) $form->getInput('show_members'));

        if (\ilPrivacySettings::_getInstance()->participantsListInCoursesEnabled()) {
            $this->object->setShowMembersExport((int) $form->getInput('show_members_export'));
        }
        $this->object->setMailToMembersType((int) $form->getInput('mail_type'));

        $this->object->enableSessionLimit((int) $form->getInput('sl'));

        $session_sp = $form->getInput('sp');
        $this->object->setNumberOfPreviousSessions(is_numeric($session_sp) ? (int) $session_sp : -1);
        $session_sn = $form->getInput('sn');
        $this->object->setNumberOfnextSessions(is_numeric($session_sn) ? (int) $session_sn : -1);
        $this->object->setAutoNotification($form->getInput('auto_notification') == 1 ? true : false);

        // lp sync
        $show_lp_sync_confirmation = false;

        // could be hidden in form
        if (isset($_POST['status_dt'])) {
            if (
                $this->object->getStatusDetermination() != ilObjCourse::STATUS_DETERMINATION_LP &&
                (int) $_POST['status_dt'] == ilObjCourse::STATUS_DETERMINATION_LP
            ) {
                $show_lp_sync_confirmation = true;
            } else {
                $this->object->setStatusDetermination((int) $form->getInput('status_dt'));
            }
        }

        if (!$old_autofill && $this->object->hasWaitingListAutoFill()) {
            $this->object->handleAutoFill();
        }
        $this->object->update();


        ilObjectServiceSettingsGUI::updateServiceSettingsForm(
            $this->object->getId(),
            $form,
            $this->getSubServices()
        );

        require_once('Services/Tracking/classes/class.ilChangeEvent.php');
        global $DIC;

        $ilUser = $DIC['ilUser'];
        ilChangeEvent::_recordWriteEvent($this->object->getId(), $ilUser->getId(), 'update');
        ilChangeEvent::_catchupWriteEvents($this->object->getId(), $ilUser->getId());

        // lp sync confirmation required
        if ($show_lp_sync_confirmation) {
            return $this->confirmLPSync();
        }

        // Update ecs export settings
        include_once 'Modules/Course/classes/class.ilECSCourseSettings.php';
        $ecs = new ilECSCourseSettings($this->object);
        if (!$ecs->handleSettingsUpdate()) {
            $form->setValuesByPost();
            ilUtil::sendFailure($GLOBALS['DIC']->language()->txt('err_check_input'));
            return $this->editObject($form);
        }

        return $this->afterUpdate();
    }

    protected function getSubServices() : array
    {
        $subs = array(
            ilObjectServiceSettingsGUI::CALENDAR_CONFIGURATION,
            ilObjectServiceSettingsGUI::AUTO_RATING_NEW_OBJECTS,
            ilObjectServiceSettingsGUI::TAG_CLOUD,
            ilObjectServiceSettingsGUI::CUSTOM_METADATA,
            ilObjectServiceSettingsGUI::BADGES,
            ilObjectServiceSettingsGUI::ORGU_POSITION_ACCESS,
            ilObjectServiceSettingsGUI::SKILLS,
            ilObjectServiceSettingsGUI::BOOKING,
            ilObjectServiceSettingsGUI::EXTERNAL_MAIL_PREFIX
        );
        if ($this->news->isGloballyActivated()) {
            $subs[] = ilObjectServiceSettingsGUI::USE_NEWS;
        }

        return $subs;
    }

    protected function confirmLPSync()
    {
        global $DIC;

        $tpl = $DIC['tpl'];

        include_once("./Services/Utilities/classes/class.ilConfirmationGUI.php");
        $cgui = new ilConfirmationGUI();
        $cgui->setFormAction($this->ctrl->getFormAction($this, "setLPSync"));
        $cgui->setHeaderText($this->lng->txt("crs_status_determination_sync"));
        $cgui->setCancel($this->lng->txt("cancel"), "edit");
        $cgui->setConfirm($this->lng->txt("confirm"), "setLPSync");

        $tpl->setContent($cgui->getHTML());
    }

    protected function setLPSyncObject()
    {
        $this->object->setStatusDetermination(ilObjCourse::STATUS_DETERMINATION_LP);
        $this->object->update();

        $this->object->syncMembersStatusWithLP();

        ilUtil::sendSuccess($this->lng->txt("settings_saved"), true);
        $this->ctrl->redirect($this, "edit");
    }

    /**
     * edit object
     *
     * @access public
     * @return
     */
    public function editObject(ilPropertyFormGUI $form = null)
    {
        $this->setSubTabs('properties');
        $this->tabs_gui->setSubTabActive('crs_settings');

        if ($form instanceof ilPropertyFormGUI) {
            $GLOBALS['DIC']['tpl']->setContent($form->getHTML());
            return true;
        } else {
            parent::editObject();
        }
    }

    /**
     * init form
     *
     * @access protected
     * @param
     * @return
     */
    protected function initEditForm()
    {
        $obj_service = $this->getObjectService();
        $setting = $this->settings;

        include_once('./Services/Form/classes/class.ilPropertyFormGUI.php');
        include_once('./Services/Calendar/classes/class.ilDateTime.php');

        $form = new ilPropertyFormGUI();
        $form->setTitle($this->lng->txt('crs_edit'));

        $form->addCommandButton('update', $this->lng->txt('save'));
        $form->addCommandButton('cancel', $this->lng->txt('cancel'));

        $form->setFormAction($this->ctrl->getFormAction($this, 'update'));

        // title and description
        $this->initFormTitleDescription($form);

        // Show didactic template type
        $this->initDidacticTemplate($form);

        // period
        include_once "Services/Form/classes/class.ilDateDurationInputGUI.php";
        $cdur = new ilDateDurationInputGUI($this->lng->txt('crs_period'), 'period');
        $this->lng->loadLanguageModule('mem');
        $cdur->enableToggleFullTime(
            $this->lng->txt('mem_period_without_time'),
            !$this->object->getCourseStartTimeIndication()
        );
        $cdur->setShowTime(true);
        $cdur->setInfo($this->lng->txt('crs_period_info'));
        $cdur->setStart($this->object->getCourseStart());
        $cdur->setEnd($this->object->getCourseEnd());
        $form->addItem($cdur);


        // activation/availability

        $this->lng->loadLanguageModule('rep');

        $section = new ilFormSectionHeaderGUI();
        $section->setTitle($this->lng->txt('rep_activation_availability'));
        $form->addItem($section);

        $online = new ilCheckboxInputGUI($this->lng->txt('rep_activation_online'), 'activation_online');
        $online->setChecked(!$this->object->getOfflineStatus());
        $online->setInfo($this->lng->txt('crs_activation_online_info'));
        $form->addItem($online);

        include_once "Services/Form/classes/class.ilDateDurationInputGUI.php";
        $dur = new ilDateDurationInputGUI($this->lng->txt('rep_time_period'), "access_period");
        $dur->setShowTime(true);
        $dur->setStart(new ilDateTime($this->object->getActivationStart(), IL_CAL_UNIX));
        $dur->setEnd(new ilDateTime($this->object->getActivationEnd(), IL_CAL_UNIX));
        $form->addItem($dur);

        $visible = new ilCheckboxInputGUI($this->lng->txt('rep_activation_limited_visibility'), 'activation_visibility');
        $visible->setInfo($this->lng->txt('crs_activation_limited_visibility_info'));
        $visible->setChecked($this->object->getActivationVisibility());
        $dur->addSubItem($visible);


        $section = new ilFormSectionHeaderGUI();
        $section->setTitle($this->lng->txt('crs_reg'));
        $form->addItem($section);

        $reg_proc = new ilRadioGroupInputGUI($this->lng->txt('crs_registration_type'), 'subscription_type');
        $reg_proc->setValue(
            ($this->object->getSubscriptionLimitationType() != IL_CRS_SUBSCRIPTION_DEACTIVATED)
                ? $this->object->getSubscriptionType()
                : IL_CRS_SUBSCRIPTION_DEACTIVATED
        );
        // $reg_proc->setInfo($this->lng->txt('crs_reg_type_info'));

        $opt = new ilRadioOption($this->lng->txt('crs_subscription_options_direct'), IL_CRS_SUBSCRIPTION_DIRECT);
        $reg_proc->addOption($opt);

        $opt = new ilRadioOption($this->lng->txt('crs_subscription_options_password'), IL_CRS_SUBSCRIPTION_PASSWORD);

        $pass = new ilTextInputGUI($this->lng->txt("password"), 'subscription_password');
        $pass->setRequired(true);
        $pass->setInfo($this->lng->txt('crs_reg_password_info'));
        $pass->setSubmitFormOnEnter(true);
        $pass->setSize(32);
        $pass->setMaxLength(32);
        $pass->setValue($this->object->getSubscriptionPassword());

        $opt->addSubItem($pass);
        $reg_proc->addOption($opt);

        $opt = new ilRadioOption($this->lng->txt('crs_subscription_options_confirmation'), IL_CRS_SUBSCRIPTION_CONFIRMATION);
        $opt->setInfo($this->lng->txt('crs_registration_confirmation_info'));
        $reg_proc->addOption($opt);

        $opt = new ilRadioOption($this->lng->txt('crs_reg_no_selfreg'), IL_CRS_SUBSCRIPTION_DEACTIVATED);
        $opt->setInfo($this->lng->txt('crs_registration_deactivated'));
        $reg_proc->addOption($opt);

        $form->addItem($reg_proc);


        // Registration codes
        $reg_code = new ilCheckboxInputGUI($this->lng->txt('crs_reg_code'), 'reg_code_enabled');
        $reg_code->setChecked($this->object->isRegistrationAccessCodeEnabled());
        $reg_code->setValue(1);
        $reg_code->setInfo($this->lng->txt('crs_reg_code_enabled_info'));

        /*
        $code = new ilNonEditableValueGUI($this->lng->txt('crs_reg_code_value'));
        $code->setValue($this->object->getRegistrationAccessCode());
        $reg_code->addSubItem($code);
        */

        #$link = new ilNonEditableValueGUI($this->lng->txt('crs_reg_code_link'));
        // Create default access code
        if (!$this->object->getRegistrationAccessCode()) {
            include_once './Services/Membership/classes/class.ilMembershipRegistrationCodeUtils.php';
            $this->object->setRegistrationAccessCode(ilMembershipRegistrationCodeUtils::generateCode());
        }
        $reg_link = new ilHiddenInputGUI('reg_code');
        $reg_link->setValue($this->object->getRegistrationAccessCode());
        $form->addItem($reg_link);

        $link = new ilCustomInputGUI($this->lng->txt('crs_reg_code_link'));
        include_once './Services/Link/classes/class.ilLink.php';
        $val = ilLink::_getLink($this->object->getRefId(), $this->object->getType(), array(), '_rcode' . $this->object->getRegistrationAccessCode());
        $link->setHTML('<span class="small">' . $val . '</span>');
        $reg_code->addSubItem($link);

        $form->addItem($reg_code);

        // time limit
        include_once "Services/Form/classes/class.ilDateDurationInputGUI.php";
        $sdur = new ilDateDurationInputGUI($this->lng->txt('crs_registration_limited'), "subscription_period");
        $sdur->setShowTime(true);
        if ($this->object->getSubscriptionStart()) {
            $sdur->setStart(new ilDateTime($this->object->getSubscriptionStart(), IL_CAL_UNIX));
        }
        if ($this->object->getSubscriptionEnd()) {
            $sdur->setEnd(new ilDateTime($this->object->getSubscriptionEnd(), IL_CAL_UNIX));
        }
        $form->addItem($sdur);

        // cancellation limit
        $cancel = new ilDateTimeInputGUI($this->lng->txt('crs_cancellation_end'), 'cancel_end');
        $cancel->setInfo($this->lng->txt('crs_cancellation_end_info'));
        $cancel_end = $this->object->getCancellationEnd();
        if ($cancel_end) {
            $cancel->setDate($cancel_end);
        }
        $form->addItem($cancel);

        // Max members
        $lim = new ilCheckboxInputGUI($this->lng->txt('crs_subscription_max_members_short'), 'subscription_membership_limitation');
        $lim->setInfo($this->lng->txt('crs_subscription_max_members_short_info'));
        $lim->setValue(1);
        $lim->setChecked($this->object->isSubscriptionMembershipLimited());

        $min = new ilTextInputGUI('', 'subscription_min');
        $min->setSubmitFormOnEnter(true);
        $min->setSize(4);
        $min->setMaxLength(4);
        $min->setValue($this->object->getSubscriptionMinMembers() ? $this->object->getSubscriptionMinMembers() : '');
        $min->setTitle($this->lng->txt('crs_subscription_min_members'));
        $min->setInfo($this->lng->txt('crs_subscription_min_members_info'));
        $lim->addSubItem($min);

        $max = new ilTextInputGUI('', 'subscription_max');
        $max->setSubmitFormOnEnter(true);
        $max->setSize(4);
        $max->setMaxLength(4);
        $max->setValue($this->object->getSubscriptionMaxMembers() ? $this->object->getSubscriptionMaxMembers() : '');
        $max->setTitle($this->lng->txt('crs_subscription_max_members'));
        $max->setInfo($this->lng->txt('crs_reg_max_info'));

        $lim->addSubItem($max);

        /*
        $wait = new ilCheckboxInputGUI($this->lng->txt('crs_waiting_list'),'waiting_list');
        $wait->setChecked($this->object->enabledWaitingList());
        $wait->setInfo($this->lng->txt('crs_wait_info'));
        $lim->addSubItem($wait);

        $wait = new ilCheckboxInputGUI($this->lng->txt('crs_waiting_list'),'waiting_list');
        $wait->setChecked($this->object->enabledWaitingList());
        $wait->setInfo($this->lng->txt('crs_wait_info'));
        $lim->addSubItem($wait);

        $auto = new ilCheckboxInputGUI($this->lng->txt('crs_waiting_list_autofill'), 'auto_wait');
        $auto->setChecked($this->object->hasWaitingListAutoFill());
        $auto->setInfo($this->lng->txt('crs_waiting_list_autofill_info'));
        $wait->addSubItem($auto);
        */

        $wait = new ilRadioGroupInputGUI($this->lng->txt('crs_waiting_list'), 'waiting_list');

        $option = new ilRadioOption($this->lng->txt('none'), 0);
        $wait->addOption($option);

        $option = new ilRadioOption($this->lng->txt('crs_waiting_list_no_autofill'), 1);
        $option->setInfo($this->lng->txt('crs_wait_info'));
        $wait->addOption($option);

        $option = new ilRadioOption($this->lng->txt('crs_waiting_list_autofill'), 2);
        $option->setInfo($this->lng->txt('crs_waiting_list_autofill_info'));
        $wait->addOption($option);

        if ($this->object->hasWaitingListAutoFill()) {
            $wait->setValue(2);
        } elseif ($this->object->enabledWaitingList()) {
            $wait->setValue(1);
        }

        $lim->addSubItem($wait);

        $form->addItem($lim);


        $pres = new ilFormSectionHeaderGUI();
        $pres->setTitle($this->lng->txt('crs_view_mode'));

        $form->addItem($pres);

        // title and icon visibility
        $form = $obj_service->commonSettings()->legacyForm($form, $this->object)->addTitleIconVisibility();

        // top actions visibility
        $form = $obj_service->commonSettings()->legacyForm($form, $this->object)->addTopActionsVisibility();

        // breadcrumbs
        if ($setting->get("rep_breadcr_crs_overwrite")) {
            $add = $setting->get("rep_breadcr_crs_default")
                ? " (" . $this->lng->txt("crs_breadcrumb_crs_only") . ")"
                : " (" . $this->lng->txt("crs_breadcrumb_full_path") . ")";
            $options = array(
                self::BREADCRUMB_DEFAULT => $this->lng->txt("crs_sys_default") . $add,
                self::BREADCRUMB_CRS_ONLY => $this->lng->txt("crs_breadcrumb_crs_only"),
                self::BREADCRUMB_FULL_PATH => $this->lng->txt("crs_breadcrumb_full_path")
            );
            $si = new ilSelectInputGUI($this->lng->txt("crs_shorten_breadcrumb"), "rep_breacrumb");
            $si->setValue((int) ilContainer::_lookupContainerSetting($this->object->getId(), "rep_breacrumb"));
            $si->setOptions($options);
            $form->addItem($si);
        }


        // custom icon
        $form = $obj_service->commonSettings()->legacyForm($form, $this->object)->addIcon();

        // tile image
        $form = $obj_service->commonSettings()->legacyForm($form, $this->object)->addTileImage();

        // list presentation
        $form = $this->initListPresentationForm($form);

        // presentation type
        $view_type = new ilRadioGroupInputGUI($this->lng->txt('crs_presentation_type'), 'view_mode');
        $view_type->setValue($this->object->getViewMode());

        $opts = new ilRadioOption($this->lng->txt('cntr_view_sessions'), IL_CRS_VIEW_SESSIONS);
        $opts->setInfo($this->lng->txt('cntr_view_info_sessions'));
        $view_type->addOption($opts);

        // Limited sessions
        $sess = new ilCheckboxInputGUI($this->lng->txt('sess_limit'), 'sl');
        $sess->setValue(1);
        $sess->setChecked($this->object->isSessionLimitEnabled());
        $sess->setInfo($this->lng->txt('sess_limit_info'));

        $prev = new ilNumberInputGUI($this->lng->txt('sess_num_prev'), 'sp');
        #$prev->setSubmitFormOnEnter(true);
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
        #$next->setSubmitFormOnEnter(true);
        $next->setMinValue(0);
        $next->setValue(
            $this->object->getNumberOfNextSessions() == -1 ?
                        '' :
                        $this->object->getNumberOfnextSessions()
        );
        $next->setSize(2);
        $next->setMaxLength(3);
        $sess->addSubItem($next);

        $opts->addSubItem($sess);




        $optsi = new ilRadioOption($this->lng->txt('cntr_view_simple'), IL_CRS_VIEW_SIMPLE);
        $optsi->setInfo($this->lng->txt('cntr_view_info_simple'));
        $view_type->addOption($optsi);

        $optbt = new ilRadioOption($this->lng->txt('cntr_view_by_type'), IL_CRS_VIEW_BY_TYPE);
        $optbt->setInfo($this->lng->txt('cntr_view_info_by_type'));
        $view_type->addOption($optbt);

        $opto = new ilRadioOption($this->lng->txt('crs_view_objective'), IL_CRS_VIEW_OBJECTIVE);
        $opto->setInfo($this->lng->txt('crs_view_info_objective'));
        $view_type->addOption($opto);

        $optt = new ilRadioOption($this->lng->txt('crs_view_timing'), IL_CRS_VIEW_TIMING);
        $optt->setInfo($this->lng->txt('crs_view_info_timing'));

        // cognos-blu-patch: begin
        $timing = new ilRadioGroupInputGUI($this->lng->txt('crs_view_timings'), "timing_mode");
        $timing->setValue($this->object->getTimingMode());

        $absolute = new ilRadioOption($this->lng->txt('crs_view_timing_absolute'), IL_CRS_VIEW_TIMING_ABSOLUTE);
        $absolute->setInfo($this->lng->txt('crs_view_info_timing_absolute'));
        $timing->addOption($absolute);

        $relative = new ilRadioOption($this->lng->txt('crs_view_timing_relative'), IL_CRS_VIEW_TIMING_RELATIVE);
        $relative->setInfo($this->lng->txt('crs_view_info_timing_relative'));
        $timing->addOption($relative);

        $optt->addSubItem($timing);
        // cognos-blu-patch: end

        $view_type->addOption($optt);

        $form->addItem($view_type);

        $this->initSortingForm(
            $form,
            array(
                ilContainer::SORT_TITLE,
                ilContainer::SORT_MANUAL,
                ilContainer::SORT_CREATION,
                ilContainer::SORT_ACTIVATION
            )
        );



        // lp vs. course status
        include_once("Services/Tracking/classes/class.ilObjUserTracking.php");
        if (ilObjUserTracking::_enabledLearningProgress()) {
            include_once './Services/Object/classes/class.ilObjectLP.php';
            $olp = ilObjectLP::getInstance($this->object->getId());
            if ($olp->getCurrentMode()) {
                $lp_status = new ilFormSectionHeaderGUI();
                $lp_status->setTitle($this->lng->txt('crs_course_status_of_users'));
                $form->addItem($lp_status);

                $lp_status_options = new ilRadioGroupInputGUI($this->lng->txt('crs_status_determination'), "status_dt");
                //				$lp_status_options->setRequired(true);
                $lp_status_options->setValue($this->object->getStatusDetermination());

                $lp_option = new ilRadioOption(
                    $this->lng->txt('crs_status_determination_lp'),
                    ilObjCourse::STATUS_DETERMINATION_LP,
                    $this->lng->txt('crs_status_determination_lp_info')
                );
                $lp_status_options->addOption($lp_option);
                $lp_status_options->addOption(new ilRadioOption(
                    $this->lng->txt('crs_status_determination_manual'),
                    ilObjCourse::STATUS_DETERMINATION_MANUAL
                ));

                $form->addItem($lp_status_options);
            }
        }

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

        $mem = new ilCheckboxInputGUI($this->lng->txt('crs_show_members'), 'show_members');
        $mem->setChecked($this->object->getShowMembers());
        $mem->setInfo($this->lng->txt('crs_show_members_info'));
        $form->addItem($mem);

        // check privacy
        if (\ilPrivacySettings::_getInstance()->participantsListInCoursesEnabled()) {
            $part_list = new ilCheckboxInputGUI($this->lng->txt('crs_show_member_export'), 'show_members_export');
            $part_list->setChecked($this->object->getShowMembersExport());
            $part_list->setInfo($this->lng->txt('crs_show_member_export_info'));
            $mem->addSubItem($part_list);
        }

        // Show members type
        $mail_type = new ilRadioGroupInputGUI($this->lng->txt('crs_mail_type'), 'mail_type');
        $mail_type->setValue($this->object->getMailToMembersType());

        $mail_tutors = new ilRadioOption(
            $this->lng->txt('crs_mail_tutors_only'),
            ilCourseConstants::MAIL_ALLOWED_TUTORS,
            $this->lng->txt('crs_mail_tutors_only_info')
        );
        $mail_type->addOption($mail_tutors);

        $mail_all = new ilRadioOption(
            $this->lng->txt('crs_mail_all'),
            ilCourseConstants::MAIL_ALLOWED_ALL,
            $this->lng->txt('crs_mail_all_info')
        );
        $mail_type->addOption($mail_all);
        $form->addItem($mail_type);

        // Notification Settings
        /*$notification = new ilFormSectionHeaderGUI();
        $notification->setTitle($this->lng->txt('crs_notification'));
        $form->addItem($notification);*/

        // Self notification
        $not = new ilCheckboxInputGUI($this->lng->txt('crs_auto_notification'), 'auto_notification');
        $not->setValue(1);
        $not->setInfo($this->lng->txt('crs_auto_notification_info'));
        $not->setChecked($this->object->getAutoNotification());
        $form->addItem($not);


        // Further information
        //$further = new ilFormSectionHeaderGUI();
        //$further->setTitle($this->lng->txt('crs_further_settings'));
        //$form->addItem($further);

        $desk = new ilCheckboxInputGUI($this->lng->txt('crs_add_remove_from_desktop'), 'abo');
        $desk->setChecked($this->object->getAboStatus());
        $desk->setInfo($this->lng->txt('crs_add_remove_from_desktop_info'));
        $form->addItem($desk);


        // Edit ecs export settings
        include_once 'Modules/Course/classes/class.ilECSCourseSettings.php';
        $ecs = new ilECSCourseSettings($this->object);
        $ecs->addSettingsToForm($form, 'crs');

        return $form;
    }

    protected function getEditFormValues()
    {
        // values are done in initEditForm()
    }

    public function sendFileObject()
    {
        include_once 'Modules/Course/classes/class.ilCourseFile.php';
        $file = new ilCourseFile((int) $_GET['file_id']);
        ilUtil::deliverFile($file->getAbsolutePath(), $file->getFileName(), $file->getFileType());
        return true;
    }

    /**
    * set sub tabs
    */
    public function setSubTabs($a_tab)
    {
        global $DIC;

        $rbacsystem = $DIC['rbacsystem'];
        $ilUser = $DIC['ilUser'];
        $ilAccess = $DIC['ilAccess'];
        $tree = $DIC['tree'];

        switch ($a_tab) {
            case "properties":
                $this->tabs_gui->addSubTabTarget(
                    "crs_settings",
                    $this->ctrl->getLinkTarget($this, 'edit'),
                    "edit",
                    get_class($this)
                );

                $this->tabs_gui->addSubTabTarget(
                    "crs_info_settings",
                    $this->ctrl->getLinkTarget($this, 'editInfo'),
                    "editInfo",
                    get_class($this)
                );

                $this->tabs_gui->addSubTabTarget(
                    "preconditions",
                    $this->ctrl->getLinkTargetByClass('ilConditionHandlerGUI', 'listConditions'),
                    "",
                    "ilConditionHandlerGUI"
                );

                $this->tabs_gui->addSubTabTarget(
                    "crs_start_objects",
                    $this->ctrl->getLinkTargetByClass('ilContainerStartObjectsGUI', 'listStructure'),
                    "listStructure",
                    get_class($this)
                );

                $this->tabs_gui->addSubTabTarget(
                    'groupings',
                    $this->ctrl->getLinkTargetByClass('ilobjcoursegroupinggui', 'listGroupings'),
                    'listGroupings',
                    get_class($this)
                );
                $lti_settings = new ilLTIProviderObjectSettingGUI($this->object->getRefId());
                if ($lti_settings->hasSettingsAccess()) {
                    $this->tabs_gui->addSubTabTarget(
                        'lti_provider',
                        $this->ctrl->getLinkTargetByClass(ilLTIProviderObjectSettingGUI::class)
                    );
                }

                // map settings
                include_once("./Services/Maps/classes/class.ilMapUtil.php");
                if (ilMapUtil::isActivated()) {
                    $this->tabs_gui->addSubTabTarget(
                        "crs_map_settings",
                        $this->ctrl->getLinkTarget($this, 'editMapSettings'),
                        "editMapSettings",
                        get_class($this)
                    );
                }


                include_once('Services/PrivacySecurity/classes/class.ilPrivacySettings.php');
                include_once('Modules/Course/classes/Export/class.ilCourseDefinedFieldDefinition.php');
                // only show if export permission is granted
                if (ilPrivacySettings::_getInstance()->checkExportAccess($this->object->getRefId()) or ilCourseDefinedFieldDefinition::_hasFields($this->object->getId())) {
                    $this->tabs_gui->addSubTabTarget(
                        'crs_custom_user_fields',
                        $this->ctrl->getLinkTargetByClass('ilobjectcustomuserfieldsgui'),
                        '',
                        'ilobjectcustomuserfieldsgui'
                    );
                }

                // certificates
                $validator = new ilCertificateActiveValidator();
                if (true === $validator->validate()) {
                    $this->tabs_gui->addSubTabTarget(
                        "certificate",
                        $this->ctrl->getLinkTargetByClass("ilcertificategui", "certificateeditor"),
                        "",
                        "ilcertificategui"
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

                if ($this->object->getShowMembersExport()) {
                    $this->tabs_gui->addSubTab(
                        'export_members',
                        $this->lng->txt('crs_show_member_export_settings'),
                        $this->ctrl->getLinkTargetByClass('ilmemberexportsettingsgui', '')
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
     * show possible sub objects selection list
     */
    public function showPossibleSubObjects()
    {
        if (
            $this->object->getViewMode() == ilContainer::VIEW_OBJECTIVE &&
            !$this->isActiveAdministrationPanel()) {
            return;
        }
        $gui = new ilObjectAddNewItemGUI($this->object->getRefId());
        $gui->render();
    }


    /**
    * save object
    * @access	public
    */
    protected function afterSave(ilObject $a_new_object)
    {
        global $DIC;

        $rbacadmin = $DIC['rbacadmin'];
        $ilUser = $DIC['ilUser'];
        $ilSetting = $DIC['ilSetting'];

        $a_new_object->getMemberObject()->add($ilUser->getId(), IL_CRS_ADMIN);
        $a_new_object->getMemberObject()->updateNotification($ilUser->getId(), $ilSetting->get('mail_crs_admin_notification', true));
        // cognos-blu-patch: begin
        $a_new_object->getMemberObject()->updateContact($ilUser->getId(), 1);
        // cognos-blu-patch: end
        $a_new_object->update();

        // BEGIN ChangeEvent: Record write event.
        require_once('Services/Tracking/classes/class.ilChangeEvent.php');
        global $DIC;

        $ilUser = $DIC['ilUser'];
        ilChangeEvent::_recordWriteEvent($a_new_object->getId(), $ilUser->getId(), 'create');
        // END ChangeEvent: Record write event.

        // always send a message
        ilUtil::sendSuccess($this->lng->txt("crs_added"), true);

        $this->ctrl->setParameter($this, "ref_id", $a_new_object->getRefId());
        ilUtil::redirect($this->getReturnLocation(
            "save",
            $this->ctrl->getLinkTarget($this, "edit", "", false, false)
        ));
    }

    /**
     * set preferences (show/hide tabel content)
     *
     * @access public
     * @return
     */
    public function setShowHidePrefs()
    {
        global $DIC;

        $ilUser = $DIC['ilUser'];

        if (isset($_GET['admin_hide'])) {
            $ilUser->writePref('crs_admin_hide', (int) $_GET['admin_hide']);
        }
        if (isset($_GET['tutor_hide'])) {
            $ilUser->writePref('crs_tutor_hide', (int) $_GET['tutor_hide']);
        }
        if (isset($_GET['member_hide'])) {
            $ilUser->writePref('crs_member_hide', (int) $_GET['member_hide']);
        }
        if (isset($_GET['subscriber_hide'])) {
            $ilUser->writePref('crs_subscriber_hide', (int) $_GET['subscriber_hide']);
        }
        if (isset($_GET['wait_hide'])) {
            $ilUser->writePref('crs_wait_hide', (int) $_GET['wait_hide']);
        }
        include_once './Modules/Course/classes/class.ilCourseParticipants.php';
        foreach (ilCourseParticipants::getMemberRoles($this->object->getRefId()) as $role_id) {
            if (isset($_GET['role_hide_' . $role_id])) {
                $ilUser->writePref('crs_role_hide_' . $role_id, (int) $_GET['role_hide_' . $role_id]);
            }
        }
    }

    public function readMemberData($ids, $selected_columns = null, bool $skip_names = false)
    {
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
        include_once('./Services/PrivacySecurity/classes/class.ilPrivacySettings.php');
        $privacy = ilPrivacySettings::_getInstance();

        if ($privacy->enabledCourseAccessTimes()) {
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

        foreach ((array) $ids as $usr_id) {
            /**
             * When building the members table in a course, user names are
             * already read out via ilUserQuery::getUserListData (#31394).
             * Adding skip_name as a parameter here is not super elegant, but
             * seems like the only practical way avoid unnecessarily reading
             * out the names again.
             */
            if (!$skip_names) {
                $name = ilObjUser::_lookupName($usr_id);
                $tmp_data['firstname'] = $name['firstname'];
                $tmp_data['lastname'] = $name['lastname'];
                $tmp_data['login'] = $name['login'];
            }
            $tmp_data['passed'] = $this->object->getMembersObject()->hasPassed($usr_id) ? 1 : 0;
            if ($this->object->getStatusDetermination() == ilObjCourse::STATUS_DETERMINATION_LP) {
                $tmp_data['passed_info'] = $this->object->getMembersObject()->getPassedInfo($usr_id);
            }
            $tmp_data['notification'] = $this->object->getMembersObject()->isNotificationEnabled($usr_id) ? 1 : 0;
            $tmp_data['blocked'] = $this->object->getMembersObject()->isBlocked($usr_id) ? 1 : 0;
            // cognos-blu-patch: begin
            $tmp_data['contact'] = $this->object->getMembersObject()->isContact($usr_id) ? 1 : 0;
            // cognos-blu-patch: end

            $tmp_data['usr_id'] = $usr_id;

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

            if ($privacy->enabledCourseAccessTimes()) {
                if (isset($progress[$usr_id]['ts']) and $progress[$usr_id]['ts']) {
                    $tmp_data['access_ut'] = $progress[$usr_id]['ts'];
                    $tmp_data['access_time'] = ilDatePresentation::formatDate(new ilDateTime($progress[$usr_id]['ts'], IL_CAL_UNIX));
                } else {
                    $tmp_data['access_ut'] = 0;
                    $tmp_data['access_time'] = $this->lng->txt('no_date');
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
     * sync course status and lp status
     *
     * @param int $a_member_id
     * @param bool $a_has_passed
     */
    public function updateLPFromStatus($a_member_id, $a_has_passed)
    {
        global $DIC;

        $ilUser = $DIC['ilUser'];

        include_once("Services/Tracking/classes/class.ilObjUserTracking.php");
        if (ilObjUserTracking::_enabledLearningProgress() &&
            $this->object->getStatusDetermination() == ilObjCourse::STATUS_DETERMINATION_LP) {
            include_once './Services/Object/classes/class.ilObjectLP.php';
            $olp = ilObjectLP::getInstance($this->object->getId());
            if ($olp->getCurrentMode() == ilLPObjSettings::LP_MODE_MANUAL_BY_TUTOR) {
                include_once 'Services/Tracking/classes/class.ilLPMarks.php';
                $marks = new ilLPMarks($this->object->getId(), $a_member_id);

                // only if status has changed
                if ($marks->getCompleted() != $a_has_passed) {
                    $marks->setCompleted($a_has_passed);
                    $marks->update();

                    // as course is origin of LP status change, block syncing
                    include_once("./Modules/Course/classes/class.ilCourseAppEventListener.php");
                    ilCourseAppEventListener::setBlockedForLP(true);

                    include_once("./Services/Tracking/classes/class.ilLPStatusWrapper.php");
                    ilLPStatusWrapper::_updateStatus($this->object->getId(), $a_member_id);
                }
            }
        }
    }



    public function autoFillObject()
    {
        global $DIC;

        $rbacsystem = $DIC['rbacsystem'];

        $this->checkPermission('write');

        if ($this->object->isSubscriptionMembershipLimited() and $this->object->getSubscriptionMaxMembers() and
           $this->object->getSubscriptionMaxMembers() <= $this->object->getMembersObject()->getCountMembers()) {
            ilUtil::sendFailure($this->lng->txt("crs_max_members_reached"));
            $this->membersObject();

            return false;
        }
        if ($number = $this->object->getMembersObject()->autoFillSubscribers()) {
            ilUtil::sendSuccess($this->lng->txt("crs_number_users_added") . " " . $number);
        } else {
            ilUtil::sendFailure($this->lng->txt("crs_no_users_added"));
        }
        $this->membersObject();

        return true;
    }

    public function leaveObject()
    {
        global $DIC;

        $ilUser = $DIC['ilUser'];

        $this->checkPermission('leave');

        if ($this->object->getMembersObject()->isLastAdmin($ilUser->getId())) {
            ilUtil::sendFailure($this->lng->txt('crs_min_one_admin'));
            $this->viewObject();
            return false;
        }

        $this->tabs_gui->setTabActive('crs_unsubscribe');
        include_once "Services/Utilities/classes/class.ilConfirmationGUI.php";
        $cgui = new ilConfirmationGUI();
        $cgui->setHeaderText($this->lng->txt('crs_unsubscribe_sure'));
        $cgui->setFormAction($this->ctrl->getFormAction($this));
        $cgui->setCancel($this->lng->txt("cancel"), "cancel");
        $cgui->setConfirm($this->lng->txt("crs_unsubscribe"), "performUnsubscribe");
        $this->tpl->setContent($cgui->getHTML());
    }

    /**
     * DEPRECATED?
     */
    public function unsubscribeObject()
    {
        $this->leaveObject();
    }

    public function performUnsubscribeObject()
    {
        global $DIC;

        $ilUser = $DIC['ilUser'];
        $ilCtrl = $DIC['ilCtrl'];

        // CHECK ACCESS
        $this->checkPermission('leave');
        $this->object->getMembersObject()->delete($this->ilias->account->getId());
        $this->object->getMembersObject()->sendUnsubscribeNotificationToAdmins($this->ilias->account->getId());
        $this->object->getMembersObject()->sendNotification($this->object->getMembersObject()->NOTIFY_UNSUBSCRIBE, $ilUser->getId());

        ilUtil::sendSuccess($this->lng->txt('crs_unsubscribed_from_crs'), true);

        $ilCtrl->setParameterByClass("ilrepositorygui", "ref_id", $this->tree->getParentId($this->ref_id));
        $ilCtrl->redirectByClass("ilrepositorygui", "");
    }

    /**
     * Get tabs for member agreement
     */
    protected function getAgreementTabs()
    {
        if ($GLOBALS['DIC']['ilAccess']->checkAccess('visible', '', $this->ref_id)) {
            $GLOBALS['DIC']['ilTabs']->addTarget(
                "info_short",
                $this->ctrl->getLinkTargetByClass(
                    array("ilobjcoursegui", "ilinfoscreengui"),
                    "showSummary"
                ),
                "infoScreen"
            );
        }
        if ($GLOBALS['DIC']['ilAccess']->checkAccess('leave', '', $this->object->getRefId()) and $this->object->getMemberObject()->isMember()) {
            $GLOBALS['DIC']['ilTabs']->addTarget(
                "crs_unsubscribe",
                $this->ctrl->getLinkTarget($this, "unsubscribe"),
                'leave',
                ""
            );
        }
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

    /**
    * Get tabs
    */
    public function getTabs()
    {
        global $DIC;

        $ilUser = $DIC['ilUser'];
        $lng = $DIC['lng'];
        $ilHelp = $DIC['ilHelp'];

        $ilAccess = $GLOBALS['DIC']->access();

        $ilHelp->setScreenIdComponent("crs");

        $this->ctrl->setParameter($this, "ref_id", $this->ref_id);

        if ($ilAccess->checkAccess('read', '', $this->ref_id)) {
            // default activation
            $this->tabs_gui->activateTab('view_content');
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

        if ($this->object->getViewMode() == IL_CRS_VIEW_TIMING and
            $ilAccess->checkAccess('write', '', $this->ref_id)
        ) {
            $this->tabs->addTab(
                'timings_timings',
                $lng->txt('timings_timings'),
                $this->ctrl->getLinkTargetByClass('ilcoursecontentgui', 'manageTimings')
            );
        } elseif (
            $this->object->getViewMode() == IL_CRS_VIEW_TIMING and
            $this->object->getMemberObject()->isParticipant() and
            $ilAccess->checkAccess('read', '', $this->ref_id)) {
            $this->tabs->addTab(
                'timings_timings',
                $lng->txt('timings_timings'),
                $this->ctrl->getLinkTargetByClass('ilcoursecontentgui', 'managePersonalTimings')
            );
        }



        // learning objectives
        if ($ilAccess->checkAccess('write', '', $this->ref_id)) {
            include_once('./Modules/Course/classes/class.ilCourseObjective.php');
            if ($this->object->getViewMode() == IL_CRS_VIEW_OBJECTIVE or ilCourseObjective::_getCountObjectives($this->object->getId())) {
                $this->tabs_gui->addTarget(
                    'crs_objectives',
                    $this->ctrl->getLinkTargetByClass('illoeditorgui', ''),
                    'illoeditorgui'
                );
            }
        }

        if (
            $ilAccess->checkAccess('visible', '', $this->ref_id) ||
            $ilAccess->checkAccess('join', '', $this->ref_id) ||
            $ilAccess->checkAccess('read', '', $this->ref_id)
        ) {
            //$next_class = $this->ctrl->getNextClass($this);

            // this is not nice. tabs should be displayed in ilcoursegui
            // not via ilrepositorygui, then next_class == ilinfoscreengui
            // could be checked
            $force_active = (strtolower($_GET["cmdClass"]) == "ilinfoscreengui"
                || strtolower($_GET["cmdClass"]) == "ilnotegui")
                ? true
                : false;
            $this->tabs_gui->addTarget(
                "info_short",
                $this->ctrl->getLinkTargetByClass(
                    array("ilobjcoursegui", "ilinfoscreengui"),
                    "showSummary"
                ),
                "infoScreen",
                "",
                "",
                $force_active
            );
        }
        if ($ilAccess->checkAccess('write', '', $this->ref_id)) {
            $force_active = (strtolower($_GET["cmdClass"]) == "ilconditionhandlergui"
                && $_GET["item_id"] == "")
                ? true
                : false;
            $this->tabs_gui->addTarget(
                "settings",
                $this->ctrl->getLinkTarget($this, "edit"),
                array("edit", "editMapSettings", "editCourseIcons", "listStructure"),
                "",
                "",
                $force_active
            );
        }


        $is_participant = ilCourseParticipants::_isParticipant($this->ref_id, $ilUser->getId());
        include_once './Services/Mail/classes/class.ilMail.php';
        $mail = new ilMail($GLOBALS['DIC']['ilUser']->getId());

        include_once './Modules/Course/classes/class.ilCourseMembershipGUI.php';
        $membership_gui = new ilCourseMembershipGUI($this, $this->object);
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
        if (ilContSkillPresentationGUI::isAccessible($this->ref_id)) {
            $this->tabs_gui->addTarget(
                "obj_tool_setting_skills",
                $this->ctrl->getLinkTargetByClass(array("ilcontainerskillgui", "ilcontskillpresentationgui"), ""),
                "",
                array("ilcontainerskillgui", "ilcontskillpresentationgui", "ilcontskilladmingui")
            );
        }

        // booking
        if ($ilAccess->checkAccess('write', '', $this->ref_id) && ilContainer::_lookupContainerSetting(
            $this->object->getId(),
            ilObjectServiceSettingsGUI::BOOKING,
            false
        )) {
            $this->tabs_gui->addTarget(
                "obj_tool_setting_booking",
                $this->ctrl->getLinkTargetByClass(array("ilbookinggatewaygui"), "")
            );
        }

        // learning progress
        include_once './Services/Tracking/classes/class.ilLearningProgressAccess.php';
        if (ilLearningProgressAccess::checkAccess($this->object->getRefId(), $is_participant)) {
            $this->tabs_gui->addTarget(
                'learning_progress',
                $this->ctrl->getLinkTargetByClass(array('ilobjcoursegui','illearningprogressgui'), ''),
                '',
                array('illplistofobjectsgui','illplistofsettingsgui','illearningprogressgui','illplistofprogressgui')
            );
        }

        // meta data
        if ($ilAccess->checkAccess('write', '', $this->ref_id)) {
            include_once "Services/Object/classes/class.ilObjectMetaDataGUI.php";
            $mdgui = new ilObjectMetaDataGUI($this->object);
            $mdtab = $mdgui->getTab();
            if ($mdtab) {
                $this->tabs_gui->addTarget(
                    "meta_data",
                    $mdtab,
                    "",
                    "ilobjectmetadatagui"
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

        if ($ilAccess->checkAccess('edit_permission', '', $this->ref_id)) {
            $this->tabs_gui->addTarget(
                "perm_settings",
                $this->ctrl->getLinkTargetByClass(array(get_class($this),'ilpermissiongui'), "perm"),
                array("perm","info","owner"),
                'ilpermissiongui'
            );
        }

        // Join/Leave
        if ($ilAccess->checkAccess('join', '', $this->ref_id)
            and !$this->object->getMemberObject()->isAssigned()) {
            include_once './Modules/Course/classes/class.ilCourseWaitingList.php';
            if (ilCourseWaitingList::_isOnList($ilUser->getId(), $this->object->getId())) {
                $this->tabs_gui->addTab(
                    'leave',
                    $this->lng->txt('membership_leave'),
                    $this->ctrl->getLinkTargetByClass('ilcourseregistrationgui', 'show', '')
                );
            } else {
                $this->tabs_gui->addTarget(
                    "join",
                    $this->ctrl->getLinkTargetByClass('ilcourseregistrationgui', "show"),
                    'show',
                    ""
                );
            }
        }
        if ($ilAccess->checkAccess('leave', '', $this->object->getRefId())
            and $this->object->getMemberObject()->isMember()) {
            $this->tabs_gui->addTarget(
                "crs_unsubscribe",
                $this->ctrl->getLinkTarget($this, "unsubscribe"),
                'leave',
                ""
            );
        }
    }


    public function executeCommand()
    {
        global $DIC;
        $rbacsystem = $DIC['rbacsystem'];
        $ilUser = $DIC['ilUser'];
        $ilAccess = $DIC['ilAccess'];
        $ilErr = $DIC['ilErr'];
        $ilTabs = $DIC['ilTabs'];
        $ilNavigationHistory = $DIC['ilNavigationHistory'];
        $ilCtrl = $DIC['ilCtrl'];
        $ilToolbar = $DIC['ilToolbar'];

        $next_class = $this->ctrl->getNextClass($this);
        $cmd = $this->ctrl->getCmd();

        $this->prepareOutput();

        // add entry to navigation history
        if (!$this->getCreationMode() &&
            $ilAccess->checkAccess('read', '', $_GET['ref_id'])) {
            include_once("./Services/Link/classes/class.ilLink.php");
            $ilNavigationHistory->addItem(
                $_GET["ref_id"],
                ilLink::_getLink($_GET["ref_id"], "crs"),
                "crs"
            );
        }
        $header_action = true;
        switch ($next_class) {
            case 'ilreputilgui':
                $ru = new \ilRepUtilGUI($this);
                $this->ctrl->setReturn($this, 'trash');
                $this->ctrl->forwardCommand($ru);
                break;

            case 'illtiproviderobjectsettinggui':

                $this->setSubTabs('properties');
                $this->tabs_gui->activateTab('settings');
                $this->tabs_gui->activateSubTab('lti_provider');
                $lti_gui = new ilLTIProviderObjectSettingGUI($this->object->getRefId());
                $lti_gui->setCustomRolesForSelection($GLOBALS['DIC']->rbac()->review()->getLocalRoles($this->object->getRefId()));
                $lti_gui->offerLTIRolesForSelection(false);
                $this->ctrl->forwardCommand($lti_gui);
                break;

            case 'ilcoursemembershipgui':

                $this->tabs_gui->activateTab('members');

                include_once './Modules/Course/classes/class.ilCourseMembershipGUI.php';
                $mem_gui = new ilCourseMembershipGUI($this, $this->object);
                $this->ctrl->forwardCommand($mem_gui);
                break;

            case "ilinfoscreengui":
                $this->infoScreen();	// forwards command
                break;

            case 'ilobjectmetadatagui':
                if (!$ilAccess->checkAccess('write', '', $this->object->getRefId())) {
                    $ilErr->raiseError($this->lng->txt('permission_denied'), $ilErr->WARNING);
                }
                $this->tabs_gui->setTabActive('meta_data');
                include_once 'Services/Object/classes/class.ilObjectMetaDataGUI.php';
                $md_gui = new ilObjectMetaDataGUI($this->object);
                $this->ctrl->forwardCommand($md_gui);
                break;

            case 'ilcourseregistrationgui':
                $this->ctrl->setReturn($this, '');
                $this->tabs_gui->setTabActive('join');
                include_once('./Modules/Course/classes/class.ilCourseRegistrationGUI.php');
                $registration = new ilCourseRegistrationGUI($this->object, $this);
                $this->ctrl->forwardCommand($registration);
                break;

            case 'ilobjectcustomuserfieldsgui':
                include_once './Services/Membership/classes/class.ilObjectCustomUserFieldsGUI.php';
                $cdf_gui = new ilObjectCustomUserFieldsGUI($this->object->getId());
                $this->setSubTabs('properties');
                $this->tabs_gui->activateTab('settings');
                $this->tabs_gui->activateSubTab('crs_custom_user_fields');
                $this->ctrl->forwardCommand($cdf_gui);
                break;

            case "ilcourseobjectivesgui":
                include_once './Modules/Course/classes/class.ilCourseObjectivesGUI.php';

                $this->ctrl->setReturn($this, "");
                $reg_gui = new ilCourseObjectivesGUI($this->object->getRefId());
                $ret = &$this->ctrl->forwardCommand($reg_gui);
                break;

            case 'ilobjcoursegroupinggui':
                include_once './Modules/Course/classes/class.ilObjCourseGroupingGUI.php';

                $this->ctrl->setReturn($this, 'edit');
                $this->setSubTabs('properties');
                $this->tabs_gui->activateTab('settings');
                $this->tabs_gui->activateSubTab('groupings');
                $crs_grp_gui = new ilObjCourseGroupingGUI($this->object, (int) $_GET['obj_id']);
                $this->ctrl->forwardCommand($crs_grp_gui);
                break;


            case "ilpropertyformgui":
                // only case is currently adv metadata internal link in info settings, see #24497
                $form = $this->initInfoEditor();
                $this->ctrl->forwardCommand($form);
                break;

            case "ilcolumngui":
                $this->tabs_gui->setTabActive('none');
                $this->checkPermission("read");
                //$this->prepareOutput();
                //include_once("./Services/Style/Content/classes/class.ilObjStyleSheet.php");
                //$this->tpl->setVariable("LOCATION_CONTENT_STYLESHEET",
                //	ilObjStyleSheet::getContentStylePath(0));
                //$this->renderObject();
                $this->viewObject();
                break;

            case "ilconditionhandlergui":
                include_once './Services/Conditions/classes/class.ilConditionHandlerGUI.php';
                // preconditions for whole course
                $this->setSubTabs("properties");
                $this->tabs_gui->activateTab('settings');
                $this->tabs_gui->activateSubTab('preconditions');
                $new_gui = new ilConditionHandlerGUI($this);
                $this->ctrl->forwardCommand($new_gui);
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

            case 'ilpermissiongui':
                include_once("Services/AccessControl/classes/class.ilPermissionGUI.php");
                $this->tabs_gui->setTabActive('perm_settings');
                $perm_gui = new ilPermissionGUI($this);
                $ret = &$this->ctrl->forwardCommand($perm_gui);
                break;

            case 'ilcalendarpresentationgui':
                include_once('./Services/Calendar/classes/class.ilCalendarPresentationGUI.php');
                $cal = new ilCalendarPresentationGUI($this->object->getRefId());
                $ret = $this->ctrl->forwardCommand($cal);
                $header_action = false;
                break;

            case 'ilcoursecontentinterface':

                $this->initCourseContentInterface();
                $this->cci_obj->cci_setContainer($this);

                $this->ctrl->forwardCommand($this->cci_obj);
                $this->setSubTabs('content');
                $this->tabs_gui->setTabActive('content');
                break;

            case 'ilcoursecontentgui':
                $this->ctrl->setReturn($this, 'members');
                include_once './Modules/Course/classes/class.ilCourseContentGUI.php';
                $course_content_obj = new ilCourseContentGUI($this);
                $this->ctrl->forwardCommand($course_content_obj);
                break;

            case 'ilpublicuserprofilegui':
                $this->tpl->enableDragDropFileUpload(null);
                require_once './Services/User/classes/class.ilPublicUserProfileGUI.php';
                $this->setSubTabs('members');
                $this->tabs_gui->setTabActive('members');
                $profile_gui = new ilPublicUserProfileGUI($_GET["user"]);
                $profile_gui->setBackUrl($this->ctrl->getLinkTargetByClass(["ilCourseMembershipGUI", "ilUsersGalleryGUI"], 'view'));
                $this->tabs_gui->setSubTabActive('crs_members_gallery');
                $html = $this->ctrl->forwardCommand($profile_gui);
                $this->tpl->setVariable("ADM_CONTENT", $html);
                break;


            case 'ilmemberagreementgui':
                include_once('Services/Membership/classes/class.ilMemberAgreementGUI.php');
                $this->tabs_gui->clearTargets();

                $this->ctrl->setReturn($this, '');
                $agreement = new ilMemberAgreementGUI($this->object->getRefId());
                $this->ctrl->forwardCommand($agreement);
                break;


            // container page editing
            case "ilcontainerpagegui":
                $ret = $this->forwardToPageObject();
                if ($ret != "") {
                    $this->tpl->setContent($ret);
                }
                $header_action = false;
                break;

            case "ilcontainerstartobjectspagegui":
                // file downloads, etc. (currently not active)
                include_once "Services/Container/classes/class.ilContainerStartObjectsPageGUI.php";
                $pgui = new ilContainerStartObjectsPageGUI($this->object->getId());
                $ret = $this->ctrl->forwardCommand($pgui);
                if ($ret) {
                    $this->tpl->setContent($ret);
                }
                break;

            case 'ilobjectcopygui':
                include_once './Services/Object/classes/class.ilObjectCopyGUI.php';
                $cp = new ilObjectCopyGUI($this);
                $cp->setType('crs');
                $this->ctrl->forwardCommand($cp);
                break;

            case "ilobjstylesheetgui":
                $this->forwardToStyleSheet();
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

            case 'ildidactictemplategui':
                $this->ctrl->setReturn($this, 'edit');
                include_once './Services/DidacticTemplate/classes/class.ilDidacticTemplateGUI.php';
                $did = new ilDidacticTemplateGUI($this);
                $this->ctrl->forwardCommand($did);
                break;

            case "ilcertificategui":
                $this->tabs_gui->activateTab("settings");
                $this->setSubTabs("properties");
                $this->tabs_gui->activateSubTab('certificate');

                $guiFactory = new ilCertificateGUIFactory();
                $output_gui = $guiFactory->create($this->object);
                $this->ctrl->forwardCommand($output_gui);
                break;

            case 'ilobjectservicesettingsgui':
                $this->ctrl->setReturn($this, 'edit');
                $this->setSubTabs("properties");
                $this->tabs_gui->activateTab('settings');
                $this->tabs_gui->acltivateSubTab('tool_settings');

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

            case 'illoeditorgui':
                #$this->tabs_gui->clearTargets();
                #$this->tabs_gui->setBackTarget($this->lng->txt('back'),$this->ctrl->getLinkTarget($this,''));
                $this->tabs_gui->activateTab('crs_objectives');

                include_once './Modules/Course/classes/Objectives/class.ilLOEditorGUI.php';
                $editor = new ilLOEditorGUI($this->object);
                $this->ctrl->forwardCommand($editor);
                if (strtolower($this->ctrl->getCmdClass()) === "illopagegui") {
                    $header_action = false;
                }
                break;

            case 'ilcontainerstartobjectsgui':
                $this->ctrl->setReturn($this, 'edit');
                $this->tabs_gui->clearTargets();
                $this->tabs_gui->setBackTarget(
                    $this->lng->txt("back_to_crs_content"),
                    $this->ctrl->getLinkTarget($this, "edit")
                );
                $this->tabs_gui->addTab(
                    "start",
                    $this->lng->txt("crs_start_objects"),
                    $this->ctrl->getLinkTargetByClass("ilcontainerstartobjectsgui", "listStructure")
                );
                if (strtolower($this->ctrl->getCmdClass()) ==
                    "ilcontainerstartobjectspagegui") {
                    $header_action = false;
                }
                global $DIC;

                $ilHelp = $DIC['ilHelp'];
                $ilHelp->setScreenIdComponent("crs");

                include_once './Services/Container/classes/class.ilContainerStartObjectsGUI.php';
                $stgui = new ilContainerStartObjectsGUI($this->object);
                $this->ctrl->forwardCommand($stgui);
                break;

            case 'illomembertestresultgui':
                include_once './Modules/Course/classes/Objectives/class.ilLOMemberTestResultGUI.php';
                $GLOBALS['DIC']['ilCtrl']->setReturn($this, 'members');
                $GLOBALS['DIC']['ilTabs']->clearTargets();
                $GLOBALS['DIC']['ilTabs']->setBackTarget(
                    $GLOBALS['DIC']['lng']->txt('back'),
                    $GLOBALS['DIC']['ilCtrl']->getLinkTarget($this, 'members')
                );

                $result_view = new ilLOMemberTestResultGUI($this, $this->object, (int) $_REQUEST['uid']);
                $this->ctrl->forwardCommand($result_view);
                break;

            case 'ilmailmembersearchgui':
                include_once 'Services/Mail/classes/class.ilMail.php';
                $mail = new ilMail($ilUser->getId());

                if (
                    !($this->object->getMailToMembersType() == ilCourseConstants::MAIL_ALLOWED_ALL ||
                    $ilAccess->checkAccess('manage_members', "", $this->object->getRefId())) &&
                    $rbacsystem->checkAccess('internal_mail', $mail->getMailObjectReferenceId())) {
                    $ilErr->raiseError($this->lng->txt("msg_no_perm_read"), $ilErr->MESSAGE);
                }

                $this->tabs_gui->setTabActive('members');

                include_once './Services/Contact/classes/class.ilMailMemberSearchGUI.php';
                include_once './Services/Contact/classes/class.ilMailMemberCourseRoles.php';

                $mail_search = new ilMailMemberSearchGUI($this, $this->object->getRefId(), new ilMailMemberCourseRoles());
                $mail_search->setObjParticipants(
                    ilCourseParticipants::_getInstanceByObjId($this->object->getId())
                );
                $this->ctrl->forwardCommand($mail_search);
                break;

            case 'ilbadgemanagementgui':
                $this->tabs_gui->setTabActive('obj_tool_setting_badges');
                include_once 'Services/Badge/classes/class.ilBadgeManagementGUI.php';
                $bgui = new ilBadgeManagementGUI($this->object->getRefId(), $this->object->getId(), 'crs');
                $this->ctrl->forwardCommand($bgui);
                break;

            case "ilcontainernewssettingsgui":
                $this->setSubTabs("properties");
                $this->tabs_gui->activateTab('settings');
                $this->tabs_gui->activateSubTab('obj_news_settings');
                $news_set_gui = new ilContainerNewsSettingsGUI($this);
                $news_set_gui->setTimeline(true);
                $news_set_gui->setCronNotifications(true);
                $news_set_gui->setHideByDate(true);
                $this->ctrl->forwardCommand($news_set_gui);
                break;

            case "ilnewstimelinegui":
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
                    'crs'
                );
                break;

            case 'ilmemberexportsettingsgui':
                $this->setSubTabs('properties');
                $this->tabs_gui->activateTab('properties');
                $this->tabs_gui->activateSubTab('export_members');
                include_once './Services/Membership/classes/Export/class.ilMemberExportSettingsGUI.php';
                $settings_gui = new ilMemberExportSettingsGUI($this->object->getType(), $this->object->getId());
                $this->ctrl->forwardCommand($settings_gui);
                break;


            case "ilcontainerskillgui":
                $this->tabs_gui->activateTab('obj_tool_setting_skills');
                include_once("./Services/Container/Skills/classes/class.ilContainerSkillGUI.php");
                $gui = new ilContainerSkillGUI($this);
                $this->ctrl->forwardCommand($gui);
                break;


            case 'ilobjecttranslationgui':
                $this->checkPermissionBool("write");
                $this->setSubTabs("properties");
                $this->tabs_gui->activateTab("settings");
                $this->tabs_gui->activateSubTab("obj_multilinguality");
                include_once("./Services/Object/classes/class.ilObjectTranslationGUI.php");
                $transgui = new ilObjectTranslationGUI($this);
                $this->ctrl->forwardCommand($transgui);
                break;

            case "ilbookinggatewaygui":
                $this->tabs_gui->activateTab('obj_tool_setting_booking');
                $gui = new ilBookingGatewayGUI($this);
                $this->ctrl->forwardCommand($gui);
                break;

            default:
/*                if(!$this->creation_mode)
                {
                    $this->checkPermission('visible');
                }*/
                /*
                if(!$this->creation_mode and !$ilAccess->checkAccess('visible','',$this->object->getRefId(),'crs'))
                {
                    $ilErr->raiseError($this->lng->txt("msg_no_perm_read"),$ilErr->MESSAGE);
                }
                */

                // #9401 - see also ilStartupGUI::_checkGoto()
                if ($cmd == 'infoScreenGoto') {
                    if (ilObjCourse::_isActivated($this->object->getId()) &&
                        ilObjCourse::_registrationEnabled($this->object->getId())) {
                        $cmd = 'join';
                    } else {
                        $cmd = 'infoScreen';
                    }
                }

                if (!$this->creation_mode) {
                    if ($cmd == "infoScreen") {
                        $this->checkPermission("visible");
                    } else {
                        //						$this->checkPermission("read");
                    }
                }


                if (!$this->creation_mode
                    && $cmd != 'infoScreen'
                    && $cmd != 'sendfile'
                    && $cmd != 'unsubscribe'
                    && $cmd != 'deliverCertificate'
                    && $cmd != 'performUnsubscribe'
                    && $cmd != 'removeFromDesk'
                    && $cmd !== 'leave'
                    && !$ilAccess->checkAccess("read", '', $this->object->getRefId())
                    || $cmd == 'join'
                    || $cmd == 'subscribe') {
                    include_once './Modules/Course/classes/class.ilCourseParticipants.php';
                    if ($rbacsystem->checkAccess('join', $this->object->getRefId()) &&
                        !ilCourseParticipants::_isParticipant($this->object->getRefId(), $ilUser->getId())) {
                        include_once('./Modules/Course/classes/class.ilCourseRegistrationGUI.php');
                        $this->ctrl->redirectByClass("ilCourseRegistrationGUI");
                    } else {
                        $this->infoScreenObject();
                        break;
                    }
                }

                if ($cmd == 'listObjectives') {
                    include_once './Modules/Course/classes/class.ilCourseObjectivesGUI.php';

                    $this->ctrl->setReturn($this, "");
                    $obj_gui = new ilCourseObjectivesGUI($this->object->getRefId());
                    $ret = &$this->ctrl->forwardCommand($obj_gui);
                    break;
                }

                // cognos-blu-patch: begin
                // cognos-blu-patch: end

                // if news timeline is landing page, redirect if necessary
                if ($cmd == "" && $this->object->isNewsTimelineLandingPageEffective()) {
                    $this->ctrl->redirectbyclass("ilnewstimelinegui");
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

        return true;
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

        // Disable aggrement if is not member of course
        if (!$this->object->getMemberObject()->isAssigned()) {
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
        if (($privacy->courseConfirmationRequired() or ilCourseDefinedFieldDefinition::_hasFields($this->object->getId()))
            and !ilMemberAgreement::_hasAccepted($ilUser->getId(), $this->object->getId())) {
            $GLOBALS['DIC']['ilLog']->write(__METHOD__ . ': Missing course confirmation.');
            return false;
        }
        // Check required fields
        include_once('Modules/Course/classes/Export/class.ilCourseUserData.php');
        if (!ilCourseUserData::_checkRequired($ilUser->getId(), $this->object->getId())) {
            $GLOBALS['DIC']['ilLog']->write(__METHOD__ . ': Missing required fields');
            return false;
        }
        return true;
    }

    // STATIC
    public static function _forwards()
    {
        return array("ilCourseRegisterGUI",'ilConditionHandlerGUI');
    }

    public function addLocatorItems()
    {
        global $DIC;

        $ilLocator = $DIC['ilLocator'];
        switch ($this->ctrl->getCmd()) {
            default:
                #$ilLocator->addItem($this->object->getTitle(), $this->ctrl->getLinkTarget($this, ""));
                break;
        }
    }

    /**
     * Called from goto?
     */
    protected function membersObject()
    {
        $GLOBALS['DIC']['ilCtrl']->redirectByClass('ilcoursemembershipgui');
    }

    /**
    * goto target course
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

            // Redirects to target location after assigning user to course
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
    * Edit Map Settings
    */
    public function editMapSettingsObject()
    {
        global $DIC;

        $ilUser = $DIC['ilUser'];
        $ilCtrl = $DIC['ilCtrl'];
        $ilUser = $DIC['ilUser'];
        $ilAccess = $DIC['ilAccess'];

        $this->setSubTabs("properties");
        $this->tabs_gui->activateTab('settings');
        $this->tabs_gui->activateSubTab('crs_map_settings');

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

        $form->setTitle($this->lng->txt("crs_map_settings"));

        // enable map
        $public = new ilCheckboxInputGUI(
            $this->lng->txt("crs_enable_map"),
            "enable_map"
        );
        $public->setValue("1");
        $public->setChecked($this->object->getEnableCourseMap());
        $form->addItem($public);

        // map location
        $loc_prop = new ilLocationInputGUI(
            $this->lng->txt("crs_map_location"),
            "location"
        );
        $loc_prop->setLatitude($latitude);
        $loc_prop->setLongitude($longitude);
        $loc_prop->setZoom($zoom);
        $form->addItem($loc_prop);

        $form->addCommandButton("saveMapSettings", $this->lng->txt("save"));

        $this->tpl->setVariable("ADM_CONTENT", $form->getHTML());
        //$this->tpl->show();
    }

    public function saveMapSettingsObject()
    {
        global $DIC;

        $ilCtrl = $DIC['ilCtrl'];
        $ilUser = $DIC['ilUser'];

        $this->object->setLatitude(ilUtil::stripSlashes($_POST["location"]["latitude"]));
        $this->object->setLongitude(ilUtil::stripSlashes($_POST["location"]["longitude"]));
        $this->object->setLocationZoom(ilUtil::stripSlashes($_POST["location"]["zoom"]));
        $this->object->setEnableCourseMap(ilUtil::stripSlashes($_POST["enable_map"]));
        $this->object->update();

        $ilCtrl->redirect($this, "editMapSettings");
    }


    /**
     * Modify Item ListGUI for presentation in container
     * @param type $a_item_list_gui
     * @param type $a_item_data
     * @param type $a_show_path
     * @return type
     */
    public function modifyItemGUI($a_item_list_gui, $a_item_data, $a_show_path)
    {
        return ilObjCourseGUI::_modifyItemGUI(
            $a_item_list_gui,
            'ilcoursecontentgui',
            $a_item_data,
            $a_show_path,
            $this->object->getAboStatus(),
            $this->object->getRefId(),
            $this->object->getId()
        );
    }

    /**
    * We need a static version of this, e.g. in folders of the course
    */
    public static function _modifyItemGUI(
        $a_item_list_gui,
        $a_cmd_class,
        $a_item_data,
        $a_show_path,
        $a_abo_status,
        $a_course_ref_id,
        $a_course_obj_id,
        $a_parent_ref_id = 0
    ) {
        global $DIC;

        $lng = $DIC['lng'];
        $ilAccess = $DIC['ilAccess'];

        // this is set for folders within the course
        if ($a_parent_ref_id == 0) {
            $a_parent_ref_id = $a_course_ref_id;
        }

        // Special handling for tests in courses with learning objectives
        if ($a_item_data['type'] == 'tst' and
            ilObjCourse::_lookupViewMode($a_course_obj_id) == ilContainer::VIEW_OBJECTIVE) {
            $a_item_list_gui->addCommandLinkParameter(array('crs_show_result' => $a_course_ref_id));
        }

        $a_item_list_gui->enableSubscribe($a_abo_status);

        $is_tutor = ($ilAccess->checkAccess(
            'write',
            '',
            $a_course_ref_id,
            'crs',
            $a_course_obj_id
        ));

        if ($a_show_path and $is_tutor) {
            $a_item_list_gui->addCustomProperty(
                $lng->txt('path'),
                ilContainer::_buildPath($a_item_data['ref_id'], $a_course_ref_id),
                false,
                true
            );
        }
    }

    /**
    * Set content sub tabs
    */
    public function setContentSubTabs()
    {
        global $DIC;

        $ilAccess = $DIC['ilAccess'];
        $lng = $DIC['lng'];
        $ilCtrl = $DIC['ilCtrl'];

        if ($this->object->getType() != 'crs') {
            return true;
        }
        if (!$ilAccess->checkAccess(
            'write',
            '',
            $this->object->getRefId(),
            'crs',
            $this->object->getId()
        )) {
            $is_tutor = false;
            // No further tabs if objective view or archives
            if ($this->object->enabledObjectiveView()) {
                return false;
            }
        } else {
            $is_tutor = true;
        }

        // These subtabs should also work, if the command is called directly in
        // ilObjCourseGUI, so please use ...ByClass methods.
        // (see ilObjCourseGUI->executeCommand: case "ilcolumngui")

        if (!$_SESSION['crs_timings_panel'][$this->object->getId()] or 1) {
            if (!$this->isActiveAdministrationPanel()) {
                $this->tabs_gui->addSubTab("view_content", $lng->txt("view"), $ilCtrl->getLinkTargetByClass("ilobjcoursegui", "view"));
            } else {
                $this->tabs_gui->addSubTab("view_content", $lng->txt("view"), $ilCtrl->getLinkTargetByClass("ilobjcoursegui", "disableAdministrationPanel"));
            }
        }
        // cognos-blu-patch: begin
        // cognos-blu-patch: begin

        $this->addStandardContainerSubTabs(false);


        return true;
    }

    /**
     * load date
     *
     * @access protected
     * @param
     * @return
     */
    protected function loadDate($a_field)
    {
        global $DIC;

        $ilUser = $DIC['ilUser'];

        include_once('./Services/Calendar/classes/class.ilDateTime.php');

        // #10206 / #10217
        if (is_array($_POST[$a_field]['date'])) {
            $dt['year'] = (int) $_POST[$a_field]['date']['y'];
            $dt['mon'] = (int) $_POST[$a_field]['date']['m'];
            $dt['mday'] = (int) $_POST[$a_field]['date']['d'];
            $dt['hours'] = (int) $_POST[$a_field]['time']['h'];
            $dt['minutes'] = (int) $_POST[$a_field]['time']['m'];
            $dt['seconds'] = (int) $_POST[$a_field]['time']['s'];
        } else {
            $date = date_parse($_POST[$a_field]['date'] . " " . $_POST[$a_field]['time']);
            $dt['year'] = (int) $date['year'];
            $dt['mon'] = (int) $date['month'];
            $dt['mday'] = (int) $date['day'];
            $dt['hours'] = (int) $date['hour'];
            $dt['minutes'] = (int) $date['minute'];
            $dt['seconds'] = (int) $date['second'];
        }

        $date = new ilDateTime($dt, IL_CAL_FKT_GETDATE, $ilUser->getTimeZone());
        return $date;
    }

    /**
     * ask reset test results
     *
     * @access public
     * @param
     * @return
     */
    public function askResetObject()
    {
        ilUtil::sendQuestion($this->lng->txt('crs_objectives_reset_sure'));

        include_once './Services/Utilities/classes/class.ilConfirmationGUI.php';
        $confirm = new ilConfirmationGUI();
        $confirm->setFormAction($this->ctrl->getFormAction($this));
        $confirm->setConfirm($this->lng->txt('reset'), 'reset');
        $confirm->setCancel($this->lng->txt('cancel'), 'cancel');

        $GLOBALS['DIC']['tpl']->setContent($confirm->getHTML());
        return true;
    }

    public function resetObject()
    {
        global $DIC;

        $ilUser = $DIC['ilUser'];

        include_once './Modules/Course/classes/Objectives/class.ilLOUserResults.php';
        $usr_results = new ilLOUserResults($this->object->getId(), $GLOBALS['DIC']['ilUser']->getId());
        $usr_results->delete();


        include_once './Modules/Course/classes/Objectives/class.ilLOTestRun.php';
        include_once './Modules/Course/classes/Objectives/class.ilLOSettings.php';
        ilLOTestRun::deleteRuns(
            $this->object->getId(),
            $GLOBALS['DIC']['ilUser']->getId()
        );

        include_once './Modules/Course/classes/class.ilCourseObjectiveResult.php';

        $tmp_obj_res = new ilCourseObjectiveResult($ilUser->getId());
        $tmp_obj_res->reset($this->object->getId());

        $ilUser->deletePref('crs_objectives_force_details_' . $this->object->getId());

        ilUtil::sendSuccess($this->lng->txt('crs_objectives_reseted'));
        $this->viewObject();
    }

    public function __checkStartObjects()
    {
        global $DIC;

        $ilAccess = $DIC['ilAccess'];
        $ilUser = $DIC['ilUser'];

        if ($ilAccess->checkAccess('write', '', $this->object->getRefId())) {
            return true;
        }

        include_once './Services/Container/classes/class.ilContainerStartObjects.php';
        $this->start_obj = new ilContainerStartObjects(
            $this->object->getRefId(),
            $this->object->getId()
        );
        if (count($this->start_obj->getStartObjects()) &&
            !$this->start_obj->allFullfilled($ilUser->getId())) {
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
            include_once './Services/Container/classes/class.ilMemberViewSettings.php';
            $settings = ilMemberViewSettings::getInstance();
            if ($settings->isActive() and $settings->getContainer() != $this->object->getRefId()) {
                $settings->setContainer($this->object->getRefId());
                $rbacsystem->initMemberView();
            }
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
        $link .= $this->lng->txt('crs_mail_permanent_link');
        $link .= chr(13) . chr(10) . chr(13) . chr(10);
        include_once './Services/Link/classes/class.ilLink.php';
        $link .= ilLink::_getLink($this->object->getRefId());
        return rawurlencode(base64_encode($link));
    }

    protected function initHeaderAction($a_sub_type = null, $a_sub_id = null)
    {
        global $DIC;

        $ilUser = $DIC->user();

        $lg = parent::initHeaderAction($a_sub_type, $a_sub_id);

        if ($lg && $this->ref_id && ilCourseParticipants::_isParticipant($this->ref_id, $ilUser->getId())) {
            // certificate

            $validator = new ilCertificateDownloadValidator();
            if (true === $validator->isCertificateDownloadable($ilUser->getId(), $this->object->getId())) {
                $cert_url = $this->ctrl->getLinkTarget($this, "deliverCertificate");

                $this->lng->loadLanguageModule("certificate");
                $lg->addCustomCommand($cert_url, "download_certificate");

                $lg->addHeaderIcon(
                    "cert_icon",
                    ilUtil::getImagePath("icon_cert.svg"),
                    $this->lng->txt("download_certificate"),
                    null,
                    null,
                    $cert_url
                );
            }

            // notification
            include_once "Services/Membership/classes/class.ilMembershipNotifications.php";
            if (ilMembershipNotifications::isActiveForRefId($this->ref_id)) {
                $noti = new ilMembershipNotifications($this->ref_id);
                if (!$noti->isCurrentUserActive()) {
                    $lg->addHeaderIcon(
                        "not_icon",
                        ilUtil::getImagePath("notification_off.svg"),
                        $this->lng->txt("crs_notification_deactivated")
                    );

                    $this->ctrl->setParameter($this, "crs_ntf", 1);
                    $caption = "crs_activate_notification";
                } else {
                    $lg->addHeaderIcon(
                        "not_icon",
                        ilUtil::getImagePath("notification_on.svg"),
                        $this->lng->txt("crs_notification_activated")
                    );

                    $this->ctrl->setParameter($this, "crs_ntf", 0);
                    $caption = "crs_deactivate_notification";
                }

                if ($noti->canCurrentUserEdit()) {
                    $lg->addCustomCommand(
                        $this->ctrl->getLinkTarget($this, "saveNotification"),
                        $caption
                    );
                }

                $this->ctrl->setParameter($this, "crs_ntf", "");
            }
        }

        return $lg;
    }

    public function deliverCertificateObject()
    {
        global $DIC;

        $ilUser = $DIC['ilUser'];
        $ilAccess = $DIC['ilAccess'];

        $user_id = null;
        if ($ilAccess->checkAccess('manage_members', '', $this->ref_id)) {
            $user_id = $_REQUEST["member_id"];
        }
        if (!$user_id) {
            $user_id = $ilUser->getId();
        }

        $objId = (int) $this->object->getId();

        $validator = new ilCertificateDownloadValidator();

        if (false === $validator->isCertificateDownloadable($user_id, $objId)) {
            ilUtil::sendFailure($this->lng->txt("permission_denied"), true);
            $this->ctrl->redirect($this);
        }

        $repository = new ilUserCertificateRepository();

        $certLogger = $DIC->logger()->cert();
        $pdfGenerator = new ilPdfGenerator($repository, $certLogger);

        $pdfAction = new ilCertificatePdfAction(
            $certLogger,
            $pdfGenerator,
            new ilCertificateUtilHelper(),
            $this->lng->txt('error_creating_certificate_pdf')
        );

        $pdfAction->downloadPdf((int) $user_id, $objId);
    }


    protected function afterSaveCallback()
    {
        $this->ctrl->redirectByClass(array('ilrepositorygui','ilobjcoursegui','illoeditorgui'), 'materials');
    }

    public function saveSortingObject()
    {
        if (isset($_POST['position']["lobj"])) {
            $lobj = $_POST['position']["lobj"];
            unset($_POST['position']["lobj"]);

            $objective_order = array();
            foreach ($lobj as $objective_id => $materials) {
                $objective_order[$objective_id] = $materials[0];
                unset($lobj[$objective_id][0]);
            }

            // objective order
            include_once "Modules/Course/classes/class.ilCourseObjective.php";
            asort($objective_order);
            $pos = 0;
            foreach (array_keys($objective_order) as $objective_id) {
                $obj = new ilCourseObjective($this->object, $objective_id);
                $obj->writePosition(++$pos);
            }

            // material order
            include_once "Modules/Course/classes/class.ilCourseObjectiveMaterials.php";
            foreach ($lobj as $objective_id => $materials) {
                $objmat = new ilCourseObjectiveMaterials($objective_id);

                asort($materials);
                $pos = 0;
                foreach (array_keys($materials) as $ass_id) {
                    $objmat->writePosition($ass_id, ++$pos);
                }
            }
        }

        return parent::saveSortingObject();
    }

    /**
     *
     * @return booleanRedirect ot test after confirmation of resetting completed objectives
     */
    protected function redirectLocToTestConfirmedObject()
    {
        include_once './Services/Link/classes/class.ilLink.php';
        ilUtil::redirect(ilLink::_getLink((int) $_REQUEST['tid']));
        return true;
    }

    /**
     * Test redirection will be moved lo adapter
     */
    protected function redirectLocToTestObject($a_force_new_run = null)
    {
        $objective_id = (int) $_REQUEST['objective_id'];
        $test_id = (int) $_REQUEST['tid'];

        include_once './Modules/Course/classes/Objectives/class.ilLOUserResults.php';
        include_once './Modules/Course/classes/Objectives/class.ilLOSettings.php';
        include_once './Modules/Course/classes/Objectives/class.ilLOTestAssignments.php';


        $res = new ilLOUserResults(
            $this->object->getId(),
            $GLOBALS['DIC']['ilUser']->getId()
        );
        $passed = $res->getCompletedObjectiveIds();

        $has_completed = false;
        if ($objective_id) {
            $objective_ids = array($objective_id);
            if (in_array($objective_id, $passed)) {
                $has_completed = true;
                $passed = array();
            }
        } else {
            include_once './Modules/Course/classes/class.ilCourseObjective.php';
            $objective_ids = ilCourseObjective::_getObjectiveIds($this->object->getId(), true);

            // do not disable objective question if all are passed
            if (count($objective_ids) == count($passed)) {
                $has_completed = true;
                $passed = array();
            }
        }

        if ($has_completed) {
            // show confirmation
            $this->redirectLocToTestConfirmation($objective_id, $test_id);
            return true;
        }

        include_once './Services/Link/classes/class.ilLink.php';
        ilUtil::redirect(ilLink::_getLink($test_id));
        return true;
    }

    /**
     * Show confirmation whether user wants to start a new run or resume a previous run
     * @param type $a_objective_id
     * @param type $a_test_id
     */
    protected function redirectLocToTestConfirmation($a_objective_id, $a_test_id)
    {
        include_once './Services/Utilities/classes/class.ilConfirmationGUI.php';
        $confirm = new ilConfirmationGUI();
        $confirm->setFormAction($GLOBALS['DIC']['ilCtrl']->getFormAction($this));

        if ($a_objective_id) {
            $question = $this->lng->txt('crs_loc_objective_passed_confirmation');
        } else {
            $question = $this->lng->txt('crs_loc_objectives_passed_confirmation');
        }

        $confirm->addHiddenItem('objective_id', $a_objective_id);
        $confirm->addHiddenItem('tid', $a_test_id);
        $confirm->setConfirm($this->lng->txt('crs_loc_tst_start'), 'redirectLocToTestConfirmed');
        $confirm->setCancel($this->lng->txt('cancel'), 'view');

        ilUtil::sendQuestion($question);

        $GLOBALS['DIC']['tpl']->setContent($confirm->getHTML());
        return true;
    }
    // end-patch lok

    /**
     *
     * @var int[] $a_exclude a list of role ids which will not added to the results (optional)
     * returns all local roles [role_id] => title
     * @return array localroles
     */
    public function getLocalRoles($a_exclude = array())
    {
        $crs_admin = $this->object->getDefaultAdminRole();
        $crs_member = $this->object->getDefaultMemberRole();
        $local_roles = $this->object->getLocalCourseRoles(false);
        $crs_roles = array();

        //put the course member role to the top of the crs_roles array
        if (in_array($crs_member, $local_roles)) {
            #$crs_roles[$crs_member] = ilObjRole::_getTranslation(array_search ($crs_member, $local_roles));
            #unset($local_roles[$crs_roles[$crs_member]]);
        }

        foreach ($local_roles as $title => $role_id) {
            if ($role_id == $crs_admin && !$this->hasAdminPermission()) {
                continue;
            }

            $crs_roles[$role_id] = ilObjRole::_getTranslation($title);
        }

        if (count($a_exclude) > 0) {
            foreach ($a_exclude as $excluded_role) {
                if (isset($crs_roles[$excluded_role])) {
                    unset($crs_roles[$excluded_role]);
                }
            }
        }
        return $crs_roles;
    }

    /**
     * user has admin permission or "edit permission" permission on this course
     * @return bool
     */
    protected function hasAdminPermission()
    {
        global $DIC;

        $ilUser = $DIC['ilUser'];
        return ilCourseParticipant::_getInstanceByObjId($this->object->getId(), $ilUser->getId())->isAdmin()
        or $this->checkPermissionBool('edit_permission');
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
} // END class.ilObjCourseGUI
