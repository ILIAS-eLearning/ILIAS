<?php

/**
 * For the purpose of streamlining the grading and learning-process status definition
 * outside of tests, SCORM courses e.t.c. the IndividualAssessment is used.
 * It caries a LPStatus, which is set Individually.
 *
 * @author Denis Klöpfer <denis.kloepfer@concepts-and-training.de>
 *
 * @ilCtrl_Calls ilObjIndividualAssessmentGUI: ilPermissionGUI
 * @ilCtrl_Calls ilObjIndividualAssessmentGUI: ilInfoScreenGUI
 * @ilCtrl_Calls ilObjIndividualAssessmentGUI: ilObjectCopyGUI
 * @ilCtrl_Calls ilObjIndividualAssessmentGUI: ilCommonActionDispatcherGUI
 * @ilCtrl_Calls ilObjIndividualAssessmentGUI: ilIndividualAssessmentSettingsGUI
 * @ilCtrl_Calls ilObjIndividualAssessmentGUI: ilIndividualAssessmentMembersGUI
 * @ilCtrl_Calls ilObjIndividualAssessmentGUI: ilLearningProgressGUI
 * @ilCtrl_Calls ilObjIndividualAssessmentGUI: ilExportGUI
 * @ilCtrl_Calls ilObjIndividualAssessmentGUI: ilObjectMetaDataGUI
 */

class ilObjIndividualAssessmentGUI extends ilObjectGUI
{
    const TAB_SETTINGS = 'settings';
    const TAB_INFO = 'info_short';
    const TAB_PERMISSION = 'perm_settings';
    const TAB_MEMBERS = 'members';
    const TAB_LP = 'learning_progress';
    const TAB_EXPORT = 'export';
    const TAB_META_DATA = "meta_data";

    /**
     * @var ilNavigationHistory
     */
    protected $ilNavigationHistory;

    /**
     * @var string
     */
    protected $type;

    /**
     * @var ilObjUser
     */
    protected $usr;

    /**
     * @var ilErrorHandling
     */
    protected $error_object;

    /**
     * @var ilAccessHandler
     */
    protected $ilAccess;

    public function __construct($a_data, $a_id = 0, $a_call_by_reference = true, $a_prepare_output = true)
    {
        global $DIC;
        $this->ilNavigationHistory = $DIC['ilNavigationHistory'];
        $this->type = 'iass';
        $this->tpl = $DIC['tpl'];
        $this->ctrl = $DIC['ilCtrl'];
        $this->usr = $DIC['ilUser'];
        $this->error_object = $DIC['ilErr'];
        $this->lng = $DIC['lng'];
        $this->lng->loadLanguageModule('iass');
        $this->tpl->loadStandardTemplate();

        parent::__construct($a_data, $a_id, $a_call_by_reference, $a_prepare_output);
    }

    public function addLocatorItems()
    {
        if (is_object($this->object)) {
            $this->locator->addItem($this->object->getTitle(), $this->ctrl->getLinkTarget($this, "view"), "", $this->object->getRefId());
        }
    }

    protected function recordIndividualAssessmentRead() {

        ilChangeEvent::_recordReadEvent(
            $this->object->getType(),
            $this->object->getRefId(),
            $this->object->getId(),
            $this->usr->getId()
        );

    }

    public function executeCommand()
    {
        $next_class = $this->ctrl->getNextClass($this);
        $cmd = $this->ctrl->getCmd();
        $this->prepareOutput();
        $this->addToNavigationHistory();

        switch ($next_class) {
            case 'ilpermissiongui':
                $this->tabs_gui->activateTab(self::TAB_PERMISSION);
                $ilPermissionGUI = new ilPermissionGUI($this);
                $this->ctrl->forwardCommand($ilPermissionGUI);
                break;
            case 'ilindividualassessmentsettingsgui':
                $this->tabs_gui->activateTab(self::TAB_SETTINGS);
                $gui = $this->object->getSettingsGUI();
                $this->ctrl->forwardCommand($gui);
                break;
            case 'ilindividualassessmentmembersgui':
                $this->membersObject();
                break;
            case 'ilinfoscreengui':
                $this->tabs_gui->activateTab(self::TAB_INFO);
                $info = $this->buildInfoScreen();
                $this->ctrl->forwardCommand($info);
                break;
            case 'illearningprogressgui':
                if (!$this->object->accessHandler()->mayViewObject()) {
                    $this->handleAccessViolation();
                }
                $this->tabs_gui->activateTab(self::TAB_LP);
                $learning_progress = new ilLearningProgressGUI(
                    ilLearningProgressGUI::LP_CONTEXT_REPOSITORY,
                    $this->object->getRefId(),
                    $this->usr->getId()
                );
                $this->ctrl->forwardCommand($learning_progress);
                break;
            case "ilcommonactiondispatchergui":
                $gui = ilCommonActionDispatcherGUI::getInstanceFromAjaxCall();
                $this->ctrl->forwardCommand($gui);
                break;
            case "ilexportgui":
                $this->tabs_gui->activateTab(self::TAB_EXPORT);
                $exp_gui = new ilExportGUI($this); // $this is the ilObj...GUI class of the resource
                $exp_gui->addFormat("xml");
                $ret = $this->ctrl->forwardCommand($exp_gui);
                break;
            case 'ilobjectmetadatagui':
                $this->checkPermissionBool("write");
                $this->tabs_gui->activateTab(self::TAB_META_DATA);
                $md_gui = new ilObjectMetaDataGUI($this->object);
                $this->ctrl->forwardCommand($md_gui);
                break;
            case 'ilobjectcopygui':
                $cp = new ilObjectCopyGUI($this);
                $cp->setType('crs');
                $this->ctrl->forwardCommand($cp);
                break;
            default:
                if (!$cmd) {
                    $cmd = 'view';
                    if ($this->object->accessHandler()->mayEditMembers()) {
                        $this->ctrl->setCmdClass('ilIndividualassessmentmembersgui');
                        $cmd = 'members';
                    }
                }
                $cmd .= 'Object';
                $this->$cmd();
            }
        return true;
    }

    public function viewObject()
    {
        $this->tabs_gui->activateTab(self::TAB_INFO);
        $this->ctrl->setCmd('showSummary');
        $this->ctrl->setCmdClass('ilinfoscreengui');
        $info = $this->buildInfoScreen();
        $this->ctrl->forwardCommand($info);
        $this->recordIndividualAssessmentRead();
    }

    public function membersObject()
    {
        $this->tabs_gui->activateTab(self::TAB_MEMBERS);
        $gui = $this->object->getMembersGUI();
        $this->ctrl->forwardCommand($gui);
    }

    protected function buildInfoScreen()
    {
        $info = new ilInfoScreenGUI($this);
        if ($this->object) {
            $record_gui = new ilAdvancedMDRecordGUI(ilAdvancedMDRecordGUI::MODE_INFO, 'iass', $this->object->getId());
            $record_gui->setInfoObject($info);
            $record_gui->parse();

            $info->addMetaDataSections($this->object->getId(), 0, $this->object->getType());

            $info = $this->addGeneralDataToInfo($info);
            if ($this->object->loadMembers()->userAllreadyMember($this->usr)) {
                $info = $this->addMemberDataToInfo($info);
            }
            $info = $this->addContactDataToInfo($info);
        }
        return $info;
    }

    protected function addMemberDataToInfo(ilInfoScreenGUI $info)
    {
        $member = $this->object->membersStorage()->loadMember($this->object, $this->usr);
        $info->addSection($this->txt('grading_info'));
        if ($member->finalized()) {
            $info->addProperty($this->txt('grading'), $this->getEntryForStatus($member->LPStatus()));
        }
        if ($member->notify() && $member->finalized()) {
            $info->addProperty($this->txt('grading_record'), nl2br($member->record()));
            if (($member->viewFile() || $view_self) && $member->fileName() && $member->fileName() != "") {
                $tpl = new ilTemplate("tpl.iass_user_file_download.html", true, true, "Modules/IndividualAssessment");
                $tpl->setVariable("FILE_NAME", $member->fileName());
                $tpl->setVariable("HREF", $this->ctrl->getLinkTarget($this, "downloadFile"));
                $info->addProperty($this->txt('iass_upload_file'), $tpl->get());
            }
        }

        return $info;
    }

    protected function downloadFileObject()
    {
        $member = $this->object->membersStorage()->loadMember($this->object, $this->usr);
        $file_storage = $this->object->getFileStorage();
        $file_storage->setUserId($this->usr->getId());
        ilUtil::deliverFile($file_storage->getFilePath(), $member->fileName());
    }

    protected function addGeneralDataToInfo(ilInfoScreenGUI $info)
    {
        $content = $this->object->getSettings()->getContent();
        if ($content !== null && $content !== '') {
            $info->addSection($this->txt('general'));
            $info->addProperty($this->txt('content'), $content);
        }
        return $info;
    }

    protected function addContactDataToInfo(ilInfoScreenGUI $info)
    {
        $info_settings = $this->object->getInfoSettings();
        if ($this->shouldShowContactInfo($info_settings)) {
            $info->addSection($this->txt('iass_contact_info'));
            $info->addProperty($this->txt('iass_contact'), $info_settings->getContact());
            $info->addProperty($this->txt('iass_responsibility'), $info_settings->getResponsibility());
            $info->addProperty($this->txt('iass_phone'), $info_settings->getPhone());
            $info->addProperty($this->txt('iass_mails'), $info_settings->getMails());
            $info->addProperty($this->txt('iass_consultation_hours'), $info_settings->getConsultationHours());
        }
        return $info;
    }

    protected function shouldShowContactInfo(ilIndividualAssessmentInfoSettings $info_settings)
    {
        $val = $info_settings->getContact();
        if ($val !== null && $val !== '') {
            return true;
        }
        $val = $info_settings->getResponsibility();
        if ($val !== null && $val !== '') {
            return true;
        }
        $val = $info_settings->getPhone();
        if ($val !== null && $val !== '') {
            return true;
        }
        $val = $info_settings->getMails();
        if ($val !== null && $val !== '') {
            return true;
        }
        $val = $info_settings->getConsultationHours();
        if ($val !== null && $val !== '') {
            return true;
        }
        return false;
    }

    public function getTabs()
    {
        if ($this->object->accessHandler()->mayViewObject()) {
            $this->tabs_gui->addTab(
                self::TAB_INFO,
                $this->txt('info_short'),
                $this->getLinkTarget('info')
                                    );
        }
        if ($this->object->accessHandler()->mayEditObject()) {
            $this->tabs_gui->addTab(
                self::TAB_SETTINGS,
                $this->txt('settings'),
                $this->getLinkTarget('settings')
                                    );
            $mdgui = new ilObjectMetaDataGUI($this->object);
            $mdtab = $mdgui->getTab();
            if ($mdtab) {
                $this->tabs_gui->addTab(
                    self::TAB_META_DATA,
                    $this->txt("meta_data"),
                    $mdtab
                );
            }
        }
        if ($this->object->accessHandler()->mayEditMembers()
            || $this->object->accessHandler()->mayGradeUser()
            || $this->object->accessHandler()->mayAmendGradeUser()
            || $this->object->accessHandler()->mayViewUser()) {
            $this->tabs_gui->addTab(
                self::TAB_MEMBERS,
                $this->txt('il_iass_members'),
                $this->getLinkTarget('members')
                                    );
        }
        if (($this->object->accessHandler()->mayViewUser()
            || $this->object->accessHandler()->mayGradeUser()
            || ($this->object->loadMembers()->userAllreadyMember($this->usr)
            && $this->object->isActiveLP()))
            && ilObjUserTracking::_enabledLearningProgress()) {
            $this->tabs_gui->addTab(
                self::TAB_LP,
                $this->txt('learning_progress'),
                $this->ctrl->getLinkTargetByClass('illearningprogressgui')
                                    );
        }

        if ($this->object->accessHandler()->mayEditObject()) {
            $this->tabs_gui->addTarget(
                self::TAB_EXPORT,
                $this->ctrl->getLinkTargetByClass('ilexportgui', ''),
                'export',
                'ilexportgui'
            );
        }

        if ($this->object->accessHandler()->mayEditPermissions()) {
            $this->tabs_gui->addTarget(
                self::TAB_PERMISSION,
                $this->ctrl->getLinkTargetByClass('ilpermissiongui', 'perm'),
                array(),
                'ilpermissiongui'
                                    );
        }
        parent::getTabs();
    }

    protected function getLinkTarget($a_cmd)
    {
        if ($a_cmd == 'settings') {
            return $this->ctrl->getLinkTargetByClass('ilindividualassessmentsettingsgui', 'edit');
        }
        if ($a_cmd == 'info') {
            return $this->ctrl->getLinkTarget($this, 'view');
        }
        if ($a_cmd == 'members') {
            return $this->ctrl->getLinkTargetByClass('ilindividualassessmentmembersgui', 'view');
        }
        return $this->ctrl->getLinkTarget($this, $a_cmd);
    }
    public function editObject()
    {
        $link = $this->getLinkTarget('settings');
        $this->ctrl->redirectToURL($link);
    }

    public function getBaseEditForm()
    {
        return $this->initEditForm();
    }

    public function handleAccessViolation()
    {
        $this->error_object->raiseError($this->txt("msg_no_perm_read"), $this->error_object->WARNING);
    }

    public static function _goto($a_target, $a_add = '')
    {
        global $DIC;
        if ($DIC['ilAccess']->checkAccess('write', '', $a_target)) {
            ilObjectGUI::_gotoRepositoryNode($a_target, 'edit');
        }
        if ($DIC['ilAccess']->checkAccess('read', '', $a_target)) {
            ilObjectGUI::_gotoRepositoryNode($a_target);
        }
    }

    protected function getEntryForStatus($a_status)
    {
        switch ($a_status) {
            case ilIndividualAssessmentMembers::LP_IN_PROGRESS:
                return $this->txt('iass_status_pending');
                break;
            case ilIndividualAssessmentMembers::LP_COMPLETED:
                return $this->txt('iass_status_completed');
                break;
            case ilIndividualAssessmentMembers::LP_FAILED:
                return $this->txt('iass_status_failed');
                break;
        }
    }

    protected function afterSave(ilObject $a_new_object)
    {
        ilUtil::sendSuccess($this->txt("iass_added"), true);
        $this->ctrl->setParameter($this, "ref_id", $a_new_object->getRefId());
        ilUtil::redirect($this->ctrl->getLinkTargetByClass('ilIndividualassessmentsettingsgui', 'edit', '', false, false));
    }

    public function addToNavigationHistory()
    {
        if (!$this->getCreationMode()) {
            if ($this->object->accessHandler()->mayViewObject()) {
                $link = ilLink::_getLink($_GET["ref_id"], "iass");
                $this->ilNavigationHistory->addItem($_GET['ref_id'], $link, 'iass');
            }
        }
    }

    protected function txt(string $code) : string
    {
        return $this->lng->txt($code);
    }
}
