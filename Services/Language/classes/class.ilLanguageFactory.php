<?php declare(strict_types=1);

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
 ********************************************************************
 */

/**
*
* @author Stefan Meyer <smeyer.ilias@gmx.de>
* @version $Id$
*
*
* @ingroup ServicesLanguage
*/
class ilLanguageFactory
{
    private static array $languages = array();
    
    /**
     * Get language object
     */
    public static function _getLanguage(string $a_lang_key = '') : ilLanguage
    {
        global $DIC;
        $lng = $DIC->language();
        
        if (!$a_lang_key) {
            if (is_object($lng)) {
                $a_lang_key = $lng->getDefaultLanguage();
            } else {
                $a_lang_key = "en";
            }
        }

        return self::$languages[$a_lang_key] ?? (self::$languages[$a_lang_key] = new ilLanguage($a_lang_key));
    }
    
    /**
     * Get language object of user
     * @static
     */
    public static function _getLanguageOfUser(int $a_usr_id) : ilLanguage
    {
        return self::_getLanguage(ilObjUser::_lookupLanguage($a_usr_id));
    }
}
