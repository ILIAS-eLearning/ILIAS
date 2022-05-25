<?php declare(strict_types=1);

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

class ilECSParticipantConsents
{
    private int $server_id;
    private int $mid;

    protected ilDBInterface $db;

    public function __construct(int $server_id, int $mid)
    {
        global $DIC;

        $this->db = $DIC->database();

        $this->server_id = $server_id;
        $this->mid = $mid;
    }

    public function delete() : void
    {
        $query = 'DELETE FROM ecs_user_consent ' .
            'WHERE mid = ' . $this->db->quote($this->mid, ilDBConstants::T_INTEGER) . ' ' .
            'AND server_id = ' . $this->db->quote($this->server_id, ilDBConstants::T_INTEGER);
        $this->db->manipulate($query);
    }

    public function hasConsents() : bool
    {
        $query = 'SELECT count(*) as num FROM ecs_user_consent ' .
            'WHERE mid = ' . $this->db->quote($this->mid, ilDBConstants::T_INTEGER) . ' ' .
            'AND server_id = ' . $this->db->quote($this->server_id, ilDBConstants::T_INTEGER);
        $res = $this->db->query($query);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            return (bool) $row->num;
        }
        return false;
    }
}
