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
 * Description of class class
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 *
 */
class ilLikeUserOrgUnitSearch extends ilAbstractSearch
{
    public const ORG_ASSIGNMENTS_TABLE = 'orgu_user_assignements';

    private ilObjOrgUnitTree $org_tree;



    public function __construct($qp_obj)
    {
        parent::__construct($qp_obj);

        $this->org_tree = ilObjOrgUnitTree::_getInstance();
        $this->org_tree->buildTempTableWithUsrAssignements(self::ORG_ASSIGNMENTS_TABLE);
    }


    public function performSearch(): ilSearchResult
    {
        $where = $this->__createWhereCondition();
        $locate = $this->__createLocateString();

        $query = 'SELECT user_id  ' .
            'FROM  ' . self::ORG_ASSIGNMENTS_TABLE . ' ' .
            $where;

        $res = $this->db->query($query);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $this->search_result->addEntry((int) $row->user_id, 'user', $this->__prepareFound($row));
        }
        return $this->search_result;
    }


    public function __createWhereCondition(): string
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
