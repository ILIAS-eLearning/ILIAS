<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

define('IL_INTERNAL_LINK_SCRIPT', 'goto.php');


/**
* Class for creating internal links on e.g repostory items.
* This class uses goto.php to create permanent links
*
* @author Stefan Meyer <meyer@leifos.com>
* @version $Id$
*
*/
class ilLink
{
    public static function _getLink($a_ref_id, $a_type = '', $a_params = array(), $append = "")
    {
        global $DIC;

        $ilObjDataCache = $DIC["ilObjDataCache"];

        if (!strlen($a_type)) {
            $a_type = $ilObjDataCache->lookupType($ilObjDataCache->lookupObjId($a_ref_id));
        }
        $param_string = '';
        if (is_array($a_params) && count($a_params)) {
            foreach ($a_params as $name => $value) {
                $param_string .= ('&' . $name . '=' . $value);
            }
        }
        return ILIAS_HTTP_PATH . '/' . IL_INTERNAL_LINK_SCRIPT . '?target=' . $a_type . '_' . $a_ref_id .
          $append . '&client_id=' . CLIENT_ID . $param_string;
    }

    /**
     * Get static link
     *
     * @access public
     * @static
     *
     * @param int reference id
     * @param string object type
     * @param bool fallback to goto.php if robots are disabled
     * @return string goto.html or goto.php link
     */
    public static function _getStaticLink(
        $a_ref_id,
        $a_type = '',
        $a_fallback_goto = true,
        $append = ""
    ) {
        global $DIC;

        $ilObjDataCache = $DIC["ilObjDataCache"];

        if (!strlen($a_type)) {
            $a_type = $ilObjDataCache->lookupType($ilObjDataCache->lookupObjId($a_ref_id));
        }
        
        include_once('Services/PrivacySecurity/classes/class.ilRobotSettings.php');
        $robot_settings = ilRobotSettings::_getInstance();
        if (!$robot_settings->robotSupportEnabled()) {
            if ($a_fallback_goto) {
                return ilLink::_getLink($a_ref_id, $a_type, array(), $append);
            } else {
                return false;
            }
        }
        
        // urlencode for append is needed e.g. to process "/" in wiki page names correctly
        return ILIAS_HTTP_PATH . '/goto_' . urlencode(CLIENT_ID) . '_' . $a_type . '_' . $a_ref_id . urlencode($append) . '.html';
    }
}
