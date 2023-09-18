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
 * System support contacts
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 * @ingroup ModulesSystemFolder
 */
class ilSystemSupportContacts
{
    /**
     * Get list
     *
     * @return string comma separated list of contacts
     */
    public static function getList(): string
    {
        global $DIC;

        $ilSetting = $DIC->settings();

        return $ilSetting->get("adm_support_contacts") ?? '';
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

        return $ilSetting->set("adm_support_contacts", implode(",", $accounts));
    }

    /**
     * Get valid support contacts
     *
     * @return array array of user IDs
     */
    public static function getValidSupportContactIds(): array
    {
        $list = self::getList();
        $list = explode(",", $list);

        return ilObjUser::_lookupId($list);
    }

    /**
     * Get mailto: emails
     *
     * @param
     * @return
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
