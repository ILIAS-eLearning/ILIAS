<?php declare(strict_types=1);

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

use ILIAS\UI;
use ILIAS\UI\Component\ViewControl;

/**
 * For the purpose of streamlining the grading and learning-process status definition
 * outside of tests, SCORM courses e.t.c. the IndividualAssessment is used.
 * It carries a LPStatus, which is set Individually.
 *
 * @ilCtrl_Calls ilIndividualAssessmentMembersGUI: ilRepositorySearchGUI
 * @ilCtrl_Calls ilIndividualAssessmentMembersGUI: ilIndividualAssessmentMemberGUI
 */
class ilIndividualAssessmentMembersGUI
{
    const F_STATUS = "status";
    const F_SORT = "sortation";

    const S_NAME_ASC = "user_lastname:asc";
    const S_NAME_DESC = "user_lastname:desc";
    const S_EXAMINER_ASC = "examiner_login:asc";
    const S_EXAMINER_DESC = "examiner_login:desc";
    const S_CHANGETIME_ASC = "change_time:asc";
    const S_CHANGETIME_DESC = "change_time:desc";

    protected ilCtrl $ctrl;
    protected ilObjIndividualAssessment $object;
    protected int $ref_id;
    protected ilGlobalPageTemplate $tpl;
    protected ilLanguage $lng;
    protected ilToolbarGUI $toolbar;
    protected ilObjUser $user;
    protected ilTabsGUI $tabs;
    protected IndividualAssessmentAccessHandler $iass_access;
    protected UI\Factory $factory;
    protected UI\Renderer $renderer;
    protected ilErrorHandling $error_object;
    protected ilIndividualAssessmentMemberGUI $member_gui;
    protected ILIAS\Refinery\Factory $refinery;
    protected ILIAS\HTTP\Wrapper\RequestWrapper $request_wrapper;
    protected ILIAS\HTTP\Wrapper\ArrayBasedRequestWrapper $post_wrapper;

    public function __construct(
        ilObjIndividualAssessment $object,
        ilCtrl $ctrl,
        ilGlobalPageTemplate $tpl,
        ilLanguage $lng,
        ilToolbarGUI $toolbar,
        ilObjUser $user,
        ilTabsGUI $tabs,
        IndividualAssessmentAccessHandler $iass_access,
        UI\Factory $factory,
        UI\Renderer $renderer,
        ilErrorHandling $error_object,
        ilIndividualAssessmentMemberGUI $member_gui,
        ILIAS\Refinery\Factory $refinery,
        ILIAS\HTTP\Wrapper\WrapperFactory $wrapper
    ) {
        $this->object = $object;
        $this->ctrl = $ctrl;
        $this->tpl = $tpl;
        $this->lng = $lng;
        $this->toolbar = $toolbar;
        $this->user = $user;
        $this->tabs = $tabs;
        $this->iass_access = $iass_access;
        $this->factory = $factory;
        $this->renderer = $renderer;
        $this->error_object = $error_object;
        $this->member_gui = $member_gui;
        $this->refinery = $refinery;
        $this->request_wrapper = $wrapper->query();
        $this->post_wrapper = $wrapper->post();

        $this->ref_id = $object->getRefId();
    }

    public function executeCommand() : void
    {
        if (!$this->iass_access->mayEditMembers()
            && !$this->iass_access->mayGradeUser()
            && !$this->iass_access->mayViewUser()
            && !$this->iass_access->mayAmendGradeUser()
        ) {
            $this->handleAccessViolation();
        }
        $cmd = $this->ctrl->getCmd();
        $next_class = $this->ctrl->getNextClass();
        $this->ctrl->saveParameterByClass("ilIndividualAssessmentMembersGUI", self::F_STATUS);
        switch ($next_class) {
            case "ilrepositorysearchgui":
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
                $this->tabs->clearTargets();
                $this->tabs->setBackTarget(
                    $this->lng->txt('back'),
                    $this->ctrl->getLinkTargetByClass(self::class, 'view')
                );
                $this->ctrl->forwardCommand($this->member_gui);
                break;
            default:
                if (!$cmd) {
                    $cmd = 'view';
                }
                $this->$cmd();
                break;
        }
    }

    protected function addedUsers() : void
    {
        if ($this->request_wrapper->retrieve('failure', $this->refinery->kindlyTo()->bool())) {
            $this->tpl->setOnScreenMessage("failure", $this->txt('iass_add_user_failure'));
        } else {
            $this->tpl->setOnScreenMessage("success", $this->txt('iass_add_user_success'));
        }
        $this->view();
    }

    protected function view() : void
    {
        if ($this->iass_access->mayEditMembers()) {
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
            $this->iass_access,
            $this->factory,
            $this->renderer,
            $this->user->getId()
        );

        $filter = $this->getFilterValue();
        $sort = $this->getSortValue();

        $entries = $this->object->loadMembersAsSingleObjects($filter, $sort);
        $table->setData($entries);
        $view_controls = $this->getViewControls();

        $output = $table->render($view_controls);

        if (count($entries) == 0) {
            $output .= $this->txt("iass_no_entries");
        }
        $this->tpl->setContent($output);
    }

    /**
     * @param int[]
     */
    public function addUsersFromSearch(array $user_ids) : void
    {
        if (!empty($user_ids)) {
            $this->addUsers($user_ids);
        }

        $this->tpl->setOnScreenMessage("info", $this->txt("search_no_selection"), true);
        $this->ctrl->redirect($this, 'view');
    }

    /**
     * Add users to corresponding iass-object. To be used by repository search.
     *
     * @param	int|string[]	$user_ids
     */
    public function addUsers(array $user_ids) : void
    {
        if (!$this->iass_access->mayEditMembers()) {
            $this->handleAccessViolation();
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
        $this->ctrl->redirect($this, 'addedUsers');
    }

    /**
     * Display confirmation form for user might be removed
     */
    protected function removeUserConfirmation() : void
    {
        if (!$this->iass_access->mayEditMembers()) {
            $this->handleAccessViolation();
        }
        $usr_id = $this->request_wrapper->retrieve("usr_id", $this->refinery->kindlyTo()->int());
        $confirm = new ilConfirmationGUI();
        $confirm->addItem('usr_id', (string) $usr_id, ilObjUser::_lookupFullname($usr_id));
        $confirm->setHeaderText($this->txt('iass_remove_user_qst'));
        $confirm->setFormAction($this->ctrl->getFormAction($this));
        $confirm->setConfirm($this->txt('remove'), 'removeUser');
        $confirm->setCancel($this->txt('cancel'), 'view');
        $this->tpl->setContent($confirm->getHTML());
    }

    /**
     * Remove users from corresponding iass-object. To be used by repository search.
     */
    public function removeUser() : void
    {
        if (!$this->iass_access->mayEditMembers()) {
            $this->handleAccessViolation();
        }
        $usr_id = $this->post_wrapper->retrieve("usr_id", $this->refinery->kindlyTo()->int());
        $iass = $this->object;
        $iass->loadMembers()
            ->withoutPresentUser(new ilObjUser($usr_id))
            ->updateStorageAndRBAC($iass->membersStorage(), $iass->accessHandler());
        ilIndividualAssessmentLPInterface::updateLPStatusByIds($iass->getId(), array($usr_id));
        $this->tpl->setOnScreenMessage("success", $this->txt("iass_user_removed"), true);
        $this->ctrl->redirect($this, 'view');
    }

    /**
     * @return ILIAS\UI\Component\Component[]
     */
    protected function getViewControls() : array
    {
        $ret = array();

        $vc_factory = $this->factory->viewControl();

        $sort = $this->getSortationControl($vc_factory);
        $ret[] = $this->getModeControl($vc_factory);
        $ret[] = $sort;

        return $ret;
    }

    protected function getModeControl(ViewControl\Factory $vc_factory) : ViewControl\Mode
    {
        $vc = $vc_factory->mode(
            $this->getModeOptions(),
            ""
        );

        if ($this->request_wrapper->has(self::F_STATUS)) {
            $vc = $vc->withActive(
                $this->request_wrapper->retrieve(
                    self::F_STATUS,
                    $this->refinery->kindlyTo()->string()
                )
            );
        }

        return $vc;
    }

    protected function getSortationControl(ViewControl\Factory $vc_factory) : ViewControl\Sortation
    {
        $target = $this->ctrl->getLinkTargetByClass("ilIndividualAssessmentMembersGUI", "view");
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

        if ($this->maybeViewLearningProgress()) {
            $ret[$this->txt("iass_filter_not_started")] =
                $this->getLinkForStatusFilter(ilIndividualAssessmentMembers::LP_ASSESSMENT_NOT_COMPLETED);
            $ret[$this->txt("iass_filter_not_finalized")] =
                $this->getLinkForStatusFilter(ilIndividualAssessmentMembers::LP_IN_PROGRESS);
            $ret[$this->txt("iass_filter_finalized")] =
                $this->getLinkForStatusFilter(ilIndividualAssessmentMembers::LP_COMPLETED);
            $ret[$this->txt("iass_filter_failed")] =
                $this->getLinkForStatusFilter(ilIndividualAssessmentMembers::LP_FAILED);
        }
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
            case ilIndividualAssessmentMembers::LP_IN_PROGRESS:
                return $this->txt("iass_filter_not_finalized");
            case ilIndividualAssessmentMembers::LP_COMPLETED:
                return $this->txt("iass_filter_finalized");
            case ilIndividualAssessmentMembers::LP_FAILED:
                return $this->txt("iass_filter_failed");
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

    protected function getFilterValue() : ?string
    {
        if (
            $this->request_wrapper->has(self::F_STATUS) &&
            $this->request_wrapper->retrieve(self::F_STATUS, $this->refinery->kindlyTo()->string()) != "" &&
            in_array(
                $this->request_wrapper->retrieve(self::F_STATUS, $this->refinery->kindlyTo()->string()),
                [
                    ilIndividualAssessmentMembers::LP_ASSESSMENT_NOT_COMPLETED,
                    ilIndividualAssessmentMembers::LP_IN_PROGRESS,
                    ilIndividualAssessmentMembers::LP_COMPLETED,
                    ilIndividualAssessmentMembers::LP_FAILED
                ]
            )
        ) {
            return $this->request_wrapper->retrieve(self::F_STATUS, $this->refinery->kindlyTo()->string());
        }

        return null;
    }

    protected function getSortOptions() : array
    {
        return [
            self::S_NAME_ASC => $this->txt("iass_sort_name_asc"),
            self::S_NAME_DESC => $this->txt("iass_sort_name_desc"),
            self::S_EXAMINER_ASC => $this->txt("iass_sort_examiner_login_asc"),
            self::S_EXAMINER_DESC => $this->txt("iass_sort_examiner_login_desc"),
            self::S_CHANGETIME_ASC => $this->txt("iass_sort_changetime_asc"),
            self::S_CHANGETIME_DESC => $this->txt("iass_sort_changetime_desc")
        ];
    }

    protected function getSortValue() : ?string
    {
        if (
            $this->request_wrapper->has(self::F_SORT) &&
            $this->request_wrapper->retrieve(self::F_SORT, $this->refinery->kindlyTo()->string()) != "" &&
            in_array(
                $this->request_wrapper->retrieve(self::F_SORT, $this->refinery->kindlyTo()->string()),
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
            return $this->request_wrapper->retrieve(self::F_SORT, $this->refinery->kindlyTo()->string());
        }

        return null;
    }

    public function handleAccessViolation() : void
    {
        $this->error_object->raiseError($this->txt("msg_no_perm_read"), $this->error_object->WARNING);
    }

    protected function maybeViewLearningProgress() : bool
    {
        return $this->iass_access->mayViewUser();
    }

    protected function txt(string $code) : string
    {
        return $this->lng->txt($code);
    }
}
