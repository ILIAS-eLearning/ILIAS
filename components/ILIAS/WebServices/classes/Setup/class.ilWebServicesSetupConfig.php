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

use ILIAS\Setup;

class ilWebServicesSetupConfig implements Setup\Config
{
    protected bool $soap_user_administration;
    protected string $soap_wsdl_path;
    protected int $soap_connect_timeout;
    protected string $rpc_server_host;
    protected int $rpc_server_port;

    protected string $soap_internal_wsdl_path;
    protected bool $soap_internal_wsdl_verify_peer;
    protected bool $soap_internal_wsdl_verify_peer_name;
    protected bool $soap_internal_wsdl_allow_self_signed;

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
        int $rpc_server_port,
        string $soap_internal_wsdl_path,
        bool $soap_internal_wsdl_verify_peer,
        bool $soap_internal_wsdl_verify_peer_name,
        bool $soap_internal_wsdl_allow_self_signed,
    ) {
        $this->soap_user_administration = $soap_user_administration;
        $this->soap_wsdl_path = $soap_wsdl_path;
        $this->soap_connect_timeout = $soap_connect_timeout;
        $this->rpc_server_host = $rpc_server_host;
        $this->rpc_server_port = $rpc_server_port;
        $this->soap_response_timeout = $soap_response_timeout;

        $this->soap_internal_wsdl_path = $soap_internal_wsdl_path;
        $this->soap_internal_wsdl_verify_peer = $soap_internal_wsdl_verify_peer;
        $this->soap_internal_wsdl_verify_peer_name = $soap_internal_wsdl_verify_peer_name;
        $this->soap_internal_wsdl_allow_self_signed = $soap_internal_wsdl_allow_self_signed;
    }

    public function isSOAPUserAdministration(): bool
    {
        return $this->soap_user_administration;
    }

    public function getSOAPWsdlPath(): string
    {
        return $this->soap_wsdl_path;
    }

    public function getSOAPConnectTimeout(): int
    {
        return $this->soap_connect_timeout;
    }

    public function getRPCServerHost(): string
    {
        return $this->rpc_server_host;
    }

    public function getRPCServerPort(): int
    {
        return $this->rpc_server_port;
    }

    public function getSoapResponseTimeout(): int
    {
        return $this->soap_response_timeout;
    }

    public function getSoapInternalWsdlPath(): string
    {
        return $this->soap_internal_wsdl_path;
    }

    public function getSoapInternalWsdlVerifyPeer(): bool
    {
        return $this->soap_internal_wsdl_verify_peer;
    }

    public function getSoapInternalWsdlVerifyPeerName(): bool
    {
        return $this->soap_internal_wsdl_verify_peer_name;
    }

    public function getSoapInternalWsdlAllowSelfSigned(): bool
    {
        return $this->soap_internal_wsdl_allow_self_signed;
    }
}
