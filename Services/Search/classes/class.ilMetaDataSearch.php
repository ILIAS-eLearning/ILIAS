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
* Class ilAdvancedSearch
*
* Base class for advanced meta search
*
* @author Stefan Meyer <meyer@leifos.com>
* @version $Id
*
* @package ilias-search
*
*/
include_once 'Services/Search/classes/class.ilAbstractSearch.php';

class ilMetaDataSearch extends ilAbstractSearch
{
    public $mode = '';

    /*
     * instance of query parser
     */
    public $query_parser = null;

    public $db = null;

    /**
    * Define meta elements to search
    *
    * @param array elements to search in. E.G array('keyword','contribute')
    * @access public
    */
    public function setMode($a_mode)
    {
        $this->mode = $a_mode;
    }
    public function getMode()
    {
        return $this->mode;
    }


    public function performSearch()
    {
        switch ($this->getMode()) {
            case 'keyword':
                return $this->__searchKeywords();

            case 'contribute':
                return $this->__searchContribute();

            case 'title':
                return $this->__searchTitles();

            case 'description':
                return $this->__searchDescriptions();

            default:
                echo "ilMDSearch::performSearch() no mode given";
                return false;
        }
    }



    // Private
    public function __createInStatement()
    {
        if (!$this->getFilter()) {
            return '';
        } else {
            $type = "('";
            $type .= implode("','", $this->getFilter());
            $type .= "')";
            
            $in = " AND obj_type IN " . $type;

            return $in;
        }
    }
    public function __searchContribute()
    {
        $this->setFields(array('entity'));

        $in = $this->__createInStatement();
        $where = $this->__createContributeWhereCondition();
        $locate = $this->__createLocateString();

        $query = "SELECT rbac_id,obj_id,obj_type " .
            $locate .
            "FROM il_meta_entity " .
            $where . " " . $in . ' ';

        $res = $this->db->query($query);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $this->search_result->addEntry($row->rbac_id, $row->obj_type, $this->__prepareFound($row), $row->obj_id);
        }

        return $this->search_result;
    }


    public function __searchKeywords()
    {
        $this->setFields(array('keyword'));

        $in = $this->__createInStatement();
        $where = $this->__createKeywordWhereCondition();
        $locate = $this->__createLocateString();

        $query = "SELECT rbac_id,obj_id,obj_type " .
            $locate .
            "FROM il_meta_keyword " .
            $where . " " . $in . ' ';

        $res = $this->db->query($query);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $this->search_result->addEntry($row->rbac_id, $row->obj_type, $this->__prepareFound($row), $row->obj_id);
        }

        return $this->search_result;
    }
    public function __searchTitles()
    {
        $this->setFields(array('title'));

        $in = $this->__createInStatement();
        $where = $this->__createTitleWhereCondition();
        $locate = $this->__createLocateString();

        $query = "SELECT rbac_id,obj_id,obj_type " .
            $locate .
            "FROM il_meta_general " .
            $where . " " . $in . ' ';

        $res = $this->db->query($query);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $this->search_result->addEntry($row->rbac_id, $row->obj_type, $this->__prepareFound($row), $row->obj_id);
        }

        return $this->search_result;
    }
    public function __searchDescriptions()
    {
        $this->setFields(array('description'));

        $in = $this->__createInStatement();
        $where = $this->__createDescriptionWhereCondition();
        $locate = $this->__createLocateString();

        $query = "SELECT rbac_id,obj_id,obj_type " .
            $locate .
            "FROM il_meta_description " .
            $where . " " . $in . ' ';

        $res = $this->db->query($query);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $this->search_result->addEntry($row->rbac_id, $row->obj_type, $this->__prepareFound($row), $row->obj_id);
        }

        return $this->search_result;
    }
}
