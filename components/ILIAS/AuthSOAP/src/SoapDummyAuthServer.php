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

namespace ILIAS\AuthSOAP;

class SoapDummyAuthServer
{
    public const SERVICE_NAME = 'ILIAS SOAP Dummy Authentication Server';
    public const SERVICE_NAMESPACE = 'urn:SoapDummyAuthServer';

    public function __construct(private readonly bool $use_wsdl = true)
    {
    }

    public function start(): never
    {
        if ($this->isPostRequest()) {
            $this->handleNativeSoapRequest();
        } else {
            $this->handleNusoapRequest();
        }

        exit();
    }

    private function isPostRequest(): bool
    {
        return strcasecmp($_SERVER['REQUEST_METHOD'] ?? '', 'POST') === 0;
    }

    private function handleNativeSoapRequest(): void
    {
        $uri = $this->buildEndpointUri();

        // Non-WSDL mode: nusoap WSDL describes RPC/encoded ops as "ns1:isValidSession", which
        // native SoapServer cannot map when loading that WSDL. Dispatch via __soapCall instead.
        $soap_server = new \SoapServer(null, [
            'uri' => self::SERVICE_NAMESPACE,
            'location' => $uri,
        ]);
        $soap_server->setObject(new SoapDummyAuthHandler());
        $soap_server->handle();
    }

    private function handleNusoapRequest(): void
    {
        $server = $this->createNusoapServer();

        $post_data = file_get_contents('php://input');

        $server->service($post_data);
    }

    private function createNusoapServer(): \soap_server
    {
        require_once __DIR__ . '/../../soap/lib/nusoap.php';
        $server = new \soap_server();
        $server->class = SoapDummyAuthHandler::class;

        if ($this->use_wsdl) {
            $this->enableWSDL($server);
        }

        $this->registerNusoapMethods($server);

        return $server;
    }

    private function buildEndpointUri(): string
    {
        $https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
        $scheme = $https ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        $script = $_SERVER['SCRIPT_NAME'] ?? '';

        return $scheme . '://' . $host . $script;
    }

    public function enableWSDL(\soap_server $server): bool
    {
        $server->configureWSDL(self::SERVICE_NAME, self::SERVICE_NAMESPACE);

        return true;
    }

    private function registerNusoapMethods(\soap_server $server): void
    {
        $server->wsdl->addComplexType(
            'intArray',
            'complexType',
            'array',
            '',
            'SOAP-ENC:Array',
            [],
            [['ref' => 'SOAP-ENC:arrayType', 'wsdl:arrayType' => 'xsd:int[]']],
            'xsd:int'
        );

        $server->wsdl->addComplexType(
            'stringArray',
            'complexType',
            'array',
            '',
            'SOAP-ENC:Array',
            [],
            [['ref' => 'SOAP-ENC:arrayType', 'wsdl:arrayType' => 'xsd:string[]']],
            'xsd:string'
        );

        $server->register(
            'isValidSession',
            [
                'ext_uid' => 'xsd:string',
                'soap_pw' => 'xsd:string',
                'new_user' => 'xsd:boolean'
            ],
            [
                'valid' => 'xsd:boolean',
                'firstname' => 'xsd:string',
                'lastname' => 'xsd:string',
                'email' => 'xsd:string'
            ],
            self::SERVICE_NAMESPACE,
            self::SERVICE_NAMESPACE . '#isValidSession',
            'rpc',
            'encoded',
            'Dummy Session Validation'
        );
    }
}
