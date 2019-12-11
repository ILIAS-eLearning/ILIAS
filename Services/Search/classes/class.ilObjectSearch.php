<?php
/*
    +-----------------------------------------------------------------------------+
    | ILIAS open source                                                           |
    +-----------------------------------------------------------------------------+
    | Copyright (c) 1998-2001 ILIAS open source, University of Cologne            |
    |                                                                             |
    | This program is free software; you can redistribute it and/or               |
    | modify it under the terms of the GNU General Public License                 |
    | as published by the Free Software Foundation; either version 2              |
    | of the License, or (at your option) any later version.                      |
    |                                                                             |
    | This program is distributed in the hope that it will be useful,             |
    | but WITHOUT ANY WARRANTY; without even the implied warranty of              |
    | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the               |
    | GNU General Public License for more details.                                |
    |                                                                             |
    | You should have received a copy of the GNU General Public License           |
    | along with this program; if not, write to the Free Software                 |
    | Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA. |
    +-----------------------------------------------------------------------------+
*/

/**
* Class ilSearchGUI
*
* GUI class for 'simple' search
*
* @author Stefan Meyer <meyer@leifos.com>
* @version $Id$
*
* @package ilias-search
*
*/
include_once 'Services/Search/classes/class.ilAbstractSearch.php';

class ilObjectSearch extends ilAbstractSearch
{
    const CDATE_OPERATOR_BEFORE = 1;
    const CDATE_OPERATOR_AFTER = 2;
    const CDATE_OPERATOR_ON = 3;
    
    private $cdate_operator = null;
    private $cdate_date = null;
    

    /**
    * Constructor
    * @access public
    */
    public function __construct(&$qp_obj)
    {
        parent::__construct($qp_obj);

        $this->setFields(array('title','description'));
    }




    public function performSearch()
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        
        $in = $this->__createInStatement();
        $where = $this->__createWhereCondition();
        
        
        
        $cdate = '';
        if ($this->getCreationDateFilterDate() instanceof ilDate) {
            if ($this->getCreationDateFilterOperator()) {
                switch ($this->getCreationDateFilterOperator()) {
                    case self::CDATE_OPERATOR_AFTER:
                        $cdate = 'AND create_date >= ' . $ilDB->quote($this->getCreationDateFilterDate()->get(IL_CAL_DATE), 'text') . ' ';
                        break;
                    
                    case self::CDATE_OPERATOR_BEFORE:
                        $cdate = 'AND create_date <= ' . $ilDB->quote($this->getCreationDateFilterDate()->get(IL_CAL_DATE), 'text') . ' ';
                        break;
                    
                    case self::CDATE_OPERATOR_ON:
                        $cdate = 'AND ' . $ilDB->like(
                            'create_date',
                            'text',
                            $this->getCreationDateFilterDate()->get(IL_CAL_DATE) . '%'
                        );
                        break;
                }
            }
        }
        
        $locate = $this->__createLocateString();

        $query = "SELECT obj_id,type " .
            $locate .
            "FROM object_data " .
            $where . " " . $cdate . ' ' . $in . ' ' .
            "ORDER BY obj_id DESC";
        
        ilLoggerFactory::getLogger('src')->debug('Object search query: ' . $query);
        
        $res = $this->db->query($query);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $this->search_result->addEntry($row->obj_id, $row->type, $this->__prepareFound($row));
        }
        return $this->search_result;
    }



    // Protected can be overwritten in Like or Fulltext classes
    public function __createInStatement()
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        $in = ' AND ' . $ilDB->in('type', (array) $this->object_types, false, 'text');
        if ($this->getIdFilter()) {
            $in .= ' AND ';
            $in .= $ilDB->in('obj_id', $this->getIdFilter(), false, 'integer');
        }
        return $in;
    }
    
    
    /**
     * Set creation date filter
     */
    public function setCreationDateFilterDate(ilDate $day)
    {
        $this->cdate_date = $day;
    }
    
    public function setCreationDateFilterOperator($a_operator)
    {
        $this->cdate_operator = $a_operator;
    }
    
    public function getCreationDateFilterDate()
    {
        return $this->cdate_date;
    }
    
    public function getCreationDateFilterOperator()
    {
        return $this->cdate_operator;
    }
}
