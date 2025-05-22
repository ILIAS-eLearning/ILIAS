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
* Class ilLikeMediaPoolSearch
*
* class for searching media pool folders and titles of mob's
*
* @author Stefan Meyer <meyer@leifos.com>
*
* @package ilias-search
*
*/
class ilLikeMediaPoolSearch extends ilMediaPoolSearch
{
    public function __createAndCondition(): string
    {
        $concat = $this->db->concat(
            array(
                array('title','text')
                )
        );


        $and = "  AND ( ";
        $counter = 0;
        foreach ($this->query_parser->getQuotedWords() as $word) {
            if ($counter++) {
                $and .= " OR";
            }
            #$and .= $concat;
            #$and .= ("LIKE ('%".$word."%')");
            #$and .= $ilDB->like($concat,'text','%'.$word.'%');
            $and .= $this->db->like('title', 'text', '%' . $word . '%');
        }
        return $and . ") ";
    }

    /**
     * Condition for mob keyword search
     * @return string
     */
    public function __createKeywordAndCondition(): string
    {
        $concat = ' keyword ';

        $and = "  WHERE  ";
        $counter = 0;
        foreach ($this->query_parser->getQuotedWords() as $word) {
            if ($counter++) {
                $and .= " OR ";
            }
            $and .= $this->db->like($concat, 'text', '%' . $word . '%');
        }
        return $and;
    }
}
