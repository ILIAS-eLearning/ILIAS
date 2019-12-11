<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Class ilAccessibilitySupportContacts
 *
 * @author Thomas Famula <famula@leifos.de>
 */
class ilAccessibilitySupportContacts
{
    /**
     * Get list
     *
     * @return string comma separated list of contacts
     */
    public static function getList()
    {
        global $DIC;

        $ilSetting = $DIC->settings();

        return $ilSetting->get("accessibility_support_contacts");
    }

    /**
     * Set list
     *
     * @param string $a_list comma separated list of contacts
     */
    public static function setList($a_list)
    {
        global $DIC;

        $ilSetting = $DIC->settings();

        $list = explode(",", $a_list);
        $accounts = array();
        foreach ($list as $l) {
            if (ilObjUser::_lookupId(trim($l)) > 0) {
                $accounts[] = trim($l);
            }
        }

        return $ilSetting->set("accessibility_support_contacts", implode(",", $accounts));
    }

    /**
     * Get valid support contacts
     *
     * @return array array of user IDs
     */
    public static function getValidSupportContactIds()
    {
        $list = self::getList();
        $list = explode(",", $list);

        return ilObjUser::_lookupId($list);
    }

    /*
     * Get mailto: emails
     */
    public static function getMailsToAddress()
    {
        $emails = array();
        foreach (self::getValidSupportContactIds() as $id) {
            if (($e = ilObjUser::_lookupEmail($id)) != "") {
                $emails[] = $e;
            }
        }
        if (!empty($emails)) {
            $emails = implode(',', $emails);
            if (trim($emails)) {
                return $emails;
            }
        }
        return "";
    }
}
