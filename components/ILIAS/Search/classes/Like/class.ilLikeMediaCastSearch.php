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
* Class ilLikeMediaCastSearch
*
* Performs Mysql Like search
*
* @author Alex Killing <alex.killing@gmx.de>
*
*
*/
class ilLikeMediaCastSearch extends ilMediaCastSearch
{
    public function __createWhereCondition(): string
    {
        $and = "  WHERE context_obj_type='mcst' AND (  ";
        $counter = 0;
        foreach ($this->query_parser->getQuotedWords() as $word) {
            if ($counter++) {
                $and .= " OR ";
            }
            #$and .= $concat;
            #$and .= ("LIKE ('%".$word."%')");
            $and .= $this->db->like('title', 'text', '%' . $word . '%');
            $and .= ' OR ';
            $and .= $this->db->like('content', 'clob', '%' . $word . '%');
        }
        return $and . ") ";
    }
}
