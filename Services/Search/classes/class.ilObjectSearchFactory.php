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

include_once 'Services/Search/classes/class.ilSearchSettings.php';
/**
* Class ilObjectSearchFactory
*
* Factory for Fulltext/LikeObjectSearch classes
* It depends on the search administration setting which class is instantiated
*
* @author Stefan Meyer <smeyer.ilias@gmx.de>
* @version $Id$
*
* @package ServicesSearch
*/

class ilObjectSearchFactory
{
    
    /**
     * get reference of ilFulltext/LikeObjectSearch.
     *
     * @param object query parser object
     * @return object reference of ilFulltext/LikeObjectSearch
     */
    public static function _getObjectSearchInstance($query_parser)
    {
        include_once './Services/Search/classes/class.ilSearchSettings.php';

        $search_settings = new ilSearchSettings();

        if ($search_settings->enabledIndex()) {
            // FULLTEXT
            include_once 'Services/Search/classes/Fulltext/class.ilFulltextObjectSearch.php';
            return new ilFulltextObjectSearch($query_parser);
        } else {
            // LIKE
            include_once 'Services/Search/classes/Like/class.ilLikeObjectSearch.php';
            return new ilLikeObjectSearch($query_parser);
        }
    }
    
    /**
     *
     */
    public static function getByTypeSearchInstance($a_object_type, $a_query_parser)
    {
        switch ($a_object_type) {
            case 'wiki':
                return self::_getWikiContentSearchInstance($a_query_parser);
                
            case 'frm':
                return self::_getForumSearchInstance($a_query_parser);
                
            case 'lm':
                return self::_getLMContentSearchInstance($a_query_parser);
                
            default:
                return self::_getObjectSearchInstance($a_query_parser);
        }
    }

    /**
     * get reference of ilFulltext/LikeMetaDataSearch.
     *
     * @param object query parser object
     * @return object reference of ilFulltext/LikeMetaDataSearch
     */
    public static function _getMetaDataSearchInstance($query_parser)
    {
        if (ilSearchSettings::getInstance()->enabledIndex()) {
            include_once 'Services/Search/classes/Fulltext/class.ilFulltextMetaDataSearch.php';
            return new ilFulltextMetaDataSearch($query_parser);
        } else {
            include_once 'Services/Search/classes/Like/class.ilLikeMetaDataSearch.php';
            return new ilLikeMetaDataSearch($query_parser);
        }
    }

    /**
     * get reference of ilFulltextLMContentSearch
     *
     * @param object query parser object
     * @return object reference of ilFulltextLMContentSearch
     */
    public static function _getLMContentSearchInstance($query_parser)
    {
        if (ilSearchSettings::getInstance()->enabledIndex()) {
            include_once 'Services/Search/classes/Fulltext/class.ilFulltextLMContentSearch.php';
            return new ilFulltextLMContentSearch($query_parser);
        } else {
            include_once './Services/Search/classes/Like/class.ilLikeLMContentSearch.php';
            return new ilLikeLMContentSearch($query_parser);
        }
    }

    /**
     * get reference of ilFulltextForumSearch
     *
     * @param object query parser object
     * @return object reference of ilFulltextForumSearch
     */
    public static function _getForumSearchInstance($query_parser)
    {
        if (ilSearchSettings::getInstance()->enabledIndex()) {
            include_once 'Services/Search/classes/Fulltext/class.ilFulltextForumSearch.php';
            return new ilFulltextForumSearch($query_parser);
        } else {
            include_once 'Services/Search/classes/Like/class.ilLikeForumSearch.php';
            return new ilLikeForumSearch($query_parser);
        }
    }
        
    /**
     * get reference of ilFulltextGlossaryDefinitionSearch
     *
     * @param object query parser object
     * @return object reference of ilFulltextGlossaryDefinitionSearch
     */
    public static function _getGlossaryDefinitionSearchInstance($query_parser)
    {
        if (ilSearchSettings::getInstance()->enabledIndex()) {
            include_once 'Services/Search/classes/Fulltext/class.ilFulltextGlossaryDefinitionSearch.php';
            return new ilFulltextGlossaryDefinitionSearch($query_parser);
        } else {
            include_once 'Services/Search/classes/Like/class.ilLikeGlossaryDefinitionSearch.php';
            return new ilLikeGlossaryDefinitionSearch($query_parser);
        }
    }
    
    /**
     * get reference of ilFulltextExerciseSearch
     *
     * @param object query parser object
     * @return object reference of ilFulltextExerciseSearch
     */
    public static function _getExerciseSearchInstance($query_parser)
    {
        if (ilSearchSettings::getInstance()->enabledIndex()) {
            include_once 'Services/Search/classes/Fulltext/class.ilFulltextExerciseSearch.php';
            return new ilFulltextExerciseSearch($query_parser);
        } else {
            include_once 'Services/Search/classes/Like/class.ilLikeExerciseSearch.php';
            return new ilLikeExerciseSearch($query_parser);
        }
    }

    /**
     * get reference of ilFulltextMediacastSearch
     *
     * @param object query parser object
     * @return object reference of ilFulltextMediacastSearch
     */
    public static function _getMediacastSearchInstance($query_parser)
    {
        if (ilSearchSettings::getInstance()->enabledIndex()) {
            include_once 'Services/Search/classes/Fulltext/class.ilFulltextMediaCastSearch.php';
            return new ilFulltextMediaCastSearch($query_parser);
        } else {
            include_once 'Services/Search/classes/Like/class.ilLikeMediaCastSearch.php';
            return new ilLikeMediaCastSearch($query_parser);
        }
    }

    /**
     * get reference of ilFulltextTestSearch
     *
     * @param object query parser object
     * @return object reference of ilFulltextTestSearch
     */
    public static function _getTestSearchInstance($query_parser)
    {
        if (ilSearchSettings::getInstance()->enabledIndex()) {
            include_once 'Services/Search/classes/Fulltext/class.ilFulltextTestSearch.php';
            return new ilFulltextTestSearch($query_parser);
        } else {
            include_once 'Services/Search/classes/Like/class.ilLikeTestSearch.php';
            return new ilLikeTestSearch($query_parser);
        }
    }

    /**
     * get reference of ilFulltextMediapoolSearch
     *
     * @param object query parser object
     * @return object reference of ilFulltextMediapoolSearch
     */
    public static function _getMediaPoolSearchInstance($query_parser)
    {
        if (ilSearchSettings::getInstance()->enabledIndex()) {
            include_once 'Services/Search/classes/Fulltext/class.ilFulltextMediaPoolSearch.php';
            return new ilFulltextMediaPoolSearch($query_parser);
        } else {
            include_once 'Services/Search/classes/Like/class.ilLikeMediaPoolSearch.php';
            return new ilLikeMediaPoolSearch($query_parser);
        }
    }
    
    /**
     * get reference of ilFulltextAdvancedSearch
     *
     * @param object query parser object
     * @return object reference of ilFulltextAdvancedSearch
     */
    public static function _getAdvancedSearchInstance($query_parser)
    {
        if (ilSearchSettings::getInstance()->enabledIndex()) {
            include_once 'Services/Search/classes/Fulltext/class.ilFulltextAdvancedSearch.php';
            return new ilFulltextAdvancedSearch($query_parser);
        } else {
            include_once './Services/Search/classes/Like/class.ilLikeAdvancedSearch.php';
            return new ilLikeAdvancedSearch($query_parser);
        }
    }

    /**
     * get reference of ilFulltextWebresourceSearch
     *
     * @param object query parser object
     * @return object reference of ilWebresourceAdvancedSearch
     */
    public static function _getWebresourceSearchInstance($query_parser)
    {
        if (ilSearchSettings::getInstance()->enabledIndex()) {
            include_once 'Services/Search/classes/Fulltext/class.ilFulltextWebresourceSearch.php';
            return new ilFulltextWebresourceSearch($query_parser);
        } else {
            include_once 'Services/Search/classes/Like/class.ilLikeWebresourceSearch.php';
            return new ilLikeWebresourceSearch($query_parser);
        }
    }

    /**
     * get reference of ilLikeUserSearch
     *
     * @param object query parser object
     * @return object reference of ilWebresourceAdvancedSearch
     */
    public static function _getUserSearchInstance($query_parser)
    {
        include_once 'Services/Search/classes/Like/class.ilLikeUserSearch.php';
        return new ilLikeUserSearch($query_parser);
    }

    /**
     * get reference of ilLikeUserDefinedFieldSearch
     *
     * @param object query parser object
     * @return object reference of ilLikeUserDefinedFieldSearch
     */
    public static function _getUserDefinedFieldSearchInstance($query_parser)
    {
        include_once 'Services/Search/classes/Like/class.ilLikeUserDefinedFieldSearch.php';
        return new ilLikeUserDefinedFieldSearch($query_parser);
    }
    
    public static function getUserMultiFieldSearchInstance($query_parser)
    {
        include_once './Services/Search/classes/Like/class.ilLikeUserMultiFieldSearch.php';
        return new ilLikeUserMultiFieldSearch($query_parser);
    }
    
    /**
     * get reference of ilFulltextWikiContentSearch
     *
     * @param object query parser object
     * @return object reference of ilFulltextWikiContentSearch
     */
    public static function _getWikiContentSearchInstance($query_parser)
    {
        if (ilSearchSettings::getInstance()->enabledIndex()) {
            include_once 'Services/Search/classes/Fulltext/class.ilFulltextWikiContentSearch.php';
            return new ilFulltextWikiContentSearch($query_parser);
        } else {
            include_once 'Services/Search/classes/Like/class.ilLikeWikiContentSearch.php';
            return new ilLikeWikiContentSearch($query_parser);
        }
    }

    /**
     * get advanced meta data search instance
     *
     * @access public
     * @static
     *
     * @param object query parser
     */
    public static function _getAdvancedMDSearchInstance($query_parser)
    {
        include_once('Services/AdvancedMetaData/classes/class.ilAdvancedMDLikeSearch.php');
        return new ilAdvancedMDLikeSearch($query_parser);
    }
    
    /**
     * get orgunit search instance
     * @param type $query_parser
     */
    public static function getUserOrgUnitAssignmentInstance($query_parser)
    {
        include_once './Services/Search/classes/Like/class.ilLikeUserOrgUnitSearch.php';
        return new ilLikeUserOrgUnitSearch($query_parser);
    }
}
