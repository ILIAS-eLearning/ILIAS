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
