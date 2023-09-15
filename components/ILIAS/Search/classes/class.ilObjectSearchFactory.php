<?php

declare(strict_types=1);
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
 * Class ilObjectSearchFactory
 *
 * Factory for ObjectSearch classes
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 *
 * @package ServicesSearch
 */
class ilObjectSearchFactory
{
    public static function _getObjectSearchInstance(ilQueryParser $query_parser): ilObjectSearch
    {
        return new ilLikeObjectSearch($query_parser);
    }

    public static function getByTypeSearchInstance(
        string $a_object_type,
        ilQueryParser $a_query_parser
    ): ilAbstractSearch {
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

    public static function _getMetaDataSearchInstance(ilQueryParser $query_parser): ilMetaDataSearch
    {
        return new ilLikeMetaDataSearch($query_parser);
    }

    public static function _getLMContentSearchInstance(ilQueryParser $query_parser): ilLMContentSearch
    {
        return new ilLikeLMContentSearch($query_parser);
    }

    public static function _getForumSearchInstance(ilQueryParser $query_parser): ilForumSearch
    {
        return new ilLikeForumSearch($query_parser);
    }

    public static function _getGlossaryDefinitionSearchInstance(
        ilQueryParser $query_parser
    ): ilGlossaryDefinitionSearch {
        return new ilLikeGlossaryDefinitionSearch($query_parser);
    }

    public static function _getExerciseSearchInstance(ilQueryParser $query_parser): ilExerciseSearch
    {
        return new ilLikeExerciseSearch($query_parser);
    }

    public static function _getMediacastSearchInstance(ilQueryParser $query_parser): ilMediaCastSearch
    {
        return new ilLikeMediaCastSearch($query_parser);
    }

    public static function _getTestSearchInstance(ilQueryParser $query_parser): ilTestSearch
    {
        return new ilLikeTestSearch($query_parser);
    }

    public static function _getMediaPoolSearchInstance(ilQueryParser $query_parser): ilMediaPoolSearch
    {
        return new ilLikeMediaPoolSearch($query_parser);
    }

    public static function _getAdvancedSearchInstance(ilQueryParser $query_parser): ilAdvancedSearch
    {
        return new ilLikeAdvancedSearch($query_parser);
    }

    public static function _getWebresourceSearchInstance(ilQueryParser $query_parser): ilWebresourceSearch
    {
        return new ilLikeWebresourceSearch($query_parser);
    }

    public static function _getUserSearchInstance(ilQueryParser $query_parser): ilUserSearch
    {
        return new ilLikeUserSearch($query_parser);
    }

    public static function _getUserDefinedFieldSearchInstance(
        ilQueryParser $query_parser
    ): ilUserDefinedFieldSearch {
        return new ilLikeUserDefinedFieldSearch($query_parser);
    }

    public static function getUserMultiFieldSearchInstance(ilQueryParser $query_parser): ilAbstractSearch
    {
        return new ilLikeUserMultiFieldSearch($query_parser);
    }

    public static function _getWikiContentSearchInstance(ilQueryParser $query_parser): ilWikiContentSearch
    {
        return new ilLikeWikiContentSearch($query_parser);
    }

    public static function _getAdvancedMDSearchInstance(ilQueryParser $query_parser): ilAdvancedMDSearch
    {
        return new ilAdvancedMDLikeSearch($query_parser);
    }

    public static function getUserOrgUnitAssignmentInstance(ilQueryParser $query_parser): ilAbstractSearch
    {
        return new ilLikeUserOrgUnitSearch($query_parser);
    }
}
