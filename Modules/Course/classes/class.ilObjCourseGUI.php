<?php

declare(strict_types=0);

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
 * Class ilObjCourseGUI
 * @author       Stefan Meyer <smeyer.ilias@gmx.de>
 * $Id$
 * @ilCtrl_Calls ilObjCourseGUI: ilCourseRegistrationGUI, ilCourseObjectivesGUI
 * @ilCtrl_Calls ilObjCourseGUI: ilObjCourseGroupingGUI, ilInfoScreenGUI, ilLearningProgressGUI, ilPermissionGUI
 * @ilCtrl_Calls ilObjCourseGUI: ilRepositorySearchGUI, ilConditionHandlerGUI
 * @ilCtrl_Calls ilObjCourseGUI: ilCourseContentGUI, ilPublicUserProfileGUI, ilMemberExportGUI
 * @ilCtrl_Calls ilObjCourseGUI: ilObjectCustomUserFieldsGUI, ilMemberAgreementGUI, ilSessionOverviewGUI
 * @ilCtrl_Calls ilObjCourseGUI: ilColumnGUI, ilContainerPageGUI
 * @ilCtrl_Calls ilObjCourseGUI: ilObjectCopyGUI, ilObjectContentStyleSettingsGUI
 * @ilCtrl_Calls ilObjCourseGUI: ilCourseParticipantsGroupsGUI, ilExportGUI, ilCommonActionDispatcherGUI
 * @ilCtrl_Calls ilObjCourseGUI: ilDidacticTemplateGUI, ilCertificateGUI, ilObjectServiceSettingsGUI
 * @ilCtrl_Calls ilObjCourseGUI: ilContainerStartObjectsGUI, ilContainerStartObjectsPageGUI
 * @ilCtrl_Calls ilObjCourseGUI: ilMailMemberSearchGUI, ilBadgeManagementGUI
 * @ilCtrl_Calls ilObjCourseGUI: ilLOPageGUI, ilObjectMetaDataGUI, ilNewsTimelineGUI, ilContainerNewsSettingsGUI
 * @ilCtrl_Calls ilObjCourseGUI: ilCourseMembershipGUI, ilPropertyFormGUI, ilContainerSkillGUI, ilCalendarPresentationGUI
 * @ilCtrl_Calls ilObjCourseGUI: ilMemberExportSettingsGUI
 * @ilCtrl_Calls ilObjCourseGUI: ilLTIProviderObjectSettingGUI, ilObjectTranslationGUI, ilBookingGatewayGUI, ilRepositoryTrashGUI
 * @extends      ilContainerGUI
 */
class ilObjCourseGUI extends ilContainerGUI
{
    public const BREADCRUMB_DEFAULT = 0;
    public const BREADCRUMB_CRS_ONLY = 1;
    public const BREADCRUMB_FULL_PATH = 2;

    private ?ilAdvancedMDRecordGUI $record_gui = null;
    private ?ilContainerStartObjects $start_obj = null;

    private ilLogger $logger;
    protected GlobalHttpState $http;
    protected Factory $refinery;
    protected ilHelpGUI $help;
    protected ilNavigationHistory $navigation_history;

    public function __construct($a_data, int $a_id, bool $a_call_by_reference = true, bool $a_prepare_output = true)
    {
        global $DIC;

        $this->type = "crs";
        parent::__construct($a_data, $a_id, $a_call_by_reference, $a_prepare_output);

        $this->help = $DIC->help();
        $this->logger = $DIC->logger()->crs();
        $this->navigation_history = $DIC['ilNavigationHistory'];
        $this->ctrl->saveParameter($this, ['ref_id']);
        $this->lng->loadLanguageModule('crs');
        $this->lng->loadLanguageModule('cert');
        $this->lng->loadLanguageModule('obj');

        $this->http = $DIC->http();
        $this->refinery = $DIC->refinery();
    }

    /**
     * @todo  check deletable
     */
    public function gatewayObject(): void
    {
        $this->viewObject();
    }

    /**
     * @inheritDoc
     */
    protected function afterImport(ilObject $new_object): void
    {
        $part = ilCourseParticipants::_getInstanceByObjId($new_object->getId());
        $part->add($this->user->getId(), ilCourseConstants::CRS_ADMIN);
        $part->updateNotification(
            $this->user->getId(),
            (bool) $this->settings->get('mail_crs_admin_notification', '1')
        );
        parent::afterImport($new_object);
    }

    public function renderObject(): void
    {
        $this->ctrl->setCmd("view");
        $this->viewObject();
    }

    public function viewObject(): void
    {
        if (strtolower($this->std_request->getBaseClass()) === "iladministrationgui") {
            parent::viewObject();
            return;
        }

        $this->tabs_gui->setTabActive('view_content');
        $this->checkPermission('read', 'view');
        if ($this->view_manager->isAdminView()) {
            parent::renderObject();
            return;
        }

        // Fill meta header tags
        ilMDUtils::_fillHTMLMetaTags($this->object->getId(), $this->object->getId(), 'crs');

        // Trac access
        if ($this->ctrl->getNextClass() != "ilcolumngui") {
            ilLearningProgress::_tracProgress(
                $this->user->getId(),
                $this->object->getId(),
                $this->object->getRefId(),
                'crs'
            );
        }

        if (!$this->checkAgreement()) {
            $this->tabs_gui->clearTargets();
            $this->ctrl->setReturn($this, 'view_content');
            $agreement = new ilMemberAgreementGUI($this->object->getRefId());
            $this->ctrl->setCmdClass(get_class($agreement));
            $this->ctrl->forwardCommand($agreement);
            return;
        }

        if (!$this->__checkStartObjects()) {
            /** @var ilContainer $obj */
            $obj = $this->object;
            $stgui = new ilContainerStartObjectsContentGUI($this, $obj);
            $stgui->enableDesktop($this->object->getAboStatus(), $this);
            $stgui->getHTML();
            return;
        }
        // views handled by general container logic
        if (
            $this->object->getViewMode() == ilContainer::VIEW_SIMPLE ||
            $this->object->getViewMode() == ilContainer::VIEW_BY_TYPE ||
            $this->object->getViewMode() == ilContainer::VIEW_SESSIONS ||
            $this->object->getViewMode() == ilContainer::VIEW_TIMING ||
            $this->object->getViewMode() == ilContainer::VIEW_OBJECTIVE
        ) {
            parent::renderObject();
        } else {
            $course_content_obj = new ilCourseContentGUI($this);
            $this->ctrl->setCmdClass(get_class($course_content_obj));
            $this->ctrl->forwardCommand($course_content_obj);
        }
    }

    public function renderContainer(): void
    {
        parent::renderObject();
    }

    /**
     * this one is called from the info button in the repository
     * not very nice to set cmdClass/Cmd manually, if everything
     * works through ilCtrl in the future this may be changed
     * @todo
     */
    public function infoScreenObject(): void
    {
        $this->ctrl->setCmd("showSummary");
        $this->ctrl->setCmdClass("ilinfoscreengui");
        $this->infoScreen();
    }

    public function infoScreen(): void
    {
        if (!$this->checkPermissionBool('read')) {
            $this->checkPermission('visible');
        }

        // Fill meta header tags
        ilMDUtils::_fillHTMLMetaTags($this->object->getId(), $this->object->getId(), 'crs');

        $this->tabs_gui->setTabActive('info_short');
        $files = ilCourseFile::_readFilesByCourse($this->object->getId());

        $info = new ilInfoScreenGUI($this);
        $info->enablePrivateNotes();
        $info->enableFeedback();
        $info->enableNews();
        $info->enableBookingInfo(true);
        if ($this->access->checkAccess("write", "", $this->ref_id)) {
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
        if ($files !== []) {
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
            $emails = explode(",", $this->object->getContactEmail());
            $mailString = '';
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
        if ($conts !== []) {
            $info->addSection($this->lng->txt("crs_mem_contacts"));
            foreach ($conts as $c) {
                $pgui = new ilPublicUserProfileGUI($c);
                $pgui->setBackUrl($this->ctrl->getLinkTargetByClass("ilinfoscreengui"));
                $pgui->setEmbedded(true);
                $info->addProperty("", $pgui->getHTML());
            }
        }

        // #10360
        $info->enableAvailability(false);
        $this->lng->loadLanguageModule("rep");
        $info->addSection($this->lng->txt("rep_activation_availability"));
        $info->showLDAPRoleGroupMappingInfo();

        // activation
        $info->addAccessPeriodProperty();

        $txt = '';
        switch ($this->object->getSubscriptionLimitationType()) {
            case ilCourseConstants::IL_CRS_SUBSCRIPTION_DEACTIVATED:
                $txt = $this->lng->txt("crs_info_reg_deactivated");
                break;

            default:
                switch ($this->object->getSubscriptionType()) {
                    case ilCourseConstants::IL_CRS_SUBSCRIPTION_CONFIRMATION:
                        $txt = $this->lng->txt("crs_info_reg_confirmation");
                        break;
                    case ilCourseConstants::IL_CRS_SUBSCRIPTION_DIRECT:
                        $txt = $this->lng->txt("crs_info_reg_direct");
                        break;
                    case ilCourseConstants::IL_CRS_SUBSCRIPTION_PASSWORD:
                        $txt = $this->lng->txt("crs_info_reg_password");
                        break;
                }
        }

        // subscription
        $info->addProperty($this->lng->txt("crs_info_reg"), $txt);
        if ($this->object->getSubscriptionLimitationType() != ilCourseConstants::IL_CRS_SUBSCRIPTION_DEACTIVATED) {
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
                    $reg_info = ilObjCourseAccess::lookupRegistrationInfo($this->object->getId());
                    $info->addProperty(
                        $this->lng->txt('mem_free_places'),
                        (string) ($reg_info['reg_info_free_places'] ?? '0')
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
        $privacy = ilPrivacySettings::getInstance();

        if ($privacy->courseConfirmationRequired() || ilCourseDefinedFieldDefinition::_getFields($this->object->getId()) || $privacy->enabledCourseExport()) {
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

    public function saveNotificationObject(): void
    {
        $noti = new ilMembershipNotifications($this->ref_id);
        if ($noti->canCurrentUserEdit()) {
            $crs_ntf = false;
            if ($this->http->wrapper()->query()->has('crs_ntf')) {
                $crs_ntf = $this->http->wrapper()->query()->retrieve(
                    'crs_ntf',
                    $this->refinery->kindlyTo()->bool()
                );
            }
            if ($crs_ntf) {
                $noti->activateUser();
            } else {
                $noti->deactivateUser();
            }
        }
        $this->tpl->setOnScreenMessage('success', $this->lng->txt("settings_saved"), true);
        $this->ctrl->redirect($this, "");
    }

    public function editInfoObject(ilPropertyFormGUI $a_form = null): void
    {
        $this->checkPermission('write');
        $this->setSubTabs('properties');
        $this->tabs_gui->setTabActive('settings');
        $this->tabs_gui->setSubTabActive('crs_info_settings');

        if (!$a_form) {
            $a_form = $this->initInfoEditor();
        }
        $this->tpl->addBlockFile('ADM_CONTENT', 'adm_content', 'tpl.edit_info.html', 'Modules/Course');
        $this->tpl->setVariable('INFO_TABLE', $a_form->getHTML());

        if (!count($files = ilCourseFile::_readFilesByCourse($this->object->getId()))) {
            return;
        }
        $rows = array();
        foreach ($files as $file) {
            $table_data['id'] = $file->getFileId();
            $table_data['filename'] = $file->getFileName();
            $table_data['filetype'] = $file->getFileType();
            $table_data['filesize'] = $file->getFileSize();

            $rows[] = $table_data;
        }
        $table_gui = new ilCourseInfoFileTableGUI($this, 'editInfo');
        $table_gui->setTitle($this->lng->txt("crs_info_download"));
        $table_gui->setData($rows);
        $table_gui->addCommandButton("cancel", $this->lng->txt("cancel"));
        $table_gui->addMultiCommand("confirmDeleteInfoFiles", $this->lng->txt("delete"));
        $table_gui->setSelectAllCheckbox("file_id");
        $this->tpl->setVariable('INFO_FILE_TABLE', $table_gui->getHTML());
    }

    public function confirmDeleteInfoFilesObject(): void
    {
        $file_ids = [];
        if ($this->http->wrapper()->post()->has('file_id')) {
            $file_ids = $this->http->wrapper()->post()->retrieve(
                'file_id',
                $this->refinery->kindlyTo()->listOf(
                    $this->refinery->kindlyTo()->int()
                )
            );
        }
        if (count($file_ids) === 0) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt('select_one'));
            $this->editInfoObject();
            return;
        }

        $this->setSubTabs('properties');
        $this->tabs_gui->setTabActive('settings');
        $this->tabs_gui->setSubTabActive('crs_info_settings');

        $c_gui = new ilConfirmationGUI();

        // set confirm/cancel commands
        $c_gui->setFormAction($this->ctrl->getFormAction($this, "deleteInfoFiles"));
        $c_gui->setHeaderText($this->lng->txt("info_delete_sure"));
        $c_gui->setCancel($this->lng->txt("cancel"), "editInfo");
        $c_gui->setConfirm($this->lng->txt("confirm"), "deleteInfoFiles");

        // add items to delete
        foreach ($file_ids as $file_id) {
            $file = new ilCourseFile($file_id);
            $c_gui->addItem("file_id[]", $file_id, $file->getFileName());
        }
        $this->tpl->setContent($c_gui->getHTML());
    }

    public function deleteInfoFilesObject(): void
    {
        $file_ids = [];
        if ($this->http->wrapper()->post()->has('file_id')) {
            $file_ids = $this->http->wrapper()->post()->retrieve(
                'file_id',
                $this->refinery->kindlyTo()->listOf(
                    $this->refinery->kindlyTo()->int()
                )
            );
        }

        if (count($file_ids) === 0) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt('select_one'));
            $this->editInfoObject();
            return;
        }

        foreach ($file_ids as $file_id) {
            $file = new ilCourseFile($file_id);
            if ($this->object->getId() == $file->getCourseId()) {
                $file->delete();
            }
        }
        $this->tpl->setOnScreenMessage('success', $this->lng->txt('settings_saved'));
        $this->editInfoObject();
    }

    public function initInfoEditor(): ilPropertyFormGUI
    {
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
        $tg->setValue($this->object->getTargetGroup() ?? "");
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

        $this->record_gui = new ilAdvancedMDRecordGUI(
            ilAdvancedMDRecordGUI::MODE_EDITOR,
            'crs',
            $this->object->getId()
        );
        $this->record_gui->setPropertyForm($form);
        $this->record_gui->parse();
        return $form;
    }

    /**
     * @todo switch to form
     */
    public function updateInfoObject(): void
    {
        $this->checkPermission('write');

        $form = $this->initInfoEditor();
        if ($form->checkInput()) {
            $this->object->setImportantInformation((string) $form->getInput('important'));
            $this->object->setSyllabus((string) $form->getInput('syllabus'));
            $this->object->setTargetGroup((string) $form->getInput('target_group'));
            $this->object->setContactName((string) $form->getInput('contact_name'));
            $this->object->setContactResponsibility((string) $form->getInput('contact_responsibility'));
            $this->object->setContactPhone((string) $form->getInput('contact_phone'));
            $this->object->setContactEmail((string) $form->getInput('contact_email'));
            $this->object->setContactConsultation((string) $form->getInput('contact_consultation'));

            $file_info = $form->getInput('file');
            $file_name = $form->getItemByPostVar('file')->getFilename();

            $file_obj = new ilCourseFile();
            $file_obj->setCourseId($this->object->getId());
            $file_obj->setFileName((string) $file_name);
            $file_obj->setFileSize((int) $file_info['size']);
            $file_obj->setFileType((string) $file_info['type']);
            $file_obj->setTemporaryName((string) $file_info['tmp_name']);
            $file_obj->setErrorCode((int) $file_info['error']);

            $error = false;
            $this->error->setMessage('');
            $file_obj->validate();
            $this->object->validateInfoSettings();
            if (strlen($this->error->getMessage())) {
                $error = $this->error->getMessage();
            }
            if (!$this->record_gui->importEditFormPostValues()) {
                $error = true;
            }
            if ($error) {
                if ($error !== true) {
                    $this->tpl->setOnScreenMessage('failure', $this->error->getMessage());
                }
                $this->editInfoObject($form);
                return;
            }
            $this->object->update();
            $file_obj->create();
            $this->record_gui->writeEditForm();

            // Update ecs content
            $ecs = new ilECSCourseSettings($this->object);
            $ecs->handleContentUpdate();

            $this->tpl->setOnScreenMessage('success', $this->lng->txt("crs_settings_saved"));
            $this->editInfoObject();
        } else {
            $this->tpl->setOnScreenMessage(
                ilGlobalTemplateInterface::MESSAGE_TYPE_SUCCESS,
                $this->lng->txt('settings_saved')
            );
        }
    }

    public function updateObject(): void
    {
        $obj_service = $this->getObjectService();
        $setting = $this->settings;

        $form = $this->initEditForm();

        if (!$form->checkInput()) {
            $form->setValuesByPost();
            $this->tpl->setOnScreenMessage('failure', $GLOBALS['DIC']->language()->txt('err_check_input'));
            $this->editObject($form);
            return;
        }

        // Additional checks: subsription min/max
        if (
            $form->getInput('subscription_max') &&
            $form->getInput('subscription_min') &&
            ($form->getInput('subscription_max') < $form->getInput('subscription_min'))
        ) {
            $min = $form->getItemByPostVar('subscription_min');
            $min->setAlert($this->lng->txt('crs_subscription_min_members_err'));
            $this->tpl->setOnScreenMessage('failure', $GLOBALS['DIC']->language()->txt('err_check_input'));
            $this->editObject($form);
            return;
        }

        // Additional checks: both tile and objective view activated (not supported)
        if (
            $form->getInput('list_presentation') == "tile" &&
            $form->getInput('view_mode') == ilCourseConstants::IL_CRS_VIEW_OBJECTIVE) {
            $this->tpl->setOnScreenMessage(
                'failure',
                $GLOBALS['DIC']->language()->txt('crs_tile_and_objective_view_not_supported')
            );
            $this->editObject($form);
        }

        // Additional checks: both tile and session limitation activated (not supported)
        if ($form->getInput('sl') == "1" &&
            $form->getInput('list_presentation') == "tile") {
            $this->tpl->setOnScreenMessage(
                'failure',
                $GLOBALS['DIC']->language()->txt('crs_tile_and_session_limit_not_supported')
            );
            $this->editObject($form);
            return;
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
        $this->object->setOfflineStatus(!$form->getInput('activation_online'));

        // activation period
        $period = $form->getItemByPostVar("access_period");
        if ($period->getStart() && $period->getEnd()) {
            $this->object->setActivationStart($period->getStart()->get(IL_CAL_UNIX));
            $this->object->setActivationEnd($period->getEnd()->get(IL_CAL_UNIX));
            $this->object->setActivationVisibility((int) $form->getInput('activation_visibility'));
        } else {
            $this->object->setActivationStart(0);
            $this->object->setActivationEnd(0);
        }

        // subscription settings
        $this->object->setSubscriptionPassword($form->getInput('subscription_password'));
        $this->object->setSubscriptionStart(0);
        $this->object->setSubscriptionEnd(0);

        $sub_type = (int) $form->getInput('subscription_type');
        $sub_period = $form->getItemByPostVar('subscription_period');

        $this->object->setSubscriptionType($sub_type);
        if ($sub_type != ilCourseConstants::IL_CRS_SUBSCRIPTION_DEACTIVATED) {
            if ($sub_period->getStart() && $sub_period->getEnd()) {
                $this->object->setSubscriptionLimitationType(ilCourseConstants::IL_CRS_SUBSCRIPTION_LIMITED);
                $this->object->setSubscriptionStart($sub_period->getStart()->get(IL_CAL_UNIX));
                $this->object->setSubscriptionEnd($sub_period->getEnd()->get(IL_CAL_UNIX));
            } else {
                $this->object->setSubscriptionLimitationType(ilCourseConstants::IL_CRS_SUBSCRIPTION_UNLIMITED);
            }
        } else {
            $this->object->setSubscriptionType(ilCourseConstants::IL_CRS_SUBSCRIPTION_DIRECT);
            $this->object->setSubscriptionLimitationType(ilCourseConstants::IL_CRS_SUBSCRIPTION_DEACTIVATED);
        }

        // registration code
        $this->object->enableRegistrationAccessCode((bool) $form->getInput('reg_code_enabled'));
        $this->object->setRegistrationAccessCode($form->getInput('reg_code'));

        // cancellation end
        $this->object->setCancellationEnd($form->getItemByPostVar("cancel_end")->getDate());

        // waiting list
        $this->object->enableSubscriptionMembershipLimitation((bool) $form->getInput('subscription_membership_limitation'));
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

        $obj_service->commonSettings()->legacyForm($form, $this->object)->saveTitleIconVisibility();
        $obj_service->commonSettings()->legacyForm($form, $this->object)->saveTopActionsVisibility();
        ilContainer::_writeContainerSetting($this->object->getId(), "rep_breacrumb", $form->getInput('rep_breacrumb'));
        $obj_service->commonSettings()->legacyForm($form, $this->object)->saveIcon();
        $obj_service->commonSettings()->legacyForm($form, $this->object)->saveTileImage();
        $this->saveListPresentation($form);

        $this->object->setViewMode((int) $form->getInput('view_mode'));
        if ($this->object->getViewMode() == ilCourseConstants::IL_CRS_VIEW_TIMING) {
            $this->object->setOrderType(ilContainer::SORT_ACTIVATION);
            $this->object->setTimingMode((int) $form->getInput('timing_mode'));
        }
        $this->object->setTimingMode((int) $form->getInput('timing_mode'));
        $this->object->setOrderType((int) $form->getInput('sorting'));
        $this->saveSortingSettings($form);

        $this->object->setAboStatus((bool) $form->getInput('abo'));
        $this->object->setShowMembers((bool) $form->getInput('show_members'));

        if (\ilPrivacySettings::getInstance()->participantsListInCoursesEnabled()) {
            $this->object->setShowMembersExport((bool) $form->getInput('show_members_export'));
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
        if ($this->http->wrapper()->post()->has('status_dt')) {
            $status_dt = $this->http->wrapper()->post()->retrieve(
                'status_dt',
                $this->refinery->kindlyTo()->int()
            );
            if (
                $this->object->getStatusDetermination() != ilObjCourse::STATUS_DETERMINATION_LP &&
                $status_dt == ilObjCourse::STATUS_DETERMINATION_LP
            ) {
                $show_lp_sync_confirmation = true;
            } else {
                $this->object->setStatusDetermination($status_dt);
            }
        }

        if (!$old_autofill && $this->object->hasWaitingListAutoFill()) {
            $this->object->handleAutoFill();
        }
        $this->object->update();

        ilObjectServiceSettingsGUI::updateServiceSettingsForm(
            $this->object->getId(),
            $form,
            array(
                ilObjectServiceSettingsGUI::CALENDAR_CONFIGURATION,
                ilObjectServiceSettingsGUI::USE_NEWS,
                ilObjectServiceSettingsGUI::AUTO_RATING_NEW_OBJECTS,
                ilObjectServiceSettingsGUI::TAG_CLOUD,
                ilObjectServiceSettingsGUI::CUSTOM_METADATA,
                ilObjectServiceSettingsGUI::BADGES,
                ilObjectServiceSettingsGUI::ORGU_POSITION_ACCESS,
                ilObjectServiceSettingsGUI::SKILLS,
                ilObjectServiceSettingsGUI::BOOKING,
                ilObjectServiceSettingsGUI::EXTERNAL_MAIL_PREFIX
            )
        );

        ilChangeEvent::_recordWriteEvent($this->object->getId(), $this->user->getId(), 'update');
        ilChangeEvent::_catchupWriteEvents($this->object->getId(), $this->user->getId());

        // lp sync confirmation required
        if ($show_lp_sync_confirmation) {
            $this->confirmLPSync();
            return;
        }

        // Update ecs export settings
        $ecs = new ilECSCourseSettings($this->object);
        if (!$ecs->handleSettingsUpdate()) {
            $form->setValuesByPost();
            $this->tpl->setOnScreenMessage('failure', $GLOBALS['DIC']->language()->txt('err_check_input'));
            $this->editObject($form);
            return;
        }
        $this->afterUpdate();
    }

    protected function confirmLPSync(): void
    {
        $cgui = new ilConfirmationGUI();
        $cgui->setFormAction($this->ctrl->getFormAction($this, "setLPSync"));
        $cgui->setHeaderText($this->lng->txt("crs_status_determination_sync"));
        $cgui->setCancel($this->lng->txt("cancel"), "edit");
        $cgui->setConfirm($this->lng->txt("confirm"), "setLPSync");
        $this->tpl->setContent($cgui->getHTML());
    }

    protected function setLPSyncObject(): void
    {
        $this->object->setStatusDetermination(ilObjCourse::STATUS_DETERMINATION_LP);
        $this->object->update();
        $this->object->syncMembersStatusWithLP();
        $this->tpl->setOnScreenMessage('success', $this->lng->txt("settings_saved"), true);
        $this->ctrl->redirect($this, "edit");
    }

    public function editObject(ilPropertyFormGUI $form = null): void
    {
        $this->setSubTabs('properties');
        $this->tabs_gui->setSubTabActive('crs_settings');

        if ($form instanceof ilPropertyFormGUI) {
            $GLOBALS['DIC']['tpl']->setContent($form->getHTML());
        } else {
            parent::editObject();
        }
    }

    /**
     * values are already set in initEditForm
     * Returning an empty array avoid overriding these values.
     */
    protected function getEditFormValues(): array
    {
        return [];
    }

    protected function initEditForm(): ilPropertyFormGUI
    {
        $obj_service = $this->getObjectService();
        $setting = $this->settings;

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

        $dur = new ilDateDurationInputGUI($this->lng->txt('rep_time_period'), "access_period");
        $dur->setShowTime(true);
        $dur->setStart(new ilDateTime($this->object->getActivationStart(), IL_CAL_UNIX));
        $dur->setEnd(new ilDateTime($this->object->getActivationEnd(), IL_CAL_UNIX));
        $form->addItem($dur);

        $visible = new ilCheckboxInputGUI(
            $this->lng->txt('rep_activation_limited_visibility'),
            'activation_visibility'
        );
        $visible->setInfo($this->lng->txt('crs_activation_limited_visibility_info'));
        $visible->setChecked((bool) $this->object->getActivationVisibility());
        $dur->addSubItem($visible);

        $section = new ilFormSectionHeaderGUI();
        $section->setTitle($this->lng->txt('crs_reg'));
        $form->addItem($section);

        $reg_proc = new ilRadioGroupInputGUI($this->lng->txt('crs_registration_type'), 'subscription_type');
        $reg_proc->setValue(
            ($this->object->getSubscriptionLimitationType() != ilCourseConstants::IL_CRS_SUBSCRIPTION_DEACTIVATED)
                ? (string) $this->object->getSubscriptionType()
                : (string) ilCourseConstants::IL_CRS_SUBSCRIPTION_DEACTIVATED
        );
        // $reg_proc->setInfo($this->lng->txt('crs_reg_type_info'));

        $opt = new ilRadioOption(
            $this->lng->txt('crs_subscription_options_direct'),
            (string) ilCourseConstants::IL_CRS_SUBSCRIPTION_DIRECT
        );
        $reg_proc->addOption($opt);

        $opt = new ilRadioOption(
            $this->lng->txt('crs_subscription_options_password'),
            (string) ilCourseConstants::IL_CRS_SUBSCRIPTION_PASSWORD
        );

        $pass = new ilTextInputGUI($this->lng->txt("password"), 'subscription_password');
        $pass->setRequired(true);
        $pass->setInfo($this->lng->txt('crs_reg_password_info'));
        $pass->setSubmitFormOnEnter(true);
        $pass->setSize(32);
        $pass->setMaxLength(32);
        $pass->setValue($this->object->getSubscriptionPassword());

        $opt->addSubItem($pass);
        $reg_proc->addOption($opt);

        $opt = new ilRadioOption(
            $this->lng->txt('crs_subscription_options_confirmation'),
            (string) ilCourseConstants::IL_CRS_SUBSCRIPTION_CONFIRMATION
        );
        $opt->setInfo($this->lng->txt('crs_registration_confirmation_info'));
        $reg_proc->addOption($opt);

        $opt = new ilRadioOption(
            $this->lng->txt('crs_reg_no_selfreg'),
            (string) ilCourseConstants::IL_CRS_SUBSCRIPTION_DEACTIVATED
        );
        $opt->setInfo($this->lng->txt('crs_registration_deactivated'));
        $reg_proc->addOption($opt);

        $form->addItem($reg_proc);

        // Registration codes
        $reg_code = new ilCheckboxInputGUI($this->lng->txt('crs_reg_code'), 'reg_code_enabled');
        $reg_code->setChecked($this->object->isRegistrationAccessCodeEnabled());
        $reg_code->setValue('1');
        $reg_code->setInfo($this->lng->txt('crs_reg_code_enabled_info'));

        // Create default access code
        if (!$this->object->getRegistrationAccessCode()) {
            $this->object->setRegistrationAccessCode(ilMembershipRegistrationCodeUtils::generateCode());
        }
        $reg_link = new ilHiddenInputGUI('reg_code');
        $reg_link->setValue($this->object->getRegistrationAccessCode());
        $form->addItem($reg_link);

        $link = new ilCustomInputGUI($this->lng->txt('crs_reg_code_link'));
        $val = ilLink::_getLink(
            $this->object->getRefId(),
            $this->object->getType(),
            array(),
            '_rcode' . $this->object->getRegistrationAccessCode()
        );
        $link->setHtml('<span class="small">' . $val . '</span>');
        $reg_code->addSubItem($link);

        $form->addItem($reg_code);

        // time limit
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
        $lim = new ilCheckboxInputGUI(
            $this->lng->txt('crs_subscription_max_members_short'),
            'subscription_membership_limitation'
        );
        $lim->setInfo($this->lng->txt('crs_subscription_max_members_short_info'));
        $lim->setValue((string) 1);
        $lim->setChecked($this->object->isSubscriptionMembershipLimited());

        $min = new ilTextInputGUI('', 'subscription_min');
        $min->setSubmitFormOnEnter(true);
        $min->setSize(4);
        $min->setMaxLength(4);
        $min->setValue($this->object->getSubscriptionMinMembers() ?: '');
        $min->setTitle($this->lng->txt('crs_subscription_min_members'));
        $min->setInfo($this->lng->txt('crs_subscription_min_members_info'));
        $lim->addSubItem($min);

        $max = new ilTextInputGUI('', 'subscription_max');
        $max->setSubmitFormOnEnter(true);
        $max->setSize(4);
        $max->setMaxLength(4);
        $max->setValue($this->object->getSubscriptionMaxMembers() ?: '');
        $max->setTitle($this->lng->txt('crs_subscription_max_members'));
        $max->setInfo($this->lng->txt('crs_reg_max_info'));

        $lim->addSubItem($max);

        $wait = new ilRadioGroupInputGUI($this->lng->txt('crs_waiting_list'), 'waiting_list');
        $option = new ilRadioOption($this->lng->txt('none'), '0');
        $wait->addOption($option);

        $option = new ilRadioOption($this->lng->txt('crs_waiting_list_no_autofill'), '1');
        $option->setInfo($this->lng->txt('crs_wait_info'));
        $wait->addOption($option);

        $option = new ilRadioOption($this->lng->txt('crs_waiting_list_autofill'), '2');
        $option->setInfo($this->lng->txt('crs_waiting_list_autofill_info'));
        $wait->addOption($option);

        if ($this->object->hasWaitingListAutoFill()) {
            $wait->setValue('2');
        } elseif ($this->object->enabledWaitingList()) {
            $wait->setValue('1');
        } else {
            $wait->setValue('0');
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

        $opts = new ilRadioOption(
            $this->lng->txt('cntr_view_sessions'),
            (string) ilCourseConstants::IL_CRS_VIEW_SESSIONS
        );
        $opts->setInfo($this->lng->txt('cntr_view_info_sessions'));
        $view_type->addOption($opts);

        // Limited sessions
        $sess = new ilCheckboxInputGUI($this->lng->txt('sess_limit'), 'sl');
        $sess->setValue('1');
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

        $optsi = new ilRadioOption($this->lng->txt('cntr_view_simple'), (string) ilCourseConstants::IL_CRS_VIEW_SIMPLE);
        $optsi->setInfo($this->lng->txt('cntr_view_info_simple'));
        $view_type->addOption($optsi);

        $optbt = new ilRadioOption(
            $this->lng->txt('cntr_view_by_type'),
            (string) ilCourseConstants::IL_CRS_VIEW_BY_TYPE
        );
        $optbt->setInfo($this->lng->txt('cntr_view_info_by_type'));
        $view_type->addOption($optbt);

        $opto = new ilRadioOption(
            $this->lng->txt('crs_view_objective'),
            (string) ilCourseConstants::IL_CRS_VIEW_OBJECTIVE
        );
        $opto->setInfo($this->lng->txt('crs_view_info_objective'));
        $view_type->addOption($opto);

        $optt = new ilRadioOption($this->lng->txt('crs_view_timing'), (string) ilCourseConstants::IL_CRS_VIEW_TIMING);
        $optt->setInfo($this->lng->txt('crs_view_info_timing'));

        // cognos-blu-patch: begin
        $timing = new ilRadioGroupInputGUI($this->lng->txt('crs_view_timings'), "timing_mode");
        $timing->setValue($this->object->getTimingMode());

        $absolute = new ilRadioOption(
            $this->lng->txt('crs_view_timing_absolute'),
            (string) ilCourseConstants::IL_CRS_VIEW_TIMING_ABSOLUTE
        );
        $absolute->setInfo($this->lng->txt('crs_view_info_timing_absolute'));
        $timing->addOption($absolute);

        $relative = new ilRadioOption(
            $this->lng->txt('crs_view_timing_relative'),
            (string) ilCourseConstants::IL_CRS_VIEW_TIMING_RELATIVE
        );
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
        if (ilObjUserTracking::_enabledLearningProgress()) {
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
                    (string) ilObjCourse::STATUS_DETERMINATION_LP,
                    $this->lng->txt('crs_status_determination_lp_info')
                );
                $lp_status_options->addOption($lp_option);
                $lp_status_options->addOption(new ilRadioOption(
                    $this->lng->txt('crs_status_determination_manual'),
                    (string) ilObjCourse::STATUS_DETERMINATION_MANUAL
                ));

                $form->addItem($lp_status_options);
            }
        }

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
                ilObjectServiceSettingsGUI::AUTO_RATING_NEW_OBJECTS,
                ilObjectServiceSettingsGUI::TAG_CLOUD,
                ilObjectServiceSettingsGUI::CUSTOM_METADATA,
                ilObjectServiceSettingsGUI::BADGES,
                ilObjectServiceSettingsGUI::ORGU_POSITION_ACCESS,
                ilObjectServiceSettingsGUI::SKILLS,
                ilObjectServiceSettingsGUI::BOOKING,
                ilObjectServiceSettingsGUI::EXTERNAL_MAIL_PREFIX
            )
        );

        $mem = new ilCheckboxInputGUI($this->lng->txt('crs_show_members'), 'show_members');
        $mem->setChecked($this->object->getShowMembers());
        $mem->setInfo($this->lng->txt('crs_show_members_info'));
        $form->addItem($mem);

        // check privacy
        if (\ilPrivacySettings::getInstance()->participantsListInCoursesEnabled()) {
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
            (string) ilCourseConstants::MAIL_ALLOWED_TUTORS,
            $this->lng->txt('crs_mail_tutors_only_info')
        );
        $mail_type->addOption($mail_tutors);

        $mail_all = new ilRadioOption(
            $this->lng->txt('crs_mail_all'),
            (string) ilCourseConstants::MAIL_ALLOWED_ALL,
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
        $not->setValue('1');
        $not->setInfo($this->lng->txt('crs_auto_notification_info'));
        $not->setChecked($this->object->getAutoNotification());
        $form->addItem($not);

        $desk = new ilCheckboxInputGUI($this->lng->txt('crs_add_remove_from_desktop'), 'abo');
        $desk->setChecked($this->object->getAboStatus());
        $desk->setInfo($this->lng->txt('crs_add_remove_from_desktop_info'));
        $form->addItem($desk);

        // Edit ecs export settings
        $ecs = new ilECSCourseSettings($this->object);
        $ecs->addSettingsToForm($form, 'crs');
        return $form;
    }

    public function sendFileObject(): void
    {
        $file_id = 0;
        if ($this->http->wrapper()->query()->has('file_id')) {
            $file_id = $this->http->wrapper()->query()->retrieve(
                'file_id',
                $this->refinery->kindlyTo()->int()
            );
        }
        $file = new ilCourseFile($file_id);
        ilFileDelivery::deliverFileLegacy($file->getAbsolutePath(), $file->getFileName(), $file->getFileType());
    }

    public function setSubTabs(string $a_tab): void
    {
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
                if (ilMapUtil::isActivated()) {
                    $this->tabs_gui->addSubTabTarget(
                        "crs_map_settings",
                        $this->ctrl->getLinkTarget($this, 'editMapSettings'),
                        "editMapSettings",
                        get_class($this)
                    );
                }

                // only show if export permission is granted
                if (ilPrivacySettings::getInstance()->checkExportAccess($this->object->getRefId()) || ilCourseDefinedFieldDefinition::_hasFields($this->object->getId())) {
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
     * @inheritDoc
     */
    protected function showPossibleSubObjects(): void
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
     * @inheritDoc
     */
    protected function afterSave(ilObject $new_object): void
    {
        $new_object->getMemberObject()->add($this->user->getId(), ilParticipants::IL_CRS_ADMIN);
        $new_object->getMemberObject()->updateNotification(
            $this->user->getId(),
            $this->settings->get('mail_crs_admin_notification', '1')
        );
        $new_object->getMemberObject()->updateContact($this->user->getId(), 1);
        $new_object->update();

        ilChangeEvent::_recordWriteEvent($new_object->getId(), $this->user->getId(), 'create');
        // END ChangeEvent: Record write event.

        // always send a message
        $this->tpl->setOnScreenMessage('success', $this->lng->txt("crs_added"), true);
        $this->ctrl->setParameter($this, "ref_id", $new_object->getRefId());
        ilUtil::redirect($this->getReturnLocation(
            "save",
            $this->ctrl->getLinkTarget($this, "edit", "")
        ));
    }

    public function readMemberData(array $ids, array $selected_columns = null): array
    {
        $show_tracking =
            (
                ilObjUserTracking::_enabledLearningProgress() &&
                ilObjUserTracking::_enabledUserRelatedData()
            );
        if ($show_tracking) {
            $olp = ilObjectLP::getInstance($this->object->getId());
            $show_tracking = $olp->isActive();
        }

        if ($show_tracking) {
            $completed = ilLPStatusWrapper::_lookupCompletedForObject($this->object->getId());
            $in_progress = ilLPStatusWrapper::_lookupInProgressForObject($this->object->getId());
            $failed = ilLPStatusWrapper::_lookupFailedForObject($this->object->getId());
        }
        $privacy = ilPrivacySettings::getInstance();

        if ($privacy->enabledCourseAccessTimes()) {
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

        $members = [];
        foreach ($ids as $usr_id) {
            $name = ilObjUser::_lookupName($usr_id);
            $tmp_data['firstname'] = $name['firstname'];
            $tmp_data['lastname'] = $name['lastname'];
            $tmp_data['login'] = ilObjUser::_lookupLogin($usr_id);
            $tmp_data['passed'] = $this->object->getMembersObject()->hasPassed($usr_id) ? 1 : 0;
            if ($this->object->getStatusDetermination() == ilObjCourse::STATUS_DETERMINATION_LP) {
                $tmp_data['passed_info'] = $this->object->getMembersObject()->getPassedInfo($usr_id);
            }
            $tmp_data['notification'] = $this->object->getMembersObject()->isNotificationEnabled($usr_id) ? 1 : 0;
            $tmp_data['blocked'] = $this->object->getMembersObject()->isBlocked($usr_id) ? 1 : 0;
            $tmp_data['contact'] = $this->object->getMembersObject()->isContact($usr_id) ? 1 : 0;

            $tmp_data['usr_id'] = $usr_id;

            if ($show_tracking) {
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
                if (isset($progress[$usr_id]['ts']) && $progress[$usr_id]['ts']) {
                    $tmp_data['access_ut'] = $progress[$usr_id]['ts'];
                    $tmp_data['access_time'] = ilDatePresentation::formatDate(new ilDateTime(
                        $progress[$usr_id]['ts'],
                        IL_CAL_UNIX
                    ));
                } else {
                    $tmp_data['access_ut'] = 0;
                    $tmp_data['access_time'] = $this->lng->txt('no_date');
                }
            }

            if ($do_prtf) {
                $tmp_data['prtf'] = $all_prtf[$usr_id] ?? null;
            }

            $members[$usr_id] = $tmp_data;
        }
        return $members;
    }

    /**
     * sync course status and lp status
     */
    public function updateLPFromStatus(int $a_member_id, bool $a_has_passed): void
    {
        if (ilObjUserTracking::_enabledLearningProgress() &&
            $this->object->getStatusDetermination() == ilObjCourse::STATUS_DETERMINATION_LP) {
            $olp = ilObjectLP::getInstance($this->object->getId());
            if ($olp->getCurrentMode() == ilLPObjSettings::LP_MODE_MANUAL_BY_TUTOR) {
                $marks = new ilLPMarks($this->object->getId(), $a_member_id);

                // only if status has changed
                if ($marks->getCompleted() !== $a_has_passed) {
                    $marks->setCompleted($a_has_passed);
                    $marks->update();

                    // as course is origin of LP status change, block syncing
                    ilCourseAppEventListener::setBlockedForLP(true);
                    ilLPStatusWrapper::_updateStatus($this->object->getId(), $a_member_id);
                }
            }
        }
    }

    public function autoFillObject(): bool
    {
        $this->checkPermission('write');
        if (
            $this->object->isSubscriptionMembershipLimited() &&
            $this->object->getSubscriptionMaxMembers() &&
            $this->object->getSubscriptionMaxMembers() <= $this->object->getMembersObject()->getCountMembers()
        ) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt("crs_max_members_reached"));
            $this->membersObject();
            return false;
        }
        if ($number = $this->object->getMembersObject()->autoFillSubscribers()) {
            $this->tpl->setOnScreenMessage('success', $this->lng->txt("crs_number_users_added") . " " . $number);
        } else {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt("crs_no_users_added"));
        }
        $this->membersObject();
        return true;
    }

    public function leaveObject(): void
    {
        $this->checkPermission('leave');

        if ($this->object->getMembersObject()->isLastAdmin($this->user->getId())) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt('crs_min_one_admin'));
            $this->viewObject();
            return;
        }

        $this->tabs_gui->setTabActive('crs_unsubscribe');
        $cgui = new ilConfirmationGUI();
        $cgui->setHeaderText($this->lng->txt('crs_unsubscribe_sure'));
        $cgui->setFormAction($this->ctrl->getFormAction($this));
        $cgui->setCancel($this->lng->txt("cancel"), "cancel");
        $cgui->setConfirm($this->lng->txt("crs_unsubscribe"), "performUnsubscribe");
        $this->tpl->setContent($cgui->getHTML());
    }

    public function unsubscribeObject(): void
    {
        $this->leaveObject();
    }

    public function performUnsubscribeObject()
    {
        $this->checkPermission('leave');
        $this->getObject()->getMembersObject()->delete($this->user->getId());
        $this->getObject()->getMembersObject()->sendUnsubscribeNotificationToAdmins($this->user->getId());
        $this->getObject()->getMembersObject()->sendNotification(
            ilCourseMembershipMailNotification::TYPE_NOTIFICATION_UNSUBSCRIBE,
            $this->user->getId()
        );
        $this->tpl->setOnScreenMessage('success', $this->lng->txt('crs_unsubscribed_from_crs'), true);

        $this->ctrl->setParameterByClass("ilrepositorygui", "ref_id", $this->tree->getParentId($this->ref_id));
        $this->ctrl->redirectByClass("ilrepositorygui", "");
    }


    protected function getAgreementTabs(): void
    {
        if ($this->access->checkAccess('visible', '', $this->ref_id)) {
            $this->tabs->addTarget(
                "info_short",
                $this->ctrl->getLinkTargetByClass(
                    array("ilobjcoursegui", "ilinfoscreengui"),
                    "showSummary"
                ),
                "infoScreen"
            );
        }
        if (
            $this->access->checkAccess('leave', '', $this->object->getRefId()) &&
            $this->object->getMemberObject()->isMember()
        ) {
            $this->tabs->addTarget(
                "crs_unsubscribe",
                $this->ctrl->getLinkTarget($this, "unsubscribe"),
                'leave',
                ""
            );
        }
    }

    public function addContentTab(): void
    {
        $this->tabs_gui->addTab(
            "view_content",
            $this->lng->txt("content"),
            $this->ctrl->getLinkTarget($this, "view")
        );
    }

    protected function getTabs(): void
    {
        $this->help->setScreenIdComponent("crs");
        $this->ctrl->setParameter($this, "ref_id", $this->ref_id);

        if ($this->access->checkAccess('read', '', $this->ref_id)) {
            // default activation
            $this->tabs_gui->activateTab('view_content');
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

        if ($this->object->getViewMode() == ilCourseConstants::IL_CRS_VIEW_TIMING && $this->access->checkAccess('write', '', $this->ref_id)
        ) {
            $this->tabs->addTab(
                'timings_timings',
                $this->lng->txt('timings_timings'),
                $this->ctrl->getLinkTargetByClass('ilcoursecontentgui', 'manageTimings')
            );
        } elseif (
            $this->object->getViewMode() == ilCourseConstants::IL_CRS_VIEW_TIMING && $this->object->getMemberObject()->isParticipant() && $this->access->checkAccess('read', '', $this->ref_id)) {
            $this->tabs->addTab(
                'timings_timings',
                $this->lng->txt('timings_timings'),
                $this->ctrl->getLinkTargetByClass('ilcoursecontentgui', 'managePersonalTimings')
            );
        }

        // learning objectives
        if ($this->access->checkAccess('write', '', $this->ref_id)) {
            if ($this->object->getViewMode() == ilCourseConstants::IL_CRS_VIEW_OBJECTIVE or ilCourseObjective::_getCountObjectives($this->object->getId())) {
                $this->tabs_gui->addTarget(
                    'crs_objectives',
                    $this->ctrl->getLinkTargetByClass('illoeditorgui', ''),
                    'illoeditorgui'
                );
            }
        }

        if (
            $this->access->checkAccess('visible', '', $this->ref_id) ||
            $this->access->checkAccess('join', '', $this->ref_id) ||
            $this->access->checkAccess('read', '', $this->ref_id)
        ) {
            $force_active =
                strcasecmp($this->ctrl->getCmdClass(), ilInfoScreenGUI::class) === 0 ||
                strcasecmp($this->ctrl->getCmdClass(), ilNoteGUI::class) === 0;
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
        if ($this->access->checkAccess('write', '', $this->ref_id)) {
            $force_active =
                strcasecmp($this->ctrl->getCmdClass(), ilConditionHandlerGUI::class) &&
                !$this->http->wrapper()->query()->has('item_id');
            $this->tabs_gui->addTarget(
                "settings",
                $this->ctrl->getLinkTarget($this, "edit"),
                array("edit", "editMapSettings", "editCourseIcons", "listStructure"),
                "",
                "",
                $force_active
            );
        }

        $is_participant = ilCourseParticipants::_isParticipant($this->ref_id, $this->user->getId());
        $mail = new ilMail($this->user->getId());

        $membership_gui = new ilCourseMembershipGUI($this, $this->object);
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
        if (ilContSkillPresentationGUI::isAccessible($this->ref_id)) {
            $this->tabs_gui->addTarget(
                "obj_tool_setting_skills",
                $this->ctrl->getLinkTargetByClass(array("ilcontainerskillgui", "ilcontskillpresentationgui"), ""),
                "",
                array("ilcontainerskillgui", "ilcontskillpresentationgui", "ilcontskilladmingui")
            );
        }

        // booking
        if ($this->access->checkAccess('write', '', $this->ref_id) && ilContainer::_lookupContainerSetting(
            $this->object->getId(),
            ilObjectServiceSettingsGUI::BOOKING,
            '0'
        )) {
            $this->tabs_gui->addTarget(
                "obj_tool_setting_booking",
                $this->ctrl->getLinkTargetByClass(array("ilbookinggatewaygui"), "")
            );
        }

        // learning progress
        if (ilLearningProgressAccess::checkAccess($this->object->getRefId(), $is_participant)) {
            $this->tabs_gui->addTarget(
                'learning_progress',
                $this->ctrl->getLinkTargetByClass(array('ilobjcoursegui', 'illearningprogressgui'), ''),
                '',
                array('illplistofobjectsgui', 'illplistofsettingsgui', 'illearningprogressgui', 'illplistofprogressgui')
            );
        }

        // meta data
        if ($this->access->checkAccess('write', '', $this->ref_id)) {
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

        if ($this->access->checkAccess('write', '', $this->object->getRefId())) {
            $this->tabs_gui->addTarget(
                'export',
                $this->ctrl->getLinkTargetByClass('ilexportgui', ''),
                'export',
                'ilexportgui'
            );
        }

        if ($this->access->checkAccess('edit_permission', '', $this->ref_id)) {
            $this->tabs_gui->addTarget(
                "perm_settings",
                $this->ctrl->getLinkTargetByClass(array(get_class($this), 'ilpermissiongui'), "perm"),
                array("perm", "info", "owner"),
                'ilpermissiongui'
            );
        }

        // Join/Leave
        if ($this->access->checkAccess('join', '', $this->ref_id) && !$this->object->getMemberObject()->isAssigned()) {
            if (ilCourseWaitingList::_isOnList($this->user->getId(), $this->object->getId())) {
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
        if ($this->access->checkAccess('leave', '', $this->object->getRefId()) && $this->object->getMemberObject()->isMember()) {
            $this->tabs_gui->addTarget(
                "crs_unsubscribe",
                $this->ctrl->getLinkTarget($this, "unsubscribe"),
                'leave',
                ""
            );
        }
    }

    public function executeCommand(): void
    {
        $next_class = $this->ctrl->getNextClass($this);
        $cmd = $this->ctrl->getCmd();

        $this->prepareOutput();

        // add entry to navigation history
        if (!$this->getCreationMode() &&
            $this->access->checkAccess('read', '', $this->ref_id)) {
            $this->navigation_history->addItem(
                $this->ref_id,
                ilLink::_getLink($this->ref_id, "crs"),
                "crs"
            );
        }

        $header_action = true;
        switch ($next_class) {
            case strtolower(ilRepositoryTrashGUI::class):
                $ru = new \ilRepositoryTrashGUI($this);
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

                $mem_gui = new ilCourseMembershipGUI($this, $this->object);
                $this->ctrl->forwardCommand($mem_gui);
                break;

            case "ilinfoscreengui":
                $this->infoScreen();    // forwards command
                break;

            case 'ilobjectmetadatagui':
                if (!$this->access->checkAccess('write', '', $this->object->getRefId())) {
                    $this->error->raiseError($this->lng->txt('permission_denied'), $this->error->WARNING);
                }
                $this->tabs_gui->setTabActive('meta_data');
                $md_gui = new ilObjectMetaDataGUI($this->object);
                $this->ctrl->forwardCommand($md_gui);
                break;

            case 'ilcourseregistrationgui':
                $this->ctrl->setReturn($this, '');
                $this->tabs_gui->setTabActive('join');
                $registration = new ilCourseRegistrationGUI($this->object, $this);
                $this->ctrl->forwardCommand($registration);
                break;

            case 'ilobjectcustomuserfieldsgui':
                $cdf_gui = new ilObjectCustomUserFieldsGUI($this->object->getId());
                $this->setSubTabs('properties');
                $this->tabs_gui->activateTab('settings');
                $this->tabs_gui->activateSubTab('crs_custom_user_fields');
                $this->ctrl->forwardCommand($cdf_gui);
                break;

            case "ilcourseobjectivesgui":

                $this->ctrl->setReturn($this, "");
                $reg_gui = new ilCourseObjectivesGUI($this->object->getRefId());
                $this->ctrl->forwardCommand($reg_gui);
                break;

            case 'ilobjcoursegroupinggui':

                $this->ctrl->setReturn($this, 'edit');
                $this->setSubTabs('properties');
                $this->tabs_gui->activateTab('settings');
                $this->tabs_gui->activateSubTab('groupings');

                $grouping_id = 0;
                if ($this->http->wrapper()->query()->has('obj_id')) {
                    $grouping_id = $this->http->wrapper()->query()->retrieve(
                        'obj_id',
                        $this->refinery->kindlyTo()->int()
                    );
                }
                $crs_grp_gui = new ilObjCourseGroupingGUI($this->object, $grouping_id);
                $this->ctrl->forwardCommand($crs_grp_gui);
                break;

            case "ilpropertyformgui":
                // only case is currently adv metadata internal link in info settings, see #24497
                $form = $this->initInfoEditor();
                $this->ctrl->forwardCommand($form);
                break;

            case "ilcolumngui":
                $this->ctrl->setReturn($this, "");
                $this->tabs_gui->setTabActive('none');
                $this->checkPermission("read");
                //$this->prepareOutput();
                //$this->tpl->setVariable("LOCATION_CONTENT_STYLESHEET",
                //	ilObjStyleSheet::getContentStylePath(0));
                //$this->renderObject();
                $this->viewObject();
                break;

            case "ilconditionhandlergui":
                // preconditions for whole course
                $this->setSubTabs("properties");
                $this->tabs_gui->activateTab('settings');
                $this->tabs_gui->activateSubTab('preconditions');
                $new_gui = new ilConditionHandlerGUI($this->object->getRefId());
                $this->ctrl->forwardCommand($new_gui);
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

            case 'ilpermissiongui':
                $this->tabs_gui->setTabActive('perm_settings');
                $perm_gui = new ilPermissionGUI($this);
                $this->ctrl->forwardCommand($perm_gui);
                break;

            case 'ilcalendarpresentationgui':
                $cal = new ilCalendarPresentationGUI($this->object->getRefId());
                $ret = $this->ctrl->forwardCommand($cal);
                $header_action = false;
                break;

            case 'ilcoursecontentgui':
                $this->ctrl->setReturn($this, 'members');
                $course_content_obj = new ilCourseContentGUI($this);
                $this->ctrl->forwardCommand($course_content_obj);
                break;

            case 'ilpublicuserprofilegui':
                $this->tpl->enableDragDropFileUpload(null);
                $this->setSubTabs('members');
                $this->tabs_gui->setTabActive('members');

                $user_id = $this->user->getId();
                if ($this->http->wrapper()->query()->has('user_id')) {
                    $user_id = $this->http->wrapper()->query()->retrieve(
                        'user_id',
                        $this->refinery->kindlyTo()->int()
                    );
                }
                $profile_gui = new ilPublicUserProfileGUI($user_id);
                $profile_gui->setBackUrl($this->ctrl->getLinkTargetByClass(["ilCourseMembershipGUI",
                                                                            "ilUsersGalleryGUI"
                ], 'view'));
                $this->tabs_gui->setSubTabActive('crs_members_gallery');
                $html = $this->ctrl->forwardCommand($profile_gui);
                $this->tpl->setVariable("ADM_CONTENT", $html);
                break;

            case 'ilmemberagreementgui':
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
                $pgui = new ilContainerStartObjectsPageGUI($this->object->getId());
                $ret = $this->ctrl->forwardCommand($pgui);
                if ($ret) {
                    $this->tpl->setContent($ret);
                }
                break;

            case 'ilobjectcopygui':
                $cp = new ilObjectCopyGUI($this);
                $cp->setType('crs');
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

            case 'ildidactictemplategui':
                $this->ctrl->setReturn($this, 'edit');
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

            case 'illoeditorgui':
                #$this->tabs_gui->clearTargets();
                #$this->tabs_gui->setBackTarget($this->lng->txt('back'),$this->ctrl->getLinkTarget($this,''));
                $this->tabs_gui->activateTab('crs_objectives');

                $editor = new ilLOEditorGUI($this->object);
                $this->ctrl->forwardCommand($editor);
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
                $this->tabs_gui->activateTab("start");
                if (strtolower($this->ctrl->getCmdClass()) ==
                    "ilcontainerstartobjectspagegui") {
                    $header_action = false;
                }
                global $DIC;

                $ilHelp = $DIC['ilHelp'];
                $this->help->setScreenIdComponent("crs");
                $stgui = new ilContainerStartObjectsGUI($this->object);
                $this->ctrl->forwardCommand($stgui);
                break;

            case 'illomembertestresultgui':
                $GLOBALS['DIC']['ilCtrl']->setReturn($this, 'members');
                $GLOBALS['DIC']['ilTabs']->clearTargets();
                $GLOBALS['DIC']['ilTabs']->setBackTarget(
                    $GLOBALS['DIC']['lng']->txt('back'),
                    $GLOBALS['DIC']['ilCtrl']->getLinkTarget($this, 'members')
                );

                $uid = 0;
                if ($this->http->wrapper()->query()->has('uid')) {
                    $uid = $this->http->wrapper()->query()->retrieve(
                        'uid',
                        $this->refinery->kindlyTo()->int()
                    );
                }
                $result_view = new ilLOMemberTestResultGUI($this, $this->object, $uid);
                $this->ctrl->forwardCommand($result_view);
                break;

            case 'ilmailmembersearchgui':
                $mail = new ilMail($this->user->getId());

                if (
                    !($this->object->getMailToMembersType() == ilCourseConstants::MAIL_ALLOWED_ALL ||
                        $this->access->checkAccess('manage_members', "", $this->object->getRefId())) &&
                    $this->rbac_system->checkAccess('internal_mail', $mail->getMailObjectReferenceId())) {
                    $this->error->raiseError($this->lng->txt("msg_no_perm_read"), $this->error->MESSAGE);
                }

                $this->tabs_gui->setTabActive('members');

                $mail_search = new ilMailMemberSearchGUI(
                    $this,
                    $this->object->getRefId(),
                    new ilMailMemberCourseRoles()
                );
                $mail_search->setObjParticipants(
                    ilCourseParticipants::_getInstanceByObjId($this->object->getId())
                );
                $this->ctrl->forwardCommand($mail_search);
                break;

            case 'ilbadgemanagementgui':
                $this->tabs_gui->setTabActive('obj_tool_setting_badges');
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
                $t = ilNewsTimelineGUI::getInstance(
                    $this->object->getRefId(),
                    $this->object->getNewsTimelineAutoEntries()
                );
                $t->setUserEditAll($this->access->checkAccess('write', '', $this->object->getRefId(), 'grp'));
                $this->showPermanentLink();
                $this->ctrl->forwardCommand($t);
                ilLearningProgress::_tracProgress(
                    $this->user->getId(),
                    $this->object->getId(),
                    $this->object->getRefId(),
                    'crs'
                );
                break;

            case 'ilmemberexportsettingsgui':
                $this->setSubTabs('properties');
                $this->tabs_gui->activateTab('properties');
                $this->tabs_gui->activateSubTab('export_members');
                $settings_gui = new ilMemberExportSettingsGUI($this->object->getType(), $this->object->getId());
                $this->ctrl->forwardCommand($settings_gui);
                break;

            case "ilcontainerskillgui":
                $this->tabs_gui->activateTab('obj_tool_setting_skills');
                $gui = new ilContainerSkillGUI($this);
                $this->ctrl->forwardCommand($gui);
                break;

            case 'ilobjecttranslationgui':
                $this->checkPermissionBool("write");
                $this->setSubTabs("properties");
                $this->tabs_gui->activateTab("settings");
                $this->tabs_gui->activateSubTab("obj_multilinguality");
                $transgui = new ilObjectTranslationGUI($this);
                $this->ctrl->forwardCommand($transgui);
                break;

            case "ilbookinggatewaygui":
                $this->tabs_gui->activateTab('obj_tool_setting_booking');
                $gui = new ilBookingGatewayGUI($this);
                $this->ctrl->forwardCommand($gui);
                break;

            default:
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
                    && !$this->access->checkAccess("read", '', $this->object->getRefId())
                    || $cmd == 'join'
                    || $cmd == 'subscribe') {
                    if ($this->rbac_system->checkAccess('join', $this->object->getRefId()) &&
                        !ilCourseParticipants::_isParticipant($this->object->getRefId(), $this->user->getId())) {
                        $this->ctrl->redirectByClass("ilCourseRegistrationGUI");
                    } else {
                        $this->infoScreenObject();
                        break;
                    }
                }

                if ($cmd == 'listObjectives') {
                    $this->ctrl->setReturn($this, "");
                    $obj_gui = new ilCourseObjectivesGUI($this->object->getRefId());
                    $this->ctrl->forwardCommand($obj_gui);
                    break;
                }
                // if news timeline is landing page, redirect if necessary
                if ($cmd == "" && $this->object->isNewsTimelineLandingPageEffective()) {
                    $this->ctrl->redirectByClass("ilnewstimelinegui");
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

    private function checkAgreement(): bool
    {
        if ($this->access->checkAccess('write', '', $this->object->getRefId())) {
            return true;
        }

        // Disable aggrement if is not member of course
        if (!$this->object->getMemberObject()->isAssigned()) {
            return true;
        }

        if (ilMemberViewSettings::getInstance()->isActive()) {
            return true;
        }

        $privacy = ilPrivacySettings::getInstance();

        // Check agreement
        if (
            (
                $privacy->courseConfirmationRequired() || ilCourseDefinedFieldDefinition::_hasFields($this->object->getId())
            ) &&
            !ilMemberAgreement::_hasAccepted($this->user->getId(), $this->object->getId())
        ) {
            $this->logger->warning('Missing course confirmation.');
            return false;
        }
        // Check required fields
        if (!ilCourseUserData::_checkRequired($this->user->getId(), $this->object->getId())) {
            $this->logger->warning('Missing required fields');
            return false;
        }
        return true;
    }

    public static function _forwards(): array
    {
        return array("ilCourseRegisterGUI", 'ilConditionHandlerGUI');
    }

    protected function membersObject(): void
    {
        $this->ctrl->redirectByClass('ilcoursemembershipgui');
    }

    public static function _goto($a_target, string $a_add = ""): void
    {
        global $DIC;
        $main_tpl = $DIC->ui()->mainTemplate();
        $ilAccess = $DIC['ilAccess'];
        $ilErr = $DIC['ilErr'];
        $lng = $DIC['lng'];
        $ilUser = $DIC['ilUser'];
        $http = $DIC->http();
        $refinery = $DIC->refinery();

        $a_target = (int) $a_target;

        if (substr($a_add, 0, 5) == 'rcode') {
            if ($ilUser->getId() == ANONYMOUS_USER_ID) {
                $target = '';
                if ($http->wrapper()->query()->has('target')) {
                    $target = $http->wrapper()->query()->retrieve(
                        'target',
                        $refinery->kindlyTo()->string()
                    );
                }
                // Redirect to login for anonymous
                ilUtil::redirect(
                    "login.php?target=" . $target . "&cmd=force_login&lang=" .
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

    public function editMapSettingsObject(): void
    {
        $this->setSubTabs("properties");
        $this->tabs_gui->activateTab('settings');
        $this->tabs_gui->activateSubTab('crs_map_settings');

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
        $loc_prop->setLatitude((float) $latitude);
        $loc_prop->setLongitude((float) $longitude);
        $loc_prop->setZoom((int) $zoom);
        $form->addItem($loc_prop);

        $form->addCommandButton("saveMapSettings", $this->lng->txt("save"));

        $this->tpl->setVariable("ADM_CONTENT", $form->getHTML());
        //$this->tpl->show();
    }

    /**
     * @todo centralize for course, group, others
     */
    public function saveMapSettingsObject(): void
    {
        $location = [];
        if ($this->http->wrapper()->post()->has('location')) {
            $custom_transformer = $this->refinery->custom()->transformation(
                fn ($array) => $array
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
        $this->object->setLatitude($location['latitude']);
        $this->object->setLongitude($location['longitude']);
        $this->object->setLocationZoom($location['zoom']);
        $this->object->setEnableCourseMap($enable_map);
        $this->object->update();
        $this->ctrl->redirect($this, "editMapSettings");
    }

    /**
     * @inheritDoc
     */
    public function modifyItemGUI(ilObjectListGUI $a_item_list_gui, array $a_item_data): void
    {
        ilObjCourseGUI::_modifyItemGUI(
            $a_item_list_gui,
            'ilcoursecontentgui',
            $a_item_data,
            $this->object->getAboStatus(),
            $this->object->getRefId(),
            $this->object->getId()
        );
    }

    public static function _modifyItemGUI(
        ilObjectListGUI $a_item_list_gui,
        string $a_cmd_class,
        array $a_item_data,
        bool $a_abo_status,
        int $a_course_ref_id,
        int $a_course_obj_id,
        int $a_parent_ref_id = 0
    ): void {
        global $DIC;
        $lng = $DIC->language();
        $ilAccess = $DIC->access();

        // this is set for folders within the course
        if ($a_parent_ref_id == 0) {
            $a_parent_ref_id = $a_course_ref_id;
        }

        // Special handling for tests in courses with learning objectives
        if ($a_item_data['type'] == 'tst' && ilObjCourse::_lookupViewMode($a_course_obj_id) == ilContainer::VIEW_OBJECTIVE) {
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
    }

    /**
     * @inheritDoc
     */
    public function setContentSubTabs(): void
    {
        global $DIC;

        $ilAccess = $DIC['ilAccess'];
        $lng = $DIC['lng'];
        $ilCtrl = $DIC['ilCtrl'];

        if ($this->object->getType() != 'crs') {
            return;
        }
        if (!$this->access->checkAccess(
            'write',
            '',
            $this->object->getRefId(),
            'crs',
            $this->object->getId()
        )) {
            $is_tutor = false;
            // No further tabs if objective view or archives
            if ($this->object->enabledObjectiveView()) {
                return;
            }
        } else {
            $is_tutor = true;
        }

        if (!$this->isActiveAdministrationPanel()) {
            $this->tabs_gui->addSubTab(
                "view_content",
                $lng->txt("view"),
                $this->ctrl->getLinkTargetByClass("ilobjcoursegui", "view")
            );
        } else {
            $this->tabs_gui->addSubTab(
                "view_content",
                $lng->txt("view"),
                $this->ctrl->getLinkTargetByClass("ilobjcoursegui", "disableAdministrationPanel")
            );
        }
        //}

        $this->addStandardContainerSubTabs(false);
    }

    public function askResetObject(): void
    {
        $this->tpl->setOnScreenMessage('question', $this->lng->txt('crs_objectives_reset_sure'));
        $confirm = new ilConfirmationGUI();
        $confirm->setFormAction($this->ctrl->getFormAction($this));
        $confirm->setConfirm($this->lng->txt('reset'), 'reset');
        $confirm->setCancel($this->lng->txt('cancel'), 'cancel');
        $this->tpl->setContent($confirm->getHTML());
    }

    public function resetObject(): void
    {
        $usr_results = new ilLOUserResults($this->object->getId(), $GLOBALS['DIC']['ilUser']->getId());
        $usr_results->delete();
        ilLOTestRun::deleteRuns(
            $this->object->getId(),
            $GLOBALS['DIC']['ilUser']->getId()
        );

        $tmp_obj_res = new ilCourseObjectiveResult($this->user->getId());
        $tmp_obj_res->reset($this->object->getId());

        $this->user->deletePref('crs_objectives_force_details_' . $this->object->getId());
        $this->tpl->setOnScreenMessage('success', $this->lng->txt('crs_objectives_reseted'));
        $this->viewObject();
    }

    public function __checkStartObjects(): bool
    {
        if ($this->access->checkAccess('write', '', $this->object->getRefId())) {
            return true;
        }
        $this->start_obj = new ilContainerStartObjects(
            $this->object->getRefId(),
            $this->object->getId()
        );
        if (count($this->start_obj->getStartObjects()) &&
            !$this->start_obj->allFullfilled($this->user->getId())) {
            return false;
        }
        return true;
    }

    /**
     * @inheritDoc
     */
    public function prepareOutput(bool $show_subobjects = true): bool
    {
        if (!$this->getCreationMode()) {
            $settings = ilMemberViewSettings::getInstance();
            if ($settings->isActive() && $settings->getContainer() != $this->object->getRefId()) {
                $settings->setContainer($this->object->getRefId());
                $this->rbac_system->initMemberView();
            }
        }
        return parent::prepareOutput($show_subobjects);
    }

    /**
     * Create a course mail signature
     * @return string
     */
    public function createMailSignature(): string
    {
        $link = chr(13) . chr(10) . chr(13) . chr(10);
        $link .= $this->lng->txt('crs_mail_permanent_link');
        $link .= chr(13) . chr(10) . chr(13) . chr(10);
        $link .= ilLink::_getLink($this->object->getRefId());
        return rawurlencode(base64_encode($link));
    }

    protected function initHeaderAction(?string $sub_type = null, ?int $sub_id = null): ?ilObjectListGUI
    {
        $lg = parent::initHeaderAction($sub_type, $sub_id);

        if ($lg && $this->ref_id && ilCourseParticipants::_isParticipant($this->ref_id, $this->user->getId())) {
            // certificate

            $validator = new ilCertificateDownloadValidator();
            if ($validator->isCertificateDownloadable($this->user->getId(), $this->object->getId())) {
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

    /**
     * @todo get rid of cert logger
     */
    public function deliverCertificateObject(): void
    {
        global $DIC;

        $user_id = null;
        if ($this->access->checkAccess('manage_members', '', $this->ref_id)) {
            $user_id = 0;
            if ($this->http->wrapper()->query()->has('member_id')) {
                $user_id = $this->http->wrapper()->query()->retrieve(
                    'member_id',
                    $this->refinery->kindlyTo()->int()
                );
            }
        }
        if (!$user_id) {
            $user_id = $this->user->getId();
        }

        $objId = $this->object->getId();

        $validator = new ilCertificateDownloadValidator();

        if (false === $validator->isCertificateDownloadable($user_id, $objId)) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt("permission_denied"), true);
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

    protected function afterSaveCallback(): void
    {
        $this->ctrl->redirectByClass(array('ilrepositorygui', 'ilobjcoursegui', 'illoeditorgui'), 'materials');
    }

    public function saveSortingObject(): void
    {
        $post_position = (array) ($this->http->request()->getParsedBody()['position'] ?? []);
        if (isset($post_position['lobj'])) {
            $lobj = $post_position['lobj'];
            $objective_order = array();
            foreach ($lobj as $objective_id => $materials) {
                $objective_order[$objective_id] = $materials[0];
                unset($lobj[$objective_id][0]);
            }
            // objective order
            asort($objective_order);
            $pos = 0;
            foreach (array_keys($objective_order) as $objective_id) {
                $obj = new ilCourseObjective($this->object, $objective_id);
                $obj->writePosition(++$pos);
            }

            // material order
            foreach ($lobj as $objective_id => $materials) {
                $objmat = new ilCourseObjectiveMaterials($objective_id);

                asort($materials);
                $pos = 0;
                foreach (array_keys($materials) as $ass_id) {
                    $objmat->writePosition($ass_id, ++$pos);
                }
            }
        }
        parent::saveSortingObject();
    }

    protected function redirectLocToTestConfirmedObject(): void
    {
        $tid = 0;
        if ($this->http->wrapper()->query()->has('tid')) {
            $tid = $this->http->wrapper()->query()->retrieve(
                'tid',
                $this->refinery->kindlyTo()->int()
            );
        }
        ilUtil::redirect(ilLink::_getLink($tid));
    }

    protected function redirectLocToTestObject($a_force_new_run = null): void
    {
        $tid = 0;
        if ($this->http->wrapper()->query()->has('tid')) {
            $tid = $this->http->wrapper()->query()->retrieve(
                'tid',
                $this->refinery->kindlyTo()->int()
            );
        }
        $objective_id = 0;
        if ($this->http->wrapper()->query()->has('objective_id')) {
            $objective_id = $this->http->wrapper()->query()->retrieve(
                'objective_id',
                $this->refinery->kindlyTo()->int()
            );
        }

        $res = new ilLOUserResults(
            $this->object->getId(),
            $this->user->getId()
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
            $objective_ids = ilCourseObjective::_getObjectiveIds($this->object->getId(), true);

            // do not disable objective question if all are passed
            if (count($objective_ids) === count($passed)) {
                $has_completed = true;
                $passed = array();
            }
        }

        if ($has_completed) {
            // show confirmation
            $this->redirectLocToTestConfirmation($objective_id, $tid);
            return;
        }
        ilUtil::redirect(ilLink::_getLink($tid));
    }

    /**
     * Show confirmation whether user wants to start a new run or resume a previous run
     */
    protected function redirectLocToTestConfirmation(int $a_objective_id, int $a_test_id): void
    {
        $confirm = new ilConfirmationGUI();
        $confirm->setFormAction($GLOBALS['DIC']['ilCtrl']->getFormAction($this));

        if ($a_objective_id) {
            $question = $this->lng->txt('crs_loc_objective_passed_confirmation');
        } else {
            $question = $this->lng->txt('crs_loc_objectives_passed_confirmation');
        }

        $confirm->addHiddenItem('objective_id', (string) $a_objective_id);
        $confirm->addHiddenItem('tid', (string) $a_test_id);
        $confirm->setConfirm($this->lng->txt('crs_loc_tst_start'), 'redirectLocToTestConfirmed');
        $confirm->setCancel($this->lng->txt('cancel'), 'view');

        $this->tpl->setOnScreenMessage('question', $question);
        $this->tpl->setContent($confirm->getHTML());
    }

    /**
     * @return array localroles
     * @param  int[] $a_exclude a list of role ids which will not added to the results (optional)
     * returns all local roles [role_id] => title
     */
    public function getLocalRoles(array $a_exclude = array()): array
    {
        $crs_admin = $this->object->getDefaultAdminRole();
        $crs_member = $this->object->getDefaultMemberRole();
        $local_roles = $this->object->getLocalCourseRoles(false);
        $crs_roles = [];

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

        if ($a_exclude !== []) {
            foreach ($a_exclude as $excluded_role) {
                if (isset($crs_roles[$excluded_role])) {
                    unset($crs_roles[$excluded_role]);
                }
            }
        }
        return $crs_roles;
    }

    protected function hasAdminPermission(): bool
    {
        return
            ilCourseParticipant::_getInstanceByObjId($this->object->getId(), $this->user->getId())->isAdmin() ||
            $this->checkPermissionBool('edit_permission');
    }

    protected function jump2UsersGalleryObject(): void
    {
        $this->ctrl->redirectByClass(ilUsersGalleryGUI::class);
    }

    /**
     * @inheritDoc
     */
    public function setSideColumnReturn(): void
    {
        $this->ctrl->setReturn($this, "view");
    }
} // END class.ilObjCourseGUI
