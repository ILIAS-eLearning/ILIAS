<?php

declare(strict_types=1);
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
 */
class ilRPCServerSettings
{
    private const RPC_SERVER_PATH = "/RPC2";

    private static ?ilRPCServerSettings $instance = null;

    public string $rpc_host = '';
    public string $rpc_port = '';

    private ilLogger $log;
    private ilSetting $settings;

    private function __construct()
    {
        global $DIC;

        $this->log = $DIC->logger()->wsrv();
        $this->settings = $DIC->settings();
    }

    public static function getInstance(): ilRPCServerSettings
    {
        if (self::$instance) {
            return self::$instance;
        }
        return self::$instance = new ilRPCServerSettings();
    }

    /**
     * Returns true if server ip and port are set.
     */
    public function isEnabled(): bool
    {
        return $this->getHost() !== '' && $this->getPort() !== '';
    }

    public function getServerUrl(): string
    {
        return 'http://' . $this->getHost() . ':' . $this->getPort() . '/' . self::RPC_SERVER_PATH;
    }

    public function getHost(): string
    {
        if ($this->rpc_host !== '') {
            return $this->rpc_host;
        }
        return $this->rpc_host = (string) $this->settings->get('rpc_server_host');
    }

    public function setHost($a_host): void
    {
        $this->rpc_host = $a_host;
    }

    public function getPort(): string
    {
        if ($this->rpc_port !== '') {
            return $this->rpc_port;
        }
        return $this->rpc_port = (string) $this->settings->get('rpc_server_port');
    }

    public function setPort(string $a_port): void
    {
        $this->rpc_port = $a_port;
    }

    public function getPath(): string
    {
        return self::RPC_SERVER_PATH;
    }

    public function update(): void
    {
        $this->settings->set('rpc_server_host', $this->getHost());
        $this->settings->set('rpc_server_port', $this->getPort());
    }

    public function pingServer(): bool
    {
        try {
            ilRpcClientFactory::factory('RPCebug')->ping();
            return true;
        } catch (Exception $e) {
            $this->log->warning('Calling RPC server failed with message: ' . $e->getMessage());
            return false;
        }
    }
}
