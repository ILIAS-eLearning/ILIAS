<?php declare(strict_types=1);

/* Copyright (c) 2020 Daniel Weise <daniel.weise@concepts-and-training.de> Extended GPL, see docs/LICENSE */

use ILIAS\Setup;

class ilWebServicesSetupConfig implements Setup\Config
{
    protected bool $soap_user_administration;
    protected string $soap_wsdl_path;
    protected int $soap_connect_timeout;
    protected string $rpc_server_host;
    protected int $rpc_server_port;

    public function __construct(
        bool $soap_user_administration,
        string $soap_wsdl_path,
        int $soap_connect_timeout,
        string $rpc_server_host,
        int $rpc_server_port
    ) {
        $this->soap_user_administration = $soap_user_administration;
        $this->soap_wsdl_path = $soap_wsdl_path;
        $this->soap_connect_timeout = $soap_connect_timeout;
        $this->rpc_server_host = $rpc_server_host;
        $this->rpc_server_port = $rpc_server_port;
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
}
