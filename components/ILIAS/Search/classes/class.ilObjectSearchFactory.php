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
