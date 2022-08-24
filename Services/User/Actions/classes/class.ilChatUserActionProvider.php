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
 * Adds link to chat
 * @author Alexander Killing <killing@leifos.de>
 */
class ilChatUserActionProvider extends ilUserActionProvider
{
    /** @var array<int, bool> */
    protected static array $user_access = array();
    /** @var array<int, bool> */
    protected static array $accepts_messages_cache = array();
    protected int $pub_ref_id = 0;
    protected bool $chat_enabled = false;
    protected bool $osc_enabled = false;

    public function __construct()
    {
        parent::__construct();

        $this->pub_ref_id = ilObjChatroom::_getPublicRefId();

        $chatSettings = new ilSetting('chatroom');
        $this->chat_enabled = (bool) $chatSettings->get('chat_enabled');
        $this->osc_enabled = (bool) $chatSettings->get('enable_osc');

        $this->lng->loadLanguageModule('chatroom');
    }

    public function getComponentId(): string
    {
        return "chtr";
    }

    /**
     * @return array<string,string>
     */
    public function getActionTypes(): array
    {
        return array(
            "invite" => $this->lng->txt('chat_user_action_invite_public_room'),
            "invite_osd" => $this->lng->txt('chat_user_action_invite_osd')
        );
    }

    protected function checkUserChatAccess(int $a_user_id): bool
    {
        if (!array_key_exists($a_user_id, self::$user_access)) {
            self::$user_access[$a_user_id] = $GLOBALS['DIC']->rbac()->system()->checkAccessOfUser($a_user_id, 'read', $this->pub_ref_id);
        }

        return self::$user_access[$a_user_id];
    }

    protected function acceptsMessages(int $a_user_id): bool
    {
        if (!array_key_exists($a_user_id, self::$accepts_messages_cache)) {
            self::$accepts_messages_cache[$a_user_id] = ilUtil::yn2tf(
                ilObjUser::_lookupPref($a_user_id, 'chat_osc_accept_msg') ?? 'n'
            );
        }

        return (bool) self::$accepts_messages_cache[$a_user_id];
    }

    /**
     * Collect all actions
     */
    public function collectActionsForTargetUser(int $a_target_user): ilUserActionCollection
    {
        $coll = ilUserActionCollection::getInstance();

        if (!$this->chat_enabled) {
            return $coll;
        }

        if ($this->checkUserChatAccess($this->getUserId()) && $a_target_user != $this->getUserId()) {
            if ($this->checkUserChatAccess($a_target_user)) {
                $f = new ilUserAction();
                $f->setType("invite");
                $f->setText($this->lng->txt('chat_invite_public_room'));
                $f->setHref('./ilias.php?baseClass=ilRepositoryGUI&amp;ref_id=' . $this->pub_ref_id . '&amp;usr_id=' . $a_target_user . '&amp;cmd=view-invitePD');
                $coll->addAction($f);
            }
        }

        if ($this->osc_enabled && $a_target_user != $this->getUserId()) {
            $f = new ilUserAction();
            $f->setHref('#');
            $f->setType("invite_osd");
            if ($this->acceptsMessages($a_target_user)) {
                $f->setText($this->lng->txt('chat_osc_start_conversation'));
                $f->setData(array(
                    'onscreenchat-userid' => $a_target_user,
                    'onscreenchat-username' => ilObjUser::_lookupLogin($a_target_user)
                ));
            } else {
                $f->setText($this->lng->txt('chat_osc_doesnt_accept_msg'));
                $f->setData(array(
                    'onscreenchat-inact-userid' => $a_target_user
                ));
            }
            $coll->addAction($f);
        }

        return $coll;
    }
}
