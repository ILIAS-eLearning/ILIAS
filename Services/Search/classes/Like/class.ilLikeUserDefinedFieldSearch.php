<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once 'Services/Search/classes/class.ilUserDefinedFieldSearch.php';

/**
* Class ilLikeUserDefinedFieldSearch
*
* Performs Mysql Like search in table usr_defined_data
*
* @author Stefan Meyer <meyer@leifos.com>
* @version $Id$
*
* @package ilias-search
*
*/
class ilLikeUserDefinedFieldSearch extends ilUserDefinedFieldSearch
{
    
    /**
     *
     * @param
     * @return
     */
    public function setFields($a_fields)
    {
        foreach ($a_fields as $field) {
            $fields[] = 'f_' . $field;
        }
        parent::setFields($fields ? $fields : array());
    }
    

    public function __createWhereCondition()
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        
        $fields = $this->getFields();
        $field = $fields[0];

        $and = "  WHERE field_id = " . $ilDB->quote((int) substr($field, 2), "integer") . " AND ( ";
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
