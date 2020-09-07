<?php
/* Copyright (c) 2017 Denis Klöpfer <denis.kloepfer@concepts-and-training.de>  Extended GPL, see ./LICENSE */
/* Copyright (c) 2018 Stefan Hecken <stefan.hecken@concepts-and-training.de> Extended GPL, see ./LICENSE */

declare(strict_types=1);

require_once 'Modules/IndividualAssessment/classes/class.ilIndividualAssessmentMembersTableGUI.php';
require_once 'Modules/IndividualAssessment/classes/LearningProgress/class.ilIndividualAssessmentLPInterface.php';

use \ILIAS\UI\Component\ViewControl;

/**
 * For the purpose of streamlining the grading and learning-process status definition
 * outside of tests, SCORM courses e.t.c. the IndividualAssessment is used.
 * It caries a LPStatus, which is set Individually.
 *
 * @ilCtrl_Calls ilIndividualAssessmentMembersGUI: ilRepositorySearchGUI
 * @ilCtrl_Calls ilIndividualAssessmentMembersGUI: ilIndividualAssessmentMemberGUI
 */
class ilIndividualAssessmentMembersGUI
{
    protected $ctrl;
    protected $parent_gui;
    protected $ref_id;
    protected $tpl;
    protected $lng;

    const F_STATUS = "status";
    const F_SORT = "sortation";

    const S_NAME_ASC = "user_lastname:asc";
    const S_NAME_DESC = "user_lastname:desc";
    const S_EXAMINER_ASC = "examiner_login:asc";
    const S_EXAMINER_DESC = "examiner_login:desc";
    const S_CHANGETIME_ASC = "change_time:asc";
    const S_CHANGETIME_DESC = "change_time:desc";

    /**
     * @var ilObjIndividualAssessment
     */
    protected $object;

    /**
     * @var ilIndividualAssessmentAccessHandler
     */
    protected $iass_access;

    public function __construct(
        ilObjIndividualAssessmentGUI $a_parent_gui,
        int $a_ref_id
    ) {
        global $DIC;
        $this->ctrl = $DIC['ilCtrl'];
        $this->parent_gui = $a_parent_gui;
        $this->object = $a_parent_gui->object;
        $this->ref_id = $a_ref_id;
        $this->tpl = $DIC['tpl'];
        $this->lng = $DIC['lng'];
        $this->toolbar = $DIC['ilToolbar'];
        $this->user = $DIC["ilUser"];
        $this->iass_access = $this->object->accessHandler();
        $this->factory = $DIC->ui()->factory();
        $this->renderer = $DIC->ui()->renderer();
    }

    public function executeCommand()
    {
        if (
            !$this->iass_access->mayEditMembers()
            && !$this->iass_access->mayGradeUser()
            && !$this->iass_access->mayViewUser()
            && !$this->iass_access->mayAmendGradeUser()
        ) {
            $this->parent_gui->handleAccessViolation();
        }

        $cmd = $this->ctrl->getCmd();
        $next_class = $this->ctrl->getNextClass();
        $this->ctrl->saveParameterByClass("ilIndividualAssessmentMembersGUI", self::F_STATUS);
        switch ($next_class) {
            case "ilrepositorysearchgui":
                require_once 'Services/Search/classes/class.ilRepositorySearchGUI.php';
                $rep_search = new ilRepositorySearchGUI();
                $rep_search->setCallback($this, "addUsersFromSearch");
                $rep_search->addUserAccessFilterCallable(
                    function ($a_user_ids) {
                        return $a_user_ids;
                    }
                );
                $this->ctrl->forwardCommand($rep_search);
                break;
            case "ilindividualassessmentmembergui":
                require_once 'Modules/IndividualAssessment/classes/class.ilIndividualAssessmentMemberGUI.php';
                $member = new ilIndividualAssessmentMemberGUI($this, $this->parent_gui, $this->ref_id);
                $this->ctrl->forwardCommand($member);
                break;
            default:
                if (!$cmd) {
                    $cmd = 'view';
                }
                $this->$cmd();
                break;
        }
    }

    protected function addedUsers()
    {
        if (!$_GET['failure']) {
            ilUtil::sendSuccess($this->txt('iass_add_user_success'));
        } else {
            ilUtil::sendFailure($this->txt('iass_add_user_failure'));
        }
        $this->view();
    }

    protected function view()
    {
        if ($this->iass_access->mayEditMembers()) {
            require_once './Services/Search/classes/class.ilRepositorySearchGUI.php';

            $search_params = ['crs', 'grp'];
            $container_id = $this->object->getParentContainerIdByType($this->ref_id, $search_params);
            if ($container_id !== 0) {
                ilRepositorySearchGUI::fillAutoCompleteToolbar(
                    $this,
                    $this->toolbar,
                    array(
                        'auto_complete_name' => $this->txt('user'),
                        'submit_name' => $this->txt('add'),
                        'add_search' => true,
                        'add_from_container' => $container_id
                    )
                );
            } else {
                ilRepositorySearchGUI::fillAutoCompleteToolbar(
                    $this,
                    $this->toolbar,
                    array(
                        'auto_complete_name' => $this->txt('user'),
                        'submit_name' => $this->txt('add'),
                        'add_search' => true
                    )
                );
            }
        }

        $table = new ilIndividualAssessmentMembersTableGUI(
            $this,
            $this->lng,
            $this->ctrl,
            $this->object->accessHandler(),
            $this->factory,
            $this->renderer,
            (int) $this->user->getId()
        );

        $get = $_GET;

        $filter = $this->getFilterValue($get);
        $sort = $this->getSortValue($get);
        $entries = $this->filterViewableOrGradeableEntries(
            $this->object->loadMembersAsSingleObjects($filter, $sort)
        );

        $table->setData($entries);
        $view_constrols = $this->getViewControls($get);

        $output = $table->render($view_constrols);

        if (count($entries) == 0) {
            $output .= $this->txt("iass_no_entries");
        }
        $this->tpl->setContent($output);
    }

    protected function filterViewableOrGradeableEntries(array $entries) : array
    {
        $user_ids = array_map(function ($e) {
            return $e->id();
        }, $entries);
        $viewable_or_gradeable_entries = $this->iass_access->filterViewableOrGradeableUsers($user_ids);

        return array_filter($entries, function ($e) use ($viewable_or_gradeable_entries) {
            return in_array($e->id(), $viewable_or_gradeable_entries);
        });
    }

    /**
     * @param int[]
     */
    public function addUsersFromSearch(array $user_ids)
    {
        if ($user_ids && is_array($user_ids) && !empty($user_ids)) {
            $this->addUsers($user_ids);
        }

        ilUtil::sendInfo($this->txt("search_no_selection"), true);
        $this->ctrl->redirectByClass(array(get_class($this->parent_gui),get_class($this)), 'view');
    }

    /**
     * Add users to corresponding iass-object. To be used by repository search.
     *
     * @param	int|string[]	$user_ids
     */
    public function addUsers(array $user_ids)
    {
        if (!$this->iass_access->mayEditMembers()) {
            $this->parent_gui->handleAccessViolation();
        }
        $iass = $this->object;
        $members = $iass->loadMembers();
        $failure = null;
        if (count($user_ids) === 0) {
            $failure = 1;
        }
        foreach ($user_ids as $user_id) {
            $user = new ilObjUser($user_id);
            if (!$members->userAllreadyMember($user)) {
                $members = $members->withAdditionalUser($user);
            } else {
                $failure = 1;
            }
        }
        $members->updateStorageAndRBAC($iass->membersStorage(), $iass->accessHandler());
        ilIndividualAssessmentLPInterface::updateLPStatusByIds($iass->getId(), $user_ids);
        $this->ctrl->setParameter($this, 'failure', $failure);
        $this->ctrl->redirectByClass(array(get_class($this->parent_gui),get_class($this)), 'addedUsers');
    }

    /**
     * Display confirmation form for user might be removed
     */
    protected function removeUserConfirmation()
    {
        if (!$this->iass_access->mayEditMembers()) {
            $this->parent_gui->handleAccessViolation();
        }
        include_once './Services/Utilities/classes/class.ilConfirmationGUI.php';
        $confirm = new ilConfirmationGUI();
        $confirm->addItem('usr_id', $_GET['usr_id'], ilObjUser::_lookupFullname($_GET['usr_id']));
        $confirm->setHeaderText($this->txt('iass_remove_user_qst'));
        $confirm->setFormAction($this->ctrl->getFormAction($this));
        $confirm->setConfirm($this->txt('remove'), 'removeUser');
        $confirm->setCancel($this->txt('cancel'), 'view');
        $this->tpl->setContent($confirm->getHTML());
    }

    /**
     * Remove users from corresponding iass-object. To be used by repository search.
     */
    public function removeUser()
    {
        if (!$this->iass_access->mayEditMembers()) {
            $this->parent_gui->handleAccessViolation();
        }
        $usr_id = $_POST['usr_id'];
        $iass = $this->object;
        $iass->loadMembers()
            ->withoutPresentUser(new ilObjUser($usr_id))
            ->updateStorageAndRBAC($iass->membersStorage(), $iass->accessHandler());
        ilIndividualAssessmentLPInterface::updateLPStatusByIds($iass->getId(), array($usr_id));
        ilUtil::sendSuccess($this->txt("iass_user_removed"), true);
        $this->ctrl->redirectByClass(array(get_class($this->parent_gui),get_class($this)), 'view');
    }

    /**
     * @return ViewControl[]
     */
    protected function getViewControls(array $get) : array
    {
        $ret = array();

        $vc_factory = $this->factory->viewControl();

        $sort = $this->getSortationControl($vc_factory);
        $ret[] = $this->getModeControl($get, $vc_factory);
        $ret[] = $sort;

        return $ret;
    }

    /**
     * @param string[] 	$get
     */
    protected function getModeControl(array $get, ViewControl\Factory $vc_factory) : ViewControl\Mode
    {
        $active = $this->getActiveLabelForModeByFilter($get[self::F_STATUS]);

        return $vc_factory->mode(
            $this->getModeOptions(),
            ""
        )
        ->withActive($active);
    }

    protected function getSortationControl(ViewControl\Factory $vc_factory) : ViewControl\Sortation
    {
        $target = $link = $this->ctrl->getLinkTargetByClass("ilIndividualAssessmentMembersGUI", "view");
        return $vc_factory->sortation(
            $this->getSortOptions()
        )
        ->withTargetURL($target, self::F_SORT)
        ->withLabel($this->txt("iass_sort"));
    }

    /**
     * @return string[]
     */
    protected function getModeOptions() : array
    {
        $ret = [];

        $ret[$this->txt("iass_filter_all")] = $this->getLinkForStatusFilter(null);
        $ret[$this->txt("iass_filter_not_started")] = $this->getLinkForStatusFilter(
            ilIndividualAssessmentMembers::LP_ASSESSMENT_NOT_COMPLETED
        );
        $ret[$this->txt("iass_filter_not_finalized")] = $this->getLinkForStatusFilter(
            ilIndividualAssessmentMembers::LP_IN_PROGRESS
        );
        $ret[$this->txt("iass_filter_finalized")] = $this->getLinkForStatusFilter(
            ilIndividualAssessmentMembers::LP_COMPLETED
        );
        $ret[$this->txt("iass_filter_failed")] = $this->getLinkForStatusFilter(
            ilIndividualAssessmentMembers::LP_FAILED
        );

        return $ret;
    }

    /**
     * @param int|string|null 	$filter
     */
    protected function getActiveLabelForModeByFilter($filter) : string
    {
        switch ($filter) {
            case ilIndividualAssessmentMembers::LP_ASSESSMENT_NOT_COMPLETED:
                return $this->txt("iass_filter_not_started");
                break;
            case ilIndividualAssessmentMembers::LP_IN_PROGRESS:
                return $this->txt("iass_filter_not_finalized");
                break;
            case ilIndividualAssessmentMembers::LP_COMPLETED:
                return $this->txt("iass_filter_finalized");
                break;
            case ilIndividualAssessmentMembers::LP_FAILED:
                return $this->txt("iass_filter_failed");
                break;
            default:
                return $this->txt("iass_filter_all");
        }
    }

    /**
     * @param int|string|null 	$value
     */
    protected function getLinkForStatusFilter($value) : string
    {
        $this->ctrl->setParameterByClass("ilIndividualAssessmentMembersGUI", self::F_STATUS, $value);
        $link = $this->ctrl->getLinkTargetByClass("ilIndividualAssessmentMembersGUI", "view");
        $this->ctrl->setParameterByClass("ilIndividualAssessmentMembersGUI", self::F_STATUS, null);

        return $link;
    }

    /**
     * @param string[] 	$get
     * @return int|string|null
     */
    protected function getFilterValue(array $get)
    {
        if (isset($get[self::F_STATUS])
            && $get[self::F_STATUS] != ""
            && in_array(
                $get[self::F_STATUS],
                [
                        ilIndividualAssessmentMembers::LP_ASSESSMENT_NOT_COMPLETED,
                        ilIndividualAssessmentMembers::LP_IN_PROGRESS,
                        ilIndividualAssessmentMembers::LP_COMPLETED,
                        ilIndividualAssessmentMembers::LP_FAILED
                    ]
            )
        ) {
            return $get[self::F_STATUS];
        }

        return null;
    }

    protected function getSortOptions() : array
    {
        return array(
            self::S_NAME_ASC => $this->txt("iass_sort_name_asc"),
            self::S_NAME_DESC => $this->txt("iass_sort_name_desc"),
            self::S_EXAMINER_ASC => $this->txt("iass_sort_examiner_login_asc"),
            self::S_EXAMINER_DESC => $this->txt("iass_sort_examiner_login_desc"),
            self::S_CHANGETIME_ASC => $this->txt("iass_sort_changetime_asc"),
            self::S_CHANGETIME_DESC => $this->txt("iass_sort_changetime_desc")
        );
    }

    /**
     * @param string[]
     * @return string|null
     */
    protected function getSortValue(array $get)
    {
        if (isset($get[self::F_SORT])
            && $get[self::F_SORT] != ""
            && in_array(
                $get[self::F_SORT],
                [
                        self::S_NAME_ASC,
                        self::S_NAME_DESC,
                        self::S_EXAMINER_ASC,
                        self::S_EXAMINER_DESC,
                        self::S_CHANGETIME_ASC,
                        self::S_CHANGETIME_DESC
                    ]
            )
        ) {
            return $get[self::F_SORT];
        }

        return null;
    }

    protected function txt(string $code) : string
    {
        return $this->lng->txt($code);
    }
}
