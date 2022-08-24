<?php

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

/**
 * Class ilBookingParticipantGUI
 * @author Jesús López <lopez@leifos.com>
 * @ilCtrl_Calls ilBookingParticipantGUI: ilRepositorySearchGUI
 */
class ilBookingParticipantGUI
{
    public const FILTER_ACTION_APPLY = 1;
    public const FILTER_ACTION_RESET = 2;
    public const PARTICIPANT_VIEW = 1;
    protected \ILIAS\BookingManager\StandardGUIRequest $book_request;

    protected ilGlobalTemplateInterface $tpl;
    protected ilTabsGUI $tabs;
    protected ilCtrl $ctrl;
    protected ilLanguage $lng;
    protected ilAccessHandler $access;
    protected ilToolbarGUI $toolbar;
    protected int $ref_id;
    protected int $pool_id;

    public function __construct(
        ilObjBookingPoolGUI $a_parent_obj
    ) {
        global $DIC;

        $this->tpl = $DIC["tpl"];
        $this->tabs = $DIC->tabs();
        $this->ctrl = $DIC->ctrl();
        $this->lng = $DIC->language();
        $this->access = $DIC->access();
        $this->toolbar = $DIC->toolbar();
        $this->book_request = $DIC->bookingManager()
                                  ->internal()
                                  ->gui()
                                  ->standardRequest();


        $this->ref_id = $a_parent_obj->getRefId();
        $this->pool_id = $a_parent_obj->getObject()->getId();

        $this->lng->loadLanguageModule("book");
    }

    public function executeCommand(): void
    {
        $ilCtrl = $this->ctrl;

        $next_class = $ilCtrl->getNextClass($this);

        switch ($next_class) {
            case 'ilrepositorysearchgui':
                $rep_search = new ilRepositorySearchGUI();
                $ref_id = $this->ref_id;
                $rep_search->addUserAccessFilterCallable(function ($a_user_id) {
                    return $this->access->filterUserIdsByRbacOrPositionOfCurrentUser(
                        'render',
                        'render',
                        $this->ref_id,
                        $a_user_id
                    );
                });
                $rep_search->setTitle($this->lng->txt("exc_add_participant"));
                $rep_search->setCallback($this, 'addParticipantObject');
                $this->ctrl->setReturn($this, 'render');
                $this->ctrl->forwardCommand($rep_search);
                break;

            default:
                $cmd = $ilCtrl->getCmd("render");
                $this->$cmd();
                break;
        }
    }

    /**
     * Render list of booking participants.
     * uses ilBookingParticipantsTableGUI
     */
    public function render(): void
    {
        if ($this->access->checkAccess('write', '', $this->ref_id)) {
            ilRepositorySearchGUI::fillAutoCompleteToolbar(
                $this,
                $this->toolbar,
                array(
                    'auto_complete_name' => $this->lng->txt('user'),
                    'submit_name' => $this->lng->txt('add'),
                    'add_search' => true,
                    'add_from_container' => $this->ref_id
                )
            );

            $table = new ilBookingParticipantsTableGUI($this, 'render', $this->ref_id, $this->pool_id);

            $this->tpl->setContent($table->getHTML());
        }
    }

    public function addUserFromAutoCompleteObject(): bool
    {
        if (trim($this->book_request->getUserLogin()) === '') {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt('msg_no_search_string'));
            $this->render();
            return false;
        }

        $users = explode(',', $this->book_request->getUserLogin());

        $user_ids = array();
        foreach ($users as $user) {
            $user_id = ilObjUser::_lookupId($user);

            if (!$user_id) {
                $this->tpl->setOnScreenMessage('failure', $this->lng->txt('user_not_known'));
                $this->render();
            }
            $user_ids[] = $user_id;
        }

        return $this->addParticipantObject($user_ids);
    }

    /**
     * Add new participant
     * @param int[] $a_user_ids
     * @throws ilCtrlException
     */
    public function addParticipantObject(
        array $a_user_ids
    ): bool {
        foreach ($a_user_ids as $user_id) {
            if (ilObject::_lookupType($user_id) === "usr") {
                $participant_obj = new ilBookingParticipant($user_id, $this->pool_id);
                if ($participant_obj->getIsNew()) {
                    $this->tpl->setOnScreenMessage('success', $this->lng->txt("book_participant_assigned"), true);
                } else {
                    $this->tpl->setOnScreenMessage('failure', $this->lng->txt("book_participant_already_assigned"));
                    return false;
                }
            } else {
                $this->tpl->setOnScreenMessage('failure', "dummy error message, change me");
                return false;
            }
        }

        $this->ctrl->redirect($this, "render");
        return true;
    }

    public function applyParticipantsFilter(): void
    {
        $this->applyFilterAction(self::FILTER_ACTION_APPLY);
    }

    public function resetParticipantsFilter(): void
    {
        $this->applyFilterAction(self::FILTER_ACTION_RESET);
    }

    protected function applyFilterAction(
        int $a_filter_action
    ): void {
        $table = new ilBookingParticipantsTableGUI($this, 'render', $this->ref_id, $this->pool_id);
        $table->resetOffset();
        if ($a_filter_action === self::FILTER_ACTION_RESET) {
            $table->resetFilter();
        } else {
            $table->writeFilterToSession();
        }

        $this->render();
    }

    public function assignObjects(): void
    {
        $this->tabs->clearTargets();
        $this->tabs->setBackTarget($this->lng->txt('book_back_to_list'), $this->ctrl->getLinkTarget($this, 'render'));

        $table = new ilBookingAssignObjectsTableGUI($this, 'assignObjects', $this->ref_id, $this->pool_id);

        $this->tpl->setContent($table->getHTML());
    }
}
