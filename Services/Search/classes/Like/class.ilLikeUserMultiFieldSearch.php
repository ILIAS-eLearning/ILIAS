<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once './Services/Search/classes/class.ilAbstractSearch.php';

/**
* Class ilLikeUserMultiFieldSearch
*
* Performs Mysql Like search in table usr_defined_data
*
* @author Stefan Meyer <meyer@leifos.com>
* @version $Id$
*
* @package ilias-search
*
*/
class ilLikeUserMultiFieldSearch extends ilAbstractSearch
{

    /**
    * Constructor
    * @access public
    */
    public function __construct($qp_obj)
    {
        parent::__construct($qp_obj);
    }
    
    /**
     * Perform search
     * @return type
     */
    public function performSearch()
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
    
    
    /**
     *
     * @param
     * @return
     */
    public function setFields($a_fields)
    {
        foreach ($a_fields as $field) {
            $fields[] = $field;
        }
        parent::setFields($fields ? $fields : array());
    }
    

    public function __createWhereCondition()
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        
        $fields = $this->getFields();
        $field = $fields[0];

        $and = "  WHERE field_id = " . $ilDB->quote($field, "text") . " AND ( ";
        $counter = 0;
        foreach ($this->query_parser->getQuotedWords() as $word) {
            if ($counter++) {
                $and .= " OR ";
            }

            if (strpos($word, '^') === 0) {
                $and .= $ilDB->like("value", "text", substr($word, 1) . "%");
            } else {
                $and .= $ilDB->like("value", "text", "%" . $word . "%");
            }
        }
        return $and . ") ";
    }
}
