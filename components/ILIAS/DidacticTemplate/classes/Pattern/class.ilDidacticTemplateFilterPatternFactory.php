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
 * Factory for didactic template filter patterns
 * @author  Stefan Meyer <meyer@leifos.com>
 * @ingroup ServicesDidacticTemplate
 */
class ilDidacticTemplateFilterPatternFactory
{
    public static function lookupPatternsByParentId(int $a_parent_id, string $a_parent_type): array
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
