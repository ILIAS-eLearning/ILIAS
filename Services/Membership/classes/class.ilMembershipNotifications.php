<?php declare(strict_types=1);/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Membership notification settings
 * @author  Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 * @ingroup ServicesMembership
 */
class ilMembershipNotifications
{
    protected int $ref_id;
    protected int $mode;
    protected array $custom;
    protected ?ilParticipants $participants = null;

    protected const VALUE_OFF = 0;
    protected const VALUE_ON = 1;
    protected const VALUE_BLOCKED = 2;

    protected const MODE_SELF = 1;
    protected const MODE_ALL = 2;
    protected const MODE_ALL_BLOCKED = 3;
    protected const MODE_CUSTOM = 4;

    protected ilSetting $setting;
    protected ilDBInterface $db;
    protected ilTree $tree;
    protected ilObjUser $user;

    public function __construct(int $a_ref_id)
    {
        global $DIC;

        $this->db = $DIC->database();

        $this->ref_id = $a_ref_id;
        $this->custom = [];
        $this->setting = $DIC->settings();
        $this->tree = $DIC->repositoryTree();
        $this->user = $DIC->user();

        $this->setMode(self::MODE_SELF);
        if ($this->ref_id) {
            $this->read();
        }
    }

    public static function isActive() : bool
    {
        global $DIC;

        $ilSetting = $DIC['ilSetting'];
        if (!$ilSetting->get("block_activated_news") || !$ilSetting->get("crsgrp_ntf")) {
            return false;
        }
        return true;
    }

    public static function isActiveForRefId(int $ref_id) : bool
    {
        if (!self::isActive()) {
            return false;
        }
        // see #31471, #30687, and ilNewsItem::getNewsForRefId
        $obj_id = ilObject::_lookupObjId($ref_id);
        if (
            !ilContainer::_lookupContainerSetting($obj_id, 'cont_use_news', "1") ||
            (
                !ilContainer::_lookupContainerSetting($obj_id, 'cont_show_news', "1") &&
                !ilContainer::_lookupContainerSetting($obj_id, 'news_timeline')
            )
        ) {
            return false;
        }
        return true;
    }

    protected function read() : void
    {
        $set = $this->db->query("SELECT nmode mode" .
            " FROM member_noti" .
            " WHERE ref_id = " . $this->db->quote($this->ref_id, "integer"));
        if ($this->db->numRows($set)) {
            $row = $this->db->fetchAssoc($set);
            $this->setMode($row["mode"]);

            if ($row["mode"] == self::MODE_CUSTOM) {
                $set = $this->db->query("SELECT *" .
                    " FROM member_noti_user" .
                    " WHERE ref_id = " . $this->db->quote($this->ref_id, "integer"));
                while ($row = $this->db->fetchAssoc($set)) {
                    $this->custom[$row["user_id"]] = $row["status"];
                }
            }
        }
    }

    public function getMode() : int
    {
        return $this->mode;
    }

    protected function setMode(int $a_value) : void
    {
        if ($this->isValidMode($a_value)) {
            $this->mode = $a_value;
        }
    }

    protected function isValidMode(int $a_value) : bool
    {
        $valid = array(
            self::MODE_SELF
            ,
            self::MODE_ALL
            ,
            self::MODE_ALL_BLOCKED
        );
        return in_array($a_value, $valid);
    }

    public function switchMode(int $a_new_mode) : void
    {
        if (!$this->ref_id) {
            return;
        }

        if (
            $this->mode &&
            $this->mode != $a_new_mode &&
            $this->isValidMode($a_new_mode)
        ) {
            $this->db->manipulate("DELETE FROM member_noti" .
                " WHERE ref_id = " . $this->db->quote($this->ref_id, "integer"));

            // no custom data
            if ($a_new_mode != self::MODE_CUSTOM) {
                $this->db->manipulate("DELETE FROM member_noti_user" .
                    " WHERE ref_id = " . $this->db->quote($this->ref_id, "integer"));
            }

            // mode self is default
            if ($a_new_mode != self::MODE_SELF) {
                $this->db->insert("member_noti", array(
                    "ref_id" => array("integer", $this->ref_id),
                    "nmode" => array("integer", $a_new_mode)
                ));
            }

            // remove all user settings (all active is preset, optional opt out)
            if ($a_new_mode == self::MODE_ALL) {
                $this->db->manipulate("DELETE FROM usr_pref" .
                    " WHERE " . $this->db->like("keyword", "text", "grpcrs_ntf_" . $this->ref_id));
            }
        }
        $this->setMode($a_new_mode);
    }

    protected function getParticipants() : ilParticipants
    {
        if ($this->participants === null) {
            $grp_ref_id = $this->tree->checkForParentType($this->ref_id, "grp");
            if ($grp_ref_id) {
                $this->participants = ilGroupParticipants::_getInstanceByObjId(ilObject::_lookupObjId($grp_ref_id));
            }

            if (!$this->participants) {
                $crs_ref_id = $this->tree->checkForParentType($this->ref_id, "crs");
                if ($crs_ref_id) {
                    $this->participants = ilCourseParticipants::_getInstanceByObjId(ilObject::_lookupObjId($crs_ref_id));
                }
            }
        }
        return $this->participants;
    }

    public function getActiveUsers() : array
    {
        $users = $all = array();
        $part_obj = $this->getParticipants();
        if ($part_obj) {
            $all = $part_obj->getParticipants();
        }
        if (!$all) {
            return [];
        }

        switch ($this->getMode()) {
            // users decide themselves
            case self::MODE_SELF:
                $set = $this->db->query("SELECT usr_id" .
                    " FROM usr_pref" .
                    " WHERE keyword = " . $this->db->quote("grpcrs_ntf_" . $this->ref_id, "text") .
                    " AND value = " . $this->db->quote(self::VALUE_ON, "text"));
                while ($row = $this->db->fetchAssoc($set)) {
                    $users[] = (int) $row["usr_id"];
                }
                break;

            // all members, mind opt-out
            case self::MODE_ALL:
                // users who did opt-out
                $inactive = array();
                $set = $this->db->query("SELECT usr_id" .
                    " FROM usr_pref" .
                    " WHERE keyword = " . $this->db->quote("grpcrs_ntf_" . $this->ref_id, "text") .
                    " AND value = " . $this->db->quote(self::VALUE_OFF, "text"));
                while ($row = $this->db->fetchAssoc($set)) {
                    $inactive[] = (int) $row["usr_id"];
                }
                $users = array_diff($all, $inactive);
                break;

            // all members, no opt-out
            case self::MODE_ALL_BLOCKED:
                $users = $all;
                break;

            // custom settings
            case self::MODE_CUSTOM:
                foreach ($this->custom as $user_id => $status) {
                    if ($status != self::VALUE_OFF) {
                        $users[] = (int) $user_id;
                    }
                }
                break;
        }
        return array_intersect($all, $users);
    }

    public function activateUser(int $a_user_id = null) : bool
    {
        return $this->toggleUser(true, $a_user_id);
    }

    public function deactivateUser(int $a_user_id = null) : bool
    {
        return $this->toggleUser(false, $a_user_id);
    }

    protected function getUser($a_user_id = null) : ?ilObjUser
    {
        if (
            $a_user_id === null ||
            $a_user_id == $this->user->getId()
        ) {
            $user = $this->user;
        } else {
            $user = new ilObjUser($a_user_id);
        }

        if (
            $user->getId() &&
            $user->getId() != ANONYMOUS_USER_ID
        ) {
            return $user;
        }
        return null;
    }

    protected function toggleUser(bool $a_status, int $a_user_id = null) : bool
    {
        if (!self::isActive()) {
            return false;
        }

        switch ($this->getMode()) {
            case self::MODE_ALL:
            case self::MODE_SELF:
                // current user!
                $user = $this->getUser();
                if ($user) {
                    // blocked value not supported in user pref!
                    $user->setPref("grpcrs_ntf_" . $this->ref_id, (int) $a_status);
                    $user->writePrefs();
                    return true;
                }
                break;

            case self::MODE_CUSTOM:
                $user = $this->getUser($a_user_id);
                if ($user) {
                    $user_id = $user->getId();

                    // did status change at all?
                    if (!array_key_exists($user_id, $this->custom) ||
                        $this->custom[$user_id != $a_status]) {
                        $this->custom[$user_id] = $a_status;

                        $this->db->replace(
                            "member_noti_user",
                            array(
                                "ref_id" => array("integer", $this->ref_id),
                                "user_id" => array("integer", $user_id),
                            ),
                            array(
                                "status" => array("integer", $a_status)
                            )
                        );
                    }
                    return true;
                }
                break;

            case self::MODE_ALL_BLOCKED:
                // no individual settings
                break;
        }
        return false;
    }

    public function isCurrentUserActive() : bool
    {
        return in_array($this->user->getId(), $this->getActiveUsers());
    }

    public function canCurrentUserEdit() : bool
    {
        $user_id = $this->user->getId();
        if ($user_id == ANONYMOUS_USER_ID) {
            return false;
        }

        switch ($this->getMode()) {
            case self::MODE_SELF:
            case self::MODE_ALL:
                return true;

            case self::MODE_ALL_BLOCKED:
                return false;

            case self::MODE_CUSTOM:
                return !(array_key_exists($user_id, $this->custom) &&
                    $this->custom[$user_id] == self::VALUE_BLOCKED);
        }
        return false;
    }

    /**
     * Get active notifications for all objects
     */
    public static function getActiveUsersforAllObjects() : array
    {
        global $DIC;

        $ilDB = $DIC->database();
        $tree = $DIC->repositoryTree();
        $log = $DIC->logger()->mmbr();

        $res = array();
        if (self::isActive()) {
            $objects = array();

            // user-preference data (MODE_SELF)
            $log->debug("read usr_pref");
            $set = $ilDB->query("SELECT DISTINCT(keyword) keyword" .
                " FROM usr_pref" .
                " WHERE " . $ilDB->like("keyword", "text", "grpcrs_ntf_%") .
                " AND value = " . $ilDB->quote("1", "text"));
            while ($row = $ilDB->fetchAssoc($set)) {
                $ref_id = substr($row["keyword"], 11);
                $objects[(int) $ref_id] = (int) $ref_id;
            }

            // all other modes
            $log->debug("read member_noti");
            $set = $ilDB->query("SELECT ref_id" .
                " FROM member_noti");
            while ($row = $ilDB->fetchAssoc($set)) {
                $objects[(int) $row["ref_id"]] = (int) $row["ref_id"];
            }

            // this might be slow but it is to be used in CRON JOB ONLY!
            foreach (array_unique($objects) as $ref_id) {
                // :TODO: enough checking?
                if (!$tree->isDeleted($ref_id)) {
                    $log->debug("get active users");
                    $noti = new self($ref_id);
                    $active = $noti->getActiveUsers();
                    if (sizeof($active)) {
                        $res[$ref_id] = $active;
                    }
                }
            }
        }
        return $res;
    }

    /**
     * Add notification settings to form
     * @param int                    $a_ref_id
     * @param ilPropertyFormGUI|null $a_form
     * @param ilFormPropertyGUI|null $a_input
     */
    public static function addToSettingsForm(
        int $a_ref_id,
        ?ilPropertyFormGUI $a_form = null,
        ?ilFormPropertyGUI $a_input = null
    ) : void {
        global $DIC;

        $lng = $DIC->language();

        if (self::isActive() &&
            $a_ref_id) {
            $lng->loadLanguageModule("membership");
            $noti = new self($a_ref_id);

            $force_noti = new ilRadioGroupInputGUI($lng->txt("mem_force_notification"), "force_noti");
            $force_noti->setRequired(true);
            if ($a_form) {
                $a_form->addItem($force_noti);
            } else {
                $a_input->addSubItem($force_noti);
            }

            if ($noti->isValidMode(self::MODE_SELF)) {
                $option = new ilRadioOption($lng->txt("mem_force_notification_mode_self"), (string) self::MODE_SELF);
                $force_noti->addOption($option);
            }
            if ($noti->isValidMode(self::MODE_ALL_BLOCKED)) {
                $option = new ilRadioOption($lng->txt("mem_force_notification_mode_blocked"),
                    (string) self::MODE_ALL_BLOCKED);
                $force_noti->addOption($option);

                if ($noti->isValidMode(self::MODE_ALL)) {
                    $changeable = new ilCheckboxInputGUI($lng->txt("mem_force_notification_mode_all_sub_blocked"),
                        "force_noti_allblk");
                    $option->addSubItem($changeable);
                }
            } elseif ($noti->isValidMode(self::MODE_ALL)) {
                $option = new ilRadioOption($lng->txt("mem_force_notification_mode_all"), (string) self::MODE_ALL);
                $force_noti->addOption($option);
            }

            // set current mode
            $current_mode = $noti->getMode();
            $has_changeable_cb = ($noti->isValidMode(self::MODE_ALL_BLOCKED) &&
                $noti->isValidMode(self::MODE_ALL));
            if (!$has_changeable_cb) {
                $force_noti->setValue((string) $current_mode);
            } else {
                switch ($current_mode) {
                    case self::MODE_SELF:
                        $force_noti->setValue((string) $current_mode);
                        /** @noinspection PhpUndefinedVariableInspection */
                        $changeable->setChecked(true); // checked as "default" on selection of parent
                        break;

                    case self::MODE_ALL_BLOCKED:
                        $force_noti->setValue((string) $current_mode);
                        break;

                    case self::MODE_ALL:
                        $force_noti->setValue((string) self::MODE_ALL_BLOCKED);
                        /** @noinspection PhpUndefinedVariableInspection */
                        $changeable->setChecked(true);
                        break;
                }
            }
        }
    }

    public static function importFromForm(int $a_ref_id, ?ilPropertyFormGUI $a_form = null) : void
    {
        if (self::isActive() &&
            $a_ref_id) {
            $noti = new self($a_ref_id);
            $has_changeable_cb = ($noti->isValidMode(self::MODE_ALL_BLOCKED) &&
                $noti->isValidMode(self::MODE_ALL));
            $changeable = null;
            if (!$a_form) {
                $mode = (int) $_POST["force_noti"];
                if ($has_changeable_cb) {
                    $changeable = (int) $_POST["force_noti_allblk"];
                }
            } else {
                $mode = $a_form->getInput("force_noti");
                if ($has_changeable_cb) {
                    $changeable = $a_form->getInput("force_noti_allblk");
                }
            }
            // checkbox (all) is subitem of all_blocked
            if ($changeable &&
                $mode == self::MODE_ALL_BLOCKED) {
                $mode = self::MODE_ALL;
            }
            $noti->switchMode($mode);
        }
    }

    public function cloneSettings(int $new_ref_id) : void
    {
        $set = $this->db->queryF(
            "SELECT * FROM member_noti " .
            " WHERE ref_id = %s ",
            array("integer"),
            array($this->ref_id)
        );
        while ($rec = $this->db->fetchAssoc($set)) {
            $this->db->insert("member_noti", array(
                "ref_id" => array("integer", $new_ref_id),
                "nmode" => array("integer", $rec["nmode"])
            ));
        }
    }
}
