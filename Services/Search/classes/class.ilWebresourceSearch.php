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
* Class ilWebresouceSearch
*
* Abstract class for glossary definitions. Should be inherited by ilFulltextGlossaryDefinitionSearch
*
* @author Stefan Meyer <meyer@leifos.com>
* @version $Id$
*
* @package ilias-search
*
*/
include_once 'Services/Search/classes/class.ilAbstractSearch.php';
 
class ilWebresourceSearch extends ilAbstractSearch
{
    public function performSearch()
    {
        $this->setFields(array('title'));

        $where = $this->__createWhereCondition();
        $locate = $this->__createLocateString();

        $query = "SELECT webr_id, link_id " .
            $locate .
            "FROM webr_items " .
            $where;

        $res = $this->db->query($query);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $this->search_result->addEntry($row->webr_id, 'webr', $this->__prepareFound($row), $row->link_id);
        }
        return $this->search_result;
    }

    public function __createAndCondition()
    {
        echo "Overwrite me!";
    }
}
