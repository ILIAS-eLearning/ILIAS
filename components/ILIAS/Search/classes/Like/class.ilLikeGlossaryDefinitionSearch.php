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
* Class ilLikeGlossaryDefinitionSearch
*
* class for searching media pool folders and titles of mob's
*
* @author Stefan Meyer <meyer@leifos.com>
*
* @package ilias-search
*
*/
class ilLikeGlossaryDefinitionSearch extends ilGlossaryDefinitionSearch
{
    public function __createWhereCondition(): string
    {
        $concat = " term ";

        $and = "  WHERE ( ";
        $counter = 0;
        foreach ($this->query_parser->getQuotedWords() as $word) {
            if ($counter++) {
                $and .= " OR";
            }
            $and .= $this->db->like($concat, 'text', '%' . $word . '%');
            #$and .= $concat;
            #$and .= ("LIKE ('%".$word."%')");
        }
        return $and . ") ";
    }
}
