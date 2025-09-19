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
 * @ilCtrl_Calls ilForumModeratorsGUI: ilRepositorySearchGUI
 */
class ilForumModeratorsGUI implements ilCtrlSecurityInterface
{
    private const string CMD_SHOW_MODERATORS = 'showModerators';
    private const string CMD_ADD_MODERATOR = 'addModerator';
    private const string CMD_HANDLE_TABLE_ACTIONS = 'handleModeratorActions';
    private const string DEFAULT_CMD = self::CMD_SHOW_MODERATORS;

    private readonly ilCtrlInterface $ctrl;
    private readonly ilGlobalTemplateInterface $tpl;
    private readonly ilLanguage $lng;
    private readonly ilTabsGUI $tabs;
    private readonly ilErrorHandling $error;
    private readonly ilObjUser $user;
    private readonly ilToolbarGUI $toolbar;
    private readonly ilForumModerators $frm_moderators;
    private readonly ilAccessHandler $access;
    private readonly \ILIAS\HTTP\Wrapper\WrapperFactory $http_wrapper;
    private readonly \ILIAS\Refinery\Factory $refinery;
    private readonly \ILIAS\HTTP\Services $http;
    private readonly \ILIAS\UI\Factory $ui_factory;
    private readonly \ILIAS\UI\Renderer $ui_renderer;

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

        $this->lng->loadLanguageModule('search');
        $this->http_wrapper = $DIC->http()->wrapper();
        $this->http = $DIC->http();
        $this->refinery = $DIC->refinery();
        $this->ui_renderer = $DIC->ui()->renderer();
        $this->ui_factory = $DIC->ui()->factory();

        $this->frm_moderators = new ilForumModerators($this->forum->getRefId());
    }

    public function executeCommand(): void
    {
        if (!$this->access->checkAccess('write', '', $this->forum->getRefId())) {
            $this->error->raiseError($this->lng->txt('permission_denied'), $this->error->MESSAGE);
        }

        $this->tabs->activateTab('frm_moderators');

        $next_class = $this->ctrl->getNextClass($this) ?? '';
        $cmd = $this->ctrl->getCmd();

        switch (strtolower($next_class)) {
            case strtolower(ilRepositorySearchGUI::class):
                $rep_search = new ilRepositorySearchGUI();
                $rep_search->setCallback($this, self::CMD_ADD_MODERATOR . 'Command');
                $this->ctrl->setReturn($this, self::CMD_SHOW_MODERATORS);
                $this->ctrl->forwardCommand($rep_search);
                break;

            default:
                if ($cmd === null || $cmd === '' || !method_exists($this, $cmd . 'Command')) {
                    $cmd = self::DEFAULT_CMD;
                }
                $verified_command = $cmd . 'Command';

                $this->$verified_command();
                break;
        }
    }

    /**
     * @param list<int> $users
     */
    public function addModeratorCommand(array $users = []): void
    {
        if ($users === []) {
            $this->tpl->setOnScreenMessage(
                $this->tpl::MESSAGE_TYPE_FAILURE,
                $this->lng->txt('frm_moderators_select_one')
            );
            return;
        }

        $frm_properties = ilForumProperties::getInstance(ilObject::_lookupObjId($this->forum->getRefId()));
        $notificaton_type = $frm_properties->getNotificationType();
        $is_membersip_enabled_parent = $this->forum->isParentMembershipEnabledContainer();
        $tmp_frm_noti = new ilForumNotification($this->forum->getRefId());

        foreach ($users as $usr_id) {
            $this->frm_moderators->addModeratorRole($usr_id);
            if ($is_membersip_enabled_parent && $notificaton_type !== NotificationType::DEFAULT) {
                $tmp_frm_noti->setUserId($usr_id);
                $tmp_frm_noti->setUserIdNoti($this->user->getId());
                $tmp_frm_noti->setUserToggle($frm_properties->getUserToggleNoti());
                $tmp_frm_noti->setAdminForce($frm_properties->getAdminForceNoti());
                $tmp_frm_noti->insertAdminForce();
            }
        }

        $this->tpl->setOnScreenMessage(
            $this->tpl::MESSAGE_TYPE_SUCCESS,
            $this->lng->txt('frm_moderator_role_added_successfully'),
            true
        );
        $this->ctrl->redirect($this, self::CMD_SHOW_MODERATORS);
    }

    private function detachModeratorRole(): void
    {
        $usr_ids = $this->http_wrapper->query()->retrieve(
            'frm_moderators_table_usr_ids',
            $this->refinery->byTrying([
                $this->refinery->kindlyTo()->listOf($this->refinery->kindlyTo()->int()),
                $this->refinery->always([])
            ])
        );

        if ($usr_ids === []) {
            $this->tpl->setOnScreenMessage(
                $this->tpl::MESSAGE_TYPE_FAILURE,
                $this->lng->txt('frm_moderators_select_at_least_one')
            );
            $this->ctrl->redirect($this, self::CMD_SHOW_MODERATORS);
        }

        $entries = $this->frm_moderators->getCurrentModerators();
        if (count($usr_ids) === count($entries)) {
            $this->tpl->setOnScreenMessage(
                $this->tpl::MESSAGE_TYPE_FAILURE,
                $this->lng->txt('frm_at_least_one_moderator'),
                true
            );
            $this->ctrl->redirect($this, self::CMD_SHOW_MODERATORS);
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
            $this->frm_moderators->detachModeratorRole($usr_id);
            $participants_result->map(function (ilParticipants $participants) use ($tmp_frm_noti, $usr_id, $obj_id) {
                if (!$participants->isAssigned($usr_id)) {
                    $tmp_frm_noti->setUserId($usr_id);
                    $tmp_frm_noti->setForumId($obj_id);
                    $tmp_frm_noti->deleteAdminForce();
                }
            });
        }

        $this->tpl->setOnScreenMessage(
            $this->tpl::MESSAGE_TYPE_SUCCESS,
            $this->lng->txt('frm_moderators_detached_role_successfully'),
            true
        );
        $this->ctrl->redirect($this, self::CMD_SHOW_MODERATORS);
    }

    private function showModeratorsCommand(): void
    {
        ilRepositorySearchGUI::fillAutoCompleteToolbar(
            $this,
            $this->toolbar,
            [
                'auto_complete_name' => $this->lng->txt('user'),
                'submit_name' => $this->lng->txt('add'),
                'add_search' => true,
                'add_from_container' => $this->frm_moderators->getRefId()
            ]
        );

        $tbl = new \ILIAS\Forum\Moderation\ForumModeratorsTable(
            $this->frm_moderators,
            $this->lng,
            $this->http,
            $this->ui_factory,
            ilUtil::_getHttpPath() . '/' . $this->ctrl->getLinkTarget(
                $this,
                self::CMD_HANDLE_TABLE_ACTIONS
            )
        );

        $this->tpl->setContent($this->ui_renderer->render($tbl->getComponent()));
    }

    private function handleModeratorActionsCommand(): void
    {
        $action = $this->http_wrapper->query()->retrieve(
            'frm_moderators_table_action',
            $this->refinery->byTrying([
                $this->refinery->kindlyTo()->string(),
                $this->refinery->always('')
            ])
        );
        match ($action) {
            'detachModeratorRole' => $this->detachModeratorRole(),
            default => $this->ctrl->redirect($this, self::CMD_SHOW_MODERATORS),
        };
    }

    public function getUnsafeGetCommands(): array
    {
        return [
            self::CMD_HANDLE_TABLE_ACTIONS,
        ];
    }

    public function getSafePostCommands(): array
    {
        return [];
    }
}
