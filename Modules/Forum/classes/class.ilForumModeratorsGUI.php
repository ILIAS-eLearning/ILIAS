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

declare(strict_types=1);

use ILIAS\Forum\Notification\NotificationType;

/**
 * Class ilForumModeratorsGUI
 * @author       Nadia Matuschek <nmatuschek@databay.de>
 * @ilCtrl_Calls ilForumModeratorsGUI: ilRepositorySearchGUI
 * @ingroup      ModulesForum
 */
class ilForumModeratorsGUI
{
    private ilCtrlInterface $ctrl;
    private ilGlobalTemplateInterface $tpl;
    private ilLanguage $lng;
    private ilTabsGUI $tabs;
    private ilErrorHandling $error;
    private ilObjUser $user;
    private ilToolbarGUI $toolbar;
    private ilForumModerators $oForumModerators;
    private ilAccessHandler $access;
    private \ILIAS\HTTP\Wrapper\WrapperFactory $http_wrapper;
    private \ILIAS\Refinery\Factory $refinery;

    public function __construct(private readonly ilObjForum $forum)
    {
        /** @var $DIC ILIAS\DI\Container */
        global $DIC;

        $this->ctrl = $DIC->ctrl();
        $this->tpl = $DIC->ui()->mainTemplate();
        $this->lng = $DIC->language();
        $this->access = $DIC->access();
        $this->tabs = $DIC->tabs();
        $this->error = $DIC['ilErr'];
        $this->user = $DIC->user();
        $this->toolbar = $DIC->toolbar();

        $this->tabs->activateTab('frm_moderators');
        $this->lng->loadLanguageModule('search');
        $this->http_wrapper = $DIC->http()->wrapper();
        $this->refinery = $DIC->refinery();

        if (!$this->access->checkAccess('write', '', $this->forum->getRefId())) {
            $this->error->raiseError($this->lng->txt('permission_denied'), $this->error->MESSAGE);
        }

        $this->oForumModerators = new ilForumModerators($this->forum->getRefId());
    }

    public function executeCommand(): void
    {
        $next_class = $this->ctrl->getNextClass($this);
        $cmd = $this->ctrl->getCmd();

        switch (strtolower($next_class)) {
            case strtolower(ilRepositorySearchGUI::class):
                $rep_search = new ilRepositorySearchGUI();
                $rep_search->setCallback($this, 'addModerator');
                $this->ctrl->setReturn($this, 'showModerators');
                $this->ctrl->forwardCommand($rep_search);
                break;

            default:
                if (!$cmd) {
                    $cmd = 'showModerators';
                }
                $this->$cmd();
                break;
        }
    }

    /**
     * @param list<int> $users
     */
    public function addModerator(array $users = []): void
    {
        if ($users === []) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt('frm_moderators_select_one'));
            return;
        }

        $frm_properties = ilForumProperties::getInstance(ilObject::_lookupObjId($this->forum->getRefId()));
        $notificaton_type = $frm_properties->getNotificationType();
        $is_membersip_enabled_parent = $this->forum->isParentMembershipEnabledContainer();
        $tmp_frm_noti = new ilForumNotification($this->forum->getRefId());

        foreach ($users as $usr_id) {
            $this->oForumModerators->addModeratorRole($usr_id);
            if ($is_membersip_enabled_parent && $notificaton_type !== NotificationType::DEFAULT) {
                $tmp_frm_noti->setUserId($usr_id);
                $tmp_frm_noti->setUserIdNoti($this->user->getId());
                $tmp_frm_noti->setUserToggle($frm_properties->getUserToggleNoti());
                $tmp_frm_noti->setAdminForce($frm_properties->getAdminForceNoti());
                $tmp_frm_noti->insertAdminForce();
            }
        }

        $this->tpl->setOnScreenMessage('success', $this->lng->txt('frm_moderator_role_added_successfully'), true);
        $this->ctrl->redirect($this, 'showModerators');
    }

    public function detachModeratorRole(): void
    {
        $usr_ids = $this->http_wrapper->post()->retrieve(
            'usr_id',
            $this->refinery->byTrying([
                $this->refinery->kindlyTo()->listOf($this->refinery->kindlyTo()->int()),
                $this->refinery->always([])
            ])
        );

        if ($usr_ids === []) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt('frm_moderators_select_at_least_one'));
            $this->ctrl->redirect($this, 'showModerators');
        }

        $entries = $this->oForumModerators->getCurrentModerators();
        if (count($usr_ids) === count($entries)) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt('frm_at_least_one_moderator'));
            $this->ctrl->redirect($this, 'showModerators');
        }

        $frm_properties = ilForumProperties::getInstance(ilObject::_lookupObjId($this->forum->getRefId()));
        $obj_id = $frm_properties->getObjId();
        $notificaton_type = $frm_properties->getNotificationType();
        $is_membersip_enabled_parent = $this->forum->isParentMembershipEnabledContainer();
        $need_participants = $is_membersip_enabled_parent && $notificaton_type !== NotificationType::DEFAULT;
        $tmp_frm_noti = new ilForumNotification($this->forum->getRefId());

        $participants_result = $need_participants
            ? new \ILIAS\Data\Result\Ok($this->forum->parentParticipants())
            : new \ILIAS\Data\Result\Error("Participants not required for ref_id {$this->forum->getRefId()}");

        foreach ($usr_ids as $usr_id) {
            $this->oForumModerators->detachModeratorRole($usr_id);
            $participants_result->map(function (ilParticipants $participants) use ($tmp_frm_noti, $usr_id, $obj_id) {
                if (!$participants->isAssigned($usr_id)) {
                    $tmp_frm_noti->setUserId($usr_id);
                    $tmp_frm_noti->setForumId($obj_id);
                    $tmp_frm_noti->deleteAdminForce();
                }
            });
        }

        $this->tpl->setOnScreenMessage('success', $this->lng->txt('frm_moderators_detached_role_successfully'), true);
        $this->ctrl->redirect($this, 'showModerators');
    }

    public function showModerators(): void
    {
        ilRepositorySearchGUI::fillAutoCompleteToolbar(
            $this,
            $this->toolbar,
            [
                'auto_complete_name' => $this->lng->txt('user'),
                'submit_name' => $this->lng->txt('add'),
                'add_search' => true,
                'add_from_container' => $this->oForumModerators->getRefId()
            ]
        );

        $tbl = new ilForumModeratorsTableGUI($this, 'showModerators', $this->forum->getRefId());

        $entries = $this->oForumModerators->getCurrentModerators();
        $num = count($entries);
        $result = [];
        $i = 0;
        foreach ($entries as $usr_id) {
            /** @var ilObjUser $user */
            $user = ilObjectFactory::getInstanceByObjId($usr_id, false);
            if (!($user instanceof ilObjUser)) {
                $this->oForumModerators->detachModeratorRole($usr_id);
                continue;
            }

            if ($num > 1) {
                $result[$i]['check'] = ilLegacyFormElementsUtil::formCheckbox(false, 'usr_id[]', (string) $user->getId());
            } else {
                $result[$i]['check'] = '';
            }
            $result[$i]['login'] = $user->getLogin();
            $result[$i]['firstname'] = $user->getFirstname();
            $result[$i]['lastname'] = $user->getLastname();
            ++$i;
        }

        $tbl->setData($result);
        $this->tpl->setContent($tbl->getHTML());
    }
}
