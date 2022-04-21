<?php declare(strict_types=1);

/**
 * Class ilECSUserConsents
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 */
class ilECSUserConsents
{
    private static $instances = [];

    private $usr_id = 0;
    private $consents = [];

    protected $db;

    protected function __construct(int $a_usr_id)
    {
        global $DIC;

        $this->db = $DIC->database();
        $this->usr_id = $a_usr_id;

        $this->read();
    }

    public static function getInstanceByUserId(int $a_usr_id) : self
    {
        if (!isset(self::$instances[$a_usr_id])) {
            self::$instances[$a_usr_id] = new self($a_usr_id);
        }
        return self::$instances[$a_usr_id];
    }

    public function getUserId() : int
    {
        return $this->usr_id;
    }

    public function hasConsented(int $a_mid) : bool
    {
        return array_key_exists($a_mid, $this->consents);
    }

    public function delete()
    {
        foreach ($this->consents as $mid => $consent) {
            $consent->delete();
        }
    }

    public function add(int $a_mid)
    {
        if (!$this->hasConsented($a_mid)) {
            $consent = new ilECSUserConsent($this->getUserId(), $a_mid);
            $consent->save();
            $this->consents[$a_mid] = $consent;
        }
    }

    protected function read() : void
    {
        $query = 'SELECT * FROM ecs_user_consent ' .
            'WHERE usr_id = ' . $this->db->quote($this->getUserId(), ilDBConstants::T_INTEGER);
        $res = $this->db->query($query);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $this->consents[(int) $row->mid] = new ilECSUserConsent((int) $row->usr_id, (int) $row->mid);
        }
    }
}