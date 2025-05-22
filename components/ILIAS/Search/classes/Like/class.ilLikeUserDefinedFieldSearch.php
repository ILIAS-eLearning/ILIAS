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
* Class ilLikeUserDefinedFieldSearch
*
* Performs Mysql Like search in table usr_defined_data
*
* @author Stefan Meyer <meyer@leifos.com>
*
* @package ilias-search
*
*/
class ilLikeUserDefinedFieldSearch extends ilUserDefinedFieldSearch
{
    public function setFields(array $a_fields): void
    {
        $fields = [];
        foreach ($a_fields as $field) {
            $fields[] = 'f_' . $field;
        }
        parent::setFields($fields);
    }


    public function __createWhereCondition(): string
    {
        $fields = $this->getFields();
        $field = $fields[0];

        $and = "  WHERE field_id = " . $this->db->quote((int) substr($field, 2), "integer") . " AND ( ";
        $counter = 0;
        foreach ($this->query_parser->getQuotedWords() as $word) {
            if ($counter++) {
                $and .= " OR ";
            }

            if (strpos($word, '^') === 0) {
                $and .= $this->db->like("value", "text", substr($word, 1) . "%");
            } else {
                $and .= $this->db->like("value", "text", "%" . $word . "%");
            }
        }
        return $and . ") ";
    }
}
