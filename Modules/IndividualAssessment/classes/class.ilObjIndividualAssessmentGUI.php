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

require_once 'Services/Object/classes/class.ilObjectGUI.php';
require_once 'Modules/IndividualAssessment/classes/class.ilIndividualAssessmentLP.php';
require_once 'Services/Tracking/classes/class.ilObjUserTracking.php';


class ilObjIndividualAssessmentGUI extends ilObjectGUI
{
    const TAB_SETTINGS = 'settings';
    const TAB_INFO = 'info_short';
    const TAB_PERMISSION = 'perm_settings';
    const TAB_MEMBERS = 'members';
    const TAB_LP = 'learning_progress';
    const TAB_EXPORT = 'export';
    const TAB_META_DATA = "meta_data";

    public function __construct($a_data, $a_id = 0, $a_call_by_reference = true, $a_prepare_output = true)
    {
        global $DIC;
        $this->ilNavigationHistory = $DIC['ilNavigationHistory'];
        $this->type = 'iass';
        $this->tpl = $DIC['tpl'];
        $this->ctrl = $DIC['ilCtrl'];
        $this->usr = $DIC['ilUser'];
        $this->ilias = $DIC['ilias'];
        $this->lng = $DIC['lng'];
        $this->ilAccess = $DIC['ilAccess'];
        $this->lng->loadLanguageModule('iass');
        $this->tpl->getStandardTemplate();
        $this->locator = $DIC['ilLocator'];

        parent::__construct($a_data, $a_id, $a_call_by_reference, $a_prepare_output);
    }

    public function addLocatorItems()
    {
        if (is_object($this->object)) {
            $this->locator->addItem($this->object->getTitle(), $this->ctrl->getLinkTarget($this, "view"), "", $this->object->getRefId());
        }
    }

    public function executeCommand()
    {
        $next_class = $this->ctrl->getNextClass($this);
        $cmd = $this->ctrl->getCmd();
        $this->prepareOutput();
        $this->addToNavigationHistory();

        switch ($next_class) {
            case 'ilpermissiongui':
                $this->tabs_gui->setTabActive(self::TAB_PERMISSION);
                require_once 'Services/AccessControl/classes/class.ilPermissionGUI.php';
                $ilPermissionGUI = new ilPermissionGUI($this);
                $this->ctrl->forwardCommand($ilPermissionGUI);
                break;
            case 'ilindividualassessmentsettingsgui':
                $this->tabs_gui->setTabActive(self::TAB_SETTINGS);
                require_once 'Modules/IndividualAssessment/classes/class.ilIndividualAssessmentSettingsGUI.php';
                $gui = new ilIndividualAssessmentSettingsGUI($this, $this->ref_id);
                $this->ctrl->forwardCommand($gui);
                break;
            case 'ilindividualassessmentmembersgui':
                $this->membersObject();
                break;
            case 'ilinfoscreengui':
                $this->tabs_gui->setTabActive(self::TAB_INFO);
                require_once 'Services/InfoScreen/classes/class.ilInfoScreenGUI.php';
                $info = $this->buildInfoScreen();
                $this->ctrl->forwardCommand($info);
                break;
            case 'illearningprogressgui':
                if (!$this->object->accessHandler()->mayViewObject()) {
                    $this->handleAccessViolation();
                }
                require_once 'Services/Tracking/classes/class.ilLearningProgressGUI.php';
                $this->tabs_gui->setTabActive(self::TAB_LP);
                $learning_progress = new ilLearningProgressGUI(
                    ilLearningProgressGUI::LP_CONTEXT_REPOSITORY,
                    $this->object->getRefId(),
                    $this->usr->getId()
                );
                $this->ctrl->forwardCommand($learning_progress);
                break;
            case "ilcommonactiondispatchergui":
                include_once("Services/Object/classes/class.ilCommonActionDispatcherGUI.php");
                $gui = ilCommonActionDispatcherGUI::getInstanceFromAjaxCall();
                $this->ctrl->forwardCommand($gui);
                break;
            case "ilexportgui":
                include_once("./Services/Export/classes/class.ilExportGUI.php");
                $this->tabs_gui->setTabActive(self::TAB_EXPORT);
                $exp_gui = new ilExportGUI($this); // $this is the ilObj...GUI class of the resource
                $exp_gui->addFormat("xml");
                $ret = $this->ctrl->forwardCommand($exp_gui);
                break;
            case 'ilobjectmetadatagui':
                $this->checkPermissionBool("write");
                $this->tabs_gui->activateTab(self::TAB_META_DATA);
                include_once 'Services/Object/classes/class.ilObjectMetaDataGUI.php';
                $md_gui = new ilObjectMetaDataGUI($this->object);
                $this->ctrl->forwardCommand($md_gui);
                break;
            case 'ilobjectcopygui':
                include_once './Services/Object/classes/class.ilObjectCopyGUI.php';
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

    public function tabsGUI()
    {
        return $this->tabs_gui;
    }

    public function viewObject()
    {
        $this->tabs_gui->setTabActive(self::TAB_INFO);
        require_once 'Services/InfoScreen/classes/class.ilInfoScreenGUI.php';
        $this->ctrl->setCmd('showSummary');
        $this->ctrl->setCmdClass('ilinfoscreengui');
        $info = $this->buildInfoScreen();
        $this->ctrl->forwardCommand($info);
    }

    public function membersObject()
    {
        $this->tabs_gui->setTabActive(self::TAB_MEMBERS);
        require_once 'Modules/IndividualAssessment/classes/class.ilIndividualAssessmentMembersGUI.php';
        $gui = new ilIndividualAssessmentMembersGUI($this, $this->ref_id);
        $this->ctrl->forwardCommand($gui);
    }

    protected function buildInfoScreen()
    {
        $info = new ilInfoScreenGUI($this);
        if ($this->object) {
            include_once('Services/AdvancedMetaData/classes/class.ilAdvancedMDRecordGUI.php');
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
        $info->addSection($this->lng->txt('grading_info'));
        if ($member->finalized()) {
            $info->addProperty($this->lng->txt('grading'), $this->getEntryForStatus($member->LPStatus()));
        }
        if ($member->notify() && $member->finalized()) {
            $info->addProperty($this->lng->txt('grading_record'), nl2br($member->record()));
            if (($member->viewFile() || $view_self) && $member->fileName() && $member->fileName() != "") {
                $tpl = new ilTemplate("tpl.iass_user_file_download.html", true, true, "Modules/IndividualAssessment");
                $tpl->setVariable("FILE_NAME", $member->fileName());
                $tpl->setVariable("HREF", $this->ctrl->getLinkTarget($this, "downloadFile"));
                $info->addProperty($this->lng->txt('iass_upload_file'), $tpl->get());
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
        $content = $this->object->getSettings()->content();
        if ($content !== null && $content !== '') {
            $info->addSection($this->lng->txt('general'));
            $info->addProperty($this->lng->txt('content'), $content);
        }
        return $info;
    }

    protected function addContactDataToInfo(ilInfoScreenGUI $info)
    {
        $info_settings = $this->object->getInfoSettings();
        if ($this->shouldShowContactInfo($info_settings)) {
            $info->addSection($this->lng->txt('iass_contact_info'));
            $info->addProperty($this->lng->txt('iass_contact'), $info_settings->contact());
            $info->addProperty($this->lng->txt('iass_responsibility'), $info_settings->responsibility());
            $info->addProperty($this->lng->txt('iass_phone'), $info_settings->phone());
            $info->addProperty($this->lng->txt('iass_mails'), $info_settings->mails());
            $info->addProperty($this->lng->txt('iass_consultation_hours'), $info_settings->consultationHours());
        }
        return $info;
    }

    protected function shouldShowContactInfo(ilIndividualAssessmentInfoSettings $info_settings)
    {
        $val = $info_settings->contact();
        if ($val !== null && $val !== '') {
            return true;
        }
        $val = $info_settings->responsibility();
        if ($val !== null && $val !== '') {
            return true;
        }
        $val = $info_settings->phone();
        if ($val !== null && $val !== '') {
            return true;
        }
        $val = $info_settings->mails();
        if ($val !== null && $val !== '') {
            return true;
        }
        $val = $info_settings->consultationHours();
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
                $this->lng->txt('info_short'),
                $this->getLinkTarget('info')
            );
        }
        if ($this->object->accessHandler()->mayEditObject()) {
            $this->tabs_gui->addTab(
                self::TAB_SETTINGS,
                $this->lng->txt('settings'),
                $this->getLinkTarget('settings')
            );
            include_once "Services/Object/classes/class.ilObjectMetaDataGUI.php";
            $mdgui = new ilObjectMetaDataGUI($this->object);
            $mdtab = $mdgui->getTab();
            if ($mdtab) {
                $this->tabs_gui->addTab(
                    self::TAB_META_DATA,
                    $this->lng->txt("meta_data"),
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
                $this->lng->txt('il_iass_members'),
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
                $this->lng->txt('learning_progress'),
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

    public function getBaseEditForm()
    {
        return $this->initEditForm();
    }

    public function handleAccessViolation()
    {
        global $DIC;
        $DIC['ilias']->raiseError($DIC['lng']->txt("msg_no_perm_read"), $DIC['ilias']->error_obj->WARNING);
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
                return $this->lng->txt('iass_status_pending');
                break;
            case ilIndividualAssessmentMembers::LP_COMPLETED:
                return $this->lng->txt('iass_status_completed');
                break;
            case ilIndividualAssessmentMembers::LP_FAILED:
                return $this->lng->txt('iass_status_failed');
                break;
        }
    }

    protected function afterSave(ilObject $a_new_object)
    {
        ilUtil::sendSuccess($this->lng->txt("iass_added"), true);
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
}
