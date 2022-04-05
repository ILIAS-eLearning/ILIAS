<?php declare(strict_types=1);
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */


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
    public function setFields(array $a_fields) : void
    {
        $fields = [];
        foreach ($a_fields as $field) {
            $fields[] = 'f_' . $field;
        }
        parent::setFields($fields);
    }
    

    public function __createWhereCondition() : string
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
