<?php

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

declare(strict_types=1);
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
