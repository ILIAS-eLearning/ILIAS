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
 * Collection of ECS settings
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 * @author Per Pascal Seeland <pascal.seeland@tik.uni-stuttgart.de>
 */
class ilECSServerSettings
{
    const ALL_SERVER = 0;
    const ACTIVE_SERVER = 1;
    const INACTIVE_SERVER = 2;

    private static ilECSServerSettings $instance;

    // Injected
    private ilDBInterface $db;
    private ilLogger $log;

    // Local
    private array $servers;


    /**
     * Singleton contructor
     */
    protected function __construct()
    {
        global $DIC;

        $this->db = $DIC->database();
        $this->log = $DIC->logger()->wsrv();

        $this->readServers();
    }

    /**
     * Get singleton instance
     *
     * @return ilECSServerSettings
     */
    public static function getInstance() : ilECSServerSettings
    {
        if (isset(self::$instance)) {
            return self::$instance;
        }
        return self::$instance = new ilECSServerSettings();
    }

    /**
     * Check if there is any active server
     * @return bool
     */
    public function activeServerExists() : bool
    {
        return count($this->getServers(static::ACTIVE_SERVER)) ? true : false;
    }

    /**
     * Check if there is any server
     * @return bool
     */
    public function serverExists()
    {
        return count($this->getServers(static::ALL_SERVER)) ? true : false;
    }

    /**
     * Get servers
     * The function must be called with  ALL_SERVER, ACTIVE_SERVER or INACTIVE_SERVER
     * @return ilECSSetting[]
     */
    public function getServers(int $server_type) : array
    {
        switch ($server_type) {
            case static::ALL_SERVER:
                return $this->servers;
                break;
            case static::ACTIVE_SERVER:
                return array_filter($this->servers, fn (ilECSSetting $server) => $server->isEnabled());
                break;
            case static::INACTIVE_SERVER:
                return array_filter($this->servers, fn (ilECSSetting $server) => !$server->isEnabled());
                break;
            default:
                throw new InvalidArgumentException();
        }
    }

    /**
     * Read all servers
     */
    private function readServers()
    {
        $query = 'SELECT server_id FROM ecs_server ' .
            'ORDER BY title ';
        $res = $this->db->query($query);

        $this->servers = array();
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $server_id = intval($row->server_id);
            $this->servers[$server_id] = ilECSSetting::getInstanceByServerId($server_id);
        }
    }
}
