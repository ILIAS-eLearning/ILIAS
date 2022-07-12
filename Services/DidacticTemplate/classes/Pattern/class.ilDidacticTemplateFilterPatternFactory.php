<?php declare(strict_types=1);
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Factory for didactic template filter patterns
 * @author  Stefan Meyer <meyer@leifos.com>
 * @ingroup ServicesDidacticTemplate
 */
class ilDidacticTemplateFilterPatternFactory
{
    public static function lookupPatternsByParentId(int $a_parent_id, string $a_parent_type) : array
    {
        global $DIC;

        $ilDB = $DIC->database();

        $query = 'SELECT pattern_id,pattern_type FROM didactic_tpl_fp ' .
            'WHERE parent_id = ' . $ilDB->quote($a_parent_id, ilDBConstants::T_INTEGER) . ' ' .
            'AND parent_type = ' . $ilDB->quote($a_parent_type, 'text');
        $res = $ilDB->query($query);

        $patterns = [];
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            switch ($row->pattern_type) {
                case ilDidacticTemplateFilterPattern::PATTERN_INCLUDE:

                    $patterns[] = new ilDidacticTemplateIncludeFilterPattern((int) $row->pattern_id);
                    break;

                case ilDidacticTemplateFilterPattern::PATTERN_EXCLUDE:

                    $patterns[] = new ilDidacticTemplateExcludeFilterPattern((int) $row->pattern_id);
                    break;
            }
        }

        return $patterns;
    }
}
