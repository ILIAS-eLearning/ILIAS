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

/**
 * Class ilForumModeratorsGUI
 * @author       Nadia Matuschek <nmatuschek@databay.de>
 * @ilCtrl_Calls ilForumModeratorsGUI: ilRepositorySearchGUI
 * @ingroup      components\ILIASForum
 */
class ilForumModeratorsGUI
{
    private readonly ilCtrlInterface $ctrl;
    private readonly ilGlobalTemplateInterface $tpl;
    private readonly ilLanguage $lng;
    private readonly ilTabsGUI $tabs;
    private readonly ilErrorHandling $error;
    private readonly ilObjUser $user;
    private readonly ilToolbarGUI $toolbar;
    private readonly ilForumModerators $oForumModerators;
    private int $ref_id = 0;
    private readonly ilAccessHandler $access;
    private readonly \ILIAS\HTTP\Wrapper\WrapperFactory $http_wrapper;
    private readonly \ILIAS\Refinery\Factory $refinery;
    private readonly \ILIAS\HTTP\Services $http;
    private readonly \ILIAS\UI\Factory $ui_factory;
    protected \ILIAS\UI\Renderer $ui_renderer;

    public function __construct()
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
        $this->http = $DIC->http();
        $this->refinery = $DIC->refinery();
        $this->ui_renderer = $DIC->ui()->renderer();
        $this->ui_factory = $DIC->ui()->factory();

        if ($this->http_wrapper->query()->has('ref_id')) {
            $this->ref_id = $this->http_wrapper->query()->retrieve(
                'ref_id',
                $this->refinery->kindlyTo()->int()
            );
        }

        if (!$this->access->checkAccess('write', '', $this->ref_id)) {
            $this->error->raiseError($this->lng->txt('permission_denied'), $this->error->MESSAGE);
        }

        $this->oForumModerators = new ilForumModerators($this->ref_id);
    }

    public function executeCommand(): void
    {
        $next_class = $this->ctrl->getNextClass($this) ?? '';
        $cmd = $this->ctrl->getCmd() ?? '';

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

    public function addModerator($users = []): void
    {
        if (!$users) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt('frm_moderators_select_one'));
            return;
        }

        $isCrsGrp = ilForumNotification::_isParentNodeGrpCrs($this->ref_id);
        $objFrmProps = ilForumProperties::getInstance(ilObject::_lookupObjId($this->ref_id));
        $frm_noti_type = $objFrmProps->getNotificationType();

        foreach ($users as $user_id) {
            $this->oForumModerators->addModeratorRole((int) $user_id);
            if ($isCrsGrp && $frm_noti_type !== 'default') {
                $tmp_frm_noti = new ilForumNotification($this->ref_id);
                $tmp_frm_noti->setUserId((int) $user_id);
                $tmp_frm_noti->setUserIdNoti($this->user->getId());
                $tmp_frm_noti->setUserToggle($objFrmProps->getUserToggleNoti());
                $tmp_frm_noti->setAdminForce($objFrmProps->getAdminForceNoti());

                $tmp_frm_noti->insertAdminForce();
            }
        }

        $this->tpl->setOnScreenMessage('success', $this->lng->txt('frm_moderator_role_added_successfully'), true);
        $this->ctrl->redirect($this, 'showModerators');
    }

    public function detachModeratorRole(): void
    {
        $usr_ids = [];
        if ($this->http_wrapper->query()->has('frm_moderators_table_usr_ids')) {
            $usr_ids = $this->http_wrapper->query()->retrieve(
                'frm_moderators_table_usr_ids',
                $this->refinery->kindlyTo()->listOf($this->refinery->kindlyTo()->int())
            );
        }

        if (!isset($usr_ids) || !is_array($usr_ids)) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt('frm_moderators_select_at_least_one'));
            $this->ctrl->redirect($this, 'showModerators');
        }

        $entries = $this->oForumModerators->getCurrentModerators();
        if (count($usr_ids) === count($entries)) {
            $this->tpl->setOnScreenMessage('failure', $this->lng->txt('frm_at_least_one_moderator'), true);
            $this->ctrl->redirect($this, 'showModerators');
        }

        $isCrsGrp = ilForumNotification::_isParentNodeGrpCrs($this->ref_id);

        $objFrmProps = ilForumProperties::getInstance(ilObject::_lookupObjId($this->ref_id));
        $frm_noti_type = $objFrmProps->getNotificationType();

        foreach ($usr_ids as $usr_id) {
            $this->oForumModerators->detachModeratorRole((int) $usr_id);

            if ($isCrsGrp && $frm_noti_type !== 'default' && !ilParticipants::_isParticipant($this->ref_id, $usr_id)) {
                $tmp_frm_noti = new ilForumNotification($this->ref_id);
                $tmp_frm_noti->setUserId((int) $usr_id);
                $tmp_frm_noti->setForumId(ilObject::_lookupObjId($this->ref_id));

                $tmp_frm_noti->deleteAdminForce();
            }
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
        if ($this->http_wrapper->query()->has('ref_id')) {
            $this->ref_id = $this->http_wrapper->query()->retrieve(
                'ref_id',
                $this->refinery->kindlyTo()->int()
            );
        }

        $tbl = new \ILIAS\Forum\Moderation\ForumModeratorsTable(
            $this->oForumModerators,
            $this->ctrl,
            $this->lng,
            $this->http,
            $this->ui_factory
        );

        $this->tpl->setContent($this->ui_renderer->render($tbl->getComponent()));
    }

    private function handleModeratorActions(): void
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
            default => $this->ctrl->redirect($this, 'showModerators'),
        };
    }
}
