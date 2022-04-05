<?php declare(strict_types=1);

/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

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
    
    
    public function performSearch() : ilSearchResult
    {
        $where = $this->__createWhereCondition();
        $locate = $this->__createLocateString();

        $query = 'SELECT user_id  ' .
            'FROM  ' . self::ORG_ASSIGNMENTS_TABLE . ' ' .
            $where;
        
        $res = $this->db->query($query);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $this->search_result->addEntry($row->user_id, 'user', $this->__prepareFound($row));
        }
        return $this->search_result;
    }
    

    public function __createWhereCondition() : string
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
