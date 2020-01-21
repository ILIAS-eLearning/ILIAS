<?php
/* Copyright (c) 1998-2015 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Adds link to chat
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 * @ingroup ServicesUser
 */
class ilChatUserActionProvider extends ilUserActionProvider
{
    /**
     * @var array
     */
    protected static $user_access = array();

    /**
     * @var array
     */
    protected static $accepts_messages_cache = array();

    /**
     * @var int|string
     */
    protected $pub_ref_id = 0;

    /**
     * Boolean to indicate if the chat is enabled.
     *
     */
    protected $chat_enabled = false;

    /**
     * Boolean to indicate if on screen chat is enabled.
     */
    protected $osc_enabled = false;

    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();

        include_once 'Modules/Chatroom/classes/class.ilObjChatroom.php';
        $this->pub_ref_id = ilObjChatroom::_getPublicRefId();

        $chatSettings = new ilSetting('chatroom');
        $this->chat_enabled = $chatSettings->get('chat_enabled');
        $this->osc_enabled  = $chatSettings->get('enable_osc');

        $this->lng->loadLanguageModule('chatroom');
    }

    /**
     * @inheritdoc
     */
    public function getComponentId()
    {
        return "chtr";
    }

    /**
     * @inheritdoc
     */
    public function getActionTypes()
    {
        return array(
            "invite" => $this->lng->txt('chat_user_action_invite_public_room'),
            "invite_osd" => $this->lng->txt('chat_user_action_invite_osd')
        );
    }


    /**
     * Check user chat access
     *
     * @param int $a_user_id
     * @return bool
     */
    protected function checkUserChatAccess($a_user_id)
    {
        if (!array_key_exists($a_user_id, self::$user_access)) {
            self::$user_access[$a_user_id] = $GLOBALS['DIC']->rbac()->system()->checkAccessOfUser($a_user_id, 'read', $this->pub_ref_id);
        }

        return self::$user_access[$a_user_id];
    }

    /**
     * @param  int $a_user_id
     * @return bool
     */
    protected function acceptsMessages($a_user_id)
    {
        if (!array_key_exists($a_user_id, self::$accepts_messages_cache)) {
            self::$accepts_messages_cache[$a_user_id] = ilUtil::yn2tf(ilObjUser::_lookupPref($a_user_id, 'chat_osc_accept_msg'));
        }

        return self::$accepts_messages_cache[$a_user_id];
    }

    /**
     * Collect all actions
     *
     * @param int $a_target_user target user
     * @return ilUserActionCollection collection
     */
    public function collectActionsForTargetUser($a_target_user)
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
                    'onscreenchat-userid'   => $a_target_user,
                    'onscreenchat-username' => ilObjUser::_lookupLogin($a_target_user)
                ));
            } else {
                $f->setText($this->lng->txt('chat_osc_doesnt_accept_msg'));
                $f->setData(array(
                    'onscreenchat-inact-userid'   => $a_target_user
                ));
            }
            $coll->addAction($f);
        }

        return $coll;
    }
}
