<?php

declare(strict_types=1);
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
* Class ilUserSearch
*
* @author Stefan Meyer <meyer@leifos.com>
* @version $Id$
*
* @package ilias-search
*
*/

class ilUserSearch extends ilAbstractSearch
{
    private bool $active_check = false;
    private bool $inactive_check = false;

    public function enableActiveCheck(bool $a_enabled): void
    {
        $this->active_check = $a_enabled;
    }

    public function enableInactiveCheck(bool $a_enabled): void
    {
        $this->inactive_check = $a_enabled;
    }

    public function performSearch(): ilSearchResult
    {
        $where = $this->__createWhereCondition();
        $locate = $this->__createLocateString();

        $query = "SELECT usr_id  " .
            $locate .
            "FROM usr_data " .
            $where;
        if ($this->active_check) {
            $query .= 'AND active = 1 ';
        } elseif ($this->inactive_check) {
            $query .= 'AND active = 0 ';
        }


        $res = $this->db->query($query);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $this->search_result->addEntry((int) $row->usr_id, 'usr', $this->__prepareFound($row));
        }
        return $this->search_result;
    }
}
