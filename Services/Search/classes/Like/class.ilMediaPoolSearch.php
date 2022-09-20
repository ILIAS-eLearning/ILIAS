<?php

declare(strict_types=1);
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
* Class ilMediaPoolSearch
*
* Abstract class for test search.
*
* @author Stefan Meyer <meyer@leifos.com>
*
* @package ilias-search
*
*/

class ilMediaPoolSearch extends ilAbstractSearch
{
    public function performSearch(): ilSearchResult
    {
        $this->setFields(array('title'));

        $and = $this->__createAndCondition();
        $locate = $this->__createLocateString();

        $query = "SELECT mep_id,obj_id " .
            $locate .
            "FROM mep_tree JOIN mep_item ON child = obj_id " .
            $and;

        $res = $this->db->query($query);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $this->search_result->addEntry(
                (int) $row->mep_id,
                'mep',
                $this->__prepareFound($row),
                (int) $row->obj_id
            );
        }
        return $this->search_result;
    }

    public function performKeywordSearch(): ?ilSearchResult
    {
        $this->setFields(array('keyword'));

        $and = $this->__createKeywordAndCondition();
        $locate = $this->__createLocateString();


        $query = "SELECT mep_id, child " .
            $locate .
            "FROM mep_item mi " .
            "JOIN mep_tree ON mi.obj_id = child " .
            "JOIN il_meta_keyword mk ON foreign_id = mk.obj_id " .
            $and .
            "AND obj_type = 'mob'";

        $res = $this->db->query($query);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $this->search_result->addEntry(
                (int) $row->mep_id,
                'mep',
                $this->__prepareFound($row),
                (int) $row->child
            );
        }
        return $this->search_result;
    }
}
