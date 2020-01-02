<?php
/* Copyright (c) 1998-2012 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilForumModeratorsGUI
 * @author Nadia Matuschek <nmatuschek@databay.de>
 * @ilCtrl_Calls ilForumModeratorsGUI: ilRepositorySearchGUI
 * @ingroup ModulesForum
 */
class ilForumModeratorsGUI
{
    private $ctrl;
    private $tpl;
    private $lng;
    private $tabs;
    private $error;
    private $user;
    private $toolbar;
    
    /**
     * @var ilForumModerators
     */
    private $oForumModerators;

    private $ref_id = 0;

    public function __construct()
    {
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

        if (!$this->access->checkAccess('write', '', (int) $_GET['ref_id'])) {
            $this->error->raiseError($this->lng->txt('permission_denied'), $this->error->MESSAGE);
        }

        $this->oForumModerators = new ilForumModerators((int) $_GET['ref_id']);
        $this->ref_id = (int) $_GET['ref_id'];
    }

    /**
     *
     */
    public function executeCommand()
    {
        $next_class = $this->ctrl->getNextClass($this);
        $cmd        = $this->ctrl->getCmd();

        switch ($next_class) {
            case 'ilrepositorysearchgui':
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
     *
     */
    public function addModerator($users = array())
    {
        if (!$users) {
            ilUtil::sendFailure($this->lng->txt('frm_moderators_select_one'));
            return;
        }

        $isCrsGrp = ilForumNotification::_isParentNodeGrpCrs($this->ref_id);
        $objFrmProps = ilForumProperties::getInstance(ilObject::_lookupObjId($this->ref_id));
        $frm_noti_type = $objFrmProps->getNotificationType();
        
        foreach ($users as $user_id) {
            $this->oForumModerators->addModeratorRole((int) $user_id);
            if ($isCrsGrp && $frm_noti_type != 'default') {
                $tmp_frm_noti = new ilForumNotification($this->ref_id);
                $tmp_frm_noti->setUserId((int) $user_id);
                $tmp_frm_noti->setUserIdNoti($this->user->getId());
                $tmp_frm_noti->setUserToggle((int) $objFrmProps->getUserToggleNoti());
                $tmp_frm_noti->setAdminForce((int) $objFrmProps->getAdminForceNoti());

                $tmp_frm_noti->insertAdminForce();
            }
        }

        ilUtil::sendSuccess($this->lng->txt('frm_moderator_role_added_successfully'), true);
        $this->ctrl->redirect($this, 'showModerators');
    }

    /**
     *
     */
    public function detachModeratorRole()
    {
        if (!isset($_POST['usr_id']) || !is_array($_POST['usr_id'])) {
            ilUtil::sendFailure($this->lng->txt('frm_moderators_select_at_least_one'));
            return $this->showModerators();
        }

        $entries = $this->oForumModerators->getCurrentModerators();
        if (count($_POST['usr_id']) == count($entries)) {
            ilUtil::sendFailure($this->lng->txt('frm_at_least_one_moderator'));
            return $this->showModerators();
        }

        $isCrsGrp = ilForumNotification::_isParentNodeGrpCrs($this->ref_id);

        $objFrmProps = ilForumProperties::getInstance(ilObject::_lookupObjId($this->ref_id));
        $frm_noti_type = $objFrmProps->getNotificationType();
        
        foreach ($_POST['usr_id'] as $usr_id) {
            $this->oForumModerators->detachModeratorRole((int) $usr_id);

            if ($isCrsGrp && $frm_noti_type != 'default') {
                if (!ilParticipants::_isParticipant($this->ref_id, $usr_id)) {
                    $tmp_frm_noti = new ilForumNotification($this->ref_id);
                    $tmp_frm_noti->setUserId((int) $usr_id);
                    $tmp_frm_noti->setForumId(ilObject::_lookupObjId($this->ref_id));

                    $tmp_frm_noti->deleteAdminForce();
                }
            }
        }

        ilUtil::sendSuccess($this->lng->txt('frm_moderators_detached_role_successfully'), true);
        $this->ctrl->redirect($this, 'showModerators');
    }

    /**
     *
     */
    public function showModerators()
    {
        ilRepositorySearchGUI::fillAutoCompleteToolbar(
            $this,
            $this->toolbar,
            array(
                'auto_complete_name' => $this->lng->txt('user'),
                'submit_name'        => $this->lng->txt('add'),
                'add_search'         => true,
                'add_from_container' => $this->oForumModerators->getRefId()
            )
        );

        $tbl = new ilForumModeratorsTableGUI($this, 'showModerators', '', (int) $_GET['ref_id']);

        $entries = $this->oForumModerators->getCurrentModerators();
        $num     = count($entries);
        $result  = array();
        $i       = 0;
        foreach ($entries as $usr_id) {
            /**
             * @var $user ilObjUser
             */
            $user = ilObjectFactory::getInstanceByObjId($usr_id, false);
            // Bugfix/Fallback for #25640
            if (!$user) {
                $this->oForumModerators->detachModeratorRole((int) $usr_id);
                continue;
            }

            if ($num > 1) {
                $result[$i]['check'] = ilUtil::formCheckbox(false, 'usr_id[]', $user->getId());
            } else {
                $result[$i]['check'] = '';
            }
            $result[$i]['login']     = $user->getLogin();
            $result[$i]['firstname'] = $user->getFirstname();
            $result[$i]['lastname']  = $user->getLastname();
            ++$i;
        }

        $tbl->setData($result);
        $this->tpl->setContent($tbl->getHTML());
    }
}
