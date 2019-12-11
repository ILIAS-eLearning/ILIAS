<?php

/* Copyright (c) 1998-2014 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * High level business class, interface to front ends
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 * @ingroup ServicesAwareness
 */
class ilAwarenessAct
{
    /**
     * @var ilLanguage
     */
    protected $lng;

    protected static $instances = array();
    protected $user_id;
    protected $ref_id = 0;
    protected static $collector;

    /**
     * Constructor
     *
     * @param int $a_user_id user ud
     */
    protected function __construct($a_user_id)
    {
        global $DIC;

        $this->lng = $DIC->language();
        $this->user_id = $a_user_id;
    }

    /**
     * Set ref id
     *
     * @param int $a_val ref id
     */
    public function setRefId($a_val)
    {
        $this->ref_id = $a_val;
    }

    /**
     * Get ref id
     *
     * @return int ref id
     */
    public function getRefId()
    {
        return $this->ref_id;
    }

    /**
     * Get instance (for a user)
     *
     * @param int $a_user_id user id
     * @return ilAwarenessAct actor class
     */
    public static function getInstance($a_user_id)
    {
        if (!isset(self::$instances[$a_user_id])) {
            self::$instances[$a_user_id] = new ilAwarenessAct($a_user_id);
        }

        return self::$instances[$a_user_id];
    }

    /**
     * Get awareness data
     *
     * @return ilAwarenessData awareness data
     */
    public function getAwarenessData($a_filter)
    {
        include_once("./Services/Awareness/classes/class.ilAwarenessData.php");
        $data = ilAwarenessData::getInstance($this->user_id);
        $data->setRefId($this->getRefId());
        $data->setFilter($a_filter);
        return $data->getData();
    }

    /**
     * Get awareness data
     *
     * @return ilAwarenessData awareness data
     */
    public function getAwarenessUserCounter()
    {
        include_once("./Services/Awareness/classes/class.ilAwarenessData.php");
        $data = ilAwarenessData::getInstance($this->user_id);
        $data->setRefId($this->getRefId());
        return $data->getUserCounter();
    }

    /**
     * Send OSD notification on new users
     */
    public function notifyOnNewOnlineContacts()
    {
        $lng = $this->lng;

        $awrn_set = new ilSetting("awrn");
        if (!$awrn_set->get("use_osd", true)) {
            return;
        }

        $ts = ilSession::get("awr_online_user_ts");

        $data = ilAwarenessData::getInstance($this->user_id);
        $data->setRefId($this->getRefId());
        $d = $data->getOnlineUserData($ts);

        $new_online_users = array();
        $no_ids = array();
        foreach ($d as $u) {
            $uname = "[" . $u->login . "]";
            if ($u->public_profile) {
                $uname = "<a href='./goto.php?target=usr_" . $u->id . "'>" . $u->lastname . ", " . $u->firstname . " " . $uname . "</a>";
            }
            if (!in_array($u->id, $no_ids)) {
                $new_online_users[] = $uname;
                $no_ids[] = $u->id;
            }
        }

        if (count($new_online_users) == 0) {
            return;
        }
        //var_dump($d); exit;
        $lng->loadLanguageModule('mail');

        include_once("./Services/Object/classes/class.ilObjectFactory.php");
        //$recipient = ilObjectFactory::getInstanceByObjId($this->user_id);
        $bodyParams = array(
            'online_user_names'         => implode("<br />", $new_online_users)
        );
        //var_dump($bodyParams); exit;
        require_once 'Services/Notifications/classes/class.ilNotificationConfig.php';
        $notification = new ilNotificationConfig('osd_main');
        $notification->setTitleVar('awareness_now_online', $bodyParams, 'awrn');
        $notification->setShortDescriptionVar('awareness_now_online_users', $bodyParams, 'awrn');
        $notification->setLongDescriptionVar('', $bodyParams, '');
        $notification->setAutoDisable(false);
        //$notification->setLink();
        $notification->setIconPath('templates/default/images/icon_usr.svg');
        $notification->setValidForSeconds(ilNotificationConfig::TTL_SHORT);
        $notification->setVisibleForSeconds(ilNotificationConfig::DEFAULT_TTS);

        //$notification->setHandlerParam('mail.sender', $sender_id);

        ilSession::set("awr_online_user_ts", date("Y-m-d H:i:s", time()));

        $notification->notifyByUsers(array($this->user_id));
    }
}
