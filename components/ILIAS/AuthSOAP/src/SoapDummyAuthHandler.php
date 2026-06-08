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

/**
 * @phpstan-type ValidationResult array{firstname: string, lastname: string, email: string, valid: bool}
 * @phpstan-type SessionParameters array{ext_uid: string, soap_pw: string, new_user: bool}
 * @phpstan-type SoapCallArgument string|bool|int|float|object|array<int|string, string|bool|int|float|object>
 * @phpstan-type SoapCallArguments list<SoapCallArgument>
 * @phpstan-type NormalizedParameterInput array<int|string, SoapCallArgument>
 */
class SoapDummyAuthHandler
{
    /**
     * @return ValidationResult
     */
    public function isValidSession(string $ext_uid, string $soap_pw, bool $new_user): array
    {
        return $this->buildValidationResult($ext_uid, $soap_pw, $new_user);
    }

    /**
     * @param SoapCallArguments $arguments
     * @return ValidationResult
     */
    public function __soapCall(string $function_name, array $arguments): array
    {
        if ($this->localOperationName($function_name) !== 'isValidSession') {
            throw new \SoapFault('SOAP-ENV:Server', "Procedure '$function_name' not present");
        }

        return $this->invokeIsValidSession($arguments);
    }

    private function localOperationName(string $name): string
    {
        if (str_contains($name, ':')) {
            return substr($name, strrpos($name, ':') + 1);
        }

        return $name;
    }

    /**
     * @param SoapCallArguments|NormalizedParameterInput $arguments
     * @return ValidationResult
     */
    private function invokeIsValidSession(array $arguments): array
    {
        if (\count($arguments) === 1) {
            $first = $arguments[0];
            if (\is_object($first)) {
                /** @var NormalizedParameterInput $arguments */
                $arguments = (array) $first;
            } elseif (\is_array($first)) {
                /** @var NormalizedParameterInput $arguments */
                $arguments = $first;
            }
        }

        $parameters = $this->normalizeParameterKeys($arguments);

        return $this->buildValidationResult(
            $parameters['ext_uid'],
            $parameters['soap_pw'],
            $parameters['new_user']
        );
    }

    /**
     * @param NormalizedParameterInput $arguments
     * @return SessionParameters
     */
    private function normalizeParameterKeys(array $arguments): array
    {
        $normalized = [];
        foreach ($arguments as $key => $value) {
            if (!\is_string($key)) {
                continue;
            }
            $normalized[$this->localOperationName($key)] = $value;
        }

        if ($normalized !== []) {
            return [
                'ext_uid' => (string) ($normalized['ext_uid'] ?? ''),
                'soap_pw' => (string) ($normalized['soap_pw'] ?? ''),
                'new_user' => (bool) ($normalized['new_user'] ?? false),
            ];
        }

        return [
            'ext_uid' => (string) ($arguments[0] ?? ''),
            'soap_pw' => (string) ($arguments[1] ?? ''),
            'new_user' => (bool) ($arguments[2] ?? false),
        ];
    }

    /**
     * @return ValidationResult
     */
    private function buildValidationResult(string $ext_uid, string $soap_pw, bool $new_user): array
    {
        $result = [
            'firstname' => '',
            'lastname' => '',
            'email' => '',
            'valid' => $ext_uid === $soap_pw,
        ];

        if ($new_user) {
            $result['firstname'] = 'first ' . $ext_uid;
            $result['lastname'] = 'last ' . $ext_uid;
            $result['email'] = $ext_uid . '@de.de';
        }

        return $result;
    }
}
