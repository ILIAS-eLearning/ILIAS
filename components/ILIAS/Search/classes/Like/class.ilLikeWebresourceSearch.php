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
* Class webresource search
*
* Performs Mysql Like search in object_data title and description
*
* @author Stefan Meyer <smeyer.ilias@gmx.de>
*
*
*/
class ilLikeWebresourceSearch extends ilWebresourceSearch
{
    public function __createWhereCondition(): string
    {
        $concat = ' title ';

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
