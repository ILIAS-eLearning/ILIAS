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
 * Permission wrapper for wikis
 * @author Alexander Killing <killing@leifos.de>
 */
class ilWikiPerm
{
    public static function check(
        string $a_perm,
        int $a_ref_id,
        string $a_cmd = ""
    ) : bool {
        global $DIC;

        $ilAccess = $DIC->access();
        switch ($a_perm) {
            case "edit_wiki_navigation":
                return ($ilAccess->checkAccess("write", "", $a_ref_id)
                    || $ilAccess->checkAccess("edit_wiki_navigation", "", $a_ref_id));
            case "delete_wiki_pages":
                return ($ilAccess->checkAccess("write", "", $a_ref_id)
                    || $ilAccess->checkAccess("delete_wiki_pages", "", $a_ref_id));
            case "activate_wiki_protection":
                return ($ilAccess->checkAccess("write", "", $a_ref_id)
                    || $ilAccess->checkAccess("activate_wiki_protection", "", $a_ref_id));
            case "wiki_html_export":
                return ($ilAccess->checkAccess("write", "", $a_ref_id)
                    || $ilAccess->checkAccess("wiki_html_export", "", $a_ref_id));
        }
        return $ilAccess->checkAccess($a_perm, $a_cmd, $a_ref_id);
    }
}
