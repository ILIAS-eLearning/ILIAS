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
* Utility class for meta data handling
*
* @author Stefan Meyer <meyer@leifos.com>
* @package ilias-core
* @version $Id$
*/
class ilMDUtils
{
    /**
     * LOM datatype duration is a string like P2M4DT7H18M2S (2 months 4 days 7 hours 18 minutes 2 seconds)
     * This function tries to parse a given string in an array of months, days, hours, minutes and seconds
     *
     * @param string string to parse
     * @return array  e.g array(1,2,0,1,2) => 1 month,2 days, 0 hours, 1 minute, 2 seconds or false if not parsable
     *
     */
    public static function _LOMDurationToArray($a_string)
    {
        $a_string = trim($a_string);
        #$pattern = '/^(PT)?(\d{1,2}H)?(\d{1,2}M)?(\d{1,2}S)?$/i';
        $pattern = '/^P(\d{1,2}M)?(\d{1,2}D)?(T(\d{1,2}H)?(\d{1,2}M)?(\d{1,2}S)?)?$/i';

        if (!preg_match($pattern, $a_string, $matches)) {
            return false;
        }
        // Month
        if (preg_match('/^P(\d+)M/i', $a_string, $matches)) {
            $months = $matches[1];
        }
        // Days
        if (preg_match('/(\d+)+D/i', $a_string, $matches)) {
            #var_dump("<pre>",$matches,"<pre>");
            $days = $matches[1];
        }
        
        if (preg_match('/(\d+)+H/i', $a_string, $matches)) {
            #var_dump("<pre>",$matches,"<pre>");
            $hours = $matches[1];
        }
        if (preg_match('/T(\d{1,2}H)?(\d+)M/i', $a_string, $matches)) {
            #var_dump("<pre>",$matches,"<pre>");
            $min = $matches[2];
        }
        if (preg_match('/(\d+)S/i', $a_string, $matches)) {
            #var_dump("<pre>",$matches,"<pre>");
            $sec = $matches[1];
        }
        
        // Hack for zero values
        if (!$months and !$days and !$hours and !$min and !$sec) {
            return false;
        }
        
        return array($months,$days,$hours,$min,$sec);
    }
    
    /**
     * Fill html meta tags
     *
     * @access public
     * @static
     *
     * @param int rbac_id
     * @param int obj_id
     * @param string obj type
     */
    public static function _fillHTMLMetaTags($a_rbac_id, $a_obj_id, $a_type)
    {
        global $DIC;

        $tpl = $DIC['tpl'];
        $ilObjDataCache = $DIC['ilObjDataCache'];
        
        include_once('Services/MetaData/classes/class.ilMDKeyword.php');
        foreach (ilMDKeyword::_getKeywordsByLanguageAsString($a_rbac_id, $a_obj_id, $a_type) as $lng_code => $key_string) {
            $tpl->setCurrentBlock('mh_meta_item');
            $tpl->setVariable('MH_META_NAME', 'keywords');
            $tpl->setVariable('MH_META_LANG', $lng_code);
            $tpl->setVariable('MH_META_CONTENT', $key_string);
            $tpl->parseCurrentBlock();
        }
        include_once('Services/MetaData/classes/class.ilMDContribute.php');
        foreach (ilMDContribute::_lookupAuthors($a_rbac_id, $a_obj_id, $a_type) as $author) {
            $tpl->setCurrentBlock('mh_meta_item');
            $tpl->setVariable('MH_META_NAME', 'author');
            $tpl->setVariable('MH_META_CONTENT', $author);
            $tpl->parseCurrentBlock();
        }
    }

    /**
     * Parse copyright
     *
     *
     * @access public
     * @static
     *
     * @param string copyright
     */
    public static function _parseCopyright($a_copyright)
    {
        include_once('Services/MetaData/classes/class.ilMDSettings.php');
        $settings = ilMDSettings::_getInstance();
        if (!$settings->isCopyrightSelectionActive()) {
            return $a_copyright;
        }
        include_once('Services/MetaData/classes/class.ilMDCopyrightSelectionEntry.php');
        return ilMDCopyrightSelectionEntry::_lookupCopyright($a_copyright);
    }
}
