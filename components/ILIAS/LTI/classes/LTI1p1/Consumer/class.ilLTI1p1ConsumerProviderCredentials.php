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
 * OAuth1 key and secret of an external tool. They share the lti_ext_provider row
 * with the LTI Advantage settings of the same provider.
 */
final class ilLTI1p1ConsumerProviderCredentials
{
    private string $key = '';
    private string $secret = '';
    private bool $key_customizable = true;

    public function getKey(): string
    {
        return $this->key;
    }

    public function setKey(string $key): void
    {
        $this->key = $key;
    }

    public function getSecret(): string
    {
        return $this->secret;
    }

    public function setSecret(string $secret): void
    {
        $this->secret = $secret;
    }

    public function isKeyCustomizable(): bool
    {
        return $this->key_customizable;
    }

    public function setKeyCustomizable(bool $key_customizable): void
    {
        $this->key_customizable = $key_customizable;
    }

    public function assignFromDbRow(array $row): void
    {
        if (array_key_exists('provider_key', $row)) {
            $this->setKey((string) $row['provider_key']);
        }
        if (array_key_exists('provider_secret', $row)) {
            $this->setSecret((string) $row['provider_secret']);
        }
        if (array_key_exists('provider_key_customizable', $row)) {
            $this->setKeyCustomizable((bool) $row['provider_key_customizable']);
        }
    }

    /**
     * @return array<string, array{0: string, 1: string|int}>
     */
    public function getDbFields(): array
    {
        return [
            'provider_key' => ['text', $this->getKey()],
            'provider_secret' => ['text', $this->getSecret()],
            'provider_key_customizable' => ['integer', (int) $this->isKeyCustomizable()],
        ];
    }
}
