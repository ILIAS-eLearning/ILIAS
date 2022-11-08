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

use ILIAS\Data;
use ILIAS\HTTP\Wrapper\ArrayBasedRequestWrapper;

/**
 * Class ilObjLearningSequenceGUI
 * @ilCtrl_isCalledBy ilObjLearningSequenceGUI: ilRepositoryGUI
 * @ilCtrl_isCalledBy ilObjLearningSequenceGUI: ilAdministrationGUI
 * @ilCtrl_Calls      ilObjLearningSequenceGUI: ilPermissionGUI
 * @ilCtrl_Calls      ilObjLearningSequenceGUI: ilInfoScreenGUI
 * @ilCtrl_Calls      ilObjLearningSequenceGUI: ilCommonActionDispatcherGUI
 * @ilCtrl_Calls      ilObjLearningSequenceGUI: ilColumnGUI
 * @ilCtrl_Calls      ilObjLearningSequenceGUI: ilObjectCopyGUI
 * @ilCtrl_Calls      ilObjLearningSequenceGUI: ilExportGUI
 * @ilCtrl_Calls      ilObjLearningSequenceGUI: ilObjLearningSequenceSettingsGUI
 * @ilCtrl_Calls      ilObjLearningSequenceGUI: ilObjLearningSequenceContentGUI
 * @ilCtrl_Calls      ilObjLearningSequenceGUI: ilObjLearningSequenceLearnerGUI
 * @ilCtrl_Calls      ilObjLearningSequenceGUI: ilObjLearningSequenceLPPollingGUI
 * @ilCtrl_Calls      ilObjLearningSequenceGUI: ilLearningSequenceMembershipGUI
 * @ilCtrl_Calls      ilObjLearningSequenceGUI: ilLearningProgressGUI
 * @ilCtrl_Calls      ilObjLearningSequenceGUI: ilObjLearningModuleGUI
 * @ilCtrl_Calls      ilObjLearningSequenceGUI: ilObjFileBasedLMGUI
 * @ilCtrl_Calls      ilObjLearningSequenceGUI: ilObjSAHSLearningModuleGUI
 * @ilCtrl_Calls      ilObjLearningSequenceGUI: ilObjContentPageGUI
 * @ilCtrl_Calls      ilObjLearningSequenceGUI: ilObjExerciseGUI
 * @ilCtrl_Calls      ilObjLearningSequenceGUI: ilObjFileGUI
 * @ilCtrl_Calls      ilObjLearningSequenceGUI: ilObjIndividualAssessmentGUI
 * @ilCtrl_Calls      ilObjLearningSequenceGUI: ilIndividualAssessmentSettingsGUI
 * @ilCtrl_Calls      ilObjLearningSequenceGUI: ilObjTestGUI
 * @ilCtrl_Calls      ilObjLearningSequenceGUI: ilObjSurveyGUI
 * @ilCtrl_Calls      ilObjLearningSequenceGUI: ilObjFileUploadHandlerGUI
 * @ilCtrl_Calls ilObjLearningSequenceGUI: ilObjLearningSequenceEditIntroGUI, ilObjLearningSequenceEditExtroGUI
 */
class ilObjLearningSequenceGUI extends ilContainerGUI implements ilCtrlBaseClassInterface
{
    public const CMD_VIEW = "view";
    public const CMD_LEARNER_VIEW = "learnerView";
    public const CMD_CONTENT = "manageContent";

    public const CMD_MEMBERS = "members";
    public const CMD_MANAGE_MEMBERS = "participants";
    public const CMD_MEMBERS_GALLERY = "jump2UsersGallery";

    public const CMD_INFO = "showSummary";
    public const CMD_INFO_SCREEN = "infoScreen";
    public const CMD_SETTINGS = "settings";
    public const CMD_PERMISSIONS = "perm";
    public const CMD_LP = "learningProgress";
    public const CMD_EXPORT = "export";
    public const CMD_IMPORT = "importFile";
    public const CMD_CREATE = "create";
    public const CMD_SAVE = "save";
    public const CMD_CANCEL = "cancel";
    public const CMD_UNPARTICIPATE = "unparticipate";
    public const CMD_ADD_TO_DESK = "addToDesk";
    public const CMD_REMOVE_FROM_DESK = "removeFromDesk";
    public const CMD_LINK = "link";
    public const CMD_CANCEL_LINK = "cancelMoveLink";
    public const CMD_CUT = "cut";
    public const CMD_CANCEL_CUT = "cancelCut";
    public const CMD_CUT_SHOWTREE = "showPasteTree";
    public const CMD_CUT_CLIPBOARD = "keepObjectsInClipboard";
    public const CMD_DELETE = "delete";
    public const CMD_CANCEL_DELETE = "cancelDelete";
    public const CMD_DELETE_CONFIRMED = "confirmedDelete";
    public const CMD_PERFORM_PASTE = 'performPasteIntoMultipleObjects';
    public const CMD_SHOW_TRASH = 'trash';
    public const CMD_UNDELETE = 'undelete';
    public const CMD_REDRAW_HEADER = 'redrawHeaderAction';

    public const TAB_VIEW_CONTENT = "view";
    public const TAB_MANAGE = "manage";
    public const TAB_CONTENT_MAIN = "manage_content_maintab";
    public const TAB_INFO = "show_summary";
    public const TAB_SETTINGS = "settings";
    public const TAB_PERMISSIONS = "perm_settings";
    public const TAB_MEMBERS = "members";
    public const TAB_LP = "learning_progress";
    public const TAB_EXPORT = "export";

    public const TAB_EDIT_INTRO = "edit_intropage";
    public const TAB_EDIT_EXTRO = "edit_extropage";


    public const MAIL_ALLOWED_ALL = 1;
    public const MAIL_ALLOWED_TUTORS = 2;

    public const ACCESS_READ = 'read';
    public const ACCESS_VISIBLE = 'visible';

    protected string $obj_type;
    protected ilNavigationHistory $navigation_history;
    protected ilObjectService $obj_service;
    protected ilObjLearningSequence $ls_object;
    protected ilRbacReview $rbac_review;
    protected ilHelpGUI $help;
    protected ILIAS\UI\Factory $ui_factory;
    protected ILIAS\UI\Renderer $ui_renderer;
    protected Data\Factory $data_factory;
    protected ILIAS\HTTP\Wrapper\RequestWrapper $request_wrapper;
    protected ArrayBasedRequestWrapper $post_wrapper;
    protected ILIAS\Refinery\Factory $refinery;

    public static function _goto(string $target): void
    {
        global $DIC;
        $main_tpl = $DIC->ui()->mainTemplate();

        $request = $DIC->http()->request();
        $lng = $DIC->language();
        $err = $DIC['ilErr'];

        $targetParameters = explode('_', $target);
        $id = (int) $targetParameters[0];

        if (!self::isAccessible($id)) {
            $err->raiseError($lng->txt('msg_no_perm_read'), $err->FATAL);
        }

        if (self::hasAccess(self::ACCESS_READ, $id)) {
            $params = ['ref_id' => $id];

            if (isset($request->getQueryParams()['gotolp'])) {
                $params['gotolp'] = 1;
            }

            self::forwardByClass(
                ilRepositoryGUI::class,
                [ilObjLearningSequenceGUI::class],
                $params,
                self::CMD_VIEW
            );
        }

        if (self::hasAccess(self::ACCESS_VISIBLE, $id)) {
            ilObjectGUI::_gotoRepositoryNode($id, 'infoScreen');
        }

        if (self::hasAccess(self::ACCESS_READ, ROOT_FOLDER_ID)) {
            $main_tpl->setOnScreenMessage('info', sprintf(
                $lng->txt('msg_no_perm_read_item'),
                ilObject::_lookupTitle(ilObject::_lookupObjId($id))
            ), true);

            self::forwardByClass(ilRepositoryGUI::class, [ilRepositoryGUI::class], ['ref_id' => ROOT_FOLDER_ID]);
        }
    }

    protected static function isAccessible(int $id): bool
    {
        return $id > 0 && (
            self::hasAccess(self::ACCESS_READ, $id) ||
                self::hasAccess(self::ACCESS_VISIBLE, $id) ||
                self::hasAccess(self::ACCESS_READ, ROOT_FOLDER_ID)
        );
    }

    protected static function hasAccess(string $mode, int $id): bool
    {
        global $DIC;
        return $DIC->access()->checkAccess($mode, '', $id);
    }

    protected static function forwardByClass(string $base_class, array $classes, array $params, string $cmd = ''): void
    {
        global $DIC;
        $ctrl = $DIC->ctrl();
        $target_class = end($classes);

        $ctrl->setTargetScript('ilias.php');
        foreach ($params as $key => $value) {
            $ctrl->setParameterByClass($target_class, $key, $value);
        }

        // insert the baseclass to the first position.
        array_splice($classes, 0, 0, $base_class);
        $ctrl->redirectByClass($classes, $cmd);
    }

    public function __construct()
    {
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
        $this->tpl = $DIC["tpl"];
        $this->obj_service = $DIC->object();
        $this->toolbar = $DIC['ilToolbar'];
        $this->request_wrapper = $DIC->http()->wrapper()->query();
        $this->post_wrapper = $DIC->http()->wrapper()->post();
        $this->refinery = $DIC->refinery();

        $this->help->setScreenIdComponent($this->obj_type);
        $this->lng->loadLanguageModule($this->obj_type);

        $this->data_factory = new Data\Factory();

        $this->ref_id = $this->request_wrapper->retrieve("ref_id", $this->refinery->kindlyTo()->int());
        parent::__construct([], $this->ref_id, true, false);
    }

    protected function recordLearningSequenceRead(): void
    {
        ilChangeEvent::_recordReadEvent(
            $this->object->getType(),
            $this->object->getRefId(),
            $this->object->getId(),
            $this->user->getId()
        );
    }

    public function executeCommand(): void
    {
        $next_class = $this->ctrl->getNextClass($this);
        $cmd = $this->ctrl->getCmd();
        $tpl = $this->tpl;

        parent::prepareOutput();
        $this->addToNavigationHistory();
        //showRepTree is from containerGUI;
        //LSO will attach allowed subitems to whitelist
        //see: $this::getAdditionalWhitelistTypes

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
                $struct = ['ilrepositorygui', 'ilobjindividualassessmentgui'];
                if ($cmd === 'edit') {
                    $struct[] = 'ilindividualassessmentsettingsgui';
                }
                $this->ctrl->redirectByClass($struct, $cmd);
                break;
            case 'ilobjtestgui':
                $struct = ['ilrepositorygui', 'ilobjtestgui'];
                $this->ctrl->redirectByClass($struct, $cmd);
                break;
            case 'ilobjlearningsequencelppollinggui':
                $gui = $this->object->getLocalDI()["gui.learner.lp"];
                $this->ctrl->setCmd($cmd);
                $this->ctrl->forwardCommand($gui);
                break;


            case "ilobjlearningsequenceeditintrogui":
                $which_page = $this->object::CP_INTRO;
                $which_tab = self::TAB_EDIT_INTRO;
                $gui_class = 'ilObjLearningSequenceEditIntroGUI';
                // no break
            case "ilobjlearningsequenceeditextrogui":

                if (!isset($which_page)) {
                    $which_page = $this->object::CP_EXTRO;
                    $which_tab = self::TAB_EDIT_EXTRO;

                    $gui_class = 'ilObjLearningSequenceEditExtroGUI';
                }

                $this->addSubTabsForContent($which_tab);

                $page_id = $this->object->getContentPageId($which_page);
                if (!$this->object->hasContentPage($which_page)) {
                    $this->object->createContentPage($which_page);
                }

                $gui = new $gui_class(
                    $this->object::CP_TYPE,
                    $page_id
                );
                $this->ctrl->setCmd($cmd);
                $out = $this->ctrl->forwardCommand($gui);

                //editor's guis will write to template, but not return
                //e.g. see ilPCTabsGUI::insert
                if (!is_null($out)) {
                    $tpl->setContent($out);
                }
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

                    case self::CMD_REDRAW_HEADER:
                        $this->redrawHeaderActionObject();
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

        // This is the base class for the call, so we ought to print.
        // TODO: This is super fishy and most probably hints on the fact, that
        // something regarding that base class usage needs to change.
        if (strtolower($this->request_wrapper->retrieve("baseClass", $this->refinery->kindlyTo()->string())) === strtolower(self::class)) {
            $tpl->printToStdOut();
        }
    }

    public function addToNavigationHistory(): void
    {
        if (
            !$this->getCreationMode() &&
            $this->access->checkAccess('read', '', $this->ref_id)
        ) {
            $link = ilLink::_getLink($this->ref_id, $this->obj_type);
            $this->navigation_history->addItem($this->ref_id, $link, $this->obj_type);
        }
    }

    protected function info(string $cmd = self::CMD_INFO): void
    {
        $this->tabs->setTabActive(self::TAB_INFO);
        $this->ctrl->setCmdClass('ilinfoscreengui');
        $this->ctrl->setCmd($cmd);
        $info = new ilInfoScreenGUI($this);
        $this->ctrl->forwardCommand($info);
    }

    protected function permissions(string $cmd = self::CMD_PERMISSIONS): void
    {
        $this->tabs->setTabActive(self::TAB_PERMISSIONS);
        $perm_gui = new ilPermissionGUI($this);
        $this->ctrl->setCmd($cmd);
        $this->ctrl->forwardCommand($perm_gui);
    }

    protected function settings(string $cmd = self::CMD_SETTINGS): void
    {
        $this->tabs->activateTab(self::TAB_SETTINGS);
        $gui = new ilObjLearningSequenceSettingsGUI(
            $this->getObject(),
            $this->ctrl,
            $this->lng,
            $this->tpl,
            $this->obj_service,
            $this->post_wrapper,
            $this->refinery,
            $this->toolbar
        );
        $this->ctrl->setCmd($cmd);
        $this->ctrl->forwardCommand($gui);
    }

    protected function view(): void
    {
        $this->recordLearningSequenceRead();
        $this->tabs->clearSubTabs();
        if (
            $this->checkAccess("read") ||
            $this->checkAccess("write")
        ) {
            $this->learnerView(self::CMD_LEARNER_VIEW);
            return;
        } else {
            $this->info(self::CMD_INFO);
        }
    }

    protected function manageContent(string $cmd = self::CMD_CONTENT): void
    {
        $this->tabs->activateTab(self::TAB_CONTENT_MAIN);
        $this->addSubTabsForContent(self::TAB_MANAGE);

        $gui = new ilObjLearningSequenceContentGUI(
            $this,
            $this->ctrl,
            $this->tpl,
            $this->lng,
            $this->access,
            new ilConfirmationGUI(),
            new LSItemOnlineStatus(),
            $this->post_wrapper,
            $this->refinery
        );
        $this->ctrl->setCmd($cmd);
        $this->ctrl->forwardCommand($gui);
    }

    protected function learnerView(string $cmd = self::CMD_LEARNER_VIEW): void
    {
        $this->tabs->activateTab(self::TAB_CONTENT_MAIN);
        $this->addSubTabsForContent(self::TAB_VIEW_CONTENT);

        $gui = $this->object->getLocalDI()["gui.learner"];

        $this->ctrl->setCmd($cmd);
        $this->ctrl->forwardCommand($gui);
    }

    protected function members(): void
    {
        $may_manage_members = $this->checkAccess("edit_members");
        $this->ctrl->setCmdClass('ilLearningSequenceMembershipGUI');
        if ($may_manage_members) {
            $this->manage_members();
        } else {
            $this->manage_members(self::CMD_MEMBERS_GALLERY);
        }
    }

    protected function manage_members(string $cmd = self::CMD_MANAGE_MEMBERS): void
    {
        $this->tabs->setTabActive(self::TAB_MEMBERS);

        $ms_gui = new ilLearningSequenceMembershipGUI(
            $this,
            $this->getObject(),
            $this->getTrackingObject(),
            ilPrivacySettings::getInstance(),
            $this->rbac_review,
            $this->settings,
            $this->toolbar,
            $this->request_wrapper,
            $this->post_wrapper,
            $this->refinery
        );

        $this->ctrl->setCmd($cmd);
        $this->ctrl->forwardCommand($ms_gui);
    }

    protected function learningProgress(string $cmd = self::CMD_LP): void
    {
        $this->tabs->setTabActive(self::TAB_LP);

        $for_user = $this->user->getId();

        if ($this->request_wrapper->has("user_id")) {
            $for_user = $this->request_wrapper->retrieve("user_id", $this->refinery->kindlyTo()->int());
        }

        $lp_gui = new ilLearningProgressGUI(
            ilLearningProgressBaseGUI::LP_CONTEXT_REPOSITORY,
            $this->getObject()->getRefId(),
            $for_user
        );

        if ($cmd === self::CMD_LP) {
            $cmd = '';
        }

        $this->ctrl->setCmd($cmd);
        $this->ctrl->forwardCommand($lp_gui);
    }

    protected function export(): void
    {
        $this->tabs->setTabActive(self::TAB_EXPORT);
        $gui = new ilExportGUI($this);
        $gui->addFormat("xml");

        $this->ctrl->forwardCommand($gui);
    }

    protected function initDidacticTemplate(ilPropertyFormGUI $form): ilPropertyFormGUI
    {
        return $form;
    }

    protected function create(): void
    {
        parent::createObject();
    }

    protected function save(): void
    {
        parent::saveObject();
    }

    protected function afterSave(ilObject $new_object): void
    {
        $participant = new ilLearningSequenceParticipants(
            $new_object->getId(),
            $this->log,
            $this->app_event_handler,
            $this->settings
        );

        $participant->add($this->user->getId(), ilParticipants::IL_LSO_ADMIN);
        $participant->updateNotification(
            $this->user->getId(),
            (bool) $this->settings->get('mail_lso_admin_notification', "1")
        );

        $settings = new ilContainerSortingSettings($new_object->getId());
        $settings->setSortMode(ilContainer::SORT_MANUAL);
        $settings->setSortDirection(ilContainer::SORT_DIRECTION_ASC);
        $settings->setSortNewItemsOrder(ilContainer::SORT_NEW_ITEMS_ORDER_CREATION);
        $settings->setSortNewItemsPosition(ilContainer::SORT_NEW_ITEMS_POSITION_BOTTOM);
        $settings->save();

        $this->tpl->setOnScreenMessage('success', $this->lng->txt('object_added'), true);
        $this->ctrl->setParameter($this, "ref_id", $new_object->getRefId());
        ilUtil::redirect(
            $this->getReturnLocation(
                "save",
                $this->ctrl->getLinkTarget($this, self::CMD_SETTINGS, "", false, false)
            )
        );
    }

    public function unparticipate(): void
    {
        if ($this->checkAccess('unparticipate')) {
            $usr_id = $this->user->getId();
            $this->getObject()->getLSRoles()->leave($usr_id);
            $this->learnerView();
        }
    }

    protected function getTabs(): void
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
                    $this->getObject()->getLSRoles()->isMember($this->user->getId())
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

    public function renderObject(): void
    {
        // disables this method in ilContainerGUI
    }


    protected function addSubTabsForContent(string $active): void
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

            $this->tabs->addSubTab(
                self::TAB_EDIT_INTRO,
                $this->lng->txt("lso_settings_intro"),
                $this->ctrl->getLinkTargetByClass(
                    strtolower('ilObjLearningSequenceEditIntroGUI'),
                    'preview'
                )
            );
            $this->tabs->addSubTab(
                self::TAB_EDIT_EXTRO,
                $this->lng->txt("lso_settings_extro"),
                $this->ctrl->getLinkTargetByClass(
                    strtolower('ilObjLearningSequenceEditExtroGUI'),
                    'preview'
                )
            );
        }
        $this->tabs->activateSubTab($active);
    }

    protected function checkAccess(string $which): bool
    {
        return $this->access->checkAccess($which, "", $this->ref_id);
    }

    protected function checkLPAccess(): bool
    {
        if (ilObject::_lookupType($this->ref_id, true) !== "lso") {
            return false;
        }

        $ref_id = $this->getObject()->getRefId();
        $is_participant = ilLearningSequenceParticipants::_isParticipant($ref_id, $this->user->getId());

        $lp_access = ilLearningProgressAccess::checkAccess($ref_id, $is_participant);
        $may_edit_lp_settings = $this->checkAccess('edit_learning_progress');

        return ($lp_access || $may_edit_lp_settings);
    }

    protected function getLinkTarget(string $cmd): string
    {
        $class = $this->getClassForTabs($cmd);
        $class_path = [
            strtolower('ilObjLearningSequenceGUI'),
            $class
        ];
        return $this->ctrl->getLinkTargetByClass($class_path, $cmd);
    }

    protected function getClassForTabs(string $cmd): string
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

    public function createMailSignature(): string
    {
        $link = chr(13) . chr(10) . chr(13) . chr(10);
        $link .= $this->lng->txt('lso_mail_permanent_link');
        $link .= chr(13) . chr(10) . chr(13) . chr(10);
        $link .= ilLink::_getLink($this->object->getRefId());

        return rawurlencode(base64_encode($link));
    }

    public function getObject(): ilObjLearningSequence
    {
        if ($this->object === null) {
            /** @var ilObjLearningSequence $obj */
            $obj = ilObjLearningSequence::getInstanceByRefId($this->ref_id);
            $this->object = $obj;
        }

        return $this->object;
    }

    protected function getTrackingObject(): ilObjUserTracking
    {
        return new ilObjUserTracking();
    }

    /**
     * @return array [role_id => title]
     */
    public function getLocalRoles(): array
    {
        $local_roles = $this->object->getLocalLearningSequenceRoles();
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
     * @return int[]|string[]
     */
    protected function getAdditionalWhitelistTypes(): array
    {
        return array_filter(
            array_keys($this->obj_definition->getSubObjects('lso', false)),
            fn ($type) => $type !== 'rolf'
        );
    }

    /**
     * @return array<int|string, mixed>
     */
    public function addCustomData(array $a_data): array
    {
        $res_data = array();
        foreach ($a_data as $usr_id => $user_data) {
            $res_data[$usr_id] = $user_data;
            $udf_data = new ilUserDefinedData($usr_id);

            foreach ($udf_data->getAll() as $field => $value) {
                list(, $field_id) = explode('_', $field);
                $res_data[$usr_id]['udf_' . $field_id] = (string) $value;
            }
        }

        return $res_data;
    }

    public function showPossibleSubObjects(): void
    {
        parent::showPossibleSubObjects();
    }
}
