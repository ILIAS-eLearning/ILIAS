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
 * Class for creating internal links on e.g repostory items.
 * This class uses goto.php to create permanent links
 *
 * @author Stefan Meyer <meyer@leifos.com>
 */
class ilLink
{
    protected const LINK_SCRIPT = "goto.php";

    public static function _getLink(
        ?int $a_ref_id,
        string $a_type = '',
        array $a_params = array(),
        string $append = ""
    ): string {
        global $DIC;

        $ilObjDataCache = $DIC["ilObjDataCache"];
        $objDefinition = $DIC['objDefinition'];

        if ($a_type === '' && !is_null($a_ref_id)) {
            $a_type = $ilObjDataCache->lookupType($ilObjDataCache->lookupObjId($a_ref_id));
        }
        $param_string = '';
        if (is_array($a_params) && count($a_params)) {
            foreach ($a_params as $name => $value) {
                $param_string .= ('&' . $name . '=' . $value);
            }
        }

        // workaround for administration links
        if ($objDefinition->isAdministrationObject($a_type)) {
            return ILIAS_HTTP_PATH . '/ilias.php?baseClass=ilAdministrationGUI&cmd=jump&ref_id=' . $a_ref_id;
        }
        switch ($a_type) {
            case 'git':
                //case 'pg':
                return ILIAS_HTTP_PATH . '/' . self::LINK_SCRIPT . '?client_id=' . CLIENT_ID . $param_string . $append;

            default:
                return ILIAS_HTTP_PATH . '/' . self::LINK_SCRIPT . '?target=' . $a_type . '_' . $a_ref_id . $append . '&client_id=' . CLIENT_ID . $param_string;
        }
    }

    /**
     * Get static link
     * @param int reference id
     * @param string object type
     * @param bool fallback to goto.php if robots are disabled
     * @return string goto.html or goto.php link
     */
    public static function _getStaticLink(
        ?int $a_ref_id,
        string $a_type = '',
        bool $a_fallback_goto = true,
        string $append = ""
    ): string {
        global $DIC;

        $ilObjDataCache = $DIC["ilObjDataCache"];

        if ($a_type === '' && $a_ref_id) {
            $a_type = $ilObjDataCache->lookupType($ilObjDataCache->lookupObjId($a_ref_id));
        }

        $robot_settings = ilRobotSettings::getInstance();
        if (!$robot_settings->robotSupportEnabled()) {
            if ($a_fallback_goto) {
                return self::_getLink($a_ref_id, $a_type, array(), $append);
            }

            return false;
        }

        // urlencode for append is needed e.g. to process "/" in wiki page names correctly
        return ILIAS_HTTP_PATH . '/goto_' . urlencode(CLIENT_ID) . '_' . $a_type . '_' . $a_ref_id . urlencode($append) . '.html';
    }
}
