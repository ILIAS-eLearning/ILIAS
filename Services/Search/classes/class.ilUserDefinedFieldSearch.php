<?php

declare(strict_types=1);
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */


/**
* Class ilUserSearch
*
* @author Stefan Meyer <meyer@leifos.com>
*
* @ingroup ServicesSearch
*/
class ilUserDefinedFieldSearch extends ilAbstractSearch
{
    public function performSearch(): ilSearchResult
    {
        $where = $this->__createWhereCondition();
        $locate = $this->__createLocateString();

        $query = "SELECT usr_id  " .
            $locate .
            "FROM udf_text " .
            $where;
        $res = $this->db->query($query);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $this->search_result->addEntry($row->usr_id, 'usr', $this->__prepareFound($row));
        }
        return $this->search_result;
    }
}
