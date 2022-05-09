<?php declare(strict_types=1);

/******************************************************************************
 *
 * This file is part of ILIAS, a powerful learning management system.
 *
 * ILIAS is licensed with the GPL-3.0, you should have received a copy
 * of said license along with the source code.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 *      https://www.ilias.de
 *      https://github.com/ILIAS-eLearning
 *
 *****************************************************************************/

/**
* @author Stefan Meyer <meyer@leifos.com>
*/
class ilECSCommunityCache
{
    /** @var array<int, array<int, self>>  */
    protected static array $instance = [];

    protected int $server_id = 0;
    protected int $community_id = 0;
    protected int $own_id = 0;
    protected string $cname = '';
    protected array $mids = array();

    protected bool $entryExists = false;
    
    private ilDBInterface $db;

    /**
     * Singleton constructor
     * @param int $server_id
     * @param int $community_id
     */
    protected function __construct(int $server_id, int $community_id)
    {
        global $DIC;

        $this->db = $DIC->database();

        $this->server_id = $server_id;
        $this->community_id = $community_id;

        $this->read();
    }

    /**
     * Get instance
     * @param int $a_server_id
     * @param int $a_community_id
     * @return ilECSCommunityCache
     */
    public static function getInstance(int $a_server_id, int $a_community_id) : ilECSCommunityCache
    {
        return self::$instance[$a_server_id][$a_community_id] ??
            (self::$instance[$a_server_id][$a_community_id] = new ilECSCommunityCache(
                $a_server_id,
                $a_community_id
            ));
    }



    public function getServerId() : int
    {
        return $this->server_id;
    }

    public function getCommunityId() : int
    {
        return $this->community_id;
    }

    public function setOwnId(int $a_id) : void
    {
        $this->own_id = $a_id;
    }

    public function getOwnId() : int
    {
        return $this->own_id;
    }

    public function setCommunityName(string $a_name) : void
    {
        $this->cname = $a_name;
    }

    public function getCommunityName() : string
    {
        return $this->cname;
    }

    public function setMids(array $a_mids) : void
    {
        $this->mids = $a_mids;
    }

    public function getMids() : array
    {
        return $this->mids;
    }

    /**
     * Create or update ecs community
     */
    public function update() : bool
    {
        if (!$this->entryExists) {
            return $this->create();
        }

        $query = 'UPDATE ecs_community ' .
            'SET own_id = ' . $this->db->quote($this->getOwnId(), 'integer') . ', ' .
            'cname = ' . $this->db->quote($this->getCommunityName(), 'text') . ', ' .
            'mids = ' . $this->db->quote(serialize($this->getMids()), 'text') . ' ' .
            'WHERE sid = ' . $this->db->quote($this->getServerId(), 'integer') . ' ' .
            'AND cid = ' . $this->db->quote($this->getCommunityId(), 'integer');
        $this->db->manipulate($query);
        return true;
    }



    /**
     * Create new dataset
     */
    protected function create() : bool
    {
        $query = 'INSERT INTO ecs_community (sid,cid,own_id,cname,mids) ' .
            'VALUES( ' .
            $this->db->quote($this->getServerId(), 'integer') . ', ' .
            $this->db->quote($this->getCommunityId(), 'integer') . ', ' .
            $this->db->quote($this->getOwnId(), 'integer') . ', ' .
            $this->db->quote($this->getCommunityName(), 'text') . ', ' .
            $this->db->quote(serialize($this->getMids()), 'text') . ' ' .
            ')';
        $this->db->manipulate($query);
        return true;
    }

    /**
     * Read dataset
     */
    private function read() : void
    {
        $this->entryExists = false;

        $query = 'SELECT * FROM ecs_community ' .
            'WHERE sid = ' . $this->db->quote($this->getServerId(), 'integer') . ' ' .
            'AND cid = ' . $this->db->quote($this->getCommunityId(), 'integer');
        $res = $this->db->query($query);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $this->entryExists = true;
            $this->setOwnId((int) $row->own_id);
            $this->setCommunityName($row->cname);
            $this->setMids(unserialize($row->mids, ['allowed_classes' => true]));
        }
    }
    
    /**
     * @todo move function into CommunityCacheRepository
     *
     * @param int $a_server_id
     * @return bool
     */
    public function deleteByServerId(int $a_server_id) : bool
    {
        $query = 'DELETE FROM ecs_community' .
            ' WHERE sid = ' . $this->db->quote($a_server_id, 'integer');
        $this->db->manipulate($query);
        return true;
    }
}
