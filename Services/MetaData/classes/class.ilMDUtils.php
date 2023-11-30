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

declare(strict_types=1);

/**
 * Utility class for meta data handling
 * @author  Stefan Meyer <meyer@leifos.com>
 * @package ilias-core
 * @version $Id$
 */
class ilMDUtils
{
    /**
     * LOM datatype duration is a string like P2M4DT7H18M2S (2 months 4 days 7 hours 18 minutes 2 seconds)
     * This function tries to parse a given string in an array of months, days, hours, minutes and seconds
     * @return int[]  e.g array(1,2,0,1,2) => 1 month,2 days, 0 hours, 1 minute, 2 seconds or empty array if not parsable
     * @deprecated use DataHelper::durationToArray
     */
    public static function _LOMDurationToArray(string $a_string): array
    {
        global $DIC;

        $data_helper = $DIC->learningObjectMetadata()->dataHelper();

        $array = $data_helper->durationToArray($a_string);
        // this function never returned the year, so we throw it away for backwards compatibility
        array_shift($array);
        return $array ?? [];
    }

    public static function _fillHTMLMetaTags(int $a_rbac_id, int $a_obj_id, string $a_type): bool
    {
        global $DIC;

        // currently disabled due to mantis 0026864
        return true;

        $tpl = $DIC['tpl'];
        $ilObjDataCache = $DIC['ilObjDataCache'];

        foreach (ilMDKeyword::_getKeywordsByLanguageAsString(
            $a_rbac_id,
            $a_obj_id,
            $a_type
        ) as $lng_code => $key_string) {
            $tpl->setCurrentBlock('mh_meta_item');
            $tpl->setVariable('MH_META_NAME', 'keywords');
            $tpl->setVariable('MH_META_LANG', $lng_code);
            $tpl->setVariable('MH_META_CONTENT', $key_string);
            $tpl->parseCurrentBlock();
        }

        foreach (ilMDContribute::_lookupAuthors($a_rbac_id, $a_obj_id, $a_type) as $author) {
            $tpl->setCurrentBlock('mh_meta_item');
            $tpl->setVariable('MH_META_NAME', 'author');
            $tpl->setVariable('MH_META_CONTENT', $author);
            $tpl->parseCurrentBlock();
        }
        return true;
    }

    /**
     * Returns an empty string if copyright selection is not active,
     * regardless of input.
     */
    public static function _parseCopyright(string $a_copyright): string
    {
        $settings = ilMDSettings::_getInstance();
        if (!$settings->isCopyrightSelectionActive()) {
            return ilMDCopyrightSelectionEntry::isEntry($a_copyright) ? '' : $a_copyright;
        }

        return ilMDCopyrightSelectionEntry::_lookupCopyright($a_copyright);
    }

    /**
     * Returns an empty string if copyright selection is not active.
     */
    public static function _getDefaultCopyright(): string
    {
        $default_id = ilMDCopyrightSelectionEntry::getDefault();
        return self::_parseCopyright(
            ilMDCopyrightSelectionEntry::createIdentifier($default_id)
        );
    }
}
