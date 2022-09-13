<?php

declare(strict_types=1);

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


/**
 * Class class.ilregistrationEmailRoleAssignments
 * @author  Stefan Meyer <meyer@leifos.com>
 */
class ilRegistrationRoleAssignments
{
    public const IL_REG_MISSING_DOMAIN = 1;
    public const IL_REG_MISSING_ROLE = 2;

    public array $assignments = [];
    public int $default_role = 0;

    protected ilDBInterface $db;
    protected ilSetting $settings;

    public function __construct()
    {
        global $DIC;

        $this->db = $DIC->database();
        $this->settings = $DIC->settings();
        $this->read();
    }

    public function getRoleByEmail(string $a_email): int
    {
        foreach ($this->assignments as $assignment) {
            if (!$assignment['domain'] || !$assignment['role']) {
                continue;
            }
            if (stripos($a_email, $assignment['domain']) !== false) {
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

    public function getDomainsByRole(int $role_id): array
    {
        $query = $this->db->query("SELECT * FROM reg_er_assignments " .
            "WHERE role = " . $this->db->quote($role_id, 'integer'));

        $res = [];
        while ($row = $this->db->fetchAssoc($query)) {
            $res[] = $row["domain"];
        }
        return $res;
    }

    public function getAssignments(): array
    {
        return $this->assignments;
    }

    public function setDomain(int $id, string $a_domain): void
    {
        $this->assignments[$id]['domain'] = $a_domain;
    }

    public function setRole(int $id, int $a_role): void
    {
        $this->assignments[$id]['role'] = $a_role;
    }

    public function getDefaultRole(): int
    {
        return $this->default_role;
    }

    public function setDefaultRole(int $a_role_id): void
    {
        $this->default_role = $a_role_id;
    }

    public function deleteAll(): bool
    {
        $query = "DELETE FROM reg_er_assignments ";
        $this->db->manipulate($query);
        $this->read();
        return true;
    }

    public function save(): bool
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
        $this->read();
        return true;
    }

    public function read(): bool
    {
        $query = "SELECT * FROM reg_er_assignments ";
        $res = $this->db->query($query);

        $this->assignments = [];
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $this->assignments[$row->assignment_id]['id'] = $row->assignment_id;
            $this->assignments[$row->assignment_id]['role'] = $row->role;
            $this->assignments[$row->assignment_id]['domain'] = $row->domain;
        }
        $this->default_role = (int) $this->settings->get('reg_default_role', '0');
        return true;
    }
}
