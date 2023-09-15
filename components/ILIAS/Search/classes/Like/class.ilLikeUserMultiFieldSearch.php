<?php

declare(strict_types=1);
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */


/**
* Class ilLikeUserMultiFieldSearch
*
* Performs Mysql Like search in table usr_defined_data
*
* @author Stefan Meyer <meyer@leifos.com>
*
* @package ilias-search
*
*/
class ilLikeUserMultiFieldSearch extends ilAbstractSearch
{
    public function performSearch(): ilSearchResult
    {
        $where = $this->__createWhereCondition();
        $locate = $this->__createLocateString();

        $query = "SELECT usr_id  " .
            $locate .
            "FROM usr_data_multi " .
            $where;

        $res = $this->db->query($query);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $this->search_result->addEntry($row->usr_id, 'usr', $this->__prepareFound($row));
        }
        return $this->search_result;
    }


    public function setFields(array $a_fields): void
    {
        $fields = [];
        foreach ($a_fields as $field) {
            $fields[] = $field;
        }
        parent::setFields($fields);
    }


    public function __createWhereCondition(): string
    {
        $fields = $this->getFields();
        $field = $fields[0];

        $and = "  WHERE field_id = " . $this->db->quote($field, "text") . " AND ( ";
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
