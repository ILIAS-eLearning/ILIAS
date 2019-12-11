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
* Class class.ilregistrationEmailRoleAssignments
*
* @author Stefan Meyer <meyer@leifos.com>
* @version $Id$
*
* @ingroup ServicesRegistration
*/

define('IL_REG_MISSING_DOMAIN', 1);
define('IL_REG_MISSING_ROLE', 2);

class ilRegistrationRoleAssignments
{
    public $assignments = array();
    public $default_role = 0;

    public function __construct()
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        $this->db = $ilDB;
        $this->__read();
    }

    public function getRoleByEmail($a_email)
    {
        global $DIC;

        $ilObjDataCache = $DIC['ilObjDataCache'];

        foreach ($this->assignments as $assignment) {
            if (!$assignment['domain'] or !$assignment['role']) {
                continue;
            }
            if (stristr($a_email, $assignment['domain'])) {
                // check if role exists
                if (!$ilObjDataCache->lookupType($assignment['role'])) {
                    continue;
                }
                return $assignment['role'];
            }
        }
        // return default
        return $this->getDefaultRole();
    }
    
    public function getAssignments()
    {
        return $this->assignments ? $this->assignments : array();
    }

    public function setDomain($a_id, $a_domain)
    {
        $this->assignments[$a_id]['domain'] = $a_domain;
    }
    public function setRole($a_id, $a_role)
    {
        $this->assignments[$a_id]['role'] = $a_role;
    }

    public function getDefaultRole()
    {
        return $this->default_role;
    }
    public function setDefaultRole($a_role_id)
    {
        $this->default_role = $a_role_id;
    }

    public function delete($a_id)
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        
        $query = "DELETE FROM reg_er_assignments " .
            "WHERE assignment_id = " . $ilDB->quote($a_id, 'integer');
        $res = $ilDB->manipulate($query);
        $this->__read();
        return true;
    }

    public function add()
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        $next_id = $ilDB->nextId('reg_er_assignments');
        $query = "INSERT INTO reg_er_assignments (assignment_id,domain,role) " .
            "VALUES( " .
            $ilDB->quote($next_id, 'integer') . ', ' .
            $ilDB->quote('', 'text') . ", " .
            $ilDB->quote(0, 'integer') .
            ")";
        $res = $ilDB->manipulate($query);
        $this->__read();
        return true;
    }

    public function save()
    {
        global $DIC;

        $ilias = $DIC['ilias'];
        $ilDB = $DIC['ilDB'];

        // Save default role
        $ilias->setSetting('reg_default_role', $this->getDefaultRole());

        foreach ($this->assignments as $assignment) {
            $query = "UPDATE reg_er_assignments " .
                "SET domain = " . $ilDB->quote($assignment['domain'], 'text') . ", " .
                "role = " . $ilDB->quote($assignment['role'], 'integer') . " " .
                "WHERE assignment_id = " . $ilDB->quote($assignment['id'], 'integer');
            $res = $ilDB->manipulate($query);
        }
        return true;
    }

    public function validate()
    {
        foreach ($this->assignments as $assignment) {
            if (!strlen($assignment['domain'])) {
                return IL_REG_MISSING_DOMAIN;
            }
            if (!$assignment['role']) {
                return IL_REG_MISSING_ROLE;
            }
        }
        if (!$this->getDefaultRole()) {
            return IL_REG_MISSING_ROLE;
        }
        return 0;
    }
            
    


    // Private
    public function __read()
    {
        global $DIC;

        $ilias = $DIC['ilias'];
        $ilDB = $DIC['ilDB'];

        $query = "SELECT * FROM reg_er_assignments ";
        $res = $this->db->query($query);

        $this->assignments = array();
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $this->assignments[$row->assignment_id]['id'] =  $row->assignment_id;
            $this->assignments[$row->assignment_id]['role'] = $row->role;
            $this->assignments[$row->assignment_id]['domain'] = $row->domain;
        }

        $this->default_role = $ilias->getSetting('reg_default_role');

        return true;
    }
}
