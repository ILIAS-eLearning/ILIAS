<?php
/*
    +-----------------------------------------------------------------------------+
    | ILIAS open source                                                           |
    +-----------------------------------------------------------------------------+
    | Copyright (c) 1998-2001 ILIAS open source, University of Cologne            |
    |                                                                             |
    | This program is free software; you can redistribute it and/or               |
    | modify it under the terms of the GNU General Public License                 |
    | as published by the Free Software Foundation; either version 2              |
    | of the License, or (at your option) any later version.                      |
    |                                                                             |
    | This program is distributed in the hope that it will be useful,             |
    | but WITHOUT ANY WARRANTY; without even the implied warranty of              |
    | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the               |
    | GNU General Public License for more details.                                |
    |                                                                             |
    | You should have received a copy of the GNU General Public License           |
    | along with this program; if not, write to the Free Software                 |
    | Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA. |
    +-----------------------------------------------------------------------------+
*/


/**
* Meta Data class Language codes and translations
*
* @package ilias-core
* @version $Id$
*/

class ilMDLanguageItem
{
    public $language_code;
    public $possible_language_codes = array();


    public function __construct($a_code)
    {
        $this->language_code = $a_code;
    }


    public function getLanguageCode()
    {
        $lang = ilMDLanguageItem::_getPossibleLanguageCodes();
        if (in_array($this->language_code, $lang)) {
            return $this->language_code;
        }
        return false;
    }


    /*
     * @static
     */
    public static function _getPossibleLanguageCodes()
    {
        return array("aa","ab","af","am","ar","as","ay","az","ba","be","bg","bh",
                     "bi","bn","bo","br","ca","co","cs","cy","da","de","dz","el","en","eo",
                     "es","et","eu","fa","fi","fj","fo","fr","fy","ga","gd","gl","gn","gu",
                     "ha","he","hi","hr","hu","hy","ia","ie","ik","id","is","it","iu","ja",
                     "jv","ka","kk","kl","km","kn","ko","ks","ku","ky","la","ln",
                     "lo","lt","lv","mg","mi","mk","ml","mn","mo","mr","ms","mt",
                     "my","na","ne","nl","no","oc","om","or","pa","pl","ps","pt",
                     "qu","rm","rn","ro",
                     "ru","rw",
                     "sa","sd","sg","sh","si","sk","sl","sm","sn","so","sq","sr","ss","st",
                     "su","sv","sw","ta","te","tg","th","ti","tk","tl","tn","to","tr","ts",
                     "tt","tw","ug","uk","ur","uz","vi","vo","wo","xh","yi","yo","za","zh",
                     "zu");
    }

    /*
     * @static
     */
    public static function _getLanguages()
    {
        global $DIC;

        $lng = $DIC['lng'];

        $lng->loadLanguageModule("meta");

        $langs = array();
        foreach (ilMDLanguageItem::_getPossibleLanguageCodes() as $lngcode) {
            $langs[$lngcode] = $lng->txt("meta_l_" . $lngcode);
        }
        asort($langs);
        return $langs;
    }
}
