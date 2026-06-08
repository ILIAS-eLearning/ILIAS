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

final readonly class SoapAuthEndpoint
{
    private string $location;
    private string $namespace;
    private bool $use_dotnet;

    public function __construct(\ilSetting $settings)
    {
        $this->namespace = (string) $settings->get('soap_auth_namespace', '');
        $this->use_dotnet = (bool) $settings->get('soap_auth_use_dotnet', '0');
        $this->location = $this->buildLocation(
            (string) $settings->get('soap_auth_server', ''),
            (int) $settings->get('soap_auth_port', '0'),
            (string) $settings->get('soap_auth_uri', ''),
            (bool) $settings->get('soap_auth_use_https', '0'),
        );
    }

    public function getLocation(): string
    {
        return $this->location;
    }

    public function getNamespace(): string
    {
        return $this->namespace;
    }

    public function useDotnet(): bool
    {
        return $this->use_dotnet;
    }

    public function createValidationClient(): SessionValidationClient
    {
        return new SessionValidationClient(
            $this->location,
            $this->namespace,
            $this->use_dotnet,
        );
    }

    private function buildLocation(
        string $server_hostname,
        int $server_port,
        string $server_uri,
        bool $use_https,
    ): string {
        $uri = $use_https ? 'https://' : 'http://';
        $uri .= $server_hostname;

        if ($server_port > 0) {
            $uri .= ':' . $server_port;
        }

        if ($server_uri !== '') {
            $uri .= '/' . $server_uri;
        }

        return $uri;
    }
}
