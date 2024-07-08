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
 ********************************************************************
 */

declare(strict_types=1);

/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Description of class class
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 *
 */
class ilLikeUserOrgUnitSearch extends ilAbstractSearch
{
    public function __construct($qp_obj)
    {
        parent::__construct($qp_obj);
    }

    public function performSearch(): ilSearchResult
    {
        $query = 'SELECT user_id FROM '
            . "(SELECT DISTINCT object_reference.ref_id AS ref_id, il_orgu_ua.user_id AS user_id, orgu_path_storage.path AS path
                    FROM il_orgu_ua
                    JOIN object_reference ON object_reference.ref_id = il_orgu_ua.orgu_id
                    JOIN object_data ON object_data.obj_id = object_reference.obj_id
                    JOIN orgu_path_storage ON orgu_path_storage.ref_id = object_reference.ref_id
                WHERE object_data.type = 'orgu' AND object_reference.deleted IS NULL) as TEMPTABLE "
            . $this->createWhereCondition();

        $res = $this->db->query($query);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $this->search_result->addEntry($row->user_id, 'user', $this->__prepareFound($row));
        }
        return $this->search_result;
    }

    protected function createWhereCondition(): string
    {
        $and = '';
        $where = 'WHERE ';
        $counter = 0;
        foreach ($this->query_parser->getQuotedWords() as $word) {
            if ($counter++) {
                $and .= " OR ";
            }
            $where .= ('ref_id = ' . $this->db->quote($word, 'integer'));
        }
        return $where;
    }
}
