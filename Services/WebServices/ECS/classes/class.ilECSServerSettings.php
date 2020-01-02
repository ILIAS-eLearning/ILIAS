<?php
/*
    +-----------------------------------------------------------------------------+
    | ILIAS open source                                                           |
    +-----------------------------------------------------------------------------+
    | Copyright (c) 1998-2006 ILIAS open source, University of Cologne            |
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

include_once './Services/WebServices/ECS/classes/class.ilECSSetting.php';

/**
 * Collection of ECS settings
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 * @version $Id$
 *
 *
 * @ingroup ServicesWebServicesECS
 */
class ilECSServerSettings
{
    private static $instance = null;

    private $servers = array();


    /**
     * Singleton contructor
     */
    protected function __construct()
    {
        $this->readActiveServers();
    }

    /**
     * Get singleton instance
     *
     * @return ilECSServerSettings
     */
    public static function getInstance()
    {
        if (self::$instance) {
            return self::$instance;
        }
        return self::$instance = new ilECSServerSettings();
    }

    /**
     * Check if there is any active server
     * @return bool
     */
    public function activeServerExists()
    {
        return count($this->getServers()) ? true : false;
    }

    /**
     * Check if there is any server
     * @return bool
     */
    public function serverExists()
    {
        return count($this->getServers()) ? true : false;
    }

    /**
     * Get servers
     * @return array ilECSSetting
     */
    public function getServers()
    {
        return (array) $this->servers;
    }

    /**
     * Read inactive servers
     * @global ilDB $ilDB
     */
    public function readInactiveServers()
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        $query = 'SELECT server_id FROM ecs_server ' .
            'WHERE active =  ' . $ilDB->quote(0, 'integer') . ' ' .
            'ORDER BY title ';
        $res = $ilDB->query($query);

        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $this->servers[$row->server_id] = ilECSSetting::getInstanceByServerId($row->server_id);
        }
    }

    /**
     * Read all actice servers
     * @global ilDB $ilDB
     */
    private function readActiveServers()
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];

        $query = 'SELECT server_id FROM ecs_server ' .
            'WHERE active =  ' . $ilDB->quote(1, 'integer') . ' ' .
            'ORDER BY title ';
        $res = $ilDB->query($query);

        $this->servers = array();
        while ($row = $res->fetchRow(ilDBConstants::FETCHMODE_OBJECT)) {
            $this->servers[$row->server_id] = ilECSSetting::getInstanceByServerId($row->server_id);
        }
    }
}
