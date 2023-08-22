<?php declare(strict_types=1);

/* Copyright (c) 2020 Daniel Weise <daniel.weise@concepts-and-training.de> Extended GPL, see docs/LICENSE */

use ILIAS\Setup;

class ilWebServicesSetupConfig implements Setup\Config
{
    /**
     * @var bool
     */
    protected $soap_user_administration;

    /**
     * @var string
     */
    protected $soap_wsdl_path;

    /**
     * @var int
     */
    protected $soap_connect_timeout;

    /**
     * @var string
     */
    protected $rpc_server_host;

    /**
     * @var int
     */
    protected $rpc_server_port;

    /**
     * @var int
     */
    protected $soap_response_timeout;

    public function __construct(
        bool $soap_user_administration,
        string $soap_wsdl_path,
        int $soap_connect_timeout,
        int $soap_response_timeout,
        string $rpc_server_host,
        int $rpc_server_port
    ) {
        $this->soap_user_administration = $soap_user_administration;
        $this->soap_wsdl_path = $soap_wsdl_path;
        $this->soap_connect_timeout = $soap_connect_timeout;
        $this->rpc_server_host = $rpc_server_host;
        $this->rpc_server_port = $rpc_server_port;
        $this->soap_response_timeout = $soap_response_timeout;
    }

    public function isSOAPUserAdministration() : bool
    {
        return $this->soap_user_administration;
    }

    public function getSOAPWsdlPath() : string
    {
        return $this->soap_wsdl_path;
    }

    public function getSOAPConnectTimeout() : int
    {
        return $this->soap_connect_timeout;
    }

    public function getRPCServerHost() : string
    {
        return $this->rpc_server_host;
    }

    public function getRPCServerPort() : int
    {
        return $this->rpc_server_port;
    }

    public function getSoapResponseTimeout(): int
    {
        return $this->soap_response_timeout;
    }
}
