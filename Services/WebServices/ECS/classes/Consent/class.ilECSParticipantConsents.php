<?php

class ilECSParticipantConsents
{
    private $server_id;
    private $mid;

    protected $db;

    public function __construct(int $server_id, int $mid)
    {
        global $DIC;

        $this->server_id = $server_id;
        $this->mid = $mid;

        $this->db = $DIC->database();
    }

    public function delete() : void
    {
        $query = 'DELETE FROM ecs_user_consent ' .
            'WHERE mid = ' . $this->db->quote($this->mid, ilDBConstants::T_INTEGER);
        $this->db->manipulate($query);
    }


    public function hasConsents() : bool
    {
        $query = 'SELECT count(*) as num FROM ecs_user_consent ' .
            'WHERE mid = ' . $this->db->quote($this->mid, ilDBConstants::T_INTEGER);
        $res = $this->db->query($query);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            return (bool) $row->num;
        }
        return false;
    }

}