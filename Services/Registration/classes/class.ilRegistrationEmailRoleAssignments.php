<?php declare(strict_types=1);
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
 * Class class.ilregistrationEmailRoleAssignments
 * @author  Stefan Meyer <meyer@leifos.com>
 * @ingroup ServicesRegistration
 */
class ilRegistrationRoleAssignments
{
    public const IL_REG_MISSING_DOMAIN = 1;
    public const IL_REG_MISSING_ROLE = 2;

    public array $assignments = array();
    public int $default_role = 0;

    protected ilDBInterface $db;
    protected ilSetting $settings;

    public function __construct()
    {
        global $DIC;

        $this->db = $DIC->database();
        $this->settings = $DIC->settings();
        $this->__read();
    }

    public function getRoleByEmail(string $a_email) : int
    {
        foreach ($this->assignments as $assignment) {
            if (!$assignment['domain'] or !$assignment['role']) {
                continue;
            }
            if (stristr($a_email, $assignment['domain'])) {
                // check if role exists
                if (!ilObject::_lookupType($assignment['role'])) {
                    continue;
                }
                return (int) $assignment['role'];
            }
        }
        // return default
        return $this->getDefaultRole();
    }

    public function getDomainsByRole(int $role_id) : array
    {
        $query = $this->db->query("SELECT * FROM reg_er_assignments " .
            "WHERE role = " . $this->db->quote($role_id, 'integer'));

        $res = [];
        while ($row = $this->db->fetchAssoc($query)) {
            $res[] = $row["domain"];
        }
        return $res;
    }

    public function getAssignments() : array
    {
        return $this->assignments;
    }

    public function setDomain(int $id, string $a_domain) : void
    {
        $this->assignments[$id]['domain'] = $a_domain;
    }

    public function setRole(int $id, int $a_role) : void
    {
        $this->assignments[$id]['role'] = $a_role;
    }

    public function getDefaultRole() : int
    {
        return $this->default_role;
    }

    public function setDefaultRole(int $a_role_id) : void
    {
        $this->default_role = $a_role_id;
    }

    public function deleteAll() : bool
    {
        $query = "DELETE FROM reg_er_assignments ";
        $this->db->manipulate($query);
        $this->__read();
        return true;
    }

    public function save() : bool
    {
        // Save default role
        $this->settings->set('reg_default_role', (string) $this->getDefaultRole());

        foreach ($this->assignments as $assignment) {
            if (empty($assignment['id'])) {
                $next_id = $this->db->nextId('reg_er_assignments');
                $query = "INSERT INTO reg_er_assignments (assignment_id,domain,role) " .
                    "VALUES( " .
                    $this->db->quote($next_id, 'integer') . ', ' .
                    $this->db->quote($assignment['domain'], 'text') . ", " .
                    $this->db->quote($assignment['role'], 'integer') .
                    ")";
                $res = $this->db->manipulate($query);
            }
        }
        $this->__read();
        return true;
    }

    public function __read() : bool
    {
        $query = "SELECT * FROM reg_er_assignments ";
        $res = $this->db->query($query);

        $this->assignments = array();
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $this->assignments[$row->assignment_id]['id'] = $row->assignment_id;
            $this->assignments[$row->assignment_id]['role'] = $row->role;
            $this->assignments[$row->assignment_id]['domain'] = $row->domain;
        }
        $this->default_role = (int) $this->settings->get('reg_default_role', '0');
        return true;
    }
}
