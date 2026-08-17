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

final class ilLTIConsumerAccessToken
{
    /** @param list<string> $scopes */
    private function __construct(
        private readonly string $client_id,
        private readonly array $scopes
    ) {
    }

    public static function fromVerifiedToken(object $token): ?self
    {
        $client_id = $token->sub ?? null;
        if (!is_string($client_id) || $client_id === '') {
            return null;
        }

        $scope = $token->{'imsglobal.org.security.scope'} ?? '';
        if (is_string($scope)) {
            $scopes = preg_split('/\s+/', trim($scope)) ?: [];
        } elseif (is_array($scope) && array_is_list($scope)) {
            $scopes = [];
            foreach ($scope as $value) {
                if (!is_string($value)) {
                    return null;
                }
                $scopes[] = $value;
            }
        } else {
            $scopes = [];
        }

        return new self($client_id, $scopes);
    }

    public function getClientId(): string
    {
        return $this->client_id;
    }

    /** @return list<string> */
    public function getScopes(): array
    {
        return $this->scopes;
    }
}
