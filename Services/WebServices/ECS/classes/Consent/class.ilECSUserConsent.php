<?php declare(strict_types=1);

/**
 * Class ilECSUserConsent
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 */
class ilECSUserConsent
{
    private $usr_id = 0;
    private $mid = 0;

    protected $db;

    public function __construct(int $a_usr_id, int $a_mid)
    {
        global $DIC;

        $this->usr_id = $a_usr_id;
        $this->mid = $a_mid;

        $this->db = $DIC->database();
    }

    public function getUserId() : int
    {
        return $this->usr_id;
    }

    public function getMid() : int
    {
        return $this->mid;
    }


    public function save() : void
    {
        try {
            $query = 'INSERT INTO ecs_user_consent (usr_id, mid) ' .
                'VALUES ( ' .
                $this->db->quote($this->getUserId(), ilDBConstants::T_INTEGER) . ', ' .
                $this->db->quote($this->getMid(), ilDBConstants::T_INTEGER) . ' ' .
                ')';
            $this->db->manipulate($query);
        } catch (ilDatabaseException $e) {
            // ignore duplicate entry
        }
    }

    public function delete() : void
    {
        $query = 'DELETE FROM ecs_user_consent ' .
            'WHERE usr_id = ' . $this->db->quote($this->getUserId(), ilDBConstants::T_INTEGER) . ' ' .
            'AND mid = ' . $this->db->quote($this->getMid(), ilDBConstants::T_INTEGER);
        $this->db->manipulate($query);
    }
}