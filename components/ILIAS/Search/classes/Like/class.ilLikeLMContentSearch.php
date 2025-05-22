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
* Class ilLikeLMContentSearch
*
* class for searching media pool folders and titles of mob's
*
* @author Stefan Meyer <meyer@leifos.com>
*
* @package ilias-search
*
*/
class ilLikeLMContentSearch extends ilLMContentSearch
{
    public function __createWhereCondition(): string
    {
        $concat = " content ";

        $and = "  WHERE ( ";
        $counter = 0;
        foreach ($this->query_parser->getQuotedWords() as $word) {
            if ($counter++) {
                $and .= " OR";
            }
            #$and .= $concat;
            #$and .= ("LIKE ('%".$word."%')");
            $and .= $this->db->like($concat, 'clob', '%' . $word . '%');
        }
        return $and . ") ";
    }
}
