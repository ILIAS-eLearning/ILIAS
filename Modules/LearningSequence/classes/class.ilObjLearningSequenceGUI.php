<?php

declare(strict_types=1);

/**
 * Class ilObjLearningSequenceGUI
 *
 * @ilCtrl_isCalledBy ilObjLearningSequenceGUI: ilRepositoryGUI
 * @ilCtrl_isCalledBy ilObjLearningSequenceGUI: ilAdministrationGUI
 * @ilCtrl_Calls ilObjLearningSequenceGUI: ilPermissionGUI
 * @ilCtrl_Calls ilObjLearningSequenceGUI: ilInfoScreenGUI
 * @ilCtrl_Calls ilObjLearningSequenceGUI: ilCommonActionDispatcherGUI
 * @ilCtrl_Calls ilObjLearningSequenceGUI: ilColumnGUI
 * @ilCtrl_Calls ilObjLearningSequenceGUI: ilObjectCopyGUI
 * @ilCtrl_Calls ilObjLearningSequenceGUI: ilExportGUI
 * @ilCtrl_Calls ilObjLearningSequenceGUI: ilContainerLinkListGUI
 * @ilCtrl_Calls ilObjLearningSequenceGUI: ilObjLearningSequenceSettingsGUI
 * @ilCtrl_Calls ilObjLearningSequenceGUI: ilObjLearningSequenceContentGUI
 * @ilCtrl_Calls ilObjLearningSequenceGUI: ilObjLearningSequenceLearnerGUI
 * @ilCtrl_Calls ilObjLearningSequenceGUI: ilLearningSequenceMembershipGUI
 * @ilCtrl_Calls ilObjLearningSequenceGUI: ilLearningProgressGUI
 *
 * @ilCtrl_Calls ilObjLearningSequenceGUI: ilObjLearningModuleGUI
 * @ilCtrl_Calls ilObjLearningSequenceGUI: ilObjFileBasedLMGUI
 * @ilCtrl_Calls ilObjLearningSequenceGUI: ilObjSAHSLearningModuleGUI
 * @ilCtrl_Calls ilObjLearningSequenceGUI: ilObjContentPageGUI
 * @ilCtrl_Calls ilObjLearningSequenceGUI: ilObjExerciseGUI
 * @ilCtrl_Calls ilObjLearningSequenceGUI: ilObjFileGUI
 * @ilCtrl_Calls ilObjLearningSequenceGUI: ilObjIndividualAssessmentGUI
 * @ilCtrl_Calls ilObjLearningSequenceGUI: ilIndividualAssessmentSettingsGUI
 * @ilCtrl_Calls ilObjLearningSequenceGUI: ilObjTestGUI
 * @ilCtrl_Calls ilObjLearningSequenceGUI: ilObjSurveyGUI

 */
class ilObjLearningSequenceGUI extends ilContainerGUI
{
    const CMD_VIEW = "view";
    const CMD_LEARNER_VIEW = "learnerView";
    const CMD_CONTENT = "manageContent";

    const CMD_MEMBERS = "members";
    const CMD_MANAGE_MEMBERS = "participants";
    const CMD_MEMBERS_GALLERY = "jump2UsersGallery";

    const CMD_INFO = "showSummary";
    const CMD_INFO_SCREEN = "infoScreen";
    const CMD_SETTINGS = "settings";
    const CMD_PERMISSIONS = "perm";
    const CMD_LP = "learningProgress";
    const CMD_EXPORT = "export";
    const CMD_IMPORT = "importFile";
    const CMD_CREATE = "create";
    const CMD_SAVE = "save";
    const CMD_CANCEL = "cancel";
    const CMD_UNPARTICIPATE = "unparticipate";
    const CMD_ADD_TO_DESK = "addToDesk";
    const CMD_REMOVE_FROM_DESK = "removeFromDesk";
    const CMD_LINK = "link";
    const CMD_CANCEL_LINK = "cancelMoveLink";
    const CMD_CUT = "cut";
    const CMD_CANCEL_CUT = "cancelCut";
    const CMD_CUT_SHOWTREE = "showPasteTree";
    const CMD_CUT_CLIPBOARD = "keepObjectsInClipboard";
    const CMD_DELETE = "delete";
    const CMD_CANCEL_DELETE = "cancelDelete";
    const CMD_DELETE_CONFIRMED = "confirmedDelete";
    const CMD_PERFORM_PASTE = 'performPasteIntoMultipleObjects';
    const CMD_SHOW_TRASH = 'trash';
    const CMD_UNDELETE = 'undelete';

    const TAB_VIEW_CONTENT = "view_content";
    const TAB_MANAGE = "manage";
    const TAB_CONTENT_MAIN = "manage_content_maintab";
    const TAB_INFO = "show_summary";
    const TAB_SETTINGS = "settings";
    const TAB_PERMISSIONS = "perm_settings";
    const TAB_MEMBERS = "members";
    const TAB_LP = "learning_progress";
    const TAB_EXPORT = "export";

    const MAIL_ALLOWED_ALL = 1;
    const MAIL_ALLOWED_TUTORS = 2;

    public $object;

    public static function _goto(string $target)
    {
        global $DIC;

        $ctrl = $DIC->ctrl();
        $id = explode("_", $target);
        $ctrl->setTargetScript("ilias.php");
        $ctrl->initBaseClass("ilRepositoryGUI");
        $ctrl->setParameterByClass("ilobjlearningsequencegui", "ref_id", $id[0]);
        $ctrl->redirectByClass(array( "ilRepositoryGUI", "ilobjlearningsequencegui" ), self::CMD_VIEW);
    }

    public function __construct()
    {
        $this->ref_id = (int) $_GET['ref_id'];
        parent::__construct([], $this->ref_id, true, false);

        $this->obj_type = ilObjLearningSequence::OBJ_TYPE;

        global $DIC;
        $this->ctrl = $DIC['ilCtrl'];
        $this->lng = $DIC['lng'];
        $this->user = $DIC['ilUser'];
        $this->tabs = $DIC['ilTabs'];
        $this->toolbar = $DIC['ilToolbar'];
        $this->help = $DIC['ilHelp'];
        $this->settings = $DIC['ilSetting'];
        $this->access = $DIC['ilAccess'];
        $this->rbac_review = $DIC['rbacreview'];
        $this->ui_factory = $DIC['ui.factory'];
        $this->ui_renderer = $DIC['ui.renderer'];
        $this->log = $DIC["ilLoggerFactory"]->getRootLogger();
        $this->app_event_handler = $DIC['ilAppEventHandler'];
        $this->navigation_history = $DIC['ilNavigationHistory'];
        $this->obj_definition = $DIC['objDefinition'];
        $this->obj_service = $DIC->object();
        $this->toolbar = $DIC['ilToolbar'];

        $this->help->setScreenIdComponent($this->obj_type);
        $this->lng->loadLanguageModule($this->obj_type);

        $this->object = $this->getObject();
    }

    protected function getCurrentItemLearningProgress()
    {
        $usr_id = (int) $this->user->getId();
        $items = $this->getLearnerItems($usr_id);
        $current_item_ref_id = $this->getCurrentItemForLearner($usr_id);
        foreach ($items as $index => $item) {
            if ($item->getRefId() === $current_item_ref_id) {
                return $item->getLearningProgressStatus();
            }
        }
    }

    public function executeCommand()
    {
        $next_class = $this->ctrl->getNextClass($this);
        $cmd = $this->ctrl->getCmd();

        //exit real early for LP-checking.
        if($cmd === LSControlBuilder::CMD_CHECK_CURRENT_ITEM_LP) {
            print $this->getCurrentItemLearningProgress();
            exit;
        }

        $tpl = $this->tpl;
        parent::prepareOutput();
        $this->addToNavigationHistory();
        //showRepTree is from containerGUI;
        //LSO will attach allowed subitems to whitelist
        //see: $this::getAdditionalWhitelistTypes
        $this->showRepTree();

        $in_player = (
            $next_class === 'ilobjlearningsequencelearnergui'
            && $cmd === 'view'
        );

        $tpl->setPermanentLink("lso", $this->ref_id);

        switch ($next_class) {
            case "ilcommonactiondispatchergui":
                $gui = ilCommonActionDispatcherGUI::getInstanceFromAjaxCall();
                $this->ctrl->forwardCommand($gui);
                break;
            case "ilinfoscreengui":
                $this->info($cmd);
                break;
            case "ilpermissiongui":
                $this->permissions($cmd);
                break;
            case "ilobjlearningsequencesettingsgui":
                $this->settings($cmd);
                break;
            case "ilobjlearningsequencecontentgui":
                $this->manageContent($cmd);
                break;
            case "ilobjlearningsequencelearnergui":
                $this->learnerView($cmd);
                break;
            case "illearningsequencemembershipgui":
                $this->manage_members($cmd);
                break;
            case 'ilmailmembersearchgui':
                $this->mail();
                break;
            case 'illearningprogressgui':
                $this->learningProgress($cmd);
                break;
            case 'ilexportgui':
                $this->export();
                break;
            case 'ilobjectcopygui':
                $cp = new ilObjectCopyGUI($this);
                $cp->setType('lso');
                $this->ctrl->forwardCommand($cp);
                break;
            case 'ilobjindividualassessmentgui':
                $struct = ['ilrepositorygui','ilobjindividualassessmentgui'];
                if ($cmd === 'edit') {
                    $struct[] = 'ilindividualassessmentsettingsgui';
                }
                $this->ctrl->redirectByClass($struct, $cmd);

                break;

            case false:
                if ($cmd === '') {
                    $cmd = self::CMD_VIEW;
                }

                switch ($cmd) {
                    case self::CMD_IMPORT:
                        $this->importFileObject();
                        break;
                    case self::CMD_INFO:
                    case self::CMD_INFO_SCREEN:
                        $this->info();
                        break;
                    case self::CMD_VIEW:
                    case self::CMD_LEARNER_VIEW:
                    case self::CMD_CONTENT:
                    case self::CMD_MEMBERS:
                    case self::CMD_SETTINGS:
                    case self::CMD_SAVE:
                    case self::CMD_CREATE:
                    case self::CMD_LP:
                    case self::CMD_UNPARTICIPATE:
                        $this->$cmd();
                        break;
                    case self::CMD_CANCEL:
                        if ($this->getCreationMode()) {
                            $this->cancelCreation();
                        }
                        break;
                    case self::CMD_REMOVE_FROM_DESK:
                        $this->removeFromDeskObject();
                        $this->view();
                        break;
                    case self::CMD_ADD_TO_DESK:
                        $this->addToDeskObject();
                        $this->view();
                        break;
                    case self::CMD_CUT:
                        $this->cutObject();
                        break;
                    case self::CMD_CUT_SHOWTREE:
                        $this->showPasteTreeObject();
                        break;
                    case self::CMD_CUT_CLIPBOARD:
                        $this->keepObjectsInClipboardObject();
                        break;
                    case self::CMD_LINK:
                        $this->linkObject();
                        break;
                    case self::CMD_DELETE:
                        $this->deleteObject();
                        break;
                    case self::CMD_DELETE_CONFIRMED:
                        $this->confirmedDeleteObject();
                        break;
                    case self::CMD_PERFORM_PASTE:
                        $this->performPasteIntoMultipleObjectsObject();
                        break;
                    case self::CMD_SHOW_TRASH:
                        $this->trashObject();
                        break;
                    case self::CMD_UNDELETE:
                        $this->undeleteObject();
                        break;

                    case self::CMD_CANCEL_CUT:
                    case self::CMD_CANCEL_DELETE:
                    case self::CMD_CANCEL_LINK:
                        $cmd = self::CMD_CONTENT;
                        $this->$cmd();
                        break;

                    default:
                        throw new ilException("ilObjLearningSequenceGUI: Invalid command '$cmd'");
                }
                break;
            default:
                throw new ilException("ilObjLearningSequenceGUI: Can't forward to next class $next_class");
        }

        if (!$in_player) {
            $this->addHeaderAction();
        }
    }

    public function addToNavigationHistory()
    {
        if (
            !$this->getCreationMode() &&
            $this->access->checkAccess('read', '', $this->ref_id)
        ) {
            $link = ilLink::_getLink($this->ref_id, $this->obj_type);
            $this->navigation_history->addItem($this->ref_id, $link, $this->obj_type);
        }
    }

    protected function info(string $cmd = self::CMD_INFO)
    {
        $this->tabs->setTabActive(self::TAB_INFO);
        $this->ctrl->setCmdClass('ilinfoscreengui');
        $this->ctrl->setCmd($cmd);
        $info = new ilInfoScreenGUI($this);
        $this->ctrl->forwardCommand($info);
    }

    protected function permissions(string $cmd = self::CMD_PERMISSIONS)
    {
        $this->tabs->setTabActive(self::TAB_PERMISSIONS);
        $perm_gui = new ilPermissionGUI($this);
        $this->ctrl->setCmd($cmd);
        $this->ctrl->forwardCommand($perm_gui);
    }

    protected function settings(string $cmd = self::CMD_SETTINGS)
    {
        $this->tabs->activateTab(self::TAB_SETTINGS);
        $gui = new ilObjLearningSequenceSettingsGUI(
            $this->getObject(),
            $this->ctrl,
            $this->lng,
            $this->tpl,
            $this->obj_service
        );
        $this->ctrl->setCmd($cmd);
        $this->ctrl->forwardCommand($gui);
    }

    protected function view()
    {
        $this->tabs->clearSubTabs();
        if ($this->checkAccess("write")) {
            $this->manageContent(self::CMD_CONTENT);
            return;
        }
        if ($this->checkAccess("read")) {
            $this->learnerView(self::CMD_LEARNER_VIEW);
            return;
        }
        $this->info(self::CMD_INFO);
    }

    protected function manageContent(string $cmd = self::CMD_CONTENT)
    {
        $this->tabs->activateTab(self::TAB_CONTENT_MAIN);
        $this->addSubTabsForContent($cmd);
        $this->tabs->activateSubTab(self::TAB_MANAGE);

        $gui = new ilObjLearningSequenceContentGUI(
            $this,
            $this->ctrl,
            $this->tpl,
            $this->lng,
            $this->access,
            new ilConfirmationGUI(),
            new LSItemOnlineStatus()
        );
        $this->ctrl->setCmd($cmd);
        $this->ctrl->forwardCommand($gui);
    }

    protected function learnerView(string $cmd = self::CMD_LEARNER_VIEW)
    {
        $this->tabs->activateTab(self::TAB_CONTENT_MAIN);
        $this->addSubTabsForContent($cmd);
        $this->tabs->activateSubTab(self::TAB_VIEW_CONTENT);

        $usr_id = (int) $this->user->getId();
        $items = $this->getLearnerItems($usr_id);
        $current_item_ref_id = $this->getCurrentItemForLearner($usr_id);

        $gui = new ilObjLearningSequenceLearnerGUI(
            $this->getObject(),
            $usr_id,
            $items,
            $current_item_ref_id,
            $this->ctrl,
            $this->lng,
            $this->tpl,
            $this->toolbar,
            $this->ui_factory,
            $this->ui_renderer
        );

        $this->ctrl->setCmd($cmd);
        $this->ctrl->forwardCommand($gui);
    }

    protected function members()
    {
        $may_manage_members = $this->checkAccess("edit_members");
        $this->ctrl->setCmdClass('ilLearningSequenceMembershipGUI');
        if ($may_manage_members) {
            $this->manage_members(self::CMD_MANAGE_MEMBERS);
        } else {
            $this->manage_members(self::CMD_MEMBERS_GALLERY);
        }
    }

    protected function manage_members(string $cmd = self::CMD_MANAGE_MEMBERS)
    {
        $this->tabs->setTabActive(self::TAB_MEMBERS);

        $ms_gui = new ilLearningSequenceMembershipGUI(
            $this,
            $this->getObject(),
            $this->getTrackingObject(),
            ilPrivacySettings::_getInstance(),
            $this->lng,
            $this->ctrl,
            $this->access,
            $this->rbac_review,
            $this->settings,
            $this->toolbar
        );

        $this->ctrl->setCmd($cmd);
        $this->ctrl->forwardCommand($ms_gui);
    }

    protected function learningProgress(string $cmd = self::CMD_LP)
    {
        $this->tabs->setTabActive(self::TAB_LP);

        $for_user = $this->user->getId();

        if ($_GET['user_id']) {
            $for_user = $_GET['user_id'];
        }

        $lp_gui = new ilLearningProgressGUI(
            ilLearningProgressGUI::LP_CONTEXT_REPOSITORY,
            $this->getObject()->getRefId(),
            $for_user
        );

        if ($cmd === self::CMD_LP) {
            $cmd = '';
        }

        $this->ctrl->setCmd($cmd);
        $this->ctrl->forwardCommand($lp_gui);
    }

    protected function export()
    {
        $this->tabs->setTabActive(self::TAB_EXPORT);
        $gui = new ilExportGUI($this);
        $gui->addFormat("xml");

        $this->ctrl->forwardCommand($gui);
    }

    protected function initDidacticTemplate(ilPropertyFormGUI $form)
    {
        return $form;
    }

    protected function create()
    {
        parent::createObject();
    }

    protected function save()
    {
        parent::saveObject();
    }

    protected function afterSave(ilObject $new_object)
    {
        $participant = new ilLearningSequenceParticipants(
            (int) $new_object->getId(),
            $this->log,
            $this->app_event_handler,
            $this->settings
        );

        $participant->add($this->user->getId(), IL_LSO_ADMIN);
        $participant->updateNotification($this->user->getId(), $this->settings->get('mail_lso_admin_notification', true));


        $settings = new \ilContainerSortingSettings($new_object->getId());
        $settings->setSortMode(\ilContainer::SORT_MANUAL);
        $settings->setSortDirection(\ilContainer::SORT_DIRECTION_ASC);
        $settings->setSortNewItemsOrder(\ilContainer::SORT_NEW_ITEMS_ORDER_CREATION);
        $settings->setSortNewItemsPosition(\ilContainer::SORT_NEW_ITEMS_POSITION_BOTTOM);
        $settings->save();

        ilUtil::sendSuccess($this->lng->txt('object_added'), true);
        $this->ctrl->setParameter($this, "ref_id", $new_object->getRefId());
        ilUtil::redirect(
            $this->getReturnLocation(
                "save",
                $this->ctrl->getLinkTarget($this, self::CMD_SETTINGS, "", false, false)
            )
        );
    }

    public function unparticipate()
    {
        if ($this->checkAccess('unparticipate')) {
            $usr_id = (int) $this->user->getId();
            $this->getObject()->leave($usr_id);
            $this->learnerView();
        }
    }

    protected function removeMember(int $usr_id)
    {
        $this->ls_object->leave($usr_id);
    }

    public function getTabs()
    {
        if ($this->checkAccess("read")) {
            $this->tabs->addTab(
                self::TAB_CONTENT_MAIN,
                $this->lng->txt(self::TAB_CONTENT_MAIN),
                $this->ctrl->getLinkTarget($this, self::CMD_VIEW, "", false, false)
            );
        }

        if ($this->checkAccess("read") || $this->checkAccess("visible")) {
            $this->tabs->addTab(
                self::TAB_INFO,
                $this->lng->txt(self::TAB_INFO),
                $this->getLinkTarget(self::CMD_INFO)
            );
        }

        if ($this->checkAccess("write")) {
            $this->tabs->addTab(
                self::TAB_SETTINGS,
                $this->lng->txt(self::TAB_SETTINGS),
                $this->getLinkTarget(self::CMD_SETTINGS)
            );
        }

        if ($this->checkAccess("read")) {
            if ($this->checkAccess("manage_members")
                || (
                    $this->getObject()->getLSSettings()->getMembersGallery()
                    &&
                    $this->object->isMember((int) $this->user->getId())
                )
            ) {
                $this->tabs->addTab(
                    self::TAB_MEMBERS,
                    $this->lng->txt(self::TAB_MEMBERS),
                    $this->ctrl->getLinkTarget($this, self::CMD_MEMBERS, "", false, false)
                );
            }
        }

        if (ilObjUserTracking::_enabledLearningProgress() && $this->checkLPAccess()) {
            $this->tabs->addTab(
                self::TAB_LP,
                $this->lng->txt(self::TAB_LP),
                $this->getLinkTarget(self::CMD_LP)
            );
        }

        if ($this->checkAccess("write")) {
            $this->tabs->addTab(
                self::TAB_EXPORT,
                $this->lng->txt(self::TAB_EXPORT),
                $this->ctrl->getLinkTargetByClass("ilexportgui", "")
            );
        }

        if ($this->checkAccess("edit_permission")) {
            $this->tabs->addTab(
                self::TAB_PERMISSIONS,
                $this->lng->txt(self::TAB_PERMISSIONS),
                $this->getLinkTarget(self::CMD_PERMISSIONS)
            );
        }
    }

    public function renderObject()
    {
        // disables this method in ilContainerGUI
    }

    protected function addSubTabsForContent()
    {
        $this->tabs->addSubTab(
            self::TAB_VIEW_CONTENT,
            $this->lng->txt(self::TAB_VIEW_CONTENT),
            $this->getLinkTarget(self::CMD_LEARNER_VIEW)
        );

        if ($this->checkAccess("write")) {
            $this->tabs->addSubTab(
                self::TAB_MANAGE,
                $this->lng->txt(self::TAB_MANAGE),
                $this->getLinkTarget(self::CMD_CONTENT)
            );
        }
    }

    protected function checkAccess($which) : bool
    {
        return $this->access->checkAccess($which, "", $this->ref_id);
    }

    protected function checkLPAccess()
    {
        $ref_id = $this->getObject()->getRefId();
        $is_participant = ilLearningSequenceParticipants::_isParticipant($ref_id, $this->user->getId());

        $lp_access = ilLearningProgressAccess::checkAccess($ref_id, $is_participant);
        $may_edit_lp_settings = $this->checkAccess('edit_learning_progress');

        return ($lp_access || $may_edit_lp_settings);
    }

    protected function getLinkTarget(string $cmd) : string
    {
        $class = $this->getClassForTabs($cmd);
        $class_path = [
            strtolower('ilObjLearningSequenceGUI'),
            $class
        ];
        return $this->ctrl->getLinkTargetByClass($class_path, $cmd);
    }

    protected function getClassForTabs(string $cmd) : string
    {
        switch ($cmd) {
            case self::CMD_CONTENT:
                return 'ilObjLearningSequenceContentGUI';
            case self::CMD_LEARNER_VIEW:
                return 'ilObjLearningSequenceLearnerGUI';
            case self::CMD_SETTINGS:
                return 'ilObjLearningSequenceSettingsGUI';
            case self::CMD_INFO:
                return 'ilInfoScreenGUI';
            case self::CMD_PERMISSIONS:
                return 'ilPermissionGUI';
            case self::CMD_LP:
                return 'ilLearningProgressGUI';
        }

        throw new InvalidArgumentException('cannot resolve class for command: ' . $cmd);
    }

    public function createMailSignature()
    {
        $link = chr(13) . chr(10) . chr(13) . chr(10);
        $link .= $this->lng->txt('lso_mail_permanent_link');
        $link .= chr(13) . chr(10) . chr(13) . chr(10);
        $link .= ilLink::_getLink($this->object->getRefId());

        return rawurlencode(base64_encode($link));
    }

    protected function getLearnerItems(int $usr_id) : array
    {
        return $this->getObject()->getLSLearnerItems($usr_id);
    }

    protected function getCurrentItemForLearner(int $usr_id) : int
    {
        return $this->getObject()->getCurrentItemForLearner($usr_id);
    }

    public function getObject()
    {
        if ($this->object === null) {
            $this->object = ilObjLearningSequence::getInstanceByRefId($this->ref_id);
        }

        return $this->object;
    }

    protected function getTrackingObject() : ilObjUserTracking
    {
        return new ilObjUserTracking();
    }

    /**
     * @return [role_id] => title
     */
    public function getLocalRoles() : array
    {
        $local_roles = $this->object->getLocalLearningSequenceRoles(false);
        $lso_member = $this->object->getDefaultMemberRole();
        $lso_roles = array();

        if (in_array($lso_member, $local_roles)) {
            $lso_roles[$lso_member] = ilObjRole::_getTranslation(array_search($lso_member, $local_roles));
            unset($local_roles[$lso_roles[$lso_member]]);
        }

        foreach ($local_roles as $title => $role_id) {
            $lso_roles[$role_id] = ilObjRole::_getTranslation($title);
        }

        return $lso_roles;
    }

    /**
     * append additional types to ilRepositoryExplorerGUI's whitelist
     */
    protected function getAdditionalWhitelistTypes() : array
    {
        $types = array_filter(
            array_keys($this->obj_definition->getSubObjects('lso', false)),
            function ($type) {
                return $type !== 'rolf';
            }
        );

        return $types;
    }

    public function addCustomData($a_data)
    {
        $res_data = array();
        foreach ($a_data as $usr_id => $user_data) {
            $res_data[$usr_id] = $user_data;
            $udf_data = new ilUserDefinedData($usr_id);

            foreach ($udf_data->getAll() as $field => $value) {
                list($f, $field_id) = explode('_', $field);
                $res_data[$usr_id]['udf_' . $field_id] = (string) $value;
            }
        }

        return $res_data;
    }
}
