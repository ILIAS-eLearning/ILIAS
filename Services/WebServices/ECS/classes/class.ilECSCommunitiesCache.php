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
class ilECSCommunitiesCache
{
    private static ?\ilECSCommunitiesCache $instance = null;

    private ilDBInterface $db;

    private array $communities = array();

    /**
     * Singleton constructor
     */
    protected function __construct()
    {
        global $DIC;

        $this->db = $DIC->database();

        $this->read();
    }

    /**
     * Singleton instance
     * @return ilECSCommunitiesCache
     */
    public static function getInstance() : ilECSCommunitiesCache
    {
        return self::$instance ?? (self::$instance = new ilECSCommunitiesCache());
    }

    /**
     * Delete comunities by server id
     */
    public function delete(int $a_server_id) : void
    {
        $query = 'DELETE FROM ecs_community ' .
            'WHERE sid = ' . $this->db->quote($a_server_id, 'integer');
        $this->db->manipulate($query);
        $this->read();
    }
    
    /**
     * Get communities
     * @return ilECSCommunityCache[]
     */
    public function getCommunities() : array
    {
        return $this->communities;
    }

    /**
     * Lookup own mid of the community of a mid
     */
    public function lookupOwnId(int $a_server_id, int $a_mid) : int
    {
        foreach ($this->getCommunities() as $com) {
            if (($com->getServerId() === $a_server_id) && in_array($a_mid, $com->getMids(), true)) {
                return $com->getOwnId();
            }
        }
        return 0;
    }

    /**
     * Lookup community title
     * @param int server_id
     * @param int mid
     */
    public function lookupTitle(int $a_server_id, int $a_mid) : string
    {
        foreach ($this->getCommunities() as $com) {
            if (($com->getServerId() === $a_server_id) && in_array($a_mid, $com->getMids(), true)) {
                return $com->getCommunityName();
            }
        }
        return '';
    }

    /**
     * Read comunities
     */
    private function read() : void
    {
        $query = 'SELECT sid,cid FROM ecs_community ';
        $res = $this->db->query($query);
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $this->communities[] = ilECSCommunityCache::getInstance((int) $row->sid, (int) $row->cid);
        }
    }
}
