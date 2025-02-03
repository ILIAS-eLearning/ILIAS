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
* Class ilLikeObjectSearch
*
* Performs Mysql Like search in object_data title and description
*
* @author Stefan Meyer <meyer@leifos.com>
*
* @package ilias-search
*
*/
class ilLikeObjectSearch extends ilObjectSearch
{
    public function __createWhereCondition(): string
    {
        $concat = $this->db->concat(
            array(
                array('title','text'),
                array('description','text'))
        );
        $where = "WHERE (";
        $counter = 0;
        foreach ($this->query_parser->getQuotedWords() as $word) {
            if ($counter++) {
                $where .= "OR";
            }

            $where .= $this->db->like($concat, 'text', '%' . $word . '%');
        }
        $where .= ') ';
        return $where;
    }
}
