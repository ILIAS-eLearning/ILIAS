<?php

/* Copyright (c) 1998-2015 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Permission wrapper for wikis
 *
 * @author Alex Killing <alex.killing@gmx.de>
 * @version $Id$
 * @ingroup ModulesWiki
 */
class ilWikiPerm
{
    /**
     * Check permission
     *
     * @param string $a_perm permission
     * @param int $a_ref_id ref id
     * @return bool true/false
     */
    public static function check($a_perm, $a_ref_id, $a_cmd = "")
    {
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
