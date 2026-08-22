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

readonly class SessionValidationClient
{
    public function __construct(
        private string $location,
        private string $namespace,
        private bool $use_dotnet = false,
    ) {
    }

    public function validateSession(
        string $ext_uid,
        string $soap_pw,
        bool $new_user,
    ): SessionValidationResult {
        $service_namespace = $this->namespace !== '' ? $this->namespace : $this->location;

        $soap_client = new \SoapClient(null, [
            'location' => $this->location,
            'uri' => $service_namespace,
            'trace' => true,
            'exceptions' => true,
            'style' => SOAP_RPC,
            'use' => SOAP_ENCODED,
        ]);

        $call_options = ['uri' => $service_namespace];
        if ($this->use_dotnet) {
            $call_options['soapaction'] = $service_namespace . '/isValidSession';
        }

        $valid = $soap_client->__soapCall(
            'isValidSession',
            $this->buildCallParameters($ext_uid, $soap_pw, $new_user),
            $call_options
        );

        return new SessionValidationResult(
            $this->normalizeValidationResult($valid),
            (string) $soap_client->__getLastRequest(),
            (string) $soap_client->__getLastResponse(),
        );
    }

    /**
     * Mirrors legacy nusoap_client::call() parameter naming (ns1: prefix in .NET mode).
     * @return list<\SoapParam>
     */
    private function buildCallParameters(string $ext_uid, string $soap_pw, bool $new_user): array
    {
        if ($this->use_dotnet) {
            return [
                new \SoapParam($ext_uid, 'ns1:ext_uid'),
                new \SoapParam($soap_pw, 'ns1:soap_pw'),
                new \SoapParam($new_user, 'ns1:new_user'),
            ];
        }

        return [
            new \SoapParam($ext_uid, 'ext_uid'),
            new \SoapParam($soap_pw, 'soap_pw'),
            new \SoapParam($new_user, 'new_user'),
        ];
    }

    /**
     * @return array{valid: bool, firstname?: string, lastname?: string, email?: string}
     */
    private function normalizeValidationResult(mixed $valid): array
    {
        if (\is_object($valid)) {
            $valid = (array) $valid;
        }

        if (!\is_array($valid)) {
            return ['valid' => false];
        }

        return $valid;
    }
}
