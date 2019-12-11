<?php

/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once './Services/Search/classes/class.ilAbstractSearch.php';
include_once './Modules/OrgUnit/classes/class.ilObjOrgUnitTree.php';
/**
 * Description of class class
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 *
 */
class ilLikeUserOrgUnitSearch extends ilAbstractSearch
{
    const ORG_ASSIGNMENTS_TABLE = 'orgu_user_assignments';
    
    /**
     * @var ilObjOrgUnitTree
     */
    private $org_tree = null;
    
    
    
    public function __construct($qp_obj)
    {
        parent::__construct($qp_obj);
        
        $this->org_tree = ilObjOrgUnitTree::_getInstance();
        $this->org_tree->buildTempTableWithUsrAssignements(self::ORG_ASSIGNMENTS_TABLE);
    }
    
    
    public function performSearch()
    {
        $where = $this->__createWhereCondition();
        $locate = $this->__createLocateString();

        $query = 'SELECT user_id  ' .
            'FROM  ' . self::ORG_ASSIGNMENTS_TABLE . ' ' .
            $where;
        
        $GLOBALS['DIC']->logger()->src()->debug($query);
        
        $res = $this->db->query($query);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $this->search_result->addEntry($row->user_id, 'user', $this->__prepareFound($row));
        }
        
        return $this->search_result;
    }
    
    
    /**
     * Create where condition
     * @global type $ilDB
     * @return type
     */
    public function __createWhereCondition()
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        
        $where = 'WHERE ';
        $counter = 0;
        foreach ($this->query_parser->getQuotedWords() as $word) {
            if ($counter++) {
                $and .= " OR ";
            }
            $where .= ('ref_id = ' . $ilDB->quote($word, 'integer'));
        }
        return $where;
    }
}
