<?php

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
