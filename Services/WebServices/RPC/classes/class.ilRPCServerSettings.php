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
* Class for storing all rpc communication settings
*
* @author Stefan Meyer <meyer@leifos.com>
* @version $Id$
*
* @package ilias
*/

define("RPC_SERVER_PATH", "/RPC2");
define("RPC_SERVER_ALIVE", true);

class ilRPCServerSettings
{
    private static $instance = null;
    
    
    public $rpc_host = '';
    public $rpc_port = '';

    public $log = null;
    public $db = null;

    public $settings_obj = null;


    /**
     * Singleton contructor
     * @return
     */
    private function __construct()
    {
        global $DIC;

        $ilDB = $DIC['ilDB'];
        $ilias = $DIC['ilias'];

        $this->log = $DIC->logger()->wsrv();
        $this->db = $ilDB;
        $this->ilias = $ilias;
    }

    /**
     * Get singelton instance
     * @return object $ilRPCServerSettings
     */
    public static function getInstance()
    {
        if (self::$instance) {
            return self::$instance;
        }
        return self::$instance = new ilRPCServerSettings();
    }
    
    /**
     * Returns true if server ip and port are set.
     * @return bool
     */
    public function isEnabled()
    {
        return strlen($this->getHost()) and strlen($this->getPort());
    }
    
    public function getServerUrl()
    {
        return 'http://' . $this->getHost() . ':' . $this->getPort() . '/' . RPC_SERVER_PATH;
    }
    

    public function getHost()
    {
        if (strlen($this->rpc_host)) {
            return $this->rpc_host;
        }
        return $this->rpc_host = $this->ilias->getSetting('rpc_server_host');
    }
    public function setHost($a_host)
    {
        $this->rpc_host = $a_host;
    }
    public function getPort()
    {
        if (strlen($this->rpc_port)) {
            return $this->rpc_port;
        }
        return $this->rpc_port = $this->ilias->getSetting('rpc_server_port');
    }
    public function setPort($a_port)
    {
        $this->rpc_port = $a_port;
    }
    public function getPath()
    {
        return RPC_SERVER_PATH;
    }

    public function update()
    {
        $this->ilias->setSetting('rpc_server_host', $this->getHost());
        $this->ilias->setSetting('rpc_server_port', $this->getPort());
        
        return true;
    }

    /**
     * @return bool
     */
    public function pingServer()
    {
        include_once './Services/WebServices/RPC/classes/class.ilRpcClientFactory.php';
        
        try {
            ilRpcClientFactory::factory('RPCebug')->ping();
            return true;
        } catch (Exception $e) {
            $this->log->warning('Calling RPC server failed with message: ' . $e->getMessage());
            return false;
        }
    }
}
